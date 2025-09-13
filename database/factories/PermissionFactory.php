<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Permission>
 */
final class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = ['user', 'settings', 'message', 'machines', 'reports'];
        $category = $this->faker->randomElement($categories);
        
        return [
            'name' => $category . '.' . $this->faker->slug(),
            'description' => $this->faker->sentence(),
            'category' => $category,
            'is_active' => true,
        ];
    }

    /**
     * Create a user permission.
     */
    public function user(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'user.' . $this->faker->slug(),
            'category' => 'user',
        ]);
    }

    /**
     * Create a settings permission.
     */
    public function settings(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'settings.' . $this->faker->slug(),
            'category' => 'settings',
        ]);
    }

    /**
     * Create a message permission.
     */
    public function message(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'message.' . $this->faker->slug(),
            'category' => 'message',
        ]);
    }

    /**
     * Create an inactive permission.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
