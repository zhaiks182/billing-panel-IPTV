<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Reemplaza el diseño básico inicial de las 4 plantillas por defecto con uno más
 * elaborado (header/footer oscuros con logo, tarjeta de credenciales, botones con
 * más presencia). Es una migración de datos, no de esquema: actualiza filas que ya
 * existen (creadas en 2026_08_05_103226_create_email_templates_table.php).
 */
return new class extends Migration
{
    public function up(): void
    {
        $logoUrl = asset('images/logo.png');

        $wrap = function (string $content) use ($logoUrl): string {
            return <<<HTML
            <div style="background:#eef1f5;padding:40px 16px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
              <div style="max-width:560px;margin:0 auto;">
                <div style="background:#0f1720;padding:28px 24px;text-align:center;border-radius:12px 12px 0 0;">
                  <img src="{$logoUrl}" alt="4LivePro Latino" width="180" style="display:block;margin:0 auto;max-width:180px;height:auto;">
                </div>
                <div style="background:#ffffff;padding:36px 32px;color:#1f2937;font-size:15px;line-height:1.7;">
            {$content}
                </div>
                <div style="background:#0f1720;padding:24px 32px;border-radius:0 0 12px 12px;text-align:center;">
                  <p style="margin:0;color:#8da0b3;font-size:12px;line-height:1.6;">
                    4LivePro Latino — IPTV Premium<br>
                    Este es un correo automático, por favor no respondas a este mensaje.
                  </p>
                </div>
              </div>
            </div>
            HTML;
        };

        $eyebrow = fn (string $color, string $label) => <<<HTML
                  <p style="margin:0 0 4px;font-size:13px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:{$color};">{$label}</p>
        HTML;

        $button = fn (string $url, string $label) => <<<HTML
                  <p style="text-align:center;margin:32px 0;">
                    <a href="{$url}" style="background:#2aa890;color:#ffffff;text-decoration:none;padding:14px 36px;border-radius:8px;font-weight:700;font-size:15px;display:inline-block;">{$label}</a>
                  </p>
        HTML;

        DB::table('email_templates')->where('key', 'verify_email')->update([
            'html_body' => $wrap(implode("\n", [
                $eyebrow('#2aa890', '✉️ Verifica tu correo'),
                "      <h1 style=\"margin:4px 0 20px;font-size:22px;color:#0f1720;\">¡Hola, {{user_name}}!</h1>",
                '      <p style="margin:0 0 24px;">Gracias por registrarte en 4LivePro Latino. Confirma tu correo electrónico para activar tu cuenta y empezar a disfrutar del servicio.</p>',
                $button('{{verification_url}}', 'Verificar mi correo'),
                '      <p style="margin:24px 0 0;font-size:13px;color:#6b7280;">Si no creaste esta cuenta, puedes ignorar este mensaje con tranquilidad.</p>',
            ])),
            'text_body' =>
                "✉️ VERIFICA TU CORREO\n\n".
                "Hola {{user_name}},\n\n".
                "Gracias por registrarte en 4LivePro Latino. Confirma tu correo entrando a este enlace:\n{{verification_url}}\n\n".
                "Si no creaste esta cuenta, puedes ignorar este mensaje.",
            'updated_at' => now(),
        ]);

        DB::table('email_templates')->where('key', 'order_approved')->update([
            'html_body' => $wrap(implode("\n", [
                $eyebrow('#2aa890', '✅ Línea activada'),
                "      <h1 style=\"margin:4px 0 20px;font-size:22px;color:#0f1720;\">¡Tu servicio ya está listo, {{user_name}}!</h1>",
                '      <p style="margin:0 0 24px;">Tu pago del pedido <strong>#{{order_id}}</strong> ({{package_name}}) fue aprobado y tu línea IPTV ya está activa. Estos son tus datos de acceso:</p>',
                '      <div style="background:#f3f4f6;border:1px solid #e5e7eb;border-radius:10px;padding:20px 24px;margin:0 0 24px;">',
                '        <table style="width:100%;border-collapse:collapse;font-size:14px;">',
                '          <tr><td style="padding:8px 0;color:#6b7280;width:120px;">Usuario</td><td style="padding:8px 0;font-family:\'Courier New\',monospace;font-weight:700;color:#0f1720;">{{xui_username}}</td></tr>',
                '          <tr><td style="padding:8px 0;color:#6b7280;">Contraseña</td><td style="padding:8px 0;font-family:\'Courier New\',monospace;font-weight:700;color:#0f1720;">{{xui_password}}</td></tr>',
                '          <tr><td style="padding:8px 0;color:#6b7280;">Lista M3U</td><td style="padding:8px 0;font-size:12px;word-break:break-all;color:#374151;">{{m3u_url}}</td></tr>',
                '          <tr><td style="padding:8px 0;color:#6b7280;">Vence</td><td style="padding:8px 0;color:#374151;">{{line_expires_at}}</td></tr>',
                '        </table>',
                '      </div>',
                $button('{{dashboard_url}}', 'Ver mi panel'),
            ])),
            'text_body' =>
                "✅ LÍNEA ACTIVADA\n\n".
                "¡Tu servicio ya está listo, {{user_name}}!\n\n".
                "Tu pago del pedido #{{order_id}} ({{package_name}}) fue aprobado y tu línea IPTV ya está activa.\n\n".
                "Usuario: {{xui_username}}\n".
                "Contraseña: {{xui_password}}\n".
                "Lista M3U: {{m3u_url}}\n".
                "Vence: {{line_expires_at}}\n\n".
                "Ver mi panel: {{dashboard_url}}",
            'updated_at' => now(),
        ]);

        DB::table('email_templates')->where('key', 'order_rejected')->update([
            'html_body' => $wrap(implode("\n", [
                $eyebrow('#dc2626', '⚠️ Pedido rechazado'),
                "      <h1 style=\"margin:4px 0 20px;font-size:22px;color:#0f1720;\">Hola, {{user_name}}</h1>",
                '      <p style="margin:0 0 16px;">No pudimos validar el comprobante de pago de tu pedido <strong>#{{order_id}}</strong>.</p>',
                '      <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:16px 20px;margin:0 0 24px;">',
                '        <p style="margin:0;font-size:14px;color:#991b1b;"><strong>Motivo:</strong> {{admin_note}}</p>',
                '      </div>',
                '      <p style="margin:0 0 24px;">Si crees que se trata de un error, contáctanos o sube un nuevo comprobante desde tu panel.</p>',
                $button('{{orders_url}}', 'Ver mis pedidos'),
            ])),
            'text_body' =>
                "⚠️ PEDIDO RECHAZADO\n\n".
                "Hola {{user_name}},\n\n".
                "No pudimos validar el comprobante de pago de tu pedido #{{order_id}}.\n\n".
                "Motivo: {{admin_note}}\n\n".
                "Si crees que es un error, contáctanos o sube un nuevo comprobante.\n\n".
                "Ver mis pedidos: {{orders_url}}",
            'updated_at' => now(),
        ]);

        DB::table('email_templates')->where('key', 'line_expiring_soon')->update([
            'html_body' => $wrap(implode("\n", [
                $eyebrow('#d97706', '⏰ Vencimiento próximo'),
                "      <h1 style=\"margin:4px 0 20px;font-size:22px;color:#0f1720;\">Tu línea vence pronto, {{user_name}}</h1>",
                '      <p style="margin:0 0 24px;">Tu línea IPTV ({{package_name}}) vence <strong>{{days_label}}</strong> ({{line_expires_at}}). Renueva ahora para que tu servicio no se interrumpa.</p>',
                $button('{{renew_url}}', 'Renovar mi línea'),
                '      <p style="margin:24px 0 0;font-size:13px;color:#6b7280;">¿Dudas? Escríbenos por WhatsApp: <a href="https://wa.me/593984564703" style="color:#2aa890;text-decoration:none;">+593 984564703</a></p>',
            ])),
            'text_body' =>
                "⏰ VENCIMIENTO PRÓXIMO\n\n".
                "Hola {{user_name}},\n\n".
                "Tu línea IPTV ({{package_name}}) vence {{days_label}} ({{line_expires_at}}).\n\n".
                "Renueva ahora para que tu servicio no se interrumpa.\n\n".
                "Renovar mi línea: {{renew_url}}\n\n".
                "¿Dudas? Escríbenos por WhatsApp: +593 984564703",
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Migración de datos (mejora de diseño) — no tiene un "down" significativo,
        // revertirla no restaura el diseño anterior automáticamente.
    }
};
