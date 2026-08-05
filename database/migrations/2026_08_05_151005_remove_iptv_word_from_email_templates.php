<?php

use App\Models\EmailTemplate;
use Illuminate\Database\Migrations\Migration;

/**
 * Quita la palabra "IPTV" de las 4 plantillas de correo por defecto (a pedido del
 * usuario, 2026-08-05). Migración de datos, no de esquema — reemplazos de texto
 * puntuales sobre filas que ya existen (ver 2026_08_05_110740_..._professional_design.php).
 */
return new class extends Migration
{
    public function up(): void
    {
        $replacements = [
            '4LivePro Latino — IPTV Premium' => '4LivePro Latino',
            'Tu línea IPTV está activa - Pedido #{{order_id}}' => 'Tu línea está activa - Pedido #{{order_id}}',
            'tu línea IPTV ya está activa' => 'tu línea ya está activa',
            'Tu línea IPTV vence pronto' => 'Tu línea vence pronto',
            'Tu línea IPTV ({{package_name}})' => 'Tu línea ({{package_name}})',
        ];

        foreach (EmailTemplate::all() as $template) {
            $apply = fn (?string $text) => $text === null ? null : strtr($text, $replacements);

            $template->update([
                'subject' => $apply($template->subject),
                'html_body' => $apply($template->html_body),
                'text_body' => $apply($template->text_body),
            ]);
        }
    }

    public function down(): void
    {
        // Migración de datos — no tiene un "down" significativo.
    }
};
