<?php

use App\Enum\CurrencyEnum;
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
        Schema::table('order_lines', function (Blueprint $table): void {
            $table->decimal('commission_amount_usd', 12)
                ->default(0.00);

            $table->decimal('commission_percentage', 5)
                ->nullable();

            $table->decimal('commission_amount_local_currency', 12)
                ->default(0.00);

            $table->string('commission_local_currency_code', 3)
                ->default(CurrencyEnum::BHD);

            $table->decimal('exchange_rate_to_usd', 10, 4);

            $table->foreignId('applied_plan_id')
                ->nullable()
                ->constrained('plans');

            $table->string('plan_exception_source_type', 20)
                ->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_lines', function (Blueprint $table): void {
            $table->dropColumn([
                'commission_amount_usd',
                'commission_percentage',
                'commission_amount_local_currency',
                'commission_local_currency_code',
                'exchange_rate_to_usd',
                'applied_plan_id',
                'plan_exception_source_type',
            ]);
        });
    }
};
