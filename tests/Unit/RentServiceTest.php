<?php

namespace Tests\Unit;

use App\Enums\Channel;
use App\Models\Option;
use App\Models\Property;
use App\Models\Rent;
use App\Models\SecurityDeposit;
use App\Models\Unit;
use App\Models\User;
use App\Notifications\SendNotification;
use App\Services\RentService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * Unit tests de RentService (spec-service-rent-fase5, U1–U4).
 *
 * U1 congela la fórmula del bloque service-charge (los mismos números
 * que F1/F2 en los contract tests HTTP): con unit(monthly_rent=1000,
 * security_deposit=200, utility_deposit=50) y Option service-charge
 * {amount: 10, type: percentage} => total 1250, charge 125, balance 1125.
 */
class RentServiceTest extends TestCase
{
    use CreatesUsers, DatabaseTransactions;

    private RentService $rents;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rents = new RentService;
    }

    /**
     * @return array{landlord: User, tenant: User, property: Property, unit: Unit}
     */
    private function arrangeUnitBranch(): array
    {
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

        return ['landlord' => $landlord, 'tenant' => $tenant, 'property' => $property, 'unit' => $unit];
    }

    /**
     * @return array<string, mixed>
     */
    private function storeData(array $arrange): array
    {
        return [
            'landlord_id' => $arrange['landlord']->id,
            'tenant_id' => $arrange['tenant']->id,
            'property_id' => $arrange['property']->id,
            'unit_id' => $arrange['unit']->id,
            'start_date' => '2026-12-01',
            'end_date' => '2027-01-15',
        ];
    }

    private function rentWithDeposit(User $landlord, User $tenant, Property $property, string $status, string $depositStatus): Rent
    {
        $rent = Rent::create([
            'landlord_id' => $landlord->id,
            'tenant_id' => $tenant->id,
            'property_id' => $property->id,
            'monthly_rent' => 1000,
            'start_date' => '2026-12-01',
            'end_date' => '2027-01-15',
            'status' => $status,
        ]);

        SecurityDeposit::create([
            'tenant_id' => $tenant->id,
            'landlord_id' => $landlord->id,
            'rent_id' => $rent->id,
            'property_id' => $property->id,
            'deposit_amount' => 200,
            'status' => $depositStatus,
        ]);

        return $rent;
    }

    public function test_u1_create_with_deposit_computes_service_charge_block(): void
    {
        // Arrange
        Notification::fake();
        Storage::fake('public');
        $arrange = $this->arrangeUnitBranch();

        // Act
        $rent = $this->rents->createWithDeposit(
            $this->storeData($arrange),
            UploadedFile::fake()->create('agreement.pdf', 100, 'application/pdf')
        );

        // Assert — fórmula congelada: security_total=1250, charge 10%=125, balance=1125
        $this->assertSame(1000.0, (float) $rent->monthly_rent);
        $this->assertSame(50.0, (float) $rent->utility_bill);
        $this->assertSame(1050.0, (float) $rent->total_amount);

        $deposit = SecurityDeposit::where('rent_id', $rent->id)->first();
        $this->assertSame(200.0, (float) $deposit->deposit_amount);
        $this->assertSame(1000.0, (float) $deposit->rent_advance);
        $this->assertSame(50.0, (float) $deposit->utility_advance);
        $this->assertSame(1250.0, (float) $deposit->total_amount);
        $this->assertSame(125.0, (float) $deposit->charge);
        $this->assertSame(1125.0, (float) $deposit->landlord_balance);
        $this->assertSame('unpaid', $deposit->status);

        // La notificación se emite tras el commit (orden 4.8)
        Notification::assertSentTo(
            [$arrange['tenant'], $arrange['landlord']],
            SendNotification::class
        );
    }

    public function test_u2_create_with_deposit_is_atomic(): void
    {
        // Arrange
        Notification::fake();
        Storage::fake('public');
        $arrange = $this->arrangeUnitBranch();

        // Fuerza el fallo de la 2ª escritura: Rent se crea dentro de la tx
        // pero SecurityDeposit falla → rollback total.
        Event::listen(
            'eloquent.creating: '.SecurityDeposit::class,
            fn () => throw new RuntimeException('boom')
        );

        // Act
        try {
            $this->rents->createWithDeposit(
                $this->storeData($arrange),
                UploadedFile::fake()->create('agreement.pdf', 100, 'application/pdf')
            );

            $this->fail('RuntimeException was expected.');
        } catch (RuntimeException) {
            // Esperado: ni rent ni deposit persisten, ni notificación.
        }

        // Assert
        $this->assertDatabaseMissing('rents', ['property_id' => $arrange['property']->id]);
        $this->assertDatabaseMissing('security_deposits', ['property_id' => $arrange['property']->id]);
        Notification::assertNothingSent();
    }

    public function test_u3_approve_notifies_only_on_api_channel(): void
    {
        // Arrange
        Notification::fake();
        $landlord = $this->createLandlord();
        $tenant = $this->createTenant(['landlord_id' => $landlord->id]);
        $property = Property::factory()->create(['landlord_id' => $landlord->id]);
        $rent = $this->rentWithDeposit($landlord, $tenant, $property, 'pending', 'paid');

        // Act — canal Api: activa y notifica
        $this->rents->approve($rent, Channel::Api);

        // Assert
        $this->assertSame('active', $rent->fresh()->status);
        Notification::assertSentTo([$tenant, $landlord], SendNotification::class);

        // Act — canal Landlord: activa sin notificación (F4 congela la divergencia)
        Notification::fake();
        $rentWeb = $this->rentWithDeposit($landlord, $tenant, $property, 'pending', 'paid');
        $this->rents->approve($rentWeb, Channel::Landlord);

        // Assert
        $this->assertSame('active', $rentWeb->fresh()->status);
        Notification::assertNothingSent();
    }

    public function test_u4_cancel_persists_cancel_info_and_status(): void
    {
        // Arrange
        Notification::fake();
        $landlord = $this->createLandlord();
        $tenant = $this->createTenant(['landlord_id' => $landlord->id]);
        $property = Property::factory()->create(['landlord_id' => $landlord->id]);
        $rent = Rent::create([
            'landlord_id' => $landlord->id,
            'tenant_id' => $tenant->id,
            'property_id' => $property->id,
            'monthly_rent' => 1000,
            'start_date' => '2026-12-01',
            'end_date' => '2027-01-15',
            'status' => 'active',
        ]);

        // Act
        $this->rents->cancel($rent, Channel::Landlord, 'Leaving the city', 'Job relocation');

        // Assert
        $rent = $rent->fresh();
        $this->assertSame('cancelled', $rent->status);

        $cancelInfo = $rent->cancel_info;
        $this->assertSame('Leaving the city', $cancelInfo['title']);
        $this->assertSame('Job relocation', $cancelInfo['reason']);
        $this->assertSame('2027-01-15', $cancelInfo['prev_end_date']);

        Notification::assertSentTo([$tenant, $landlord], SendNotification::class);
    }
}
