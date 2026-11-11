<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $testimonials = [
            ['star' => '5', 'client_name' => 'Michael Turner', 'client_image' => 'uploads/25/10-587/1760167826.png', 'work_at' => 'UrbanStay Apartments', 'text' => 'This platform has completely streamlined how I manage my apartments. From rent tracking to maintenance requests, everything is easy to handle in one place. It saves hours of manual work every week!', 'created_at' => '2025-06-24 15:25:25', 'updated_at' => '2025-10-11 13:32:01'],
            ['star' => '4', 'client_name' => 'Sophia Reed', 'client_image' => 'uploads/25/10-105/1760167905.png', 'work_at' => 'BlueSky Realty', 'text' => 'The analytics and reporting features are impressive. I can easily monitor performance and understand client trends, which helps me make smarter decisions and close deals faster.', 'created_at' => '2025-06-24 15:25:25', 'updated_at' => '2025-10-11 13:31:45'],
            ['star' => '3', 'client_name' => 'Daniel Kim', 'client_image' => 'uploads/25/10-198/1760167969.png', 'work_at' => 'PrimeRent Solutions', 'text' => 'Managing multiple properties used to be a headache, but not anymore. The automation tools and clean interface make our daily operations smoother and far more efficient.', 'created_at' => '2025-06-24 15:25:25', 'updated_at' => '2025-10-11 13:32:49'],
            ['star' => '5', 'client_name' => 'James Carter', 'client_image' => 'uploads/25/10-891/1760168010.jpg', 'work_at' => 'Carter Residential Group', 'text' => 'The system’s real-time insights are game-changing. It keeps me informed about occupancy rates and income flow instantly, helping me make better investment and management decisions.', 'created_at' => '2025-06-24 15:25:25', 'updated_at' => '2025-10-11 13:33:30'],
            ['star' => '4', 'client_name' => 'Alim Patel', 'client_image' => 'uploads/25/10-932/1760168359.png', 'work_at' => 'HomeNest Properties', 'text' => 'I love how user-friendly the dashboard is. Tenants can pay rent online, submit maintenance issues, and communicate directly — everything works seamlessly, even on mobile.', 'created_at' => '2025-10-11 13:39:19', 'updated_at' => '2025-10-11 13:39:19'],
        ];

        Testimonial::insert($testimonials);
    }
}
