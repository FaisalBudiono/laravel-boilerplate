<?php

declare(strict_types=1);

namespace App\Services\Auth\RefreshToken;

use App\Models\Auth\Login;
use App\Models\User\User;
use App\Services\Auth\RefreshToken\Exceptions\InvalidTokenException;
use App\Services\Auth\RefreshToken\Exceptions\RefreshTokenException;
use App\Services\Auth\ValueObject\RefreshToken;
use App\Services\Auth\ValueObject\RefreshTokenUser;
use App\Services\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Services\Formatter\Randomizer\Randomizer;
use App\Services\JWT\Exceptions\JWTErrorCode;
use App\Services\JWT\Exceptions\JWTException;
use App\Services\JWT\Parser\JWTRefreshParser;
use App\Services\JWT\Signer\JWTRefreshSigner;
use App\Services\JWT\ValueObject\RefreshClaims;

class RefreshTokenManagerPermanent implements RefreshTokenManager
{
    public function __construct(
        protected JWTRefreshSigner $signer,
        protected JWTRefreshParser $parser,
        protected Randomizer $randomizer,
    ) {}

    public function generate(
        User $user,
        string $ipAddress,
        string $userAgent,
        ?string $parentToken,
    ): string {
        $refreshPayload = $this->randomizer->getRandomizeString();

        $cred = new Login();
        $cred->user_id = $user->id;
        $cred->ip_address = $ipAddress;
        $cred->user_agent = $userAgent;
        $cred->payload = $refreshPayload;
        $cred->last_activity_at = now();
        $cred->save();

        return $this->signer->sign($user, $refreshPayload);
    }

    public function invalidate(string $token): void
    {
        $claims = $this->parser->parse($token);

        $cred = $this->findCredsByClaimsOrFail($claims);
        $cred->delete();
    }

    public function isValid(string $token): bool
    {
        try {
            $this->fetch($token);

            return true;
        } catch (RefreshTokenException) {
            return false;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function fetch(string $token): RefreshToken
    {
        try {
            $this->signer->validate($token);

            $claims = $this->parser->parse($token);
            $cred = $this->findCredsByClaimsOrFail($claims);

            return new RefreshToken(
                user: new RefreshTokenUser(
                    id: (string) $cred->user->id,
                    email: $cred->user->email,
                ),
                token: $claims->sessionPayload,
                parentToken: null,
            );
        } catch (JWTException $e) {
            throw new InvalidTokenException($e->exceptionMessage, $e);
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    protected function findCredsByClaimsOrFail(RefreshClaims $claims): Login
    {
        $cred = Login::with('user')
            ->where('user_id', $claims->user->id)
            ->where('payload', $claims->sessionPayload)
            ->first();
        if (is_null($cred)) {
            throw new InvalidTokenException(new ExceptionMessageStandard(
                'Refresh token is invalid',
                JWTErrorCode::GENERIC_INVALID->value,
            ));
        }

        return $cred;
    }
}
