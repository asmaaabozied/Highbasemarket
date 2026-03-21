<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_visits', function (Blueprint $table): void {

            //  Date-based queries (Today / Timeline)
            $table->index('scheduled_at');

            //  Status & source filters
            $table->index('status');
            $table->index('source_type');

            /**
             * Composite indexes (IMPORTANT)
             */

            // Employee + date (Today visits)
            $table->index(
                ['employee_id', 'scheduled_at'],
                'ev_employee_scheduled_at_idx'
            );

            // Branch + date (Manager / branch views)
            $table->index(
                ['branch_id', 'scheduled_at'],
                'ev_branch_scheduled_at_idx'
            );

            // Schedule + date (projection / next-date logic)
            $table->index(
                ['schedule_visit_id', 'scheduled_at'],
                'ev_schedule_scheduled_at_idx'
            );

            // Status + date (filtering)
            $table->index(
                ['status', 'scheduled_at'],
                'ev_status_scheduled_at_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('employee_visits', function (Blueprint $table): void {

            $table->dropIndex(['scheduled_at']);
            $table->dropIndex(['status']);
            $table->dropIndex(['source_type']);

            $table->dropIndex('ev_employee_scheduled_at_idx');
            $table->dropIndex('ev_branch_scheduled_at_idx');
            $table->dropIndex('ev_schedule_scheduled_at_idx');
            $table->dropIndex('ev_status_scheduled_at_idx');
        });
    }
};
