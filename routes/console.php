<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('demo:reset', function () {
    if (! app()->environment('demo')) {
        $this->warn('demo:reset is only available in the demo environment.');

        return 1;
    }

    demoReset();

    $this->info('Demo reset successfully.');

    return 0;
})->describe('Demo reset successfully.');
