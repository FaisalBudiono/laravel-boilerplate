<?php

declare(strict_types=1);

namespace Tests\Unit\Core\User\UserCore;

use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Core\User\Policy\UserPolicyContract;
use App\Exceptions\Core\Auth\Permission\InsufficientPermissionException;
use App\Exceptions\Core\User\UserEmailDuplicatedException;
use App\Core\User\Enum\UserExceptionCode;
use App\Models\User\User;
use App\Port\Core\User\UpdateUserPort;
use Illuminate\Support\Facades\Hash;
use Mockery\MockInterface;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class UserCore_Update_Test extends UserCoreBaseTestCase
{
    protected UpdateUserPort|MockInterface $mockRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRequest = $this->mock(UpdateUserPort::class);

        $this->mock(UserPolicyContract::class);
    }

    #[Test]
    public function should_throw_insufficient_permission_exception_when_denied_by_policy(): void
    {
        // Arrange
        User::factory()->count(5)->create();

        $userActor = User::find($this->faker()->numberBetween(2, User::count()));
        assert($userActor instanceof User);
        $user = User::find($this->faker()->numberBetween(2, User::count()));
        assert($user instanceof User);

        $this->mockRequest->shouldReceive('getUserActor')->once()->andReturn($userActor);
        $this->mockRequest->shouldReceive('getUserModel')->once()->andReturn($user);

        $this->mock(
            UserPolicyContract::class,
            function (MockInterface $mock) use ($user, $userActor) {
                $mock->shouldReceive('update')->once()->with($userActor, $user)->andReturn(false);
            }
        );


        try {
            // Act
            $this->makeService()->update($this->mockRequest);

            $this->fail('Should throw error');
        } catch (AssertionFailedError $e) {
            throw $e;
        } catch (\Throwable $e) {
            // Assert
            $expectedException = new InsufficientPermissionException(new ExceptionMessageStandard(
                'Insufficient permission to update user',
                UserExceptionCode::DUPLICATED->value,
            ));
            $this->assertEquals($expectedException, $e);

            $this->assertDatabaseHas('users', [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'password' => $user->password,
            ]);
        }
    }

    #[Test]
    public function should_throw_user_email_duplicated_exception_when_email_is_used_by_other_user(): void
    {
        // Arrange
        User::factory()->count(5)->create();

        $duplicatedUser = User::find(1);
        assert($duplicatedUser instanceof User);

        $userActor = User::find($this->faker()->numberBetween(2, User::count()));
        assert($userActor instanceof User);
        $user = User::find($this->faker()->numberBetween(2, User::count()));
        assert($user instanceof User);

        $this->mockRequest->shouldReceive('getUserActor')->once()->andReturn($userActor);
        $this->mockRequest->shouldReceive('getUserModel')->once()->andReturn($user);
        $this->mockRequest->shouldReceive('getEmail')->once()->andReturn($duplicatedUser->email);

        $this->mock(
            UserPolicyContract::class,
            function (MockInterface $mock) use ($user, $userActor) {
                $mock->shouldReceive('update')->once()->with($userActor, $user)->andReturn(true);
            }
        );



        try {
            // Act
            $this->makeService()->update($this->mockRequest);

            $this->fail('Should throw error');
        } catch (AssertionFailedError $e) {
            throw $e;
        } catch (\Throwable $e) {
            // Assert
            $expectedException = new UserEmailDuplicatedException(new ExceptionMessageStandard(
                'Email is already in used',
                UserExceptionCode::DUPLICATED->value,
            ));
            $this->assertEquals($expectedException, $e);

            $this->assertDatabaseHas('users', [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'password' => $user->password,
            ]);
        }
    }

    #[Test]
    #[DataProvider('inputDataProvider')]
    public function should_update_user_detail_successfully(
        string $emailBefore,
        string $emailAfter,
    ): void {
        // Arrange
        User::factory()->count(5)->create();

        $userActor = User::find($this->faker()->numberBetween(2, User::count()));
        assert($userActor instanceof User);

        $user = User::find($this->faker()->numberBetween(1, User::count()));
        assert($user instanceof User);
        $user->email = $emailBefore;
        $user->save();

        $name = $this->faker()->name();
        $password = $this->faker()->words(3, true);

        $this->mockRequest->shouldReceive('getUserActor')->once()->andReturn($userActor);
        $this->mockRequest->shouldReceive('getUserModel')->once()->andReturn($user);
        $this->mockRequest->shouldReceive('getEmail')->once()->andReturn($emailAfter);
        $this->mockRequest->shouldReceive('getName')->once()->andReturn($name);
        $this->mockRequest->shouldReceive('getUserPassword')->once()->andReturn($password);

        $mockedPassword = $this->faker()->words(4, true);
        Hash::shouldReceive('make')->with($password)->once()
            ->andReturn($mockedPassword);

        $this->mock(
            UserPolicyContract::class,
            function (MockInterface $mock) use ($user, $userActor) {
                $mock->shouldReceive('update')->once()->with($userActor, $user)->andReturn(true);
            }
        );



        // Act
        $result = $this->makeService()->update($this->mockRequest);


        // Assert
        $this->assertDatabaseHas('users', [
            'id' => $result->id,
            'name' => $name,
            'email' => $emailAfter,
            'password' => $mockedPassword,
        ]);
    }

    public static function inputDataProvider(): array
    {
        $faker = self::makeFaker();

        return [
            'change all data' => [
                $faker->email(),
                $faker->email(),
            ],
            'email did not change' => [
                $emailBefore = $faker->email(),
                $emailBefore,
            ],
        ];
    }
}
