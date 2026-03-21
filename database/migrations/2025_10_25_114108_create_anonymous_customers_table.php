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
        Schema::create('anonymous_customers', function (Blueprint $table): void {
            $table->id();
            // removed in next migrations
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->json('phone')->nullable();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            // till here
            $table->string('cr_number')->nullable();
            $table->string('vat_number')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('employees')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anonymous_customers');
    }
};
