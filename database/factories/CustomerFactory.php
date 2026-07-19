<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'uuid'      => (string) Str::uuid(),
            'name'      => fake()->name(),
            'company'   => fake()->company(),
            'email'     => fake()->unique()->safeEmail(),
            'phone'     => '+9665' . fake()->numerify('########'),
            'country'   => 'SA',
            'is_active' => true,
            'notes'     => fake()->optional()->sentence(),
            'meta'      => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
