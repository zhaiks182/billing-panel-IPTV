<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Actualiza la plantilla "order_invoice" (creada en
 * 2026_08_05_155412_add_order_invoice_email_template.php) para que el estado y el texto
 * de introducción usen variables ({{status_label}}, {{intro_text}}) en vez de texto fijo
 * "Pendiente de pago" — necesario porque ahora los paquetes demo también reciben este
 * correo (a pedido del usuario, 2026-08-05), y ahí el estado real es "Prueba gratuita",
 * no "pendiente de pago" (no hay comprobante ni revisión de pago en un trial).
 */
return new class extends Migration
{
    public function up(): void
    {
        $replacements = [
            'Factura #{{order_id}} - Pendiente de pago' => 'Factura #{{order_id}} - {{status_label}}',
            '<span style="background:#fef3c7;color:#92400e;font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;padding:4px 10px;border-radius:9999px;display:inline-block;">Pendiente de pago</span>'
                => '<span style="background:#fef3c7;color:#92400e;font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;padding:4px 10px;border-radius:9999px;display:inline-block;">{{status_label}}</span>',
            'Hola {{user_name}}, recibimos tu pedido y el comprobante de pago que subiste. Está en revisión — en cuanto lo confirmemos, activaremos tu línea y te avisaremos por correo.'
                => 'Hola {{user_name}}, {{intro_text}}',
            "🧾 FACTURA — PENDIENTE DE PAGO\n\n" => "🧾 FACTURA — {{status_label}}\n\n",
        ];

        $textReplacements = [
            "🧾 FACTURA — PENDIENTE DE PAGO\n\n" => "🧾 FACTURA — {{status_label}}\n\n",
            "Hola {{user_name}}, recibimos tu pedido y el comprobante de pago que subiste. Está en revisión — en cuanto lo confirmemos, activaremos tu línea y te avisaremos por correo.\n\n"
                => "Hola {{user_name}}, {{intro_text}}\n\n",
        ];

        $template = DB::table('email_templates')->where('key', 'order_invoice')->first();

        if (! $template) {
            return;
        }

        DB::table('email_templates')->where('key', 'order_invoice')->update([
            'subject' => strtr($template->subject, $replacements),
            'html_body' => strtr($template->html_body, $replacements),
            'text_body' => strtr($template->text_body, $textReplacements),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Migración de datos — no tiene un "down" significativo.
    }
};
