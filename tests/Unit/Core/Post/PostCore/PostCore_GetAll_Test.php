<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Post\PostCore;

use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Core\Post\Enum\PostExceptionCode;
use App\Core\Post\Policy\PostPolicyContract;
use App\Exceptions\Core\Auth\Permission\InsufficientPermissionException;
use App\Models\Post\Post;
use App\Models\User\User;
use App\Port\Core\Post\GetAllPostPort;
use Mockery\MockInterface;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class PostCore_GetAll_Test extends PostCoreBaseTestCase
{
    protected GetAllPostPort|MockInterface $mockRequest;

    protected function setUp(): void
    {
        parent::setUp();

        User::factory()
            ->count(self::totalUser())
            ->has(Post::factory()->count(self::totalPostsPerUser()))
            ->create();

        $this->mockRequest = $this->mock(GetAllPostPort::class);

        $this->mock(PostPolicyContract::class);
    }

    #[Test]
    public function should_throw_insufficient_permission_exception_when_dont_have_enough_permission_to_see_all_post(): void
    {
        // Arrange
        $userActor = User::findByIDOrFail(1);

        $this->mockRequest->shouldReceive('getUserActor')->once()->andReturn($userActor);
        $this->mockRequest->shouldReceive('getUserFilter')->once()->andReturn(null);

        $this->mock(
            PostPolicyContract::class,
            function (MockInterface $mock) use ($userActor) {
                $mock->shouldReceive('seeAll')->once()->with($userActor)->andReturn(false);
            }
        );


        try {
            // Act
            $this->makeService()->getAll($this->mockRequest);
            $this->fail('Should throw error');
        } catch (AssertionFailedError $e) {
            throw $e;
        } catch (\Throwable $e) {
            // Assert
            $expectedException = new InsufficientPermissionException(new ExceptionMessageStandard(
                'Insufficient permission to fetch posts',
                PostExceptionCode::PERMISSION_INSUFFICIENT->value,
            ));
            $this->assertEquals($expectedException, $e);
        }
    }

    #[Test]
    public function should_throw_insufficient_permission_exception_when_dont_have_enough_permission_to_see_post_filtered_by_user(): void
    {
        // Arrange
        $userActor = User::findByIDOrFail(1);
        $userFilter = User::findByIDOrFail($this->faker->numberBetween(2, self::totalUser()));

        $this->mockRequest->shouldReceive('getUserActor')->once()->andReturn($userActor);
        $this->mockRequest->shouldReceive('getUserFilter')->once()->andReturn($userFilter);
        $this->mockRequest->shouldReceive('getPerPage')->once()->andReturn(null);

        $this->mock(
            PostPolicyContract::class,
            function (MockInterface $mock) use ($userActor, $userFilter) {
                $mock->shouldReceive('seeUserPost')->once()->with($userActor, $userFilter)->andReturn(false);
            }
        );


        try {
            // Act
            $this->makeService()->getAll($this->mockRequest);
            $this->fail('Should throw error');
        } catch (AssertionFailedError $e) {
            throw $e;
        } catch (\Throwable $e) {
            // Assert
            $expectedException = new InsufficientPermissionException(new ExceptionMessageStandard(
                'Insufficient permission to fetch posts',
                PostExceptionCode::PERMISSION_INSUFFICIENT->value,
            ));
            $this->assertEquals($expectedException, $e);
        }
    }

    #[Test]
    #[DataProvider('perPageDataProvider')]
    public function should_successfully_return_posts_with_total_of_per_page_and_page(
        ?int $perPage,
        int $expectedPerPage,
        int $page,
    ): void {
        // Arrange
        $this->mockRequest->shouldReceive('getPerPage')->once()->andReturn($perPage);
        $this->mockRequest->shouldReceive('getPage')->once()->andReturn($page);
        $this->mockRequest->shouldReceive('getUserFilter')->once()->andReturn(null);

        $this->mockRequest->shouldReceive('getUserActor')->once()->andReturn(
            $userActor = User::factory()->create()->fresh(),
        );

        $this->mock(
            PostPolicyContract::class,
            function (MockInterface $mock) use ($userActor) {
                $mock->shouldReceive('seeAll')->once()->with($userActor)->andReturn(true);
            }
        );


        // Act
        $results = $this->makeService()->getAll($this->mockRequest);


        // Assert
        $this->assertSame(Post::all()->count(), $results->total());
        $this->assertSame($expectedPerPage, $results->perPage());
        $this->assertSame($page, $results->currentPage());
        collect($results->items())->each(function (Post $post) {
            $this->assertLoadedRelationships($post);
        });
    }

    public static function perPageDataProvider(): array
    {
        return [
            'null per page and 2nd page' => [
                null,
                self::totalDefaultPerPage(),
                2,
            ],
            '20 per page and 1st page' => [
                20,
                20,
                1,
            ],
            '43 per page and 4th page' => [
                43,
                43,
                4,
            ],
        ];
    }

    #[Test]
    #[DataProvider('userFilterDataProvider')]
    public function should_successfully_return_posts_when_filtered_by_user(
        int $filterUserID,
        int $expectedTotal,
    ): void {
        // Arrange
        $this->mockRequest->shouldReceive('getPerPage')->once()->andReturn(null);
        $this->mockRequest->shouldReceive('getPage')->once()->andReturn(1);

        $userFilter = is_null($filterUserID) ? null : User::findByIDOrFail($filterUserID);
        $this->mockRequest->shouldReceive('getUserFilter')->once()->andReturn($userFilter);

        $this->mockRequest->shouldReceive('getUserActor')->once()->andReturn(
            $userActor = User::factory()->create(),
        );

        $this->mock(
            PostPolicyContract::class,
            function (MockInterface $mock) use ($userActor, $userFilter) {
                $mock->shouldReceive('seeUserPost')->once()->with($userActor, $userFilter)->andReturn(true);
            }
        );


        // Act
        $results = $this->makeService()->getAll($this->mockRequest);


        // Assert
        $this->assertSame($expectedTotal, $results->total());
        collect($results->items())->each(function (Post $post) use ($filterUserID) {
            $this->assertEquals($post->user_id, $filterUserID);
            $this->assertLoadedRelationships($post);
        });
    }

    public static function userFilterDataProvider(): array
    {
        return [
            '1st user' => [
                1,
                self::totalPostsPerUser(),
                [1],
            ],
            '3rd user' => [
                3,
                self::totalPostsPerUser(),
            ],
        ];
    }

    protected static function totalDefaultPerPage(): int
    {
        return 15;
    }

    protected static function totalUser(): int
    {
        return 5;
    }

    protected static function totalPostsPerUser(): int
    {
        return 10;
    }
}
