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
        Schema::create('turnstile_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('enabled')->default(false);
            $table->string('site_key')->nullable();
            $table->text('secret_key')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turnstile_settings');
    }
};
