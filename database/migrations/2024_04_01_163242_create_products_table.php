<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->nullable();
            $table->string('internal_id')->nullable();
            $table->string('sequence')->nullable();
            $table->foreignId('brand_id')->nullable();
            $table->foreignId('category_group_id')->nullable();
            $table->string('slug');
            $table->string('name');
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->string('full_name');
            $table->boolean('taxable')->default(true);
            $table->decimal('tax_value')->default(5);
            $table->string('country')->nullable();
            $table->string('barcode')->nullable();
            $table->string('shelf_time')->nullable();
            $table->boolean('published')->default(false);
            $table->string('status')->default('active');
            $table->json('options')->nullable();
            $table->json('packaging')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
