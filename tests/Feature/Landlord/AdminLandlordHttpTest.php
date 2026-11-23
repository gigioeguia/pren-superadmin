<?php

namespace Tests\Feature\Landlord;

use App\Http\Middleware\PreventRequestForgery;
use App\Models\Plan;
use App\Models\PlanSubscribe;
use App\Models\User;
use App\Models\UserDetail;
use App\Notifications\SendNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * Contrato HTTP de Admin/LandlordController (spec-service-landlord-fase5,
 * casos F1–F6 del canal admin).
 *
 * Tests de contrato, NO de caracterización: congelan el comportamiento
 * actual para garantizar que la migración a LandlordService no cambia el
 * contrato observable. Deben seguir en verde antes y después de la
 * migración — no llevan @group characterization.
 *
 * Observación empírica (spec §11): el guard `file_exists()` sobre rutas
 * relativas de storage (destroy/deleteAll) es un no-op con Storage::fake,
 * por lo que F4/F5 congelan el contrato HTTP+DB; la limpieza real de media
 * a nivel de disco queda cubierta por U3 sobre el service (tras la
 * sustitución por `Storage::exists()`, spec §5).
 */
class AdminLandlordHttpTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    private function adminWith(string ...$permissions): User
    {
        $admin = $this->createAdmin();

        foreach ($permissions as $permission) {
            $admin->givePermissionTo(
                Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web'])
            );
        }

        return $admin;
    }

    private function landlordWithDetail(array $attributes = []): User
    {
        $landlord = $this->createLandlord($attributes);

        UserDetail::create([
            'user_id' => $landlord->id,
            'gender' => 'male',
            'mykad' => ['front_image' => null, 'back_image' => null],
        ]);

        return $landlord;
    }

    public function test_f1_admin_store_creates_landlord_userdetail_and_free_trio(): void
    {
        // Arrange
        $admin = $this->adminWith('landlords-create');
        $free = Plan::factory()->free()->create();
        Notification::fake();

        // Act
        $response = $this->actingAs($admin)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->postJson('/admin/landlords', [
                'name' => 'Contract Landlord',
                'email' => 'contract-f1@example.test',
                'password' => 'Password123',
                'phone' => ['mobile_code' => '+1', 'mobile_no' => '555'],
                'gender' => 'male',
                'mykad_id' => 'ID-123',
            ]);

        // Assert — contrato HTTP
        $response->assertOk()
            ->assertJsonPath('message', __('Landlord created successfully.'))
            ->assertJsonPath('redirect', route('admin.landlords.index'));

        // User con role server-side
        $user = User::where('email', 'contract-f1@example.test')->first();
        $this->assertSame('landlord', $user->role);
        $this->assertSame(1, $user->is_setupped);

        // UserDetail (solo el canal admin lo crea)
        $detail = UserDetail::where('user_id', $user->id)->first();
        $this->assertNotNull($detail);
        $this->assertSame('male', $detail->gender);
        $this->assertSame('ID-123', $detail->mykad_id);

        // Trío free-plan con los 8 campos del canal admin
        $subscribe = PlanSubscribe::where('user_id', $user->id)->first();
        $this->assertNotNull($subscribe);
        $this->assertSame($free->id, $subscribe->plan_id);
        $this->assertSame('Free Plan', $subscribe->plan_name);
        $this->assertSame($free->durationInDays(), (int) $subscribe->duration);
        $this->assertSame(-1, (int) $subscribe->number_of_property);
        $this->assertSame(-1, (int) $subscribe->number_of_tenant);
        $this->assertSame(-1, (int) $subscribe->number_of_labor);
        $this->assertSame('paid', $subscribe->payment_status);

        $this->assertSame($subscribe->id, $user->plan_subscribe_id);
        $this->assertNotNull($user->subscriptionDate);
        $this->assertTrue(Carbon::parse($user->will_expire)->isFuture());

        // Notificación post-commit al nuevo landlord (canal admin)
        Notification::assertSentTo($user, SendNotification::class);
    }

    public function test_f2_admin_store_ignores_crafted_role_will_expire_and_plan_subscribe_id(): void
    {
        // Arrange — sin plan free: el bloque free-plan no asigna nada
        $admin = $this->adminWith('landlords-create');
        Plan::factory()->create();
        $foreign = PlanSubscribe::factory()->create();

        // Act
        $response = $this->actingAs($admin)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->postJson('/admin/landlords', [
                'name' => 'Crafted Landlord',
                'email' => 'contract-f2@example.test',
                'password' => 'Password123',
                'role' => 'superadmin',
                'will_expire' => '2099-12-31 00:00:00',
                'plan_subscribe_id' => $foreign->id,
                'balance' => 999,
            ]);

        // Assert — Seg-28: las asignaciones server-side prevalecen
        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'email' => 'contract-f2@example.test',
            'role' => 'landlord',
            'will_expire' => null,
            'plan_subscribe_id' => null,
            'balance' => 0,
        ]);
    }

    public function test_f3_admin_update_replaces_image_and_cleans_old_directory(): void
    {
        // Arrange
        $admin = $this->adminWith('landlords-update');
        $disk = config('filesystems.default');
        Storage::fake($disk);

        $landlord = $this->landlordWithDetail(['email' => 'f3@example.test', 'image' => 'uploads/26/10/old.jpg']);
        $landlord->user_details->update([
            'mykad' => ['front_image' => 'uploads/26/10/front-old.jpg', 'back_image' => 'uploads/26/10/back-old.jpg'],
        ]);
        Storage::disk($disk)->put('uploads/26/10/old.jpg', 'img');
        Storage::disk($disk)->put('uploads/26/10/front-old.jpg', 'img');
        Storage::disk($disk)->put('uploads/26/10/back-old.jpg', 'img');

        // Act
        $response = $this->actingAs($admin)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->postJson("/admin/landlords/{$landlord->id}", [
                '_method' => 'PUT',
                'name' => 'Updated Landlord',
                'email' => 'f3@example.test',
                'image' => UploadedFile::fake()->image('new.jpg'),
            ]);

        // Assert
        $response->assertOk()
            ->assertJsonPath('message', 'Landlord update successfully')
            ->assertJsonPath('redirect', route('admin.landlords.index'));

        $user = $landlord->fresh();
        $this->assertNotSame('uploads/26/10/old.jpg', $user->image);
        Storage::disk($disk)->assertExists($user->image);
        $this->assertSame('Updated Landlord', $user->name);

        // Directorios viejos borrados tras el commit (Fase 4 / 4.8)
        Storage::disk($disk)->assertMissing('uploads/26/10/old.jpg');
        Storage::disk($disk)->assertMissing('uploads/26/10/front-old.jpg');
        Storage::disk($disk)->assertMissing('uploads/26/10/back-old.jpg');
    }

    public function test_f4_admin_destroy_deletes_landlord_and_user_detail(): void
    {
        // Arrange
        $admin = $this->adminWith('landlords-delete');
        $landlord = $this->landlordWithDetail(['image' => 'uploads/26/11/gone.jpg']);

        // Act
        $response = $this->actingAs($admin)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->deleteJson("/admin/landlords/{$landlord->id}");

        // Assert — contrato HTTP + DB
        $response->assertOk()
            ->assertJsonPath('message', __('Landlord deleted successfully'))
            ->assertJsonPath('redirect', route('admin.landlords.index'));

        $this->assertDatabaseMissing('users', ['id' => $landlord->id]);
        $this->assertDatabaseMissing('user_details', ['user_id' => $landlord->id]);
    }

    public function test_f5_admin_delete_all_deletes_selected_rows_only(): void
    {
        // Arrange
        $admin = $this->adminWith('landlords-delete');
        $deleteA = $this->landlordWithDetail();
        $deleteB = $this->landlordWithDetail();
        $keep = $this->createLandlord();

        // Act
        $response = $this->actingAs($admin)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->postJson('/admin/landlords/delete-all', [
                'ids' => [$deleteA->id, $deleteB->id],
            ]);

        // Assert
        $response->assertOk()
            ->assertJsonPath('message', 'Selected landlord deleted successfully')
            ->assertJsonPath('redirect', route('admin.landlords.index'));

        $this->assertDatabaseMissing('users', ['id' => $deleteA->id]);
        $this->assertDatabaseMissing('users', ['id' => $deleteB->id]);
        $this->assertDatabaseMissing('user_details', ['user_id' => $deleteA->id]);
        $this->assertDatabaseMissing('user_details', ['user_id' => $deleteB->id]);
        $this->assertDatabaseHas('users', ['id' => $keep->id]);
    }

    public function test_f6_filter_excel_and_csv_routes_stay_alive(): void
    {
        // Arrange
        $admin = $this->adminWith('landlords-read');
        $this->createLandlord(['name' => 'Alice Contract']);
        $this->createLandlord(['name' => 'Bob Contract']);

        // Act & Assert — filter (AJAX) filtra por búsqueda
        $filter = $this->actingAs($admin)
            ->postJson('/admin/landlords/filter', [
                'search' => 'Alice',
            ], ['X-Requested-With' => 'XMLHttpRequest']);

        $filter->assertOk();
        $this->assertStringContainsString('Alice Contract', $filter->json('data'));
        $this->assertStringNotContainsString('Bob Contract', $filter->json('data'));

        // Excel y CSV vivos tras la migración
        $this->actingAs($admin)->get('/admin/landlords-excel')->assertDownload('landlords.xlsx');
        $this->actingAs($admin)->get('/admin/landlords-csv')->assertDownload('landlords.csv');
    }
}
