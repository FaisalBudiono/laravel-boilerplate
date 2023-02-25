<?php

namespace Tests\Unit;

use App\Core\User\UserCore;
use App\Port\Core\User\CreateUserPort;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Mockery\MockInterface;
use Tests\TestCase;

class UserCoreTest extends TestCase
{
    use RefreshDatabase;

    protected UserCore $core;

    protected function setUp(): void
    {
        parent::setUp();

        $this->core = new UserCore();
    }

    /**
     * @test
     */
    public function create_should_save_user_data()
    {
        // Arrange
        $name = $this->faker->name;
        $email = $this->faker->email;
        $password = $this->faker->password;

        /** @var CreateUserPort */
        $mockRequest = $this->mock(CreateUserPort::class, function (MockInterface $mock) use ($name, $email, $password) {
            $mock->shouldReceive('getName')->andReturn($name);
            $mock->shouldReceive('getEmail')->andReturn($email);
            $mock->shouldReceive('getUserPassword')->andReturn($password);
        });

        $hashedPassword = $this->faker->words(7, true);
        Hash::shouldReceive('make')->with($password)->andReturn($hashedPassword);


        // Act
        $result = $this->core->create($mockRequest);


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
}
