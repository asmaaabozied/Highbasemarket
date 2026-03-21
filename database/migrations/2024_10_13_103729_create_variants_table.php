<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('variants', function (Blueprint $table): void {
            $table->id();
            $table->uuid();
            $table->foreignId('product_id');
            $table->string('name')->nullable();
            $table->string('country')->nullable();
            $table->string('barcode')->nullable();
            $table->boolean('main')->nullable();
            $table->string('image')->nullable();
            $table->json('images')->nullable();
            $table->json('attributes');
            $table->json('packages')->nullable();
            $table->boolean('taxable')->default(false);
            $table->decimal('tax_rate')->default(0);
            $table->string('status')->default('active');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('variants');
    }
};
