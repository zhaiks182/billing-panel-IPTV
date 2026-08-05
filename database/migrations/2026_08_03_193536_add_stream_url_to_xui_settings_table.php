<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('xui_settings', function (Blueprint $table) {
            $table->string('stream_url')->nullable()->after('panel_url');
        });
    }

    public function down(): void
    {
        Schema::table('xui_settings', function (Blueprint $table) {
            $table->dropColumn('stream_url');
        });
    }
};
