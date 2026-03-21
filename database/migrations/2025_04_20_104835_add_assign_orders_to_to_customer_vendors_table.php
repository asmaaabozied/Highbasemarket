<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_vendors', function (Blueprint $table): void {
            $table->foreignId('assign_orders_to')
                ->nullable()
                ->constrained('employees')
                ->nullOnDelete();
        });
    }
};
