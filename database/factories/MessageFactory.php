<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
final class MessageFactory extends Factory
{
    protected $model = Message::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $types = ['success', 'error', 'warning', 'info', 'system'];
        
        return [
            'type' => $this->faker->randomElement($types),
            'title' => $this->faker->sentence(3),
            'text' => $this->faker->paragraph(2),
            'url' => $this->faker->optional(0.3)->url(),
            'button_text' => $this->faker->optional(0.4)->words(2, true),
            'button_url' => $this->faker->optional(0.4)->url(),
            'is_active' => $this->faker->boolean(80),
            'trigger_condition' => $this->faker->optional(0.2)->randomElements([
                ['field' => 'user.phone_verified_at', 'value' => 'not_null'],
                ['field' => 'cabinet.is_active', 'value' => true],
            ], 1)[0] ?? null,
            'expires_at' => $this->faker->optional(0.3)->dateTimeBetween('now', '+30 days'),
        ];
    }

    /**
     * Create a toast message.
     */
    public function toast(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $this->faker->randomElement(['success', 'error', 'warning', 'info']),
            'title' => null,
            'url' => null,
            'button_text' => null,
            'button_url' => null,
        ]);
    }

    /**
     * Create a persistent message.
     */
    public function persistent(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $this->faker->randomElement(['info', 'warning', 'error']),
        ]);
    }

    /**
     * Create a system message.
     */
    public function system(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'system',
            'title' => $this->faker->randomElement([
                'Обновление системы',
                'Технические работы',
                'Важное уведомление',
                'Задолженность',
            ]),
        ]);
    }
}
