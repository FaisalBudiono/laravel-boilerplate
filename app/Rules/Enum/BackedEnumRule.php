<?php

namespace App\Rules\Enum;

use BackedEnum;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use ValueError;

class BackedEnumRule implements ValidationRule
{
    public function __construct(protected BackedEnum $enum)
    {
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $enumValueType = get_debug_type($this->enum->value);

            if ($enumValueType !== get_debug_type($value)) {
                $value = $this->castValue($enumValueType, $value);
            }

            $this->enum->from($value);
        } catch (ValueError $e) {
            $fail("The :attribute should only contain: {$this->getStringifiedValidValue()}");
        }
    }

    protected function castValue(string $type, mixed $value): mixed
    {
        if ($type === 'int') {
            return intval($value);
        }

        if ($type === 'string') {
            return (string) $value;
        }
    }

    protected function getStringifiedValidValue(): string
    {
        return collect($this->enum->cases())->implode(function (BackedEnum $enum) {
            return $enum->value;
        }, ', ');
    }
}
