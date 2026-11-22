<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * U1–U5 del spec-user-fillable-restrictivo-fase5 (Fase 5 / 5.11):
 * contrato del modelo — allowlist de 7 columnas, columnas retiradas
 * solo vía forceFill, factories unguarded y hooks intactos.
 */
class UserFillableTest extends TestCase
{
    use RefreshDatabase;

    public function test_u1_fillable_is_exact_allowlist(): void
    {
        $this->assertSame(
            ['name', 'email', 'image', 'phone', 'password', 'profile_type', 'is_setupped'],
            (new User)->getFillable()
        );
    }

    public function test_u2_fill_ignores_removed_columns(): void
    {
        // Arrange
        $user = User::factory()->landlord()->create();

        // Act
        $user->fill([
            'landlord_id' => 1,
            'plan_subscribe_id' => 1,
            'unique_id' => 'HACKED',
            'balance' => 999,
            'subscriptionDate' => now(),
            'will_expire' => now()->addYear(),
            'email_verified_at' => now(),
            'remember_token' => 'hacked',
        ])->save();

        // Assert — el fill descarta silenciosamente las retiradas
        $this->assertNull($user->fresh()->landlord_id);
        $this->assertNull($user->fresh()->plan_subscribe_id);
        $this->assertNotSame('HACKED', $user->fresh()->unique_id);
        $this->assertNotSame(999.0, (float) $user->fresh()->balance);
        $this->assertNull($user->fresh()->subscriptionDate);
        $this->assertNotSame('hacked', $user->fresh()->remember_token);
    }

    public function test_u3_forcefill_assigns_removed_columns(): void
    {
        // Arrange
        $user = User::factory()->landlord()->create();

        // Act
        $user->forceFill(['balance' => 999])->save();

        // Assert — vía explícita server-side
        $this->assertSame(999.0, (float) $user->fresh()->balance);
    }

    public function test_u4_factory_writes_beyond_fillable_unguarded(): void
    {
        // Arrange — factories corren dentro de Model::unguarded()
        $user = User::factory()->landlord()->create();

        // Assert
        $this->assertSame('landlord', $user->role);
        $this->assertNotNull($user->unique_id);
        $this->assertNotNull($user->balance);
        $this->assertNotNull($user->will_expire);
    }

    public function test_u5_creating_hooks_stay_intact(): void
    {
        // Act — create solo con claves del contrato
        $user = User::create([
            'name' => 'Hook User',
            'email' => 'hook@example.test',
            'password' => 'Password123',
        ]);

        // Assert — booted() (role default) y boot() (unique_id) intactos
        $this->assertSame('landlord', $user->role);
        $this->assertSame(6, strlen((string) $user->unique_id));
    }
}
