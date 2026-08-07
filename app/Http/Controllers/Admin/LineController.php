<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Line;
use App\Models\Package;
use App\Notifications\OrderApproved;
use App\Services\Xui\XuiApiException;
use App\Services\Xui\XuiLineService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;

class LineController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q'));

        $lines = Line::with(['user', 'order.package'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('xui_username', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($u) use ($search) {
                            $u->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('expires_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.lines.index', compact('lines', 'search'));
    }

    public function show(Line $line)
    {
        $line->load(['user', 'order.package']);
        $packages = Package::where('is_active', true)->where('is_trial', false)->orderBy('price')->get();

        return view('admin.lines.show', compact('line', 'packages'));
    }

    public function renew(Line $line, XuiLineService $xui)
    {
        $line->loadMissing('order.package');

        if (! $line->order?->package) {
            return back()->withErrors(['xui' => 'Esta línea no tiene un pedido/paquete asociado para renovar.']);
        }

        try {
            $xui->applyPackage($line, $line->order->package);
        } catch (XuiApiException|RuntimeException $e) {
            return back()->withErrors(['xui' => $e->getMessage()]);
        }

        return back()->with('status', 'Línea renovada correctamente.');
    }

    public function applyPackage(Request $request, Line $line, XuiLineService $xui)
    {
        $validated = $request->validate([
            'package_id' => ['required', 'exists:packages,id'],
        ]);

        $package = Package::findOrFail($validated['package_id']);

        try {
            $xui->applyPackage($line, $package);
        } catch (XuiApiException|RuntimeException $e) {
            return back()->withErrors(['xui' => $e->getMessage()]);
        }

        return back()->with('status', "Se aplicó el paquete «{$package->name}» a la línea.");
    }

    public function toggleSuspend(Line $line, XuiLineService $xui)
    {
        $suspending = $line->status !== 'suspended';

        try {
            $xui->setSuspended($line, $suspending);
        } catch (XuiApiException $e) {
            return back()->withErrors(['xui' => $e->getMessage()]);
        }

        $verb = $suspending ? 'suspendida' : 'reactivada';

        return back()->with('status', "Línea {$verb} correctamente.");
    }

    public function changePassword(Line $line, XuiLineService $xui)
    {
        $newPassword = Str::random(10);

        try {
            $xui->changePassword($line, $newPassword);
        } catch (XuiApiException $e) {
            return back()->withErrors(['xui' => $e->getMessage()]);
        }

        return back()->with('status', "Contraseña actualizada. Nueva contraseña: {$newPassword}");
    }

    public function resend(Line $line)
    {
        $line->loadMissing(['user', 'order']);

        if (! $line->order) {
            return back()->withErrors(['xui' => 'Esta línea no tiene un pedido asociado, no se puede reenviar el correo.']);
        }

        $line->user->notify(new OrderApproved($line->order, $line));

        return back()->with('status', "Credenciales reenviadas a {$line->user->email}.");
    }

    public function sync(Line $line, XuiLineService $xui)
    {
        try {
            $xui->syncFromXui($line);
        } catch (XuiApiException|RuntimeException $e) {
            return back()->withErrors(['xui' => $e->getMessage()]);
        }

        return back()->with('status', 'Línea sincronizada con XUI ONE.');
    }
}
