<?php

namespace App\Http\Controllers\User;

use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Core\Logger\Message\LoggerMessageFactoryContract;
use App\Core\User\UserCoreContract;
use App\Exceptions\Core\User\UserEmailDuplicatedException;
use App\Exceptions\Http\ConflictException;
use App\Exceptions\Http\InternalServerErrorException;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\DeleteUserRequest;
use App\Http\Requests\User\GetAllUserRequest;
use App\Http\Requests\User\GetMyInfoRequest;
use App\Http\Requests\User\GetUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\User\UserResource;
use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function __construct(
        protected UserCoreContract $core,
        protected LoggerMessageFactoryContract $logFormatter,
    ) {
    }

    public function destroy(DeleteUserRequest $request)
    {
        try {
            Log::info(
                $this->logFormatter->makeHTTPStart(
                    'Delete user endpoint',
                    [],
                ),
            );

            $this->core->delete($request);

            Log::info(
                $this->logFormatter->makeHTTPSuccess('Delete user endpoint'),
            );

            return response()->json([], Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            Log::error(
                $this->logFormatter->makeHTTPError($e)
            );
            throw new InternalServerErrorException(new ExceptionMessageGeneric);
        }
    }

    public function index(GetAllUserRequest $request)
    {
        try {
            Log::info(
                $this->logFormatter->makeHTTPStart(
                    'Get all user endpoint',
                    $request->toArray(),
                )
            );

            $users = $this->core->getAll($request);

            Log::info($this->logFormatter->makeHTTPSuccess('Get all user endpoint'));

            return UserResource::collection($users);
        } catch (Exception $e) {
            Log::error($this->logFormatter->makeHTTPError($e));
            throw new InternalServerErrorException(new ExceptionMessageGeneric);
        }
    }

    public function me(GetMyInfoRequest $request)
    {
        try {
            Log::info(
                $this->logFormatter->makeHTTPStart(
                    'Get my information',
                    [],
                ),
            );

            $user = $this->core->get($request);

            Log::info($this->logFormatter->makeHTTPSuccess('Get my information'),);

            return UserResource::make($user)
                ->response()
                ->setStatusCode(Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error($this->logFormatter->makeHTTPError($e));
            throw new InternalServerErrorException(new ExceptionMessageGeneric);
        }
    }

    public function show(GetUserRequest $request)
    {
        try {
            Log::info(
                $this->logFormatter->makeHTTPStart(
                    'Show user endpoint',
                    $request->toArray()
                ),
            );

            $user = $this->core->get($request);

            Log::info($this->logFormatter->makeHTTPSuccess('Show user endpoint'));

            return UserResource::make($user)
                ->response()
                ->setStatusCode(Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error($this->logFormatter->makeHTTPError($e));
            throw new InternalServerErrorException(new ExceptionMessageGeneric);
        }
    }

    public function store(CreateUserRequest $request)
    {
        try {
            Log::info(
                $this->logFormatter->makeHTTPStart(
                    'Create user endpoint',
                    $request->toArray(),
                ),
            );

            $user = $this->core->create($request);

            Log::info($this->logFormatter->makeHTTPSuccess('Create user endpoint'));

            return UserResource::make($user)
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
        } catch (UserEmailDuplicatedException $e) {
            Log::warning($this->logFormatter->makeHTTPError($e));
            throw new ConflictException($e->exceptionMessage);
        } catch (Exception $e) {
            Log::error($this->logFormatter->makeHTTPError($e));
            throw new InternalServerErrorException(new ExceptionMessageGeneric);
        }
    }

    public function update(UpdateUserRequest $request)
    {
        try {
            Log::info(
                $this->logFormatter->makeHTTPStart(
                    'Update user endpoint',
                    $request->toArray(),
                )
            );

            $user = $this->core->update($request);

            Log::info($this->logFormatter->makeHTTPSuccess('Update user endpoint'));

            return UserResource::make($user);
        } catch (UserEmailDuplicatedException $e) {
            Log::warning($this->logFormatter->makeHTTPError($e));
            throw new ConflictException($e->exceptionMessage);
        } catch (Exception $e) {
            Log::error($this->logFormatter->makeHTTPError($e));
            throw new InternalServerErrorException(new ExceptionMessageGeneric);
        }
    }
}
