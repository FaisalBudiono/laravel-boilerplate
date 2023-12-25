<?php

declare(strict_types=1);

namespace Tests\Helper\Trait;

trait JSONTrait
{
    protected function jsonToArray(string $json): array
    {
        return json_decode($json, true);
    }
}
