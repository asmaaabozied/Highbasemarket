<?php

use App\Enum\EmployeeVisitStatusEnum;
use App\Enum\SourceTypeEnum;
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
        Schema::create('employee_visits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('branches')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->cascadeOnDelete();
            $table->foreignId('parent_visit_id')->nullable()->constrained('employee_visits')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('schedule_visit_id')
                ->nullable()
                ->constrained('schedule_visits')
                ->cascadeOnDelete();
            $table->timestamp('scheduled_at');
            $table->timestamp('checkout_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->string('status')->default(EmployeeVisitStatusEnum::SCHEDULED);
            $table->string('source_type')->default(SourceTypeEnum::SCHEDULE);
            $table->unsignedTinyInteger('weight')->default(0);
            $table->unsignedTinyInteger('custom_weight')->default(0);
            $table->string('postpone_reason')->nullable();
            $table->longText('postpone_notes')->nullable();
            $table->string('purpose')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_visits');
    }
};
