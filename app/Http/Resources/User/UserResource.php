<?php

declare(strict_types=1);

namespace App\Http\Resources\User;

use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->model()->id,
            'name' => $this->model()->name,
            'email' => $this->model()->email,
            'createdAt' => $this->model()->created_at?->toIso8601String(),
            'updatedAt' => $this->model()->updated_at?->toIso8601String(),
        ];
    }

    protected function model(): User
    {
        return $this->resource;
    }
}
