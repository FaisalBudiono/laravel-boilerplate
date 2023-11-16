<?php

namespace Tests\Unit\Core\Post\PostCore;

use App\Models\Permission\Enum\RoleName;
use App\Models\Permission\Role;
use App\Models\Post\Post;
use App\Models\User\User;
use App\Port\Core\Post\GetAllPostPort;
use Illuminate\Support\Collection;
use Mockery\MockInterface;
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
    }

    #[Test]
    #[DataProvider('perPageDataProvider')]
    public function should_return_posts_with_total_of_per_page(
        ?int $perPage,
        int $expectedPerPage,
    ): void {
        // Arrange
        $this->mockRequest->shouldReceive('getPerPage')->once()->andReturn($perPage);
        $this->mockRequest->shouldReceive('getPage')->once()->andReturn(1);
        $this->mockRequest->shouldReceive('getUserFilter')->once();

        $mockedAdminRole = Role::factory()->create([
            'name' => RoleName::ADMIN,
        ]);
        $mockedUser = User::factory()->create();
        $mockedUser->syncRoles($mockedAdminRole);
        $this->mockRequest->shouldReceive('getUserActor')->once()->andReturn($mockedUser);


        // Act
        $results = $this->makeService()->getAll($this->mockRequest);


        // Assert
        $this->assertSame(Post::all()->count(), $results->total());
        $this->assertSame($expectedPerPage, $results->perPage());
        collect($results->items())->each(function (Post $post) {
            $this->assertLoadedRelationships($post);
        });
    }

    public static function perPageDataProvider(): array
    {
        return [
            'null per page' => [
                null,
                self::totalDefaultPerPage(),
            ],
            '20 per page' => [
                20,
                20,
            ],
            '43 per page' => [
                43,
                43,
            ],
        ];
    }

    #[Test]
    #[DataProvider('pageDataProvider')]
    public function should_return_posts_with_page_of(
        int $page,
    ): void {
        // Arrange
        $this->mockRequest->shouldReceive('getPerPage')->once()->andReturn(null);
        $this->mockRequest->shouldReceive('getPage')->once()->andReturn($page);
        $this->mockRequest->shouldReceive('getUserFilter')->once();

        $mockedAdminRole = Role::factory()->create([
            'name' => RoleName::ADMIN,
        ]);
        $mockedUser = User::factory()->create();
        $mockedUser->syncRoles($mockedAdminRole);
        $this->mockRequest->shouldReceive('getUserActor')->once()->andReturn($mockedUser);


        // Act
        $results = $this->makeService()->getAll($this->mockRequest);


        // Assert
        $this->assertSame(Post::all()->count(), $results->total());
        $this->assertSame($page, $results->currentPage());
        collect($results->items())->each(function (Post $post) {
            $this->assertLoadedRelationships($post);
        });
    }

    public static function pageDataProvider(): array
    {
        return [
            '1st' => [
                1,
            ],
            '2nd' => [
                2,
            ],
            '4th' => [
                4,
            ],
        ];
    }

    #[Test]
    #[DataProvider('userFilterDataProvider')]
    public function should_be_able_to_filter_by_user_when_role_is_admin(
        ?int $filterUserID,
        int $expectedTotal,
        array $expectedUserIDs,
    ): void {
        // Arrange
        $this->mockRequest->shouldReceive('getPerPage')->once()->andReturn(null);
        $this->mockRequest->shouldReceive('getPage')->once()->andReturn(1);

        $filterUser = is_null($filterUserID) ? null : User::findByIDOrFail($filterUserID);
        $this->mockRequest->shouldReceive('getUserFilter')->once()->andReturn($filterUser);

        $mockedAdminRole = Role::factory()->create([
            'name' => RoleName::ADMIN,
        ]);
        $mockedUser = User::factory()->create();
        $mockedUser->syncRoles($mockedAdminRole);
        $this->mockRequest->shouldReceive('getUserActor')->once()->andReturn($mockedUser);


        // Act
        $results = $this->makeService()->getAll($this->mockRequest);


        // Assert
        $this->assertSame($expectedTotal, $results->total());
        collect($results->items())->each(function (Post $post) use ($expectedUserIDs) {
            $this->assertContains($post->user_id, $expectedUserIDs);
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
                [3],
            ],
            'all user' => [
                null,
                self::totalPostsPerUser() * self::totalUser(),
                Collection::times(self::totalUser())->toArray(),
            ],
        ];
    }

    #[Test]
    #[DataProvider('userFilterDataProvider')]
    public function should_only_see_their_post_when_role_is_not_admin(
        ?int $filterUserID,
    ): void {
        // Arrange
        $this->mockRequest->shouldReceive('getPerPage')->once()->andReturn(null);
        $this->mockRequest->shouldReceive('getPage')->once()->andReturn(1);

        $filterUser = is_null($filterUserID) ? null : User::findByIDOrFail($filterUserID);
        $this->mockRequest->shouldReceive('getUserFilter')->once()->andReturn($filterUser);

        $mockedUser = User::findByIDOrFail(
            $this->faker->numberBetween(1, self::totalUser()),
        );
        $this->mockRequest->shouldReceive('getUserActor')->once()->andReturn($mockedUser);


        // Act
        $results = $this->makeService()->getAll($this->mockRequest);


        // Assert
        $this->assertSame(self::totalPostsPerUser(), $results->total());
        collect($results->items())->each(function (Post $post) use ($mockedUser) {
            $this->assertSame($post->user_id, $mockedUser->id);
            $this->assertLoadedRelationships($post);
        });
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
