<?php

declare(strict_types=1);

namespace Tests;

use Faker\Factory;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use WithFaker;

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
