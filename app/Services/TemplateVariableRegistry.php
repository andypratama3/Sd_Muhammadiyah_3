<?php

namespace App\Services;

use App\Models\Siswa;

class TemplateVariableRegistry
{
    /**
     * Semua variabel yang tersedia, dikelompokkan berdasarkan kategori.
     * Setiap variabel punya: key, label, icon, db_field (nullable), is_reserved.
     *
     * @return array<string, array{title: string, icon: string, color: string, variables: array}>
     */
    public static function getGrouped(): array
    {
        return [

            // ─────────────────────────────────────────────────
            // DATA SISWA — akan di-auto-fill saat generate dari DB
            // ─────────────────────────────────────────────────
            'siswa' => [
                'title' => 'Data Siswa',
                'icon'  => 'bx bx-user',
                'color' => '#696cff',
                'variables' => [
                    ['key' => 'nama_siswa',      'label' => 'Nama Siswa',      'db_field' => 'name'],
                    ['key' => 'nisn',             'label' => 'NISN',            'db_field' => 'nisn'],
                    ['key' => 'jenis_kelamin',    'label' => 'Jenis Kelamin',   'db_field' => 'jk'],
                    ['key' => 'tempat_lahir',     'label' => 'Tempat Lahir',    'db_field' => 'tmpt_lahir'],
                    ['key' => 'tanggal_lahir',    'label' => 'Tanggal Lahir',   'db_field' => 'tgl_lahir'],
                    ['key' => 'agama',            'label' => 'Agama',           'db_field' => 'agama'],
                    ['key' => 'nama_ayah',        'label' => 'Nama Ayah',       'db_field' => 'nama_ayah'],
                    ['key' => 'nama_ibu',         'label' => 'Nama Ibu',        'db_field' => 'nama_ibu'],
                    ['key' => 'pekerjaan_ayah',   'label' => 'Pekerjaan Ayah',  'db_field' => 'pekerjaan_ayah'],
                    ['key' => 'pekerjaan_ibu',    'label' => 'Pekerjaan Ibu',   'db_field' => 'pekerjaan_ibu'],
                    ['key' => 'alamat_siswa',     'label' => 'Alamat',          'db_field' => 'nama_jalan'],
                    ['key' => 'no_hp',            'label' => 'No. HP',          'db_field' => 'no_hp'],
                    ['key' => 'nama_wali',        'label' => 'Nama Wali',       'db_field' => 'nama_wali'],
                ],
            ],

            // ─────────────────────────────────────────────────
            // DATA SEKOLAH — isian statis / default
            // ─────────────────────────────────────────────────
            'sekolah' => [
                'title' => 'Data Sekolah',
                'icon'  => 'bx bx-buildings',
                'color' => '#03c3ec',
                'variables' => [
                    ['key' => 'nama_sekolah',   'label' => 'Nama Sekolah',     'db_field' => null],
                    ['key' => 'alamat_sekolah', 'label' => 'Alamat Sekolah',   'db_field' => null],
                    ['key' => 'kepala_sekolah', 'label' => 'Kepala Sekolah',   'db_field' => null],
                    ['key' => 'nip',            'label' => 'NIP',              'db_field' => null],
                    ['key' => 'tahun_ajaran',   'label' => 'Tahun Ajaran',     'db_field' => null],
                    ['key' => 'semester',        'label' => 'Semester',         'db_field' => null],
                    ['key' => 'wali_kelas',     'label' => 'Wali Kelas',       'db_field' => null],
                ],
            ],

            // ─────────────────────────────────────────────────
            // DATA SURAT — isian per-dokumen
            // ─────────────────────────────────────────────────
            'surat' => [
                'title' => 'Data Surat',
                'icon'  => 'bx bx-envelope',
                'color' => '#ffab00',
                'variables' => [
                    ['key' => 'nomor_surat',  'label' => 'Nomor Surat',    'db_field' => null],
                    ['key' => 'tanggal',      'label' => 'Tanggal',        'db_field' => null],
                    ['key' => 'perihal',      'label' => 'Perihal',        'db_field' => null],
                    ['key' => 'keterangan',   'label' => 'Keterangan',     'db_field' => null],
                    ['key' => 'isi',          'label' => 'Isi Surat',      'db_field' => null],
                    ['key' => 'tujuan',       'label' => 'Tujuan',         'db_field' => null],
                    ['key' => 'tembusan',     'label' => 'Tembusan',       'db_field' => null],
                ],
            ],

            // ─────────────────────────────────────────────────
            // NILAI & PRESTASI
            // ─────────────────────────────────────────────────
            'nilai' => [
                'title' => 'Nilai & Prestasi',
                'icon'  => 'bx bx-trophy',
                'color' => '#71dd37',
                'variables' => [
                    ['key' => 'kelas',        'label' => 'Kelas',             'db_field' => null],
                    ['key' => 'nilai_rata',   'label' => 'Nilai Rata-rata',   'db_field' => null],
                    ['key' => 'peringkat',    'label' => 'Peringkat',         'db_field' => null],
                    ['key' => 'predikat',     'label' => 'Predikat',          'db_field' => null],
                    ['key' => 'catatan',      'label' => 'Catatan',           'db_field' => null],
                ],
            ],

            // ─────────────────────────────────────────────────
            // SISTEM — reserved variables (logo, barcode)
            // ─────────────────────────────────────────────────
            'sistem' => [
                'title' => 'Sistem (Otomatis)',
                'icon'  => 'bx bx-cog',
                'color' => '#8592a3',
                'variables' => [
                    ['key' => 'logo',               'label' => 'Logo Sekolah',       'db_field' => null, 'reserved' => true],
                    ['key' => 'barcode_signature',   'label' => 'QR Code Verifikasi', 'db_field' => null, 'reserved' => true],
                ],
            ],
        ];
    }

    /**
     * Flat list — semua variabel key saja (tanpa reserved).
     *
     * @return array<string>
     */
    public static function allKeys(): array
    {
        $keys = [];
        foreach (self::getGrouped() as $group) {
            foreach ($group['variables'] as $var) {
                if (! ($var['reserved'] ?? false)) {
                    $keys[] = $var['key'];
                }
            }
        }
        return $keys;
    }

    /**
     * Map data siswa ke template variables.
     */
    public static function mapSiswaData(Siswa $siswa): array
    {
        $data = [];
        $group = self::getGrouped()['siswa']['variables'];

        foreach ($group as $var) {
            if ($var['db_field']) {
                $value = $siswa->{$var['db_field']};
                // Format tanggal lahir
                if ($var['key'] === 'tanggal_lahir' && $value) {
                    try {
                        $value = \Carbon\Carbon::parse($value)->translatedFormat('d F Y');
                    } catch (\Exception $e) {}
                }
                // Format jenis kelamin
                if ($var['key'] === 'jenis_kelamin' && $value) {
                    $value = strtoupper($value) === 'L' ? 'Laki-laki' : 'Perempuan';
                }
                $data[$var['key']] = $value ?? '';
            }
        }

        return $data;
    }

    /**
     * Default data sekolah.
     */
    public static function getSekolahDefaults(): array
    {
        return [
            'nama_sekolah'   => 'SD Muhammadiyah 3 Samarinda Seberang',
            'alamat_sekolah' => 'Jalan Dato Iba, Kel. Sungai Keledang, Samarinda Seberang 75131',
            'tahun_ajaran'   => self::currentTahunAjaran(),
            'semester'       => self::currentSemester(),
            'tanggal'        => now()->translatedFormat('d F Y'),
        ];
    }

    private static function currentTahunAjaran(): string
    {
        $y = (int) date('Y');
        $m = (int) date('n');
        return $m >= 7 ? "{$y}/" . ($y + 1) : ($y - 1) . "/{$y}";
    }

    private static function currentSemester(): string
    {
        return (int) date('n') >= 7 ? 'Ganjil' : 'Genap';
    }
}
