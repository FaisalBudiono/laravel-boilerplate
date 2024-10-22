<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Exceptions\Auth\InvalidCredentialException;
use App\Models\User\User;
use App\Port\Services\Auth\LoginPort;
use App\Services\Auth\Exceptions\AuthErrorCode;
use App\Services\Auth\ValueObject\Token;
use App\Services\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Services\JWT\Signer\JWTSigner;
use App\Services\JWT\ValueObject\Claims;
use App\Services\JWT\ValueObject\ClaimsUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthService implements AuthServiceContract
{
    public function __construct(
        protected JWTSigner $signer,
    ) {}

    public function login(LoginPort $port): Token
    {
        try {
            DB::beginTransaction();

            $u = User::query()->where('email', $port->getEmail())->first();

            if (is_null($u)) {
                throw $this->invalidCredException();
            }

            if (!Hash::check($port->getUserPassword(), $u->password)) {
                throw $this->invalidCredException();
            }

            $claims = new Claims(
                new ClaimsUser(
                    (string) $u->id,
                    $u->email,
                ),
                'something',
                Carbon::now(),
                Carbon::now()->subSecond(),
                Carbon::now()->addMinutes(10),
            );

            $at = $this->signer->sign($claims);

            DB::commit();

            return new Token(
                $at,
                'dummy-refresh-token',
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }

    protected function invalidCredException(): InvalidCredentialException
    {
        return new InvalidCredentialException(
            new ExceptionMessageStandard(
                'Credential is invalid',
                AuthErrorCode::INVALID_CREDENTIAL->value,
            ),
        );
    }
}
