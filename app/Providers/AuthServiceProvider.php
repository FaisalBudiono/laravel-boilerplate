<?php

namespace App\Providers;

use App\Core\Auth\JWT\JWTGuardContract;
use App\Models\Post\Post;
use App\Policies\Post\PostPolicy;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Post::class => PostPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Auth::extend(
            'jwt',
            function (Application $app, string $name, array $config) {
                return $app->make(JWTGuardContract::class);
            }
        );
    }
}
