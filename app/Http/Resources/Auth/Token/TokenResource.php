<?php

declare(strict_types=1);

namespace App\Http\Resources\Auth\Token;

use App\Services\Auth\ValueObject\Token;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TokenResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'Bearer',
            'accessToken' => $this->model()->accessToken,
            'refreshToken' => $this->model()->refreshToken,
        ];
    }

    protected function model(): Token
    {
        return $this->resource;
    }
}
