<?php

namespace Tests\Helper\MockInstance\Middleware;

use App\Models\User\User;
use Tests\Helper\MockInstance\Middleware\AuthenticatedByJWT\Implementor;
use Tests\TestCase;

class MockerAuthenticatedByJWT
{
    protected Implementor $implementor;

    public function __construct(
        TestCase $test,
    ) {
        $this->implementor = Implementor::make($test);
    }

    public static function make(TestCase $test): self
    {
        return new self($test);
    }

    public function bindInstance(): void
    {
        $this->implementor->bindInstance();
    }

    public function mockLogin(User $user): self
    {
        $this->implementor->mockLogin($user);
        return $this;
    }

    public function mockGuestAndThrow(\Throwable $e): self
    {
        $this->implementor->mockGuestAndThrow($e);
        return $this;
    }
}
