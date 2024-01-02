<?php

declare(strict_types=1);

namespace Tests\Unit\Core\User\UserCore;

use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Core\User\Enum\UserExceptionCode;
use App\Core\User\Policy\UserPolicyContract;
use App\Exceptions\Core\Auth\Permission\InsufficientPermissionException;
use App\Models\User\User;
use App\Port\Core\User\DeleteUserPort;
use Mockery\MockInterface;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\Test;

class UserCore_Delete_Test extends UserCoreBaseTestCase
{
    protected DeleteUserPort|MockInterface $mockRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRequest = $this->mock(DeleteUserPort::class);

        $this->mock(UserPolicyContract::class);
    }

    #[Test]
    public function should_throw_insufficient_permission_exception_when_denied_by_policy(): void
    {
        // Arrange
        $totalData = 5;
        User::factory()->count($totalData)->create();

        $userActor = User::findByIDOrFail($this->faker()->numberBetween(1, User::count()));
        $user = User::findByIDOrFail($this->faker()->numberBetween(1, User::count()));

        $this->mockRequest->shouldReceive('getUserActor')->once()->andReturn($userActor);
        $this->mockRequest->shouldReceive('getUserModel')->once()->andReturn($user);

        $this->mock(
            UserPolicyContract::class,
            function (MockInterface $mock) use ($user, $userActor) {
                $mock->shouldReceive('delete')->once()->with($userActor, $user)->andReturn(false);
            }
        );


        try {
            // Act
            $this->makeService()->delete($this->mockRequest);
            $this->fail('Should throw error');
        } catch (AssertionFailedError $e) {
            throw $e;
        } catch (\Throwable $e) {
            // Assert
            $expectedException = new InsufficientPermissionException(new ExceptionMessageStandard(
                'Insufficient permission to delete user',
                UserExceptionCode::PERMISSION_INSUFFICIENT->value,
            ));
            $this->assertEquals($expectedException, $e);

            $this->assertDatabaseHas('users', [
                'id' => $user->id,
                'deleted_at' => null,
            ]);
            $this->assertDatabaseCount('users', $totalData);
        }
    }

    #[Test]
    public function should_successfully_soft_delete_requested_user(): void
    {
        // Arrange
        $totalData = 5;
        User::factory()->count($totalData)->create();

        $userActor = User::findByIDOrFail($this->faker()->numberBetween(1, User::count()));
        $user = User::findByIDOrFail($this->faker()->numberBetween(1, User::count()));

        $this->mockRequest->shouldReceive('getUserActor')->once()->andReturn($userActor);
        $this->mockRequest->shouldReceive('getUserModel')->once()->andReturn($user);

        $this->mock(
            UserPolicyContract::class,
            function (MockInterface $mock) use ($user, $userActor) {
                $mock->shouldReceive('delete')->once()->with($userActor, $user)->andReturn(true);
            }
        );


        // Act
        $this->makeService()->delete($this->mockRequest);


        // Assert
        $this->assertSoftDeleted($user);
        $this->assertDatabaseCount('users', $totalData);
    }
}
