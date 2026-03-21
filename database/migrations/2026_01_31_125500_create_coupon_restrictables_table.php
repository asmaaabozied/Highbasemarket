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
        // Drop old restriction tables
        Schema::dropIfExists('coupon_product');
        Schema::dropIfExists('coupon_customer');

        // Create unified polymorphic restriction table
        Schema::create('coupon_restrictables', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->morphs('restrictable');
            $table->timestamps();

            // Ensure unique restrictions per coupon
            $table->unique(['coupon_id', 'restrictable_id', 'restrictable_type'], 'coupon_restrictable_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_restrictables');
    }
};
