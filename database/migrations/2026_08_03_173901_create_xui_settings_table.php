<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('xui_settings', function (Blueprint $table) {
            $table->id();
            $table->string('panel_url')->nullable();
            $table->text('api_token')->nullable();
            $table->json('bouquet_ids')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('xui_settings');
    }
};
