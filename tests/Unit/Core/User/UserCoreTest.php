<?php

namespace Tests\Unit;

use App\Core\User\UserCore;
use App\Exceptions\Core\User\UserEmailDuplicatedException;
use App\Models\User\User;
use App\Port\Core\User\CreateUserPort;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Mockery\CompositeExpectation;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserCoreTest extends TestCase
{
    use RefreshDatabase;

    protected UserCore $core;

    protected CreateUserPort $mockRequest;
    /** @var CompositeExpectation[] */
    protected $mockedRequestMethods;

    protected function setUp(): void
    {
        parent::setUp();

        $this->core = new UserCore();

        $this->mockRequest = $this->mock(CreateUserPort::class, function (MockInterface $mock) {
            $this->mockedRequestMethods['getName'] = $mock->shouldReceive('getName');
            $this->mockedRequestMethods['getEmail'] = $mock->shouldReceive('getEmail');
            $this->mockedRequestMethods['getUserPassword'] = $mock->shouldReceive('getUserPassword');
        });
    }

    #[Test]
    public function create_should_save_user_data()
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
    public function create_should_throw_error_when_user_email_already_registered()
    {
        // Arrange
        $email = $this->faker->email();

        User::factory()->create([
            'email' => $email,
        ]);

        $this->mockedRequestMethods['getEmail']->andReturn($email);


        // Assert
        $this->expectException(UserEmailDuplicatedException::class);
        $this->expectExceptionMessage('Email is duplicated');


        // Act
        $this->core->create($this->mockRequest);
    }
}
