<?php

namespace App\Rules\Date;

use App\Core\Date\DatetimeFormat;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DateISORule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (
            is_string($value)
            && Carbon::hasFormat($value, DatetimeFormat::ISO_WITH_MILLIS->value)
        ) {
            return;
        }

        $fail('The :attribute should be ISO formatted datetime in UTC timezone with millisecond');
    }
}
