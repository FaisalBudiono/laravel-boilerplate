<?php

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
        /** @var User */
        $resource = $this->resource;

        return [
            'id' => $resource->id,
            'name' => $resource->name,
            'email' => $resource->email,
            'created_at' => $resource->created_at?->format(DatetimeFormat::ISO_WITH_MILLIS->value),
            'updated_at' => $resource->updated_at?->format(DatetimeFormat::ISO_WITH_MILLIS->value),
        ];
    }
}
