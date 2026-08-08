<?php

namespace App\Services\Xui;

use App\Models\User;
use App\Notifications\OrderApproved;
use App\Notifications\OrderInvoice;

/**
 * Activa la línea M3U de la prueba gratuita pendiente de un usuario justo
 * después de que verifica su correo (ver VerifyEmailController). Evita que
 * se cree la línea real en XUI antes de confirmar que el correo es legítimo.
 * La factura de la prueba (OrderInvoice) también se envía recién acá, no al
 * crear el pedido — no tiene sentido facturar algo que todavía podría no
 * llegar a activarse por un correo falso sin verificar.
 */
class TrialActivator
{
    public function __construct(private readonly XuiLineService $xui)
    {
    }

    public function activatePendingFor(User $user): void
    {
        $order = $user->orders()
            ->where('status', 'pending')
            ->whereHas('package', fn ($q) => $q->where('is_trial', true))
            ->latest()
            ->first();

        if (! $order) {
            return;
        }

        try {
            $line = $this->xui->activate($order);

            // Sin paso intermedio "approved": una prueba gratis no la aprueba un admin, se
            // activa sola al verificar el correo — pasa directo de "pending" a "activated".
            $order->update(['status' => 'activated', 'approved_at' => now()]);
            $user->notify(new OrderInvoice($order));
            $user->notify(new OrderApproved($order, $line));
        } catch (XuiApiException $e) {
            $order->update(['status' => 'error', 'admin_note' => $e->getMessage()]);
        }
    }
}
