<?php

namespace Database\Factories;

use App\Enum\EmployeeVisitStatusEnum;
use App\Enum\SourceTypeEnum;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeVisit;
use App\Models\ScheduleVisit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeVisitFactory extends Factory
{
    protected $model = EmployeeVisit::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'schedule_visit_id' => ScheduleVisit::factory(),
            'scheduled_at'      => now(),
            'source_type'       => SourceTypeEnum::SCHEDULE,
            'status'            => EmployeeVisitStatusEnum::SCHEDULED,
            'employee_id'       => Employee::factory(),
            'customer_id'       => Branch::factory(),
            'order_id'          => null,
            'custom_weight'     => 1,
            'created_by'        => User::factory(),
            'weight'            => 1,
        ];
    }
}
