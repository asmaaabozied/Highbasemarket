<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $table->json('attributes')->nullable();
            $table->json('custom_fields')->nullable();
            $table->json('displayed_attributes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $table->dropColumn('attributes');
            $table->dropColumn('custom_fields');
            $table->dropColumn('displayed_attributes');
        });
    }
};
