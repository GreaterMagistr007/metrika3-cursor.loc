<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Cabinet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cabinet>
 */
final class CabinetFactory extends Factory
{
    protected $model = Cabinet::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company() . ' Cabinet',
            'description' => $this->faker->paragraph(),
            'owner_id' => User::factory(),
            'is_active' => true,
        ];
    }

    /**
     * Create an inactive cabinet.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
