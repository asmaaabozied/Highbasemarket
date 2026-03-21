<?php

namespace Database\Factories;

use App\Enum\RecurrenceTypeEnum;
use App\Enum\VisitPurposeTypeEnum;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\ScheduleVisit;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ScheduleVisit>
 */
class ScheduleVisitFactory extends Factory
{
    protected $model = ScheduleVisit::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id'      => Employee::factory(),
            'customer_id'      => Branch::factory(),
            'recurrence_type'  => RecurrenceTypeEnum::WEEKLY,
            'recurrence_value' => CarbonInterface::MONDAY,
            'created_by'       => User::factory(),
            'start_date'       => fake()->date(),
            'end_date'         => null,
            'purpose'          => VisitPurposeTypeEnum::ORDER_DELIVERY,
        ];
    }
}
