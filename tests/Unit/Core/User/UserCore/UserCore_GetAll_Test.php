<?php

namespace Tests\Unit\Core\User\UserCore;

use App\Core\Query\OrderDirection;
use App\Core\User\Query\UserOrderBy;
use App\Models\User\User;
use App\Port\Core\User\GetAllUserPort;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helper\QueryDataProvider;

class UserCore_GetAll_Test extends UserCoreBaseTestCase
{
    use RefreshDatabase;

    protected GetAllUserPort|MockInterface $mockRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRequest = $this->mock(GetAllUserPort::class);
    }

    #[Test]
    public function should_return_30_latest_data_when_called_with_no_parameter(): void
    {
        // Arrange
        $this->makeMockUsers(35);


        // Assert
        $this->mockRequest->shouldReceive('getOrderBy')->once()->andReturnNull();
        $this->mockRequest->shouldReceive('getOrderDirection')->once()->andReturnNull();
        $this->mockRequest->shouldReceive('getPage')->once()->andReturnNull();
        $this->mockRequest->shouldReceive('getPerPage')->once()->andReturnNull();


        // Act
        $results = $this->makeService()->getAll($this->mockRequest);


        // Assert
        $expectedResults = DB::table('users')
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        $this->assertSame($expectedResults->count(), $results->perPage());
        $this->assertSame(1, $results->currentPage());

        collect($results->items())->each(function (
            User $user,
            int $index
        ) use ($expectedResults) {
            $this->assertSame($expectedResults[$index]->id, $user->id);
        });
    }

    #[Test]
    public function should_return_data_with_requested_per_page(): void
    {
        // Arrange
        $this->makeMockUsers(12);

        $perPage = 10;


        // Assert
        $this->mockRequest->shouldReceive('getOrderBy')->once()->andReturnNull();
        $this->mockRequest->shouldReceive('getOrderDirection')->once()->andReturnNull();
        $this->mockRequest->shouldReceive('getPage')->once()->andReturnNull();
        $this->mockRequest->shouldReceive('getPerPage')->once()->andReturn($perPage);


        // Act
        $results = $this->makeService()->getAll($this->mockRequest);


        // Assert
        $expectedResults = DB::table('users')
            ->orderByDesc('created_at')
            ->limit($perPage)
            ->get();

        $this->assertSame($perPage, $results->perPage());
        $this->assertSame(1, $results->currentPage());

        collect($results->items())->each(function (
            User $user,
            int $index
        ) use ($expectedResults) {
            $this->assertSame($expectedResults[$index]->id, $user->id);
        });
    }

    #[Test]
    public function should_return_data_with_requested_page(): void
    {
        // Arrange
        $this->makeMockUsers(65);

        $defaultPerPage = 30;
        $page = 3;


        // Assert
        $this->mockRequest->shouldReceive('getOrderBy')->once()->andReturnNull();
        $this->mockRequest->shouldReceive('getOrderDirection')->once()->andReturnNull();
        $this->mockRequest->shouldReceive('getPerPage')->once()->andReturnNull();
        $this->mockRequest->shouldReceive('getPage')->once()->andReturn($page);


        // Act
        $results = $this->makeService()->getAll($this->mockRequest);


        // Assert
        $expectedResults = DB::table('users')
            ->orderByDesc('created_at')
            ->limit($defaultPerPage)
            ->offset(60)
            ->get();

        $this->assertSame($defaultPerPage, $results->perPage());
        $this->assertSame($page, $results->currentPage());

        collect($results->items())->each(function (
            User $user,
            int $index
        ) use ($expectedResults) {
            $this->assertSame($expectedResults[$index]->id, $user->id);
        });
    }

    #[Test]
    #[DataProviderExternal(QueryDataProvider::class, 'orderDirection')]
    public function should_return_data_with_requested_order_direction(
        OrderDirection $orderDirection
    ): void {
        // Arrange
        $this->makeMockUsers(30);

        $defaultPerPage = 30;


        // Assert
        $this->mockRequest->shouldReceive('getOrderBy')->once()->andReturnNull();
        $this->mockRequest->shouldReceive('getPage')->once()->andReturnNull();
        $this->mockRequest->shouldReceive('getPerPage')->once()->andReturnNull();
        $this->mockRequest->shouldReceive('getOrderDirection')->once()->andReturn($orderDirection);


        // Act
        $results = $this->makeService()->getAll($this->mockRequest);


        // Assert
        $expectedResults = DB::table('users')
            ->select('id')
            ->orderBy('created_at', $orderDirection->value)
            ->limit($defaultPerPage)
            ->get();

        $this->assertSame($defaultPerPage, $results->perPage());
        $this->assertSame(1, $results->currentPage());

        collect($results->items())->each(function (
            User $user,
            int $index
        ) use ($expectedResults) {
            $this->assertSame($expectedResults[$index]->id, $user->id);
        });
    }

    #[Test]
    #[DataProvider('orderByDataProvider')]
    public function should_return_data_with_requested_order_by(
        UserOrderBy $orderBy
    ): void {
        // Arrange
        $this->makeMockUsers(30);

        $defaultPerPage = 30;


        // Assert
        $this->mockRequest->shouldReceive('getOrderDirection')->once()->andReturnNull();
        $this->mockRequest->shouldReceive('getPage')->once()->andReturnNull();
        $this->mockRequest->shouldReceive('getPerPage')->once()->andReturnNull();
        $this->mockRequest->shouldReceive('getOrderBy')->once()->andReturn($orderBy);


        // Act
        $results = $this->makeService()->getAll($this->mockRequest);


        // Assert
        $expectedResults = DB::table('users')
            ->select('id')
            ->orderBy($orderBy->value, 'desc')
            ->limit($defaultPerPage)
            ->get();

        $this->assertSame($defaultPerPage, $results->perPage());
        $this->assertSame(1, $results->currentPage());

        collect($results->items())->each(function (
            User $user,
            int $index
        ) use ($expectedResults) {
            $this->assertSame($expectedResults[$index]->id, $user->id);
        });
    }

    public static function orderByDataProvider(): array
    {
        return [
            'by name' => [
                UserOrderBy::NAME,
            ],
            'by email' => [
                UserOrderBy::EMAIL,
            ],
            'by created_at' => [
                UserOrderBy::CREATED_AT,
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
}
