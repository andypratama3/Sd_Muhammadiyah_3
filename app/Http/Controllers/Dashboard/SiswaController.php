<?php

namespace App\Http\Controllers\Dashboard;


use App\Models\Kelas;
use App\Models\Siswa;
use Barryvdh\DomPDF\PDF;
use App\Exports\SiswaExport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use App\DataTransferObjects\SiswaData;
use Yajra\DataTables\Facades\DataTables;
use Yajra\DataTables\Contracts\DataTable;
use App\Actions\Dashboard\Siswa\SiswaAction;
use App\Actions\Dashboard\Siswa\SiswaActionDelete;
use App\Http\Controllers\Api\Dashboard\WilayahApi;

class SiswaController extends Controller
{
    protected $wilayahApi;
    public function __construct(WilayahApi $wilayahApi)
    {
        $this->getprovinsi = $wilayahApi;
        $this->getKelurahan = $wilayahApi;
    }
    public function index()
    {
        return view('dashboard.data.siswa.index');
    }
    public function data_table(Request $request)
    {
        $siswa = Siswa::with('kelas')->select(['id','name','nisn','jk','foto','slug']);
        return DataTables::of($siswa)
                ->addColumn('kelas.name', function ($kelas) {
                    $kelas_name = $kelas->kelas->pluck('name');
                    return $kelas_name;
                })
                ->addColumn('kelas.category', function ($kelas) {
                    $category_name = $kelas['kelas'][0]['pivot']['category_kelas'];
                    return $category_name;
                })
                ->addColumn('options', function ($row){
                    return '
                    <a href="' . route('dashboard.datamaster.siswa.show', $row->slug) . '" class="btn btn-sm btn-warning"><i class="fa fa-eye"></i></a>
                    <a href="' . route('dashboard.datamaster.siswa.edit', $row->slug) . '" class="btn btn-sm btn-primary"><i class="fa fa-pen"></i></a>
                    <button data-id="' . $row['slug'] . '" class="btn btn-sm btn-danger" id="btn-delete"><i class="fa fa-trash"></i></button>
                ';
                })
                ->rawColumns(['options'])
                ->addIndexColumn()
                ->make(true);
    }
    public function create()
    {
        $result_provinsi = $this->getprovinsi->provinsi()->json();
        // $result_provinsi = Http::get(route('provinsi.api'))->json();
        $kelass = Kelas::all();
        return view('dashboard.data.siswa.create', compact('kelass','result_provinsi'));
    }
    public function store(SiswaData $siswaData, SiswaAction $siswaAction)
    {
        $siswaAction->execute($siswaData);
        return redirect()->route('dashboard.datamaster.siswa.index')->with('success','Berhasil Menambahkan Siswa');
    }
    public function show(Siswa $siswa)
    {
        return view('dashboard.data.siswa.show', compact('siswa'));
    }
    public function edit(Siswa $siswa)
    {
        $kelass = Kelas::all();
        $result_provinsi = $this->getprovinsi->provinsi()->json();
        return view('dashboard.data.siswa.edit',compact('siswa','kelass','result_provinsi'));
    }
    public function update(SiswaData $siswaData, SiswaAction $siswaAction)
    {
        $siswaAction->execute($siswaData);
        return redirect()->route('dashboard.datamaster.siswa.index')->with('success','Berhasil Update Siswa');
    }
    public function destroy(Siswa $siswa, SiswaActionDelete $SiswaActionDelete)
    {
        if($SiswaActionDelete)
        {
            $SiswaActionDelete->execute($siswa);
            return response()->json(['status' => 'success', 'message' => 'Berhasil Menghapus Siswa']);
        }else{
            return response()->json(['status' => 'error', 'message' => 'Gagal Menghapus Siswa']);
        }

    }
    public function checknisn(Request $request)
    {
        $nisn = $request->nisn;
        $siswa = Siswa::where('nisn', $nisn)->first();
        if(empty($siswa)){
            return response()->json(['siswa' => $siswa, 'nisn Bisa Di gunakan']);
        }else {
            return response()->json(['error','nisn Telah Ada']);
        }
    }
    public function cetak_data($slug)
    {
        $siswa = Siswa::where('slug', $slug)->firstOrFail();

         /*
          ! take Request data From jquery
        */
        $provinsi_id = $siswa->provinsi_id;
        $kabupaten_id = $siswa->kabupaten_id;
        $kecamatan_id = $siswa->kecamatan_id;

        $response_provinsi = Http::get("https://emsifa.github.io/api-wilayah-indonesia/api/provinces.json");

        if ($response_provinsi->successful()) {
            /*
                ! take provinsi data from Api
             */
            $provinsi = $response_provinsi->json();
            $provinsi_take = collect($provinsi)->where('id', $provinsi_id)->first();

             /*
                ! take kabupaten data from Api
             */
            $response_kabupaten = Http::get("https://emsifa.github.io/api-wilayah-indonesia/api/regencies/$provinsi_id.json");
            $kabupaten = $response_kabupaten->json();
            $kabupaten_take = collect($kabupaten)->first();

             /*
                ! take kecamatan data from Api
             */
            $response_kecamatan = Http::get("https://emsifa.github.io/api-wilayah-indonesia/api/districts/$kabupaten_id.json");
            $kecamatan = $response_kecamatan->json();
            $kecamatan_take = collect($kecamatan)->first();

             /*
                ! take kelurahan data from Api
             */
            $response_kelurahan = Http::get("https://emsifa.github.io/api-wilayah-indonesia/api/villages/$kecamatan_id.json");
            $kelurahan = $response_kelurahan->json();
            $kelurahan_take = collect($kelurahan)->first();

            // return response()->json([
            //     'success' => true,
            //     'message' => 'Data API Sukses Di Ambil',
            //     'provinsi' => $provinsi_take,
            //     'kabupaten' => $kabupaten_take,
            //     'kecamatan' => $kecamatan_take,
            //     'kelurahan' => $kelurahan_take,
            // ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve data from the API',
            ]);
        }
        $data = [
            'siswa' => $siswa,
            'provinsi_take'  => $provinsi_take,
            'kabupaten_take' => $kabupaten_take,
            'kecamatan_take' => $kecamatan_take,
            'kelurahan_take' => $kelurahan_take,
        ];

        // $pdf = \PDF::loadView('dashboard.data.siswa.cetak', $data);
        // return $pdf->download('siswa'. $siswa->name .'.pdf');
        return view('dashboard.data.siswa.cetak', compact('siswa','provinsi_take','kabupaten_take','kecamatan_take','kelurahan_take'));
    }
    public function export_pdf()
    {

    }
    public function export_excel()
    {
        return Excel::download(new SiswaExport, 'siswa-export-excel.xlsx');
    }
}
