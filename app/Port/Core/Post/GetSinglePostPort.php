<?php

declare(strict_types=1);

namespace App\Port\Core\Post;

use App\Models\Post\Post;

interface GetSinglePostPort
{
    public function getPost(): Post;
}
