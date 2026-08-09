<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Aviso al cliente cuando su línea YA venció (no antes, como line_expiring_soon) — a pedido
 * del usuario, 2026-08-09: se encontró un caso real (línea #38, Roberto Ríos) que venció sin
 * que nadie se enterara porque el recordatorio existente solo avisa antes del vencimiento.
 * Mismo patrón $wrap()/heredoc que add_ticket_email_templates.
 */
return new class extends Migration
{
    public function up(): void
    {
        $logoUrl = asset('images/logo.png');

        $html = <<<HTML
        <div style="background:#eef1f5;padding:40px 16px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
          <div style="max-width:560px;margin:0 auto;">
            <div style="background:#0f1720;padding:28px 24px;text-align:center;border-radius:12px 12px 0 0;">
              <img src="{$logoUrl}" alt="4LivePro Latino" width="180" style="display:block;margin:0 auto;max-width:180px;height:auto;">
            </div>
            <div style="background:#ffffff;padding:36px 32px;color:#1f2937;font-size:15px;line-height:1.7;">
              <p style="margin:0 0 4px;font-size:13px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:#dc2626;">⛔ Línea vencida</p>
              <h1 style="margin:4px 0 20px;font-size:22px;color:#0f1720;">Tu línea venció, {{user_name}}</h1>
              <p style="margin:0 0 24px;">Tu línea ({{package_name}}) venció el <strong>{{line_expired_at}}</strong> y tu servicio se interrumpió. Renueva ahora para reactivarla.</p>
              <p style="text-align:center;margin:32px 0;">
                <a href="{{renew_url}}" style="background:#2aa890;color:#ffffff;text-decoration:none;padding:14px 36px;border-radius:8px;font-weight:700;font-size:15px;display:inline-block;">Renovar mi línea</a>
              </p>
              <p style="margin:24px 0 0;font-size:13px;color:#6b7280;">¿Dudas? Escríbenos por WhatsApp: <a href="https://wa.me/593984564703" style="color:#2aa890;text-decoration:none;">+593 984564703</a></p>
            </div>
            <div style="background:#0f1720;padding:24px 32px;border-radius:0 0 12px 12px;text-align:center;">
              <p style="margin:0;color:#8da0b3;font-size:12px;line-height:1.6;">
                4LivePro Latino<br>
                Este es un correo automático, por favor no respondas a este mensaje.
              </p>
            </div>
          </div>
        </div>
        HTML;

        $text = "⛔ LÍNEA VENCIDA\n".
            "Hola {{user_name}},\n".
            "Tu línea ({{package_name}}) venció el {{line_expired_at}} y tu servicio se interrumpió.\n".
            "Renueva ahora para reactivarla.\n".
            "Renovar mi línea: {{renew_url}}\n".
            '¿Dudas? Escríbenos por WhatsApp: +593 984564703';

        DB::table('email_templates')->updateOrInsert(
            ['key' => 'line_expired'],
            [
                'name' => 'Línea vencida',
                'subject' => 'Tu línea ha vencido',
                'html_body' => $html,
                'text_body' => $text,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('email_templates')->where('key', 'line_expired')->delete();
    }
};
