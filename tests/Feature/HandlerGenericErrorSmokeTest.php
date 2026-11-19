<?php

namespace Tests\Feature;

use App\Support\ErrorMessage;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class HandlerGenericErrorSmokeTest extends TestCase
{
    public function test_handler_returns_generic_message_in_production(): void
    {
        config(['app.debug' => false]);

        Route::get('/__boom', function () {
            throw new \RuntimeException('secret-internal');
        });

        $response = $this->get('/__boom');

        $response->assertStatus(500);
        $this->assertStringNotContainsString('secret-internal', $response->getContent());
        $this->assertStringContainsString(ErrorMessage::GENERIC, $response->getContent());
    }
}
