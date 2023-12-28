<?php

declare(strict_types=1);

namespace Tests\Unit\Core\User\UserCore;

use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Exceptions\Core\User\UserEmailDuplicatedException;
use App\Models\User\Enum\UserExceptionCode;
use App\Models\User\User;
use App\Port\Core\User\UpdateUserPort;
use Illuminate\Support\Facades\Hash;
use Mockery\MockInterface;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\Test;

class UserCore_Update_Test extends UserCoreBaseTestCase
{
    protected UpdateUserPort|MockInterface $mockRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRequest = $this->mock(UpdateUserPort::class);
    }

    #[Test]
    public function should_throw_user_email_duplicated_exception_when_email_is_used_by_other_user(): void
    {
        // Arrange
        User::factory()->count(5)->create();

        $duplicatedUser = User::find(1);
        assert($duplicatedUser instanceof User);

        $user = User::find($this->faker()->numberBetween(2, User::count()));
        assert($user instanceof User);


        // Assert
        $this->mockRequest->shouldReceive('getUserModel')->once()->andReturn($user);
        $this->mockRequest->shouldReceive('getEmail')->once()->andReturn($duplicatedUser->email);


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
    public function should_update_user_detail_successfully(): void
    {
        // Arrange
        User::factory()->count(5)->create();
        $user = User::find($this->faker()->numberBetween(1, User::count()));
        assert($user instanceof User);

        $email = $this->faker()->email();
        $name = $this->faker()->name();
        $password = $this->faker()->words(3, true);


        // Assert
        $this->mockRequest->shouldReceive('getUserModel')->once()->andReturn($user);
        $this->mockRequest->shouldReceive('getEmail')->once()->andReturn($email);
        $this->mockRequest->shouldReceive('getName')->once()->andReturn($name);
        $this->mockRequest->shouldReceive('getUserPassword')->once()->andReturn($password);

        $mockedPassword = $this->faker()->words(4, true);
        Hash::shouldReceive('make')->with($password)->once()
            ->andReturn($mockedPassword);


        // Act
        $result = $this->makeService()->update($this->mockRequest);


        // Assert
        $this->assertDatabaseHas('users', [
            'id' => $result->id,
            'name' => $name,
            'email' => $email,
            'password' => $mockedPassword,
        ]);
    }

    #[Test]
    public function should_update_user_detail_successfully_even_when_email_is_not_changed(): void
    {
        // Assert
        User::factory()->count(5)->create();


        // Arrange
        $user = User::find($this->faker()->numberBetween(1, User::count()));
        assert($user instanceof User);

        $this->mockRequest->shouldReceive('getUserModel')->once()->andReturn($user);
        $this->mockRequest->shouldReceive('getEmail')->once()->andReturn($user->email);

        $name = $this->faker()->name();
        $this->mockRequest->shouldReceive('getName')->once()->andReturn($name);

        $password = $this->faker()->words(3, true);
        $this->mockRequest->shouldReceive('getUserPassword')->once()->andReturn($password);

        $mockedPassword = $this->faker()->words(4, true);
        Hash::shouldReceive('make')->with($password)->once()
            ->andReturn($mockedPassword);


        // Act
        $result = $this->makeService()->update($this->mockRequest);


        // Assert
        $this->assertDatabaseHas('users', [
            'id' => $result->id,
            'name' => $name,
            'email' => $user->email,
            'password' => $mockedPassword,
        ]);
    }
}
