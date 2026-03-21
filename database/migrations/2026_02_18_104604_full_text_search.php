<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->fullText(['name']);
        });

        Schema::table('variants', function (Blueprint $table) {
            $table->fullText(['name']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->fullText(['name']);
        });

        Schema::table('brands', function (Blueprint $table) {
            $table->fullText(['name']);
        });
    }
};
