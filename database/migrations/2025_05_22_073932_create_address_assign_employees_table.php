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
        Schema::create('address_assign_employees', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('employee_id');
            $table->foreignId('branch_address_id');
            $table->string('releasing_stock')->nullable();
            $table->string('receiving_stock')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('address_assign_employees');
    }
};
