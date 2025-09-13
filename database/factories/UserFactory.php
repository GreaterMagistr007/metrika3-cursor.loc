<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
final class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'phone' => '+7' . $this->faker->numerify('##########'),
            'telegram_id' => $this->faker->optional(0.7)->numberBetween(100000000, 999999999),
            'telegram_data' => $this->faker->optional(0.5)->randomElements([
                'id' => $this->faker->numberBetween(100000000, 999999999),
                'first_name' => $this->faker->firstName(),
                'last_name' => $this->faker->lastName(),
                'username' => $this->faker->userName(),
            ], $this->faker->numberBetween(1, 4)),
            'phone_verified_at' => $this->faker->optional(0.8)->dateTimeBetween('-1 year', 'now'),
            'last_login_at' => $this->faker->optional(0.6)->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Create a verified user.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'phone_verified_at' => now(),
        ]);
    }

    /**
     * Create an unverified user.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'phone_verified_at' => null,
        ]);
    }

    /**
     * Create a user with Telegram data.
     */
    public function withTelegram(): static
    {
        return $this->state(fn (array $attributes) => [
            'telegram_id' => $this->faker->numberBetween(100000000, 999999999),
            'telegram_data' => [
                'id' => $this->faker->numberBetween(100000000, 999999999),
                'first_name' => $this->faker->firstName(),
                'last_name' => $this->faker->lastName(),
                'username' => $this->faker->userName(),
            ],
        ]);
    }
}
