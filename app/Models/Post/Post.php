<?php

declare(strict_types=1);

namespace App\Models\Post;

use App\Core\Formatter\ExceptionErrorCode;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Exceptions\Models\ModelNotFoundException;
use App\Models\User\User;
use Carbon\Carbon;
use Database\Factories\Post\PostFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string|null $content
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 *
 * @property User $user
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function eagerLoadAll(): array
    {
        return [
            'user',
        ];
    }

    public static function findByIDOrFail(int $id): self
    {
        $post = self::query()->where('id', $id)->first();

        if (is_null($post)) {
            throw new ModelNotFoundException(new ExceptionMessageStandard(
                'Post ID is not found',
                ExceptionErrorCode::MODEL_NOT_FOUND->value,
            ));
        }

        return $post;
    }

    protected static function newFactory()
    {
        return PostFactory::new();
    }
}
