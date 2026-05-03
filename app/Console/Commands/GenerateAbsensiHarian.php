<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Karyawan;
use App\Models\Absensi;
use App\Models\JamKerja;
use App\Services\AbsensiService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Yasumi\Yasumi;

class GenerateAbsensiHarian extends Command
{
    protected $signature = 'absensi:generate-harian {--date= : Tanggal untuk generate absensi (Y-m-d)}';
    protected $description = 'Generate status absensi harian untuk semua karyawan (alpha jika tidak absen dan tidak cuti)';

    protected $absensiService;

    /** Cache holidays per tahun supaya tidak re-init tiap iterasi karyawan */
    private array $holidayCache = [];

    public function __construct(AbsensiService $absensiService)
    {
        parent::__construct();
        $this->absensiService = $absensiService;
    }

    public function handle()
    {
        $this->info('🚀 Memulai generate absensi harian...');

        $tanggalString = $this->option('date') ?? Carbon::now()->format('Y-m-d');
        $tanggal       = Carbon::createFromFormat('Y-m-d', $tanggalString);

        $karyawans = Karyawan::with(['user.roles'])->get();
        $this->info("👥 Total karyawan aktif: {$karyawans->count()}");

        $stats = [
            'hadir'          => 0,
            'cuti'           => 0,
            'izin'           => 0,
            'sakit'          => 0,
            'alpha'          => 0,
            'libur'          => 0,
            'libur_nasional' => 0,
            'error'          => 0,
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
                    'tanggal'     => $tanggal->toDateString(),
                    'error'       => $e->getMessage(),
                ]);
                $stats['error']++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->displayStats($stats);
        $this->info('✅ Selesai!');

        return 0;
    }

    // =========================================================================
    // PROCESS PER KARYAWAN
    // =========================================================================

    private function processKaryawan(Karyawan $karyawan, Carbon $tanggal)
    {
        $tanggalString = $tanggal->toDateString();
        $isHariKerja   = $this->isHariKerja($tanggal);
        $namaLiburNas  = $this->getNamaLiburNasional($tanggal); // null = bukan libnas

        // ------------------------------------------------------------------
        // 1. Cek absensi yang sudah ada — jangan override
        // ------------------------------------------------------------------
        $existingAbsensi = Absensi::where('karyawan_id', $karyawan->id)
            ->where('tanggal', $tanggalString)
            ->first();

        if ($existingAbsensi) {
            if (in_array($existingAbsensi->status_kehadiran, ['cuti', 'izin', 'sakit', 'libur', 'libur_nasional'])) {
                return $existingAbsensi->status_kehadiran;
            }

            // Sudah absen masuk, jangan disentuh
            if ($existingAbsensi->jam_masuk) {
                return 'hadir';
            }
        }

        // ------------------------------------------------------------------
        // 2. Libur nasional — deteksi otomatis via Yasumi, prioritas tertinggi
        //    (Lebaran/Nyepi bisa jatuh di hari kerja DB, tetap harus libur)
        //    jam_masuk & jam_pulang dibiarkan NULL agar karyawan tetap bisa
        //    absen jika ternyata masuk kerja di hari libur nasional
        // ------------------------------------------------------------------
        if ($namaLiburNas !== null) {
            if (!$existingAbsensi) {
                Absensi::create([
                    'karyawan_id'       => $karyawan->id,
                    'tanggal'           => $tanggalString,
                    'jam_kerja_id'      => $this->getJamKerjaId($karyawan, $tanggal),
                    'status_kehadiran'  => 'libur',
                    'jam_masuk'         => null, // NULL = masih bisa absen jika masuk
                    'jam_pulang'        => null,
                    'lokasi_absensi_id' => null,
                    'keterangan'        => 'Auto-generated: Libur Nasional — ' . $namaLiburNas,
                ]);
            }
            return 'libur_nasional';
        }

        // ------------------------------------------------------------------
        // 3. Cuti / Izin / Sakit yang disetujui (hanya jika hari kerja)
        // ------------------------------------------------------------------
        if ($isHariKerja) {
            $pengajuanCuti = $this->absensiService->cekStatusCuti($karyawan->id, $tanggalString);

            if ($pengajuanCuti) {
                Absensi::updateOrCreate(
                    [
                        'karyawan_id' => $karyawan->id,
                        'tanggal'     => $tanggalString,
                    ],
                    [
                        'status_kehadiran' => $pengajuanCuti->jenis,
                        'keterangan'       => 'Auto-generated: ' . ucfirst($pengajuanCuti->jenis) . ' - ' . $pengajuanCuti->alasan,
                    ]
                );

                return $pengajuanCuti->jenis;
            }
        }

        // ------------------------------------------------------------------
        // 4. Bukan hari kerja (Sabtu/Minggu sesuai konfigurasi DB)
        //    jam NULL agar tetap bisa absen jika ada yang masuk
        // ------------------------------------------------------------------
        if (!$isHariKerja) {
            if (!$existingAbsensi) {
                Absensi::create([
                    'karyawan_id'       => $karyawan->id,
                    'tanggal'           => $tanggalString,
                    'jam_kerja_id'      => $this->getJamKerjaId($karyawan, $tanggal),
                    'status_kehadiran'  => 'libur',
                    'jam_masuk'         => null, // NULL = masih bisa absen jika masuk
                    'jam_pulang'        => null,
                    'lokasi_absensi_id' => null,
                    'keterangan'        => 'Auto-generated: Hari libur (bukan hari kerja)',
                ]);
            }

            return 'libur';
        }

        // ------------------------------------------------------------------
        // 5. Hari kerja, tidak hadir, tidak ada keterangan = alpha
        //    jam NULL agar jika karyawan telat absen masih bisa masuk
        // ------------------------------------------------------------------
        if (!$existingAbsensi) {
            Absensi::create([
                'karyawan_id'       => $karyawan->id,
                'tanggal'           => $tanggalString,
                'jam_kerja_id'      => $this->getJamKerjaId($karyawan, $tanggal),
                'status_kehadiran'  => 'alpha',
                'jam_masuk'         => null, // NULL = masih bisa absen meskipun sudah alpha
                'jam_pulang'        => null,
                'lokasi_absensi_id' => null,
                'keterangan'        => 'Auto-generated: Tidak hadir tanpa keterangan',
            ]);
        }

        return 'alpha';
    }

    // =========================================================================
    // LIBUR NASIONAL — Yasumi (otomatis, tanpa mapping manual)
    // =========================================================================

    private function getNamaLiburNasional(Carbon $tanggal): ?string
    {
        try {
            $tahun = (int) $tanggal->year;

            if (!isset($this->holidayCache[$tahun])) {
                $this->holidayCache[$tahun] = Yasumi::create('id', $tahun);
            }

            $provider = $this->holidayCache[$tahun];

            if (!$provider->isHoliday($tanggal->toDateTimeImmutable())) {
                return null;
            }

            foreach ($provider->getHolidays() as $holiday) {
                if ($holiday->format('Y-m-d') === $tanggal->format('Y-m-d')) {
                    return $holiday->getName();
                }
            }

            return 'Hari Libur Nasional';
        } catch (\Exception $e) {
            Log::warning('Yasumi provider error, skipping holiday detection', [
                'tanggal' => $tanggal->toDateString(),
                'error'   => $e->getMessage(),
            ]);
            return null;
        }
    }

    // =========================================================================
    // HARI KERJA — dari tabel JamKerja DB
    // =========================================================================

    private function isHariKerja(Carbon $tanggal): bool
    {
        $namaHari = strtolower($tanggal->locale('id')->dayName);

        return JamKerja::where('hari', $namaHari)
            ->where('is_hari_kerja', true)
            ->exists();
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    private function getJamKerjaModel(Karyawan $karyawan, Carbon $tanggal)
    {
        try {
            $jenisPegawai = $this->absensiService->getJenisPegawaiFromRole($karyawan);
            return $this->absensiService->getJamKerja($jenisPegawai, $tanggal);
        } catch (\Exception $e) {
            Log::warning('Jam kerja tidak ditemukan untuk auto-set jam pulang', [
                'karyawan_id' => $karyawan->id,
                'error'       => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function getJamKerjaId(Karyawan $karyawan, Carbon $tanggal)
    {
        try {
            $jenisPegawai = $this->absensiService->getJenisPegawaiFromRole($karyawan);
            $jamKerja     = $this->absensiService->getJamKerja($jenisPegawai, $tanggal);
            return $jamKerja?->id;
        } catch (\Exception $e) {
            Log::warning('Jam kerja tidak ditemukan', [
                'karyawan_id' => $karyawan->id,
                'error'       => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function displayStats(array $stats): void
    {
        $this->info('📊 Hasil Generate Absensi:');
        $this->table(
            ['Status', 'Jumlah'],
            [
                ['✅ Hadir (sudah absen)',        $stats['hadir']],
                ['🏖️  Cuti',                      $stats['cuti']],
                ['📝 Izin',                       $stats['izin']],
                ['🤒 Sakit',                      $stats['sakit']],
                ['🎌 Libur Nasional (otomatis)',  $stats['libur_nasional']],
                ['🛌 Libur (bukan hari kerja)',   $stats['libur']],
                ['❌ Alpha',                      $stats['alpha']],
                ['⚠️  Error',                     $stats['error']],
            ]
        );
    }
}
