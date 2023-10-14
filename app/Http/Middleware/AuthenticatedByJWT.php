<?php

namespace App\Http\Middleware;

use App\Core\Auth\JWT\Parser\JWTParser;
use App\Core\Auth\JWT\Signer\JWTSigner;
use App\Core\Formatter\ExceptionErrorCode;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Exceptions\Core\Auth\JWT\JWTException;
use App\Exceptions\Http\UnauthorizedException;
use App\Models\User\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticatedByJWT
{
    public function __construct(
        protected JWTSigner $signer,
        protected JWTParser $parser,
    ) {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, \Closure $next): Response
    {
        $authHeader = $request->headers->get('authorization', '');

        if (!$this->isTypeBearer($this->fetchTokenType($authHeader))) {
            throw new UnauthorizedException(new ExceptionMessageStandard(
                'Authentication is needed to proceed',
                ExceptionErrorCode::AUTHENTICATION_NEEDED->value,
            ));
        }

        $this->validateToken($this->fetchBearerToken($authHeader));

        return $next($request);
    }

    protected function fetchBearerToken(string $authHeader): string
    {
        $arrayed = explode(' ', $authHeader);
        $tokenInArray = array_slice($arrayed, 1, count($arrayed));

        return implode(' ', $tokenInArray);
    }

    protected function fetchTokenType(string $authHeader): string
    {
        return explode(' ', $authHeader)[0] ?? '';
    }

    protected function isTypeBearer(string $tokenType): bool
    {
        return strtolower($tokenType) === 'bearer';
    }

    protected function validateToken(string $token): void
    {
        try {
            $this->signer->validate($token);

            $claims = $this->parser->parse($token);
            User::findByIDOrFail($claims->user->id);
        } catch (JWTException $e) {
            throw new UnauthorizedException($e->exceptionMessage);
        } catch (\Throwable $e) {
            throw new UnauthorizedException(new ExceptionMessageStandard(
                'Failed to validate provided token',
                ExceptionErrorCode::AUTHENTICATION_NEEDED->value,
            ));
        }
    }
}
