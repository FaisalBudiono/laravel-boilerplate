<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    protected $coreBinders = [
        \App\Providers\CoreBinder\CoreBinderUser::class,
    ];

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        foreach ($this->coreBinders as $classNameBinder) {
            /** @var \App\Providers\CoreBinder\CoreBinder */
            $coreBinder = new $classNameBinder;

            $coreBinder->bootCore($this->app);
        }
    }
}