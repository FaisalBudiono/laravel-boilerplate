<?php

namespace Tests\Unit\Models\User;

use App\Core\Formatter\ExceptionErrorCode;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Exceptions\Models\ModelNotFoundException;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class User_FindByEmailOrFail_Test extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'id' => $this->faker()->numberBetween(1, 100),
        ]);
    }

    #[Test]
    public function should_successfully_return_user_instance()
    {
        // Act
        $user = User::findByEmailOrFail($this->user->email);


        // Assert
        $this->assertTrue($user->is($this->user));
    }

    #[Test]
    public function should_throw_model_not_found_exception_when_email_is_not_found()
    {
        // Arrange
        $notFoundEmail = $this->user->email . 'notfound';


        // Assert
        $expectedException = new ModelNotFoundException(new ExceptionMessageStandard(
            'User email is not found',
            ExceptionErrorCode::MODEL_NOT_FOUND->value,
        ));
        $this->expectExceptionObject($expectedException);


        // Act
        User::findByEmailOrFail($notFoundEmail);
    }
}
