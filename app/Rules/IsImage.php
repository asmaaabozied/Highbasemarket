<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class IsImage implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value) {
            return;
        }

        if ($value instanceof \Illuminate\Http\UploadedFile) {
            return;
        }

        $in_chucked = Storage::files("chunked_attachments/$value");

        if (Str::startsWith($value, 'http')) {
            return;
        }

        if (! Storage::has("public/$value") && ! Storage::has($value) && empty($in_chucked)) {
            $fail('The :attribute does not been uploaded.');
        }
    }
}
