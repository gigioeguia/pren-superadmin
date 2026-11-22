<?php

namespace Tests\Unit;

use App\Enums\Channel;
use App\Models\Application;
use App\Models\Category;
use App\Models\Option;
use App\Models\Property;
use App\Models\Rent;
use App\Models\SecurityDeposit;
use App\Models\User;
use App\Notifications\SendNotification;
use App\Services\ApplicationService;
use App\Services\RentService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * Unit tests de ApplicationService (spec-service-application-fase5, U1–U3).
 *
 * U1 congela la delegación de agreementSign al RentService
 * (createWithAgreement invocado 1× con el plan de la rama, 0 creación
 * local de deposit/rent) — desviación documentada del spec §11: el
 * método delegado es createWithAgreement, no createWithDeposit, para
 * preservar las ramas 1–8 con cálculo idéntico (decisión de diseño
 * ante la contradicción spec §5 vs F4).
 */
class ApplicationServiceTest extends TestCase
{
    use CreatesUsers, DatabaseTransactions;

    private function propertyFor(User $landlord, int $categoryId): Property
    {
        $category = Category::find($categoryId) ?? Category::forceCreate([
            'id' => $categoryId,
            'name' => "Category {$categoryId}",
            'status' => 1,
        ]);

        return Property::factory()->create([
            'landlord_id' => $landlord->id,
            'category_id' => $category->id,
            'security_deposit' => 200,
            'utility_deposit' => 50,
            'rent_info' => ['property_rent' => 1000, 'monthly_rent' => 1000],
        ]);
    }

    public function test_u1_agreement_sign_delegates_creation_to_rent_service(): void
    {
        // Arrange
        Notification::fake();
        Storage::fake('public');
        Option::factory()->create(['key' => 'service-charge', 'value' => ['amount' => 10, 'type' => 'percentage']]);

        $landlord = $this->createLandlord();
        $tenant = $this->createTenant(['landlord_id' => $landlord->id]);
        $property = $this->propertyFor($landlord, 1);
        $application = Application::create([
            'tenant_id' => $tenant->id,
            'property_id' => $property->id,
            'status' => 1,
            'start_date' => '2026-12-01',
            'end_date' => '2027-01-15',
            'landlord_agreement' => 'uploads/26/11/landlord.pdf',
        ]);

        // Stub del RentService: congela la llamada única y el plan de la
        // rama cat 1 (deposit con utility, charge base 1250).
        $rents = Mockery::mock(RentService::class);
        $rents->shouldReceive('createWithAgreement')
            ->once()
            ->withArgs(function (array $rentAttributes, array $depositAttributes, float|int $chargeBase) use ($application, $property): bool {
                $depositOk = (float) $depositAttributes['total_amount'] === 1250.0
                    && (float) $depositAttributes['deposit_amount'] === 200.0
                    && $depositAttributes['rent_advance'] === 1000
                    && (float) $depositAttributes['utility_advance'] === 50.0
                    && $depositAttributes['tenant_id'] === $application->tenant_id
                    && $depositAttributes['landlord_id'] === $property->landlord_id;

                $rentOk = $rentAttributes['monthly_rent'] === 1000
                    && (float) $rentAttributes['utility_bill'] === 50.0
                    && (float) $rentAttributes['total_amount'] === 1050.0
                    && $rentAttributes['unit_id'] === null
                    && $rentAttributes['landlord_agreement'] === 'uploads/26/11/landlord.pdf';

                return $depositOk && $rentOk && (float) $chargeBase === 1250.0;
            });
        $applications = new ApplicationService($rents);

        // Act
        $result = $applications->agreementSign($application, UploadedFile::fake()->create('signed.pdf', 100, 'application/pdf'));

        // Assert — delegación sin creación local: ni deposit ni rent se
        // escriben desde el service, y la notificación sale post-commit.
        $this->assertSame($application->id, $result->id);
        $this->assertNotNull($application->fresh()->tenant_agreement);
        $this->assertDatabaseMissing('security_deposits', ['property_id' => $property->id]);
        $this->assertDatabaseMissing('rents', ['property_id' => $property->id]);
        Notification::assertSentTo([$tenant, $landlord], SendNotification::class);
    }

    public function test_u2_approve_notifies_only_on_api_channel(): void
    {
        // Arrange
        Notification::fake();
        Storage::fake('public');
        Storage::disk('public')->put('uploads/26/11/tenant.pdf', 'signed');

        $landlord = $this->createLandlord();
        $tenant = $this->createTenant(['landlord_id' => $landlord->id]);

        $makeReady = function () use ($landlord, $tenant): Application {
            $property = $this->propertyFor($landlord, 1);
            $rent = Rent::create([
                'landlord_id' => $landlord->id,
                'tenant_id' => $tenant->id,
                'property_id' => $property->id,
                'monthly_rent' => 1000,
                'status' => 'pending',
            ]);
            SecurityDeposit::create([
                'tenant_id' => $tenant->id,
                'landlord_id' => $landlord->id,
                'rent_id' => $rent->id,
                'property_id' => $property->id,
                'deposit_amount' => 200,
                'status' => 'paid',
            ]);

            return Application::create([
                'tenant_id' => $tenant->id,
                'property_id' => $property->id,
                'status' => 1,
                'tenant_agreement' => 'uploads/26/11/tenant.pdf',
            ]);
        };

        $applications = new ApplicationService(new RentService);

        // Act — canal Api: activa y notifica
        $apiApplication = $makeReady();
        $applications->approve($apiApplication, Channel::Api);

        // Assert
        $this->assertSame(2, $apiApplication->fresh()->status);
        Notification::assertSentTo([$tenant, $landlord], SendNotification::class);

        // Act — canal Landlord: activa sin notificación (F7 congela la divergencia)
        Notification::fake();
        $webApplication = $makeReady();
        $applications->approve($webApplication, Channel::Landlord);

        // Assert
        $this->assertSame(2, $webApplication->fresh()->status);
        Notification::assertNothingSent();
    }

    public function test_u3_file_download_diverges_by_channel(): void
    {
        // Arrange
        $landlord = $this->createLandlord();
        $tenant = $this->createTenant(['landlord_id' => $landlord->id]);
        $application = Application::create([
            'tenant_id' => $tenant->id,
            'property_id' => $this->propertyFor($landlord, 1)->id,
            'status' => 1,
            'landlord_agreement' => 'uploads/26/11/landlord.pdf',
            'tenant_agreement' => 'uploads/26/11/tenant.pdf',
        ]);

        $applications = new ApplicationService(new RentService);

        // Act + Assert — web ignora $type y sirve landlord_agreement;
        // api lee el atributo `file` inexistente → null siempre.
        $this->assertSame(
            'uploads/26/11/landlord.pdf',
            $applications->fileDownload($application, Channel::Landlord, 'ncs')
        );
        $this->assertNull($applications->fileDownload($application, Channel::Api, null));
    }
}
