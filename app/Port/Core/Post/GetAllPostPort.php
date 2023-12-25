<?php

declare(strict_types=1);

namespace App\Port\Core\Post;

use App\Models\User\User;

interface GetAllPostPort
{
    public function getUserActor(): User;

    public function getPerPage(): ?int;
    public function getPage(): int;
    public function getUserFilter(): ?User;
}
