<?php

namespace Tests\Unit\Providers\CoreBinder;

use Illuminate\Contracts\Foundation\Application;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;
use Tests\Unit\Providers\CoreServiceProviderTest;

abstract class CoreBinderTestCaseAbstract extends TestCase
{
    /** @var Application|MockInterface */
    protected $applicationMock;
    protected CoreServiceProviderTest $test;

    public function __construct(CoreServiceProviderTest $test)
    {
        $this->test = $test;
    }

    public function assertBind(): void
    {
        $abstractWithImplementClassNames = $this->abstractWithImplementationList();

        foreach ($abstractWithImplementClassNames as $abstractClassName => $implementorSpecs) {
            $this->test->applicationMock->shouldReceive('bind')
                ->once()
                ->with($abstractClassName, Mockery::on(function ($app) use ($implementorSpecs) {
                    $implementorClassName = $implementorSpecs[0];
                    $constructorClassNames = $implementorSpecs[1] ?? [];

                    $constructorArgs = collect($constructorClassNames)->map(
                        fn ($className) => $this->test->mock($className)
                    );

                    $service = new $implementorClassName(...$constructorArgs);

                    $this->test->assertEquals($service, $app($this->test->applicationMock));

                    return true;
                }))->andReturnNull();
        }
    }

    public function assertMake(): void
    {
        $abstractClassNames = array_keys($this->abstractWithImplementationList());

        foreach ($abstractClassNames as $className) {
            $this->test->applicationMock->shouldReceive('make')
                ->with(Mockery::on(function (string $argClassName) use ($className) {
                    try {
                        $this->test->assertSame($className, $argClassName);

                        return true;
                    } catch (\Throwable $th) {
                        return false;
                    }
                }))->andReturn($this->test->mock($className));
        }
    }

    abstract protected function abstractWithImplementationList(): array;
}
