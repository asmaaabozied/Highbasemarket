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
        Schema::create('quote_products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('quote_detail_id');
            $table->nullableMorphs('quotable');
            $table->float('price')->nullable();
            $table->float('temperature')->nullable();
            $table->float('total_price')->nullable();
            $table->integer('quantity')->nullable();
            $table->integer('size')->nullable();
            $table->string('pack')->nullable();
            $table->string('unit')->nullable();
            $table->json('tech_specifications')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_products');
    }
};
