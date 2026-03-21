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
        Schema::table('quote_payments', function (Blueprint $table): void {
            $table->float('influencer_shared')->nullable();
            $table->string('quote_status')->nullable()->default('in progress');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quote_payments', function (Blueprint $table): void {
            $table->dropColumn('influencer_shared');
            $table->dropColumn('quote_status');
        });
    }
};
