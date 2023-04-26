<?php

namespace App\Providers\CoreBinder;

use App\Core\Formatter\Randomizer\Randomizer;
use App\Core\Formatter\Randomizer\RandomizerUUID;
use Illuminate\Contracts\Foundation\Application;

class CoreBinderFormatter implements CoreBinder
{
    public function bootCore(Application $app): void
    {
        $app->bind(Randomizer::class, function (Application $app) {
            return new RandomizerUUID;
        });
    }
}
