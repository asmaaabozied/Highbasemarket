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
            $table->boolean('assigned')->default(false);
        });

        Schema::table('steps', function (Blueprint $table): void {
            $table->string('reaction')->nullable();
            $table->string('confirmation')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('steps', function (Blueprint $table): void {
            $table->dropColumn('reaction');
            $table->dropColumn('confirmation');
        });

        Schema::table('progress', function (Blueprint $table): void {
            $table->dropColumn('assigned');
        });
    }
};
