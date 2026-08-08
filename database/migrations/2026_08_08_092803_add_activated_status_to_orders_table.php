<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Separa "Aprobado" (pago confirmado por el admin) de "Activado" (la línea ya existe de
 * verdad en XUI ONE) — antes ambos eran el mismo valor 'approved', así que si XUI fallaba
 * el pedido pasaba directo de 'pending' a 'error' sin dejar rastro de que el pago sí se
 * había confirmado. No se puede usar Schema::table()->enum()->change() sin doctrine/dbal
 * (no está instalado en este proyecto), de ahí el ALTER TABLE crudo.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE orders MODIFY status ENUM('pending','approved','activated','rejected','error') NOT NULL DEFAULT 'pending'");

        // Pedidos ya existentes con status='approved' significaban "pago confirmado y línea
        // creada" bajo el modelo viejo — bajo el nuevo modelo eso es 'activated'.
        DB::table('orders')->where('status', 'approved')->update(['status' => 'activated']);
    }

    public function down(): void
    {
        DB::table('orders')->where('status', 'activated')->update(['status' => 'approved']);

        DB::statement("ALTER TABLE orders MODIFY status ENUM('pending','approved','rejected','error') NOT NULL DEFAULT 'pending'");
    }
};
