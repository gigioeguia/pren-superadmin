<?php

namespace Tests\Feature\Application;

use App\Models\Application;
use App\Models\PlanSubscribe;
use App\Models\Property;
use App\Models\Rent;
use App\Models\SecurityDeposit;
use App\Models\User;
use App\Notifications\SendNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Group;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * Contrato HTTP de Landlord/ApplicationController (web) y
 * Api/LandlordApplicationController (api) — spec-service-application-fase5,
 * casos F5–F12.
 *
 * Tests de contrato congelando el comportamiento actual por canal
 * (divergencias de la tabla §4 del spec): el canal web no notifica y
 * usa los ordenes de checks A (reject) / atributo landlord_agreement
 * (file_download); el canal api notifica, responde 409 en el guard de
 * reject y lee el atributo inexistente `file` (quirk F11).
 *
 * F10/F11 son caracterizaciones de bug conocido (quirk file_download):
 * llevan @group characterization y se espera que sigan pasando — se
 * romperán solo en un follow-up futuro fuera de 5.13.
 */
class LandlordApplicationHttpTest extends TestCase
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

    private function makeProperty(User $landlord): Property
    {
        return Property::factory()->create(['landlord_id' => $landlord->id]);
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

    /**
     * Fixture del approve: application firmada por el tenant + deposit
     * pagado + rent vinculado (unit_id null en ambos lados del lookup).
     *
     * @return array{application: Application, rent: Rent}
     */
    private function applicationReadyForApproval(User $landlord, User $tenant, string $depositStatus = 'paid'): array
    {
        Storage::fake('public');
        Storage::disk('public')->put('uploads/26/11/tenant.pdf', 'signed-content');

        $property = $this->makeProperty($landlord);
        $application = $this->makeApplication($tenant, $property, [
            'tenant_agreement' => 'uploads/26/11/tenant.pdf',
        ]);

        $rent = Rent::create([
            'landlord_id' => $landlord->id,
            'tenant_id' => $tenant->id,
            'property_id' => $property->id,
            'monthly_rent' => 1000,
            'start_date' => '2026-12-01',
            'end_date' => '2027-01-15',
            'status' => 'pending',
        ]);

        SecurityDeposit::create([
            'tenant_id' => $tenant->id,
            'landlord_id' => $landlord->id,
            'rent_id' => $rent->id,
            'property_id' => $property->id,
            'deposit_amount' => 200,
            'status' => $depositStatus,
        ]);

        return ['application' => $application, 'rent' => $rent];
    }

    public function test_f5_processing_web_uploads_agreement_without_notification(): void
    {
        // Arrange
        Notification::fake();
        Storage::fake('public');

        $landlord = $this->landlordWithSubscription();
        $tenant = $this->createTenant(['landlord_id' => $landlord->id]);
        $application = $this->makeApplication($tenant, $this->makeProperty($landlord));

        // Act
        $response = $this->actingAs($landlord)->postJson("/landlord/application/processing/{$application->id}", [
            'landlord_agreement' => UploadedFile::fake()->create('agreement.pdf', 100, 'application/pdf'),
        ]);

        // Assert — upload + status 1; el canal web NO notifica (tabla §4)
        $response->assertOk()->assertJson([
            'message' => 'Application processes successfully',
            'redirect' => route('landlord.tenant-applications.show', $application->id),
        ]);

        $application = $application->fresh();
        $this->assertSame(1, $application->status);
        $this->assertNotNull($application->landlord_agreement);
        Storage::disk('public')->assertExists($application->landlord_agreement);

        Notification::assertNothingSent();

        // Sin archivo el canal web exige landlord_agreement (required)
        $this->actingAs($landlord)
            ->postJson("/landlord/application/processing/{$application->id}")
            ->assertStatus(422);
    }

    public function test_f6_processing_api_uploads_agreement_and_notifies(): void
    {
        // Arrange
        Notification::fake();
        Storage::fake('public');

        $landlord = $this->createLandlord();
        $tenant = $this->createTenant(['landlord_id' => $landlord->id]);
        $application = $this->makeApplication($tenant, $this->makeProperty($landlord), [
            'landlord_agreement' => 'uploads/26/11/old.pdf',
        ]);

        // Act
        Sanctum::actingAs($landlord, ['*']);
        $response = $this->postJson("/api/v1/landlord/application/processing/{$application->id}", [
            'landlord_agreement' => UploadedFile::fake()->create('agreement.pdf', 100, 'application/pdf'),
        ]);

        // Assert — idem web + sendNotification (tabla §4) + shape data
        $response->assertOk()->assertJson(['message' => 'Application processes successfully'])
            ->assertJsonPath('data.id', $application->id);

        $application = $application->fresh();
        $this->assertSame(1, $application->status);
        Storage::disk('public')->assertExists($application->landlord_agreement);

        Notification::assertSentTo([$tenant, $landlord], SendNotification::class);

        // Quirk congelado: el canal api acepta POST sin archivo y
        // SOBRESCRIBE landlord_agreement con null.
        Sanctum::actingAs($landlord, ['*']);
        $this->postJson("/api/v1/landlord/application/processing/{$application->id}")
            ->assertOk();

        $this->assertNull($application->fresh()->landlord_agreement);
    }

    public function test_f7a_approve_web_activates_rent_without_notification(): void
    {
        // Arrange
        Notification::fake();

        $landlord = $this->landlordWithSubscription();
        $tenant = $this->createTenant(['landlord_id' => $landlord->id]);
        $fixture = $this->applicationReadyForApproval($landlord, $tenant);
        $application = $fixture['application'];
        $rent = $fixture['rent'];

        // Act
        $response = $this->actingAs($landlord)->postJson("/landlord/application/approved/{$application->id}");

        // Assert — deposit paid → status 2 + rent activo; web sin notif
        $response->assertOk()->assertJson([
            'message' => 'Application approved successfully',
            'redirect' => route('landlord.tenant-applications.show', $application->id),
        ]);

        $this->assertSame(2, $application->fresh()->status);
        $this->assertSame('active', $rent->fresh()->status);

        Notification::assertNothingSent();
    }

    public function test_f7b_approve_api_activates_rent_and_notifies(): void
    {
        // Arrange
        Notification::fake();

        $landlord = $this->createLandlord();
        $tenant = $this->createTenant(['landlord_id' => $landlord->id]);
        $fixture = $this->applicationReadyForApproval($landlord, $tenant);
        $application = $fixture['application'];
        $rent = $fixture['rent'];

        // Act
        Sanctum::actingAs($landlord, ['*']);
        $response = $this->postJson("/api/v1/landlord/application/approved/{$application->id}");

        // Assert — idem web + sendNotification (tabla §4)
        $response->assertOk()->assertJson([
            'message' => 'Application approved successfully',
        ])->assertJsonPath('data.id', $application->id);

        $this->assertSame(2, $application->fresh()->status);
        $this->assertSame('active', $rent->fresh()->status);

        Notification::assertSentTo([$tenant, $landlord], SendNotification::class);
    }

    public function test_f7c_approve_unpaid_diverges_message_between_channels(): void
    {
        // Arrange — deposit unpaid
        Notification::fake();

        $landlord = $this->landlordWithSubscription();
        $tenant = $this->createTenant(['landlord_id' => $landlord->id]);
        $fixture = $this->applicationReadyForApproval($landlord, $tenant, 'unpaid');
        $application = $fixture['application'];

        // Act + Assert — web: 404 'Application Unpaid'
        $this->actingAs($landlord)
            ->postJson("/landlord/application/approved/{$application->id}")
            ->assertStatus(404)
            ->assertJson(['message' => 'Application Unpaid']);

        // Act + Assert — api: 404 'Application security deposit unpaid'
        Sanctum::actingAs($landlord, ['*']);
        $this->postJson("/api/v1/landlord/application/approved/{$application->id}")
            ->assertStatus(404)
            ->assertJson(['message' => 'Application security deposit unpaid']);

        $this->assertSame(1, $application->fresh()->status);
    }

    public function test_f7d_approve_without_tenant_agreement_diverges_status_code(): void
    {
        // Arrange — application sin tenant_agreement
        Notification::fake();

        $landlord = $this->landlordWithSubscription();
        $tenant = $this->createTenant(['landlord_id' => $landlord->id]);
        $property = $this->makeProperty($landlord);
        $application = $this->makeApplication($tenant, $property);

        // Act + Assert — web: 404
        $this->actingAs($landlord)
            ->postJson("/landlord/application/approved/{$application->id}")
            ->assertStatus(404)
            ->assertJson(['message' => 'Tenant agreement file does not exist']);

        // Act + Assert — api: 200 con el mismo mensaje (quirk sin código)
        Sanctum::actingAs($landlord, ['*']);
        $this->postJson("/api/v1/landlord/application/approved/{$application->id}")
            ->assertOk()
            ->assertJson(['message' => 'Tenant agreement file does not exist']);

        $this->assertSame(1, $application->fresh()->status);
    }

    public function test_f8_reject_web_uses_check_order_a(): void
    {
        // Arrange
        Notification::fake();
        Storage::fake('public');

        $landlord = $this->landlordWithSubscription();
        $tenant = $this->createTenant(['landlord_id' => $landlord->id]);
        $property = $this->makeProperty($landlord);
        $application = $this->makeApplication($tenant, $property);

        // Act — sin tenant_agreement: la rama de update procede
        $response = $this->actingAs($landlord)->postJson("/landlord/application/rejected/{$application->id}", [
            'rejected_cause' => 'Not suitable',
        ]);

        // Assert — orden A web: update + redirect; sin notificación
        $response->assertOk()->assertJson([
            'message' => 'Application rejected successfully',
            'redirect' => route('landlord.tenant-applications.show', $application->id),
        ]);

        $application = $application->fresh();
        $this->assertSame(3, $application->status);
        $this->assertSame('Not suitable', $application->rejected_cause);

        Notification::assertNothingSent();

        // Act — con tenant_agreement firmado en storage: guard bloqueante
        Storage::disk('public')->put('uploads/26/11/tenant.pdf', 'signed');
        $signedApplication = $this->makeApplication($tenant, $property, [
            'tenant_agreement' => 'uploads/26/11/tenant.pdf',
            'status' => 2,
        ]);

        $response = $this->actingAs($landlord)->postJson("/landlord/application/rejected/{$signedApplication->id}", [
            'rejected_cause' => 'Too late',
        ]);

        // Assert — guard web responde 200 (quirk: sin código de error)
        $response->assertOk()->assertJson([
            'message' => 'Application cannot be rejected because a tenant agreement already exists.',
        ]);
        $this->assertSame(2, $signedApplication->fresh()->status);
    }

    public function test_f9_reject_api_uses_check_order_b_with_409(): void
    {
        // Arrange
        Notification::fake();
        Storage::fake('public');

        $landlord = $this->createLandlord();
        $tenant = $this->createTenant(['landlord_id' => $landlord->id]);
        $property = $this->makeProperty($landlord);

        Storage::disk('public')->put('uploads/26/11/tenant.pdf', 'signed');
        $signedApplication = $this->makeApplication($tenant, $property, [
            'tenant_agreement' => 'uploads/26/11/tenant.pdf',
        ]);

        // Act — guard con acuerdo firmado
        Sanctum::actingAs($landlord, ['*']);
        $response = $this->postJson("/api/v1/landlord/application/rejected/{$signedApplication->id}", [
            'rejected_cause' => 'Too late',
        ]);

        // Assert — orden B api: guard → 409 (divergencia con web F8)
        $response->assertStatus(409)->assertJson([
            'message' => 'Application cannot be rejected because a tenant agreement already exists.',
        ]);
        $this->assertSame(1, $signedApplication->fresh()->status);

        // Act — sin acuerdo: update + notificación
        $application = $this->makeApplication($tenant, $property);
        Sanctum::actingAs($landlord, ['*']);
        $response = $this->postJson("/api/v1/landlord/application/rejected/{$application->id}", [
            'rejected_cause' => 'Not suitable',
        ]);

        // Assert — 200 + shape data + sendNotification (tabla §4)
        $response->assertOk()->assertJson([
            'message' => 'Application rejected successfully',
        ])->assertJsonPath('data.id', $application->id);

        $application = $application->fresh();
        $this->assertSame(3, $application->status);
        $this->assertSame('Not suitable', $application->rejected_cause);

        Notification::assertSentTo([$tenant, $landlord], SendNotification::class);

        // rejected_cause es required en ambos canales
        Sanctum::actingAs($landlord, ['*']);
        $this->postJson("/api/v1/landlord/application/rejected/{$application->id}")
            ->assertStatus(422);
    }

    /**
     * // Expected to fail after the file_download quirk fix (follow-up
     * // outside 5.13, spec §5) — update to unified behavior when it fails.
     */
    #[Group('characterization')]
    public function test_f10_file_download_web_ignores_type_parameter(): void
    {
        // Arrange — bug conocido (spec §5): el canal web ignora $type y
        // sirve landlord_agreement aunque se pida otro tipo.
        Storage::fake('public');
        Storage::disk('public')->put('uploads/26/11/landlord.pdf', 'LANDLORD-CONTENT');
        Storage::disk('public')->put('uploads/26/11/tenant.pdf', 'TENANT-CONTENT');

        $landlord = $this->landlordWithSubscription();
        $tenant = $this->createTenant(['landlord_id' => $landlord->id]);
        $application = $this->makeApplication($tenant, $this->makeProperty($landlord), [
            'landlord_agreement' => 'uploads/26/11/landlord.pdf',
            'tenant_agreement' => 'uploads/26/11/tenant.pdf',
        ]);

        // Act — type basura (NCS): el controlador actual lo ignora
        $response = $this->actingAs($landlord)
            ->get("/landlord/application/file/{$application->id}/ncs");

        // Assert — congela el servicio de landlord_agreement con cualquier $type
        $response->assertOk();
        $this->assertSame('LANDLORD-CONTENT', $response->streamedContent());

        // Sin landlord_agreement: mensaje 'File not found' con 200
        $empty = $this->makeApplication($tenant, $this->makeProperty($landlord));
        $this->actingAs($landlord)
            ->get("/landlord/application/file/{$empty->id}/ncs")
            ->assertOk()
            ->assertJson(['message' => 'File not found']);
    }

    /**
     * // Expected to fail after the file_download quirk fix (follow-up
     * outside 5.13, spec §5) — update to unified behavior when it fails.
     */
    #[Group('characterization')]
    public function test_f11_file_download_api_reads_missing_file_attribute(): void
    {
        // Arrange — bug conocido (spec §5): el canal api lee $application->file,
        // atributo que no existe en el modelo ni en la tabla → null siempre.
        Storage::fake('public');
        Storage::disk('public')->put('uploads/26/11/landlord.pdf', 'LANDLORD-CONTENT');
        Storage::disk('public')->put('uploads/26/11/tenant.pdf', 'TENANT-CONTENT');

        $landlord = $this->createLandlord();
        $tenant = $this->createTenant(['landlord_id' => $landlord->id]);
        $application = $this->makeApplication($tenant, $this->makeProperty($landlord), [
            'landlord_agreement' => 'uploads/26/11/landlord.pdf',
            'tenant_agreement' => 'uploads/26/11/tenant.pdf',
        ]);

        // Act
        Sanctum::actingAs($landlord, ['*']);
        $response = $this->getJson("/api/v1/landlord/application/file/{$application->id}");

        // Assert — con ambos acuerdos en storage igual responde File not found
        $response->assertOk()->assertJson(['message' => 'File not found']);
    }

    public function test_f12_web_index_filter_excel_csv_and_show_stay_alive(): void
    {
        // Arrange
        $landlord = $this->landlordWithSubscription();
        $tenant = $this->createTenant(['landlord_id' => $landlord->id, 'name' => 'Contract Tenant One']);
        $ownApplication = $this->makeApplication($tenant, $this->makeProperty($landlord));

        $otherLandlord = $this->createLandlord();
        $otherTenant = $this->createTenant(['landlord_id' => $otherLandlord->id]);
        $foreignApplication = $this->makeApplication($otherTenant, $this->makeProperty($otherLandlord));

        // Act + Assert — index scopea a las properties del landlord
        $response = $this->actingAs($landlord)->get('/landlord/tenant-applications');
        $response->assertOk()->assertViewIs('landlord.application.index');

        $ids = collect($response->viewData('applications')->items())->pluck('id');
        $this->assertContains($ownApplication->id, $ids);
        $this->assertNotContains($foreignApplication->id, $ids);

        // Act + Assert — filter ajax renderiza las filas propias
        $filter = $this->actingAs($landlord)->postJson('/landlord/tenant-applications/filter', [
            'search' => 'Contract Tenant One',
        ], ['X-Requested-With' => 'XMLHttpRequest']);

        $filter->assertOk()->assertJsonStructure(['data']);
        $this->assertStringContainsString('Contract Tenant One', $filter->json('data'));

        // Act + Assert — exports vivos (rutas applications.excel/csv)
        $this->actingAs($landlord)->get('/landlord/applications-excel')->assertOk();
        $this->actingAs($landlord)->get('/landlord/applications-csv')->assertOk();

        // Act + Assert — show web renderiza la vista de detalle
        $this->actingAs($landlord)
            ->get("/landlord/tenant-applications/{$ownApplication->id}")
            ->assertOk()
            ->assertViewIs('landlord.application.view');
    }
}
