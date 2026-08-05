<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Agrega la plantilla de correo para "olvidé mi contraseña" (antes usaba el
 * ResetPassword genérico de Laravel, en inglés y sin el diseño de las demás
 * plantillas — ver AppServiceProvider::boot(), ResetPassword::toMailUsing()).
 * Mismo diseño (header/footer con logo) que las 4 plantillas de
 * 2026_08_05_110740_update_email_templates_professional_design.php.
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

        $eyebrow = fn (string $color, string $label) => <<<HTML
                  <p style="margin:0 0 4px;font-size:13px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:{$color};">{$label}</p>
        HTML;

        $button = fn (string $url, string $label) => <<<HTML
                  <p style="text-align:center;margin:32px 0;">
                    <a href="{$url}" style="background:#2aa890;color:#ffffff;text-decoration:none;padding:14px 36px;border-radius:8px;font-weight:700;font-size:15px;display:inline-block;">{$label}</a>
                  </p>
        HTML;

        $htmlBody = $wrap(implode("\n", [
            $eyebrow('#2aa890', '🔑 Restablecer contraseña'),
            "      <h1 style=\"margin:4px 0 20px;font-size:22px;color:#0f1720;\">Hola, {{user_name}}</h1>",
            '      <p style="margin:0 0 24px;">Recibimos una solicitud para restablecer la contraseña de tu cuenta en 4LivePro Latino. Haz clic en el siguiente botón para elegir una nueva contraseña:</p>',
            $button('{{reset_url}}', 'Restablecer contraseña'),
            '      <p style="margin:0 0 8px;font-size:13px;color:#6b7280;">Este enlace expira en {{expire_minutes}} minutos.</p>',
            '      <p style="margin:0;font-size:13px;color:#6b7280;">Si no solicitaste este cambio, puedes ignorar este mensaje — tu contraseña actual seguirá funcionando.</p>',
        ]));

        $textBody =
            "🔑 RESTABLECER CONTRASEÑA\n\n".
            "Hola {{user_name}},\n\n".
            "Recibimos una solicitud para restablecer la contraseña de tu cuenta en 4LivePro Latino.\n\n".
            "Restablecer contraseña: {{reset_url}}\n\n".
            "Este enlace expira en {{expire_minutes}} minutos.\n\n".
            "Si no solicitaste este cambio, puedes ignorar este mensaje — tu contraseña actual seguirá funcionando.";

        DB::table('email_templates')->updateOrInsert(
            ['key' => 'password_reset'],
            [
                'name' => 'Restablecer contraseña',
                'subject' => 'Restablece tu contraseña',
                'html_body' => $htmlBody,
                'text_body' => $textBody,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('email_templates')->where('key', 'password_reset')->delete();
    }
};
