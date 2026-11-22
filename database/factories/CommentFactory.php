<?php

namespace Database\Factories;

use App\Models\Blog;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'blog_id' => Blog::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'comment' => fake()->sentence(),
            'status' => 1,
        ];
    }
}
