<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A pedido del usuario, tras ver el sitio de referencia: agrega iconos por categoría (para
 * el sidebar en árbol nuevo) y hace pública la documentación de XUI ONE que antes era
 * `audience = internal` — "lo referente para el admin, también debe estar público". La
 * documentación interna (guías de administración del panel) ahora es visible en /ayuda
 * igual que la de instalación — deja de ser exclusiva del admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('help_categories', function (Blueprint $table) {
            $table->string('icon')->nullable()->after('name');
        });

        $icons = [
            'instalacion' => '📲',
            'preguntas-frecuentes' => '❓',
            'xui-one-administracion' => '🖥️',
            'xui-one-lineas-revendedores' => '🔗',
        ];

        foreach ($icons as $slug => $icon) {
            DB::table('help_categories')->where('slug', $slug)->update(['icon' => $icon]);
        }

        DB::table('help_categories')->where('slug', 'xui-one-administracion')->update([
            'audience' => 'public',
            'description' => 'Cómo configurar servidores, bouquets, paquetes y contenido en el panel XUI ONE.',
        ]);

        DB::table('help_categories')->where('slug', 'xui-one-lineas-revendedores')->update([
            'audience' => 'public',
            'description' => 'Cómo crear y gestionar líneas de clientes y sub-revendedores en XUI ONE.',
        ]);
    }

    public function down(): void
    {
        DB::table('help_categories')
            ->whereIn('slug', ['xui-one-administracion', 'xui-one-lineas-revendedores'])
            ->update(['audience' => 'internal']);

        Schema::table('help_categories', function (Blueprint $table) {
            $table->dropColumn('icon');
        });
    }
};
