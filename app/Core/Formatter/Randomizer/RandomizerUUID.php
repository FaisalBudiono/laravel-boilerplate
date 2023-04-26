<?php

namespace App\Core\Formatter\Randomizer;

use Illuminate\Support\Str;

class RandomizerUUID implements Randomizer
{
    public function getRandomizeString(): string
    {
        return Str::uuid();
    }
}
