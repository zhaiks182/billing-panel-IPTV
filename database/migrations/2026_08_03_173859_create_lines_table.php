<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained();
            $table->string('xui_line_id')->nullable();
            $table->string('xui_username');
            $table->string('xui_password');
            $table->string('m3u_url')->nullable();
            $table->unsignedTinyInteger('max_connections')->default(1);
            $table->timestamp('expires_at');
            $table->enum('status', ['active', 'expired', 'suspended'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lines');
    }
};
