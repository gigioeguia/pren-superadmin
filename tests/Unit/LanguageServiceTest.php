<?php

namespace Tests\Unit;

use App\Models\Language;
use App\Services\LanguageService;
use App\Support\CacheKeys;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class LanguageServiceTest extends TestCase
{
    use DatabaseTransactions;

    private const CODE_A = 'zz';

    private const CODE_B = 'zy';

    private const CODE_C = 'zx';

    protected LanguageService $service;

    protected function tearDown(): void
    {
        foreach ([self::CODE_A, self::CODE_B, self::CODE_C] as $code) {
            File::delete(base_path("lang/{$code}.json"));
        }

        parent::tearDown();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(LanguageService::class);
    }

    public function test_u1_store_creates_row_and_base_json(): void
    {
        // Arrange
        File::delete($jsonPath = base_path('lang/'.self::CODE_A.'.json'));

        // Act
        $language = $this->service->store([
            'name' => 'Zeta',
            'code' => self::CODE_A,
            'order' => 1,
            'icon' => null,
        ]);

        // Assert
        $this->assertInstanceOf(Language::class, $language);
        $this->assertDatabaseHas('languages', ['code' => self::CODE_A, 'name' => 'Zeta']);
        $this->assertFileExists($jsonPath);
        $this->assertSame(
            File::get(base_path('lang/en.json')),
            File::get($jsonPath)
        );
    }

    public function test_u2_delete_many_excludes_default_and_cleans_json_post_commit(): void
    {
        // Arrange
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
        $count = $this->service->deleteMany([$default->id, $beta->id, $gamma->id]);

        // Assert
        $this->assertSame(2, $count);
        $this->assertDatabaseHas('languages', ['id' => $default->id, 'is_default' => 1]);
        $this->assertDatabaseMissing('languages', ['id' => $beta->id]);
        $this->assertDatabaseMissing('languages', ['id' => $gamma->id]);
        $this->assertFileExists(base_path('lang/'.self::CODE_A.'.json'));
        $this->assertFileDoesNotExist(base_path('lang/'.self::CODE_B.'.json'));
    }

    public function test_u3_set_status_guard_keeps_default_active(): void
    {
        // Arrange
        $default = Language::create([
            'name' => 'Default', 'code' => self::CODE_A, 'order' => 1,
            'status' => 1, 'is_default' => 1,
        ]);

        // Act
        $this->service->setStatus($default, 0);

        // Assert — guard is_default: no-op.
        $this->assertSame(1, $default->fresh()->status);
    }

    public function test_u4_set_default_switches_and_invalidates_cache(): void
    {
        // Arrange
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
        $returned = $this->service->setDefault((string) $target->id);

        // Assert
        $this->assertNotNull($returned);
        $this->assertSame(true, $target->fresh()->is_default);
        $this->assertSame(false, $current->fresh()->is_default);
        $this->assertNull(cache(CacheKeys::languages()));
    }
}
