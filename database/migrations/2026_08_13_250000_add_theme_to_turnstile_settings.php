<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Antes el tema del widget (claro/oscuro) estaba hardcodeado por separado en cada vista que
 * lo renderiza (login admin, login cliente, componente compartido de registro/checkout/
 * tickets) — a pedido del usuario, se centraliza como una sola configuración que aplica a
 * los tres lugares a la vez.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('turnstile_settings', function (Blueprint $table) {
            $table->string('theme')->default('dark')->after('secret_key');
        });
    }

    public function down(): void
    {
        Schema::table('turnstile_settings', function (Blueprint $table) {
            $table->dropColumn('theme');
        });
    }
};
