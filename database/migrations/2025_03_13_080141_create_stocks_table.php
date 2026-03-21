<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table): void {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('product_id');
            $table->foreignId('variant_id');
            $table->foreignId('branch_id');
            $table->decimal('price')->unsigned();
            $table->unsignedInteger('quantity');
            $table->json('tiers')->nullable();
            $table->string('packaging');
            $table->string('image')->nullable();
            $table->json('images')->nullable();
            $table->json('config')->nullable();
            $table->string('status')->default('pending');
            $table->boolean('show_price')->default(false);
            $table->dateTime('published_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
