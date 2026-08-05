<?php

namespace App\Services\Xui;

use App\Models\User;
use App\Notifications\OrderApproved;

/**
 * Activa la línea M3U de la prueba gratuita pendiente de un usuario justo
 * después de que verifica su correo (ver VerifyEmailController). Evita que
 * se cree la línea real en XUI antes de confirmar que el correo es legítimo.
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

            $order->update(['status' => 'approved', 'approved_at' => now()]);
            $user->notify(new OrderApproved($order, $line));
        } catch (XuiApiException $e) {
            $order->update(['status' => 'error', 'admin_note' => $e->getMessage()]);
        }
    }
}
