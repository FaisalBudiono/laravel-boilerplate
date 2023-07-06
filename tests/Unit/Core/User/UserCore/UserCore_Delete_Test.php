<?php

namespace Tests\Unit\Core\User\UserCore;

use App\Core\User\UserCore;
use App\Core\User\UserCoreContract;
use App\Models\User\User;
use App\Port\Core\User\DeleteUserPort;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserCore_Delete_Test extends TestCase
{
    use RefreshDatabase;

    protected UserCore $core;

    protected DeleteUserPort $mockRequest;
    /** @var (\Mockery\ExpectationInterface|\Mockery\Expectation|\Mockery\HigherOrderMessage)[] */
    protected $mockedRequestMethods;

    protected function setUp(): void
    {
        parent::setUp();

        $this->core = new UserCore();

        $this->mockRequest = $this->mock(DeleteUserPort::class, function (MockInterface $mock) {
            $this->getClassMethods(DeleteUserPort::class)->each(
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
    public function should_successfully_soft_delete_requested_user()
    {
        // Arrange
        $totalData = 5;
        User::factory()->count($totalData)->create();

        /** @var User */
        $user = User::find($this->faker()->numberBetween(1, User::count()));

        $this->mockedRequestMethods['getUserModel']->once()->withNoArgs()
            ->andReturn($user);


        // Act
        $this->core->delete($this->mockRequest);


        // Assert
        $this->assertSoftDeleted($user);
        $this->assertDatabaseCount('users', $totalData);
    }
}
