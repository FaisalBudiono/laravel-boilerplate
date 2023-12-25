<?php

declare(strict_types=1);

namespace App\Core\Formatter\Randomizer;

interface Randomizer
{
    public function getRandomizeString(): string;
}
