<?php

declare(strict_types=1);

namespace Tests\Unit\Core\User\UserCore;

use App\Models\User\User;
use App\Port\Core\User\GetUserPort;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class UserCore_Get_Test extends UserCoreBaseTestCase
{
    protected GetUserPort|MockInterface $mockRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRequest = $this->mock(GetUserPort::class);
    }

    #[Test]
    public function should_return_user_model(): void
    {
        // Assert
        User::factory()->count(5)->create();

        $user = User::find($this->faker->numberBetween(1, User::count()));
        assert($user instanceof User);


        // Assert
        $this->mockRequest->shouldReceive('getUserModel')->once()->andReturn($user);


        // Act
        $result = $this->makeService()->get($this->mockRequest);


        // Assert
        $this->assertEquals(
            $user->replicate()->refresh(),
            $result->replicate()->refresh()
        );
    }
}
