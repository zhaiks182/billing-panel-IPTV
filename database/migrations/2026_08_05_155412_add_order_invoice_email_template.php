<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Agrega la plantilla de "factura pendiente de pago", enviada apenas el cliente sube su
 * comprobante y crea el pedido (antes no se enviaba ningún correo en ese momento — solo
 * al aprobar/rechazar). A pedido del usuario, 2026-08-05, inspirada en el formato de
 * factura de otro panel de referencia (número de factura, facturado a, detalle, total,
 * estado "pendiente de pago"), adaptada a nuestro flujo (pago manual con comprobante, no
 * pasarela en línea) y al mismo diseño que las demás plantillas.
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
                    4LivePro Latino<br>
                    Este es un correo automático, por favor no respondas a este mensaje.
                  </p>
                </div>
              </div>
            </div>
            HTML;
        };

        $htmlBody = $wrap(implode("\n", [
            '      <p style="margin:0 0 4px;font-size:13px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:#d97706;">🧾 Factura</p>',
            "      <h1 style=\"margin:4px 0 12px;font-size:22px;color:#0f1720;\">Pedido #{{order_id}}</h1>",
            '      <p style="margin:0 0 20px;"><span style="background:#fef3c7;color:#92400e;font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;padding:4px 10px;border-radius:9999px;display:inline-block;">Pendiente de pago</span></p>',
            '      <p style="margin:0 0 24px;">Hola {{user_name}}, recibimos tu pedido y el comprobante de pago que subiste. Está en revisión — en cuanto lo confirmemos, activaremos tu línea y te avisaremos por correo.</p>',
            '      <div style="background:#f3f4f6;border:1px solid #e5e7eb;border-radius:10px;padding:16px 20px;margin:0 0 20px;font-size:13px;">',
            '        <p style="margin:0 0 4px;color:#6b7280;text-transform:uppercase;font-size:11px;letter-spacing:.05em;">Facturado a</p>',
            '        <p style="margin:0;color:#0f1720;font-weight:600;">{{user_name}}</p>',
            '        <p style="margin:0;color:#374151;">{{billing_address}}</p>',
            '      </div>',
            '      <table style="width:100%;border-collapse:collapse;margin:0 0 4px;font-size:14px;">',
            '        <tr>',
            '          <td style="padding:10px 0;border-bottom:2px solid #0f1720;color:#6b7280;font-size:11px;text-transform:uppercase;letter-spacing:.05em;">Descripción</td>',
            '          <td style="padding:10px 0;border-bottom:2px solid #0f1720;color:#6b7280;font-size:11px;text-transform:uppercase;letter-spacing:.05em;text-align:right;">Importe</td>',
            '        </tr>',
            '        <tr>',
            '          <td style="padding:12px 0;border-bottom:1px solid #e5e7eb;color:#0f1720;">',
            '            <strong>{{package_name}}</strong><br>',
            '            <span style="color:#6b7280;font-size:12px;">Método de pago: {{payment_method_name}}</span>',
            '          </td>',
            '          <td style="padding:12px 0;border-bottom:1px solid #e5e7eb;color:#0f1720;text-align:right;vertical-align:top;">{{amount}}</td>',
            '        </tr>',
            '      </table>',
            '      <table style="width:100%;border-collapse:collapse;margin:0 0 24px;font-size:14px;">',
            '        <tr>',
            '          <td style="padding:6px 0;color:#6b7280;">Fecha de emisión</td>',
            '          <td style="padding:6px 0;text-align:right;color:#0f1720;">{{issued_date}}</td>',
            '        </tr>',
            '        <tr>',
            '          <td style="padding:10px 0;font-weight:700;color:#0f1720;border-top:2px solid #0f1720;">Total</td>',
            '          <td style="padding:10px 0;text-align:right;font-weight:700;color:#0f1720;border-top:2px solid #0f1720;">{{amount}}</td>',
            '        </tr>',
            '      </table>',
            '      <p style="text-align:center;margin:28px 0;">',
            '        <a href="{{orders_url}}" style="background:#2aa890;color:#ffffff;text-decoration:none;padding:14px 36px;border-radius:8px;font-weight:700;font-size:15px;display:inline-block;">Ver mi pedido</a>',
            '      </p>',
        ]));

        $textBody =
            "🧾 FACTURA — PENDIENTE DE PAGO\n\n".
            "Pedido #{{order_id}}\n\n".
            "Hola {{user_name}}, recibimos tu pedido y el comprobante de pago que subiste. Está en revisión — en cuanto lo confirmemos, activaremos tu línea y te avisaremos por correo.\n\n".
            "Facturado a:\n".
            "{{user_name}}\n".
            "{{billing_address_text}}\n\n".
            "Descripción: {{package_name}}\n".
            "Método de pago: {{payment_method_name}}\n".
            "Importe: {{amount}}\n".
            "Fecha de emisión: {{issued_date}}\n".
            "Total: {{amount}}\n\n".
            "Ver mi pedido: {{orders_url}}";

        DB::table('email_templates')->updateOrInsert(
            ['key' => 'order_invoice'],
            [
                'name' => 'Factura de pedido (pendiente de pago)',
                'subject' => 'Factura #{{order_id}} - Pendiente de pago',
                'html_body' => $htmlBody,
                'text_body' => $textBody,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('email_templates')->where('key', 'order_invoice')->delete();
    }
};
