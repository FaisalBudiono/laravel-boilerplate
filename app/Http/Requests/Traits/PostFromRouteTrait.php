<?php

declare(strict_types=1);

namespace App\Http\Requests\Traits;

use App\Models\Post\Post;

trait PostFromRouteTrait
{
    protected ?Post $postFromRoute = null;

    protected function getPostFromRoute(): Post
    {
        if (is_null($this->postFromRoute)) {
            $this->postFromRoute = $this->route('postID');
        }

        return $this->postFromRoute;
    }
}
