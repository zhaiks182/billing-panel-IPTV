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
        Schema::table('telegram_settings', function (Blueprint $table) {
            $table->boolean('daily_summary_enabled')->default(false)->after('webhook_secret');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('telegram_settings', function (Blueprint $table) {
            $table->dropColumn('daily_summary_enabled');
        });
    }
};
