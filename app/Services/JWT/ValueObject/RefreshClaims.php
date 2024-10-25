<?php

declare(strict_types=1);

namespace App\Services\JWT\ValueObject;

use Carbon\Carbon;

readonly class RefreshClaims
{
    public function __construct(
        public RefreshClaimsUser $user,
        public string $audience,
        public Carbon $issueAt,
        public Carbon $notBeforeAt,
        public string $sessionPayload,
    ) {}
}
