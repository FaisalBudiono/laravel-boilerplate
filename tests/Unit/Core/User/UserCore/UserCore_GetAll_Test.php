<?php

declare(strict_types=1);

namespace Tests\Unit\Core\User\UserCore;

use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Core\Query\Enum\OrderDirection;
use App\Core\User\Enum\UserExceptionCode;
use App\Core\User\Policy\UserPolicyContract;
use App\Core\User\Query\UserOrderBy;
use App\Exceptions\Core\Auth\Permission\InsufficientPermissionException;
use App\Models\User\User;
use App\Port\Core\User\GetAllUserPort;
use Illuminate\Support\Facades\DB;
use Mockery\MockInterface;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class UserCore_GetAll_Test extends UserCoreBaseTestCase
{
    protected GetAllUserPort|MockInterface $mockRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRequest = $this->mock(GetAllUserPort::class);

        $this->mock(UserPolicyContract::class);
    }

    #[Test]
    public function should_throw_insufficient_permission_exception_when_denied_by_policy(): void
    {
        // Arrange
        $this->mockRequest->shouldReceive('getUserActor')->once()->andReturn(
            $userActor = User::factory()->create(),
        );

        $this->mock(
            UserPolicyContract::class,
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
            $expectedException = new InsufficientPermissionException(new ExceptionMessageStandard(
                'Insufficient permission to see all users',
                UserExceptionCode::PERMISSION_INSUFFICIENT->value,
            ));
            $this->assertEquals($expectedException, $e);
        }
    }

    #[Test]
    #[DataProvider('inputDataProvider')]
    public function should_successfully_return_users(
        int $totalCreatedUser,
        ?UserOrderBy $mockedOrderBy,
        ?OrderDirection $mockedOrderDir,
        ?int $mockedPage,
        ?int $mockedPerPage,
        array $expectedBuilderMethods,
    ): void {
        // Arrange
        $this->makeMockUsers($totalCreatedUser);

        $this->mockRequest->shouldReceive('getUserActor')->once()->andReturn(
            $userActor = User::factory()->create(),
        );
        $this->mockRequest->shouldReceive('getOrderBy')->once()->andReturn($mockedOrderBy);
        $this->mockRequest->shouldReceive('getOrderDirection')->once()->andReturn($mockedOrderDir);
        $this->mockRequest->shouldReceive('getPage')->once()->andReturn($mockedPage);
        $this->mockRequest->shouldReceive('getPerPage')->once()->andReturn($mockedPerPage);

        $this->mock(
            UserPolicyContract::class,
            function (MockInterface $mock) use ($userActor) {
                $mock->shouldReceive('seeAll')->once()->with($userActor)->andReturn(true);
            }
        );


        // Act
        $results = $this->makeService()->getAll($this->mockRequest);


        // Assert
        $builder = DB::connection();

        collect($expectedBuilderMethods)->each(function (array $arg) use (&$builder) {
            $builder = $builder->{$arg[0]}(...$arg[1]);
        });
        $expectedResults = $builder->get();

        $this->assertSame($mockedPerPage ?? 30, $results->perPage());
        $this->assertSame($mockedPage ?? 1, $results->currentPage());

        collect($results->items())->each(function (
            User $user,
            int $index
        ) use ($expectedResults) {
            $this->assertSame($expectedResults[$index]->id, $user->id);
        });
    }

    public static function inputDataProvider(): array
    {
        return [
            'no input given' => [
                1,
                null,
                null,
                null,
                null,
                [
                    ['table', ['users']],
                    ['orderByDesc', ['created_at']],
                    ['limit', [30]],
                ],
            ],
            '15 per page 2nd page' => [
                31,
                null,
                null,
                $page = 2,
                $perPage = 15,
                [
                    ['table', ['users']],
                    ['orderByDesc', ['created_at']],
                    ['limit', [$perPage]],
                    ['offset', [self::calculateOffset($page, $perPage)]],
                ],
            ],
            '3 per page 2nd page order by email ascending' => [
                10,
                UserOrderBy::EMAIL,
                OrderDirection::ASCENDING,
                $page = 2,
                $perPage = 3,
                [
                    ['table', ['users']],
                    ['orderBy', ['email']],
                    ['limit', [$perPage]],
                    ['offset', [self::calculateOffset($page, $perPage)]],
                ],
            ],
        ];
    }

    protected function makeMockUsers(int $numberOfData): void
    {
        User::factory()->count($numberOfData)->create()->each(function (User $user) {
            $user->created_at = now()->addMinutes($user->id);
            $user->save();
        });
    }

    protected static function calculateOffset(int $page, int $perPage): int
    {
        return ($page - 1) * $perPage;
    }
}
