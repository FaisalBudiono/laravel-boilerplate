<?php

namespace App\Http\Middleware;

use App\Core\Auth\JWT\Parser\JWTParser;
use App\Core\Auth\JWT\Signer\JWTSigner;
use App\Core\Formatter\ExceptionErrorCode;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Exceptions\Http\UnauthorizedException;
use App\Models\User\User;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

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
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->headers->get('authentication', '');

        if (!$this->isTypeBearer($this->fetchTokenType($authHeader))) {
            $this->throwForbiddenException();
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

    protected function throwForbiddenException(?Throwable $e = null): never
    {
        throw new UnauthorizedException(new ExceptionMessageStandard(
            'Authentication is needed to proceed.',
            ExceptionErrorCode::AUTHENTICATION_NEEDED->value,
        ));
    }

    protected function validateToken(string $token): void
    {
        try {
            $this->signer->validate($token);

            $claims = $this->parser->parse($token);
            User::findByIdOrFail($claims->user->id);
        } catch (Exception $e) {
            $this->throwForbiddenException($e);
        }
    }
}
