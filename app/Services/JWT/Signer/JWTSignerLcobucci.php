<?php

declare(strict_types=1);

namespace App\Services\JWT\Signer;

use App\Services\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Services\JWT\Exceptions\InvalidSignatureException;
use App\Services\JWT\Exceptions\InvalidTimeRelatedClaimException;
use App\Services\JWT\Exceptions\JWTErrorCode;
use App\Services\JWT\ValueObject\Claims;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha512;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Token\Builder;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Validator;
use Psr\Clock\ClockInterface;

class JWTSignerLcobucci implements JWTSigner
{
    public function sign(Claims $claims): string
    {
        return $this->makeBuilder()->issuedAt($claims->issueAt->toImmutable())
            ->expiresAt($claims->expiredAt->toImmutable())
            ->canOnlyBeUsedAfter($claims->notBeforeAt->toImmutable())
            ->permittedFor($claims->audience)
            ->withClaim('user', [
                'id' => $claims->user->id,
                'email' => $claims->user->email,
            ])->getToken($this->makeSigner(), InMemory::plainText($this->getPrivateKey()))
            ->toString();
    }

    public function validate(string $token): void
    {
        $tokenObj = $this->formatToken($token);

        if (!$this->hasValidSignature($tokenObj)) {
            throw new InvalidSignatureException(new ExceptionMessageStandard(
                'JWT signature is invalid',
                JWTErrorCode::INVALID_SIGNATURE->value,
            ));
        }

        if (!$this->hasValidTimeRelatedClaims($tokenObj)) {
            throw new InvalidTimeRelatedClaimException(new ExceptionMessageStandard(
                'Time related claims is invalid (e.g. nbf, exp, iat)',
                JWTErrorCode::TIME_RELATED->value,
            ));
        }
    }

    protected function formatToken(string $token): Token
    {
        $parser = new Parser(new JoseEncoder());

        return $parser->parse($token);
    }

    protected function getPrivateKey(): string
    {
        return (string) config('jwt.rsa.access-token.private', '');
    }

    protected function getPublicKey(): string
    {
        return (string) config('jwt.rsa.access-token.public', '');
    }

    protected function hasValidSignature(Token $token): bool
    {
        return $this->makeValidator()->validate(
            $token,
            new SignedWith(
                $this->makeSigner(),
                InMemory::plainText($this->getPublicKey()),
            ),
        );
    }

    protected function hasValidTimeRelatedClaims(Token $token): bool
    {
        return $this->makeValidator()->validate(
            $token,
            new LooseValidAt(
                new class () implements ClockInterface {
                    public function now(): \DateTimeImmutable
                    {
                        return now()->toDateTimeImmutable();
                    }
                },
            ),
        );
    }

    protected function makeBuilder(): Builder
    {
        return new Builder(new JoseEncoder(), ChainedFormatter::default());
    }

    protected function makeSigner(): Signer
    {
        return new Sha512();
    }

    protected function makeValidator(): Validator
    {
        return new Validator();
    }
}
