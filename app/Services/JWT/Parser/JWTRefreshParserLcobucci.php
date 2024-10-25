<?php

declare(strict_types=1);

namespace App\Services\JWT\Parser;

use App\Services\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Services\JWT\Exceptions\FailedParsingException;
use App\Services\JWT\Exceptions\JWTErrorCode;
use App\Services\JWT\ValueObject\RefreshClaims;
use App\Services\JWT\ValueObject\RefreshClaimsUser;
use Carbon\Carbon;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\UnencryptedToken;

class JWTRefreshParserLcobucci implements JWTRefreshParser
{
    public function parse(string $token): RefreshClaims
    {
        try {
            $parsedToken = $this->makeParser()->parse($token);
            assert($parsedToken instanceof UnencryptedToken);

            $user = $parsedToken->claims()->get('user');
            $issueAt = $parsedToken->claims()->get('iat');
            $notBeforeAt = $parsedToken->claims()->get('nbf');

            return new RefreshClaims(
                user: new RefreshClaimsUser(
                    id: $user['id'] ?? '',
                    email: $user['email'] ?? '',
                ),
                audience: $parsedToken->claims()->get('aud')[0] ?? '',
                notBeforeAt: $this->getInvalidDateWhenNull($notBeforeAt),
                issueAt: $this->getInvalidDateWhenNull($issueAt),
                sessionPayload: $parsedToken->claims()->get('session')['payload'] ?? '',
            );
        } catch (\Throwable $e) {
            throw new FailedParsingException(new ExceptionMessageStandard(
                'Failed to decode JWT token',
                JWTErrorCode::FAILED_DECODING->value,
            ), $e);
        }
    }

    protected function createInvalidDate(): Carbon
    {
        return Carbon::parse('01-01-0000 00:00:00');
    }

    protected function getInvalidDateWhenNull(?\DateTimeImmutable $date): Carbon
    {
        return is_null($date)
            ? $this->createInvalidDate()
            : Carbon::createFromImmutable($date);
    }

    protected function makeParser(): Parser
    {
        return new Parser(new JoseEncoder());
    }
}
