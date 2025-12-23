<?php

namespace App\Actions\Dashboard\Jadwal;

use App\Models\Jadwal;
use App\Models\JadwalDetail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class JadwalAction
{
    public function execute($request, $isUpdate = false, $jadwalId = null)
    {
        return DB::transaction(function () use ($request, $isUpdate, $jadwalId) {

            // =====================
            // HANDLE FILE UPLOAD
            // =====================
            $fileName = null;

            if ($request->hasFile('jadwal_file')) {
                $file = $request->file('jadwal_file');
                $ext = $file->getClientOriginalExtension();
                $kelasName = $request->input('kelas'); // Idealnya ambil nama kelas dari DB

                $fileName = 'Jadwal_' . Str::slug($kelasName) . '_' . now()->format('YmdHis') . '.' . $ext;

                // Simpan ke storage
                $file->storeAs('public/file/jadwal/', $fileName);
            }

            // =====================
            // CREATE OR UPDATE JADWAL
            // =====================
            if ($isUpdate && $jadwalId) {

                // UPDATE
                $jadwal = Jadwal::findOrFail($jadwalId);

                $updateData = [
                    'tahun_ajaran' => $request->input('tahun_ajaran'),
                    'kelas_id' => $request->input('kelas'),
                    'category_kelas' => $request->input('category_kelas'),
                ];

                // Update file jika ada file baru
                if ($fileName) {
                    // Hapus file lama jika ada
                    if ($jadwal->jadwal && Storage::exists('public/file/jadwal/' . $jadwal->jadwal)) {
                        Storage::delete('public/file/jadwal/' . $jadwal->jadwal);
                    }
                    $updateData['jadwal'] = $fileName;
                }

                $jadwal->update($updateData);

                // Hapus detail jadwal lama
                $jadwal->jadwal_details()->delete();

            } else {

                // CREATE
                $jadwal = Jadwal::create([
                    'tahun_ajaran' => $request->input('tahun_ajaran'),
                    'kelas_id' => $request->input('kelas'),
                    'category_kelas' => $request->input('category_kelas'),
                    'jadwal' => $fileName,
                ]);
            }

            // =====================
            // SIMPAN DETAIL JADWAL
            // =====================
            $jadwalDetails = $request->input('jadwal', []);

            foreach ($jadwalDetails as $detail) {
                // Skip jika data kosong
                if (empty($detail['hari']) || empty($detail['mulai']) || empty($detail['selesai'])) {
                    continue;
                }

                JadwalDetail::create([
                    'jadwal_id' => $jadwal->id,
                    'hari' => ucfirst(strtolower(trim($detail['hari']))),
                    'time_start' => substr($detail['mulai'], 0, 5),
                    'time_end' => substr($detail['selesai'], 0, 5),
                    'pelajaran_id' => !empty($detail['pelajaran_id']) ? $detail['pelajaran_id'] : null,
                    'guru_id' => !empty($detail['guru_id']) ? $detail['guru_id'] : null,
                    'color' => !empty($detail['color']) ? $detail['color'] : 'bg-blue-100',
                ]);
            }

            return $jadwal;
        });
    }
}
