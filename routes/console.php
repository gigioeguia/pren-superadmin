<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('demo:reset', function () {
    demoReset();
})->describe('Demo reset successfully.');
