<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AuditLog>
 */
final class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $events = [
            'user_registered',
            'user_logged_in',
            'cabinet_created',
            'cabinet_updated',
            'cabinet_deleted',
            'user_invited',
            'user_removed',
            'ownership_transferred'
        ];
        
        return [
            'user_id' => User::factory(),
            'event' => $this->faker->randomElement($events),
            'description' => $this->faker->sentence(),
            'metadata' => $this->faker->optional(0.3)->randomElements([
                'ip_address' => $this->faker->ipv4(),
                'user_agent' => $this->faker->userAgent(),
                'cabinet_id' => $this->faker->numberBetween(1, 100),
                'target_user_id' => $this->faker->numberBetween(1, 100),
            ], $this->faker->numberBetween(1, 3)),
        ];
    }

    /**
     * Create a cabinet-related audit log.
     */
    public function cabinet(): static
    {
        return $this->state(fn (array $attributes) => [
            'event' => $this->faker->randomElement([
                'cabinet_created',
                'cabinet_updated',
                'cabinet_deleted'
            ]),
            'description' => $this->faker->randomElement([
                'Кабинет создан',
                'Кабинет обновлен',
                'Кабинет удален'
            ]),
        ]);
    }

    /**
     * Create a user-related audit log.
     */
    public function user(): static
    {
        return $this->state(fn (array $attributes) => [
            'event' => $this->faker->randomElement([
                'user_registered',
                'user_logged_in',
                'user_invited',
                'user_removed'
            ]),
            'description' => $this->faker->randomElement([
                'Пользователь зарегистрирован',
                'Пользователь вошел в систему',
                'Пользователь приглашен',
                'Пользователь удален'
            ]),
        ]);
    }
}
