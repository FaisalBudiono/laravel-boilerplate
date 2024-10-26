<?php

declare(strict_types=1);

namespace App\Services\JWT\Signer;

use App\Models\User\User;
use App\Services\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Services\JWT\Exceptions\InvalidSignatureException;
use App\Services\JWT\Exceptions\JWTErrorCode;
use App\Services\JWT\Exceptions\FailedParsingException;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha512;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Token\Builder;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Validator;

class JWTRefreshSignerLcobucci implements JWTRefreshSigner
{
    public function sign(User $user, string $sessionPayload): string
    {
        return $this->makeBuilder()->issuedAt(now()->toDateTimeImmutable())
            ->canOnlyBeUsedAfter(now()->toDateTimeImmutable())
            ->permittedFor($this->getJWTAudience())
            ->withClaim('user', [
                'id' => (string) $user->id,
                'email' => $user->email,
            ])->withClaim('session', [
                'payload' => $sessionPayload,
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
    }

    protected function formatToken(string $token): Token
    {
        try {
            $parser = new Parser(new JoseEncoder());

            return $parser->parse($token);
        } catch (\Throwable $e) {
            throw new FailedParsingException(new ExceptionMessageStandard(
                'Failed to decode JWT token',
                JWTErrorCode::FAILED_DECODING->value,
            ), $e);
        }
    }

    protected function getJWTAudience(): string
    {
        return (string) config('jwt.aud', '');
    }

    protected function getPrivateKey(): string
    {
        return (string) config('jwt.rsa.refresh-token.private', '');
    }

    protected function getPublicKey(): string
    {
        return (string) config('jwt.rsa.refresh-token.public', '');
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
