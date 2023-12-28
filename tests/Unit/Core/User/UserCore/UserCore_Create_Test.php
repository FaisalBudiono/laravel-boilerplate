<?php

declare(strict_types=1);

namespace Tests\Unit\Core\User\UserCore;

use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Exceptions\Core\User\UserEmailDuplicatedException;
use App\Models\User\Enum\UserExceptionCode;
use App\Models\User\User;
use App\Port\Core\User\CreateUserPort;
use Illuminate\Support\Facades\Hash;
use Mockery\MockInterface;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\Test;

class UserCore_Create_Test extends UserCoreBaseTestCase
{
    protected CreateUserPort|MockInterface $mockRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRequest = $this->mock(CreateUserPort::class);
    }

    #[Test]
    public function should_successfully_save_and_return_user_data(): void
    {
        // Arrange
        $name = $this->faker->name;
        $email = $this->faker->email;
        $password = $this->faker->password;


        // Assert
        $this->mockRequest->shouldReceive('getName')->once()->andReturn($name);
        $this->mockRequest->shouldReceive('getEmail')->once()->andReturn($email);
        $this->mockRequest->shouldReceive('getUserPassword')->once()->andReturn($password);

        $hashedPassword = $this->faker->words(7, true);
        Hash::shouldReceive('make')->with($password)->andReturn($hashedPassword);


        // Act
        $result = $this->makeService()->create($this->mockRequest);


        // Assert
        $this->assertDatabaseCount('users', 1);

        $expectedResult = [
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword,
        ];
        $this->assertDatabaseHas('users', $expectedResult);
        $this->assertDatabaseHas($result, $expectedResult);
    }

    #[Test]
    public function should_throw_error_when_user_email_already_registered(): void
    {
        // Arrange
        $email = $this->faker->email();

        User::factory()->create([
            'email' => $email,
        ]);


        // Assert
        $this->mockRequest->shouldReceive('getEmail')->once()->andReturn($email);


        try {
            // Act
            $this->makeService()->create($this->mockRequest);
            $this->fail('Should throw error');
        } catch (AssertionFailedError $e) {
            throw $e;
        } catch (\Throwable $e) {
            $expectedException = new UserEmailDuplicatedException(new ExceptionMessageStandard(
                'Email is duplicated',
                UserExceptionCode::DUPLICATED->value,
            ));
            $this->assertEquals($expectedException, $e);
        }
    }
}
