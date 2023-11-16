<?php

namespace App\Http\Resources\Post;

use App\Core\Date\DatetimeFormat;
use App\Http\Resources\User\UserResource;
use App\Models\Post\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $resource = $this->resource;
        assert($resource instanceof Post);

        return [
            'id' => $resource->id,
            'title' => $resource->title,
            'content' => $resource->content,
            'user' => UserResource::make($this->whenLoaded('user')),
            'created_at' => $resource->created_at?->format(DatetimeFormat::ISO_WITH_MILLIS->value),
            'updated_at' => $resource->updated_at?->format(DatetimeFormat::ISO_WITH_MILLIS->value),
        ];
    }
}
