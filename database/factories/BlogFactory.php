<?php

namespace Database\Factories;

use App\Models\Blog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Blog>
 */
class BlogFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4).' '.fake()->unique()->randomNumber(5);

        return [
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'image' => 'backend/assets/img/no_image.jpg',
            'status' => 1,
            'descriptions' => fake()->paragraph(),
            'tags' => ['news'],
            'meta' => [],
        ];
    }
}
