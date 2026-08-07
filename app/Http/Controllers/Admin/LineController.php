<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Line;
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

        return view('admin.lines.show', compact('line'));
    }

    public function renew(Line $line, XuiLineService $xui)
    {
        $line->loadMissing('order.package');

        if (! $line->order?->package) {
            return back()->withErrors(['xui' => 'Esta línea no tiene un pedido/paquete asociado para renovar.']);
        }

        try {
            $xui->renew($line, $line->order->package->durationInDays());
        } catch (XuiApiException $e) {
            return back()->withErrors(['xui' => $e->getMessage()]);
        }

        return back()->with('status', 'Línea renovada correctamente.');
    }

    public function addDays(Request $request, Line $line, XuiLineService $xui)
    {
        $validated = $request->validate([
            'days' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        try {
            $xui->renew($line, (float) $validated['days']);
        } catch (XuiApiException $e) {
            return back()->withErrors(['xui' => $e->getMessage()]);
        }

        return back()->with('status', "Se agregaron {$validated['days']} día(s) a la línea.");
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
