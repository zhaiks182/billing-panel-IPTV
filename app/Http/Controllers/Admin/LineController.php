<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Line;
use App\Models\LineActivityLog;
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
        $activityLogs = LineActivityLog::where('line_id', $line->id)->with('admin')->latest()->get();

        return view('admin.lines.show', compact('line', 'packages', 'activityLogs'));
    }

    public function renew(Line $line, XuiLineService $xui)
    {
        $line->loadMissing('order.package');

        if (! $line->order?->package) {
            return back()->withErrors(['xui' => 'Esta línea no tiene un pedido/paquete asociado para renovar.']);
        }

        $packageName = $line->order->package->name;

        try {
            $xui->applyPackage($line, $line->order->package);
        } catch (XuiApiException|RuntimeException $e) {
            LineActivityLog::record($line, 'renew_failed', "Intentó renovar con el paquete «{$packageName}» pero falló: {$e->getMessage()}");

            return back()->withErrors(['xui' => $e->getMessage()]);
        }

        LineActivityLog::record($line, 'renewed', "Renovó la línea aplicando el paquete «{$packageName}».");

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
            LineActivityLog::record($line, 'apply_package_failed', "Intentó aplicar el paquete «{$package->name}» pero falló: {$e->getMessage()}");

            return back()->withErrors(['xui' => $e->getMessage()]);
        }

        LineActivityLog::record($line, 'apply_package', "Aplicó el paquete «{$package->name}» a la línea.");

        return back()->with('status', "Se aplicó el paquete «{$package->name}» a la línea.");
    }

    public function toggleSuspend(Line $line, XuiLineService $xui)
    {
        $suspending = $line->status !== 'suspended';

        try {
            $xui->setSuspended($line, $suspending);
        } catch (XuiApiException $e) {
            $verb = $suspending ? 'suspender' : 'reactivar';
            LineActivityLog::record($line, 'suspend_failed', "Intentó {$verb} la línea pero falló: {$e->getMessage()}");

            return back()->withErrors(['xui' => $e->getMessage()]);
        }

        $verb = $suspending ? 'suspendida' : 'reactivada';
        LineActivityLog::record($line, $suspending ? 'suspended' : 'reactivated', "Dejó la línea {$verb}.");

        return back()->with('status', "Línea {$verb} correctamente.");
    }

    public function changePassword(Line $line, XuiLineService $xui)
    {
        $newPassword = Str::random(10);

        try {
            $xui->changePassword($line, $newPassword);
        } catch (XuiApiException $e) {
            LineActivityLog::record($line, 'password_change_failed', "Intentó cambiar la contraseña pero falló: {$e->getMessage()}");

            return back()->withErrors(['xui' => $e->getMessage()]);
        }

        LineActivityLog::record($line, 'password_changed', 'Cambió la contraseña de la línea.');

        return back()->with('status', "Contraseña actualizada. Nueva contraseña: {$newPassword}");
    }

    public function resend(Line $line)
    {
        $line->loadMissing(['user', 'order']);

        if (! $line->order) {
            return back()->withErrors(['xui' => 'Esta línea no tiene un pedido asociado, no se puede reenviar el correo.']);
        }

        $line->user->notify(new OrderApproved($line->order, $line));

        LineActivityLog::record($line, 'credentials_resent', "Reenvió las credenciales por correo a {$line->user->email}.");

        return back()->with('status', "Credenciales reenviadas a {$line->user->email}.");
    }

    public function sync(Line $line, XuiLineService $xui)
    {
        try {
            $xui->syncFromXui($line);
        } catch (XuiApiException|RuntimeException $e) {
            LineActivityLog::record($line, 'sync_failed', "Intentó sincronizar con XUI ONE pero falló: {$e->getMessage()}");

            return back()->withErrors(['xui' => $e->getMessage()]);
        }

        LineActivityLog::record($line, 'synced', 'Sincronizó la línea con XUI ONE.');

        return back()->with('status', 'Línea sincronizada con XUI ONE.');
    }

    public function destroy(Line $line, XuiLineService $xui)
    {
        $username = $line->xui_username;
        $customerEmail = $line->user?->email;

        try {
            $xui->delete($line);
        } catch (XuiApiException $e) {
            LineActivityLog::record($line, 'delete_failed', "Intentó eliminar la línea de {$username} pero falló: {$e->getMessage()}");

            return back()->withErrors(['xui' => $e->getMessage()]);
        }

        // La línea ya no existe en este punto (xui->delete() la borró) — line_id queda NULL
        // en el log (nullOnDelete), por eso el texto trae el usuario XUI y el correo del
        // cliente para que el registro siga siendo entendible sin la fila original.
        LineActivityLog::record(null, 'deleted', "Eliminó la línea {$username} del cliente {$customerEmail}.");

        return redirect()->route('admin.lines.index')->with('status', "Línea {$username} eliminada. Ya no aparecerá en el panel del cliente.");
    }
}
