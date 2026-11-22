<?php

namespace Database\Factories;

use App\Models\User;
use App\Support\DemoCredentials;
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

    public function demo(): static
    {
        return $this->afterCreating(function (User $user): void {
            $user->forceFill(['is_demo' => true])->save();
        });
    }

    /**
     * Landlord demo de UserSeeder (spec 4.10 / DT-M9).
     * Password vía App\Support\DemoCredentials: config o aleatoria (Fase 5 / 5.2).
     * role/is_demo no son fillables y se fuerzan tras crear.
     */
    public function demoLandlord(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Alex',
            'email' => 'landlord@example.com',
            'image' => 'uploads/25/05-132/1748140914.jpeg',
            'phone' => ['mobile_code' => '+880', 'mobile_no' => '01933445566'],
            'password' => DemoCredentials::hash(DemoCredentials::password('landlord@example.com')),
            'unique_id' => '687324',
            'is_setupped' => 1,
            'balance' => 1000,
            'profile_type' => null,
            'subscriptionDate' => now(),
            'will_expire' => now()->addYears(5),
            'email_verified_at' => null,
        ])->afterCreating(function (User $user): void {
            $user->forceFill(['role' => 'landlord', 'is_demo' => true])->save();
        });
    }

    /**
     * Tenant demo de UserSeeder (spec 4.10 / DT-M9).
     * Password vía App\Support\DemoCredentials: config o aleatoria (Fase 5 / 5.2).
     */
    public function demoTenant(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Britanni Edwards',
            'email' => 'tenant@example.com',
            'phone' => ['mobile_code' => '+260', 'mobile_no' => '34'],
            'password' => DemoCredentials::hash(DemoCredentials::password('tenant@example.com')),
            'unique_id' => '000004',
            'is_setupped' => 1,
            'balance' => 0,
            'profile_type' => 'company',
            'subscriptionDate' => null,
            'will_expire' => null,
            'email_verified_at' => null,
            'created_at' => '2025-09-23 10:00:13',
            'updated_at' => '2025-09-23 10:00:13',
        ])->afterCreating(function (User $user): void {
            $user->forceFill(['role' => 'tenant'])->save();
        });
    }
}
