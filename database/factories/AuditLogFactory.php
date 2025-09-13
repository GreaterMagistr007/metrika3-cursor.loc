<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\Cabinet;
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
        return [
            'user_id' => User::factory(),
            'cabinet_id' => Cabinet::factory(),
            'subject_type' => User::class,
            'subject_id' => User::factory(),
            'event' => $this->faker->randomElement([
                'user.invited',
                'user.removed',
                'permission.assigned',
                'permission.revoked',
                'cabinet.created',
                'cabinet.updated',
                'cabinet.deleted',
                'ownership.transferred',
            ]),
            'description' => $this->faker->sentence(),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'metadata' => [
                'test_data' => $this->faker->word(),
                'random_value' => $this->faker->numberBetween(1, 100),
            ],
        ];
    }

    /**
     * Create audit log for specific event.
     */
    public function forEvent(string $event): static
    {
        return $this->state(fn (array $attributes) => [
            'event' => $event,
        ]);
    }

    /**
     * Create audit log for specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Create audit log for specific cabinet.
     */
    public function forCabinet(Cabinet $cabinet): static
    {
        return $this->state(fn (array $attributes) => [
            'cabinet_id' => $cabinet->id,
        ]);
    }
}