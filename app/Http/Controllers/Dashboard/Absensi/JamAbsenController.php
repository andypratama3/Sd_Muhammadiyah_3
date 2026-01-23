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
        $jamKerja = JamKerja::orderBy('is_default', 'desc')->get();
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
            'jam_masuk'     => 'required|date_format:H:i',
            'batas_masuk'   => 'required|date_format:H:i|after_or_equal:jam_masuk',
            'jam_pulang'    => 'required|date_format:H:i',
            'batas_pulang'  => 'required|date_format:H:i|after_or_equal:jam_pulang',
            'hari'          => 'required|string',
            'is_default'    => 'nullable|boolean',
        ]);

        // Jika default = true, nonaktifkan default lain
        if (!empty($validated['is_default'])) {
            JamKerja::where('is_default', true)->update(['is_default' => false]);
        }

        JamKerja::create($validated);

        return redirect()
            ->route('dashboard.jam.absen.index')
            ->with('success', 'Jam kerja berhasil ditambahkan');
    }

    /**
     * Form edit jam kerja
     */
    public function edit(JamKerja $jamKerja)
    {
        return view('dashboard.absensis.jam_kerja.edit', compact('jamKerja'));
    }

    /**
     * Update jam kerja
     */
    public function update(Request $request, JamKerja $jamKerja)
    {
        $validated = $request->validate([
            'nama_shift'    => 'required|string|max:100',
            'jenis_pegawai' => 'required|string|max:100',
            'jam_masuk'     => 'required|date_format:H:i',
            'batas_masuk'   => 'required|date_format:H:i|after_or_equal:jam_masuk',
            'jam_pulang'    => 'required|date_format:H:i',
            'batas_pulang'  => 'required|date_format:H:i|after_or_equal:jam_pulang',
            'hari'          => 'required|string',
            'is_default'    => 'nullable|boolean',
        ]);

        if (!empty($validated['is_default'])) {
            JamKerja::where('id', '!=', $jamKerja->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        $jamKerja->update($validated);

        return redirect()
            ->route('dashboard.jam.absen.index')
            ->with('success', 'Jam kerja berhasil diperbarui');
    }
}
