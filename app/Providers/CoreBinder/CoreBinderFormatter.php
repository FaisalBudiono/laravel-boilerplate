<?php

declare(strict_types=1);

namespace App\Providers\CoreBinder;

use App\Core\Formatter\Randomizer\Randomizer;
use App\Core\Formatter\Randomizer\RandomizerUUID;
use Illuminate\Contracts\Foundation\Application;

class CoreBinderFormatter implements CoreBinder
{
    public function bootCore(Application $app): void
    {
        $app->bind(
            Randomizer::class,
            fn (Application $app) => new RandomizerUUID()
        );
    }
}
