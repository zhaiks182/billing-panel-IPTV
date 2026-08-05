<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Package;
use App\Models\PaymentMethod;
use App\Models\TurnstileSetting;
use App\Models\User;
use App\Notifications\OrderApproved;
use App\Notifications\OrderInvoice;
use App\Rules\ValidTurnstile;
use App\Services\Xui\XuiApiException;
use App\Services\Xui\XuiLineService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        return view('orders.create', compact('package', 'paymentMethods', 'trialAlreadyUsed', 'needsVerificationGate', 'turnstileSiteKey'));
    }

    public function status(Order $order)
    {
        abort_unless($order->user_id === auth()->id(), 403);

        return response()->json(['status' => $order->status]);
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
                return redirect()->route('orders.create', $package)
                    ->with('status', 'Ya usaste tu prueba gratuita. Si quieres seguir disfrutando del servicio, elige uno de nuestros planes de pago.');
            }

            return $this->storeTrial($request, $package, $xui, $user);
        }

        $validated = $request->validate([
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'customer_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $proofPath = $request->file('proof')->store('proofs', 'public');

        $order = $user->orders()->create([
            'package_id' => $package->id,
            'payment_method_id' => $validated['payment_method_id'],
            'amount' => $package->price,
            'proof_path' => $proofPath,
            'customer_note' => $validated['customer_note'] ?? null,
            'is_renewal' => $user->lines()->where('status', 'active')->exists(),
            'status' => 'pending',
        ]);

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
        $order = $user->orders()->create([
            'package_id' => $package->id,
            'payment_method_id' => null,
            'amount' => 0,
            'is_renewal' => $user->lines()->where('status', 'active')->exists(),
            'status' => 'pending',
        ]);

        $user->notify(new OrderInvoice($order));

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

            $order->update(['status' => 'approved', 'approved_at' => now()]);
            $user->notify(new OrderApproved($order, $line));

            if ($request->wantsJson()) {
                return response()->json(['status' => 'approved', 'redirect' => route('dashboard')]);
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
        $orders = $request->user()->orders()
            ->with(['package', 'paymentMethod', 'line'])
            ->latest()
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }
}
