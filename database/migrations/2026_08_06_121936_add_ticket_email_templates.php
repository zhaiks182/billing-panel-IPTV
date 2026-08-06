<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Agrega las 3 plantillas de correo del módulo de tickets de soporte (2026-08-06), mismo
 * patrón $wrap()/heredoc que 2026_08_05_155412_add_order_invoice_email_template.php.
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

        $button = fn (string $label, string $url) => '      <p style="text-align:center;margin:28px 0;">'
            ."\n".'        <a href="'.$url.'" style="background:#2aa890;color:#ffffff;text-decoration:none;padding:14px 36px;border-radius:8px;font-weight:700;font-size:15px;display:inline-block;">'.$label.'</a>'
            ."\n".'      </p>';

        DB::table('email_templates')->updateOrInsert(
            ['key' => 'ticket_created'],
            [
                'name' => 'Ticket de soporte creado',
                'subject' => 'Ticket #{{ticket_id}} recibido - {{subject}}',
                'html_body' => $wrap(implode("\n", [
                    '      <p style="margin:0 0 4px;font-size:13px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:#2aa890;">🎫 Ticket de soporte</p>',
                    "      <h1 style=\"margin:4px 0 12px;font-size:22px;color:#0f1720;\">Ticket #{{ticket_id}}</h1>",
                    '      <p style="margin:0 0 20px;"><span style="background:#e0f2f1;color:#0f766e;font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;padding:4px 10px;border-radius:9999px;display:inline-block;margin-right:6px;">{{category_label}}</span><span style="background:#f3f4f6;color:#374151;font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;padding:4px 10px;border-radius:9999px;display:inline-block;">Prioridad {{priority_label}}</span></p>',
                    '      <p style="margin:0 0 20px;">Hola {{user_name}}, recibimos tu ticket «{{subject}}». Nuestro equipo lo va a revisar y te vamos a responder por aquí.</p>',
                    '      <div style="background:#f3f4f6;border:1px solid #e5e7eb;border-radius:10px;padding:16px 20px;margin:0 0 20px;font-size:14px;color:#374151;white-space:pre-line;">{{message}}</div>',
                    $button('Ver mi ticket', '{{ticket_url}}'),
                ])),
                'text_body' =>
                    "🎫 TICKET DE SOPORTE — {{category_label}} / Prioridad {{priority_label}}\n\n".
                    "Ticket #{{ticket_id}}: {{subject}}\n\n".
                    "Hola {{user_name}}, recibimos tu ticket. Nuestro equipo lo va a revisar y te vamos a responder por aquí.\n\n".
                    "Tu mensaje:\n{{message}}\n\n".
                    'Ver mi ticket: {{ticket_url}}',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('email_templates')->updateOrInsert(
            ['key' => 'ticket_reply'],
            [
                'name' => 'Nueva respuesta en ticket',
                'subject' => 'Nueva respuesta en tu ticket #{{ticket_id}} - {{subject}}',
                'html_body' => $wrap(implode("\n", [
                    '      <p style="margin:0 0 4px;font-size:13px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:#2aa890;">💬 Nueva respuesta</p>',
                    "      <h1 style=\"margin:4px 0 20px;font-size:22px;color:#0f1720;\">Ticket #{{ticket_id}}: {{subject}}</h1>",
                    '      <p style="margin:0 0 20px;">Hola {{user_name}}, tienes una respuesta nueva en tu ticket de soporte:</p>',
                    '      <div style="background:#f3f4f6;border:1px solid #e5e7eb;border-radius:10px;padding:16px 20px;margin:0 0 20px;font-size:14px;color:#374151;white-space:pre-line;">{{reply_message}}</div>',
                    $button('Ver la conversación completa', '{{ticket_url}}'),
                ])),
                'text_body' =>
                    "💬 NUEVA RESPUESTA\n\n".
                    "Ticket #{{ticket_id}}: {{subject}}\n\n".
                    "Hola {{user_name}}, tienes una respuesta nueva en tu ticket de soporte:\n\n".
                    "{{reply_message}}\n\n".
                    'Ver la conversación completa: {{ticket_url}}',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('email_templates')->updateOrInsert(
            ['key' => 'ticket_closed'],
            [
                'name' => 'Ticket cerrado',
                'subject' => 'Ticket #{{ticket_id}} resuelto - {{subject}}',
                'html_body' => $wrap(implode("\n", [
                    '      <p style="margin:0 0 4px;font-size:13px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:#2aa890;">✅ Ticket cerrado</p>',
                    "      <h1 style=\"margin:4px 0 20px;font-size:22px;color:#0f1720;\">Ticket #{{ticket_id}} resuelto</h1>",
                    '      <p style="margin:0 0 20px;">Hola {{user_name}}, tu ticket «{{subject}}» fue marcado como resuelto. Este fue el resumen de la solución aplicada:</p>',
                    '      <div style="background:#f3f4f6;border:1px solid #e5e7eb;border-radius:10px;padding:16px 20px;margin:0 0 20px;font-size:14px;color:#374151;white-space:pre-line;">{{resolution}}</div>',
                    '      <p style="margin:0 0 20px;color:#6b7280;font-size:13px;">Si el problema persiste, puedes responder este ticket para reabrirlo.</p>',
                    $button('Ver el ticket', '{{ticket_url}}'),
                ])),
                'text_body' =>
                    "✅ TICKET CERRADO\n\n".
                    "Ticket #{{ticket_id}} resuelto: {{subject}}\n\n".
                    "Hola {{user_name}}, tu ticket fue marcado como resuelto. Este fue el resumen de la solución aplicada:\n\n".
                    "{{resolution}}\n\n".
                    "Si el problema persiste, puedes responder este ticket para reabrirlo.\n\n".
                    'Ver el ticket: {{ticket_url}}',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('email_templates')->whereIn('key', ['ticket_created', 'ticket_reply', 'ticket_closed'])->delete();
    }
};
