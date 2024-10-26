<?php

declare(strict_types=1);

namespace Database\Factories\Auth;

use App\Models\Auth\Login;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Login>
 */
class LoginFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'payload' => $this->faker->uuid(),
            'last_activity_at' => Carbon::parse($this->faker->dateTime()),
        ];
    }
}
