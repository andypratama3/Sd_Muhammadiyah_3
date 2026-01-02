<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Kelas;
use App\Models\Rapot;
use App\Models\Siswa;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RapotController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $rapots = Rapot::with('siswa', 'kelas');

        // Filter search
        if ($request->search) {
            $rapots->whereHas('siswa', fn($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('nisn', 'like', "%{$request->search}%")
            );
        }

        // Filter kelas
        if ($request->kelas) {
            $rapots->where('kelas_id', $request->kelas);
        }

        // Filter tahun
        if ($request->tahun) {
            $rapots->where('tahun', $request->tahun);
        }

        // Filter status file
        if ($request->status) {
            if ($request->status == 'with-file') {
                $rapots->whereNotNull('file_rapot');
            } else {
                $rapots->whereNull('file_rapot');
            }
        }

        if($request->kategori) {
            $rapots->where('kategori', $request->kategori);
        }

        $rapots = $rapots->orderBy('created_at', 'desc')->paginate(15);
        $kelass = Kelas::orderBy('name', 'asc')->get();

        return view('dashboard.data.rapot.index', compact('rapots', 'kelass'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kelass = Kelas::orderBy('name', 'asc')->get();
        return view('dashboard.data.rapot.create', compact('kelass'));
    }

    /**
     * Get siswa berdasarkan kelas (tanpa category_kelas)
     */
    public function getSiswa(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'tahun' => 'required|numeric'
        ]);

        $kelasId = $request->input('kelas_id');
        $tahun = $request->input('tahun');

        // Get kelas name
        $kelas = Kelas::find($kelasId);

        // Get siswa berdasarkan kelas saja (tanpa category filter)
        $siswas = Siswa::with('kelas')
            ->select([
                'siswas.id',
                'siswas.name',
                'siswas.nisn',
                'siswas.jk',
                'siswas.slug',
                'siswas.foto'
            ])
            ->whereHas('kelas', function ($query) use ($kelasId) {
                $query->where('kelas_id', $kelasId);
            })
            ->whereNull('siswas.deleted_at')
            ->orderBy('siswas.name', 'asc')
            ->get()
            ->map(function ($siswa) use ($kelasId) {
                // Format response dengan kelas info
                return [
                    'id' => $siswa->id,
                    'name' => $siswa->name,
                    'nisn' => $siswa->nisn,
                    'jk' => $siswa->jk,
                    'slug' => $siswa->slug,
                    'foto' => $siswa->foto,
                    'kelas_id' => $kelasId,
                    'kelas_name' => $siswa->kelas->pluck('name')->implode(', ') ?? '-'
                ];
            });

        return response()->json([
            'siswa' => $siswas,
            'kelas_name' => $kelas->name ?? '-',
            'total' => $siswas->count()
        ]);
    }

    /**
     * Store rapot data in batch (tanpa category_kelas)
     */
    public function store(Request $request)
    {
        try {
            // Validate basic inputs
            $request->validate([
                'kelas' => 'required|exists:kelas,id',
                'tahun' => 'required|numeric|min:2000|max:' . (date('Y') + 1),
                'rapot' => 'required|array|min:1',
                'kategori' => 'required|string'
            ]);

            // Validate each rapot entry
            $rapotData = $request->input('rapot', []);
            if (empty($rapotData)) {
                return redirect()
                    ->back()
                    ->with('error', 'Harap tambahkan minimal satu siswa!')
                    ->withInput();
            }

            // Additional validation for rapot entries
            foreach ($rapotData as $index => $data) {
                if (!isset($data['siswa_id'])) {
                    return redirect()
                        ->back()
                        ->with('error', "Data siswa tidak valid pada baris " . ($index + 1))
                        ->withInput();
                }

                // Check if at least catatan or file is provided
                $hasCatatan = !empty($data['catatan']);
                $hasFile = $request->hasFile("rapot.$index.file_rapot");

                if (!$hasCatatan && !$hasFile) {
                    return redirect()
                        ->back()
                        ->with('warning', "Baris " . ($index + 1) . ": Minimal ada catatan atau file rapot!")
                        ->withInput();
                }
            }

            $kelasId = $request->input('kelas');
            $tahun = $request->input('tahun');

            DB::beginTransaction();

            $successCount = 0;
            $updateCount = 0;
            $errors = [];

            foreach ($rapotData as $index => $data) {
                try {
                    // Skip jika tidak ada data
                    if (empty($data['siswa_id'])) {
                        continue;
                    }

                    // Verify siswa exists
                    $siswa = Siswa::find($data['siswa_id']);
                    if (!$siswa) {
                        $errors[] = "Baris " . ($index + 1) . ": Siswa tidak ditemukan";
                        continue;
                    }

                    $rapotInput = [
                        'siswa_id' => $data['siswa_id'],
                        'kelas_id' => $kelasId,
                        'tahun' => $tahun,
                        'angkatan' => $tahun,
                        'kategori' => $request->input('kategori'),
                        'catatan' => $data['catatan'] ?? null,
                    ];

                    // Handle file upload
                    if ($request->hasFile("rapot.$index.file_rapot")) {
                        $file = $request->file("rapot.$index.file_rapot");

                        // Validate file
                        if (!$file->isValid()) {
                            $errors[] = "Baris " . ($index + 1) . ": File tidak valid";
                            continue;
                        }

                        // Validate file size
                        if ($file->getSize() > 5242880) { // 5MB in bytes
                            $errors[] = "Baris " . ($index + 1) . ": Ukuran file melebihi 5MB";
                            continue;
                        }

                        // Create unique filename
                        $fileName = 'rapot_' . $tahun . '_' . $siswa->id . '_' . time() . '.' . $file->getClientOriginalExtension();

                        try {
                            $path = $file->storeAs('rapot/' . $tahun, $fileName, 'public');
                            $rapotInput['file_rapot'] = $path;
                        } catch (\Exception $e) {
                            $errors[] = "Baris " . ($index + 1) . ": Gagal upload file - " . $e->getMessage();
                            continue;
                        }
                    }

                    // Check if rapot already exists
                    $existingRapot = Rapot::where('siswa_id', $data['siswa_id'])
                        ->where('kelas_id', $kelasId)
                        ->where('tahun', $tahun)
                        ->first();

                    if ($existingRapot) {
                        // Delete old file if exists and new file uploaded
                        if (isset($rapotInput['file_rapot']) && $existingRapot->file_rapot) {
                            Storage::disk('public')->delete($existingRapot->file_rapot);
                        }

                        $existingRapot->update($rapotInput);
                        $updateCount++;
                    } else {
                        Rapot::create($rapotInput);
                        $successCount++;
                    }

                } catch (\Exception $e) {
                    $errors[] = "Baris " . ($index + 1) . ": " . $e->getMessage();
                }
            }

            if ($successCount === 0 && $updateCount === 0) {
                DB::rollBack();
                $errorMsg = !empty($errors) ? implode(', ', $errors) : 'Tidak ada data yang berhasil disimpan';
                return redirect()
                    ->back()
                    ->with('error', $errorMsg)
                    ->withInput();
            }

            DB::commit();

            $message = "Berhasil menyimpan rapot: {$successCount} baru, {$updateCount} diperbarui";
            if (!empty($errors)) {
                $message .= " | Kesalahan: " . implode(', ', $errors);
            }

            return redirect()
                ->route('dashboard.datamaster.rapot.index')
                ->with('success', $message);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $rapot = Rapot::with('siswa', 'kelas')->findOrFail($id);
        return view('dashboard.data.rapot.show', compact('rapot'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $rapot = Rapot::findOrFail($id);
        $kelass = Kelas::orderBy('name', 'asc')->get();
        return view('dashboard.data.rapot.edit', compact('rapot', 'kelass'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $rapot = Rapot::findOrFail($id);

        $request->validate([
            'catatan' => 'nullable|string',
            'file_rapot' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
            'delete_file' => 'nullable|in:1',
            'kategori' => 'required|string',
        ]);

        $updateData = [
            'catatan' => $request->input('catatan'),
            'kategori' => $request->input('kategori'),
            'file_rapot' => $rapot->file_rapot
        ];

        // Handle delete file
        if ($request->input('delete_file') == 1) {
            if ($rapot->file_rapot && Storage::disk('public')->exists($rapot->file_rapot)) {
                Storage::disk('public')->delete($rapot->file_rapot);
            }
            $updateData['file_rapot'] = null;
        }

        // Handle new file upload
        if ($request->hasFile('file_rapot')) {
            $file = $request->file('file_rapot');

            // Validate file
            if (!$file->isValid()) {
                return redirect()
                    ->back()
                    ->with('error', 'File tidak valid!')
                    ->withInput();
            }

            // Validate file size
            if ($file->getSize() > 5242880) { // 5MB in bytes
                return redirect()
                    ->back()
                    ->with('error', 'Ukuran file melebihi 5MB!')
                    ->withInput();
            }

            // Delete old file if exists
            if ($rapot->file_rapot && Storage::disk('public')->exists($rapot->file_rapot)) {
                Storage::disk('public')->delete($rapot->file_rapot);
            }

            $fileName = 'rapot_' . $rapot->tahun . '_' . $rapot->siswa_id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('rapot/' . $rapot->tahun, $fileName, 'public');
            $updateData['file_rapot'] = $path;
        }


        // Minimal validation: harus ada catatan atau file
        $hasCatatan = !empty($updateData['catatan']);
        $hasFile = !empty($updateData['file_rapot']);


        $hasExistingFile = !empty($rapot->file_rapot) && $updateData['file_rapot'] !== null;

        // Jika ada file lama dan tidak dihapus, atau ada file baru, atau ada catatan
        if (!$hasCatatan && !$hasFile && !($hasExistingFile && $request->input('delete_file') != 1)) {
            return redirect()
                ->back()
                ->with('error', 'Minimal harus ada catatan atau file rapot!')
                ->withInput();
        }

        $rapot->update($updateData);

        return redirect()
            ->route('dashboard.datamaster.rapot.index')
            ->with('success', 'Rapot berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $rapot = Rapot::findOrFail($id);

        // Delete file if exists
        if ($rapot->file_rapot && Storage::disk('public')->exists($rapot->file_rapot)) {
            Storage::disk('public')->delete($rapot->file_rapot);
        }

        $rapot->delete();

        return redirect()
            ->route('dashboard.datamaster.rapot.index')
            ->with('success', 'Rapot berhasil dihapus!');
    }
}
