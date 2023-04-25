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
use App\Http\Resources\User\UserResource;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function __construct(
        protected UserCoreContract $core,
        protected LoggerMessageFormatterFactoryContract $loggerFormatter,
    ) {
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
        } catch (\Exception $e) {
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
