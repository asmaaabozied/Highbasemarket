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
        Schema::create('rfq_posts', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->boolean('published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->string('status')->default('open')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->foreignId('branch_id')->nullable();
            $table->text('description')->nullable();
            $table->json('prefer_countries')->nullable();
            $table->json('address')->nullable();
            $table->foreignId('category_id')->nullable();
            $table->foreignId('category_group_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rfq_posts');
    }
};
