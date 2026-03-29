<?php

namespace App\Enum\Rules;

use App\Enum\Enum\RecurrenceTypeEnum;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidRecurrenceValue implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $type = request()->input('recurrence_type');

        if (in_array($type, [RecurrenceTypeEnum::WEEKLY->value, RecurrenceTypeEnum::BIWEEKLY->value])) {
            if ($value < 0 || $value > 6) {
                $fail("The $attribute must be between 0 and 7 for weekly or biweekly recurrence.");
            }
        } elseif ($type === RecurrenceTypeEnum::MONTHLY->value) {
            if ($value < 1 || $value > 31) {
                $fail("The $attribute must be between 1 and 31 for the selected month.");
            }
        }
    }
}
