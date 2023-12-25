<?php

declare(strict_types=1);

namespace App\Core\Formatter\Randomizer;

use Illuminate\Support\Str;

class RandomizerUUID implements Randomizer
{
    public function getRandomizeString(): string
    {
        return Str::uuid()->toString();
    }
}
