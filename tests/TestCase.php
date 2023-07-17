<?php

namespace Tests;

use Faker\Factory;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionMethod;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use WithFaker;

    protected function getClassMethods(string $classname): Collection
    {
        $reflection = new ReflectionClass($classname);
        return collect($reflection->getMethods())->map(fn (
            ReflectionMethod $reflectionMethod
        ) => $reflectionMethod->getName());
    }

    /**
     * Create a Faker instance for the given locale.
     *
     * @param  string|null  $locale
     * @return \Faker\Generator
     */
    protected static function makeFaker($locale = null)
    {
        return Factory::create($locale ?? Factory::DEFAULT_LOCALE);
    }
}
