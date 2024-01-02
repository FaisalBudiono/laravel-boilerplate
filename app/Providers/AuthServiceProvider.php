<?php

declare(strict_types=1);

namespace App\Providers;

use App\Core\Auth\JWT\JWTGuardContract;
use App\Core\Post\Policy\PostPolicyContract;
use App\Core\User\Policy\UserPolicyContract;
use App\Models\Post\Post;
use App\Models\User\User;
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
        Post::class => PostPolicyContract::class,
        User::class => UserPolicyContract::class,
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
