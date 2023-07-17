<?php

namespace Tests\Unit\Providers\CoreBinder;

use App\Core\Auth\AuthJWTCore;
use App\Core\Auth\AuthJWTCoreContract;
use App\Core\Auth\JWT\JWTGuard;
use App\Core\Auth\JWT\JWTGuardContract;
use App\Core\Auth\JWT\Mapper\JWTMapper;
use App\Core\Auth\JWT\Mapper\JWTMapperContract;
use App\Core\Auth\JWT\Parser\JWTParser;
use App\Core\Auth\JWT\Parser\JWTParserLcobucci;
use App\Core\Auth\JWT\Refresh\Cacher\Cacher as RefreshTokenCacher;
use App\Core\Auth\JWT\Refresh\Cacher\CacherLaravel as RefreshTokenCacherLaravel;
use App\Core\Auth\JWT\Refresh\Mapper\UserTokenMapper as RefreshTokenUserTokenMapper;
use App\Core\Auth\JWT\Refresh\Mapper\UserTokenMapperContract as RefreshTokenUserTokenMapperContract;
use App\Core\Auth\JWT\Refresh\RefreshTokenManager;
use App\Core\Auth\JWT\Refresh\RefreshTokenManagerContract;
use App\Core\Auth\JWT\Signer\JWTSigner;
use App\Core\Auth\JWT\Signer\JWTSignerLcobucci;
use Illuminate\Http\Request;
use Tests\Unit\Providers\CoreBinder\Dependencies\DependencyFactoryMockery;
use Tests\Unit\Providers\CoreBinder\Dependencies\DependencyFactoryRaw;

class CoreBinderTestCaseAuth extends CoreBinderTestCaseAbstract
{
    protected function abstractWithImplementationList(): array
    {
        return [
            AuthJWTCoreContract::class => [
                AuthJWTCore::class,
                [
                    new DependencyFactoryMockery($this->test, JWTMapperContract::class),
                    new DependencyFactoryMockery($this->test, JWTSigner::class),
                    new DependencyFactoryMockery($this->test, RefreshTokenManagerContract::class),
                ],
            ],

            JWTGuardContract::class => [
                JWTGuard::class,
                [
                    new DependencyFactoryRaw(new Request()),
                    new DependencyFactoryMockery($this->test, JWTParser::class),
                    new DependencyFactoryMockery($this->test, JWTSigner::class),
                ]
            ],
            JWTMapperContract::class => [
                JWTMapper::class,
            ],
            JWTParser::class => [
                JWTParserLcobucci::class,
            ],
            JWTSigner::class => [
                JWTSignerLcobucci::class,
            ],

            RefreshTokenCacher::class => [
                RefreshTokenCacherLaravel::class,
            ],

            RefreshTokenManagerContract::class => [
                RefreshTokenManager::class,
                [
                    new DependencyFactoryMockery($this->test, RefreshTokenUserTokenMapperContract::class),
                    new DependencyFactoryMockery($this->test, RefreshTokenCacher::class),
                ]
            ],

            RefreshTokenUserTokenMapperContract::class => [
                RefreshTokenUserTokenMapper::class,
            ],
        ];
    }
}
