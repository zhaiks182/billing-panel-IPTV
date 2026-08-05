<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;

#[Fillable(['key', 'name', 'subject', 'html_body', 'text_body'])]
class EmailTemplate extends Model
{
    public function getRouteKeyName(): string
    {
        return 'key';
    }

    /**
     * Variables disponibles por plantilla, para mostrarlas en el editor del admin.
     * Deben coincidir con lo que cada notificación pasa a EmailTemplate::mail().
     */
    public static function variableCatalog(): array
    {
        return [
            'verify_email' => [
                'user_name' => 'Nombre del usuario',
                'verification_url' => 'Enlace para verificar el correo',
            ],
            'order_approved' => [
                'user_name' => 'Nombre del usuario',
                'order_id' => 'Número de pedido',
                'package_name' => 'Nombre del paquete',
                'xui_username' => 'Usuario de la línea (XUI ONE)',
                'xui_password' => 'Contraseña de la línea (XUI ONE)',
                'm3u_url' => 'URL de la lista M3U',
                'line_expires_at' => 'Fecha de vencimiento de la línea',
                'dashboard_url' => 'Enlace al panel del cliente',
            ],
            'order_rejected' => [
                'user_name' => 'Nombre del usuario',
                'order_id' => 'Número de pedido',
                'admin_note' => 'Motivo del rechazo',
                'orders_url' => 'Enlace a "mis pedidos"',
            ],
            'line_expiring_soon' => [
                'user_name' => 'Nombre del usuario',
                'package_name' => 'Nombre del paquete',
                'line_expires_at' => 'Fecha de vencimiento',
                'days_label' => 'Texto de días restantes (ej. "mañana" o "en 3 días")',
                'renew_url' => 'Enlace para renovar',
            ],
        ];
    }

    public function availableVariables(): array
    {
        return static::variableCatalog()[$this->key] ?? [];
    }

    /**
     * Sustituye {{variable}} (con o sin espacios) por su valor. Las variables sin
     * valor provisto se dejan tal cual, para que un typo en el template sea visible.
     */
    public static function substitute(string $text, array $variables): string
    {
        return preg_replace_callback('/\{\{\s*(\w+)\s*\}\}/', function ($match) use ($variables) {
            return array_key_exists($match[1], $variables) ? (string) $variables[$match[1]] : $match[0];
        }, $text);
    }

    /**
     * Convierte HTML a texto plano razonable (saltos de línea en <br>/<p>/<div>,
     * enlaces como "texto (url)"). Se usa como respaldo si la plantilla no tiene
     * texto plano propio, y como base para el botón "Generar texto desde HTML".
     */
    public static function htmlToText(string $html): string
    {
        $text = preg_replace('/<a\s[^>]*href="([^"]*)"[^>]*>(.*?)<\/a>/is', '$2 ($1)', $html);
        $text = preg_replace('/<(br|\/p|\/div|\/tr|\/li)\s*\/?>/i', "\n", $text);
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        $text = preg_replace('/[ \t]+\n/', "\n", $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        return trim($text);
    }

    public static function render(string $key, array $variables): array
    {
        $template = static::where('key', $key)->firstOrFail();

        return [
            'subject' => static::substitute($template->subject, $variables),
            'html' => static::substitute($template->html_body, $variables),
            'text' => static::substitute($template->text_body ?: static::htmlToText($template->html_body), $variables),
        ];
    }

    public static function mail(string $key, array $variables): MailMessage
    {
        $rendered = static::render($key, $variables);

        return (new MailMessage)
            ->subject($rendered['subject'])
            ->view(['html' => 'emails.template-html', 'text' => 'emails.template-text'], [
                'html' => $rendered['html'],
                'text' => $rendered['text'],
            ]);
    }
}
