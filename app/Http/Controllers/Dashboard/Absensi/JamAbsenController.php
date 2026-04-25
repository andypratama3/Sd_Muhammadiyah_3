<?php

namespace App\Http\Controllers\Dashboard\Absensi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JamKerja;

class JamAbsenController extends Controller
{
    /**
     * List jam kerja
     */
    public function index()
    {
        $jamKerja = JamKerja::orderBy('jenis_pegawai')
            ->orderBy('is_default', 'desc')
            ->orderBy('hari')
            ->paginate(20);

        return view('dashboard.absensis.jam_kerja.index', compact('jamKerja'));
    }

    /**
     * Form tambah jam kerja
     */
    public function create()
    {
        return view('dashboard.absensis.jam_kerja.create');
    }

    /**
     * Simpan jam kerja
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_shift'    => 'required|string|max:100',
            'jenis_pegawai' => 'required|string|max:100',

            // ✅ Terima H:i (browser lama) dan H:i:s (step="1")
            'jam_masuk'     => ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'batas_masuk'   => ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'jam_pulang'    => ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'batas_pulang'  => ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],

            'hari'          => 'required|in:senin,selasa,rabu,kamis,jumat,sabtu,minggu',
            'is_default'    => 'nullable|boolean',
            'is_hari_kerja'  => 'nullable|boolean',
        ], [
            'jam_masuk.regex'    => 'Format jam masuk tidak valid (contoh: 07:00:00)',
            'batas_masuk.regex'  => 'Format batas masuk tidak valid (contoh: 08:00:00)',
            'jam_pulang.regex'   => 'Format jam pulang tidak valid (contoh: 15:00:00)',
            'batas_pulang.regex' => 'Format batas pulang tidak valid (contoh: 16:00:00)',
        ]);

        // ✅ Normalisasi semua waktu ke format H:i:s agar konsisten di DB
        $validated['jam_masuk']   = $this->normalizeTime($validated['jam_masuk']);
        $validated['batas_masuk'] = $this->normalizeTime($validated['batas_masuk']);
        $validated['jam_pulang']  = $this->normalizeTime($validated['jam_pulang']);
        $validated['batas_pulang'] = $this->normalizeTime($validated['batas_pulang']);

        // Jika default = true, nonaktifkan default lain pada jenis pegawai yang sama
        if (!empty($validated['is_default'])) {
            JamKerja::where('jenis_pegawai', $validated['jenis_pegawai'])
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        JamKerja::create($validated);

        return redirect()
            ->route('dashboard.jam.absen.index')
            ->with('success', 'Jam kerja berhasil ditambahkan');
    }

    /**
     * Form edit jam kerja
     */
    public function edit($id)
    {
        $jamKerja = JamKerja::findOrFail($id);
        return view('dashboard.absensis.jam_kerja.edit', compact('jamKerja'));
    }

    /**
     * Update jam kerja
     */
    public function update(Request $request, $id)
    {
        $jamKerja = JamKerja::findOrFail($id);
        $validated = $request->validate([
            'nama_shift'    => 'required|string|max:100',
            'jenis_pegawai' => 'required|string|max:100',

            // ✅ Terima H:i (browser lama) dan H:i:s (step="1")
            'jam_masuk'     => ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'batas_masuk'   => ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'jam_pulang'    => ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'batas_pulang'  => ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],

            'hari'          => 'required|in:senin,selasa,rabu,kamis,jumat,sabtu,minggu',
            'is_default'    => 'nullable|boolean',
            'is_hari_kerja'  => 'nullable|boolean',
        ], [
            'jam_masuk.regex'    => 'Format jam masuk tidak valid (contoh: 07:00:00)',
            'batas_masuk.regex'  => 'Format batas masuk tidak valid (contoh: 08:00:00)',
            'jam_pulang.regex'   => 'Format jam pulang tidak valid (contoh: 15:00:00)',
            'batas_pulang.regex' => 'Format batas pulang tidak valid (contoh: 16:00:00)',
        ]);

        // ✅ Normalisasi semua waktu ke format H:i:s
        $validated['jam_masuk']    = $this->normalizeTime($validated['jam_masuk']);
        $validated['batas_masuk']  = $this->normalizeTime($validated['batas_masuk']);
        $validated['jam_pulang']   = $this->normalizeTime($validated['jam_pulang']);
        $validated['batas_pulang'] = $this->normalizeTime($validated['batas_pulang']);

        // Jika is_default dicentang, nonaktifkan default lain (kecuali dirinya sendiri)
        if (!empty($validated['is_default'])) {
            JamKerja::where('id', '!=', $jamKerja->id)
                ->where('jenis_pegawai', $validated['jenis_pegawai'])
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }


        $jamKerja->update($validated);


        return redirect()
            ->route('dashboard.jam.absen.index')
            ->with('success', 'Jam kerja berhasil diperbarui');
    }

    /**
     * Hapus jam kerja
     */
    public function destroy(JamKerja $jamKerja)
    {
        // Jangan hapus jika sedang digunakan sebagai default
        if ($jamKerja->is_default) {
            return redirect()
                ->route('dashboard.jam.absen.index')
                ->with('error', 'Jam kerja default tidak dapat dihapus. Atur jam kerja lain sebagai default terlebih dahulu.');
        }

        $jamKerja->delete();

        return redirect()
            ->route('dashboard.jam.absen.index')
            ->with('success', 'Jam kerja berhasil dihapus');
    }

    /**
     * Toggle is_hari_kerja
     */
    public function toggleHariKerja(JamKerja $jamKerja)
    {
        $jamKerja->update(['is_hari_kerja' => !$jamKerja->is_hari_kerja]);
        $status = $jamKerja->is_hari_kerja ? 'Hari Kerja' : 'Bukan Hari Kerja';

        return response()->json([
            'success'       => true,
            'message'       => "Status berhasil diubah menjadi: {$status}",
            'is_hari_kerja' => $jamKerja->is_hari_kerja
        ]);
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Normalisasi waktu ke format H:i:s
     * "06:45"    → "06:45:00"
     * "06:45:00" → "06:45:00" (tidak berubah)
     */
    private function normalizeTime(string $time): string
    {
        return strlen($time) === 5 ? $time . ':00' : $time;
    }
}
