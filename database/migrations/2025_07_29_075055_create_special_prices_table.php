<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('special_prices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('special_price_template_id');
            $table->morphs('targetable');
            $table->decimal('amount');
            $table->string('type')->default('percentage');
            $table->boolean('is_increment')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('special_prices');
    }
};
