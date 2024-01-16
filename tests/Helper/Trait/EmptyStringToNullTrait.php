<?php

declare(strict_types=1);

namespace Tests\Helper\Trait;

trait EmptyStringToNullTrait
{
    protected static function emptyStringToNull(mixed $value): mixed
    {
        if (is_null($value) || $value === '') {
            return null;
        }

        return $value;
    }
}
