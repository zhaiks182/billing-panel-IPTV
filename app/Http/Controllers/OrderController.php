<?php

namespace App\Http\Controllers;

use App\Exceptions\PackageSoldOutException;
use App\Models\Order;
use App\Models\Package;
use App\Models\PaymentMethod;
use App\Models\TurnstileSetting;
use App\Models\User;
use App\Notifications\OrderApproved;
use App\Notifications\OrderInvoice;
use App\Rules\ValidTurnstile;
use App\Services\InvoicePdfService;
use App\Services\Xui\XuiApiException;
use App\Services\Xui\XuiLineService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class OrderController extends Controller
{
    public function create(Package $package)
    {
        abort_unless($package->is_active, 404);

        $user = auth()->user();

        $paymentMethods = $package->is_trial
            ? collect()
            : PaymentMethod::where('is_active', true)->get();

        $trialAlreadyUsed = $package->is_trial && $user && $this->hasUsedTrial($user);
        $needsVerificationGate = $package->is_trial && ! ($user && $user->hasVerifiedEmail());

        $turnstileSiteKey = TurnstileSetting::current()->isActive()
            ? TurnstileSetting::current()->site_key
            : null;

        $soldOut = $package->isSoldOut();

        return view('orders.create', compact('package', 'paymentMethods', 'trialAlreadyUsed', 'needsVerificationGate', 'turnstileSiteKey', 'soldOut'));
    }

    public function status(Order $order)
    {
        abort_unless($order->user_id === auth()->id(), 403);

        return response()->json(['status' => $order->status]);
    }

    public function invoice(Order $order, InvoicePdfService $pdf)
    {
        abort_unless($order->user_id === auth()->id(), 403);

        return response($pdf->generate($order))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="'.$pdf->filename($order).'"');
    }

    public function store(Request $request, Package $package, XuiLineService $xui)
    {
        abort_unless($package->is_active, 404);

        $user = $request->user();

        if (! $user) {
            $user = $this->registerGuest($request);
        }

        if ($package->is_trial) {
            if ($this->hasUsedTrial($user)) {
                $message = 'Ya usaste tu prueba gratuita. Si quieres seguir disfrutando del servicio, elige uno de nuestros planes de pago.';

                if ($request->wantsJson()) {
                    return response()->json(['status' => 'trial_already_used', 'message' => $message], 422);
                }

                return redirect()->route('orders.create', $package)->with('status', $message);
            }

            return $this->storeTrial($request, $package, $xui, $user);
        }

        $validated = $request->validate([
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'customer_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $proofPath = $request->file('proof')->store('proofs', 'public');

        try {
            $order = $this->createOrderWithStockCheck($user, $package, [
                'payment_method_id' => $validated['payment_method_id'],
                'amount' => $package->price,
                'proof_path' => $proofPath,
                'customer_note' => $validated['customer_note'] ?? null,
                'is_renewal' => $user->lines()->where('status', 'active')->exists(),
                'status' => 'pending',
            ]);
        } catch (PackageSoldOutException) {
            $message = 'Este paquete se agotó. Elige otro plan o contáctanos.';

            if ($request->wantsJson()) {
                return response()->json(['status' => 'sold_out', 'message' => $message], 422);
            }

            return redirect()->route('orders.create', $package)->with('status', $message);
        }

        $user->notify(new OrderInvoice($order));

        session()->forget('cart_package_id');

        return redirect()->route('orders.index')
            ->with('status', "Tu pedido #{$order->id} fue recibido y está en revisión.");
    }

    private function registerGuest(Request $request): User
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone_country_code' => ['required', 'string', 'max:6'],
            'phone' => ['required', 'string', 'max:30'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255', 'regex:/^[\pL\s\.\'-]+$/u'],
            'state' => ['required', 'string', 'max:255', 'regex:/^[\pL\s\.\'-]+$/u'],
            'postal_code' => ['required', 'string', 'max:20', 'regex:/^[0-9]+$/'],
            'country' => ['required', 'string', Rule::in(collect(config('countries'))->pluck('name'))],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'cf-turnstile-response' => [new ValidTurnstile],
        ], [
            'city.regex' => 'La ciudad solo puede contener letras.',
            'state.regex' => 'El estado/provincia solo puede contener letras.',
            'postal_code.regex' => 'El código postal solo puede contener números.',
            'country.in' => 'Selecciona un país válido de la lista.',
        ]);

        $user = User::create([
            'name' => trim($request->first_name.' '.$request->last_name),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone_country_code' => $request->phone_country_code,
            'phone' => $request->phone,
            'address_line_1' => $request->address_line_1,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'country' => $request->country,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return $user;
    }

    /**
     * Crea el pedido dentro de una transacción, re-leyendo el paquete con lockForUpdate()
     * como primera consulta (antes del conteo) — así dos compras casi simultáneas del
     * último cupo se serializan en vez de vender de más. "Vendido" = cualquier pedido que
     * no sea 'rejected' (ver Package::soldCount()). No aplica si stock_limit es null.
     */
    private function createOrderWithStockCheck(User $user, Package $package, array $attributes): Order
    {
        return DB::transaction(function () use ($user, $package, $attributes) {
            $locked = Package::where('id', $package->id)->lockForUpdate()->first();

            if ($locked->force_sold_out) {
                throw new PackageSoldOutException;
            }

            if ($locked->stock_limit !== null) {
                $sold = Order::where('package_id', $locked->id)->where('status', '!=', 'rejected')->count();
                $soldSinceLimit = max(0, $sold - (int) ($locked->stock_baseline_sold ?? 0));

                if ($soldSinceLimit >= $locked->stock_limit) {
                    throw new PackageSoldOutException;
                }
            }

            return $user->orders()->create(array_merge(['package_id' => $package->id], $attributes));
        });
    }

    private function hasUsedTrial($user): bool
    {
        if ($user->isAdmin()) {
            return false;
        }

        return $user->orders()
            ->whereHas('package', fn ($q) => $q->where('is_trial', true))
            ->exists();
    }

    private function storeTrial(Request $request, Package $package, XuiLineService $xui, $user)
    {
        try {
            $order = $this->createOrderWithStockCheck($user, $package, [
                'payment_method_id' => null,
                'amount' => 0,
                'is_renewal' => $user->lines()->where('status', 'active')->exists(),
                'status' => 'pending',
            ]);
        } catch (PackageSoldOutException) {
            $message = 'Este paquete se agotó. Elige otro plan o contáctanos.';

            if ($request->wantsJson()) {
                return response()->json(['status' => 'sold_out', 'message' => $message], 422);
            }

            return redirect()->route('orders.create', $package)->with('status', $message);
        }

        session()->forget('cart_package_id');

        if (! $user->hasVerifiedEmail()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'pending_verification',
                    'order_id' => $order->id,
                    'email' => $user->email,
                ]);
            }

            return redirect()->route('orders.index')
                ->with('status', 'Revisa tu correo para verificar tu cuenta y activar tu línea de prueba automáticamente.');
        }

        try {
            $line = $xui->activate($order);

            $order->update(['status' => 'activated', 'approved_at' => now()]);
            $user->notify(new OrderInvoice($order));
            $user->notify(new OrderApproved($order, $line));

            if ($request->wantsJson()) {
                return response()->json(['status' => 'activated', 'redirect' => route('dashboard')]);
            }

            return redirect()->route('dashboard')
                ->with('status', '¡Tu línea de prueba está activa!');
        } catch (XuiApiException $e) {
            $order->update(['status' => 'error', 'admin_note' => $e->getMessage()]);

            if ($request->wantsJson()) {
                return response()->json(['status' => 'error'], 422);
            }

            return redirect()->route('orders.index')
                ->with('status', "Tu prueba #{$order->id} no pudo activarse automáticamente. Un administrador la revisará.");
        }
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $statusCounts = $user->orders()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $orders = $user->orders()
            ->with(['package', 'paymentMethod', 'line'])
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('orders.index', compact('orders', 'statusCounts'));
    }
}
