<?php

namespace Tests\Unit\Support;

use App\Models\Option;
use App\Support\Otp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class OtpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_u14_otp_disabled(): void
    {
        Option::create([
            'key' => 'email-varification',
            'value' => ['otp_status' => 'off'],
        ]);

        $this->assertSame(0, Otp::visibilityTimeInSeconds());
    }

    public function test_u15_empty_time_uses_fallback(): void
    {
        Config::set('auth.otp_visibility_time', 3);

        Option::create([
            'key' => 'email-varification',
            'value' => [
                'otp_status' => 'on',
                'otp_expiration_time' => null,
                'otp_duration_type' => 'minute',
            ],
        ]);

        $this->assertSame(180, Otp::visibilityTimeInSeconds());
    }

    public function test_u16_minutes(): void
    {
        Option::create([
            'key' => 'email-varification',
            'value' => [
                'otp_status' => 'on',
                'otp_expiration_time' => 5,
                'otp_duration_type' => 'minute',
            ],
        ]);

        $this->assertSame(300, Otp::visibilityTimeInSeconds());
    }

    public function test_u17_seconds(): void
    {
        Option::create([
            'key' => 'email-varification',
            'value' => [
                'otp_status' => 'on',
                'otp_expiration_time' => 90,
                'otp_duration_type' => 'second',
            ],
        ]);

        $this->assertSame(90, Otp::visibilityTimeInSeconds());
    }

    public function test_u18_status_absent(): void
    {
        Option::create([
            'key' => 'email-varification',
            'value' => [],
        ]);

        $this->assertSame(0, Otp::visibilityTimeInSeconds());
    }
}
