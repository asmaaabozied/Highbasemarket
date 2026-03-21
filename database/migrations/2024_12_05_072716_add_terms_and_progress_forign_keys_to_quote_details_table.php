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
        Schema::table('quote_details', function (Blueprint $table): void {
            $table->dropColumn('terms');
            $table->foreignId('term_id');
            $table->foreignId('progress_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quote_details', function (Blueprint $table): void {
            $table->dropColumn('term_id');
            $table->dropColumn('progress_id');
            $table->longText('terms')->nullable();
        });
    }
};
