<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageExists implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $in_chucked = Storage::files("chunked_attachments/$value");

        if (Str::startsWith($value, 'http')) {
            return;
        }

        if (! Storage::has("public/$value") && ! Storage::has($value) && empty($in_chucked)) {
            $fail('The :attribute does not been uploaded.');
        }
    }
}
