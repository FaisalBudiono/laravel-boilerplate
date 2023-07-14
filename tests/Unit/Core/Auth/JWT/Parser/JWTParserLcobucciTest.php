<?php

namespace Tests\Unit\Core\Auth\JWT\Parser;

use App\Core\Auth\JWT\Parser\JWTParserExceptionCode;
use App\Core\Auth\JWT\Parser\JWTParserLcobucci;
use App\Core\Auth\JWT\ValueObject\Claims;
use App\Core\Auth\JWT\ValueObject\ClaimsUser;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Exceptions\Core\Auth\JWT\FailedParsingException;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Builder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class JWTParserLcobucciTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $now = now();

        Carbon::setTestNow($now);
    }

    #[Test]
    #[DataProvider('invalidJwtTokenDataProvider')]
    public function parse_should_throw_failed_parsing_exception_when_jwt_is_not_parsable(string $invalidToken)
    {
        // Arrange
        $service = $this->makeService();


        // Pre-Assert
        $expectedException = new FailedParsingException(new ExceptionMessageStandard(
            'Failed to decode JWT token',
            JWTParserExceptionCode::FAILED_DECODING->value,
        ));
        $this->expectExceptionObject($expectedException);


        // Act
        $service->parse($invalidToken);
    }

    public static function invalidJwtTokenDataProvider(): array
    {
        $token = self::makeBuilder()
            ->canOnlyBeUsedAfter(now()->subYear()->toImmutable())
            ->issuedAt(now()->subYear()->toImmutable())
            ->expiresAt(now()->addYear()->toImmutable())
            ->getToken(new Sha256, InMemory::plainText(random_bytes(32)))
            ->toString();

        $invalidHeader = Str::substr($token, 1, Str::length($token));

        $separatedToken = explode('.', $token);
        $invalidClaims = implode(
            '.',
            [
                $separatedToken[0],
                Str::substr($separatedToken[1], 1, Str::length($separatedToken[1])),
                $separatedToken[2],
            ],
        );

        return [
            'header is not parsable' => [
                $invalidHeader,
            ],
            'claims is not parsable' => [
                $invalidClaims,
            ],
        ];
    }

    #[Test]
    #[DataProvider('validJwtTokenDataProvider')]
    public function parse_should_return_claim_for_provided_token(
        string $mockedToken,
        Claims $expectedClaim
    ) {
        // Arrange

        $service = $this->makeService();


        // Act
        $result = $service->parse($mockedToken);


        // Arrange
        $this->assertEquals($expectedClaim, $result,);
    }

    public static function validJwtTokenDataProvider(): array
    {
        $faker = self::makeFaker();

        $issueAt = Carbon::parse($faker->dateTime);
        $expiredAt = Carbon::parse($faker->dateTime);
        $notBeforeAt = Carbon::parse($faker->dateTime);
        $audiences = collect([
            $faker->words(3, true),
            $faker->words(3, true),
        ]);

        $userId = $faker->numerify();
        $userEmail = $faker->email();

        return [
            'complete data' => [
                self::makeBuilder()
                    ->issuedAt($issueAt->toImmutable())
                    ->expiresAt($expiredAt->toImmutable())
                    ->canOnlyBeUsedAfter($notBeforeAt->toImmutable())
                    ->permittedFor(...$audiences)
                    ->withClaim('user', [
                        'id' => $userId,
                        'email' => $userEmail,
                    ])->getToken(new Sha256, InMemory::plainText(random_bytes(32)))
                    ->toString(),
                new Claims(
                    new ClaimsUser($userId, $userEmail),
                    $audiences,
                    $issueAt,
                    $notBeforeAt,
                    $expiredAt,
                )
            ],

            'without user ID' => [
                self::makeBuilder()
                    ->issuedAt($issueAt->toImmutable())
                    ->expiresAt($expiredAt->toImmutable())
                    ->canOnlyBeUsedAfter($notBeforeAt->toImmutable())
                    ->permittedFor(...$audiences)
                    ->withClaim('user', [
                        'email' => $userEmail,
                    ])->getToken(new Sha256, InMemory::plainText(random_bytes(32)))
                    ->toString(),
                new Claims(
                    new ClaimsUser('', $userEmail),
                    $audiences,
                    $issueAt,
                    $notBeforeAt,
                    $expiredAt,
                )
            ],
            'without user email' => [
                self::makeBuilder()
                    ->issuedAt($issueAt->toImmutable())
                    ->expiresAt($expiredAt->toImmutable())
                    ->canOnlyBeUsedAfter($notBeforeAt->toImmutable())
                    ->permittedFor(...$audiences)
                    ->withClaim('user', [
                        'id' => $userId,
                    ])->getToken(new Sha256, InMemory::plainText(random_bytes(32)))
                    ->toString(),
                new Claims(
                    new ClaimsUser($userId, ''),
                    $audiences,
                    $issueAt,
                    $notBeforeAt,
                    $expiredAt,
                )
            ],
            'without user' => [
                self::makeBuilder()
                    ->issuedAt($issueAt->toImmutable())
                    ->expiresAt($expiredAt->toImmutable())
                    ->canOnlyBeUsedAfter($notBeforeAt->toImmutable())
                    ->permittedFor(...$audiences)
                    ->getToken(new Sha256, InMemory::plainText(random_bytes(32)))
                    ->toString(),
                new Claims(
                    new ClaimsUser('', ''),
                    $audiences,
                    $issueAt,
                    $notBeforeAt,
                    $expiredAt,
                )
            ],

            'with only 1 audience' => [
                self::makeBuilder()
                    ->issuedAt($issueAt->toImmutable())
                    ->expiresAt($expiredAt->toImmutable())
                    ->canOnlyBeUsedAfter($notBeforeAt->toImmutable())
                    ->permittedFor($audiences[0])
                    ->withClaim('user', [
                        'id' => $userId,
                        'email' => $userEmail,
                    ])->getToken(new Sha256, InMemory::plainText(random_bytes(32)))
                    ->toString(),
                new Claims(
                    new ClaimsUser($userId, $userEmail),
                    collect([$audiences[0]]),
                    $issueAt,
                    $notBeforeAt,
                    $expiredAt,
                )
            ],
            'without audience' => [
                self::makeBuilder()
                    ->issuedAt($issueAt->toImmutable())
                    ->expiresAt($expiredAt->toImmutable())
                    ->canOnlyBeUsedAfter($notBeforeAt->toImmutable())
                    ->withClaim('user', [
                        'id' => $userId,
                        'email' => $userEmail,
                    ])->getToken(new Sha256, InMemory::plainText(random_bytes(32)))
                    ->toString(),
                new Claims(
                    new ClaimsUser($userId, $userEmail),
                    collect(),
                    $issueAt,
                    $notBeforeAt,
                    $expiredAt,
                )
            ],

            'without issue at' => [
                self::makeBuilder()
                    ->expiresAt($expiredAt->toImmutable())
                    ->canOnlyBeUsedAfter($notBeforeAt->toImmutable())
                    ->permittedFor(...$audiences)
                    ->withClaim('user', [
                        'id' => $userId,
                        'email' => $userEmail,
                    ])->getToken(new Sha256, InMemory::plainText(random_bytes(32)))
                    ->toString(),
                new Claims(
                    new ClaimsUser($userId, $userEmail),
                    $audiences,
                    self::createInvalidDate(),
                    $notBeforeAt,
                    $expiredAt,
                )
            ],

            'without expired at' => [
                self::makeBuilder()
                    ->issuedAt($issueAt->toImmutable())
                    ->canOnlyBeUsedAfter($notBeforeAt->toImmutable())
                    ->permittedFor(...$audiences)
                    ->withClaim('user', [
                        'id' => $userId,
                        'email' => $userEmail,
                    ])->getToken(new Sha256, InMemory::plainText(random_bytes(32)))
                    ->toString(),
                new Claims(
                    new ClaimsUser($userId, $userEmail),
                    $audiences,
                    $issueAt,
                    $notBeforeAt,
                    self::createInvalidDate(),
                )
            ],

            'without not before at' => [
                self::makeBuilder()
                    ->issuedAt($issueAt->toImmutable())
                    ->expiresAt($expiredAt->toImmutable())
                    ->permittedFor(...$audiences)
                    ->withClaim('user', [
                        'id' => $userId,
                        'email' => $userEmail,
                    ])->getToken(new Sha256, InMemory::plainText(random_bytes(32)))
                    ->toString(),
                new Claims(
                    new ClaimsUser($userId, $userEmail),
                    $audiences,
                    $issueAt,
                    self::createInvalidDate(),
                    $expiredAt,
                )
            ],
        ];
    }

    protected static function createInvalidDate(): Carbon
    {
        return Carbon::parse('01-01-0000 00:00:00');
    }

    protected static function makeBuilder(): Builder
    {
        return new Builder(new JoseEncoder, ChainedFormatter::default());
    }

    protected function makeService(): JWTParserLcobucci
    {
        return new JWTParserLcobucci;
    }
}
