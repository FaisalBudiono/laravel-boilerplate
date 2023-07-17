<?php

namespace App\Core\Auth\JWT;

use App\Core\Auth\JWT\Parser\JWTParser;
use App\Core\Auth\JWT\Signer\JWTSigner;
use App\Models\User\User;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

class JWTGuard implements JWTGuardContract
{
    protected ?Authenticatable $user = null;

    public function __construct(
        protected Request $request,
        protected JWTParser $parser,
        protected JWTSigner $signer,
    ) {
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check(): bool
    {
        return !is_null($this->user());
    }

    /**
     * Determine if the guard has a user instance.
     *
     * @return bool
     */
    public function hasUser(): bool
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
        return is_null($this->user());
    }

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|string|null
     */
    public function id(): int | string | null
    {
        return $this->user()?->getAuthIdentifier();
    }

    /**
     * Set the current user.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return void
     */
    public function setUser(Authenticatable $user): void
    {
        $this->user = $user;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user(): ?Authenticatable
    {
        if (is_null($this->user)) {
            $this->setUserFromAuthorizationHeader();
        }

        return $this->user;
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function validate(array $credentials = []): bool
    {
        try {
            $token = $credentials['token'] ?? '';
            if (empty($token)) {
                return false;
            }

            $this->setUserTokenOrFail($token);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    protected function isAllowedType(string $authType): bool
    {
        return strtolower($authType) === 'bearer';
    }

    protected function hasAuthorizationHeader(): bool
    {
        return $this->request->headers->has($this->getAuthorizationHeaderName());
    }

    protected function getAuthorizationHeader(): ?string
    {
        return $this->request->headers->get($this->getAuthorizationHeaderName());
    }

    protected function getAuthorizationHeaderName(): string
    {
        return 'authorization';
    }

    protected function parseAuthToken(): string
    {
        return explode(' ', $this->getAuthorizationHeader())[1] ?? '';
    }

    protected function parseAuthType(): string
    {
        return explode(' ', $this->getAuthorizationHeader())[0] ?? '';
    }

    protected function setUserFromAuthorizationHeader(): void
    {
        try {
            if (!$this->hasAuthorizationHeader()) {
                return;
            }

            if (!$this->isAllowedType($this->parseAuthType())) {
                return;
            }

            $this->setUserTokenOrFail($this->parseAuthToken());
        } catch (Exception $e) {
        }
    }

    protected function setUserTokenOrFail(string $token): void
    {
        $this->signer->validate($token);

        $claims = $this->parser->parse($token);
        $user = User::findByIDOrFail($claims->user->id);

        $this->setUser($user);
    }
}
