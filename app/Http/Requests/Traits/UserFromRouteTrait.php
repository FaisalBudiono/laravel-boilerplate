<?php

declare(strict_types=1);

namespace App\Http\Requests\Traits;

use App\Models\User\User;

trait UserFromRouteTrait
{
    protected ?User $userFromRouteUserID = null;

    protected function getUserFromRouteUserID(): User
    {
        if (is_null($this->postFromRoute)) {
            $this->userFromRouteUserID = $this->route('userID');
        }

        return $this->userFromRouteUserID;
    }
}
