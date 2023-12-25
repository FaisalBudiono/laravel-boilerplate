<?php

declare(strict_types=1);

namespace App\Core\Auth\JWT\Signer;

use App\Core\Auth\JWT\Signer\JWTSigner;
use App\Core\Auth\JWT\ValueObject\Claims;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Exceptions\Core\Auth\JWT\InvalidSignatureException;
use App\Exceptions\Core\Auth\JWT\InvalidTimeRelatedClaimException;
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
        return $this->makeBuilder()
            ->issuedAt($claims->issueAt->toImmutable())
            ->expiresAt($claims->expiredAt->toImmutable())
            ->canOnlyBeUsedAfter($claims->notBeforeAt->toImmutable())
            ->permittedFor(...$claims->audiences)
            ->withClaim('user', [
                'id' => $claims->user->id,
                'email' => $claims->user->userEmail,
            ])->getToken($this->makeSigner(), InMemory::plainText($this->getPrivateKey()))
            ->toString();
    }

    public function validate(string $token): void
    {
        if (!$this->hasValidSignature($this->formatToken($token))) {
            throw new InvalidSignatureException(new ExceptionMessageStandard(
                'JWT signature is invalid',
                JWTSignerExceptionCode::INVALID_SIGNATURE->value,
            ));
        }

        if (!$this->hasValidTimeRelatedClaims($this->formatToken($token))) {
            throw new InvalidTimeRelatedClaimException(new ExceptionMessageStandard(
                'Time related claims is invalid (e.g. nbf, exp, iat)',
                JWTSignerExceptionCode::TIME_RELATED->value,
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
        return config('jwt.key.rsa.private');
    }

    protected function getPublicKey(): string
    {
        return config('jwt.key.rsa.public');
    }

    protected function hasValidSignature(Token $token): bool
    {
        return $this->makeValidator()->validate(
            $token,
            new SignedWith($this->makeSigner(), InMemory::plainText($this->getPublicKey())),
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
                }
            )
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
