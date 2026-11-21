<?php

namespace Tests\Feature\Options;

use App\Models\PlanSubscribe;
use App\Models\Property;
use App\Models\RentPayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * Pub1 + F1–F4 del spec-get-option-tipos-consistentes-fase5 (Fase 5 / 5.7):
 * las vistas de scope renderizan con la tabla options vacía — antes 500
 * por accesos sin guard sobre el fallback `[]` (Seg-24).
 *
 * Nota (adaptación §8 del spec): el render directo no pasa por el
 * middleware web, así que se comparte `$errors` vía withViewErrors()
 * y se autentica donde la vista/layout lee auth()->user() (api-invoice
 * l.81-93, header de admin/landlord master).
 */
class OptionlessViewsSmokeTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->withViewErrors([]);
    }

    public function test_pub1_public_page_renders_without_options(): void
    {
        // Act + Assert
        $this->get('/privacy-policy')->assertOk();
    }

    public function test_f1_api_invoice_renders_without_options(): void
    {
        // Arrange
        $rentPayment = RentPayment::factory()->create();
        $this->actingAs($this->createTenant());

        // Act
        $html = view('api-invoice.rent-payment', ['rent_payment' => $rentPayment])->render();

        // Assert
        $this->assertStringContainsString('backend/assets/img/logo.svg', $html);
    }

    public function test_f2_landlord_invoice_renders_without_options(): void
    {
        // Arrange
        $rentPayment = RentPayment::factory()->create();

        // Act
        $html = view('landlord.rent-payments.view', ['rent_payment' => $rentPayment])->render();

        // Assert
        $this->assertStringContainsString('backend/assets/img/logo.svg', $html);
    }

    public function test_f3_admin_subscription_invoice_renders_without_options(): void
    {
        // Arrange
        $subscriber = PlanSubscribe::factory()->create();

        // Act
        $html = view('admin.subscriptions.invoice', ['subscriber' => $subscriber])->render();

        // Assert
        $this->assertStringContainsString('backend/assets/img/logo.svg', $html);
    }

    public function test_f4_property_success_renders_without_options(): void
    {
        // Arrange
        $property = Property::factory()->create();
        $this->actingAs($this->createAdmin());

        // Act — canal admin y canal landlord
        $adminHtml = view('admin.properties.property-success', ['property' => $property])->render();

        $this->actingAs($this->createLandlord());
        $landlordHtml = view('landlord.properties.property-success', ['property' => $property])->render();

        // Assert
        $this->assertStringContainsString('Thank you for posting on', $adminHtml);
        $this->assertStringContainsString('Thank you for posting on', $landlordHtml);
    }
}
