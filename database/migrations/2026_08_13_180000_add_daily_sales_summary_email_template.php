<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Plantilla del resumen diario de ventas por correo (a pedido del usuario: "también
 * agregarlo para el correo, para que se envíe a soporte@4livepro.com") — mismo contenido
 * que ya se manda por Telegram todos los días a las 10pm (App\Services\Telegram\SalesReportBuilder),
 * mismo patrón $wrap()/heredoc que el resto de plantillas internas del admin
 * (2026_08_06_143942_add_ticket_admin_alert_email_templates.php).
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

        $statRow = fn (string $label, string $value) => '        <tr>'
            .'<td style="padding:10px 16px;font-size:13px;color:#6b7280;border-bottom:1px solid #e5e7eb;">'.$label.'</td>'
            .'<td style="padding:10px 16px;font-size:14px;font-weight:700;color:#0f1720;text-align:right;border-bottom:1px solid #e5e7eb;">'.$value.'</td>'
            .'</tr>';

        DB::table('email_templates')->updateOrInsert(
            ['key' => 'daily_sales_summary'],
            [
                'name' => 'Aviso interno: resumen diario de ventas',
                'subject' => '📊 Resumen de ventas — {{date}}',
                'html_body' => $wrap(implode("\n", [
                    '      <p style="margin:0 0 4px;font-size:13px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:#2aa890;">📊 Resumen diario</p>',
                    "      <h1 style=\"margin:4px 0 20px;font-size:22px;color:#0f1720;\">Ventas de hoy ({{date}})</h1>",
                    '      <table style="width:100%;border-collapse:collapse;background:#f9fafb;border-radius:8px;overflow:hidden;">',
                    $statRow('Pedidos pagados aprobados', '{{paid_orders_count}}'),
                    $statRow('Ingresos', '{{revenue}}'),
                    $statRow('Demos activadas', '{{trial_orders_count}}'),
                    $statRow('Total pedidos aprobados', '{{total_orders_count}}'),
                    '      </table>',
                ])),
                'text_body' =>
                    "📊 RESUMEN DE VENTAS — {{date}}\n\n".
                    "Pedidos pagados aprobados: {{paid_orders_count}}\n".
                    "Ingresos: {{revenue}}\n".
                    "Demos activadas: {{trial_orders_count}}\n\n".
                    'Total pedidos aprobados: {{total_orders_count}}',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('email_templates')->where('key', 'daily_sales_summary')->delete();
    }
};
