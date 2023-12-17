<?php

namespace Tests\Unit\Core\User\UserCore;

use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Core\User\UserCore;
use App\Core\User\UserCoreContract;
use App\Exceptions\Core\User\UserEmailDuplicatedException;
use App\Models\User\Enum\UserExceptionCode;
use App\Models\User\User;
use App\Port\Core\User\CreateUserPort;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserCore_Create_Test extends TestCase
{
    use RefreshDatabase;

    protected UserCore $core;

    protected CreateUserPort|MockInterface $mockRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->core = new UserCore();

        $this->mockRequest = $this->mock(CreateUserPort::class);
    }

    #[Test]
    public function should_implement_user_core_contract(): void
    {
        // Assert
        $this->assertInstanceOf(UserCoreContract::class, $this->core);
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
        $result = $this->core->create($this->mockRequest);


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

        $expectedException = new UserEmailDuplicatedException(new ExceptionMessageStandard(
            'Email is duplicated',
            UserExceptionCode::DUPLICATED->value,
        ));
        $this->expectExceptionObject($expectedException);


        // Act
        $this->core->create($this->mockRequest);
    }
}
