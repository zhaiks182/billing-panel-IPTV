<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Roles de administrador — hasta ahora `role='admin'` daba acceso total. Se agrega un
 * segundo nivel (`admin_role`) para poder crear cuentas de "Soporte" con acceso limitado
 * (sin Configuración ni Paquetes/Categorías/Métodos de pago/Cupones). Solo tiene sentido
 * cuando `role='admin'` — para clientes queda `null`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('admin_role')->nullable()->after('role');
        });

        // Todos los admins que ya existen quedan en super_admin — nadie pierde acceso.
        DB::table('users')->where('role', 'admin')->update(['admin_role' => 'super_admin']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('admin_role');
        });
    }
};
