<?php

declare(strict_types=1);

namespace App\Exceptions\Http;

class NotFound extends AbstractHttpException
{
    protected function getStatusCode(): int
    {
        return 404;
    }
}
