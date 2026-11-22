<?php

namespace Tests\Unit;

use App\Enums\Channel;
use App\Models\Plan;
use App\Models\PlanSubscribe;
use App\Models\UserDetail;
use App\Services\LandlordService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * Unit tests de LandlordService (spec-service-landlord-fase5, U1–U4).
 *
 * U3 valida la sustitución file_exists() → Storage::exists() (spec §5):
 * el guard original era un no-op sobre rutas relativas de storage; el
 * service borra la media de verdad tras el commit.
 */
class LandlordServiceTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    private LandlordService $landlords;

    protected function setUp(): void
    {
        parent::setUp();

        $this->landlords = new LandlordService;
    }

    public function test_u1_create_account_diverges_per_channel(): void
    {
        // Arrange — canal web (signup)
        $data = ['name' => 'Web Landlord', 'email' => 'u1-web@example.test', 'password' => 'Password123'];

        // Act
        $web = $this->landlords->createAccount($data, Channel::Landlord, '123456', now()->addMinutes(5));
        $api = $this->landlords->createAccount(
            ['name' => 'Api Landlord', 'email' => 'u1-api@example.test', 'password' => 'Password123'],
            Channel::Api
        );

        // Assert — is_setupped: web 1 / api 0; role siempre server-side
        $this->assertSame('landlord', $web->role);
        $this->assertSame(1, $web->is_setupped);
        $this->assertSame('123456', $web->remember_token);
        $this->assertTrue($web->email_verified_at->isFuture());

        $this->assertSame('landlord', $api->role);
        // Default de columna: visible tras re-fetch (la instancia no lo refleja)
        $this->assertSame(0, $api->fresh()->is_setupped);
        $this->assertNull($api->remember_token);
        $this->assertTrue($api->email_verified_at->diffInSeconds(now()) < 5);
    }

    public function test_u2_create_account_ignores_crafted_keys(): void
    {
        // Act — crafted keys en el payload: solo se leen name/email/password
        $user = $this->landlords->createAccount([
            'name' => 'Crafted',
            'email' => 'u2@example.test',
            'password' => 'Password123',
            'role' => 'superadmin',
            'plan_subscribe_id' => 999,
            'will_expire' => '2099-12-31 00:00:00',
            'balance' => 500,
        ], Channel::Api);

        // Assert — Seg-28: asignaciones server-side prevalecen
        $this->assertSame('landlord', $user->role);
        $this->assertNull($user->plan_subscribe_id);
        $this->assertNull($user->will_expire);
        $this->assertSame(0.0, (float) $user->balance);

        // Sin UserDetail ni trío: createAccount no crea nada más
        $this->assertNull(UserDetail::where('user_id', $user->id)->first());
        $this->assertSame(0, PlanSubscribe::where('user_id', $user->id)->count());
    }

    public function test_u3_delete_many_captures_media_and_deletes_after_commit(): void
    {
        // Arrange
        $disk = config('filesystems.default');
        Storage::fake($disk);

        $a = $this->createLandlord(['image' => 'uploads/26/11/a.jpg']);
        $b = $this->createLandlord(['image' => 'uploads/26/11/b.jpg']);
        $keep = $this->createLandlord();

        foreach (['a', 'b'] as $name) {
            UserDetail::create([
                'user_id' => ${$name}->id,
                'mykad' => ['front_image' => "uploads/26/11/{$name}-front.jpg", 'back_image' => null],
            ]);
            Storage::disk($disk)->put("uploads/26/11/{$name}.jpg", 'img');
            Storage::disk($disk)->put("uploads/26/11/{$name}-front.jpg", 'img');
        }

        // Act
        $deleted = $this->landlords->deleteMany([$a->id, $b->id]);

        // Assert — filas borradas en tx, user_detail explícito (comportamiento actual)
        $this->assertSame(2, $deleted);
        $this->assertDatabaseMissing('users', ['id' => $a->id]);
        $this->assertDatabaseMissing('users', ['id' => $b->id]);
        $this->assertDatabaseMissing('user_details', ['user_id' => $a->id]);
        $this->assertDatabaseMissing('user_details', ['user_id' => $b->id]);
        $this->assertDatabaseHas('users', ['id' => $keep->id]);

        // Media borrada tras el commit — Storage::exists (ex file_exists, spec §5)
        Storage::disk($disk)->assertMissing('uploads/26/11/a.jpg');
        Storage::disk($disk)->assertMissing('uploads/26/11/b.jpg');
        Storage::disk($disk)->assertMissing('uploads/26/11/a-front.jpg');
        Storage::disk($disk)->assertMissing('uploads/26/11/b-front.jpg');
    }

    public function test_u4_activate_free_plan_fields_per_channel(): void
    {
        // Arrange
        $plan = Plan::factory()->free()->create();
        $admin = $this->createLandlord();
        $web = $this->createLandlord();
        $api = $this->createLandlord();

        // Act
        $subAdmin = $this->landlords->activateFreePlan($admin, Channel::Admin, $plan);
        $subWeb = $this->landlords->activateFreePlan($web, Channel::Landlord, $plan);
        $subApi = $this->landlords->activateFreePlan($api, Channel::Api, $plan);

        // Assert — canal admin: 8 campos
        $this->assertSame($plan->id, $subAdmin->plan_id);
        $this->assertSame('Free Plan', $subAdmin->plan_name);
        $this->assertSame($admin->id, $subAdmin->user_id);
        $this->assertSame($plan->durationInDays(), (int) $subAdmin->duration);
        $this->assertSame(-1, (int) $subAdmin->number_of_property);
        $this->assertSame(-1, (int) $subAdmin->number_of_tenant);
        $this->assertSame(-1, (int) $subAdmin->number_of_labor);
        $this->assertSame('paid', $subAdmin->payment_status);

        // Canal web: 5 campos (sin number_of_*)
        $this->assertSame('Free Plan', $subWeb->plan_name);
        $this->assertSame('paid', $subWeb->payment_status);
        $this->assertNull($subWeb->number_of_property);

        // Canal api: 3 campos — payment_status queda en su default 'unpaid'
        $this->assertNull($subApi->plan_name);
        $this->assertSame('unpaid', $subApi->fresh()->payment_status);

        // Trío forceFill en los tres usuarios
        foreach ([$admin, $web, $api] as $user) {
            $fresh = $user->fresh();
            $this->assertNotNull($fresh->plan_subscribe_id);
            $this->assertNotNull($fresh->subscriptionDate);
            $this->assertTrue(Carbon::parse($fresh->will_expire)->isFuture());
        }

        // Sin plan: null sin efectos
        $orphan = $this->createLandlord();
        $this->assertNull($this->landlords->activateFreePlan($orphan, Channel::Api, null));
        $this->assertNull($orphan->fresh()->plan_subscribe_id);
    }
}
