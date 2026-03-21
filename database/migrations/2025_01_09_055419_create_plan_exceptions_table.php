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
        Schema::create('plan_exceptions', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->foreignId('plan_id')->nullable();
            $table->nullableMorphs('exceptionable');
            $table->string('module')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_exceptions');
    }
};
