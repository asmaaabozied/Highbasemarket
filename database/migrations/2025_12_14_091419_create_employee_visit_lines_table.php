<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_visit_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_line_id');
            $table->foreignId('employee_visit_id');
            $table->decimal('quantity')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_visit_lines');
    }
};
