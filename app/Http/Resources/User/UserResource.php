<?php

declare(strict_types=1);

namespace App\Http\Resources\User;

use App\Core\Date\DatetimeFormat;
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
            'createdAt' => $this->model()->created_at?->format(DatetimeFormat::ISO_WITH_MILLIS->value),
            'updatedAt' => $this->model()->updated_at?->format(DatetimeFormat::ISO_WITH_MILLIS->value),
        ];
    }

    protected function model(): User
    {
        return $this->resource;
    }
}
