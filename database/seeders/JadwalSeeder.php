<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Jadwal;
use App\Models\Pelajaran;
use Illuminate\Support\Str;
use App\Models\JadwalDetail;
use Illuminate\Database\Seeder;

class JadwalSeeder extends Seeder
{
    public function run(): void
    {
        // ====================================
        // STRUKTUR DATA: PER KATEGORI KELAS
        // Format: 'KATEGORI' => [jadwal detail]
        // ====================================
        $scheduleByCategory = [
            'BAGHDAD' => [
                [
                    'pelajaran' => 'Bahasa Indonesia',
                    'hari' => 'Senin',
                    'time_start' => '07:00',
                    'time_end' => '08:30',
                    'color' => 'bg-blue-100',
                ],
                [
                    'pelajaran' => 'Sholat Duha',
                    'hari' => 'Senin',
                    'time_start' => '08:30',
                    'time_end' => '09:00',
                    'color' => 'bg-green-100',
                ],
                [
                    'pelajaran' => 'Matematika',
                    'hari' => 'Senin',
                    'time_start' => '09:00',
                    'time_end' => '10:30',
                    'color' => 'bg-yellow-100',
                ],
                [
                    'pelajaran' => 'IPA',
                    'hari' => 'Selasa',
                    'time_start' => '07:00',
                    'time_end' => '08:30',
                    'color' => 'bg-red-100',
                ],
                [
                    'pelajaran' => 'IPS',
                    'hari' => 'Selasa',
                    'time_start' => '08:30',
                    'time_end' => '10:00',
                    'color' => 'bg-lime-100',
                ],
                [
                    'pelajaran' => 'Bahasa Inggris',
                    'hari' => 'Rabu',
                    'time_start' => '07:00',
                    'time_end' => '08:30',
                    'color' => 'bg-cyan-100',
                ],
                [
                    'pelajaran' => 'Bahasa Arab',
                    'hari' => 'Rabu',
                    'time_start' => '08:30',
                    'time_end' => '10:00',
                    'color' => 'bg-violet-100',
                ],
                [
                    'pelajaran' => 'Seni Rupa',
                    'hari' => 'Kamis',
                    'time_start' => '07:00',
                    'time_end' => '08:30',
                    'color' => 'bg-rose-100',
                ],
                [
                    'pelajaran' => 'Olahraga',
                    'hari' => 'Kamis',
                    'time_start' => '08:30',
                    'time_end' => '10:00',
                    'color' => 'bg-emerald-100',
                ],
                [
                    'pelajaran' => 'Penjaskes',
                    'hari' => 'Jumat',
                    'time_start' => '07:00',
                    'time_end' => '08:30',
                    'color' => 'bg-orange-100',
                ],
                [
                    'pelajaran' => 'Teknologi',
                    'hari' => 'Jumat',
                    'time_start' => '08:30',
                    'time_end' => '10:00',
                    'color' => 'bg-gray-100',
                ],
            ],
            'ISTANBUL' => [
                [
                    'pelajaran' => 'Bahasa Indonesia',
                    'hari' => 'Senin',
                    'time_start' => '07:00',
                    'time_end' => '08:30',
                    'color' => 'bg-blue-100',
                ],
                [
                    'pelajaran' => 'Sholat Duha',
                    'hari' => 'Senin',
                    'time_start' => '08:30',
                    'time_end' => '09:00',
                    'color' => 'bg-green-100',
                ],
                [
                    'pelajaran' => 'Matematika',
                    'hari' => 'Senin',
                    'time_start' => '09:00',
                    'time_end' => '10:30',
                    'color' => 'bg-yellow-100',
                ],
                [
                    'pelajaran' => 'IPA',
                    'hari' => 'Selasa',
                    'time_start' => '07:00',
                    'time_end' => '08:30',
                    'color' => 'bg-red-100',
                ],
                [
                    'pelajaran' => 'IPS',
                    'hari' => 'Selasa',
                    'time_start' => '08:30',
                    'time_end' => '10:00',
                    'color' => 'bg-lime-100',
                ],
                [
                    'pelajaran' => 'Bahasa Inggris',
                    'hari' => 'Rabu',
                    'time_start' => '07:00',
                    'time_end' => '08:30',
                    'color' => 'bg-cyan-100',
                ],
                [
                    'pelajaran' => 'Bahasa Arab',
                    'hari' => 'Rabu',
                    'time_start' => '08:30',
                    'time_end' => '10:00',
                    'color' => 'bg-violet-100',
                ],
                [
                    'pelajaran' => 'Sejarah',
                    'hari' => 'Kamis',
                    'time_start' => '07:00',
                    'time_end' => '08:30',
                    'color' => 'bg-amber-100',
                ],
                [
                    'pelajaran' => 'Geografi',
                    'hari' => 'Kamis',
                    'time_start' => '08:30',
                    'time_end' => '10:00',
                    'color' => 'bg-slate-100',
                ],
                [
                    'pelajaran' => 'Olahraga',
                    'hari' => 'Jumat',
                    'time_start' => '07:00',
                    'time_end' => '08:30',
                    'color' => 'bg-emerald-100',
                ],
                [
                    'pelajaran' => 'Seni Musik',
                    'hari' => 'Jumat',
                    'time_start' => '08:30',
                    'time_end' => '10:00',
                    'color' => 'bg-fuchsia-100',
                ],
            ],
            'Madinah' => [
                [
                    'pelajaran' => 'Al-Qur\'an',
                    'hari' => 'Senin',
                    'time_start' => '07:00',
                    'time_end' => '08:00',
                    'color' => 'bg-indigo-100',
                ],
                [
                    'pelajaran' => 'Tajweed',
                    'hari' => 'Senin',
                    'time_start' => '08:00',
                    'time_end' => '09:00',
                    'color' => 'bg-blue-100',
                ],
                [
                    'pelajaran' => 'Hadits',
                    'hari' => 'Selasa',
                    'time_start' => '07:00',
                    'time_end' => '08:30',
                    'color' => 'bg-purple-100',
                ],
                [
                    'pelajaran' => 'Fiqih',
                    'hari' => 'Selasa',
                    'time_start' => '08:30',
                    'time_end' => '09:30',
                    'color' => 'bg-pink-100',
                ],
                [
                    'pelajaran' => 'Tauhid',
                    'hari' => 'Rabu',
                    'time_start' => '07:00',
                    'time_end' => '08:30',
                    'color' => 'bg-orange-100',
                ],
                [
                    'pelajaran' => 'Akhlak',
                    'hari' => 'Rabu',
                    'time_start' => '08:30',
                    'time_end' => '09:30',
                    'color' => 'bg-rose-100',
                ],
                [
                    'pelajaran' => 'Bahasa Arab',
                    'hari' => 'Kamis',
                    'time_start' => '07:00',
                    'time_end' => '08:30',
                    'color' => 'bg-violet-100',
                ],
                [
                    'pelajaran' => 'Sastra Arab',
                    'hari' => 'Kamis',
                    'time_start' => '08:30',
                    'time_end' => '09:30',
                    'color' => 'bg-purple-100',
                ],
                [
                    'pelajaran' => 'Sirah Nabawi',
                    'hari' => 'Jumat',
                    'time_start' => '07:00',
                    'time_end' => '08:30',
                    'color' => 'bg-green-200',
                ],
                [
                    'pelajaran' => 'Diskusi Agama',
                    'hari' => 'Jumat',
                    'time_start' => '08:30',
                    'time_end' => '09:30',
                    'color' => 'bg-teal-100',
                ],
            ],
            'Mekkah' => [
                // START Senin
                [
                    'pelajaran' => 'Persiapan',
                    'hari' => 'Senin',
                    'time_start' => '07:15',
                    'time_end' => '07:30',
                    'color' => 'bg-pink-100',
                ],
                [
                    'pelajaran' => 'Upacara',
                    'hari' => 'Senin',
                    'time_start' => '07:30',
                    'time_end' => '08:15',
                    'color' => 'bg-indigo-100',
                ],
                [
                    'pelajaran' => 'Sholat Duha',
                    'hari' => 'Senin',
                    'time_start' => '08:15',
                    'time_end' => '10:20',
                    'color' => 'bg-orange-100',
                ],
                [
                    'pelajaran' => 'Muroja\'ah',
                    'hari' => 'Senin',
                    'time_start' => '08:15',
                    'time_end' => '10:20',
                    'color' => 'bg-purple-100',
                ],
                [
                    'pelajaran' => 'Tilawati',
                    'hari' => 'Senin',
                    'time_start' => '08:15',
                    'time_end' => '10:20',
                    'color' => 'bg-violet-100',
                ],
                [
                    'pelajaran' => 'Tahfiz',
                    'hari' => 'Senin',
                    'time_start' => '08:15',
                    'time_end' => '10:20',
                    'color' => 'bg-rose-100',
                ],
                [
                    'pelajaran' => 'Imla',
                    'hari' => 'Senin',
                    'time_start' => '08:15',
                    'time_end' => '10:20',
                    'color' => 'bg-green-200',
                ],
                [
                    'pelajaran' => 'Instirahat',
                    'hari' => 'Senin',
                    'time_start' => '10:20',
                    'time_end' => '10:50',
                    'color' => 'bg-green-200',
                ],
                [
                    'pelajaran' => 'AL Islam',
                    'hari' => 'Senin',
                    'time_start' => '10:50',
                    'time_end' => '11:20',
                    'color' => 'bg-green-200',
                ],
                [
                    'pelajaran' => 'AL Islam',
                    'hari' => 'Senin',
                    'time_start' => '11:20',
                    'time_end' => '11:50',
                    'color' => 'bg-green-200',
                ],
                [
                    'pelajaran' => 'AL Islam',
                    'hari' => 'Senin',
                    'time_start' => '11:50',
                    'time_end' => '12:30',
                    'color' => 'bg-green-200',
                ],
                [
                    'pelajaran' => 'Sholat Dzuhur',
                    'hari' => 'Senin',
                    'time_start' => '12:30',
                    'time_end' => '13:00',
                    'color' => 'bg-green-200',
                ],
                [
                    'pelajaran' => 'Doa Harian Dan Pulang',
                    'hari' => 'Senin',
                    'time_start' => '13:00',
                    'time_end' => '13:00',
                    'color' => 'bg-green-200',
                ],
                // END Senin
                [
                    'pelajaran' => 'Sholat Duha',
                    'hari' => 'Selasa',
                    'time_start' => '07:15',
                    'time_end' => '07:30',
                    'color' => 'bg-blue-100',
                ],
                [
                    'pelajaran' => 'Sastra Arab',
                    'hari' => 'Jumat',
                    'time_start' => '07:00',
                    'time_end' => '08:30',
                    'color' => 'bg-purple-100',
                ],
                [
                    'pelajaran' => 'Diskusi Agama',
                    'hari' => 'Jumat',
                    'time_start' => '08:30',
                    'time_end' => '09:30',
                    'color' => 'bg-teal-100',
                ],
            ],
            'Jeddah' => [
                [
                    'pelajaran' => 'Bahasa Inggris',
                    'hari' => 'Senin',
                    'time_start' => '08:00',
                    'time_end' => '09:30',
                    'color' => 'bg-cyan-100',
                ],
                [
                    'pelajaran' => 'Conversation',
                    'hari' => 'Kamis',
                    'time_start' => '09:00',
                    'time_end' => '10:00',
                    'color' => 'bg-teal-100',
                ],
            ],
            'ALEXANDRIA' => [
                [
                    'pelajaran' => 'IPS',
                    'hari' => 'Senin',
                    'time_start' => '07:00',
                    'time_end' => '08:30',
                    'color' => 'bg-lime-100',
                ],
            ],
            'KAIRO' => [
                [
                    'pelajaran' => 'Sejarah',
                    'hari' => 'Selasa',
                    'time_start' => '07:00',
                    'time_end' => '08:30',
                    'color' => 'bg-amber-100',
                ],
            ],
            'YERUSSALEM' => [
                [
                    'pelajaran' => 'Geografi',
                    'hari' => 'Rabu',
                    'time_start' => '07:00',
                    'time_end' => '08:30',
                    'color' => 'bg-slate-100',
                ],
            ],
            'ANKARA' => [
                [
                    'pelajaran' => 'Bahasa Arab',
                    'hari' => 'Kamis',
                    'time_start' => '08:00',
                    'time_end' => '09:30',
                    'color' => 'bg-violet-100',
                ],
            ],
            'GRANADA' => [
                [
                    'pelajaran' => 'Seni Rupa',
                    'hari' => 'Jumat',
                    'time_start' => '08:00',
                    'time_end' => '09:30',
                    'color' => 'bg-rose-100',
                ],
            ],
            'CORDOBA' => [
                [
                    'pelajaran' => 'Musik',
                    'hari' => 'Senin',
                    'time_start' => '10:00',
                    'time_end' => '11:30',
                    'color' => 'bg-fuchsia-100',
                ],
            ],
            'DAMASKUS' => [
                [
                    'pelajaran' => 'Olahraga',
                    'hari' => 'Selasa',
                    'time_start' => '10:00',
                    'time_end' => '11:30',
                    'color' => 'bg-emerald-100',
                ],
            ],
            'AL QUDS' => [
                [
                    'pelajaran' => 'Sains',
                    'hari' => 'Senin',
                    'time_start' => '07:00',
                    'time_end' => '08:30',
                    'color' => 'bg-green-100',
                ],
            ],
            'ANDALUSIA' => [
                [
                    'pelajaran' => 'Teknologi',
                    'hari' => 'Senin',
                    'time_start' => '07:00',
                    'time_end' => '08:30',
                    'color' => 'bg-gray-100',
                ],
            ],
            'AMMAN' => [
                [
                    'pelajaran' => 'Bahasa Indonesia',
                    'hari' => 'Senin',
                    'time_start' => '07:00',
                    'time_end' => '08:30',
                    'color' => 'bg-blue-100',
                ],
            ],
            'BUKHARA' => [
                [
                    'pelajaran' => 'Sastra',
                    'hari' => 'Senin',
                    'time_start' => '07:00',
                    'time_end' => '08:30',
                    'color' => 'bg-yellow-100',
                ],
            ],
            'ABU DHABI' => [
                [
                    'pelajaran' => 'Teknologi Informasi',
                    'hari' => 'Senin',
                    'time_start' => '08:00',
                    'time_end' => '09:30',
                    'color' => 'bg-sky-100',
                ],
            ],
            'GAZA' => [
                [
                    'pelajaran' => 'Perdamaian',
                    'hari' => 'Selasa',
                    'time_start' => '08:00',
                    'time_end' => '09:00',
                    'color' => 'bg-green-200',
                ],
            ],
        ];

        // ====================================
        // AMBIL SEMUA KELAS (EXCLUDE LULUS)
        // ====================================
        $kelas = Kelas::where('name', '!=', 'Lulus')->orderBy('name', 'asc')->get();

        // ====================================
        // LOOP PER KELAS
        // ====================================
        foreach($kelas as $kel) {
            // Decode category_kelas (JSON to array)
            $categories = json_decode($kel->category_kelas, true) ?? [];

            // Jika kategori kosong, skip
            if (empty($categories)) {
                $this->command->warn("Kelas '{$kel->name}' tidak memiliki kategori. Skip!");
                continue;
            }

            // ====================================
            // LOOP PER KATEGORI DALAM KELAS
            // ====================================
            foreach ($categories as $category) {
                // Cek apakah kategori ada di schedule
                if (!array_key_exists($category, $scheduleByCategory)) {
                    $this->command->warn("Kategori '{$category}' di kelas '{$kel->name}' tidak ditemukan di scheduleByCategory. Skip!");
                    continue;
                }

                // Buat Jadwal Master (per kelas + kategori)
                $jadwal = Jadwal::firstOrCreate(
                    [
                        'tahun_ajaran' => '2025/2026',
                        'kelas_id' => $kel->id,
                        'category_kelas' => $category,
                    ],
                    [
                        'id' => Str::uuid(),
                        'jadwal' => "Jadwal Tahun Ajaran 2025/2026 - {$category}",
                    ]
                );

                // ====================================
                // AMBIL SCHEDULE BERDASAR KATEGORI
                // ====================================
                $scheduleData = $scheduleByCategory[$category];

                // ====================================
                // LOOP PER DETAIL JADWAL
                // ====================================
                foreach ($scheduleData as $item) {
                    // Cari atau buat Pelajaran
                    $pelajaran = Pelajaran::where('name', $item['pelajaran'])->first();
                    if (!$pelajaran) {
                        $pelajaran = Pelajaran::create([
                            'id' => Str::uuid(),
                            'name' => $item['pelajaran'],
                            'slug' => Str::slug($item['pelajaran']),
                        ]);
                    }

                    // Buat JadwalDetail
                    JadwalDetail::firstOrCreate(
                        [
                            'jadwal_id' => $jadwal->id,
                            'hari' => $item['hari'],
                            'time_start' => $item['time_start'],
                            'pelajaran_id' => $pelajaran->id,
                        ],
                        [
                            'id' => Str::uuid(),
                            'time_end' => $item['time_end'],
                            'color' => $item['color'],
                        ]
                    );
                }

                $this->command->info("✓ Jadwal untuk kelas '{$kel->name}' - kategori '{$category}' berhasil dibuat.");
            }
        }

        $this->command->info('✓ Seeder Jadwal & JadwalDetail selesai!');
    }
}
