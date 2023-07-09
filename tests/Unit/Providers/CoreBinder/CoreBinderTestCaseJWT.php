<?php

namespace Tests\Unit\Providers\CoreBinder;

use App\Core\Auth\JWT\JWTGuard;
use App\Core\Auth\JWT\JWTGuardContract;
use App\Core\Auth\JWT\Parser\JWTParser;
use App\Core\Auth\JWT\Parser\JWTParserLcobucci;
use App\Core\Auth\JWT\Signer\JWTSigner;
use App\Core\Auth\JWT\Signer\JWTSignerLcobucci;
use Illuminate\Http\Request;
use Tests\Unit\Providers\CoreBinder\Dependencies\DependencyFactoryMockery;
use Tests\Unit\Providers\CoreBinder\Dependencies\DependencyFactoryRaw;

class CoreBinderTestCaseJWT extends CoreBinderTestCaseAbstract
{
    protected function abstractWithImplementationList(): array
    {
        return [
            JWTGuardContract::class => [
                JWTGuard::class,
                [
                    new DependencyFactoryRaw(new Request()),
                    new DependencyFactoryMockery($this->test, JWTParser::class),
                    new DependencyFactoryMockery($this->test, JWTSigner::class),
                ]
            ],
            JWTParser::class => [
                JWTParserLcobucci::class,
            ],
            JWTSigner::class => [
                JWTSignerLcobucci::class,
            ],
        ];
    }
}
