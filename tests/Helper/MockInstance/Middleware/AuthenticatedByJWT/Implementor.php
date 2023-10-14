<?php

namespace Tests\Helper\MockInstance\Middleware\AuthenticatedByJWT;

use App\Core\Auth\JWT\JWTGuardContract;
use App\Http\Middleware\AuthenticatedByJWT;
use App\Models\User\User;
use Mockery\MockInterface;
use Tests\TestCase;

class Implementor extends TestCase
{
    protected AuthenticatedByJWT $jwtMiddleware;
    protected JWTGuardContract $jwtGuard;

    protected MockInterface $mockInterfaceJWTMiddleware;
    protected MockInterface $mockInterfaceJWTGuard;

    public function __construct(
        protected TestCase $test,
    ) {
        $this->jwtMiddleware = $this->test->mock(
            AuthenticatedByJWT::class,
            function (MockInterface $mock) {
                $this->mockInterfaceJWTMiddleware = $mock;
            }
        );
        $this->jwtGuard = $this->test->mock(
            JWTGuardContract::class,
            function (MockInterface $mock) {
                $this->mockInterfaceJWTGuard = $mock;
            }
        );
    }

    public static function make(TestCase $test): self
    {
        return new self($test);
    }

    public function bindInstance(): void
    {
        $this->test->instance(AuthenticatedByJWT::class, $this->jwtMiddleware);
        $this->test->instance(JWTGuardContract::class, $this->jwtGuard);
    }

    public function mockLogin(User $user): self
    {
        $this->mockInterfaceJWTMiddleware
            ->shouldReceive('handle')
            ->once()
            ->andReturnUsing(function ($argRequest, $argNext) {
                return $argNext($argRequest);
            });

        $this->mockInterfaceJWTGuard->shouldReceive('user')
            ->withNoArgs()
            ->andReturn($user);

        return $this;
    }

    public function mockGuestAndThrow(\Throwable $e): self
    {
        $this->mockInterfaceJWTMiddleware->shouldReceive('handle')->once()->andThrow($e);

        $this->mockInterfaceJWTGuard->shouldReceive('user')
            ->withNoArgs()
            ->andReturnNull();

        return $this;
    }
}
