<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Cabinet;
use App\Models\CabinetUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CabinetUser>
 */
final class CabinetUserFactory extends Factory
{
    protected $model = CabinetUser::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $roles = ['admin', 'manager', 'operator'];
        
        return [
            'cabinet_id' => Cabinet::factory(),
            'user_id' => User::factory(),
            'role' => $this->faker->randomElement($roles),
            'is_owner' => false,
            'joined_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Create an admin cabinet user.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }

    /**
     * Create a manager cabinet user.
     */
    public function manager(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'manager',
        ]);
    }

    /**
     * Create an operator cabinet user.
     */
    public function operator(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'operator',
        ]);
    }

    /**
     * Create an owner cabinet user.
     */
    public function owner(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
            'is_owner' => true,
        ]);
    }
}
