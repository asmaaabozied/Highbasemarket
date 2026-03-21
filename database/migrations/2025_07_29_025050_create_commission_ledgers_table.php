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
        Schema::create('commission_ledgers', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('order_line_id')
                ->constrained('order_lines')
                ->onDelete('cascade');

            $table->decimal('amount_usd', 12)->default(0);
            $table->decimal('paid_amount_usd', 12)->default(0);

            $table->enum('status', ['unpaid', 'partially_paid', 'paid'])
                ->default('unpaid');

            $table->dateTime('payable_at')->nullable();  // Set when delivered_at is set
            $table->dateTime('paid_at')->nullable();     // Set when paid_amount_usd >= amount_usd

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_ledgers');
    }
};
