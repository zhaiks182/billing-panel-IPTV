<?php

namespace App\Listeners;

use App\Models\EmailLog;
use App\Models\User;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Registra en `email_logs` cada correo que el sistema intenta enviar (para el historial
 * "tipo WHMCS" en Admin > Usuarios > [cliente]). Funciona en dos fases porque Laravel no
 * dispara ningún evento de "falló el envío": MessageSending crea la fila con
 * status='failed' como valor pesimista por defecto (antes de intentar el envío real por
 * SMTP), y si MessageSent llega a dispararse (el envío tuvo éxito), se actualiza a 'sent'.
 * Las dos fases se correlacionan con un header custom (`X-Email-Log-Id`) porque son eventos
 * separados, no builds del mismo objeto en el mismo método.
 *
 * Todo el cuerpo de los dos métodos está en try/catch silencioso a propósito: este listener
 * corre en el camino crítico de CADA correo de la app (login, pedidos, tickets...) — un bug
 * acá nunca debe impedir que el correo real se mande.
 *
 * NO registrar esto a mano con Event::listen() — Laravel 13 auto-descubre listeners en
 * app/Listeners por convención (primer parámetro tipado con la clase del evento), así que
 * un registro manual además de la auto-detección hace que cada método corra dos veces por
 * cada correo (bug real encontrado en pruebas: dos filas por envío, una 'sent' y otra
 * 'failed' huérfana, porque dos UUIDs distintos competían por el mismo header).
 */
class LogEmailAttempt
{
    public function handleSending(MessageSending $event): void
    {
        try {
            if (! Schema::hasTable('email_logs')) {
                return;
            }

            $to = collect($event->message->getTo())->first();

            if (! $to) {
                return;
            }

            $logId = (string) Str::uuid();
            $event->message->getHeaders()->addTextHeader('X-Email-Log-Id', $logId);

            EmailLog::create([
                'log_uuid' => $logId,
                'user_id' => User::where('email', $to->getAddress())->value('id'),
                'to_email' => $to->getAddress(),
                'subject' => (string) $event->message->getSubject(),
                'html_body' => $event->message->getHtmlBody(),
                'text_body' => $event->message->getTextBody(),
                'status' => 'failed',
            ]);
        } catch (\Throwable $e) {
            Log::warning('LogEmailAttempt::handleSending falló (no afecta el envío real): '.$e->getMessage());
        }
    }

    public function handleSent(MessageSent $event): void
    {
        try {
            if (! Schema::hasTable('email_logs')) {
                return;
            }

            $header = $event->message->getHeaders()->get('X-Email-Log-Id');

            if (! $header) {
                return;
            }

            EmailLog::where('log_uuid', $header->getBodyAsString())->update(['status' => 'sent']);
        } catch (\Throwable $e) {
            Log::warning('LogEmailAttempt::handleSent falló (no afecta el envío real): '.$e->getMessage());
        }
    }
}
