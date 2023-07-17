<?php

namespace Tests\Unit\Core\Auth\JWT\Signer;

use App\Core\Auth\JWT\Signer\JWTSignerExceptionCode;
use App\Core\Auth\JWT\Signer\JWTSignerLcobucci;
use App\Core\Auth\JWT\ValueObject\Claims;
use App\Core\Auth\JWT\ValueObject\ClaimsUser;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Exceptions\Core\Auth\JWT\InvalidSignatureException;
use App\Exceptions\Core\Auth\JWT\InvalidTimeRelatedClaimException;
use Carbon\Carbon;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha512;
use Lcobucci\JWT\Token\Builder;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class JWTSignerLcobucciTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $now = now();

        Carbon::setTestNow($now);
    }

    #[Test]
    public function sign_should_be_able_to_parse_token_correctly()
    {
        // Arrange
        $mockedClaim = new Claims(
            new ClaimsUser($this->faker->numerify, $this->faker->email),
            collect(
                $this->faker->words(3, true),
                $this->faker->words(3, true),
            ),
            Carbon::parse($this->faker->dateTime),
            Carbon::parse($this->faker->dateTime),
            Carbon::parse($this->faker->dateTime),
        );

        $parser = new Parser(new JoseEncoder);
        $validator = new Validator;


        // Act
        $result = $this->makeService()->sign($mockedClaim);


        // Assert
        $token  = $parser->parse($result);
        assert($token instanceof UnencryptedToken);

        $isValidSignedKey = $validator->validate(
            $token,
            new SignedWith(
                $this->makeSigner(),
                InMemory::plainText($this->getPublicKey()),
            ),
        );
        $this->assertTrue($isValidSignedKey, 'Signed key is not valid');

        $this->assertEquals($mockedClaim->issueAt, $token->claims()->get('iat'));
        $this->assertEquals(
            $mockedClaim->expiredAt->toImmutable(),
            $token->claims()->get('exp'),
        );
        $this->assertEquals(
            $mockedClaim->notBeforeAt->toImmutable(),
            $token->claims()->get('nbf'),
        );
        $this->assertEquals(
            $mockedClaim->audiences->toArray(),
            $token->claims()->get('aud'),
        );
        $this->assertEquals([
            'id' => $mockedClaim->user->id,
            'email' => $mockedClaim->user->userEmail,
        ], $token->claims()->get('user'));
    }

    #[Test]
    public function validate_should_throw_invalid_signature_exception_when_signature_is_not_matched()
    {
        // Arrange
        $invalidPrivateKey = "-----BEGIN PRIVATE KEY-----\nMIIEvwIBADANBgkqhkiG9w0BAQEFAASCBKkwggSlAgEAAoIBAQDWIIVKCpFc8E2C\nBAV9jstt7hliMm2m60UDrER3qzOEStntZg7FNITghweALbIG8i5KbBiNS3u1NIqW\n4zPAe0jxgzNfSUtzh8P8wtNZHsg+Uywwd9Xt1QlB4/WbxtmCZwcfrQdkD+4OP8rw\n/xmFISpF6S33sfhVvnyeUb6MqHzG3i4ufTqGnWdQAYkZh6giRC+j+CQL/nWJTHOz\n8zmh5BWXbQ16eyrUp8p9EplJDtV2JqgyE50senGs8G9IKKWZrBwnWd8HJS5yMJUf\ntQuRBsfHU+XJSJF87dXFvpL7AGWJY/01Qbp3U1x9ezHnaybYdILzyvYIrQW2mpvB\nOUpGKr4PAgMBAAECggEBAKOl0RiIQRZdlW8DccrG4lSOvxmcXs9OSb2H3//xePrn\nVeyorisre046BJKC2eeTGavJN25tPQt9L1ooJHo7/sCNvCpb0u1l2nSH1YzsCLAR\nUtlsDLSqt1uDREeczslpwjkEPXzM6+w59vj+jduAQFWT44zFmHy3i3hYEyBe+JXm\n++aIj/G2bBme9Bxof6Q8HZLSZlyPYjsfq3rtAR0ArNNSQ/BzktKsbBpyPWgqEvIO\nElZU6F+Ipa9+hV7VU4v8xRjRAw2bXdAYAZHMfXMtBg7zEGvBvDKhTa9gcaG6WRGf\nfhflZ8d+yKQGNR+hlai3duyvgGFOOc3TNQ08pIGsYfECgYEA9gDZ2MInF5hE0coB\nb3g9L7Q3j9DHoIH8pxMFzG4eVPbH7Z7KfYIYXGQvCMk/xlUMyjJKVmuEYmVcefZX\nMUrgBZoYy+cAjZgxjwdeSH/E64JlK/Sqp/pCylaIk2BqpsKDVdHuVIo5oIh/OtnS\namANnITOsjc5WuJy85kj+Z8lbpkCgYEA3tQQTq+vAoZC6iYi9VJTvSe2wnjA0qeH\ns5dki7sYX9aoomrvvydlTjMQ9kjtrI+aY5n3dOzcQKmSeL1bf45FOyoswjvo2g7M\nydiqGOj/8Pgpvz/NEpOIQHLNqlborIkU1vcdsTqGmu4TJfVkVJ8OfsJTLx6CRjdh\nwrOOhBq0wucCgYBsNduduWmwu04qag1Plzhy73cxT8lAFW2poHiAgD/fZ95x69Nu\nefd1TKxT1RK0j1zc5FpGwWyuS5/uFiosiJ8aV7poluhrYHMMU1Vp1qosXmNafnlD\nApa2onHZQiQnzpAvA/UuQs8uilxM6tvf5viVzOWPBzO3grzF4qssdpDkoQKBgQDI\nVk0bEaT9VgzPS95eRdh31j7gdYSXYHwHIXQKlPoDIJGZBR/r8tWICy2S4FqfrLSY\nHBN5koMrt8myuDyNYDIqUW7QauCdPHUufJfhsYp68gNGqWwM6Yu0tgLmxSCIDu8n\nniGZ+A6ROL8Kf6fm1OJJYRk84ecqjhxc2ualKwWdvQKBgQDtR4KAASmwZkfL6bgu\nHJx7Y7nKlTNRbjZJkJI04zjQ40yImOJ0cNM54fL8r8xJEz0tGGK0+qvmaPHEXRG5\naB+GTKvWg+fGRyS5dgTMjsRQQOkzXGEzlEyAUIO9DRA6d3r8HBlh5MHoyLyaekmg\nQMX7pxUPSoZZ6R4tzCLMfaSCCg==\n-----END PRIVATE KEY-----";

        $invalidToken = $this->makeBuilder()
            ->getToken($this->makeSigner(), InMemory::plainText($invalidPrivateKey));


        // Pre-Assert
        $expectedException = new InvalidSignatureException(new ExceptionMessageStandard(
            'JWT signature is invalid',
            JWTSignerExceptionCode::INVALID_SIGNATURE->value,
        ));
        $this->expectExceptionObject($expectedException);


        // Act
        $this->makeService()->validate($invalidToken->toString());
    }

    #[Test]
    #[DataProvider('invalidTimeClaimDataProvider')]
    public function validate_should_throw_invalid_signature_exception_when_time_related_claims_is_invalid(
        Carbon $mockedNotBeforeAt,
        Carbon $mockedIssueAt,
        Carbon $mockedExpiredAt,
    ) {
        // Arrange
        $invalidToken = $this->makeBuilder()
            ->canOnlyBeUsedAfter($mockedNotBeforeAt->toImmutable())
            ->issuedAt($mockedIssueAt->toImmutable())
            ->expiresAt($mockedExpiredAt->toImmutable())
            ->getToken($this->makeSigner(), InMemory::plainText($this->getPrivateKey()));


        // Pre-Assert
        $expectedException = new InvalidTimeRelatedClaimException(new ExceptionMessageStandard(
            'Time related claims is invalid (e.g. nbf, exp, iat)',
            JWTSignerExceptionCode::TIME_RELATED->value,
        ));
        $this->expectExceptionObject($expectedException);


        // Act
        $this->makeService()->validate($invalidToken->toString());
    }

    public static function invalidTimeClaimDataProvider(): array
    {
        return [
            'not before is still in the future' => [
                now()->addYear(),
                now()->subYear(),
                now()->addYear(),
            ],
            'issue at is in future' => [
                now()->subYear(),
                now()->addYear(),
                now()->addYear(),
            ],
            'already expired' => [
                now()->subYear(),
                now()->subYear(),
                now()->subYear(),
            ],
        ];
    }

    #[Test]
    public function validate_should_not_throw_anything_when_signature_and_time_related_claims_is_valid()
    {
        // Arrange
        $validToken = $this->makeBuilder()
            ->canOnlyBeUsedAfter(now()->subYear()->toImmutable())
            ->issuedAt(now()->subYear()->toImmutable())
            ->expiresAt(now()->addYear()->toImmutable())
            ->getToken($this->makeSigner(), InMemory::plainText($this->getPrivateKey()));


        // Pre-Assert
        $this->expectNotToPerformAssertions();


        // Act
        $this->makeService()->validate($validToken->toString());
    }

    protected function getPrivateKey(): string
    {
        return config('jwt.key.rsa.private');
    }

    protected function getPublicKey(): string
    {
        return config('jwt.key.rsa.public');
    }

    protected function makeBuilder(): Builder
    {
        return new Builder(new JoseEncoder, ChainedFormatter::default());
    }

    protected function makeSigner(): Signer
    {
        return new Sha512();
    }

    protected function makeService(): JWTSignerLcobucci
    {
        return new JWTSignerLcobucci;
    }
}
