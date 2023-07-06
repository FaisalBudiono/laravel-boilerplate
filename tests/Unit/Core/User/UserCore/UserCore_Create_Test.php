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

    protected CreateUserPort $mockRequest;
    /** @var (\Mockery\ExpectationInterface|\Mockery\Expectation|\Mockery\HigherOrderMessage)[] */
    protected $mockedRequestMethods;

    protected function setUp(): void
    {
        parent::setUp();

        $this->core = new UserCore();

        $this->mockRequest = $this->mock(CreateUserPort::class, function (MockInterface $mock) {
            $this->getClassMethods(CreateUserPort::class)->each(
                fn (string $methodName) =>
                $this->mockedRequestMethods[$methodName] = $mock->shouldReceive($methodName)
            );
        });
    }

    #[Test]
    public function should_implement_user_core_contract()
    {
        // Assert
        $this->assertInstanceOf(UserCoreContract::class, $this->core);
    }

    #[Test]
    public function should_successfully_save_and_return_user_data()
    {
        // Arrange
        $name = $this->faker->name;
        $email = $this->faker->email;
        $password = $this->faker->password;

        $this->mockedRequestMethods['getName']->andReturn($name);
        $this->mockedRequestMethods['getEmail']->andReturn($email);
        $this->mockedRequestMethods['getUserPassword']->andReturn($password);

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
    public function should_throw_error_when_user_email_already_registered()
    {
        // Arrange
        $email = $this->faker->email();

        User::factory()->create([
            'email' => $email,
        ]);

        $this->mockedRequestMethods['getEmail']->andReturn($email);


        // Assert
        $expectedException = new UserEmailDuplicatedException(new ExceptionMessageStandard(
            'Email is duplicated',
            UserExceptionCode::DUPLICATED->value,
        ));
        $this->expectExceptionObject($expectedException);


        // Act
        $this->core->create($this->mockRequest);
    }
}
