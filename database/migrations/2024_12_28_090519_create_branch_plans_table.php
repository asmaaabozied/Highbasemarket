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
        Schema::create('branch_plans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('plans')->onDelete('cascade');
            $table->integer('duration')->nullable();
            $table->boolean('is_percentage')->nullable();
            $table->decimal('amount')->nullable();
            $table->json('attributes')->nullable();
            $table->string('cancellation_type')->nullable();
            $table->string('status')->default('active');
            $table->dateTime('expiration_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_plans');
    }
};
