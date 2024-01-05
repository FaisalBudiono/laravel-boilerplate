<?php

declare(strict_types=1);

namespace Tests;

use Faker\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use LazilyRefreshDatabase;
    use WithFaker;

    protected function assertNestedRelationship(
        Model $model,
        string $relationship,
    ): void {
        $fullRelationships = collect(explode('.', $relationship));
        $currentRelationship = $fullRelationships->shift();

        $this->assertTrue(
            $model->relationLoaded($currentRelationship),
            "{$relationship} not loaded",
        );

        $haveMoreRelationship = $fullRelationships->count() > 0;
        if (!is_null($model->{$currentRelationship}) && $haveMoreRelationship) {
            $nextRelationship = $fullRelationships->implode('.');
            $this->assertNestedRelationship($model->{$currentRelationship}, $nextRelationship);
        }
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
