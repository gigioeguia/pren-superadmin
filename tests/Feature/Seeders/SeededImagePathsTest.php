<?php

namespace Tests\Feature\Seeders;

use App\Models\Blog;
use App\Models\Client;
use App\Models\Feature;
use App\Models\Highlight;
use App\Models\Option;
use App\Models\Property;
use App\Models\PropertyDetail;
use App\Models\Testimonial;
use App\Models\Unit;
use App\Models\User;
use App\Models\UserDetail;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeededImagePathsTest extends TestCase
{
    use RefreshDatabase;

    public function test_f3_all_seeded_image_paths_exist_in_public(): void
    {
        $this->seed(DatabaseSeeder::class);

        $paths = [];

        User::whereNotNull('image')->pluck('image')->each(function (string $path) use (&$paths): void {
            $paths[] = $path;
        });

        UserDetail::query()->get()->each(function (UserDetail $detail) use (&$paths): void {
            $mykad = $detail->mykad;

            if (is_array($mykad)) {
                foreach (['front_image', 'back_image'] as $key) {
                    if (! empty($mykad[$key])) {
                        $paths[] = (string) $mykad[$key];
                    }
                }
            }
        });

        Property::query()->get()->each(function (Property $property) use (&$paths): void {
            foreach ($property->cover_image ?? [] as $path) {
                $paths[] = (string) $path;
            }
        });

        PropertyDetail::whereNotNull('floorplan_image')->pluck('floorplan_image')->each(function (string $path) use (&$paths): void {
            $paths[] = $path;
        });

        Unit::query()->get()->each(function (Unit $unit) use (&$paths): void {
            foreach ($unit->images ?? [] as $path) {
                $paths[] = (string) $path;
            }
        });

        Option::query()->get()->each(function (Option $option) use (&$paths): void {
            $this->collectFromOptionValue($option->value, $paths);
        });

        Blog::whereNotNull('image')->pluck('image')->each(fn (string $path) => $paths[] = $path);
        Feature::whereNotNull('image')->pluck('image')->each(fn (string $path) => $paths[] = $path);
        Client::whereNotNull('image')->pluck('image')->each(fn (string $path) => $paths[] = $path);
        Highlight::whereNotNull('image')->pluck('image')->each(fn (string $path) => $paths[] = $path);
        Testimonial::whereNotNull('client_image')->pluck('client_image')->each(fn (string $path) => $paths[] = $path);

        $paths = array_values(array_unique($paths));
        $this->assertNotEmpty($paths);

        foreach ($paths as $path) {
            $this->assertFileExists(public_path($path), "Seeded image does not exist: {$path}");
        }
    }

    /**
     * @param  array<string, mixed>|string|null  $value
     * @param  array<int, string>  $paths
     */
    private function collectFromOptionValue(array|string|null $value, array &$paths): void
    {
        if (is_array($value)) {
            foreach ($value as $nested) {
                $this->collectFromOptionValue($nested, $paths);
            }

            return;
        }

        if (is_string($value)) {
            preg_match_all('#uploads/[^\s"\',\[\]]+#', $value, $matches);

            foreach ($matches[0] as $match) {
                $paths[] = $match;
            }
        }
    }
}
