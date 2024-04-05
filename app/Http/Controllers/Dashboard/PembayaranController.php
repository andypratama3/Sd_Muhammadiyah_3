<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use App\Exports\InvoiceExcel;
use App\Models\JudulPembayaran;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use App\DataTransferObjects\PembayaranData;
use App\Actions\Dashboard\Pembayaran\PembayaranAction;
use App\Actions\Dashboard\Pembayaran\PembayaranActionDelete;

class PembayaranController extends Controller
{
    public function index()
    {
        $no = 0;
        $pembayarans = Pembayaran::all();
        $juduls = JudulPembayaran::all();
        return view('dashboard.data.pembayaran.index', compact('no','pembayarans','juduls'));
    }
    public function data_table(Request $request)
    {
        $query = Pembayaran::with('kelas','siswa','judul')->select(['order_id','gross_amount','siswa_id','status','kelas_id','judul_id','category_kelas']);

        if($request->judul_pembayaran){
            $query->where('judul_id', $request->judul_pembayaran);
        }

        return DataTables::of($query)
        ->addColumn('name', function ($judul) {
            $judul_pembayaran = $judul->judul->name;
            return $judul_pembayaran;
        })
        ->addColumn('siswa.name', function ($siswa) {
            $siswa_name = $siswa->siswa->name;
            return $siswa_name;
        })
        ->addColumn('kelas.name', function ($kelas) {
            $kelas_name = $kelas->kelas->name;
            return $kelas_name;
        })

        ->addColumn('options', function ($row){
            return '
            <a href="' . route('dashboard.datamaster.pembayaran.show', $row->order_id) . '" class="btn btn-sm btn-warning"><i class="fa fa-eye"></i></a>
            <a href="' . route('dashboard.datamaster.pembayaran.edit', $row->order_id) . '" class="btn btn-sm btn-primary"><i class="fa fa-pen"></i></a>
            <button data-id="' . $row['order_id'] . '" class="btn btn-sm btn-danger" id="btn-delete"><i class="fa fa-trash"></i></button>
        ';
        })
        ->rawColumns(['options'])
        ->addIndexColumn()
        ->make(true);
    }
    public function create()
    {
        $juduls = JudulPembayaran::all();
        $siswas = Siswa::select(['id','name','slug'])->get();
        $kelass = Kelas::orderBy('name','asc')->get();
        return view('dashboard.data.pembayaran.create', compact('siswas','kelass','juduls'));
    }
    public function store(PembayaranData $pembayaranData, PembayaranAction $pembayaranAction)
    {
        $pembayaranAction->execute($pembayaranData);
        return redirect()->route('dashboard.datamaster.pembayaran.index')->with('success', 'Berhasil Menambahkan Pembayaran');
    }
    public function show($order_id)
    {
        $pembayaran = Pembayaran::where('order_id', $order_id)->firstOrFail();

        return view('dashboard.data.pembayaran.show', compact('pembayaran'));
    }
    public function edit($order_id)
    {
        $juduls = JudulPembayaran::all();
        $pembayaran = Pembayaran::where('order_id', $order_id)->firstOrFail();
        $siswas = Siswa::select(['id','name','slug'])->get();
        $kelass = Kelas::orderBy('name','asc')->get();
        return view('dashboard.data.pembayaran.edit', compact('juduls','siswas','kelass','pembayaran'));
    }
    public function update(PembayaranData $pembayaranData, PembayaranAction $pembayaranAction)
    {
        $pembayaranAction->execute($pembayaranData);
        return redirect()->route('dashboard.datamaster.pembayaran.index')->with('success', 'Berhasil Update Pembayaran');
    }
    public function destroy(PembayaranActionDelete $pembayaranActionDelete, $order_id)
    {
        if($pembayaranActionDelete)
        {
            $pembayaranActionDelete->execute($order_id);
            return response()->json(['status' => 'success', 'message' => 'Berhasil Menghapus Invoice']);
            // toaster()->sucess('Berhasil Menghapus Artikel');
        }else{
            return response()->json(['status' => 'error', 'message' => 'Gagal Menghapus Artikel']);
        }
    }
    public function exportExcel(Request $request)
    {
        $judulId = $request->judul_id;
        $judul_pembayaran = JudulPembayaran::where('id', $judulId)->first();

        if ($judulId != null) {
            return Excel::download(new InvoiceExcel($judulId), "siswa-invoice-$judul_pembayaran->name   -excel.xlsx");
        } else {
            return redirect()->route('dashboard.datamaster.pembayaran.index')->with('error', 'Silahkan Pilih Kategori Pembayaran =');
        }
    }

}
