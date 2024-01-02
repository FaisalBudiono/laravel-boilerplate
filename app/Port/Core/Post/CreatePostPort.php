<?php

declare(strict_types=1);

namespace App\Port\Core\Post;

use App\Port\Core\NeedActorPort;

interface CreatePostPort extends NeedActorPort
{
    public function getTitle(): string;
    public function getPostContent(): ?string;
}
