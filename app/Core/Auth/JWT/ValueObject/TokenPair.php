<?php

namespace App\Core\Auth\JWT\ValueObject;

use Illuminate\Contracts\Support\Arrayable;

readonly class TokenPair implements Arrayable
{
    public function __construct(
        public string $accessToken,
        public string $refreshToken,
    ) {
    }

    public function toArray()
    {
        return [
            'type' => 'Bearer',
            'accessToken' => $this->accessToken,
            'refreshToken' => $this->refreshToken,
        ];
    }
}
