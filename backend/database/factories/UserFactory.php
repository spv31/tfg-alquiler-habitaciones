<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
            'identifier' => $this->faker->regexify('[0-9]{8}[A-Z]'),
            'role' => 'tenant',
            'user_type' => 'individual',
            'phone_number' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function owner(): self
    {
        return $this->state(fn() => ['role' => 'owner']);
    }

    public function tenant(): self
    {
        return $this->state(fn() => ['role' => 'tenant']);
    }
}
