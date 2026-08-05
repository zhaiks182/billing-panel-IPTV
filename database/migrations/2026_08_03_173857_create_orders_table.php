<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('package_id')->constrained();
            $table->foreignId('payment_method_id')->constrained();
            $table->decimal('amount', 8, 2);
            $table->string('proof_path')->nullable();
            $table->text('customer_note')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'error'])->default('pending');
            $table->text('admin_note')->nullable();
            $table->boolean('is_renewal')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
