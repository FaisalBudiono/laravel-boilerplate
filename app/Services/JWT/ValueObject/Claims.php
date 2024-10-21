<?php

declare(strict_types=1);

namespace App\Services\JWT\ValueObject;

use Carbon\Carbon;
use Illuminate\Support\Collection;

readonly class Claims
{
    /**
     * @var Collection<int,string> $audiences
     */
    public function __construct(
        public ClaimsUser $user,
        public string $audience,
        public Carbon $issueAt,
        public Carbon $notBeforeAt,
        public Carbon $expiredAt,
    ) {}
}
