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
        Schema::create('cards', function (Blueprint $table): void {
            $table->id();
            $table->string('card_id');
            $table->string('object');
            $table->string('first_six');
            $table->string('first_eight');
            $table->string('scheme');
            $table->string('brand');
            $table->string('last_four');
            $table->string('name');
            $table->json('expiry')->nullable();
            $table->string('customer_id')->nullable();
            $table->foreignId('account_id')->nullable();
            $table->string('cvc')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
