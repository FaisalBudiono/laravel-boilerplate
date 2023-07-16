<?php

namespace App\Http\Controllers\User;

use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Core\Logger\Message\LoggerMessageFactoryContract;
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
        protected LoggerMessageFactoryContract $loggerMessage,
    ) {
    }

    public function destroy(DeleteUserRequest $request)
    {
        try {
            Log::info(
                $this->loggerMessage->makeHTTPStart(
                    'Delete user endpoint',
                    [],
                ),
            );

            $this->core->delete($request);

            Log::info(
                $this->loggerMessage->makeHTTPSuccess('Delete user endpoint'),
            );

            return response()->json([], Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            Log::error(
                $this->loggerMessage->makeHTTPError($e)
            );
            throw new InternalServerErrorException(new ExceptionMessageGeneric);
        }
    }

    public function index(GetAllUserRequest $request)
    {
        try {
            Log::info(
                $this->loggerMessage->makeHTTPStart(
                    'Get all user endpoint',
                    $request->toArray(),
                )
            );

            $users = $this->core->getAll($request);

            Log::info($this->loggerMessage->makeHTTPSuccess('Get all user endpoint'));

            return UserResource::collection($users);
        } catch (Exception $e) {
            Log::error($this->loggerMessage->makeHTTPError($e));
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
                $this->loggerMessage->makeHTTPStart(
                    'Create user endpoint',
                    $request->toArray(),
                ),
            );

            $user = $this->core->create($request);

            Log::info($this->loggerMessage->makeHTTPSuccess('Create user endpoint'));

            return UserResource::make($user)
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
        } catch (UserEmailDuplicatedException $e) {
            Log::warning($this->loggerMessage->makeHTTPError($e));
            throw new ConflictException($e->exceptionMessage);
        } catch (Exception $e) {
            Log::error($this->loggerMessage->makeHTTPError($e));
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
