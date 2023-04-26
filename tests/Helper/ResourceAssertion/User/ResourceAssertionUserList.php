<?php

namespace Tests\Helper\ResourceAssertion\User;

use Illuminate\Testing\TestResponse;
use Tests\Helper\ResourceAssertion\ResourceAssertion;
use Tests\TestCase;

class ResourceAssertionUserList implements ResourceAssertion
{
    public function assertResource(TestCase $test, TestResponse $response): void
    {
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                ]
            ],
        ]);
    }
}
