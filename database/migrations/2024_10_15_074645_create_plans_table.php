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
        Schema::create('plans', function (Blueprint $table): void {
            $table->id();
            $table->string('title')->nullable();
            $table->text('notes')->nullable();
            $table->enum('plan_mode', ['free', 'trial', 'paid'])->nullable();
            $table->enum('plan_type', ['local', 'global', 'services'])->nullable();
            $table->boolean('is_auto_renewable')->nullable();
            $table->integer('duration')->nullable();
            $table->boolean('is_percentage')->nullable();
            $table->decimal('amount', 5)->nullable();
            $table->json('attributes')->nullable();
            $table->string('cancellation_type')->nullable();
            $table->string('status')->nullable();
            $table->text('description')->nullable();
            $table->json('auto_assignees')->nullable();
            $table->json('countries')->nullable();
            $table->unsignedBigInteger('associated_plan_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
