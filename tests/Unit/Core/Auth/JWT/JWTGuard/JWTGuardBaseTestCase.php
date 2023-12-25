<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Auth\JWT\JWTGuard;

use App\Core\Auth\JWT\JWTGuard;
use App\Core\Auth\JWT\Parser\JWTParser;
use App\Core\Auth\JWT\Signer\JWTSigner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

abstract class JWTGuardBaseTestCase extends TestCase
{
    use RefreshDatabase;

    public static function noAuthorizationHeaderDataProvider(): array
    {
        $noHeader = new Request();

        $randomHeader = new Request();
        $randomHeader->headers->set('random', 'asd');

        return [
            'no header at all' => [
                $noHeader,
            ],
            'with header but not authorization header' => [
                $randomHeader,
            ],
        ];
    }

    public static function notBearerTokenDataProvider(): array
    {
        $faker = self::makeFaker();

        return [
            'only has first value' => [
                $faker->regexify('[a-zA-Z0-9]{40}'),
            ],
            'the first value is not "bearer"' => [
                'not-bearer' . $faker->regexify('[a-zA-Z0-9]{40}'),
            ],
        ];
    }

    protected function makeService(
        ?Request $request = null,
        ?JWTParser $jwtParser = null,
        ?JWTSigner $jwtSigner = null,
    ): JWTGuard {
        if (is_null($request)) {
            $request = new Request();
        }

        if (is_null($jwtParser)) {
            $jwtParser = $this->mock(JWTParser::class);
        }

        if (is_null($jwtSigner)) {
            $jwtSigner = $this->mock(JWTSigner::class);
        }

        return new JwtGuard($request, $jwtParser, $jwtSigner);
    }
}
