<?php

declare(strict_types=1);

namespace Tests\Unit\Providers\ModelBinding;

use App\Core\Formatter\ExceptionErrorCode;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Models\User\User;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class ModelBindingUserTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'id' => $this->faker->numberBetween(1, 100),
        ]);

        Route::get('/tests/{userID}', function (User $userID) {
            return response()->json([
                'foo' => $userID->id,
            ]);
        })->middleware('model-binding')->name('dummy');
    }

    #[Test]
    public function should_show_200_when_user_id_found(): void
    {
        // Act
        $response = $this->getJson(
            '/tests/' . $this->user->id,
        );


        // Assert
        $response->assertOk();
        $response->assertJson([
            'foo' => $this->user->id,
        ]);
    }

    #[Test]
    public function should_show_404_when_user_id_not_found(): void
    {
        // Act
        $response = $this->getJson(
            '/tests/' . $this->user->id + 1,
        );


        // Assert
        $response->assertNotFound();

        $expectedErrorMessage = new ExceptionMessageStandard(
            'User ID is not found',
            ExceptionErrorCode::MODEL_NOT_FOUND->value,
        );
        $response->assertJsonPath(
            'errors',
            $expectedErrorMessage->getJsonResponse()->toArray()
        );
    }

    #[Test]
    public function should_not_register_route_when_model_binding_is_not_number(): void
    {
        // Arrange
        $this->withoutExceptionHandling();

        $this->expectException(NotFoundHttpException::class);


        // Act
        $this->getJson(
            '/tests/asd',
        );
    }
}
