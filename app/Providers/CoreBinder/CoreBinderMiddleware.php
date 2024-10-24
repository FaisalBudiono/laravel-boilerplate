<?php

declare(strict_types=1);

namespace App\Providers\CoreBinder;

use App\Http\Middleware\XRequestIDMiddleware;
use App\Services\Formatter\Randomizer\Randomizer;
use Illuminate\Contracts\Foundation\Application;

class CoreBinderMiddleware implements CoreBinder
{
    public function bootCore(Application $app): void
    {
        $app->bind(
            XRequestIDMiddleware::class,
            fn (Application $app) => new XRequestIDMiddleware(
                $app->make(Randomizer::class),
            ),
        );
    }
}
