<?php

namespace Tests\Feature\Cache;

use App\Models\Gateway;
use App\Models\Plan;
use App\Support\CacheKeys;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class CachePaymentInvalidationTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    public function test_p1_manual_payment_invalidates_plan_data(): void
    {
        // Arrange
        // HasUploader escribe en config('filesystems.default'), que en este
        // repo es el disco 'public' con root '.' (raiz del repo): fakearlo.
        Storage::fake(config('filesystems.default'));

        $plan = Plan::factory()->create();
        $landlord = $this->createLandlord();
        $gateway = Gateway::factory()->manual()->create();

        // Stale plantado en la clave del landlord (gap G4, rama unpaid)
        cache([CacheKeys::planData($landlord->id) => 'stale']);

        // Act
        $response = $this->withSession([
            'payment_type' => 'subscription',
            'user_id' => $landlord->id,
            'platform' => 'web',
        ])->post("/payments/{$plan->id}/{$gateway->id}", [
            'manual_data' => 'Bank transfer ACME 123456789',
            'attachment' => UploadedFile::fake()->image('receipt.jpg'),
        ]);

        // Assert
        $response->assertRedirect(route('order.status', ['status' => 'success']));
        $this->assertNull(cache(CacheKeys::planData($landlord->id)));
    }
}
