<?php

declare(strict_types=1);

namespace App\Services\JWT\ValueObject;

use Carbon\Carbon;

readonly class Claims
{
    public function __construct(
        public ClaimsUser $user,
        public string $audience,
        public Carbon $issueAt,
        public Carbon $notBeforeAt,
        public Carbon $expiredAt,
    ) {}
}
