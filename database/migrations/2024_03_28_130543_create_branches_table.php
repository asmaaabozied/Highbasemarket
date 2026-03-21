<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('account_id');
            $table->foreignId('parent_id')->nullable()->constrained('branches');
            $table->string('internal_id')->nullable();
            $table->string('slug')->unique();
            $table->json('name');
            $table->mediumText('description')->nullable();
            $table->string('cr')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->boolean('main_branch')->default(false);
            $table->string('status')->default('active');
            $table->json('address')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
