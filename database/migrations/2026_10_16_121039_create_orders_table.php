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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cashier_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('member_id')->nullable()->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('subtotal');
            $table->decimal('tax_percent', 5, 2);
            $table->unsignedBigInteger('tax_amount');
            $table->unsignedBigInteger('total');
            $table->string('payment_method', 20);
            $table->unsignedBigInteger('cash_received')->nullable();
            $table->unsignedBigInteger('change_due')->nullable();
            $table->unsignedInteger('points_earned')->default(0);
            $table->unsignedInteger('member_points_after')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
