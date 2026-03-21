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
        Schema::table('inviters', function (Blueprint $table): void {
            $table->enum('type', ['inviter', 'acceptor'])->default('inviter');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inviters', function (Blueprint $table): void {
            $table->dropColumn('type');
        });
    }
};
