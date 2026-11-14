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
        Schema::table('orders', function (Blueprint $table): void {
            $table->unsignedInteger('points_redeemed')->default(0)->after('points_earned');
            $table->unsignedInteger('point_discount_amount')->default(0)->after('discount_amount');
            $table->timestamp('voided_at')->nullable()->after('member_points_after')->index();
            $table->foreignId('voided_by')->nullable()->after('voided_at')->constrained('users')->restrictOnDelete();
            $table->string('void_reason', 255)->nullable()->after('voided_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('voided_by');
            $table->dropIndex(['voided_at']);
            $table->dropColumn(['points_redeemed', 'point_discount_amount', 'voided_at', 'void_reason']);
        });
    }
};
