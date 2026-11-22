<?php

namespace Tests\Feature\Rent;

use App\Models\Option;
use App\Models\Property;
use App\Models\Rent;
use App\Models\SecurityDeposit;
use App\Models\Unit;
use App\Models\User;
use App\Notifications\SendNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * Contrato HTTP de Api/RentController (spec-service-rent-fase5, casos
 * F2, F5, F7, F8, F9, F11, F14, F15 del canal api).
 *
 * Tests de contrato, NO de caracterización: congelan el comportamiento
 * actual (quirks incluidos, spec §5.7) para garantizar que la migración a
 * RentService no cambia el contrato observable. Deben seguir en verde
 * antes y después de la migración — no llevan @group characterization.
 *
 * F14/F15 congelan quirks declarados en el spec: la rama no_paginate no
 * scopea por landlord y el input landlord_id filtra por landlord ajeno.
 */
class ApiRentHttpTest extends TestCase
{
    use CreatesUsers, DatabaseTransactions;

    private function makeRent(User $landlord, User $tenant, Property $property, string $status, ?Unit $unit = null): Rent
    {
        return Rent::create([
            'landlord_id' => $landlord->id,
            'tenant_id' => $tenant->id,
            'property_id' => $property->id,
            'unit_id' => $unit?->id,
            'monthly_rent' => 1000,
            'start_date' => '2026-12-01',
            'end_date' => '2027-01-15',
            'status' => $status,
        ]);
    }

    private function paidDeposit(User $landlord, User $tenant, Rent $rent): SecurityDeposit
    {
        return SecurityDeposit::create([
            'tenant_id' => $tenant->id,
            'landlord_id' => $landlord->id,
            'rent_id' => $rent->id,
            'property_id' => $rent->property_id,
            'deposit_amount' => 200,
            'status' => 'paid',
        ]);
    }

    public function test_f2_store_creates_rent_and_security_deposit(): void
    {
        // Arrange
        Notification::fake();
        Storage::fake('public');
        Option::factory()->create(['key' => 'service-charge', 'value' => ['amount' => 10, 'type' => 'percentage']]);

        $landlord = $this->createLandlord();
        $tenant = $this->createTenant(['landlord_id' => $landlord->id]);
        $property = Property::factory()->create(['landlord_id' => $landlord->id, 'type' => 'rent']);
        $unit = Unit::create([
            'property_id' => $property->id,
            'unit_name' => 'U1',
            'monthly_rent' => 1000,
            'security_deposit' => 200,
            'utility_deposit' => 50,
            'min_rental_value' => 1,
            'min_rental_unit' => 'months',
        ]);

        // Act
        Sanctum::actingAs($landlord, ['*']);
        $response = $this->postJson('/api/v1/landlord/rent', [
            'tenant_id' => $tenant->id,
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'start_date' => '2026-12-01',
            'end_date' => '2027-01-15',
            'landlord_agreement' => UploadedFile::fake()->create('agreement.pdf', 100, 'application/pdf'),
        ]);

        // Assert — shape + mismo cálculo congelado que F1 (web)
        $response->assertOk()->assertJson(['message' => 'Data save successfully']);

        $rent = Rent::where('property_id', $property->id)->first();
        $this->assertNotNull($rent);
        $response->assertJsonPath('data.id', $rent->id);
        $this->assertSame(1000.0, (float) $rent->monthly_rent);
        $this->assertSame(50.0, (float) $rent->utility_bill);
        $this->assertSame(1050.0, (float) $rent->total_amount);
        Storage::disk('public')->assertExists($rent->landlord_agreement);

        $deposit = SecurityDeposit::where('rent_id', $rent->id)->first();
        $this->assertSame(1250.0, (float) $deposit->total_amount);
        $this->assertSame(125.0, (float) $deposit->charge);
        $this->assertSame(1125.0, (float) $deposit->landlord_balance);
        $this->assertSame('unpaid', $deposit->status);

        Notification::assertSentTo([$tenant, $landlord], SendNotification::class);
    }

    public function test_f5_approved_activates_rent_with_notification(): void
    {
        // Arrange
        Notification::fake();

        $landlord = $this->createLandlord();
        $tenant = $this->createTenant(['landlord_id' => $landlord->id]);
        $property = Property::factory()->create(['landlord_id' => $landlord->id]);
        $rent = $this->makeRent($landlord, $tenant, $property, 'pending');
        $this->paidDeposit($landlord, $tenant, $rent);

        // Act
        Sanctum::actingAs($landlord, ['*']);
        $response = $this->postJson("/api/v1/landlord/rent/approved/{$rent->id}");

        // Assert — la rama api SÍ emite notificación (tabla §4 del spec)
        $response->assertOk()->assertJson([
            'message' => 'Rent approved successfully',
            'status' => ['id' => $rent->id, 'status' => 'active'],
        ]);
        $this->assertSame('active', $rent->fresh()->status);
        Notification::assertSentTo([$tenant, $landlord], SendNotification::class);
    }

    public function test_f7_tenant_cancelled_persists_cancel_info_and_status(): void
    {
        // Arrange
        Notification::fake();

        $landlord = $this->createLandlord();
        $tenant = $this->createTenant(['landlord_id' => $landlord->id]);
        $property = Property::factory()->create(['landlord_id' => $landlord->id]);
        $rent = $this->makeRent($landlord, $tenant, $property, 'active');

        // Act
        Sanctum::actingAs($tenant, ['*']);
        $response = $this->postJson("/api/v1/landlord/rent/cancelled/{$rent->id}", [
            'title' => 'Leaving the city',
            'reason' => 'Job relocation',
        ]);

        // Assert — misma lógica cancel_info+status que la web (F6)
        $response->assertOk()->assertJson(['message' => 'Rent cancelled successfully']);

        $rent = $rent->fresh();
        $this->assertSame('cancelled', $rent->status);
        $this->assertSame('Leaving the city', $rent->cancel_info['title']);
        $this->assertSame('Job relocation', $rent->cancel_info['reason']);
        $this->assertSame('2027-01-15', $rent->cancel_info['prev_end_date']);

        Notification::assertSentTo([$tenant, $landlord], SendNotification::class);
    }

    public function test_f8_agreement_upload_stores_file_without_transaction(): void
    {
        // Arrange
        Notification::fake();
        Storage::fake('public');

        $landlord = $this->createLandlord();
        $tenant = $this->createTenant(['landlord_id' => $landlord->id]);
        $property = Property::factory()->create(['landlord_id' => $landlord->id]);
        $rent = $this->makeRent($landlord, $tenant, $property, 'pending');

        // Act
        Sanctum::actingAs($tenant, ['*']);
        $response = $this->postJson("/api/v1/landlord/rent/agreement-sign/{$rent->id}", [
            'tenant_agreement' => UploadedFile::fake()->create('signed.pdf', 100, 'application/pdf'),
        ]);

        // Assert — 1 escritura sin tx + notificación (comportamiento actual)
        $response->assertOk()->assertJson(['message' => 'Agreement sent successfully']);

        $rent = $rent->fresh();
        $this->assertNotNull($rent->tenant_agreement);
        Storage::disk('public')->assertExists($rent->tenant_agreement);

        Notification::assertSentTo([$tenant, $landlord], SendNotification::class);
    }

    public function test_f9_tenant_accept_sets_processing_status(): void
    {
        // Arrange
        Notification::fake();

        $landlord = $this->createLandlord();
        $tenant = $this->createTenant(['landlord_id' => $landlord->id]);
        $property = Property::factory()->create(['landlord_id' => $landlord->id]);
        $rent = $this->makeRent($landlord, $tenant, $property, 'pending');

        // Act
        Sanctum::actingAs($tenant, ['*']);
        $response = $this->postJson("/api/v1/landlord/rent/accept/{$rent->id}");

        // Assert — estado de aceptación actual: pending → processing
        $response->assertOk()->assertJson([
            'message' => 'Rent accepted successfully',
            'status' => ['id' => $rent->id, 'status' => 'processing'],
        ]);
        $this->assertSame('processing', $rent->fresh()->status);
        Notification::assertSentTo([$tenant, $landlord], SendNotification::class);
    }

    public function test_f11_index_branches_by_role(): void
    {
        // Arrange
        $landlord = $this->createLandlord();
        $ownTenant = $this->createTenant(['landlord_id' => $landlord->id]);
        $ownRent = $this->makeRent($landlord, $ownTenant, Property::factory()->create(['landlord_id' => $landlord->id]), 'active');

        $otherLandlord = $this->createLandlord();
        $otherTenant = $this->createTenant(['landlord_id' => $otherLandlord->id]);
        $this->makeRent($otherLandlord, $otherTenant, Property::factory()->create(['landlord_id' => $otherLandlord->id]), 'active');

        // Act + Assert — rama landlord: solo rents propias
        Sanctum::actingAs($landlord, ['*']);
        $response = $this->getJson('/api/v1/landlord/rent');

        $response->assertOk()->assertJson(['message' => 'Data fetched successfully']);
        $ids = collect($response->json('data.data'))->pluck('id');
        $this->assertContains($ownRent->id, $ids);
        $this->assertSame([$ownRent->id], $ids->all());

        // Act + Assert — rama tenant: solo rents donde tenant_id = auth
        Sanctum::actingAs($ownTenant, ['*']);
        $response = $this->getJson('/api/v1/landlord/rent');

        $response->assertOk();
        $ids = collect($response->json('data.data'))->pluck('id');
        $this->assertContains($ownRent->id, $ids);
        $this->assertSame([$ownRent->id], $ids->all());
    }

    public function test_f14_index_no_paginate_returns_plain_collection(): void
    {
        // Arrange
        $landlord = $this->createLandlord();
        $tenant = $this->createTenant(['landlord_id' => $landlord->id]);
        $rent = $this->makeRent($landlord, $tenant, Property::factory()->create(['landlord_id' => $landlord->id]), 'active');

        // Act
        Sanctum::actingAs($landlord, ['*']);
        $response = $this->getJson('/api/v1/landlord/rent?no_paginate=true');

        // Assert — rama Collection sin metadatos de paginador
        $response->assertOk()->assertJson(['message' => 'Data fetched successfully']);
        $this->assertIsList($response->json('data.data'));
        $this->assertContains($rent->id, collect($response->json('data.data'))->pluck('id'));
    }

    public function test_f15_index_landlord_id_input_reads_foreign_landlord(): void
    {
        // Arrange — quirk declarado (spec §5.7): input landlord_id filtra
        // por landlord ajeno cuando el actor es landlord.
        $landlordA = $this->createLandlord();
        $landlordB = $this->createLandlord();
        $tenantB = $this->createTenant(['landlord_id' => $landlordB->id]);
        $rentA = $this->makeRent($landlordA, $this->createTenant(['landlord_id' => $landlordA->id]), Property::factory()->create(['landlord_id' => $landlordA->id]), 'active');
        $rentB = $this->makeRent($landlordB, $tenantB, Property::factory()->create(['landlord_id' => $landlordB->id]), 'active');

        // Act
        Sanctum::actingAs($landlordA, ['*']);
        $response = $this->getJson("/api/v1/landlord/rent?landlord_id={$landlordB->id}");

        // Assert — congela la lectura cruzada; el fix es fase de seguridad
        $response->assertOk();
        $ids = collect($response->json('data.data'))->pluck('id');
        $this->assertContains($rentB->id, $ids);
        $this->assertNotContains($rentA->id, $ids);
    }
}
