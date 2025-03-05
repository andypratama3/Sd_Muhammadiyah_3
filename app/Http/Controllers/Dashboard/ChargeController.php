<?php

namespace App\Http\Controllers\Dashboard;

use Carbon\Carbon;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Charge;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Exports\ChargeExport;
use App\Models\JudulPembayaran;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Controllers\Api\V1\MidtransPaymentController;

class ChargeController extends Controller
{

    protected $midtrans;

    public function __construct(MidtransPaymentController $midtrans)
    {
        $this->midtrans = $midtrans;
    }

    public function index()
    {
        $category_payments = JudulPembayaran::orderBy('name')->get();
        $kelass = Kelas::select('id','name')->orderBy('name','asc')->get();
        return view('dashboard.data.charge.index', compact('category_payments','kelass'));
    }

    public function data_table(Request $request)
    {
        $charges = Charge::with('siswa')
                ->orderBy('created_at', 'desc');

        if($request->category_payment){
            $charges = $charges->where('category_payment_id', $request->category_payment);
        }

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
                    <a href="' . route('dashboard.datamaster.charge.show', $row->id) . '" class="btn btn-sm m-1 btn-warning"><i class="fa fa-eye"></i></a>
                    <a href="' . route('dashboard.datamaster.charge.edit', $row->id) . '" class="btn btn-sm m-1 btn-info"><i class="fa fa-edit"></i></a>
                    <button data-id="' . $row['id'] . '" class="btn btn-sm btn-danger me-1" id="btn-delete"><i class="fa fa-trash"></i></button>
                ';
            })
            ->addColumn('gross_amount', function ($row) {
                // with .
                return "Rp " . number_format($row->gross_amount, 0, ',', '.');
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
        $kelas = Kelas::select('id','name')->orderBy('name','asc')->get();
        $kategori_pembayaran = JudulPembayaran::orderBy('name')->get();
        return view('dashboard.data.charge.create', compact('kelas','kategori_pembayaran'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_payment_id' => 'required',
            'kelas_id' => 'required',
        ]);

        $kategori_pembayaran = JudulPembayaran::find($request->category_payment_id);
        if (!$kategori_pembayaran) {
            return redirect()->route('dashboard.datamaster.charge.index')->with('error', 'Kategori Pembayaran Tidak Ditemukan');
        }

        $siswaList = Siswa::whereHas('kelas', function ($query) use ($request) {
            $query->where('kelas_id', $request->kelas_id);
        })->get();

        foreach ($siswaList as $siswa) {
            // Generate order_id yang lebih terstruktur
            $order_id = Str::uuid();



            // Tentukan jumlah pembayaran berdasarkan kategori pembayaran
            switch ($kategori_pembayaran->name) {
                case 'SPP':
                    $gross_amount = $siswa->spp;
                    break;
                case 'DPP':
                    $gross_amount = $siswa->dpp;
                    break;
                case 'Seragam':
                    $gross_amount = $siswa->seragam;
                    break;
                default:
                    $gross_amount = (int) str_replace('.', '', $request->gross_amount ?? '0');
                    break;
            }


            try {
                DB::beginTransaction();

                // Insert data ke tabel charges
                DB::table('charges')->insert([
                    'id' => Str::uuid(),
                    'name' => "{$kategori_pembayaran->name} {$siswa->name}",
                    'order_id' => $order_id,
                    'siswa_id' => $siswa->id,
                    'gross_amount' => $gross_amount,
                    'payment_type' => 'bank_transfer',
                    'bank' => 'bca',
                    'va_number' => $siswa->nisn . $kategori_pembayaran->code,
                    'transaction_id' => Str::uuid(),
                    'transaction_time' => now(),
                    'fraud_status' => 'accept',
                    'transaction_status' => 'pending',
                    'category_payment_id' => $kategori_pembayaran->id,
                    'snap_token' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Kirim pembayaran ke Midtrans
                $midtransResponse = $this->sendPaymentToMidtrans($siswa, $order_id,$gross_amount, $kategori_pembayaran);

                if (isset($midtransResponse['error'])) {
                    DB::rollBack();
                    return redirect()->route('dashboard.datamaster.charge.index')->with('error', $midtransResponse['error']);
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->route('dashboard.datamaster.charge.index')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
            }
        }

        return redirect()->route('dashboard.datamaster.charge.index')->with('success', 'Pembayaran berhasil dibuat.');
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

        $transaction_status = $request->transaction_status;
        $payment_type = null;

        if ($transaction_status === 'pay_offline') {
            try {
                $status = 'pay_offline';
                if ($this->midtrans->update_transaction_status($charge, $status)) {
                    $transaction_status = $status;
                    $payment_type = 'pay_offline';
                } else {
                    return redirect()->route('dashboard.datamaster.charge.index')
                        ->with('error', 'Gagal Mengupdate Status Transaksi di Midtrans.');
                }
            } catch (\Exception $e) {
                return redirect()->route('dashboard.datamaster.charge.index')
                    ->with('error', 'Terjadi Kesalahan: ' . $e->getMessage());
            }
        }


        $charge->update([
            'transaction_status' => $transaction_status,
            // 'payment_type' => $payment_type,
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

        // Format Name
        $carbonStartDate = Carbon::parse($startDate)->locale('id')->translatedFormat('d F Y');
        $carbonEndDate = Carbon::parse($endDate)->locale('id')->translatedFormat('d F Y');

        return Excel::download(new ChargeExport($startDate, $endDate, $kelas), "Rekap Pembayaran SPP Dari $carbonStartDate Sampai $carbonEndDate.xlsx");
    }

    private function sendPaymentToMidtrans(Siswa $siswa, $order_id,$gross_amount, $kategori_pembayaran)
    {
        $client = new Client();
        $server_key = env('MIDTRANS_SERVER_KEY');
        $monthName = Carbon::now()->locale('id_ID')->format('F');

        $params = [
            'payment_type' => 'bank_transfer',
            'transaction_details' => [
                'order_id' => $order_id,
                'gross_amount' => $gross_amount,
            ],
            'customer_details' => [
                'first_name' => $siswa->name,
                'email' => $siswa->email,
                'phone' => $siswa->no_hp,
            ],
            'bank_transfer' => [
                'bank' => 'bca',
                'va_number' => $siswa->nisn . $kategori_pembayaran->code,
            ],
            'expiry' => [
                'start_time' => now()->toIso8601String(),
                'duration' => 20,
                'unit' => 'days',
            ],
            'item_details' => [
                [
                    'id' => 1,
                    'price' => $gross_amount,
                    'quantity' => 1,
                    'name' => "Pembayaran {$kategori_pembayaran->name} {$siswa->name}",
                    'category' => $kategori_pembayaran->name,
                    'merchant_name' => "Sekolah Kreatif SD Muhammadiyah 3 Samarinda",
                ]
            ]
        ];

        try {
            $response = $client->post('https://api.midtrans.com/v2/charge', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode($server_key . ':'),
                    'Content-Type' => 'application/json',
                ],
                'json' => $params,
            ]);

            $responseData = json_decode($response->getBody(), true);
            // save va_number
            $charge = Charge::where('order_id', $order_id)->first();
            $charge->va_number = $responseData['va_numbers'][0]['va_number'];
            $charge->save();


            return $responseData;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }


}
