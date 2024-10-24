<?php

declare(strict_types=1);

namespace App\Providers\CoreBinder;

use App\Services\Formatter\Randomizer\Randomizer;
use App\Services\Formatter\Randomizer\RandomizerUUID;
use Illuminate\Contracts\Foundation\Application;

class CoreBinderFormatter implements CoreBinder
{
    public function bootCore(Application $app): void
    {
        $app->bind(
            Randomizer::class,
            fn (Application $app) => new RandomizerUUID(),
        );
    }
}
