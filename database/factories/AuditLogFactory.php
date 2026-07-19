<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AuditEvent;
use App\Models\AuditLog;
use App\Models\License;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    public function definition(): array
    {
        return [
            'event'          => fake()->randomElement(AuditEvent::cases())->value,
            'auditable_type' => License::class,
            'auditable_id'   => License::factory(),
            'actor_type'     => 'admin',
            'actor_id'       => 1,
            'actor_name'     => 'System Admin',
            'ip_address'     => fake()->ipv4(),
            'user_agent'     => 'Mozilla/5.0',
            'description'    => fake()->sentence(),
            'old_values'     => null,
            'new_values'     => null,
            'meta'           => null,
            // previous_hash + hash are computed in the model's creating hook.
        ];
    }
}
