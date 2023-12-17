<?php

namespace Tests\Unit\Core\User\UserCore;

use App\Models\User\User;
use App\Port\Core\User\DeleteUserPort;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class UserCore_Delete_Test extends UserCoreBaseTestCase
{
    use RefreshDatabase;

    protected DeleteUserPort|MockInterface $mockRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRequest = $this->mock(DeleteUserPort::class);
    }

    #[Test]
    public function should_successfully_soft_delete_requested_user(): void
    {
        // Arrange
        $totalData = 5;
        User::factory()->count($totalData)->create();

        /** @var User */
        $user = User::find($this->faker()->numberBetween(1, User::count()));


        // Assert
        $this->mockRequest->shouldReceive('getUserModel')->once()->andReturn($user);


        // Act
        $this->makeService()->delete($this->mockRequest);


        // Assert
        $this->assertSoftDeleted($user);
        $this->assertDatabaseCount('users', $totalData);
    }
}
