<?php

namespace Tests\Feature\Rent;

use App\Models\Option;
use App\Models\PlanSubscribe;
use App\Models\Property;
use App\Models\Rent;
use App\Models\SecurityDeposit;
use App\Models\Unit;
use App\Models\User;
use App\Notifications\SendNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * Contrato HTTP de Landlord/RentInvitationController (spec-service-rent-fase5,
 * casos F1, F3, F4, F6, F10, F12, F13 del canal web).
 *
 * Tests de contrato, NO de caracterización: congelan el comportamiento
 * actual (quirks incluidos, spec §5.7) para garantizar que la migración a
 * RentService no cambia el contrato observable. Deben seguir en verde
 * antes y después de la migración — no llevan @group characterization.
 *
 * Números congelados del bloque service-charge (nota §6 del spec):
 *   unit(monthly_rent=1000, security_deposit=200, utility_deposit=50) con
 *   Option service-charge {amount: 10, type: percentage} =>
 *   rent.total_amount=1050, deposit.total_amount=1250, charge=125,
 *   deposit.landlord_balance=1125 (1250 - 10%).
 */
class LandlordRentHttpTest extends TestCase
{
    use CreatesUsers, DatabaseTransactions;

    /**
     * Todo landlord web necesita PlanSubscribe vigente: además del bloqueo
     * de rutas de ExpiredMiddleware, su línea post-check accede
     * $plan_subscribe->plan sin null-guard (crash 500 si no hay plan).
     */
    private function landlordWithSubscription(): User
    {
        $landlord = $this->createLandlord();
        PlanSubscribe::factory()->create(['user_id' => $landlord->id]);

        return $landlord;
    }

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

    public function test_f1_store_creates_rent_and_security_deposit(): void
    {
        // Arrange
        Notification::fake();
        Storage::fake('public');
        Option::factory()->create(['key' => 'service-charge', 'value' => ['amount' => 10, 'type' => 'percentage']]);

        $landlord = $this->landlordWithSubscription();
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

        // Act — 45 días ≥ mínimo 1 month (30 días)
        $response = $this->actingAs($landlord)->postJson('/landlord/rents', [
            'tenant_id' => $tenant->id,
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'start_date' => '2026-12-01',
            'end_date' => '2027-01-15',
            'landlord_agreement' => UploadedFile::fake()->create('agreement.pdf', 100, 'application/pdf'),
        ]);

        // Assert — shape + cálculo congelado
        $response->assertOk()->assertJson([
            'message' => 'Rent Invitation sent successfully',
            'redirect' => route('landlord.rents.index'),
        ]);

        $rent = Rent::where('property_id', $property->id)->first();
        $this->assertNotNull($rent);
        $this->assertSame($landlord->id, $rent->landlord_id);
        $this->assertSame(1000.0, (float) $rent->monthly_rent);
        $this->assertSame(50.0, (float) $rent->utility_bill);
        $this->assertSame(1050.0, (float) $rent->total_amount);
        Storage::disk('public')->assertExists($rent->landlord_agreement);

        $deposit = SecurityDeposit::where('rent_id', $rent->id)->first();
        $this->assertNotNull($deposit);
        $this->assertSame(200.0, (float) $deposit->deposit_amount);
        $this->assertSame(1000.0, (float) $deposit->rent_advance);
        $this->assertSame(50.0, (float) $deposit->utility_advance);
        $this->assertSame(1250.0, (float) $deposit->total_amount);
        $this->assertSame(125.0, (float) $deposit->charge);
        $this->assertSame(1125.0, (float) $deposit->landlord_balance);
        $this->assertSame('unpaid', $deposit->status);

        Notification::assertSentTo([$tenant, $landlord], SendNotification::class);
    }

    public function test_f3_store_rejects_period_below_minimum_for_every_unit(): void
    {
        // Arrange
        Notification::fake();
        Storage::fake('public');
        Option::factory()->create(['key' => 'service-charge', 'value' => ['amount' => 10, 'type' => 'percentage']]);

        $landlord = $this->landlordWithSubscription();
        $tenant = $this->createTenant(['landlord_id' => $landlord->id]);

        // Multiplicadores del match: days=1, weeks=7, months=30, years=365
        $minimums = ['days' => 1, 'weeks' => 7, 'months' => 30, 'years' => 365];

        foreach ($minimums as $minUnit => $minDays) {
            $property = Property::factory()->create(['landlord_id' => $landlord->id, 'type' => 'rent']);
            $unit = Unit::create([
                'property_id' => $property->id,
                'unit_name' => 'U-'.$minUnit,
                'monthly_rent' => 1000,
                'security_deposit' => 200,
                'utility_deposit' => 50,
                'min_rental_value' => 1,
                'min_rental_unit' => $minUnit,
            ]);

            // Act — rentalDays = minDays - 1, justo bajo el mínimo
            $response = $this->actingAs($landlord)->postJson('/landlord/rents', [
                'tenant_id' => $tenant->id,
                'property_id' => $property->id,
                'unit_id' => $unit->id,
                'start_date' => '2026-12-01',
                'end_date' => Carbon::parse('2026-12-01')->addDays($minDays - 1)->toDateString(),
                'landlord_agreement' => UploadedFile::fake()->create('agreement.pdf', 100, 'application/pdf'),
            ]);

            // Assert — 422 con el mensaje exacto del controlador actual
            $response->assertStatus(422)->assertJson([
                'message' => "The rental period must be at least 1 {$minUnit}.",
            ]);
            $this->assertDatabaseMissing('rents', ['property_id' => $property->id]);
        }
    }

    public function test_f4_approved_activates_rent_without_notification(): void
    {
        // Arrange
        Notification::fake();

        $landlord = $this->landlordWithSubscription();
        $tenant = $this->createTenant(['landlord_id' => $landlord->id]);
        $property = Property::factory()->create(['landlord_id' => $landlord->id]);
        $rent = $this->makeRent($landlord, $tenant, $property, 'pending');

        SecurityDeposit::create([
            'tenant_id' => $tenant->id,
            'landlord_id' => $landlord->id,
            'rent_id' => $rent->id,
            'property_id' => $property->id,
            'deposit_amount' => 200,
            'status' => 'paid',
        ]);

        // Act
        $response = $this->actingAs($landlord)->postJson("/landlord/rents/approved/{$rent->id}");

        // Assert — la rama web no emite notificación (tabla §4 del spec)
        $response->assertOk()->assertJson([
            'message' => 'Rent approved successfully',
            'redirect' => route('landlord.rents.index'),
        ]);
        $this->assertSame('active', $rent->fresh()->status);
        Notification::assertNothingSent();
    }

    public function test_f6_canceled_persists_cancel_info_and_status(): void
    {
        // Arrange
        Notification::fake();

        $landlord = $this->landlordWithSubscription();
        $tenant = $this->createTenant(['landlord_id' => $landlord->id]);
        $property = Property::factory()->create(['landlord_id' => $landlord->id]);
        $rent = $this->makeRent($landlord, $tenant, $property, 'active');

        // Act
        $response = $this->actingAs($landlord)->postJson("/landlord/rents/canceled/{$rent->id}", [
            'title' => 'Leaving the city',
            'reason' => 'Job relocation',
        ]);

        // Assert — cancel_info {title, reason, prev_end_date derivado} + status
        $response->assertOk()->assertJson(['message' => 'Rent cancelled successfully']);

        $rent = $rent->fresh();
        $this->assertSame('cancelled', $rent->status);

        $cancelInfo = $rent->cancel_info;
        $this->assertSame('Leaving the city', $cancelInfo['title']);
        $this->assertSame('Job relocation', $cancelInfo['reason']);
        $this->assertSame('2027-01-15', $cancelInfo['prev_end_date']);

        // La cancelación notifica en ambos canales (tabla §4)
        Notification::assertSentTo([$tenant, $landlord], SendNotification::class);
    }

    public function test_f10_index_loads_tenants_properties_and_rents(): void
    {
        // Arrange
        $landlord = $this->landlordWithSubscription();
        $tenant = $this->createTenant(['landlord_id' => $landlord->id]);
        $property = Property::factory()->create([
            'landlord_id' => $landlord->id,
            'type' => 'rent',
            'status' => 1,
            'rent_info' => ['monthly_rent' => 1000, 'min_rental_value' => 1, 'min_rental_unit' => 'months'],
        ]);
        $rent = $this->makeRent($landlord, $tenant, $property, 'pending');

        // Act
        $response = $this->actingAs($landlord)->get('/landlord/rents');

        // Assert — dropdowns (tenants/properties) + listado paginado
        $response->assertOk()->assertViewIs('landlord.rents.index');
        $this->assertCount(1, $response->viewData('tenants'));
        $this->assertTrue($response->viewData('properties')->contains($property));
        $this->assertSame($rent->id, $response->viewData('rents')->items()[0]->id);
    }

    public function test_f12_get_unit_lists_free_units_only(): void
    {
        // Arrange
        $landlord = $this->landlordWithSubscription();
        $tenant = $this->createTenant(['landlord_id' => $landlord->id]);
        $property = Property::factory()->create(['landlord_id' => $landlord->id, 'type' => 'rent']);

        $free = Unit::create(['property_id' => $property->id, 'unit_name' => 'Free A']);
        $taken = Unit::create(['property_id' => $property->id, 'unit_name' => 'Taken B']);
        $this->makeRent($landlord, $tenant, $property, 'active', $taken);

        // Act
        $response = $this->actingAs($landlord)->getJson("/landlord/get-units?property_id={$property->id}");

        // Assert — solo unidades sin rent activa/processing
        $response->assertOk();
        $this->assertSame(['Free A'], collect($response->json())->pluck('unit_name')->all());
    }

    public function test_f13_filter_excel_and_csv_routes_stay_alive(): void
    {
        // Arrange
        $landlord = $this->landlordWithSubscription();
        $tenant = $this->createTenant(['landlord_id' => $landlord->id, 'name' => 'Contract Tenant One']);
        $property = Property::factory()->create(['landlord_id' => $landlord->id, 'type' => 'rent']);
        $this->makeRent($landlord, $tenant, $property, 'pending');

        // Act — filter ajax renderiza las filas propias del landlord
        $filter = $this->actingAs($landlord)->postJson('/landlord/rents/filter', [
            'search' => 'Contract Tenant One',
        ], ['X-Requested-With' => 'XMLHttpRequest']);

        // Assert
        $filter->assertOk()->assertJsonStructure(['data']);
        $this->assertStringContainsString('Contract Tenant One', $filter->json('data'));

        // Exports vivos (paraguas §5.4: tras la migración delegan al service)
        $this->actingAs($landlord)->get('/landlord/rents-excel')->assertOk();
        $this->actingAs($landlord)->get('/landlord/rents-csv')->assertOk();
    }
}
