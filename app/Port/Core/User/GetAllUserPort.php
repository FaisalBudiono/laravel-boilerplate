<?php

namespace App\Port\Core\User;

use App\Core\Query\OrderDirection;
use App\Core\User\Query\UserOrderBy;

interface GetAllUserPort
{
    public function getOrderBy(): ?UserOrderBy;
    public function getOrderDirection(): ?OrderDirection;
    public function getPage(): ?int;
    public function getPerPage(): ?int;
}