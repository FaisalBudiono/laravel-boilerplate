<?php

namespace Tests\Helper;

use App\Core\Query\OrderDirection;
use Illuminate\Foundation\Testing\WithFaker;

class QueryDataProvider
{
    use WithFaker;

    public static function orderDirection(): array
    {
        return [
            'asc' => [
                OrderDirection::ASCENDING,
            ],
            'desc' => [
                OrderDirection::DESCENDING,
            ],
        ];
    }
}
