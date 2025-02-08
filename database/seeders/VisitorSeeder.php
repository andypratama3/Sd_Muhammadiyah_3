<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\Visitor;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class VisitorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Variasi IP address
        $ips = [
            '192.168.1.1', '203.0.113.42', '172.16.254.1', '10.0.0.1', '8.8.8.8'
        ];

        // Variasi User-Agent
        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/97.0.4692.99 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.81 Safari/537.36',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Firefox/91.0',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 14_2 like Mac OS X) AppleWebKit/537.36 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/537.36'
        ];

        $data = [];

        for ($i = 0; $i < 5000; $i++) {
            // Tentukan waktu dasar
            $timestamp = rand(strtotime('2020-01-01'), strtotime('2024-12-31'));
            $createdAt = Carbon::createFromTimestamp($timestamp);
            $updatedAt = Carbon::createFromTimestamp(rand($timestamp, strtotime('2024-12-31'))); // Pastikan `updated_at` >= `created_at`

            $data[] = [
                'id' => Str::uuid(),
                'ip_address' => $ips[array_rand($ips)],
                'user_agent' => $userAgents[array_rand($userAgents)],
                'date' => $createdAt,
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ];
        }

        foreach (array_chunk($data, 1000) as $chunk) {
            Visitor::insert($chunk);
        }
    }
}
