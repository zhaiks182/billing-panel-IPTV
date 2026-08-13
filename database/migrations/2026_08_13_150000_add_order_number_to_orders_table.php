<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_number', 4)->nullable()->unique()->after('id');
        });

        $used = [];

        foreach (DB::table('orders')->orderBy('id')->pluck('id') as $id) {
            do {
                $number = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            } while (in_array($number, $used, true));

            $used[] = $number;

            DB::table('orders')->where('id', $id)->update(['order_number' => $number]);
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('order_number');
        });
    }
};
