<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Spmb;
use App\Models\Siswa;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use App\Exports\SpmbExportData;
use App\Services\WhatsAppService;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use App\Actions\Dashboard\Siswa\SiswaAction;

class SpmbController extends Controller
{
    protected $whatsApp;

    public function __construct()
    {
        $this->whatsApp = new WhatsAppService();
    }

    public function index()
    {
        return view('dashboard.spmb.index');
    }

    public function data_table(Request $request)
    {
        $spmb = Spmb::whereYear('created_at', date('Y'))->orderBy('created_at', 'asc');

        if($request->tahun) {
            $spmb = $spmb->whereYear('created_at', $request->tahun);
        }

        return DataTables::of($spmb)
            ->addColumn('nomor_urut', function ($row) {
                $nomor_urut = sprintf('%03d', $row->nomor_urut);

                return $nomor_urut;
            })
            ->addColumn('action', function ($row) {
                if (isset($row->id)) {
                    return '
                        <a href="'.route('dashboard.spmb.show', $row->id).'" class="btn btn-sm btn-warning"><i class="fa fa-eye"></i></a>
                        <a href="'.route('dashboard.spmb.edit', $row->id).'" class="btn btn-sm btn-primary"><i class="fa fa-pen"></i></a>
                        <button data-id="'.$row['id'].'" class="btn btn-sm btn-danger" id="btn-delete"><i class="fa fa-trash"></i></button>
                    ';
                }
            })
            ->addColumn('status_pembayaran', function ($row) {
                if (isset($row->status_pembayaran)) {
                    $status_pembayaran = $row->status_pembayaran;

                    if ($status_pembayaran == 'pending') {
                        return '<span class="badge bg-warning">PENDING</span>';
                    } else {
                        return '<span class="badge bg-success">SELESAI</span>';
                    }
                }
            })
            ->addColumn('status', function ($row) {
                $status = $row->status;

                if ($status == 'tidak_diterima') {
                    return '<span class="badge bg-danger">TIDAK DITERIMA</span>';
                } elseif($status == 'diterima') {
                    return '<span class="badge bg-success">DITERIMA</span>';
                } else {
                    return '<span class="badge bg-warning">PENDING</span>';
                }
            })
            ->rawColumns(['action', 'status','status_pembayaran'])
            ->addIndexColumn()
            ->make(true);
    }

    // public function

    public function edit(Spmb $spmb)
    {
        return view('dashboard.spmb.edit', compact('spmb'));
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'nama' => 'required|string|max:100',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'agama' => 'required|string|max:100',
            'suku' => 'required|string|max:100',
            'alamat' => 'required|string|max:255',
            'nama_asal_sekolah' => 'nullable|string|max:100',
            'sttb' => 'nullable|string|max:100',
            'alamat_sekolah' => 'nullable|string|max:255',
            'select_data' => 'required|in:orang_tua,wali',

            // data orang tua
            'nama_ayah' => 'nullable|string|max:100',
            'nama_ibu' => 'nullable|string|max:100',
            'pendidikan_ayah' => 'nullable|string|max:100',
            'alamat_ayah' => 'nullable|string|max:255',
            'pendidikan_ibu' => 'nullable|string|max:100',
            'pekerjaan_ayah' => 'nullable|string|max:100',
            'pekerjaan_ibu' => 'nullable|string|max:100',
            'alamat_ibu' => 'nullable|string|max:255',

            // wali
            'nama_wali' => 'nullable|string|max:100',
            'pekerjaan_wali' => 'nullable|string|max:100',
            'alamat_wali' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            // file upload
            'file_sttb' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'akta_kelahiran' => 'file|mimes:jpg,jpeg,png,pdf|max:10240',
            'kk' => 'file|mimes:jpg,jpeg,png,pdf|max:10240',
            'pas_foto' => 'file|mimes:jpg,jpeg,png|max:10240',
            'status' => 'required',
        ]);

        $spmb = Spmb::findOrFail($id);
        $uploadedFiles = [];
        $fileFields = ['file_sttb', 'akta_kelahiran', 'kk', 'pas_foto'];

        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $ext = strtolower($file->getClientOriginalExtension());
                $filename = ucfirst($field) . '_' . Str::slug($validatedData['nama']) . '_' . date('YmdHis') . '.' . $ext;
                $destination = public_path("storage/files/spmb/$field/");

                if (!file_exists($destination)) {
                    mkdir($destination, 0755, true);
                }

                // Hapus file lama jika ada
                $oldFile = $spmb->{$field};
                if ($oldFile && file_exists($destination . $oldFile)) {
                    unlink($destination . $oldFile);
                }

                // Simpan file baru
                if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                    ImageHelper::resizeAndSave($file, $destination, $filename);
                } else {
                    $file->move($destination, $filename);
                }

                $uploadedFiles[$field] = $filename;
            } else {
                // Tidak ada file baru, pakai file lama
                $uploadedFiles[$field] = $spmb->{$field};
            }
        }

        // Update data
        $spmb->update([
            ...$validatedData,
            'file_sttb' => $uploadedFiles['file_sttb'],
            'akta_kelahiran' => $uploadedFiles['akta_kelahiran'],
            'kk' => $uploadedFiles['kk'],
            'pas_foto' => $uploadedFiles['pas_foto'],
        ]);

       if ($validatedData['status'] == 'diterima') {
            $noHp = '+62' . ltrim($spmb->phone ?? '85349734475', '0');
            $appUrl = env('APP_URL');
            $pesan = "Halo {$spmb->nama},\n\n" .
                    "Selamat! Anda telah dinyatakan *DITERIMA* pada Seleksi Penerimaan Murid Baru SD Muhammadiyah 3 Samarinda.\n\n" .
                    "Silakan melengkapi data pada tautan berikut:\n" .
                    "$appUrl/spmb/kelengkapan-data/{$spmb->order_id}\n\n" .
                    "Terima kasih, kami nantikan kehadiran Anda.";

            // $this->whatsApp->sendMessage($noHp, $pesan);
        }

        return redirect()->route('dashboard.spmb.index')->with('success', 'Data SPMB berhasil diperbarui.');
    }

    public function export(Request $request)
    {
        $request->validate([
            'year' => 'required|digits:4',
        ]);

        $year = $request->query('year');

        return Excel::download(new SpmbExportData($year), "spmb_$year.xlsx");
    }



    public function destroy($id)
    {
        $spmb = Spmb::where('id', $id)->firstOrFail();

        $action = $spmb->delete();

        if (! $action) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal Menghapus Data',
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil Menghapus Data',
        ]);
    }
}
