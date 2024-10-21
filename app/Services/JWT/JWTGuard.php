<?php

declare(strict_types=1);

namespace App\Services\JWT;

use App\Exceptions\Resource\ModelNotFoundException;
use App\Models\User\User;
use App\Services\Formatter\ExceptionErrorCode;
use App\Services\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Services\JWT\Exceptions\MissingHeaderException;
use App\Services\JWT\Exceptions\JWTErrorCode;
use App\Services\JWT\Exceptions\JWTException;
use App\Services\JWT\Parser\JWTParser;
use App\Services\JWT\Signer\JWTSigner;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

class JWTGuard implements JWTGuardContract
{
    protected ?Authenticatable $user = null;

    public function __construct(
        protected Request $req,
        protected JWTSigner $signer,
        protected JWTParser $parser,
    ) {}

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check()
    {
        return !is_null($this->user());
    }

    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest()
    {
        try {
            $this->authTokenHeader();

            return false;
        } catch (MissingHeaderException) {
            return true;
        } catch (\Throwable) {
            return false;
        }
    }


    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        if (is_null($this->user)) {
            $this->user = $this->fetchUserOrNull();
        }

        return $this->user;
    }

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|string|null
     */
    public function id()
    {
        return $this->user()?->getAuthIdentifier();
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        try {
            $token = (string) $credentials['token'] ?? '';
            if (empty($token)) {
                return false;
            }

            $this->signer->validate($token);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Determine if the guard has a user instance.
     *
     * @return bool
     */
    public function hasUser()
    {
        return !is_null($this->user());
    }

    /**
     * Set the current user.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return $this
     */
    public function setUser(Authenticatable $user)
    {
        $this->user = $user;

        return $this;
    }

    protected function authTokenHeader(): string
    {
        $authHeader = $this->req->header('authorization');

        if (is_null($authHeader)) {
            throw new MissingHeaderException(new ExceptionMessageStandard(
                'Authorization header is missing',
                JWTErrorCode::MISSING_HEADER->value,
            ));
        }

        $headerValues = explode(' ', $authHeader);
        if (count($headerValues) < 2) {
            throw new MissingHeaderException(new ExceptionMessageStandard(
                'Authorization header is invalid',
                JWTErrorCode::MISSING_HEADER->value,
            ));
        }

        if (strtolower($headerValues[0]) !== 'bearer') {
            throw new MissingHeaderException(new ExceptionMessageStandard(
                'Authorization should be bearer token',
                JWTErrorCode::MISSING_HEADER->value,
            ));
        }

        return $headerValues[1];
    }

    protected function fetchUserOrFail(string $token): User
    {
        $this->signer->validate($token);
        $c = $this->parser->parse($token);

        $u = User::query()->where('id', $c->user->id)->first();

        if (is_null($u)) {
            throw new ModelNotFoundException(new ExceptionMessageStandard(
                'User not found',
                ExceptionErrorCode::RESOURCE_NOT_FOUND->value,
            ));
        }

        return $u;
    }

    protected function fetchUserOrNull(): ?User
    {
        try {
            return $this->fetchUserOrFail($this->authTokenHeader());
        } catch (JWTException|ModelNotFoundException) {
            return null;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
