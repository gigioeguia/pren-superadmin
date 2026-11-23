<?php

namespace Tests\Feature\Profile;

use App\Models\PlanSubscribe;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * Contract tests F8–F11 (spec-tx-legadas-services-fase5, Fase 5 / 5.18).
 * F7 reusa AdminProfileSelfTest (g4/g5) — sin casos nuevos.
 *
 * Congelan el contrato observable de los flujos con tx de profile
 * ANTES de moverlos a ProfileService. F9 y F10 congelan HOY el
 * comportamiento inseguro/roto (D-IDOR / D-FIX-validation) y se
 * actualizan en el commit del slice 5 junto a la desviación.
 */
class ProfileTxContractTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    /**
     * Landlord con plan vigente — exige ExpiredMiddleware (grupo landlord).
     */
    private function landlordWithPlan(array $attributes = []): User
    {
        $landlord = $this->createLandlord($attributes);
        $subscribe = PlanSubscribe::factory()->create(['user_id' => $landlord->id]);
        $landlord->forceFill([
            'plan_subscribe_id' => $subscribe->id,
            'will_expire' => now()->addMonth(),
        ])->save();

        return $landlord;
    }

    public function test_f8_landlord_updates_own_profile(): void
    {
        // Arrange
        $landlord = $this->landlordWithPlan();

        // Act
        $response = $this->actingAs($landlord)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->putJson("/landlord/profile/{$landlord->id}", [
                'name' => 'Updated Name',
                'email' => $landlord->email,
                'profile_type' => 'individual',
                'gender' => 'male',
            ]);

        // Assert
        $response->assertOk()
            ->assertJsonPath('message', 'Profile update successfully')
            ->assertJsonPath('redirect', route('landlord.profile.index'));

        $fresh = $landlord->fresh();
        $this->assertSame('Updated Name', $fresh->name);
        $this->assertSame('individual', $fresh->profile_type);
        $this->assertSame(1, (int) $fresh->is_setupped);
        $this->assertSame($landlord->id, $fresh->landlord_id);
        $this->assertSame('male', $fresh->user_details->gender);
    }

    public function test_f9_landlord_update_ignores_foreign_id(): void
    {
        // Arrange — D-IDOR (patrón Seg-29) aplicada en el slice 5: el
        // route-id se ignora y el target es siempre el usuario de sesión
        $attacker = $this->landlordWithPlan();
        $victim = $this->createLandlord(['name' => 'Victim']);
        $victimLandlordId = $victim->landlord_id;

        // Act
        $response = $this->actingAs($attacker)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->putJson("/landlord/profile/{$victim->id}", [
                'name' => 'Hacked Name',
                'email' => $attacker->email,
            ]);

        // Assert — se actualiza el propio; la cuenta ajena queda intacta
        // (antes: la víctima se sobrescribía con landlord_id del atacante)
        $response->assertOk();
        $this->assertSame('Hacked Name', $attacker->fresh()->name);
        $this->assertSame($attacker->id, $attacker->fresh()->landlord_id);
        $this->assertSame(1, (int) $attacker->fresh()->is_setupped);
        $this->assertSame('Victim', $victim->fresh()->name);
        $this->assertSame($victimLandlordId, $victim->fresh()->landlord_id);
    }

    public function test_f10_landlord_name_over_255_chars_is_rejected(): void
    {
        // Arrange — D-FIX-validation: 'string:255px' → 'string|max:255'
        // (antes: pasaba la validación rota y moría en la DB 1406 → 500)
        $landlord = $this->landlordWithPlan();
        $originalName = $landlord->name;

        // Act
        $response = $this->actingAs($landlord)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->putJson("/landlord/profile/{$landlord->id}", [
                'name' => str_repeat('a', 256),
                'email' => $landlord->email,
            ]);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
        $this->assertSame($originalName, $landlord->fresh()->name);
    }

    public function test_f11_api_profile_setup_updates_user_and_details(): void
    {
        // Arrange
        $landlord = $this->createLandlord();
        Sanctum::actingAs($landlord);

        // Act
        $response = $this->postJson('/api/v1/profile-setup', [
            'name' => 'Setup Name',
            'email' => $landlord->email,
            'profile_type' => 'company',
            'gender' => 'female',
            'company_info' => ['company' => 'ACME'],
        ]);

        // Assert
        $response->assertOk()->assertJsonPath('message', __('Profile updated successfully.'));

        $fresh = $landlord->fresh();
        $this->assertSame('Setup Name', $fresh->name);
        $this->assertSame('company', $fresh->profile_type);
        $this->assertSame(1, (int) $fresh->is_setupped);
        $this->assertSame('female', $fresh->user_details->gender);
        $this->assertSame('ACME', $fresh->user_details->company_info['company'] ?? null);
    }
}
