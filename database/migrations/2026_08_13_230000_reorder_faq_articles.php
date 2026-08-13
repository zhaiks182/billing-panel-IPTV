<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Reordena los artículos de "Preguntas frecuentes" para que coincidan con la secuencia
 * que el usuario mostró de un sitio de referencia (solo el ORDEN de presentación, no el
 * contenido — el contenido de cada artículo sigue siendo el original ya escrito, no una
 * traducción de esa fuente).
 */
return new class extends Migration
{
    public function up(): void
    {
        $order = [
            'que-es-iptv' => 1,
            'como-funciona-iptv' => 2,
            'que-es-suscripcion-iptv' => 3,
            'que-es-una-lista-m3u' => 4,
            'que-es-vod' => 5,
            'que-es-catchup-tv' => 6,
            'iptv-vs-cable' => 7,
            'que-es-android-tv-box' => 8,
            'que-es-dispositivo-mag' => 9,
            'que-es-revendedor-iptv' => 10,
            'cuantas-conexiones-simultaneas' => 11,
        ];

        foreach ($order as $slug => $sortOrder) {
            DB::table('help_articles')->where('slug', $slug)->update(['sort_order' => $sortOrder]);
        }
    }

    public function down(): void
    {
        $original = [
            'que-es-iptv' => 1,
            'que-es-una-lista-m3u' => 2,
            'cuantas-conexiones-simultaneas' => 3,
            'como-funciona-iptv' => 4,
            'que-es-suscripcion-iptv' => 5,
            'que-es-vod' => 6,
            'que-es-catchup-tv' => 7,
            'iptv-vs-cable' => 8,
            'que-es-android-tv-box' => 9,
            'que-es-dispositivo-mag' => 10,
            'que-es-revendedor-iptv' => 11,
        ];

        foreach ($original as $slug => $sortOrder) {
            DB::table('help_articles')->where('slug', $slug)->update(['sort_order' => $sortOrder]);
        }
    }
};
