<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stocks', function (Blueprint $table): void {
            $table->integer('vat')->nullable();
            $table->date('expiration_date')->nullable();
            $table->string('sku')->nullable();
            $table->decimal('rrp', 10, 2)->nullable();
            $table->decimal('moq', 10, 2)->nullable();
            $table->string('shipping_class')->nullable();
        });
    }
};
