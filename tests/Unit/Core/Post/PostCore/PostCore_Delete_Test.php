<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Post\PostCore;

use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Core\Post\Enum\PostExceptionCode;
use App\Core\Post\Policy\PostPolicyContract;
use App\Exceptions\Core\Auth\Permission\InsufficientPermissionException;
use App\Models\Post\Post;
use App\Models\User\User;
use App\Port\Core\Post\DeletePostPort;
use Mockery\MockInterface;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\Test;

class PostCore_Delete_Test extends PostCoreBaseTestCase
{
    protected DeletePostPort|MockInterface $mockRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRequest = $this->mock(DeletePostPort::class);

        $this->mock(PostPolicyContract::class);
    }

    #[Test]
    public function should_throw_insufficient_permission_exception_when_denied_by_policy(): void
    {
        // Arrange
        $totalPost = 10;
        $mockPost = $this->faker->randomElement(
            Post::factory()->count($totalPost)->create()
        );
        assert($mockPost instanceof Post);

        $this->mockRequest->shouldReceive('getUserActor')->once()->andReturn(
            $userActor = User::factory()->create(),
        );
        $this->mockRequest->shouldReceive('getPost')->once()->andReturn($mockPost);

        $this->mock(
            PostPolicyContract::class,
            function (MockInterface $mock) use ($userActor, $mockPost) {
                $mock->shouldReceive('delete')->once()->with($userActor, $mockPost)->andReturn(false);
            }
        );


        try {
            // Act
            $this->makeService()->delete($this->mockRequest);
            $this->fail('Should throw error');
        } catch (AssertionFailedError $e) {
            throw $e;
        } catch (\Throwable $e) {
            // Assert
            $expectedException = new InsufficientPermissionException(new ExceptionMessageStandard(
                'Insufficient permission to delete post',
                PostExceptionCode::PERMISSION_INSUFFICIENT->value,
            ));
            $this->assertEquals($expectedException, $e);

            $this->assertDatabaseCount('posts', $totalPost);
            $this->assertDatabaseHas('posts', [
                'id' => $mockPost->id,
                'deleted_at' => null,
            ]);
        }
    }

    #[Test]
    public function should_successfully_soft_delete_post(): void
    {
        // Arrange
        $totalPost = 10;
        $mockPost = $this->faker->randomElement(
            Post::factory()->count($totalPost)->create()
        );
        assert($mockPost instanceof Post);

        $this->mockRequest->shouldReceive('getUserActor')->once()->andReturn(
            $userActor = User::factory()->create(),
        );
        $this->mockRequest->shouldReceive('getPost')->once()->andReturn($mockPost);

        $this->mock(
            PostPolicyContract::class,
            function (MockInterface $mock) use ($userActor, $mockPost) {
                $mock->shouldReceive('delete')->once()->with($userActor, $mockPost)->andReturn(true);
            }
        );



        // Act
        $this->makeService()->delete($this->mockRequest);


        // Assert
        $this->assertDatabaseCount('posts', $totalPost);
        $this->assertSoftDeleted('posts', [
            'id' => $mockPost->id,
        ]);
    }

    #[Test]
    public function should_successfully_be_able_to_soft_delete_post_multiple_times(): void
    {
        // Arrange
        $totalPost = 10;
        $mockPost = $this->faker->randomElement(
            Post::factory()->count($totalPost)->create()
        );
        assert($mockPost instanceof Post);

        $this->mockRequest->shouldReceive('getUserActor')->atLeast()->once()->andReturn(
            $userActor = User::factory()->create(),
        );
        $this->mockRequest->shouldReceive('getPost')->atLeast()->once()->andReturn($mockPost);

        $this->mock(
            PostPolicyContract::class,
            function (MockInterface $mock) use ($userActor, $mockPost) {
                $mock->shouldReceive('delete')->atLeast()->once()->with($userActor, $mockPost)->andReturn(true);
            }
        );


        // Act
        $this->makeService()->delete($this->mockRequest);
        $this->makeService()->delete($this->mockRequest);
        $this->makeService()->delete($this->mockRequest);


        // Assert
        $this->assertDatabaseCount('posts', $totalPost);
        $this->assertSoftDeleted('posts', [
            'id' => $mockPost->id,
        ]);
    }
}
