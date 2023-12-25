<?php

declare(strict_types=1);

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
        return [
            'id' => $this->model()->id,
            'title' => $this->model()->title,
            'content' => $this->model()->content,
            'user' => UserResource::make($this->whenLoaded('user')),
            'createdAt' => $this->model()->created_at?->format(DatetimeFormat::ISO_WITH_MILLIS->value),
            'updatedAt' => $this->model()->updated_at?->format(DatetimeFormat::ISO_WITH_MILLIS->value),
        ];
    }

    protected function model(): Post
    {
        return $this->resource;
    }
}
