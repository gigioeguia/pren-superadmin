<?php

namespace App\Support;

class Otp
{
    public static function visibilityTimeInSeconds(): int
    {
        $otpSettings = get_option('email-varification') ?? [];

        $otpStatus = $otpSettings['otp_status'] ?? 'off';

        if ($otpStatus === 'off') {
            return 0;
        }

        $time = $otpSettings['otp_expiration_time'] ?? null;
        $durationType = $otpSettings['otp_duration_type'] ?? 'minute';

        $defaultFromEnv = (int) (config('auth.otp_visibility_time') ?? 3);

        if (empty($time)) {
            return $defaultFromEnv * 60;
        }

        return $durationType === 'minute' ? $time * 60 : (int) $time;
    }
}
