<?php

namespace App\Core\User;

use App\Models\User\User;
use App\Port\Core\User\CreateUserPort;
use App\Port\Core\User\DeleteUserPort;
use App\Port\Core\User\GetAllUserPort;
use App\Port\Core\User\GetUserPort;
use App\Port\Core\User\UpdateUserPort;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserCoreContract
{
    public function create(CreateUserPort $request): User;
    public function delete(DeleteUserPort $request): void;
    public function get(GetUserPort $request): User;
    public function getAll(GetAllUserPort $request): LengthAwarePaginator;
    public function update(UpdateUserPort $request): User;
}
