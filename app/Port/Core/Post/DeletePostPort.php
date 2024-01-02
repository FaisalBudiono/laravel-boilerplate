<?php

declare(strict_types=1);

namespace App\Port\Core\Post;

use App\Models\Post\Post;
use App\Port\Core\NeedActorPort;

interface DeletePostPort extends NeedActorPort
{
    public function getPost(): Post;
}
