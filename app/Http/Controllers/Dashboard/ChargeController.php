<?php

namespace App\Http\Controllers\Dashboard;

use Carbon\Carbon;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Charge;
use Illuminate\Http\Request;
use App\Exports\ChargeExport;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class ChargeController extends Controller
{
    public function index()
    {
        $kelass = Kelas::select('id','name')->orderBy('name','asc')->get();
        return view('dashboard.data.charge.index', compact('kelass'));
    }

    public function data_table(Request $request)
    {
        $charges = Charge::with('siswa')
                ->whereYear('created_at', Carbon::now()->year)
                ->whereMonth('created_at', Carbon::now()->month)
                ->orderBy('created_at', 'desc');

        if($request->kelas){
            $charges = $charges->whereHas('siswa.kelas', function ($query) use ($request) {
                $query->where('id', $request->kelas);
            });
        }

        if ($request->date) {
            $dates = explode(' : ', $request->date);
            $startDate = Carbon::createFromFormat('d-m-Y', trim($dates[0]))->format('Y-m-d');
            $endDate = Carbon::createFromFormat('d-m-Y', trim($dates[1]))->format('Y-m-d');

            $charges = Charge::whereBetween(DB::raw('date(created_at)'), [$startDate, $endDate]);
        }

        return DataTables::of($charges)
            ->addColumn('options', function ($row) {
                return '
                    <a href="' . route('dashboard.datamaster.charge.show', $row->id) . '" class="btn btn-sm me-2 btn-warning"><i class="fa fa-eye"></i></a>
                    <a href="' . route('dashboard.datamaster.charge.edit', $row->id) . '" class="btn btn-sm me-2 btn-info"><i class="fa fa-edit"></i></a>
                    <button data-id="' . $row['id'] . '" class="btn btn-sm btn-danger me-1" id="btn-delete"><i class="fa fa-trash"></i></button>
                ';
            })
            ->addColumn('va_number', function ($row) {
                return $row->va_number;
            })
            ->addColumn('siswa.name', function ($row) {
                return $row->siswa->name;
            })
            ->addColumn('kelas.name', function ($row) {
                return $row->siswa->kelas->pluck('name')->implode(', ');
            })
            ->rawColumns(['options'])
            ->addIndexColumn()
            ->make(true);
    }

    public function create()
    {
        $siswas = Siswa::all();
        return view('dashboard.data.charge.create', compact('siswas'));
    }

    public function show($id)
    {
        $charge = Charge::with('siswa')->find($id);
        return view('dashboard.data.charge.show', compact('charge'));
    }

    public function edit($id)
    {
        $charge = Charge::with('siswa')->where('id', $id)->firstOrFail();

        return view('dashboard.data.charge.edit', compact('charge'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'transaction_status' => 'required',
        ]);

        $charge = Charge::find($id);

        if (!$charge) {
            return redirect()->route('dashboard.datamaster.charge.index')->with('error', 'Data Tidak Ditemukan');
        }

        $charge->update([
            'transaction_status' => $request->transaction_status,
        ]);

        return redirect()->route('dashboard.datamaster.charge.index')->with('success', 'Data Berhasil Diubah');
    }

    public function destroy($id)
    {
        $charge = Charge::find($id);
        $action = $charge->delete();
        if($action){
            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil Menghapus Data'
            ]);
        }else{
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal Menghapus Data'
            ]);
        }
    }

    public function exportExcel(Request $request)
    {
        $request->validate([
            'date' => 'required',
        ]);

        $dates = explode(' : ', $request->date);
        $startDate = Carbon::createFromFormat('d-m-Y', trim($dates[0]))->format('Y-m-d');
        $endDate = Carbon::createFromFormat('d-m-Y', trim($dates[1]))->format('Y-m-d');

        $kelas = $request->kelas;

        return Excel::download(new ChargeExport($startDate, $endDate, $kelas), "charge-$startDate-$endDate-excel.xlsx");
    }

}
