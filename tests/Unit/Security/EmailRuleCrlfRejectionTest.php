<?php

namespace Tests\Unit\Security;

use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('smoke')]
#[Group('security')]
class EmailRuleCrlfRejectionTest extends TestCase
{
    /**
     * F2 (spec 5.15): guard app-side del advisory high GHSA-5vg9-5847-vvmq /
     * CVE-2026-48019 (CRLF injection en la regla email por defecto, parchado
     * por el framework en >=13.10). Los ~14 flujos con `Mail::to($request->email)`
     * dependen de que estas direcciones no pasen validacion.
     */
    public function test_f2_email_rule_rejects_crlf_payloads(): void
    {
        $injections = [
            "user@example.com\r\nBcc: attacker@evil.com",
            "user@example.com\nBcc: attacker@evil.com",
            "user@example.com\rBcc: attacker@evil.com",
        ];

        foreach ($injections as $email) {
            $passes = Validator::make(['email' => $email], ['email' => 'required|email'])->passes();

            $this->assertFalse($passes, 'La regla email debe rechazar CR/LF: '.json_encode($email));
        }
    }

    public function test_f2_email_rule_still_accepts_clean_addresses(): void
    {
        $passes = Validator::make(
            ['email' => 'user@example.com'],
            ['email' => 'required|email'],
        )->passes();

        $this->assertTrue($passes);
    }
}
