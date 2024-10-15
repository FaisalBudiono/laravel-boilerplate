<?php

declare(strict_types=1);

namespace App\Services\Formatter;

use Illuminate\Support\Collection;

interface ExceptionMessage
{
    public function getJsonResponse(): Collection;
    public function getMessage(): string;
}
