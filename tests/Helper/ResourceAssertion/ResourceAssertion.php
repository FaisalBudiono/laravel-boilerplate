<?php

namespace Tests\Helper\ResourceAssertion;

use Illuminate\Testing\TestResponse;
use Tests\TestCase;

interface ResourceAssertion
{
    public function assertResource(TestCase $test, TestResponse $response): void;
}
