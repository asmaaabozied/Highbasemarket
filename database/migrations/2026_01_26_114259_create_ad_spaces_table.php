<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_spaces', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('branch_id');
            $table->string('business_type');
            $table->json('available_ad_spaces');
            $table->boolean('has_dine_in_area')->default(false);
            $table->json('outlet_size')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_spaces');
    }
};
