<?php

namespace Tests\Feature\Application;

use App\Models\Application;
use App\Models\Category;
use App\Models\Option;
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
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * Contrato HTTP de Api/TenantApplicationController (spec-service-application-fase5,
 * casos F1–F4 del canal api tenant + F13 de refuerzo sobre el index).
 *
 * Tests de contrato, NO de caracterización: congelan el comportamiento
 * actual (quirks incluidos, spec §4) para garantizar que la migración a
 * ApplicationService no cambia el contrato observable. Deben seguir en
 * verde antes y después de la migración — no llevan @group characterization.
 *
 * Quirks congelados aquí:
 *  - F1a: `document_file` se sube a storage pero NO se persiste (la clave
 *    cae en mass-assignment y no existe columna) — el path se descarta.
 *  - F3/F4: rent.unit_id solo se puebla en la rama category 6 (aunque la
 *    application tenga unit_id, las ramas 1–8 no lo propagan al Rent).
 *
 * Números congelados del bloque service-charge (Option {amount: 10,
 * type: percentage}): property(security_deposit=200, monthly_rent=1000,
 * utility_deposit=50) → charge base 1250, charge 125.
 */
class TenantApplicationHttpTest extends TestCase
{
    use CreatesUsers, DatabaseTransactions;

    private function categoryWithId(int $id): Category
    {
        return Category::find($id) ?? Category::forceCreate([
            'id' => $id,
            'name' => "Category {$id}",
            'status' => 1,
        ]);
    }

    /**
     * Property del fixture congelado: deposit 200, monthly 1000, utility 50.
     */
    private function propertyFor(User $landlord, int $categoryId): Property
    {
        return Property::factory()->create([
            'landlord_id' => $landlord->id,
            'category_id' => $this->categoryWithId($categoryId)->id,
            'security_deposit' => 200,
            'utility_deposit' => 50,
            'rent_info' => ['property_rent' => 1000, 'monthly_rent' => 1000],
        ]);
    }

    private function makeApplication(User $tenant, Property $property, array $attributes = []): Application
    {
        // $attributes primero: permite sobreescribir los defaults.
        return Application::create($attributes + [
            'tenant_id' => $tenant->id,
            'property_id' => $property->id,
            'status' => 1,
            'start_date' => '2026-12-01',
            'end_date' => '2027-01-15',
        ]);
    }

    public function test_f1a_store_creates_application_for_tenant(): void
    {
        // Arrange
        Notification::fake();
        Storage::fake('public');

        $landlord = $this->createLandlord();
        $tenant = $this->createTenant(['landlord_id' => $landlord->id]);
        $property = $this->propertyFor($landlord, 1);

        // Act
        Sanctum::actingAs($tenant, ['*']);
        $response = $this->postJson('/api/v1/tenant/application', [
            'property_id' => $property->id,
            'start_date' => '2026-12-01',
            'end_date' => '2027-01-15',
            'document_file' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
        ]);

        // Assert — shape actual + fila creada con el payload visible
        $response->assertOk()->assertJson(['message' => 'Application created successfully.']);

        $application = Application::where('property_id', $property->id)->first();
        $this->assertNotNull($application);
        $response->assertJsonPath('data.id', $application->id);
        $this->assertSame($tenant->id, $application->tenant_id);
        $this->assertSame('2026-12-01', $application->start_date);
        $this->assertSame('2027-01-15', $application->end_date);

        // Quirk congelado: el archivo se sube a storage pero el path se
        // descarta (sin columna) — queda 1 archivo huérfano en el disk.
        $this->assertCount(1, Storage::disk('public')->allFiles());

        Notification::assertSentTo([$tenant, $landlord], SendNotification::class);
    }

    public function test_f1b_store_rejects_duplicate_application(): void
    {
        // Arrange
        Notification::fake();

        $landlord = $this->createLandlord();
        $tenant = $this->createTenant(['landlord_id' => $landlord->id]);
        $property = $this->propertyFor($landlord, 1);
        $this->makeApplication($tenant, $property);

        // Act
        Sanctum::actingAs($tenant, ['*']);
        $response = $this->postJson('/api/v1/tenant/application', [
            'property_id' => $property->id,
        ]);

        // Assert — 409 con el mensaje exacto del controlador actual
        $response->assertStatus(409)->assertJson([
            'message' => 'You have already apply for this property',
        ]);
        $this->assertSame(1, Application::where('property_id', $property->id)->count());
    }

    public function test_f1c_store_rejects_period_below_minimum_for_every_unit(): void
    {
        // Arrange
        Notification::fake();

        $landlord = $this->createLandlord();
        $tenant = $this->createTenant(['landlord_id' => $landlord->id]);

        // Multiplicadores del match: days=1, weeks=7, months=30, years=365
        $minimums = ['days' => 1, 'weeks' => 7, 'months' => 30, 'years' => 365];

        foreach ($minimums as $minUnit => $minDays) {
            $property = $this->propertyFor($landlord, 1);
            $unit = Unit::create([
                'property_id' => $property->id,
                'unit_name' => 'U-'.$minUnit,
                'min_rental_value' => 1,
                'min_rental_unit' => $minUnit,
            ]);

            // Act — rentalDays = minDays - 1, justo bajo el mínimo
            Sanctum::actingAs($tenant, ['*']);
            $response = $this->postJson('/api/v1/tenant/application', [
                'property_id' => $property->id,
                'unit_id' => $unit->id,
                'start_date' => '2026-12-01',
                'end_date' => Carbon::parse('2026-12-01')->addDays($minDays - 1)->toDateString(),
            ]);

            // Assert — 422 con el mensaje exacto del controlador actual
            $response->assertStatus(422)->assertJson([
                'message' => "The rental period must be at least 1 {$minUnit}.",
            ]);
            $this->assertDatabaseMissing('applications', ['property_id' => $property->id]);
        }
    }

    public function test_f2_show_returns_application_with_security_deposit_shape(): void
    {
        // Arrange
        $landlord = $this->createLandlord();
        $tenant = $this->createTenant(['landlord_id' => $landlord->id]);
        $property = $this->propertyFor($landlord, 1);
        $application = $this->makeApplication($tenant, $property);

        $deposit = SecurityDeposit::create([
            'tenant_id' => $tenant->id,
            'landlord_id' => $landlord->id,
            'property_id' => $property->id,
            'deposit_amount' => 200,
            'status' => 'paid',
        ]);

        // Act
        Sanctum::actingAs($tenant, ['*']);
        $response = $this->getJson("/api/v1/tenant/application/{$application->id}");

        // Assert — shape actual (sin punto final en el mensaje) +
        // security_deposit {id, status} buscado solo por property_id
        $response->assertOk()->assertJson([
            'message' => 'Data fetched successfully',
            'data' => ['id' => $application->id],
        ]);
        $response->assertJsonPath('data.security_deposit.id', $deposit->id);
        $response->assertJsonPath('data.security_deposit.status', 'paid');
    }

    public function test_f3_agreement_sign_creates_deposit_and_rent_for_category_1(): void
    {
        // Arrange
        Notification::fake();
        Storage::fake('public');
        Option::factory()->create(['key' => 'service-charge', 'value' => ['amount' => 10, 'type' => 'percentage']]);

        $landlord = $this->createLandlord();
        $tenant = $this->createTenant(['landlord_id' => $landlord->id]);
        $property = $this->propertyFor($landlord, 1);
        $application = $this->makeApplication($tenant, $property, [
            'landlord_agreement' => 'uploads/26/11/landlord.pdf',
        ]);

        // Act
        Sanctum::actingAs($tenant, ['*']);
        $response = $this->postJson("/api/v1/tenant/application/sign/{$application->id}", [
            'tenant_agreement' => UploadedFile::fake()->create('signed.pdf', 100, 'application/pdf'),
        ]);

        // Assert — 200 + cálculo congelado (idéntico al actual)
        $response->assertOk()->assertJson(['message' => 'Agreement signed successfully']);

        $application = $application->fresh();
        $this->assertNotNull($application->tenant_agreement);
        Storage::disk('public')->assertExists($application->tenant_agreement);

        $deposit = SecurityDeposit::where('property_id', $property->id)->first();
        $this->assertNotNull($deposit);
        $this->assertSame($tenant->id, $deposit->tenant_id);
        $this->assertSame($landlord->id, $deposit->landlord_id);
        $this->assertSame(200.0, (float) $deposit->deposit_amount);
        $this->assertSame(1000.0, (float) $deposit->rent_advance);
        $this->assertSame(50.0, (float) $deposit->utility_advance);
        $this->assertSame(1250.0, (float) $deposit->total_amount);
        $this->assertSame(125.0, (float) $deposit->charge);
        $this->assertSame(1125.0, (float) $deposit->landlord_balance);
        $this->assertSame('unpaid', $deposit->status);

        $rent = Rent::where('property_id', $property->id)->first();
        $this->assertNotNull($rent);
        $this->assertSame($deposit->rent_id, $rent->id);
        $this->assertSame(1000.0, (float) $rent->monthly_rent);
        $this->assertSame(50.0, (float) $rent->utility_bill);
        $this->assertSame(1050.0, (float) $rent->total_amount);
        $this->assertSame('2026-12-01', $rent->start_date);
        $this->assertSame('2027-01-15', $rent->end_date);
        $this->assertSame('uploads/26/11/landlord.pdf', $rent->landlord_agreement);
        $this->assertSame($application->tenant_agreement, $rent->tenant_agreement);

        Notification::assertSentTo([$tenant, $landlord], SendNotification::class);
    }

    /**
     * Tabla de ramas congelada (spec §6 F4): cats 2–3 incluyen utility en el
     * SecurityDeposit, cats 4/5/7/8 lo excluyen (deposit.utility_advance cae
     * en el default DB 0, mientras rent.utility_bill queda NULL porque se
     * inserta explícito desde el atributo ausente del modelo en memoria),
     * cat 6 resuelve todo desde la unidad.
     */
    public function test_f4_agreement_sign_freezes_branch_table_for_categories_2_to_8(): void
    {
        // property: deposit 200 + monthly 1000 + utility 50 → charge base 1250
        // unit (cat 6): deposit 300 + monthly 2000 + utility 70 → base 2370
        $branches = [
            2 => ['deposit_total' => 1250.0, 'balance' => 1125.0, 'charge' => 125.0, 'deposit_utility' => 50.0, 'rent_utility' => 50.0, 'rent_monthly' => 1000.0, 'rent_total' => 1050.0],
            3 => ['deposit_total' => 1250.0, 'balance' => 1125.0, 'charge' => 125.0, 'deposit_utility' => 50.0, 'rent_utility' => 50.0, 'rent_monthly' => 1000.0, 'rent_total' => 1050.0],
            4 => ['deposit_total' => 1200.0, 'balance' => 1075.0, 'charge' => 125.0, 'deposit_utility' => 0.0, 'rent_utility' => null, 'rent_monthly' => 1000.0, 'rent_total' => 1050.0],
            5 => ['deposit_total' => 1200.0, 'balance' => 1075.0, 'charge' => 125.0, 'deposit_utility' => 0.0, 'rent_utility' => null, 'rent_monthly' => 1000.0, 'rent_total' => 1050.0],
            7 => ['deposit_total' => 1200.0, 'balance' => 1075.0, 'charge' => 125.0, 'deposit_utility' => 0.0, 'rent_utility' => null, 'rent_monthly' => 1000.0, 'rent_total' => 1050.0],
            8 => ['deposit_total' => 1200.0, 'balance' => 1075.0, 'charge' => 125.0, 'deposit_utility' => 0.0, 'rent_utility' => null, 'rent_monthly' => 1000.0, 'rent_total' => 1050.0],
            6 => ['deposit_total' => 2370.0, 'balance' => 2133.0, 'charge' => 237.0, 'deposit_utility' => 70.0, 'rent_utility' => 70.0, 'rent_monthly' => 2000.0, 'rent_total' => 2070.0],
        ];

        foreach ($branches as $categoryId => $expected) {
            // Arrange — fixture por rama
            Notification::fake();
            Storage::fake('public');
            Option::firstOrCreate(['key' => 'service-charge'], ['value' => ['amount' => 10, 'type' => 'percentage']]);

            $landlord = $this->createLandlord();
            $tenant = $this->createTenant(['landlord_id' => $landlord->id]);
            $property = $this->propertyFor($landlord, $categoryId);

            $unit = null;
            if ($categoryId === 6) {
                $unit = Unit::create([
                    'property_id' => $property->id,
                    'unit_name' => 'U-cat6',
                    'monthly_rent' => 2000,
                    'security_deposit' => 300,
                    'utility_deposit' => 70,
                ]);
            }

            $application = $this->makeApplication($tenant, $property, ['unit_id' => $unit?->id]);

            // Act
            Sanctum::actingAs($tenant, ['*']);
            $response = $this->postJson("/api/v1/tenant/application/sign/{$application->id}", [
                'tenant_agreement' => UploadedFile::fake()->create('signed.pdf', 100, 'application/pdf'),
            ]);

            // Assert — rama {$categoryId}: mismos números que el código actual
            $response->assertOk()->assertJson(['message' => 'Agreement signed successfully']);

            $deposit = SecurityDeposit::where('property_id', $property->id)->first();
            $this->assertNotNull($deposit, "cat {$categoryId}: deposit no creado");
            $this->assertSame($expected['deposit_total'], (float) $deposit->total_amount, "cat {$categoryId}: deposit.total_amount");
            $this->assertSame($expected['balance'], (float) $deposit->landlord_balance, "cat {$categoryId}: deposit.landlord_balance");
            $this->assertSame($expected['charge'], (float) $deposit->charge, "cat {$categoryId}: deposit.charge");
            $this->assertSame($expected['deposit_utility'], (float) $deposit->utility_advance, "cat {$categoryId}: deposit.utility_advance");

            $rent = Rent::where('property_id', $property->id)->first();
            $this->assertNotNull($rent, "cat {$categoryId}: rent no creado");
            $this->assertSame($expected['rent_monthly'], (float) $rent->monthly_rent, "cat {$categoryId}: rent.monthly_rent");
            $this->assertSame($expected['rent_total'], (float) $rent->total_amount, "cat {$categoryId}: rent.total_amount");

            if ($expected['rent_utility'] === null) {
                $this->assertNull($rent->utility_bill, "cat {$categoryId}: rent.utility_bill debe ser NULL");
            } else {
                $this->assertSame($expected['rent_utility'], (float) $rent->utility_bill, "cat {$categoryId}: rent.utility_bill");
            }

            if ($categoryId === 6) {
                $this->assertSame($unit->id, $deposit->unit_id, 'cat 6: deposit.unit_id');
                $this->assertSame($unit->id, $rent->unit_id, 'cat 6: rent.unit_id');
            } else {
                // Quirk congelado: las ramas 1–8 no propagan unit_id al Rent
                $this->assertNull($rent->unit_id, "cat {$categoryId}: rent.unit_id debe ser NULL");
            }

            $this->assertSame($deposit->rent_id, $rent->id, "cat {$categoryId}: deposit.rent_id");
            Notification::assertSentTo([$tenant, $landlord], SendNotification::class);
        }
    }

    /**
     * Caso de refuerzo añadido en implementación (spec §11): el index api
     * brancha por rol — landlord ve applications de sus properties aunque
     * la ruta viva bajo el prefijo tenant.
     */
    public function test_f13_index_branches_by_role(): void
    {
        // Arrange
        $landlord = $this->createLandlord();
        $tenant = $this->createTenant(['landlord_id' => $landlord->id]);
        $ownApplication = $this->makeApplication($tenant, $this->propertyFor($landlord, 1));

        $otherLandlord = $this->createLandlord();
        $otherTenant = $this->createTenant(['landlord_id' => $otherLandlord->id]);
        $this->makeApplication($otherTenant, $this->propertyFor($otherLandlord, 1));

        // Act + Assert — rama tenant: solo applications propias
        Sanctum::actingAs($tenant, ['*']);
        $response = $this->getJson('/api/v1/tenant/application');

        $response->assertOk()->assertJson(['message' => 'Data fetched successfully.']);
        $ids = collect($response->json('data.data'))->pluck('id');
        $this->assertSame([$ownApplication->id], $ids->all());

        // Act + Assert — rama landlord: applications sobre sus properties
        Sanctum::actingAs($landlord, ['*']);
        $response = $this->getJson('/api/v1/tenant/application');

        $response->assertOk();
        $ids = collect($response->json('data.data'))->pluck('id');
        $this->assertSame([$ownApplication->id], $ids->all());
    }
}
