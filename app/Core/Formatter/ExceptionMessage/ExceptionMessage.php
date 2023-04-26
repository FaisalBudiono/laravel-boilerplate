<?php

namespace App\Core\Formatter\ExceptionMessage;

use Illuminate\Support\Collection;

interface ExceptionMessage
{
    public function getJsonResponse(): Collection;
    public function getMessage(): string;
}
