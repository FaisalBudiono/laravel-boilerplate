<?php

declare(strict_types=1);

namespace App\Services\Formatter\Randomizer;

interface Randomizer
{
    public function getRandomizeString(): string;
}
