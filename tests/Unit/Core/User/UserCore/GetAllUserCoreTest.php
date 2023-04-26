<?php

namespace Tests\Unit\Core\User\UserCore;

use App\Core\Query\OrderDirection;
use App\Core\User\Query\UserOrderBy;
use App\Core\User\UserCore;
use App\Core\User\UserCoreContract;
use App\Models\User\User;
use App\Port\Core\User\GetAllUserPort;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helper\QueryDataProvider;
use Tests\TestCase;

class GetAllUserCoreTest extends TestCase
{
    use RefreshDatabase;

    protected UserCore $core;

    protected GetAllUserPort $mockRequest;
    /** @var (\Mockery\ExpectationInterface|\Mockery\Expectation|\Mockery\HigherOrderMessage)[] */
    protected $mockedRequestMethods;

    protected function setUp(): void
    {
        parent::setUp();

        $this->core = new UserCore();

        $this->mockRequest = $this->mock(GetAllUserPort::class, function (MockInterface $mock) {
            $this->getClassMethods(GetAllUserPort::class)->each(
                function (string $methodName) use ($mock) {
                    $this->mockedRequestMethods[$methodName] = $mock->shouldReceive($methodName);
                }
            );
        });
    }

    #[Test]
    public function should_implement_right_interface()
    {
        // Assert
        $this->assertInstanceOf(UserCoreContract::class, $this->core);
    }

    #[Test]
    public function should_return_30_latest_data_when_called_with_no_parameter()
    {
        // Assert
        $this->makeMockUsers(35);

        $this->mockedRequestMethods['getPerPage']
            ->once()
            ->withNoArgs()
            ->andReturnNull();


        // Act
        $results = $this->core->getAll($this->mockRequest);


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
    public function should_return_data_with_requested_per_page()
    {
        // Assert
        $this->makeMockUsers(12);

        $perPage = 10;
        $this->mockedRequestMethods['getPerPage']
            ->once()
            ->withNoArgs()
            ->andReturn($perPage);


        // Act
        $results = $this->core->getAll($this->mockRequest);


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
    public function should_return_data_with_requested_page()
    {
        // Assert
        $this->makeMockUsers(65);

        $defaultPerPage = 30;
        $page = 3;
        $this->mockedRequestMethods['getPage']
            ->once()
            ->withNoArgs()
            ->andReturn($page);


        // Act
        $results = $this->core->getAll($this->mockRequest);


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
    ) {
        // Assert
        $this->makeMockUsers(30);

        $defaultPerPage = 30;

        $this->mockedRequestMethods['getOrderDirection']
            ->once()
            ->withNoArgs()
            ->andReturn($orderDirection);


        // Act
        $results = $this->core->getAll($this->mockRequest);


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
    ) {
        // Assert
        $this->makeMockUsers(30);

        $defaultPerPage = 30;

        $this->mockedRequestMethods['getOrderBy']
            ->once()
            ->withNoArgs()
            ->andReturn($orderBy);


        // Act
        $results = $this->core->getAll($this->mockRequest);


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
