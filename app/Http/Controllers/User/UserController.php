<?php

namespace App\Http\Controllers\User;

use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Core\Logger\MessageFormatter\LoggerMessageFormatterFactoryContract;
use App\Core\Logger\MessageFormatter\ProcessingStatus;
use App\Core\User\UserCoreContract;
use App\Exceptions\Core\User\UserEmailDuplicatedException;
use App\Exceptions\Http\ConflictException;
use App\Exceptions\Http\InternalServerErrorException;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\DeleteUserRequest;
use App\Http\Requests\User\GetAllUserRequest;
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
        protected LoggerMessageFormatterFactoryContract $loggerFormatter,
    ) {
    }

    public function destroy(DeleteUserRequest $request)
    {
        try {
            Log::info(
                $this->loggerFormatter->makeGeneric(
                    $request->getEndpointInfo(),
                    $request->getXRequestID(),
                    ProcessingStatus::BEGIN,
                    'Delete user endpoint',
                    [
                        'input' => $request->toArray(),
                    ],
                )->getMessage()
            );

            $this->core->delete($request);

            Log::info(
                $this->loggerFormatter->makeGeneric(
                    $request->getEndpointInfo(),
                    $request->getXRequestID(),
                    ProcessingStatus::SUCCESS,
                    'Delete user endpoint',
                    [],
                )->getMessage()
            );

            return response()->json([], Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            Log::error(
                $this->loggerFormatter->makeGeneric(
                    $request->getEndpointInfo(),
                    $request->getXRequestID(),
                    ProcessingStatus::ERROR,
                    $e->getMessage(),
                    [
                        'trace' => $e->getTrace(),
                    ],
                )->getMessage()
            );
            throw new InternalServerErrorException(new ExceptionMessageGeneric);
        }
    }

    public function index(GetAllUserRequest $request)
    {
        try {
            Log::info(
                $this->loggerFormatter->makeGeneric(
                    $request->getEndpointInfo(),
                    $request->getXRequestID(),
                    ProcessingStatus::BEGIN,
                    'Get all user endpoint',
                    [
                        'input' => $request->toArray(),
                    ],
                )->getMessage()
            );

            $users = $this->core->getAll($request);

            Log::info(
                $this->loggerFormatter->makeGeneric(
                    $request->getEndpointInfo(),
                    $request->getXRequestID(),
                    ProcessingStatus::SUCCESS,
                    'Get all user endpoint',
                    [],
                )->getMessage()
            );

            return UserResource::collection($users);
        } catch (Exception $e) {
            Log::error(
                $this->loggerFormatter->makeGeneric(
                    $request->getEndpointInfo(),
                    $request->getXRequestID(),
                    ProcessingStatus::ERROR,
                    $e->getMessage(),
                    [
                        'trace' => $e->getTrace(),
                    ],
                )->getMessage()
            );
            throw new InternalServerErrorException(new ExceptionMessageGeneric);
        }
    }

    public function show(GetUserRequest $request)
    {
        try {
            Log::info(
                $this->loggerFormatter->makeGeneric(
                    $request->getEndpointInfo(),
                    $request->getXRequestID(),
                    ProcessingStatus::BEGIN,
                    'Show user endpoint',
                    [
                        'input' => $request->toArray(),
                    ],
                )->getMessage()
            );

            $user = $this->core->get($request);

            Log::info(
                $this->loggerFormatter->makeGeneric(
                    $request->getEndpointInfo(),
                    $request->getXRequestID(),
                    ProcessingStatus::SUCCESS,
                    'Show user endpoint',
                    [],
                )->getMessage()
            );

            return UserResource::make($user)
                ->response()
                ->setStatusCode(Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error(
                $this->loggerFormatter->makeGeneric(
                    $request->getEndpointInfo(),
                    $request->getXRequestID(),
                    ProcessingStatus::ERROR,
                    $e->getMessage(),
                    [
                        'trace' => $e->getTrace(),
                    ],
                )->getMessage()
            );
            throw new InternalServerErrorException(new ExceptionMessageGeneric);
        }
    }

    public function store(CreateUserRequest $request)
    {
        try {
            Log::info(
                $this->loggerFormatter->makeGeneric(
                    $request->getEndpointInfo(),
                    $request->getXRequestID(),
                    ProcessingStatus::BEGIN,
                    'Create user endpoint',
                    [
                        'input' => $request->toArray(),
                    ],
                )->getMessage()
            );

            $user = $this->core->create($request);

            Log::info(
                $this->loggerFormatter->makeGeneric(
                    $request->getEndpointInfo(),
                    $request->getXRequestID(),
                    ProcessingStatus::SUCCESS,
                    'Create user endpoint',
                    [],
                )->getMessage()
            );

            return UserResource::make($user)
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
        } catch (UserEmailDuplicatedException $e) {
            Log::error(
                $this->loggerFormatter->makeGeneric(
                    $request->getEndpointInfo(),
                    $request->getXRequestID(),
                    ProcessingStatus::ERROR,
                    $e->getMessage(),
                    [
                        'trace' => $e->getTrace(),
                    ],
                )->getMessage()
            );
            throw new ConflictException($e->exceptionMessage);
        } catch (Exception $e) {
            Log::error(
                $this->loggerFormatter->makeGeneric(
                    $request->getEndpointInfo(),
                    $request->getXRequestID(),
                    ProcessingStatus::ERROR,
                    $e->getMessage(),
                    [
                        'trace' => $e->getTrace(),
                    ],
                )->getMessage()
            );
            throw new InternalServerErrorException(new ExceptionMessageGeneric);
        }
    }

    public function update(UpdateUserRequest $request)
    {
        try {
            Log::info(
                $this->loggerFormatter->makeGeneric(
                    $request->getEndpointInfo(),
                    $request->getXRequestID(),
                    ProcessingStatus::BEGIN,
                    'Update user endpoint',
                    [
                        'input' => $request->toArray(),
                    ],
                )->getMessage()
            );

            $user = $this->core->update($request);

            Log::info(
                $this->loggerFormatter->makeGeneric(
                    $request->getEndpointInfo(),
                    $request->getXRequestID(),
                    ProcessingStatus::SUCCESS,
                    'Update user endpoint',
                    [],
                )->getMessage()
            );

            return UserResource::make($user);
        } catch (UserEmailDuplicatedException $e) {
            Log::error(
                $this->loggerFormatter->makeGeneric(
                    $request->getEndpointInfo(),
                    $request->getXRequestID(),
                    ProcessingStatus::ERROR,
                    $e->getMessage(),
                    [
                        'trace' => $e->getTrace(),
                    ],
                )->getMessage()
            );
            throw new ConflictException($e->exceptionMessage);
        } catch (Exception $e) {
            Log::error(
                $this->loggerFormatter->makeGeneric(
                    $request->getEndpointInfo(),
                    $request->getXRequestID(),
                    ProcessingStatus::ERROR,
                    $e->getMessage(),
                    [
                        'trace' => $e->getTrace(),
                    ],
                )->getMessage()
            );
            throw new InternalServerErrorException(new ExceptionMessageGeneric);
        }
    }
}
