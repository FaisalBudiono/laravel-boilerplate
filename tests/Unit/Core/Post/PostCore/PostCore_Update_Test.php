<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Post\PostCore;

use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Core\Post\Enum\PostExceptionCode;
use App\Core\Post\Policy\PostPolicyContract;
use App\Exceptions\Core\Auth\Permission\InsufficientPermissionException;
use App\Models\Post\Post;
use App\Models\User\User;
use App\Port\Core\Post\UpdatePostPort;
use Mockery\MockInterface;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class PostCore_Update_Test extends PostCoreBaseTestCase
{
    protected UpdatePostPort|MockInterface $mockRequest;

    protected Post $mockPost;

    protected function setUp(): void
    {
        parent::setUp();

        $mockPosts = Post::factory()->count(
            $this->faker->numberBetween(2, 10),
        )->create();
        $this->mockPost = $this->faker->randomElement($mockPosts);

        $this->mockRequest = $this->mock(UpdatePostPort::class);

        $this->mock(PostPolicyContract::class);
    }

    #[Test]
    public function should_throw_insufficient_permission_exception_when_denied_by_policy(): void
    {
        // Arrange
        $user = $this->faker()->randomElement(User::all());
        assert($user instanceof User);

        $this->mockRequest->shouldReceive('getUserActor')->once()->andreturn($user);
        $this->mockRequest->shouldReceive('getPost')->once()->andreturn($this->mockPost);

        $this->mock(
            PostPolicyContract::class,
            function (MockInterface $mock) use ($user) {
                $mock->shouldReceive('update')->once()->with($user, $this->mockPost)->andReturn(false);
            }
        );


        try {
            // Act
            $this->makeService()->update($this->mockRequest);
            $this->fail('Should throw error');
        } catch (AssertionFailedError $e) {
            throw $e;
        } catch (\Throwable $e) {
            // Assert
            $expectedException = new InsufficientPermissionException(new ExceptionMessageStandard(
                'Insufficient permission to update post',
                PostExceptionCode::PERMISSION_INSUFFICIENT->value,
            ));
            $this->assertEquals($expectedException, $e);

            $this->assertDatabaseHas('posts', [
                'id' => $this->mockPost->id,
                'title' => $this->mockPost->title,
                'content' => $this->mockPost->content,
            ]);
        }
    }

    #[Test]
    #[DataProvider('inputDataProvider')]
    public function should_successfully_update_post(
        string $title,
        ?string $content,
    ): void {
        // Arrange
        $user = $this->faker()->randomElement(User::all());
        assert($user instanceof User);

        $this->mockRequest->shouldReceive('getUserActor')->once()->andreturn($user);
        $this->mockRequest->shouldReceive('getPost')->once()->andreturn($this->mockPost);
        $this->mockRequest->shouldReceive('getTitle')->once()->andreturn($title);
        $this->mockRequest->shouldReceive('getPostContent')->once()->andreturn($content);

        $this->mock(
            PostPolicyContract::class,
            function (MockInterface $mock) use ($user) {
                $mock->shouldReceive('update')->once()->with($user, $this->mockPost)->andReturn(true);
            }
        );


        // Act
        $result = $this->makeService()->update($this->mockRequest);


        // Assert
        $this->assertDatabaseHas('posts', [
            'id' => $this->mockPost->id,
            'title' => $title,
            'content' => $content,
        ]);

        $this->assertLoadedRelationships($result);
    }

    public static function inputDataProvider(): array
    {
        $faker = self::makeFaker();

        return [
            'complete data' => [
                $faker->word(),
                $faker->sentence(),
            ],
            'content is null' => [
                $faker->word(),
                null,
            ],
        ];
    }
}
