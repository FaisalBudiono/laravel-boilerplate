<?php

declare(strict_types=1);

namespace App\Port\Core\User;

use App\Core\Query\Enum\OrderDirection;
use App\Core\User\Query\UserOrderBy;
use App\Port\Core\NeedActorPort;

interface GetAllUserPort extends NeedActorPort
{
    public function getOrderBy(): ?UserOrderBy;
    public function getOrderDirection(): ?OrderDirection;
    public function getPage(): ?int;
    public function getPerPage(): ?int;
}
