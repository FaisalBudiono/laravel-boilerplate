<?php

namespace Tests\Unit\Core\User\UserCore;

use App\Core\User\UserCore;
use App\Core\User\UserCoreContract;
use App\Models\User\User;
use App\Port\Core\User\GetUserPort;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserCore_Get_Test extends TestCase
{
    use RefreshDatabase;

    protected UserCore $core;

    protected GetUserPort $mockRequest;
    /** @var (\Mockery\ExpectationInterface|\Mockery\Expectation|\Mockery\HigherOrderMessage)[] */
    protected $mockedRequestMethods;

    protected function setUp(): void
    {
        parent::setUp();

        $this->core = new UserCore();

        $this->mockRequest = $this->mock(GetUserPort::class, function (MockInterface $mock) {
            $this->getClassMethods(GetUserPort::class)->each(
                fn (string $methodName) =>
                $this->mockedRequestMethods[$methodName] = $mock->shouldReceive($methodName)
            );
        });
    }

    #[Test]
    public function should_implement_right_interface()
    {
        // Assert
        $this->assertInstanceOf(UserCoreContract::class, $this->core);
    }

    #[Test]
    public function should_return_user_model()
    {
        // Assert
        User::factory()->count(5)->create();

        /** @var User */
        $user = User::find($this->faker->numberBetween(1, User::count()));

        $this->mockedRequestMethods['getUserModel']
            ->once()
            ->withNoArgs()
            ->andReturn($user);


        // Act
        $result = $this->core->get($this->mockRequest);


        // Assert
        $this->assertEquals(
            $user->replicate()->refresh(),
            $result->replicate()->refresh()
        );
    }
}