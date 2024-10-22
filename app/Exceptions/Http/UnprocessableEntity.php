<?php

declare(strict_types=1);

namespace App\Exceptions\Http;

class UnprocessableEntity extends AbstractHttpException
{
    protected function getStatusCode(): int
    {
        return 422;
    }
}
