<?php

use App\Models\Option;
use Database\Seeders\LanguageSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/update', function () {

    $version = Option::where('key', 'version')->value('value') ?? null;

    Artisan::call('migrate');

    if (! $version) {
        Artisan::call('db:seed', [
            '--class' => LanguageSeeder::class,
            '--force' => true,
        ]);
    }

    Option::updateOrCreate(
        ['key' => 'version'],
        ['value' => '2.1']
    );

    // Clear caches
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');

    return redirect('/')->with('message', __('System updated successfully.'));
});

Route::get('/cache-clear', function () {

    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');

    return back()->with('success', __('Cache has been cleared.'));
});
