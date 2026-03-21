<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stocks', function (Blueprint $table): void {
            $table->decimal('price', 15, 3)->change();
            $table->decimal('selling_price', 15, 3)->nullable()->change();
        });

        Schema::table('order_lines', function (Blueprint $table): void {
            $table->decimal('price', 15, 3)->change();
            $table->decimal('total', 15, 3)->change();
            $table->decimal('hb_commission', 15, 3)->nullable()->change();
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->decimal('total', 15, 3)->change();
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->decimal('amount', 15, 3)->change();
            $table->decimal('pending', 15, 3)->change();
        });

        Schema::table('payment_items', function (Blueprint $table): void {
            $table->decimal('amount', 15, 3)->change();
        });
    }
};
