<?php

declare(strict_types=1);

namespace App\Core\User;

use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Core\Query\Enum\OrderDirection;
use App\Core\User\Query\UserOrderBy;
use App\Exceptions\Core\Auth\Permission\InsufficientPermissionException;
use App\Exceptions\Core\User\UserEmailDuplicatedException;
use App\Models\Permission\Enum\RoleName;
use App\Core\User\Enum\UserExceptionCode;
use App\Events\User\UserCreated;
use App\Models\User\User;
use App\Port\Core\User\CreateUserPort;
use App\Port\Core\User\DeleteUserPort;
use App\Port\Core\User\GetAllUserPort;
use App\Port\Core\User\GetUserPort;
use App\Port\Core\User\UpdateUserPort;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserCore implements UserCoreContract
{
    public function create(CreateUserPort $request): User
    {
        try {
            DB::beginTransaction();

            $email = $request->getEmail();

            $isEmailExist = User::query()
                ->where('email', $email)
                ->exists();
            if ($isEmailExist) {
                throw new UserEmailDuplicatedException(new ExceptionMessageStandard(
                    'Email is duplicated',
                    UserExceptionCode::DUPLICATED->value,
                ));
            }

            $user = new User();
            $user->name = $request->getName();
            $user->email = $email;
            $user->password = Hash::make($request->getUserPassword());
            $user->save();

            $user->syncRoles(RoleName::NORMAL);

            UserCreated::dispatch($user, $request->getRequestID());

            DB::commit();

            return $user;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete(DeleteUserPort $request): void
    {
        $user = $request->getUserModel();

        if ($request->getUserActor()->cannot('delete', $user)) {
            throw new InsufficientPermissionException(new ExceptionMessageStandard(
                'Insufficient permission to delete user',
                UserExceptionCode::PERMISSION_INSUFFICIENT->value,
            ));
        }

        $user->delete();
    }

    public function get(GetUserPort $request): User
    {
        $user = $request->getUserModel();
        if ($request->getUserActor()->cannot('see', $user)) {
            throw new InsufficientPermissionException(new ExceptionMessageStandard(
                'Insufficient permission to see user',
                UserExceptionCode::PERMISSION_INSUFFICIENT->value,
            ));
        }

        return $user;
    }

    public function getAll(GetAllUserPort $request): LengthAwarePaginator
    {
        if ($request->getUserActor()->cannot('seeAll', User::class)) {
            throw new InsufficientPermissionException(new ExceptionMessageStandard(
                'Insufficient permission to see all users',
                UserExceptionCode::PERMISSION_INSUFFICIENT->value,
            ));
        }

        $page = $request->getPage() ?? 1;
        $perPage = $request->getPerPage() ?? 30;
        $orderDirection = $request->getOrderDirection() ?? OrderDirection::DESCENDING;
        $orderBy = $request->getOrderBy() ?? UserOrderBy::CREATED_AT;

        return User::query()
            ->orderBy($orderBy->value, $orderDirection->value)
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function update(UpdateUserPort $request): User
    {
        try {
            DB::beginTransaction();

            $user = $request->getUserModel();
            if ($request->getUserActor()->cannot('update', $user)) {
                throw new InsufficientPermissionException(new ExceptionMessageStandard(
                    'Insufficient permission to update user',
                    UserExceptionCode::DUPLICATED->value,
                ));
            }

            $email = $request->getEmail();
            $isEmailExist = User::query()
                ->where('email', $email)
                ->where('id', '<>', $user->id)
                ->exists();
            if ($isEmailExist) {
                throw new UserEmailDuplicatedException(new ExceptionMessageStandard(
                    'Email is already in used',
                    UserExceptionCode::DUPLICATED->value,
                ));
            }

            $user->name = $request->getName();
            $user->email = $email;
            $user->password = Hash::make($request->getUserPassword());
            $user->save();

            DB::commit();

            return $user;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
