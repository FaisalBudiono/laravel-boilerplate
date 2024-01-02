<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Post\PostCore;

use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Core\Post\Enum\PostExceptionCode;
use App\Core\Post\Policy\PostPolicyContract;
use App\Exceptions\Core\Auth\Permission\InsufficientPermissionException;
use App\Models\Post\Post;
use App\Models\User\User;
use App\Port\Core\Post\GetSinglePostPort;
use Mockery\MockInterface;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\Test;

class PostCore_Get_Test extends PostCoreBaseTestCase
{
    protected GetSinglePostPort|MockInterface $mockRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRequest = $this->mock(GetSinglePostPort::class);

        $this->mock(PostPolicyContract::class);
    }

    #[Test]
    public function should_throw_insufficient_permission_exception_when_denied_by_policy(): void
    {
        // Arrange
        $mockPost = $this->faker->randomElement(Post::factory()->count(10)->create());
        assert($mockPost instanceof Post);

        $this->mockRequest->shouldReceive('getUserActor')->once()->andReturn(
            $userActor = User::factory()->create(),
        );
        $this->mockRequest->shouldReceive('getPost')->once()->andReturn($mockPost);

        $this->mock(
            PostPolicyContract::class,
            function (MockInterface $mock) use ($userActor, $mockPost) {
                $mock->shouldReceive('see')->once()->with($userActor, $mockPost)->andReturn(false);
            }
        );


        try {
            // Act
            $this->makeService()->get($this->mockRequest);
            $this->fail('Should throw error');
        } catch (AssertionFailedError $e) {
            throw $e;
        } catch (\Throwable $e) {
            // Assert
            $expectedException = new InsufficientPermissionException(new ExceptionMessageStandard(
                'Insufficient permission to see post',
                PostExceptionCode::PERMISSION_INSUFFICIENT->value,
            ));
            $this->assertEquals($expectedException, $e);
        }
    }

    #[Test]
    public function should_successfully_return_post_with_relationship_loaded(): void
    {
        // Arrange
        $mockPost = $this->faker->randomElement(Post::factory()->count(10)->create());
        assert($mockPost instanceof Post);

        $this->mockRequest->shouldReceive('getUserActor')->once()->andReturn(
            $userActor = User::factory()->create(),
        );
        $this->mockRequest->shouldReceive('getPost')->once()->andReturn($mockPost);

        $this->mock(
            PostPolicyContract::class,
            function (MockInterface $mock) use ($userActor, $mockPost) {
                $mock->shouldReceive('see')->once()->with($userActor, $mockPost)->andReturn(true);
            }
        );


        // Act
        $result = $this->makeService()->get($this->mockRequest);


        // Assert
        $this->assertSame($mockPost->id, $result->id);
        $this->assertLoadedRelationships($result);
    }
}
