<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Karyawan;
use App\Models\Absensi;
use App\Services\AbsensiService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GenerateAbsensiHarian extends Command
{
    protected $signature = 'absensi:generate-harian {--date= : Tanggal untuk generate absensi (Y-m-d)}';
    protected $description = 'Generate status absensi harian untuk semua karyawan (alpha jika tidak absen dan tidak cuti)';

    protected $absensiService;

    public function __construct(AbsensiService $absensiService)
    {
        parent::__construct();
        $this->absensiService = $absensiService;
    }

    public function handle()
    {
        $this->info('🚀 Memulai generate absensi harian...');

        // Ambil tanggal dari parameter atau gunakan kemarin
        $tanggalString = $this->option('date') ?? Carbon::now()->format('Y-m-d');
        $tanggal = Carbon::createFromFormat('Y-m-d', $tanggalString);


        // Cek apakah hari kerja
        if (!$this->isHariKerja($tanggal)) {
            $this->warn("⚠️  {$tanggal->format('l')} bukan hari kerja. Skip generate absensi.");
            return 0;
        }

        // Ambil semua karyawan aktif
        $karyawans = Karyawan::with(['user.roles'])->get();
        $this->info("👥 Total karyawan aktif: {$karyawans->count()}");

        $stats = [
            'hadir' => 0,
            'cuti' => 0,
            'izin' => 0,
            'sakit' => 0,
            'alpha' => 0,
            'error' => 0
        ];

        $progressBar = $this->output->createProgressBar($karyawans->count());
        $progressBar->start();

        foreach ($karyawans as $karyawan) {
            try {
                $result = $this->processKaryawan($karyawan, $tanggal);
                $stats[$result]++;
            } catch (\Exception $e) {
                $this->error("\n❌ Error untuk {$karyawan->name}: {$e->getMessage()}");
                Log::error('Generate Absensi Error', [
                    'karyawan_id' => $karyawan->id,
                    'tanggal' => $tanggal->toDateString(),
                    'error' => $e->getMessage()
                ]);
                $stats['error']++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Tampilkan statistik
        $this->displayStats($stats);
        $this->info('✅ Selesai!');

        return 0;
    }

    /**
     * Cek apakah hari kerja
     */
    private function isHariKerja(Carbon $tanggal)
    {
        $namaHari = strtolower($tanggal->locale('id')->dayName);
        $hariKerja = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'];

        return in_array($namaHari, $hariKerja);
    }

    /**
     * Process single karyawan
     */
    private function processKaryawan(Karyawan $karyawan, Carbon $tanggal)
    {
        $tanggalString = $tanggal->toDateString();


        // 1. Cek apakah sudah ada absensi
        $existingAbsensi = Absensi::where('karyawan_id', $karyawan->id)
            ->where('tanggal', $tanggalString)
            ->first();


        // Jika sudah ada record absensi dengan status apapun (hadir/cuti/izin/sakit), skip
        if ($existingAbsensi) {
            // Jika sudah absen masuk
            if ($existingAbsensi->jam_masuk) {
                return 'hadir';
            }

            // Jika sudah ada status kehadiran selain hadir (cuti/izin/sakit)
            if (in_array($existingAbsensi->status_kehadiran, ['cuti', 'izin', 'sakit'])) {
                return $existingAbsensi->status_kehadiran;
            }
        }

        // 2. Cek apakah sedang cuti/izin/sakit yang disetujui
        $pengajuanCuti = $this->absensiService->cekStatusCuti($karyawan->id, $tanggalString);

        if ($pengajuanCuti) {
            Absensi::updateOrCreate(
                [
                    'karyawan_id' => $karyawan->id,
                    'tanggal' => $tanggalString
                ],
                [
                    'status_kehadiran' => $pengajuanCuti->jenis,
                    'keterangan' => 'Auto-generated: ' . ucfirst($pengajuanCuti->jenis) . ' - ' . $pengajuanCuti->alasan
                ]
            );

            return $pengajuanCuti->jenis;
        }

        // 3. Tidak ada absensi dan tidak ada izin = alpha
        // Hanya create jika belum ada record sama sekali
        if (!$existingAbsensi) {
            $jamKerjaId = $this->getJamKerjaId($karyawan, $tanggal);

            Absensi::create([
                'karyawan_id' => $karyawan->id,
                'tanggal' => $tanggalString,
                'jam_kerja_id' => $jamKerjaId,
                'status_kehadiran' => 'alpha',
                // 'lokasi_absensi_id' => 1,
                'keterangan' => 'Auto-generated: Tidak hadir tanpa keterangan'
            ]);
        }

        return 'alpha';
    }

    /**
     * Get jam kerja ID untuk karyawan
     */
    private function getJamKerjaId(Karyawan $karyawan, Carbon $tanggal)
    {
        try {
            $jenisPegawai = $this->absensiService->getJenisPegawaiFromRole($karyawan);
            $jamKerja = $this->absensiService->getJamKerja($jenisPegawai, $tanggal);

            return $jamKerja?->id;
        } catch (\Exception $e) {
            Log::warning('Jam kerja tidak ditemukan', [
                'karyawan_id' => $karyawan->id,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Display statistics
     */
    private function displayStats(array $stats)
    {
        $this->info('📊 Hasil Generate Absensi:');
        $this->table(
            ['Status', 'Jumlah'],
            [
                ['✅ Hadir (sudah absen)', $stats['hadir']],
                ['🏖️  Cuti', $stats['cuti']],
                ['📝 Izin', $stats['izin']],
                ['🤒 Sakit', $stats['sakit']],
                ['❌ Alpha', $stats['alpha']],
                ['⚠️  Error', $stats['error']],
            ]
        );
    }
}
