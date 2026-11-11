<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            ['title' => 'Duplex', 'image' => 'uploads/25/10-332/1760178735.svg', 'status' => '1', 'created_at' => '2025-10-11 16:32:16', 'updated_at' => '2025-10-11 16:32:16'],
            ['title' => 'LUXURY', 'image' => 'uploads/25/10-530/1760178795.svg', 'status' => '1', 'created_at' => '2025-10-11 16:33:15', 'updated_at' => '2025-10-11 16:33:15'],
            ['title' => 'Business', 'image' => 'uploads/25/10-954/1760178822.svg', 'status' => '1', 'created_at' => '2025-10-11 16:33:42', 'updated_at' => '2025-10-11 16:33:42'],
            ['title' => 'Landscape', 'image' => 'uploads/25/10-119/1760178935.svg', 'status' => '1', 'created_at' => '2025-10-11 16:34:02', 'updated_at' => '2025-10-11 16:35:35'],
            ['title' => 'GLOBAL', 'image' => 'uploads/25/10-196/1760178892.svg', 'status' => '1', 'created_at' => '2025-10-11 16:34:52', 'updated_at' => '2025-10-11 16:34:52'],
            ['title' => 'Green House', 'image' => 'uploads/25/10-999/1760178965.svg', 'status' => '1', 'created_at' => '2025-10-11 16:36:05', 'updated_at' => '2025-10-11 16:36:05'],
        ];

        Client::insert($clients);
    }
}
