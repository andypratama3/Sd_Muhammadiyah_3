<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Kelas;
use Illuminate\Http\Request;
use App\Models\JudulPembayaran;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class WhatsappController extends Controller
{
    public function index()
    {
        return view('dashboard.whatsaap.index');
    }

    public function data_table(Request $request)
    {
        $data = DB::table('whatsapp_incoming_messages')->orderBy('created_at', 'asc')->get();

        return DataTables::of($data)
            ->addColumn('action', function ($row) {
                 return '
                    <button type="button" data-url="'.route('dashboard.monitoring.whatsapp.show', $row->id).'" class="btn btn-sm btn-warning btn-show-data" ><i class="fa fa-eye"></i></button>
                ';
            })
            ->editColumn('type', function ($row) {
                return ucfirst($row->type);
            })
            ->editColumn('profile_name', function ($row) {
                return ucwords($row->profile_name);
            })
            ->rawColumns(['action'])
            ->addIndexColumn()
            ->make(true);
    }

    public function show($id)
    {
        $message = DB::table('whatsapp_incoming_messages')->where('id', $id)->first();


        if(!$message) {
            return response()->json(['status' => 'error', 'message' => 'Message not found']);
        }

        return response()->json(['status' => 'success', 'data' => $message]);

    }

    public function brodcast()
    {
        $kelas = Kelas::where('name', '!=', 'Lulus')->get();
        $kategoriPembayaran = JudulPembayaran::all();
        return view('dashboard.whatsaap.brodcast', compact('kelas','kategoriPembayaran'));
    }

    public function store(Request $request)
    {

    }


    public function error_index()
    {
        return view('dashboard.whatsaap.error');
    }

     public function error_show($id)
    {
        $status = DB::table('whatsapp_message_statuses')->where('id', $id)->first();
        return response()->json([
            'success' => true,
            'data' => $status
        ]);
    }

    public function data_table_error(Request $request)
    {
        if ($request->ajax()) {

            $data = DB::table('whatsapp_message_statuses')->orderBy('created_at', 'asc')->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('status', function ($row) {
                    $statusColors = [
                        'sent' => 'primary',
                        'delivered' => 'info',
                        'read' => 'success',
                        'failed' => 'danger',
                        'pending' => 'warning',
                    ];
                    $color = $statusColors[$row->status] ?? 'secondary';
                    return '<span class="badge bg-' . $color . '">' . ucfirst($row->status) . '</span>';
                })
                ->addColumn('timestamp', function ($row) {
                    return $row->timestamp ?? '-';
                })
                ->addColumn('has_errors', function ($row) {
                    if ($row->errors) {
                        return '<span class="badge bg-danger"><i class="fa-solid fa-exclamation-triangle"></i> Yes</span>';
                    }
                    return '<span class="badge bg-success"><i class="fa-solid fa-check"></i> No</span>';
                })
                ->addColumn('action', function ($row) {
                    $showUrl = route('dashboard.monitoring.whatsapp-error.show', $row->id);
                    return '
                        <button class="btn btn-info btn-sm btn-show-data" data-url="' . $showUrl . '" title="Lihat Detail">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    ';
                })
                ->rawColumns(['status', 'has_errors', 'action'])
                ->make(true);
        }
    }
}
