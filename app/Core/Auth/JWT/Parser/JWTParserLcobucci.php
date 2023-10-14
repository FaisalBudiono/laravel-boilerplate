<?php

namespace App\Core\Auth\JWT\Parser;

use App\Core\Auth\JWT\ValueObject\Claims;
use App\Core\Auth\JWT\ValueObject\ClaimsUser;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Exceptions\Core\Auth\JWT\FailedParsingException;
use Carbon\Carbon;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\UnencryptedToken;

class JWTParserLcobucci implements JWTParser
{
    public function parse(string $token): Claims
    {
        try {
            $parsedToken = $this->makeParser()->parse($token);
            assert($parsedToken instanceof UnencryptedToken);

            $user = $parsedToken->claims()->get('user');
            $invalidAt = $parsedToken->claims()->get('iat');
            $notBeforeAt = $parsedToken->claims()->get('nbf');
            $expiredAt = $parsedToken->claims()->get('exp');

            return new Claims(
                new ClaimsUser(
                    $user['id'] ?? '',
                    $user['email'] ?? '',
                ),
                collect($parsedToken->claims()->get('aud')),
                $this->getInvalidDateWhenNull($invalidAt),
                $this->getInvalidDateWhenNull($notBeforeAt),
                $this->getInvalidDateWhenNull($expiredAt),
            );
        } catch (\Throwable $e) {
            throw  new FailedParsingException(new ExceptionMessageStandard(
                'Failed to decode JWT token',
                JWTParserExceptionCode::FAILED_DECODING->value,
            ));
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
        return new Parser(new JoseEncoder);
    }
}
