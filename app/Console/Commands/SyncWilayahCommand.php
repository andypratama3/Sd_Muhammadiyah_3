<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class SyncWilayahCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-wilayah-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync wilayah Indonesia from API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Start Sync");

        try {
            $provinces = $this->fetchData("https://andypratama3.github.io/api-wilayah-indonesia/api/provinces.json");
            if (!$provinces) return;

            foreach ($provinces as $prov) {
                DB::table('provinsi')->updateOrInsert(
                    ['province_id' => $prov['id']],
                    [
                        'name' => $prov['name'],
                        'updated_at' => Carbon::now(),
                        'created_at' => DB::raw('IFNULL(created_at, NOW())')
                    ]
                );

                $this->info("Sync wilayah: success syncing province {$prov['name']}");

                // Ambil data kota/kabupaten
                $cities = $this->fetchData("https://andypratama3.github.io/api-wilayah-indonesia/api/regencies/{$prov['id']}.json");
                if (!$cities) continue;

                foreach ($cities as $city) {
                    DB::table('kabupaten')->updateOrInsert(
                        ['regency_id' => $city['id']],
                        [
                            'province_id' => $prov['id'],
                            'name' => $city['name'],
                            'updated_at' => Carbon::now(),
                            'created_at' => DB::raw('IFNULL(created_at, NOW())')
                        ]
                    );

                    $this->info("Sync wilayah: success syncing city {$city['name']}");

                    // Ambil data kecamatan
                    $districts = $this->fetchData("https://andypratama3.github.io/api-wilayah-indonesia/api/districts/{$city['id']}.json");
                    if (!$districts) continue;

                    foreach ($districts as $district) {
                        DB::table('kecamatan')->updateOrInsert(
                            ['district_id' => $district['id']],
                            [
                                'regency_id' => $city['id'],
                                'name' => $district['name'],
                                'updated_at' => Carbon::now(),
                                'created_at' => DB::raw('IFNULL(created_at, NOW())')
                            ]
                        );

                        $this->info("Sync wilayah: success syncing district {$district['name']}");

                        // Ambil data kelurahan
                        $villages = $this->fetchData("https://andypratama3.github.io/api-wilayah-indonesia/api/villages/{$district['id']}.json");
                        if (!$villages) continue;

                        foreach ($villages as $village) {
                            DB::table('kelurahan')->updateOrInsert(
                                ['village_id' => $village['id']],
                                [
                                    'district_id' => $district['id'],
                                    'name' => $village['name'],
                                    'updated_at' => Carbon::now(),
                                    'created_at' => DB::raw('IFNULL(created_at, NOW())')
                                ]
                            );

                            $this->info("Sync wilayah: success syncing village {$village['name']}");
                        }
                    }
                }
            }

            $this->info('Sync wilayah: finished syncing all regions');

        } catch (Exception $e) {
            $this->error("Sync wilayah: error - " . $e->getMessage());
        }
    }

    /**
     * Fetch data from API with retry and error handling.
     */
    private function fetchData($url)
    {
        try {
            $response = Http::timeout(30)->retry(3, 1000)->get($url);

            if ($response->failed()) {
                $this->error("Failed to fetch data from $url");
                return null;
            }

            sleep(1); // Hindari rate-limit API
            return $response->json();
        } catch (Exception $e) {
            $this->error("HTTP request failed: " . $e->getMessage());
            return null;
        }
    }
}
