<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Resource\User;

use App\Core\Date\DatetimeFormat;
use App\Http\Resources\User\UserResource;
use App\Models\User\User;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    #[Test]
    #[DataProvider('dateDataProvider')]
    public function should_return_right_arrayable_format(
        ?Carbon $mockDate,
    ): void {
        // Arrange
        $user = User::factory()->create([
            'created_at' => $mockDate,
            'updated_at' => $mockDate,
        ]);


        // Act
        $result = UserResource::make($user)->toJson();


        // Assert
        $this->assertJsonStringEqualsJsonString(json_encode([
            ...$this->makeDefaultResponse($user),
        ]), $result);
    }

    public static function dateDataProvider(): array
    {
        return [
            'filled date' => [
                now(),
            ],
            'date is null' => [
                null,
            ],
        ];
    }

    protected function makeDefaultResponse(User $user): array
    {
        return [
            'id' => (string) $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'createdAt' => $user->created_at?->format(DatetimeFormat::ISO_WITH_MILLIS->value),
            'updatedAt' => $user->updated_at?->format(DatetimeFormat::ISO_WITH_MILLIS->value),
        ];
    }
}
