<?php

namespace App\Models\User;

use App\Core\Formatter\ExceptionErrorCode;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Exceptions\Models\ModelNotFoundException;
use App\Models\Post\Post;
use Carbon\Carbon;
use Database\Factories\User\UserFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property Collection<int, Post> $posts
 */
class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use Notifiable;
    use SoftDeletes;

    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public static function findByIDOrFail(int $id): self
    {
        $user = self::query()->where('id', $id)->first();

        if (is_null($user)) {
            throw new ModelNotFoundException(new ExceptionMessageStandard(
                'User ID is not found',
                ExceptionErrorCode::MODEL_NOT_FOUND->value,
            ));
        }

        return $user;
    }

    public static function findByEmailOrFail(string $email): self
    {
        $user = self::query()->where('email', $email)->first();

        if (is_null($user)) {
            throw new ModelNotFoundException(new ExceptionMessageStandard(
                'User email is not found',
                ExceptionErrorCode::MODEL_NOT_FOUND->value,
            ));
        }

        return $user;
    }

    protected static function newFactory()
    {
        return UserFactory::new();
    }
}
