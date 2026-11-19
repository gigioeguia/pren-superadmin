<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class PermissionDenialSmokeTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
    }

    /**
     * Crea un admin con rol 'admin' al que se le revoca un permiso concreto.
     */
    private function adminWithout(string $permission): User
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $role = Role::findByName('admin', 'web');
        if ($role->hasPermissionTo($permission)) {
            $role->revokePermissionTo($permission);
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $admin = User::factory()->admin()->create();
        $admin->assignRole($role);

        return $admin;
    }

    public function test_f40_admin_without_blogs_read_receives_403(): void
    {
        $admin = $this->adminWithout('blogs-read');
        $this->actingAs($admin)->get('/admin/blogs')->assertForbidden();
    }

    public function test_f41_admin_without_clients_read_receives_403(): void
    {
        $admin = $this->adminWithout('clients-read');
        $this->actingAs($admin)->get('/admin/clients')->assertForbidden();
    }

    public function test_f42_admin_without_comments_delete_receives_403(): void
    {
        $admin = $this->adminWithout('comments-delete');
        $this->actingAs($admin)->post('/admin/comments/delete-all')->assertForbidden();
    }

    public function test_f43_admin_without_faqs_read_receives_403(): void
    {
        $admin = $this->adminWithout('faqs-read');
        $this->actingAs($admin)->get('/admin/faqs')->assertForbidden();
    }

    public function test_f44_admin_without_features_read_receives_403(): void
    {
        $admin = $this->adminWithout('features-read');
        $this->actingAs($admin)->get('/admin/features')->assertForbidden();
    }

    public function test_f45_admin_without_highlights_read_receives_403(): void
    {
        $admin = $this->adminWithout('highlights-read');
        $this->actingAs($admin)->get('/admin/highlights')->assertForbidden();
    }

    public function test_f46_admin_without_testimonials_read_receives_403(): void
    {
        $admin = $this->adminWithout('testimonials-read');
        $this->actingAs($admin)->get('/admin/testimonials')->assertForbidden();
    }

    public function test_f47_admin_without_website_settings_read_receives_403(): void
    {
        $admin = $this->adminWithout('website-settings-read');
        $this->actingAs($admin)->get('/admin/website-settings')->assertForbidden();
    }

    public function test_f48_admin_without_manage_settings_read_receives_403(): void
    {
        $admin = $this->adminWithout('manage-settings-read');
        $this->actingAs($admin)->get('/admin/manage-settings')->assertForbidden();
    }

    public function test_f49_admin_without_translations_create_receives_403(): void
    {
        $admin = $this->adminWithout('translations-create');
        $this->actingAs($admin)->post('/admin/translations', [])->assertForbidden();
    }

    public function test_f50_admin_without_permissions_read_receives_403(): void
    {
        $admin = $this->adminWithout('permissions-read');
        $this->actingAs($admin)->get('/admin/permissions')->assertForbidden();
    }

    public function test_f51_admin_without_languages_read_receives_403(): void
    {
        $admin = $this->adminWithout('languages-read');
        $this->actingAs($admin)->get('/admin/languages')->assertForbidden();
    }
}
