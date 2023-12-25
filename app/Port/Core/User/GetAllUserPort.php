<?php

declare(strict_types=1);

namespace App\Port\Core\User;

use App\Core\Query\Enum\OrderDirection;
use App\Core\User\Query\UserOrderBy;

interface GetAllUserPort
{
    public function getOrderBy(): ?UserOrderBy;
    public function getOrderDirection(): ?OrderDirection;
    public function getPage(): ?int;
    public function getPerPage(): ?int;
}
