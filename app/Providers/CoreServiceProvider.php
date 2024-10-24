<?php

declare(strict_types=1);

namespace App\Providers;

use App\Providers\CoreBinder\CoreBinder;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    /** @var class-string<CoreBinder>[] */
    protected $coreBinders = [
        \App\Providers\CoreBinder\CoreBinderAuth::class,
        \App\Providers\CoreBinder\CoreBinderFormatter::class,
        \App\Providers\CoreBinder\CoreBinderJWT::class,
        \App\Providers\CoreBinder\CoreBinderUser::class,
    ];

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        foreach ($this->coreBinders as $classNameBinder) {
            $coreBinder = new $classNameBinder();

            $coreBinder->bootCore($this->app);
        }
    }
}
