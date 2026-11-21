<?php

namespace Tests\Feature\Options;

use App\Models\Option;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * G1–G2 del spec-get-option-tipos-consistentes-fase5 (Fase 5 / 5.7):
 * `version` (única key escalar) leída vía App\Support\Options::string
 * en el sidebar admin.
 */
class VersionSidebarSmokeTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_g1_version_is_visible_when_seeded(): void
    {
        // Arrange
        $admin = $this->createAdmin();
        Option::create([
            'key' => 'version',
            'value' => '2.1',
        ]);

        // Act + Assert
        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Version : 2.1');
    }

    public function test_g2_version_absent_does_not_crash(): void
    {
        // Arrange
        $admin = $this->createAdmin();

        // Act + Assert
        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertDontSee('Version :');
    }
}
