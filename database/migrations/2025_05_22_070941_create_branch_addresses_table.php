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
        Schema::create('branch_addresses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('branch_id');
            $table->string('address_name');
            $table->string('address_operations');
            $table->string('address_purpose');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->json('address')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_addresses');
    }
};
