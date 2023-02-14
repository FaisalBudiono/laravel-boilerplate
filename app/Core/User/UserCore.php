<?php

namespace App\Core\User;

use App\Models\User\User;
use App\Port\Core\User\CreateUserPort;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserCore implements UserCoreInterface
{
    public function create(CreateUserPort $request): User
    {
        try {
            DB::beginTransaction();

            $user = new User;
            $user->name = $request->getName();
            $user->email = $request->getEmail();
            $user->password = Hash::make($request->getPassword());
            $user->save();

            DB::commit();

            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
