<?php

declare(strict_types=1);

namespace App\Providers\CoreBinder;

use Illuminate\Contracts\Foundation\Application;

interface CoreBinder
{
    public function bootCore(Application $app): void;
}
