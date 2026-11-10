<?php

namespace Database\Seeders;

use App\Models\Highlight;
use Illuminate\Database\Seeder;

class HighlightSeeder extends Seeder
{
    public function run(): void
    {
        $highlights = array(
            array('title' => 'Exceptional Homes For Exceptional Lives.', 'short_title' => 'Ultimate Luxury', 'slug' => 'exceptional-homes-for-exceptional-lives', 'image' => 'uploads/25/10-852/1760609611.png', 'status' => '1', 'description' => '<p>We design and manage properties that go beyond walls and roofs—homes built with comfort, style, and innovation to match the lifestyle you dese rve. Our commitment is to create living spaces where quality meets convenience, making every day truly exceptional.</p><p>We believe a home should be more than just a place to live — it should be a reflection of who you are. Each of our properties is thoughtfully designed to combine timeless architecture, modern comfort, and unmatched attention to detail. From spacious interiors to serene surroundings, we create living spaces that inspire, uplift, and enhance every moment of your life.</p><p> </p><p>Because&nbsp;we believe a home should be more than just a place to live — it should be a reflection of who you are. Each of our properties is thoughtfully designed to combine timeless architecture, modern comfort, and unmatched attention to detail. From spacious interiors to serene surroundings, we create living spaces that inspire, uplift, and enhance every moment of your life. Because exceptional people deserve nothing less than exceptional homes.</p>', 'created_at' => '2025-10-16 16:13:31', 'updated_at' => '2025-10-18 10:51:50'),
            array('title' => 'Find The Perfect Place To Call Home.', 'short_title' => 'Extreme Comport', 'slug' => 'find-the-perfect-place-to-call-home', 'image' => 'uploads/25/10-531/1760609757.png', 'status' => '1', 'description' => '<p>We design and manage properties that go beyond walls and roofs—homes built with comfort, style, and innovation to match the lifestyle you dese rve. Our commitment is to create living spaces where quality meets convenience, making every day truly exceptional.</p><p>We believe a home is far more than just a place to live — it’s a personal sanctuary that mirrors your dreams, your passions, and your lifestyle. Every property we craft is designed with care, blending timeless architecture, modern comfort, and meticulous attention to every detail. From airy interiors filled with natural light to peaceful surroundings that nurture your soul, we create living environments where every moment feels meaningful and serene.</p><p>Because we understand that a home should inspire you every single day — a space where comfort meets elegance, and design meets emotion. It’s where mornings begin with warmth, evenings end in peace, and every corner tells a story of who you are. We believe your home should evolve with you — a timeless retreat that celebrates life, love, and the beauty of belonging.</p>', 'created_at' => '2025-10-15 16:15:57', 'updated_at' => '2025-10-18 10:52:33')
        );

        Highlight::insert($highlights);
    }
}
