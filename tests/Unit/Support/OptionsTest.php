<?php

namespace Tests\Unit\Support;

use App\Models\Option;
use App\Support\Options;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * U1–U9 del spec-get-option-tipos-consistentes-fase5 (Fase 5 / 5.7, Seg-24):
 * contrato tipado de App\Support\Options + helper get_option().
 */
class OptionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_u1_helper_returns_empty_array_when_row_is_missing(): void
    {
        // Act + Assert
        $this->assertSame([], get_option('general'));
    }

    public function test_u2_helper_returns_stored_array(): void
    {
        // Arrange
        Option::create([
            'key' => 'general',
            'value' => ['app_name' => 'X'],
        ]);

        // Act + Assert
        $this->assertSame(['app_name' => 'X'], get_option('general'));
    }

    public function test_u3_string_returns_empty_string_when_row_is_missing(): void
    {
        // Act + Assert
        $this->assertSame('', Options::string('version'));
    }

    public function test_u4_string_returns_stored_scalar(): void
    {
        // Arrange
        Option::create([
            'key' => 'version',
            'value' => '2.1',
        ]);

        // Act + Assert
        $this->assertSame('2.1', Options::string('version'));
    }

    public function test_u5_corrupt_json_falls_back_to_typed_default(): void
    {
        // Arrange — insert raw para bypassear el cast json del modelo
        DB::table('options')->insert(['key' => 'general', 'value' => '{bad']);

        // Act + Assert
        $this->assertSame([], Options::array('general'));
        $this->assertSame('', Options::string('general'));
    }

    public function test_u6_scalar_row_read_as_array_falls_back_silently(): void
    {
        // Arrange
        Option::create([
            'key' => 'version',
            'value' => '2.1',
        ]);

        // Act + Assert
        $this->assertSame([], Options::array('version'));
    }

    public function test_u7_array_row_read_as_string_falls_back_silently(): void
    {
        // Arrange
        Option::create([
            'key' => 'general',
            'value' => ['app_name' => 'X'],
        ]);

        // Act + Assert
        $this->assertSame('', Options::string('general'));
    }

    public function test_u8_explicit_defaults_are_respected(): void
    {
        // Act + Assert
        $this->assertSame(['app_name' => 'D'], Options::array('general', ['app_name' => 'D']));
        $this->assertSame('0.0', Options::string('version', '0.0'));
    }

    public function test_u9_observer_invalidation_stays_alive_through_helper(): void
    {
        // Arrange
        $option = Option::create([
            'key' => 'general',
            'value' => ['app_name' => 'X'],
        ]);
        $this->assertSame(['app_name' => 'X'], get_option('general'));

        // Act — update de instancia: la mass-op de builder no dispara observers (gotcha 4.9)
        $option->update(['value' => ['app_name' => 'Y']]);

        // Assert
        $this->assertSame(['app_name' => 'Y'], get_option('general'));
    }
}
