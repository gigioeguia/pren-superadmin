<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'role' => 'landlord',
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'phone' => ['code' => '+1', 'phone' => '123456789'],
            'unique_id' => str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT),
            'is_setupped' => 1,
            'balance' => 0,
            'will_expire' => now()->addDays(30),
            'remember_token' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function landlord(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'landlord',
            'is_setupped' => 1,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
            'is_setupped' => 1,
        ]);
    }

    public function tenant(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'tenant',
            'is_setupped' => 1,
        ]);
    }

    public function superadmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'superadmin',
            'is_setupped' => 1,
        ]);
    }
}
