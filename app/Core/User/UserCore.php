<?php

namespace App\Core\User;

use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Exceptions\Core\User\UserEmailDuplicatedException;
use App\Models\User\Enum\UserExceptionCode;
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

            $isEmailExist = User::query()
                ->where('email', $request->getEmail())
                ->exists();
            if ($isEmailExist) {
                throw new UserEmailDuplicatedException(new ExceptionMessageStandard(
                    'Email is duplicated',
                    UserExceptionCode::DUPLICATED->value,
                ));
            }

            $user = new User;
            $user->name = $request->getName();
            $user->email = $request->getEmail();
            $user->password = Hash::make($request->getUserPassword());
            $user->save();

            DB::commit();

            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
