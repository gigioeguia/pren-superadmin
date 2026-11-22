<?php

namespace Tests\Feature\Language;

use App\Models\Language;
use App\Support\CacheKeys;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * Contrato HTTP de Admin/LanguageController (spec spec-service-language-fase5, F1–F11).
 *
 * Tests de contrato, NO de caracterización: congelan el comportamiento
 * actual (quirks incluidos) para garantizar que la migración a
 * LanguageService no cambia el contrato observable. Deben seguir en
 * verde antes y después de la migración — no llevan @group characterization.
 */
class AdminLanguageHttpTest extends TestCase
{
    use CreatesUsers, DatabaseTransactions;

    // Codes reservados para tests: no colisionan con los 105 locales de lang/.
    private const CODE_A = 'zz';

    private const CODE_B = 'zy';

    private const CODE_C = 'zx';

    protected function tearDown(): void
    {
        foreach ([self::CODE_A, self::CODE_B, self::CODE_C] as $code) {
            File::delete(base_path("lang/{$code}.json"));
        }

        parent::tearDown();
    }

    public function test_f1_index_lists_languages(): void
    {
        // Arrange
        $admin = $this->createSuperadmin();
        $baseline = Language::count();
        Language::create(['name' => 'Zeta', 'code' => self::CODE_A, 'order' => 1, 'status' => 1, 'is_default' => 0]);
        Language::create(['name' => 'Ypsilon', 'code' => self::CODE_B, 'order' => 2, 'status' => 1, 'is_default' => 0]);
        Language::create(['name' => 'Xi', 'code' => self::CODE_C, 'order' => 3, 'status' => 1, 'is_default' => 0]);

        // Act
        $response = $this->actingAs($admin)->get('/admin/languages');

        // Assert
        $response->assertOk()->assertViewIs('admin.languages.index');
        $this->assertCount($baseline + 3, $response->viewData('languages'));
    }

    public function test_f2_filter_returns_matching_rows_only(): void
    {
        // Arrange
        $admin = $this->createSuperadmin();
        Language::create(['name' => 'English', 'code' => self::CODE_A, 'order' => 1, 'status' => 1, 'is_default' => 0]);
        Language::create(['name' => 'French', 'code' => self::CODE_B, 'order' => 2, 'status' => 1, 'is_default' => 0]);

        // Act
        $response = $this->actingAs($admin)->postJson('/admin/languages/filter', [
            'search' => 'Fre',
        ], ['X-Requested-With' => 'XMLHttpRequest']);

        // Assert
        $response->assertOk()->assertJsonStructure(['data']);
        $this->assertStringContainsString('French', $response->json('data'));
        $this->assertStringNotContainsString('English', $response->json('data'));
    }

    public function test_f3_store_creates_row_and_json_file(): void
    {
        // Arrange
        Storage::fake('public');
        $admin = $this->createSuperadmin();
        $jsonPath = base_path('lang/'.self::CODE_A.'.json');
        File::delete($jsonPath);

        // Act
        $response = $this->actingAs($admin)->postJson('/admin/languages', [
            'order' => 1,
            'name' => 'Zeta',
            'code' => self::CODE_A,
            'icon' => UploadedFile::fake()->image('icon.png'),
        ]);

        // Assert
        $response->assertOk()->assertJson(['redirect' => route('admin.languages.index')]);
        $this->assertDatabaseHas('languages', ['code' => self::CODE_A, 'name' => 'Zeta']);
        $this->assertFileExists($jsonPath);

        $language = Language::where('code', self::CODE_A)->first();
        $this->assertNotNull($language->icon);
        Storage::disk('public')->assertExists($language->icon);
    }

    public function test_f4_update_moves_json_and_replaces_icon(): void
    {
        // Arrange
        Storage::fake('public');
        $admin = $this->createSuperadmin();
        $language = Language::create([
            'name' => 'Zeta', 'code' => self::CODE_A, 'order' => 1,
            'status' => 1, 'is_default' => 0, 'icon' => 'flags/old.svg',
        ]);
        Storage::disk('public')->put('flags/old.svg', 'flag');
        File::put(base_path('lang/'.self::CODE_A.'.json'), '{"k":"v"}');
        File::delete(base_path('lang/'.self::CODE_B.'.json'));

        // Act
        $response = $this->actingAs($admin)->putJson("/admin/languages/{$language->id}", [
            'name' => 'Zeta Renamed',
            'code' => self::CODE_B,
            'order' => 2,
            'icon' => UploadedFile::fake()->image('new.png'),
        ]);

        // Assert
        $response->assertOk();
        $this->assertDatabaseHas('languages', ['id' => $language->id, 'name' => 'Zeta Renamed', 'code' => self::CODE_B]);
        $this->assertFileDoesNotExist(base_path('lang/'.self::CODE_A.'.json'));
        $this->assertFileExists(base_path('lang/'.self::CODE_B.'.json'));

        // Icono nuevo subido, viejo borrado (HasUploader::deleteOldFile).
        Storage::disk('public')->assertMissing('flags/old.svg');
        Storage::disk('public')->assertExists($language->fresh()->icon);
    }

    public function test_f5_destroy_removes_row_and_json_but_not_icon(): void
    {
        // Arrange
        Storage::fake('public');
        $admin = $this->createSuperadmin();
        $language = Language::create([
            'name' => 'Zeta', 'code' => self::CODE_A, 'order' => 1,
            'status' => 1, 'is_default' => 0, 'icon' => 'flags/us.svg',
        ]);
        Storage::disk('public')->put('flags/us.svg', 'flag');
        File::put($jsonPath = base_path('lang/'.self::CODE_A.'.json'), '{"k":"v"}');

        // Act
        $response = $this->actingAs($admin)->deleteJson("/admin/languages/{$language->id}");

        // Assert
        $response->assertOk();
        $this->assertDatabaseMissing('languages', ['id' => $language->id]);
        $this->assertFileDoesNotExist($jsonPath);

        // Quirk preservado (spec §5.1): el guard file_exists() relativo al CWD
        // nunca coincide con la ruta del disk — el icono NO se borra hoy.
        Storage::disk('public')->assertExists('flags/us.svg');
    }

    public function test_f6_delete_all_excludes_default_language(): void
    {
        // Arrange
        $admin = $this->createSuperadmin();
        $default = Language::create([
            'name' => 'Default', 'code' => self::CODE_A, 'order' => 1,
            'status' => 1, 'is_default' => 1,
        ]);
        File::put(base_path('lang/'.self::CODE_A.'.json'), '{}');
        $beta = Language::create([
            'name' => 'Beta', 'code' => self::CODE_B, 'order' => 2,
            'status' => 1, 'is_default' => 0,
        ]);
        File::put(base_path('lang/'.self::CODE_B.'.json'), '{}');
        $gamma = Language::create([
            'name' => 'Gamma', 'code' => self::CODE_C, 'order' => 3,
            'status' => 1, 'is_default' => 0,
        ]);

        // Act
        $response = $this->actingAs($admin)->postJson('/admin/languages/delete-all', [
            'ids' => [$default->id, $beta->id, $gamma->id],
        ]);

        // Assert
        $response->assertOk();
        $this->assertDatabaseHas('languages', ['id' => $default->id, 'is_default' => 1]);
        $this->assertDatabaseMissing('languages', ['id' => $beta->id]);
        $this->assertDatabaseMissing('languages', ['id' => $gamma->id]);
        $this->assertFileExists(base_path('lang/'.self::CODE_A.'.json'));
        $this->assertFileDoesNotExist(base_path('lang/'.self::CODE_B.'.json'));
    }

    public function test_f7_status_guard_keeps_default_language_active(): void
    {
        // Arrange
        $admin = $this->createSuperadmin();
        $language = Language::create([
            'name' => 'Default', 'code' => self::CODE_A, 'order' => 1,
            'status' => 1, 'is_default' => 1,
        ]);

        // Act
        $response = $this->actingAs($admin)->postJson("/admin/languages/status/{$language->id}", [
            'status' => 0,
        ]);

        // Assert — guard is_default: 403 y permanece activa.
        $response->assertStatus(403);
        $this->assertDatabaseHas('languages', ['id' => $language->id, 'status' => 1]);
    }

    public function test_f8_set_default_switches_option_and_invalidates_cache(): void
    {
        // Arrange
        $admin = $this->createSuperadmin();
        $current = Language::create([
            'name' => 'Alpha', 'code' => self::CODE_A, 'order' => 1,
            'status' => 1, 'is_default' => 1,
        ]);
        $target = Language::create([
            'name' => 'Beta', 'code' => self::CODE_B, 'order' => 2,
            'status' => 1, 'is_default' => 0,
        ]);
        cache([CacheKeys::languages() => 'stale']);

        // Act
        $response = $this->actingAs($admin)->postJson("/admin/languages/default/{$target->id}");

        // Assert
        $response->assertOk();
        $this->assertDatabaseHas('languages', ['id' => $target->id, 'is_default' => 1]);
        $this->assertDatabaseHas('languages', ['id' => $current->id, 'is_default' => 0]);
        $this->assertNull(cache(CacheKeys::languages()));
    }

    public function test_f9_translations_survive_store_and_update_flows(): void
    {
        // Arrange
        Storage::fake('public');
        $admin = $this->createSuperadmin();
        File::delete(base_path('lang/'.self::CODE_A.'.json'));
        $baseKeys = json_decode(File::get(base_path('lang/en.json')), true);

        // Act — store copia el JSON base sin reescribir contenido.
        $this->actingAs($admin)->postJson('/admin/languages', [
            'order' => 1, 'name' => 'Zeta', 'code' => self::CODE_A,
        ])->assertOk();
        $this->assertSame($baseKeys, json_decode(File::get(base_path('lang/'.self::CODE_A.'.json')), true));

        // Act — update cambia el code: el JSON se mueve intacto.
        $custom = ['greeting' => 'hola', 'farewell' => 'adios'];
        File::put(base_path('lang/'.self::CODE_A.'.json'), json_encode($custom));
        $language = Language::where('code', self::CODE_A)->first();
        File::delete(base_path('lang/'.self::CODE_B.'.json'));

        $this->actingAs($admin)->putJson("/admin/languages/{$language->id}", [
            'name' => 'Zeta', 'code' => self::CODE_B, 'order' => 2,
        ])->assertOk();

        // Assert — keys del JSON idénticas tras el move.
        $this->assertSame($custom, json_decode(File::get(base_path('lang/'.self::CODE_B.'.json')), true));
    }

    public function test_f10_show_lists_translations(): void
    {
        // Arrange
        $admin = $this->createSuperadmin();
        Language::create([
            'name' => 'Zeta', 'code' => self::CODE_A, 'order' => 1,
            'status' => 1, 'is_default' => 0,
        ]);
        File::put(base_path('lang/'.self::CODE_A.'.json'), json_encode([
            'k1' => 'v1', 'k2' => 'v2', 'k3' => 'v3',
        ]));

        // Act
        $response = $this->actingAs($admin)->get('/admin/languages/'.self::CODE_A);

        // Assert
        $response->assertOk()->assertViewIs('admin.languages.translates.translate');
        $this->assertCount(3, $response->viewData('rows'));
    }

    public function test_f11_show_aborts_when_json_file_is_missing(): void
    {
        // Arrange
        $admin = $this->createSuperadmin();
        Language::create([
            'name' => 'Zeta', 'code' => self::CODE_A, 'order' => 1,
            'status' => 1, 'is_default' => 0,
        ]);
        File::delete(base_path('lang/'.self::CODE_A.'.json'));

        // Act
        $response = $this->actingAs($admin)->get('/admin/languages/'.self::CODE_A);

        // Assert
        $response->assertNotFound();
    }
}
