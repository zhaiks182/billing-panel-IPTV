<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ícono grande por artículo (junto al H1, como en la referencia visual que compartió el
 * usuario) — mismo patrón ya usado en help_categories.icon.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('help_articles', function (Blueprint $table) {
            $table->string('icon')->nullable()->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('help_articles', function (Blueprint $table) {
            $table->dropColumn('icon');
        });
    }
};
