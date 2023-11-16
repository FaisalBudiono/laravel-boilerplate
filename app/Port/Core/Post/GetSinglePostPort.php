<?php

namespace App\Port\Core\Post;

use App\Models\Post\Post;

interface GetSinglePostPort
{
    public function getPost(): Post;
}
