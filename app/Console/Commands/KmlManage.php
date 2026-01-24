<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\KmlService;

class KmlManage extends Command
{
    protected $signature = 'kml:manage {action : info|validate|clear-cache|test}
                                       {--lat= : Latitude untuk test}
                                       {--lon= : Longitude untuk test}';

    protected $description = 'Manage KML file untuk absensi';

    protected $kmlService;

    public function __construct(KmlService $kmlService)
    {
        parent::__construct();
        $this->kmlService = $kmlService;
    }

    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'info':
                $this->showInfo();
                break;

            case 'validate':
                $this->validateKml();
                break;

            case 'clear-cache':
                $this->clearCache();
                break;

            case 'test':
                $this->testLocation();
                break;

            default:
                $this->error('Action tidak valid. Gunakan: info, validate, clear-cache, atau test');
                return 1;
        }

        return 0;
    }

    private function showInfo()
    {
        $this->info('📄 Informasi File KML');
        $this->newLine();

        $info = $this->kmlService->getKmlInfo();

        if (!$info['exists']) {
            $this->error('❌ ' . $info['message']);
            $this->line('Path: ' . config('absensi.kml_file_path', 'Belum dikonfigurasi'));
            $this->newLine();
            $this->warn('💡 Tip: Set path KML di config/absensi.php');
            return;
        }

        $this->table(
            ['Property', 'Value'],
            [
                ['File Name', $info['file_name']],
                ['File Path', $info['file_path']],
                ['File Size', $info['file_size_human']],
                ['Last Modified', $info['last_modified_human']],
                ['Total Polygons', $info['total_polygons']],
                ['Cached', $info['is_cached'] ? '✅ Yes' : '❌ No'],
            ]
        );

        if (!empty($info['areas'])) {
            $this->newLine();
            $this->info('📍 Areas:');
            foreach ($info['areas'] as $area) {
                $this->line("  • {$area}");
            }
        }

        $this->newLine();
        $this->info('✅ File KML loaded successfully');
    }

    private function validateKml()
    {
        $this->info('🔍 Validasi File KML...');
        $this->newLine();

        $result = $this->kmlService->loadKmlFile();

        if (!$result['success']) {
            $this->error('❌ ' . $result['message']);
            return;
        }

        $this->info("✅ File KML valid");
        $this->info("📊 Ditemukan {$result['count']} polygon(s)");
        $this->newLine();

        if (!empty($result['polygons'])) {
            $tableData = [];
            foreach ($result['polygons'] as $index => $polygon) {
                $tableData[] = [
                    $index + 1,
                    $polygon['name'],
                    $polygon['point_count'] ?? count($polygon['coordinates'])
                ];
            }

            $this->table(
                ['#', 'Area Name', 'Points'],
                $tableData
            );
        }
    }

    private function clearCache()
    {
        $this->info('🗑️  Clearing KML cache...');

        $this->kmlService->clearCache();

        $this->newLine();
        $this->info('✅ Cache KML berhasil dihapus');
        $this->line('💡 Data akan di-reload dari file saat validasi berikutnya');
    }

    private function testLocation()
    {
        $lat = $this->option('lat');
        $lon = $this->option('lon');

        if (!$lat || !$lon) {
            $this->error('❌ Gunakan --lat dan --lon untuk test lokasi');
            $this->newLine();
            $this->line('Contoh:');
            $this->line('  php artisan kml:manage test --lat=-0.479486 --lon=117.154618');
            return;
        }

        $this->info("🧪 Testing lokasi: {$lat}, {$lon}");
        $this->newLine();

        // Show loading
        $bar = $this->output->createProgressBar(3);
        $bar->setFormat('verbose');
        $bar->start();

        $bar->advance();
        sleep(1);

        $result = $this->kmlService->validateLocation($lat, $lon);

        $bar->advance();
        sleep(1);

        $bar->finish();
        $this->newLine(2);

        if ($result['valid']) {
            $this->info("✅ {$result['message']}");

            $this->newLine();
            $this->table(
                ['Property', 'Value'],
                [
                    ['Latitude', $lat],
                    ['Longitude', $lon],
                    ['Status', '✅ Valid'],
                    ['Area Name', $result['area_name'] ?? '-'],
                ]
            );
        } else {
            $this->error("❌ {$result['message']}");

            $this->newLine();
            $this->table(
                ['Property', 'Value'],
                [
                    ['Latitude', $lat],
                    ['Longitude', $lon],
                    ['Status', '❌ Invalid'],
                    ['Reason', 'Diluar area yang diizinkan'],
                ]
            );

            $this->newLine();
            $this->warn('💡 Pastikan koordinat berada dalam polygon yang terdefinisi di file KML');
        }
    }
}
