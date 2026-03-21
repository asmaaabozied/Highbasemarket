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
        Schema::create('quote_payments', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->foreignId('influencer_id')->nullable();
            $table->foreignId('quoteId')->nullable();
            $table->float('amount')->nullable()->default(0);
            $table->string('status')->nullable()->default('Unpaid');
            $table->foreignId('vendor_account_id')->nullable();
            $table->foreignId('customer_account_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_payments');
    }
};
