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
        Schema::table('employee_visits', function (Blueprint $table): void {
            $table->decimal('employee_lat', 10, 7)->nullable();
            $table->decimal('employee_lng', 10, 7)->nullable();
            $table->decimal('destination_lat', 10, 7)->nullable();
            $table->decimal('destination_lng', 10, 7)->nullable();
            $table->decimal('distance_covered')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_visits', function (Blueprint $table): void {
            $table->dropColumn([
                'employee_lat',
                'employee_lng',
                'destination_lat',
                'destination_lng',
                'distance_covered',
            ]);
        });
    }
};
