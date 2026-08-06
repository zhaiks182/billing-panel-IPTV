<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Plantillas de correo para los avisos internos al admin (Telegram tiene su equivalente en
 * App\Jobs\SendNewTicketAdminAlert/SendTicketReplyAdminAlert). Antes estos correos se
 * mandaban con Mail::raw() (texto plano) — el usuario pidió el mismo diseño de marca que
 * ya usa el correo del cliente (`ticket_created`/`ticket_reply`). Mismo patrón
 * $wrap()/heredoc que 2026_08_06_121936_add_ticket_email_templates.php.
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
            ['key' => 'ticket_admin_new'],
            [
                'name' => 'Aviso interno: ticket nuevo',
                'subject' => '🎫 Nuevo ticket #{{ticket_id}} - {{subject}}',
                'html_body' => $wrap(implode("\n", [
                    '      <p style="margin:0 0 4px;font-size:13px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:#2aa890;">🎫 Ticket de soporte</p>',
                    "      <h1 style=\"margin:4px 0 12px;font-size:22px;color:#0f1720;\">Nuevo ticket #{{ticket_id}}</h1>",
                    '      <p style="margin:0 0 20px;"><span style="background:#e0f2f1;color:#0f766e;font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;padding:4px 10px;border-radius:9999px;display:inline-block;margin-right:6px;">{{category_label}}</span><span style="background:#f3f4f6;color:#374151;font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;padding:4px 10px;border-radius:9999px;display:inline-block;">Prioridad {{priority_label}}</span></p>',
                    '      <p style="margin:0 0 20px;">Nuevo ticket de <strong>{{customer_name}}</strong> ({{customer_email}}):</p>',
                    '      <div style="background:#f3f4f6;border:1px solid #e5e7eb;border-radius:10px;padding:16px 20px;margin:0 0 20px;font-size:14px;color:#374151;white-space:pre-line;">{{message}}</div>',
                    $button('Ver ticket', '{{ticket_url}}'),
                ])),
                'text_body' =>
                    "🎫 NUEVO TICKET — {{category_label}} / Prioridad {{priority_label}}\n\n".
                    "Ticket #{{ticket_id}}: {{subject}}\n\n".
                    "Cliente: {{customer_name}} ({{customer_email}})\n\n".
                    "Mensaje:\n{{message}}\n\n".
                    'Ver ticket: {{ticket_url}}',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('email_templates')->updateOrInsert(
            ['key' => 'ticket_admin_reply'],
            [
                'name' => 'Aviso interno: respuesta de cliente',
                'subject' => '💬 Nueva respuesta en ticket #{{ticket_id}} - {{subject}}',
                'html_body' => $wrap(implode("\n", [
                    '      <p style="margin:0 0 4px;font-size:13px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:#2aa890;">💬 Nueva respuesta</p>',
                    "      <h1 style=\"margin:4px 0 20px;font-size:22px;color:#0f1720;\">Ticket #{{ticket_id}}: {{subject}}</h1>",
                    '      <p style="margin:0 0 20px;"><strong>{{customer_name}}</strong> respondió este ticket:</p>',
                    '      <div style="background:#f3f4f6;border:1px solid #e5e7eb;border-radius:10px;padding:16px 20px;margin:0 0 20px;font-size:14px;color:#374151;white-space:pre-line;">{{message}}</div>',
                    $button('Ver ticket', '{{ticket_url}}'),
                ])),
                'text_body' =>
                    "💬 NUEVA RESPUESTA\n\n".
                    "Ticket #{{ticket_id}}: {{subject}}\n\n".
                    "{{customer_name}} respondió:\n\n".
                    "{{message}}\n\n".
                    'Ver ticket: {{ticket_url}}',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('email_templates')->whereIn('key', ['ticket_admin_new', 'ticket_admin_reply'])->delete();
    }
};
