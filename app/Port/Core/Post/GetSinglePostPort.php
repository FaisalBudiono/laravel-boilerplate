<?php

declare(strict_types=1);

namespace App\Port\Core\Post;

use App\Models\Post\Post;
use App\Models\User\User;

interface GetSinglePostPort
{
    public function getUserActor(): User;

    public function getPost(): Post;
}
