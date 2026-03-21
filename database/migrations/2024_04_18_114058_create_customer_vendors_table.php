<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_vendors', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vendor_id')->constrained('branches');
            $table->foreignId('customer_id')->constrained('branches');
            $table->foreignId('inviter_employee_id')->nullable();
            $table->foreignId('acceptor_employee_id')->nullable();
            $table->json('config')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_vendors');
    }
};
