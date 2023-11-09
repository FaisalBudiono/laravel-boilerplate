<?php

namespace App\Models\Post;

use Carbon\Carbon;
use Database\Factories\Post\PostFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string|null $content
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class Post extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'user_id' => 'integer',
    ];

    protected static function newFactory()
    {
        return PostFactory::new();
    }
}
