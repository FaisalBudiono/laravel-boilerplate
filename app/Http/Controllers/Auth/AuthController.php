<?php

namespace App\Http\Controllers\Auth;

use App\Core\Auth\AuthJWTCoreContract;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Core\Logger\Message\LoggerMessageFactoryContract;
use App\Exceptions\Core\Auth\InvalidCredentialException;
use App\Exceptions\Http\InternalServerErrorException;
use App\Exceptions\Http\UnauthorizedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Exception;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function __construct(
        protected AuthJWTCoreContract $core,
        protected LoggerMessageFactoryContract $logFormatter,
    ) {
    }

    public function login(LoginRequest $request)
    {
        try {
            Log::info(
                $this->logFormatter->makeHTTPStart(
                    'Login',
                    $request->toArray()
                ),
            );

            $tokenPair = $this->core->login($request);

            Log::info($this->logFormatter->makeHTTPSuccess('Login'));

            return response()->json([
                'data' => $tokenPair->toArray(),
            ]);
        } catch (InvalidCredentialException $e) {
            Log::warning($this->logFormatter->makeHTTPError($e));
            throw new UnauthorizedException($e->exceptionMessage);
        } catch (Exception $e) {
            Log::error($this->logFormatter->makeHTTPError($e));
            throw new InternalServerErrorException(new ExceptionMessageGeneric);
        }
    }
}
