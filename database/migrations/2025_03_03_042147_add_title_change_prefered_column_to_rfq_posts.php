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
        Schema::table('rfq_posts', function (Blueprint $table): void {
            $table->renameColumn('prefer_countries', 'preferred_countries');
            $table->string('title')->after('id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rfq_posts', function (Blueprint $table): void {
            $table->dropColumn('title');
            $table->renameColumn('preferred_countries', 'prefer_countries');
        });
    }
};
