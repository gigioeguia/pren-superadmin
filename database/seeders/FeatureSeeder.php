<?php

namespace Database\Seeders;

use App\Models\Feature;
use Illuminate\Database\Seeder;

class FeatureSeeder extends Seeder
{
    public function run(): void
    {
        $features = [
            ['title' => 'Dashboard', 'bg_color' => '#ecefff', 'image' => 'uploads/25/10-505/1760168976.svg', 'status' => '1', 'created_at' => '2025-06-24 10:52:25', 'updated_at' => '2025-10-11 13:49:36'],
            ['title' => 'Property', 'bg_color' => '#fff0f4', 'image' => 'uploads/25/10-448/1760169076.svg', 'status' => '1', 'created_at' => '2025-06-24 10:52:25', 'updated_at' => '2025-10-11 13:51:16'],
            ['title' => 'Landlords', 'bg_color' => '#e8f7ef', 'image' => 'uploads/25/10-366/1760169133.svg', 'status' => '1', 'created_at' => '2025-06-24 10:52:25', 'updated_at' => '2025-10-11 13:52:13'],
            ['title' => 'Tenants', 'bg_color' => '#fff1e6', 'image' => 'uploads/25/10-149/1760169514.svg', 'status' => '1', 'created_at' => '2025-06-24 10:52:25', 'updated_at' => '2025-10-11 13:58:34'],
            ['title' => 'Application', 'bg_color' => '#dff4fe', 'image' => 'uploads/25/10-294/1760169585.svg', 'status' => '1', 'created_at' => '2025-06-24 10:52:25', 'updated_at' => '2025-10-11 13:59:45'],
            ['title' => 'Maintenance', 'bg_color' => '#dcf3e7', 'image' => 'uploads/25/10-687/1760173606.svg', 'status' => '1', 'created_at' => '2025-06-24 10:52:25', 'updated_at' => '2025-10-11 15:06:46'],
            ['title' => 'Labor', 'bg_color' => '#fff2e0', 'image' => 'uploads/25/10-557/1760171104.svg', 'status' => '1', 'created_at' => '2025-06-24 10:52:25', 'updated_at' => '2025-10-11 14:25:04'],
            ['title' => 'Rent Invitation', 'bg_color' => '#f0e8ff', 'image' => 'uploads/25/10-288/1760173897.svg', 'status' => '1', 'created_at' => '2025-06-24 10:52:25', 'updated_at' => '2025-10-11 15:11:37'],
            ['title' => 'Payments', 'bg_color' => '#f7ecff', 'image' => 'uploads/25/10-418/1760171175.svg', 'status' => '1', 'created_at' => '2025-06-24 10:52:25', 'updated_at' => '2025-10-11 14:26:15'],
            ['title' => 'Refund', 'bg_color' => '#e1fffd', 'image' => 'uploads/25/10-92/1760173855.svg', 'status' => '1', 'created_at' => '2025-06-24 10:52:25', 'updated_at' => '2025-10-11 15:10:55'],
            ['title' => 'Withdraw', 'bg_color' => '#ecefff', 'image' => 'uploads/25/10-117/1760171251.svg', 'status' => '1', 'created_at' => '2025-06-24 10:52:25', 'updated_at' => '2025-10-11 14:27:31'],
            ['title' => 'Incomes', 'bg_color' => '#e4ffeb', 'image' => 'uploads/25/10-893/1760171287.svg', 'status' => '1', 'created_at' => '2025-06-24 10:52:25', 'updated_at' => '2025-10-11 14:28:07'],
            ['title' => 'Expenses', 'bg_color' => '#fbece0', 'image' => 'uploads/25/10-768/1760173811.svg', 'status' => '1', 'created_at' => '2025-06-24 10:52:25', 'updated_at' => '2025-10-11 15:10:11'],
            ['title' => 'House Type', 'bg_color' => '#f3e6ff', 'image' => 'uploads/25/10-545/1760174206.svg', 'status' => '1', 'created_at' => '2025-06-24 10:52:25', 'updated_at' => '2025-10-11 15:16:46'],
            ['title' => 'Reports', 'bg_color' => '#dff4fe', 'image' => 'uploads/25/10-531/1760171389.svg', 'status' => '1', 'created_at' => '2025-06-24 10:52:25', 'updated_at' => '2025-10-11 14:29:49'],
            ['title' => 'Roles & Permissions', 'bg_color' => '#ffe7e2', 'image' => 'uploads/25/10-883/1760173648.svg', 'status' => '1', 'created_at' => '2025-06-24 10:52:25', 'updated_at' => '2025-10-11 15:07:28'],
            ['title' => 'Messages', 'bg_color' => '#dff4fe', 'image' => 'uploads/25/10-282/1760171493.svg', 'status' => '1', 'created_at' => '2025-06-24 10:52:25', 'updated_at' => '2025-10-11 15:07:28'],
            ['title' => 'Notification', 'bg_color' => '#fff2e0', 'image' => 'uploads/25/10-611/1760173549.svg', 'status' => '1', 'created_at' => '2025-06-24 10:52:25', 'updated_at' => '2025-10-11 15:07:28'],
            ['title' => '100+ Languages', 'bg_color' => '#dcf3e7', 'image' => 'uploads/25/10-359/1760171544.svg', 'status' => '1', 'created_at' => '2025-06-24 10:52:25', 'updated_at' => '2025-10-11 15:07:28'],
            ['title' => 'More Features...', 'bg_color' => '#f0e8ff', 'image' => 'uploads/25/10-126/1760171567.svg', 'status' => '1', 'created_at' => '2025-06-24 10:52:25', 'updated_at' => '2025-10-11 15:07:28'],
        ];

        Feature::insert($features);
    }
}
