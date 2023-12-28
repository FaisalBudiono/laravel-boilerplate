<?php

declare(strict_types=1);

namespace Tests\Feature\User;

use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Core\User\UserCoreContract;
use App\Exceptions\Http\InternalServerErrorException;
use App\Models\User\User;
use App\Port\Core\User\DeleteUserPort;
use Mockery\MockInterface;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\BaseFeatureTestCase;

class UserDestroyTest extends BaseFeatureTestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'id' => $this->faker()->numberBetween(1, 100),
        ]);

        $this->instance(UserCoreContract::class, $this->mock(UserCoreContract::class));
    }

    #[Test]
    public function should_show_500_when_thrown_generic_error(): void
    {
        // Arrange
        $this->withoutExceptionHandling();

        $mockException = new \Error('generic error');


        // Assert
        $mockCore = $this->mock(
            UserCoreContract::class,
            function (MockInterface $mock) use ($mockException) {
                $mock->shouldReceive('delete')
                    ->once()
                    ->withArgs(fn (
                        DeleteUserPort $argInput
                    ) => $this->validateRequest($argInput))
                    ->andThrow($mockException);
            }
        );
        $this->instance(UserCoreContract::class, $mockCore);


        try {
            // Act
            $this->deleteJson(
                $this->getEndpointUrl($this->user->id),
            );
            $this->fail('Should throw error');
        } catch (AssertionFailedError $e) {
            throw $e;
        } catch (\Throwable $e) {
            $expectedException = new InternalServerErrorException(
                new ExceptionMessageGeneric(),
                $mockException,
            );
            $this->assertEquals($expectedException, $e);
        }
    }

    #[Test]
    public function should_show_204_when_successfully_delete_user(): void
    {
        // Assert
        $mockCore = $this->mock(
            UserCoreContract::class,
            function (MockInterface $mock) {
                $mock->shouldReceive('delete')
                    ->once()
                    ->withArgs(fn (
                        DeleteUserPort $argInput
                    ) => $this->validateRequest($argInput));
            }
        );
        $this->instance(UserCoreContract::class, $mockCore);


        // Act
        $response = $this->deleteJson(
            $this->getEndpointUrl($this->user->id),
        );


        // Assert
        $response->assertNoContent();
    }

    protected function getEndpointUrl(int $userId): string
    {
        return route('user.destroy', ['userID' => $userId]);
    }

    protected function validateRequest(DeleteUserPort $argInput): bool
    {
        try {
            $this->assertTrue($argInput->getUserModel()->is($this->user));
            return true;
        } catch (\Throwable $e) {
            dd($e);
        }
    }
}
