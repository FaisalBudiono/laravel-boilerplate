<?php

declare(strict_types=1);

namespace App\Services\Formatter;

use Illuminate\Support\Collection;

interface ExceptionMessage
{
    /**
     * @return Collection<string, mixed>
     */
    public function getJsonResponse(): Collection;
    public function getMessage(): string;
}
