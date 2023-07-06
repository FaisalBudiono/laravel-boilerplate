<?php

namespace App\Core\Auth\JWT\ValueObject;

use Carbon\Carbon;
use Illuminate\Support\Collection;

readonly class Claims
{
    public function __construct(
        public ClaimsUser $user,
        public Collection $audiences,
        public Carbon $issueAt,
        public Carbon $notBeforeAt,
        public Carbon $expiredAt,
    ) {
    }
}
