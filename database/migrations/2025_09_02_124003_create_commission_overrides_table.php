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
        Schema::create('commission_overrides', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->foreignId('order_line_id')
                ->constrained('order_lines')
                ->cascadeOnDelete();

            $table->foreignId('override_by_id')
                ->constrained('users');

            $table->decimal('commission_usd_before', 12, 2);
            $table->decimal('commission_usd_after', 12, 2);
            $table->decimal('commission_local_before', 12, 2);
            $table->decimal('commission_local_after', 12, 2);
            $table->longText('reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_overrides');
    }
};
