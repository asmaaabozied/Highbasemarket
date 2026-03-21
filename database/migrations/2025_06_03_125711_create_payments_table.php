<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id');
            $table->foreignId('branch_id')->nullable();
            $table->foreignId('employee_id')->nullable();
            $table->decimal('amount');
            $table->decimal('pending')->default(0);
            $table->string('status');
            $table->dateTime('confirmation_date')->nullable();
            $table->foreignId('confirmed_by')->nullable();
            $table->string('attachment')->nullable();
            $table->string('type');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
