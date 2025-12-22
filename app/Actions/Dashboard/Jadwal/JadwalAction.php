<?php

namespace App\Actions\Dashboard\Jadwal;

use App\Models\Jadwal;
use App\Models\JadwalDetail;
use Illuminate\Support\Str;

class JadwalAction
{
    public function execute($jadwalData)
    {
        /** =====================
         * UPLOAD FILE (OPTIONAL)
         * ===================== */
        $pictureName = null;

        if ($jadwalData->jadwal_file) {
            $file = $jadwalData->file('jadwal_file');
            $ext  = $file->getClientOriginalExtension();

            $uploadPath = public_path('storage/file/jadwal/');
            $pictureName = 'Jadwal_' . Str::slug($jadwalData->kelas) . '_' . now()->format('YmdHis') . '.' . $ext;

            $file->move($uploadPath, $pictureName);
        }

        /** =====================
         * CREATE / UPDATE JADWAL
         * ===================== */
        if (!empty($jadwalData->slug)) {
            // UPDATE
            $jadwal = Jadwal::findOrFail($jadwalData->slug);

            $jadwal->update([
                'tahun_ajaran'   => $jadwalData->tahun_ajaran,
                'kelas'          => $jadwalData->kelas, // sesuaikan nama kolom
                'category_kelas' => $jadwalData->category_kelas,
                'jadwal'         => $pictureName ?? $jadwal->jadwal,
            ]);

            // hapus detail lama
            $jadwal->jadwal_details()->delete();

        } else {
            // CREATE
            $jadwal = Jadwal::create([
                'tahun_ajaran'   => $jadwalData->tahun_ajaran,
                'kelas_id'          => $jadwalData->kelas,
                'category_kelas' => $jadwalData->category_kelas,
                'jadwal'         => $pictureName,
                'slug'           => Str::slug($jadwalData->kelas) . '-' . Str::random(5),
            ]);
        }

        /** =====================
         * SIMPAN DETAIL JADWAL
         * ===================== */
        if (!empty($jadwalData->jadwal)) {
            foreach ($jadwalData->jadwal as $detail) {
                JadwalDetail::create([
                    'jadwal_id'    => $jadwal->id,
                    'hari'         => $detail['hari'],
                    'time_start'   => $detail['mulai'],
                    'time_end'     => $detail['selesai'],
                    'pelajaran_id' => $detail['pelajaran_id'],
                    'guru_id'      => $detail['guru_id'],
                    'color'        => $detail['color'] ?? '#3b82f6',
                ]);
            }
        }

        return $jadwal;
    }
}
