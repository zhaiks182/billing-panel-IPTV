<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\HtmlString;

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
            'line_expired' => [
                'user_name' => 'Nombre del usuario',
                'package_name' => 'Nombre del paquete',
                'line_expired_at' => 'Fecha en que venció',
                'renew_url' => 'Enlace para renovar',
            ],
            'password_reset' => [
                'user_name' => 'Nombre del usuario',
                'reset_url' => 'Enlace para restablecer la contraseña',
                'expire_minutes' => 'Minutos antes de que expire el enlace',
            ],
            'order_invoice' => [
                'user_name' => 'Nombre del usuario',
                'order_id' => 'Número de pedido',
                'package_name' => 'Nombre del paquete',
                'amount' => 'Monto a pagar (con formato, ej. "$10.00 USD")',
                'payment_method_name' => 'Método de pago elegido (o "Prueba gratuita" en demos)',
                'status_label' => 'Etiqueta de estado ("Pendiente de pago" o "Prueba gratuita")',
                'intro_text' => 'Texto de introducción tras el saludo (varía si es pedido pagado o prueba gratuita)',
                'issued_date' => 'Fecha de emisión',
                'billing_address' => 'Dirección de facturación para el HTML (varias líneas, ya con <br>)',
                'billing_address_text' => 'Dirección de facturación para el texto plano (con saltos de línea)',
                'orders_url' => 'Enlace a "mis pedidos"',
            ],
            'ticket_created' => [
                'user_name' => 'Nombre del cliente (o invitado)',
                'ticket_id' => 'Número de ticket',
                'subject' => 'Asunto del ticket',
                'category_label' => 'Categoría (en español, ej. "Instalación")',
                'priority_label' => 'Prioridad (en español, ej. "Media")',
                'message' => 'Mensaje inicial del ticket',
                'ticket_url' => 'Enlace para ver el ticket (con token si es invitado)',
            ],
            'ticket_reply' => [
                'user_name' => 'Nombre del cliente (o invitado)',
                'ticket_id' => 'Número de ticket',
                'subject' => 'Asunto del ticket',
                'reply_message' => 'Texto de la respuesta del administrador',
                'ticket_url' => 'Enlace para ver el ticket (con token si es invitado)',
            ],
            'ticket_closed' => [
                'user_name' => 'Nombre del cliente (o invitado)',
                'ticket_id' => 'Número de ticket',
                'subject' => 'Asunto del ticket',
                'resolution' => 'Solución aplicada',
                'ticket_url' => 'Enlace para ver el ticket (con token si es invitado)',
            ],
            'ticket_admin_new' => [
                'ticket_id' => 'Número de ticket',
                'subject' => 'Asunto del ticket',
                'customer_name' => 'Nombre del cliente (o invitado)',
                'customer_email' => 'Correo del cliente (o invitado)',
                'category_label' => 'Categoría (en español, ej. "Instalación")',
                'priority_label' => 'Prioridad (en español, ej. "Media")',
                'message' => 'Mensaje inicial del ticket',
                'ticket_url' => 'Enlace al ticket en el panel de admin',
            ],
            'ticket_admin_reply' => [
                'ticket_id' => 'Número de ticket',
                'subject' => 'Asunto del ticket',
                'customer_name' => 'Nombre del cliente (o invitado)',
                'message' => 'Texto de la respuesta del cliente/invitado',
                'ticket_url' => 'Enlace al ticket en el panel de admin',
            ],
            'daily_sales_summary' => [
                'date' => 'Fecha del resumen (ej. "13/08/2026")',
                'paid_orders_count' => 'Cantidad de pedidos pagados aprobados hoy',
                'revenue' => 'Ingresos del día (con formato, ej. "$45.50 USD")',
                'trial_orders_count' => 'Cantidad de demos activadas hoy',
                'total_orders_count' => 'Total de pedidos aprobados hoy (pagados + demos)',
            ],
        ];
    }

    public function availableVariables(): array
    {
        return static::variableCatalog()[$this->key] ?? [];
    }

    /**
     * Datos de ejemplo para el botón "Enviar correo de prueba" del editor, ya que ahí no
     * existe un pedido/línea real todavía sobre el cual construir las variables.
     */
    public function sampleVariables(): array
    {
        return match ($this->key) {
            'verify_email' => [
                'user_name' => 'Juan Pérez',
                'verification_url' => url('/verify-email/ejemplo'),
            ],
            'order_approved' => [
                'user_name' => 'Juan Pérez',
                'order_id' => '1042',
                'package_name' => '1 mes - 1 pantalla',
                'xui_username' => 'usuario_demo',
                'xui_password' => 'clave_demo123',
                'm3u_url' => 'http://tu-panel.com:2082/playlist/usuario_demo/clave_demo123/m3u_plus',
                'line_expires_at' => now()->addDays(30)->format('d/m/Y'),
                'dashboard_url' => route('dashboard'),
            ],
            'order_rejected' => [
                'user_name' => 'Juan Pérez',
                'order_id' => '1042',
                'admin_note' => 'El comprobante no coincide con el monto del pedido.',
                'orders_url' => route('orders.index'),
            ],
            'line_expiring_soon' => [
                'user_name' => 'Juan Pérez',
                'package_name' => '1 mes - 1 pantalla',
                'line_expires_at' => now()->addDays(2)->format('d/m/Y H:i'),
                'days_label' => 'en 2 días',
                'renew_url' => route('home'),
            ],
            'line_expired' => [
                'user_name' => 'Juan Pérez',
                'package_name' => '1 mes - 1 pantalla',
                'line_expired_at' => now()->subHours(3)->format('d/m/Y H:i'),
                'renew_url' => route('home'),
            ],
            'password_reset' => [
                'user_name' => 'Juan Pérez',
                'reset_url' => url('/reset-password/ejemplo'),
                'expire_minutes' => (string) config('auth.passwords.users.expire', 60),
            ],
            'order_invoice' => [
                'user_name' => 'Juan Pérez',
                'order_id' => '1042',
                'package_name' => '1 mes - 1 pantalla',
                'amount' => '$10.00 USD',
                'payment_method_name' => 'Transferencia bancaria',
                'status_label' => 'Pendiente de pago',
                'intro_text' => 'recibimos tu pedido y el comprobante de pago que subiste. Está en revisión — en cuanto lo confirmemos, activaremos tu línea y te avisaremos por correo.',
                'issued_date' => now()->format('d/m/Y'),
                'billing_address' => 'Av. Amazonas 123<br>Quito, Pichincha, 170150, Ecuador',
                'billing_address_text' => "Av. Amazonas 123\nQuito, Pichincha, 170150, Ecuador",
                'orders_url' => route('orders.index'),
            ],
            'ticket_created' => [
                'user_name' => 'Juan Pérez',
                'ticket_id' => '4207',
                'subject' => 'No puedo ver algunos canales',
                'category_label' => 'Canales o contenido',
                'priority_label' => 'Media',
                'message' => 'Desde ayer no puedo ver los canales de deportes, el resto funciona bien.',
                'ticket_url' => url('/tickets/58'),
            ],
            'ticket_reply' => [
                'user_name' => 'Juan Pérez',
                'ticket_id' => '4207',
                'subject' => 'No puedo ver algunos canales',
                'reply_message' => 'Hola, ya revisamos tu línea y actualizamos el servidor. ¿Puedes probar de nuevo?',
                'ticket_url' => url('/tickets/58'),
            ],
            'ticket_closed' => [
                'user_name' => 'Juan Pérez',
                'ticket_id' => '4207',
                'subject' => 'No puedo ver algunos canales',
                'resolution' => 'Se reasignó el servidor de streaming del cliente. Confirmado con el cliente que los canales ya cargan correctamente.',
                'ticket_url' => url('/tickets/58'),
            ],
            'ticket_admin_new' => [
                'ticket_id' => '4207',
                'subject' => 'No puedo ver algunos canales',
                'customer_name' => 'Juan Pérez',
                'customer_email' => 'juan.perez@example.com',
                'category_label' => 'Canales o contenido',
                'priority_label' => 'Media',
                'message' => 'Desde ayer no puedo ver los canales de deportes, el resto funciona bien.',
                'ticket_url' => url('/adm_4livepro/tickets/58'),
            ],
            'ticket_admin_reply' => [
                'ticket_id' => '4207',
                'subject' => 'No puedo ver algunos canales',
                'customer_name' => 'Juan Pérez',
                'message' => 'Gracias, ya probé de nuevo y sigue igual.',
                'ticket_url' => url('/adm_4livepro/tickets/58'),
            ],
            'daily_sales_summary' => [
                'date' => now()->format('d/m/Y'),
                'paid_orders_count' => '3',
                'revenue' => '$45.50 USD',
                'trial_orders_count' => '2',
                'total_orders_count' => '5',
            ],
            default => [],
        };
    }

    /**
     * Sustituye {{variable}} (con o sin espacios) por su valor. Las variables sin
     * valor provisto se dejan tal cual, para que un typo en el template sea visible.
     * Sin escapar — se usa para el asunto y el texto plano, que no interpretan HTML.
     */
    public static function substitute(string $text, array $variables): string
    {
        return preg_replace_callback('/\{\{\s*(\w+)\s*\}\}/', function ($match) use ($variables) {
            return array_key_exists($match[1], $variables) ? (string) $variables[$match[1]] : $match[0];
        }, $text);
    }

    /**
     * Igual que substitute(), pero para el cuerpo HTML: escapa cada valor con e() antes de
     * insertarlo, para que datos de terceros (mensaje de un ticket, nombre de invitado, dirección
     * de facturación, etc. — varios de ellos escritos por usuarios anónimos sin cuenta) no puedan
     * inyectar HTML en un correo que además se manda con {!! !!} (emails.template-html). Un valor
     * envuelto en HtmlString se considera HTML ya armado a propósito (ej. "billing_address", que
     * intencionalmente lleva <br>) y se inserta tal cual, sin escapar.
     */
    public static function substituteHtml(string $text, array $variables): string
    {
        return preg_replace_callback('/\{\{\s*(\w+)\s*\}\}/', function ($match) use ($variables) {
            if (! array_key_exists($match[1], $variables)) {
                return $match[0];
            }

            $value = $variables[$match[1]];

            return $value instanceof HtmlString ? $value->toHtml() : e((string) $value);
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
            'html' => static::substituteHtml($template->html_body, $variables),
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
