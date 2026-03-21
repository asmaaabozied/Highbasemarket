<?php

use App\Enum\EmployeeVisitStatusEnum;
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
        Schema::create('employee_visit_overrides', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('schedule_visit_id')
                ->constrained('schedule_visits')
                ->cascadeOnDelete();
            $table->foreignId('parent_visit_id')->nullable()->constrained('employee_visit_overrides')->cascadeOnDelete();

            $table->date('visit_date'); // future date being modified
            $table->string('status')->default(EmployeeVisitStatusEnum::SCHEDULED);

            // Postpone/Reschedule info
            $table->string('postpone_reason')->nullable();
            $table->longText('postpone_notes')->nullable();
            $table->timestamp('rescheduled_at')->nullable();

            $table->foreignId('modified_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_visit_overrides');
    }
};
