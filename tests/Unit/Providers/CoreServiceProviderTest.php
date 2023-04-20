<?php

namespace Tests\Unit\Providers;

use App\Providers\CoreServiceProvider;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;
use Tests\Unit\Providers\CoreBinder\CoreBinderTestCaseAbstract;
use Tests\Unit\Providers\CoreBinder\CoreBinderTestCaseUser;

class CoreServiceProviderTest extends TestCase
{
    /** @var Application|MockInterface */
    public $applicationMock;
    public CoreServiceProvider $serviceProvider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->applicationMock = Mockery::mock(Application::class);
        $this->serviceProvider = new CoreServiceProvider($this->applicationMock);
    }

    /**
     * @test
     */
    public function should_be_able_to_be_contructed()
    {
        // Assert
        $this->assertInstanceOf(ServiceProvider::class, $this->serviceProvider);
    }

    /**
     * @test
     */
    public function should_bind_core_service()
    {
        // Arrange
        $coreAssertionClassNames = [
            CoreBinderTestCaseUser::class,
        ];


        // Assert
        foreach ($coreAssertionClassNames as $className) {
            /** @var CoreBinderTestCaseAbstract */
            $coreAssertion = new $className($this);

            $coreAssertion->assertBind();
            $coreAssertion->assertMake();
        }


        // Act
        $this->serviceProvider->boot();
    }
}