<?php

namespace App\Providers\CoreBinder;

use Illuminate\Contracts\Foundation\Application;

interface CoreBinder
{
    public function bootCore(Application $app): void;
}
