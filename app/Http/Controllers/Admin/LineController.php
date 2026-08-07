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
        $statusFilter = $request->query('status', '');

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
            ->when($statusFilter === 'active', function ($query) {
                $query->where('status', 'active')
                    ->where('expires_at', '>', now()->addDays(Line::EXPIRING_SOON_DAYS));
            })
            ->when($statusFilter === 'expiring_soon', function ($query) {
                $query->where('status', 'active')
                    ->whereBetween('expires_at', [now(), now()->addDays(Line::EXPIRING_SOON_DAYS)]);
            })
            ->when($statusFilter === 'expired', function ($query) {
                $query->where('status', '!=', 'suspended')->where('expires_at', '<=', now());
            })
            ->when($statusFilter === 'suspended', function ($query) {
                $query->where('status', 'suspended');
            })
            ->when($statusFilter === 'demo', function ($query) {
                $query->whereHas('order.package', function ($p) {
                    $p->where('is_trial', true);
                });
            })
            ->latest('expires_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.lines.index', compact('lines', 'search', 'statusFilter'));
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

    public function destroy(Line $line, XuiLineService $xui)
    {
        $username = $line->xui_username;

        try {
            $xui->delete($line);
        } catch (XuiApiException $e) {
            return back()->withErrors(['xui' => $e->getMessage()]);
        }

        return redirect()->route('admin.lines.index')->with('status', "Línea {$username} eliminada. Ya no aparecerá en el panel del cliente.");
    }
}
