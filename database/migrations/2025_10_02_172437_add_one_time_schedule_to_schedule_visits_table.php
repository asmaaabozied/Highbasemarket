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

        Schema::table('schedule_visits', function (Blueprint $table): void {
            $table->string('schedule_type')
                ->default('recurring')
                ->after('branch_id');
            $table->string('recurrence_type')->nullable()->change();
            $table->string('recurrence_value')->nullable()->change();
            $table->date('start_date')->nullable()->change();
            $table->date('end_date')->nullable()->change();

            $table->date('one_time_date')->nullable()->after('end_date');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::table('schedule_visits', function (Blueprint $table): void {
            $table->dropColumn('schedule_type');
            $table->dropColumn('one_time_date');

            $table->string('recurrence_type')->nullable(false)->change();
            $table->string('recurrence_value')->nullable(false)->change();
            $table->date('start_date')->nullable(false)->change();
            $table->date('end_date')->nullable()->change();
        });

    }
};
