<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('subject');
            $table->longText('html_body');
            $table->longText('text_body');
            $table->timestamps();
        });

        $wrapHtml = function (string $content): string {
            return <<<HTML
            <div style="background:#f3f4f6;padding:32px 16px;font-family:Arial,Helvetica,sans-serif;">
              <div style="max-width:520px;margin:0 auto;background:#ffffff;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;">
                <div style="background:#2aa890;padding:20px 24px;">
                  <span style="color:#ffffff;font-size:18px;font-weight:600;">4LivePro Latino</span>
                </div>
                <div style="padding:24px;color:#1f2937;font-size:15px;line-height:1.6;">
            {$content}
                </div>
                <div style="padding:16px 24px;color:#9ca3af;font-size:12px;border-top:1px solid #e5e7eb;">
                  4LivePro Latino — este es un correo automático, no respondas a este mensaje.
                </div>
              </div>
            </div>
            HTML;
        };

        $button = fn (string $url, string $label) => <<<HTML
                  <p style="text-align:center;margin:28px 0;">
                    <a href="{$url}" style="background:#2aa890;color:#ffffff;text-decoration:none;padding:12px 24px;border-radius:6px;font-weight:600;display:inline-block;">{$label}</a>
                  </p>
        HTML;

        $now = now();

        DB::table('email_templates')->insert([
            [
                'key' => 'verify_email',
                'name' => 'Verificación de correo',
                'subject' => 'Verifica tu correo electrónico',
                'html_body' => $wrapHtml(
                    "      <p>Hola {{user_name}},</p>\n".
                    "      <p>Gracias por registrarte. Por favor confirma tu correo electrónico haciendo clic en el siguiente botón:</p>\n".
                    $button('{{verification_url}}', 'Verificar correo electrónico').
                    "      <p>Si no creaste una cuenta, puedes ignorar este mensaje.</p>"
                ),
                'text_body' =>
                    "Hola {{user_name}},\n\n".
                    "Gracias por registrarte. Confirma tu correo entrando a este enlace:\n{{verification_url}}\n\n".
                    "Si no creaste una cuenta, puedes ignorar este mensaje.",
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'order_approved',
                'name' => 'Pedido aprobado / línea activada',
                'subject' => 'Tu línea IPTV está activa - Pedido #{{order_id}}',
                'html_body' => $wrapHtml(
                    "      <p>Hola {{user_name}},</p>\n".
                    "      <p>Tu pago del pedido #{{order_id}} ({{package_name}}) fue aprobado y tu línea IPTV ya está activa.</p>\n".
                    "      <table style=\"width:100%;border-collapse:collapse;margin:20px 0;\">\n".
                    "        <tr><td style=\"padding:6px 0;color:#6b7280;\">Usuario:</td><td style=\"padding:6px 0;font-weight:600;\">{{xui_username}}</td></tr>\n".
                    "        <tr><td style=\"padding:6px 0;color:#6b7280;\">Contraseña:</td><td style=\"padding:6px 0;font-weight:600;\">{{xui_password}}</td></tr>\n".
                    "        <tr><td style=\"padding:6px 0;color:#6b7280;\">Lista M3U:</td><td style=\"padding:6px 0;word-break:break-all;\">{{m3u_url}}</td></tr>\n".
                    "        <tr><td style=\"padding:6px 0;color:#6b7280;\">Vence:</td><td style=\"padding:6px 0;\">{{line_expires_at}}</td></tr>\n".
                    "      </table>\n".
                    $button('{{dashboard_url}}', 'Ver mi panel')
                ),
                'text_body' =>
                    "Hola {{user_name}},\n\n".
                    "Tu pago del pedido #{{order_id}} ({{package_name}}) fue aprobado y tu línea IPTV ya está activa.\n\n".
                    "Usuario: {{xui_username}}\n".
                    "Contraseña: {{xui_password}}\n".
                    "Lista M3U: {{m3u_url}}\n".
                    "Vence: {{line_expires_at}}\n\n".
                    "Ver mi panel: {{dashboard_url}}",
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'order_rejected',
                'name' => 'Pedido rechazado',
                'subject' => 'Tu pedido #{{order_id}} fue rechazado',
                'html_body' => $wrapHtml(
                    "      <p>Hola {{user_name}},</p>\n".
                    "      <p>No pudimos validar el comprobante de pago de tu pedido #{{order_id}}.</p>\n".
                    "      <p><strong>Motivo:</strong> {{admin_note}}</p>\n".
                    "      <p>Si crees que es un error, contáctanos o sube un nuevo comprobante.</p>\n".
                    $button('{{orders_url}}', 'Ver mis pedidos')
                ),
                'text_body' =>
                    "Hola {{user_name}},\n\n".
                    "No pudimos validar el comprobante de pago de tu pedido #{{order_id}}.\n\n".
                    "Motivo: {{admin_note}}\n\n".
                    "Si crees que es un error, contáctanos o sube un nuevo comprobante.\n\n".
                    "Ver mis pedidos: {{orders_url}}",
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'line_expiring_soon',
                'name' => 'Recordatorio de vencimiento',
                'subject' => 'Tu línea IPTV vence pronto',
                'html_body' => $wrapHtml(
                    "      <p>Hola {{user_name}},</p>\n".
                    "      <p>Tu línea IPTV ({{package_name}}) vence {{days_label}} ({{line_expires_at}}).</p>\n".
                    "      <p>Renueva ahora para que tu servicio no se interrumpa.</p>\n".
                    $button('{{renew_url}}', 'Renovar mi línea').
                    "      <p>¿Dudas? Escríbenos por WhatsApp: +593 984564703</p>"
                ),
                'text_body' =>
                    "Hola {{user_name}},\n\n".
                    "Tu línea IPTV ({{package_name}}) vence {{days_label}} ({{line_expires_at}}).\n\n".
                    "Renueva ahora para que tu servicio no se interrumpa.\n\n".
                    "Renovar mi línea: {{renew_url}}\n\n".
                    "¿Dudas? Escríbenos por WhatsApp: +593 984564703",
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
