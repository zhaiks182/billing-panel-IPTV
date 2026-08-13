<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * A pedido del usuario: reordena las categorías de Ayuda en el sidebar/listado.
 */
return new class extends Migration
{
    public function up(): void
    {
        $order = [
            'preguntas-frecuentes' => 1,
            'instalacion' => 2,
            'xui-one-administracion' => 3,
            'xui-one-lineas-revendedores' => 4,
        ];

        foreach ($order as $slug => $sortOrder) {
            DB::table('help_categories')->where('slug', $slug)->update(['sort_order' => $sortOrder]);
        }
    }

    public function down(): void
    {
        DB::table('help_categories')->where('slug', 'instalacion')->update(['sort_order' => 1]);
        DB::table('help_categories')->where('slug', 'preguntas-frecuentes')->update(['sort_order' => 2]);
        DB::table('help_categories')->where('slug', 'xui-one-administracion')->update(['sort_order' => 1]);
        DB::table('help_categories')->where('slug', 'xui-one-lineas-revendedores')->update(['sort_order' => 2]);
    }
};
