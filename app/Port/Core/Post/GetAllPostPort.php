<?php

declare(strict_types=1);

namespace App\Port\Core\Post;

use App\Models\User\User;
use App\Port\Core\NeedActorPort;

interface GetAllPostPort extends NeedActorPort
{
    public function getPerPage(): ?int;
    public function getPage(): int;
    public function getUserFilter(): ?User;
}
