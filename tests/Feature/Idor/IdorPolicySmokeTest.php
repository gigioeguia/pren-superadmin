<?php

namespace Tests\Feature\Idor;

use App\Models\Application;
use App\Models\Property;
use App\Models\Rent;
use App\Models\RentPayment;
use App\Models\SecurityDeposit;
use App\Models\Withdraw;
use Illuminate\Support\Facades\Gate;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class IdorPolicySmokeTest extends TestCase
{
    use CreatesUsers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->landlordA = $this->createLandlord();
        $this->landlordB = $this->createLandlord();
        $this->tenantA = $this->createTenant();
        $this->tenantB = $this->createTenant();
        $this->admin = $this->createAdmin();
    }

    public function test_f2_landlord_cannot_view_foreign_property(): void
    {
        $propertyB = Property::factory()->create(['landlord_id' => $this->landlordB->id]);

        $this->assertFalse(Gate::forUser($this->landlordA)->allows('view', $propertyB));
        $this->assertTrue(Gate::forUser($this->admin)->allows('view', $propertyB));
    }

    public function test_f3_landlord_cannot_update_foreign_property(): void
    {
        $propertyB = Property::factory()->create(['landlord_id' => $this->landlordB->id]);

        $this->assertFalse(Gate::forUser($this->landlordA)->allows('update', $propertyB));
    }

    public function test_f4_landlord_cannot_delete_foreign_property(): void
    {
        $propertyB = Property::factory()->create(['landlord_id' => $this->landlordB->id]);

        $this->assertFalse(Gate::forUser($this->landlordA)->allows('delete', $propertyB));
    }

    public function test_f5_api_landlord_cannot_update_foreign_property(): void
    {
        $propertyB = Property::factory()->create(['landlord_id' => $this->landlordB->id]);

        $this->assertFalse(Gate::forUser($this->landlordA)->allows('update', $propertyB));
    }

    public function test_f7_tenant_cannot_view_foreign_application(): void
    {
        $propertyB = Property::factory()->create(['landlord_id' => $this->landlordB->id]);
        $applicationB = Application::create([
            'tenant_id' => $this->tenantB->id,
            'property_id' => $propertyB->id,
            'status' => 0,
        ]);

        $this->assertFalse(Gate::forUser($this->tenantA)->allows('view', $applicationB));
    }

    public function test_f8_landlord_cannot_process_foreign_application(): void
    {
        $propertyB = Property::factory()->create(['landlord_id' => $this->landlordB->id]);
        $applicationB = Application::create([
            'tenant_id' => $this->tenantB->id,
            'property_id' => $propertyB->id,
            'status' => 0,
        ]);

        $this->assertFalse(Gate::forUser($this->landlordA)->allows('process', $applicationB));
        $this->assertTrue(Gate::forUser($this->admin)->allows('process', $applicationB));
    }

    public function test_f9_landlord_cannot_download_foreign_application(): void
    {
        $propertyB = Property::factory()->create(['landlord_id' => $this->landlordB->id]);
        $applicationB = Application::create([
            'tenant_id' => $this->tenantB->id,
            'property_id' => $propertyB->id,
            'status' => 0,
        ]);

        $this->assertFalse(Gate::forUser($this->landlordA)->allows('fileDownload', $applicationB));
    }

    public function test_f11_landlord_cannot_view_foreign_security_deposit(): void
    {
        $depositB = SecurityDeposit::factory()->create([
            'landlord_id' => $this->landlordB->id,
            'tenant_id' => $this->tenantB->id,
        ]);

        $this->assertFalse(Gate::forUser($this->landlordA)->allows('view', $depositB));
    }

    public function test_f12_tenant_cannot_offline_pay_foreign_security_deposit(): void
    {
        $depositB = SecurityDeposit::factory()->create([
            'landlord_id' => $this->landlordB->id,
            'tenant_id' => $this->tenantB->id,
        ]);

        $this->assertFalse(Gate::forUser($this->tenantA)->allows('offlinePayment', $depositB));
    }

    public function test_f15_landlord_cannot_view_foreign_withdraw(): void
    {
        $withdrawB = Withdraw::create([
            'user_id' => $this->landlordB->id,
            'amount' => 10,
            'charge' => 0,
            'status' => 'pending',
        ]);

        $this->assertFalse(Gate::forUser($this->landlordA)->allows('view', $withdrawB));
    }

    public function test_f16_api_landlord_cannot_view_foreign_withdraw_invoice(): void
    {
        $withdrawB = Withdraw::create([
            'user_id' => $this->landlordB->id,
            'amount' => 10,
            'charge' => 0,
            'status' => 'pending',
        ]);

        $this->assertFalse(Gate::forUser($this->landlordA)->allows('invoice', $withdrawB));
    }

    public function test_f19_landlord_cannot_view_foreign_rent_payment(): void
    {
        $rent = Rent::create([
            'landlord_id' => $this->landlordB->id,
            'tenant_id' => $this->tenantB->id,
            'property_id' => Property::factory()->create(['landlord_id' => $this->landlordB->id])->id,
        ]);

        $paymentB = RentPayment::create([
            'rent_id' => $rent->id,
        ]);

        $this->assertFalse(Gate::forUser($this->landlordA)->allows('view', $paymentB));
    }

    public function test_f23_tenant_cannot_update_property(): void
    {
        $propertyB = Property::factory()->create(['landlord_id' => $this->landlordB->id]);

        $this->assertFalse(Gate::forUser($this->tenantA)->allows('update', $propertyB));
    }

    public function test_f24_superadmin_bypass(): void
    {
        $propertyB = Property::factory()->create(['landlord_id' => $this->landlordB->id]);

        $this->assertTrue(Gate::forUser($this->createSuperadmin())->allows('view', $propertyB));
        $this->assertTrue(Gate::forUser($this->createSuperadmin())->allows('update', $propertyB));
        $this->assertTrue(Gate::forUser($this->createSuperadmin())->allows('delete', $propertyB));
    }

    public function test_f1_landlord_can_view_own_property(): void
    {
        $propertyA = Property::factory()->create(['landlord_id' => $this->landlordA->id]);

        $this->assertTrue(Gate::forUser($this->landlordA)->allows('view', $propertyA));
    }
}
