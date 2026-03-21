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
        Schema::table('progress', function (Blueprint $table): void {
            $table->string('status')->change()->nullable();
        });

        Schema::table('steps', function (Blueprint $table): void {
            $table->string('status')->change()->nullable();
            $table->string('type')->change()->nullable();
        });

        Schema::table('actions', function (Blueprint $table): void {
            $table->string('status')->change()->nullable();
        });

        Schema::table('confirms', function (Blueprint $table): void {
            $table->string('type')->change()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
