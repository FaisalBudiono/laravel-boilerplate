<?php

namespace Database\Factories\Permission;

use App\Models\Permission\Enum\RoleName;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post\Post>
 */
class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => fake()->randomElement(RoleName::cases()),
            'guard_name' => config('auth.defaults.guard', ''),
        ];
    }
}
