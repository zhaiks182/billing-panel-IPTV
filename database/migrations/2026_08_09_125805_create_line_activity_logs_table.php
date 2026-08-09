<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('line_activity_logs', function (Blueprint $table) {
            $table->id();
            // Nullable + nullOnDelete: si la línea o el admin se borran despues, el log
            // sobrevive (la descripción ya trae el texto humano necesario para entender
            // qué pasó) — es justamente para auditar un reclamo, no debe desaparecer.
            $table->foreignId('line_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->text('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('line_activity_logs');
    }
};
