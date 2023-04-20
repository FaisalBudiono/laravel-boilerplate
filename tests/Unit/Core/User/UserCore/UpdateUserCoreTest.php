<?php

namespace Tests\Unit\Core\User\UserCore;

use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Core\User\UserCore;
use App\Core\User\UserCoreContract;
use App\Exceptions\Core\User\UserEmailDuplicatedException;
use App\Models\User\Enum\UserExceptionCode;
use App\Models\User\User;
use App\Port\Core\User\UpdateUserPort;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateUserCoreTest extends TestCase
{
    use RefreshDatabase;

    protected UserCore $core;

    protected UpdateUserPort $mockRequest;
    /** @var (\Mockery\ExpectationInterface|\Mockery\Expectation|\Mockery\HigherOrderMessage)[] */
    protected $mockedRequestMethods;

    protected function setUp(): void
    {
        parent::setUp();

        $this->core = new UserCore();

        $this->mockRequest = $this->mock(UpdateUserPort::class, function (MockInterface $mock) {
            $this->getClassMethods(UpdateUserPort::class)->each(
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
    public function should_throw_user_email_duplicated_exception_when_email_is_used_by_other_user()
    {
        // Assert
        User::factory()->count(5)->create();
        /** @var User */
        $duplicatedUser = User::find(1);

        /** @var User */
        $user = User::find($this->faker()->numberBetween(2, User::count()));
        $this->mockedRequestMethods['getUserModel']->once()->withNoArgs()
            ->andReturn($user);

        $this->mockedRequestMethods['getEmail']->once()->withNoArgs()
            ->andReturn($duplicatedUser->email);


        // Assert
        $expectedException = new UserEmailDuplicatedException(new ExceptionMessageStandard(
            'Email is already in used',
            UserExceptionCode::DUPLICATED->value,
        ));
        $this->expectExceptionObject($expectedException);


        // Act
        $result = $this->core->update($this->mockRequest);


        // Assert
        $this->assertDatabaseHas('users', [
            'id' => $result->id,
            'name' => $user->name,
            'email' => $user->name,
            'password' => $user->password,
        ]);
    }

    #[Test]
    public function should_update_user_detail_successfully()
    {
        // Assert
        User::factory()->count(5)->create();

        /** @var User */
        $user = User::find($this->faker()->numberBetween(1, User::count()));
        $this->mockedRequestMethods['getUserModel']->once()->withNoArgs()
            ->andReturn($user);

        $email = $this->faker()->email();
        $this->mockedRequestMethods['getEmail']->once()->withNoArgs()
            ->andReturn($email);

        $name = $this->faker()->name();
        $this->mockedRequestMethods['getName']->once()->withNoArgs()
            ->andReturn($name);

        $password = $this->faker()->words(3, true);
        $this->mockedRequestMethods['getUserPassword']->once()->withNoArgs()
            ->andReturn($password);

        $mockedPassword = $this->faker()->words(4, true);
        Hash::shouldReceive('make')->with($password)->once()
            ->andReturn($mockedPassword);


        // Act
        $result = $this->core->update($this->mockRequest);


        // Assert
        $this->assertDatabaseHas('users', [
            'id' => $result->id,
            'name' => $name,
            'email' => $email,
            'password' => $mockedPassword,
        ]);
    }

    #[Test]
    public function should_update_user_detail_successfully_even_when_email_is_not_changed()
    {
        // Assert
        User::factory()->count(5)->create();

        /** @var User */
        $user = User::find($this->faker()->numberBetween(1, User::count()));
        $this->mockedRequestMethods['getUserModel']->once()->withNoArgs()
            ->andReturn($user);

        $this->mockedRequestMethods['getEmail']->once()->withNoArgs()
            ->andReturn($user->email);

        $name = $this->faker()->name();
        $this->mockedRequestMethods['getName']->once()->withNoArgs()
            ->andReturn($name);

        $password = $this->faker()->words(3, true);
        $this->mockedRequestMethods['getUserPassword']->once()->withNoArgs()
            ->andReturn($password);

        $mockedPassword = $this->faker()->words(4, true);
        Hash::shouldReceive('make')->with($password)->once()
            ->andReturn($mockedPassword);


        // Act
        $result = $this->core->update($this->mockRequest);


        // Assert
        $this->assertDatabaseHas('users', [
            'id' => $result->id,
            'name' => $name,
            'email' => $user->email,
            'password' => $mockedPassword,
        ]);
    }
}
