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
        Schema::table('lines', function (Blueprint $table) {
            $table->dropColumn('reminder_sent_at');
            $table->timestamp('reminder_7d_sent_at')->nullable()->after('expires_at');
            $table->timestamp('reminder_3d_sent_at')->nullable()->after('reminder_7d_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lines', function (Blueprint $table) {
            $table->dropColumn(['reminder_7d_sent_at', 'reminder_3d_sent_at']);
            $table->timestamp('reminder_sent_at')->nullable()->after('expires_at');
        });
    }
};
