<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_list_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('saved_list_id');
            $table->foreignId('stock_id');
            $table->decimal('quantity')->nullable();
            $table->string('packaging')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_list_items');
    }
};
