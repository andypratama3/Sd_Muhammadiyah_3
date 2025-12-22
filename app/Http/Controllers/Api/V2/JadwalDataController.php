<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Kelas;
use App\Models\Jadwal;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class JadwalDataController extends Controller
{
    public function tahunAjaran()
    {
        $jadwal = Jadwal::groupBy('tahun_ajaran')->pluck('tahun_ajaran');

        if($jadwal) {
            return $this->success($jadwal, 'ok');
        }
        return $this->error('Data Tidak Di Temukan');

    }

    public function kelas()
    {
        $kelas = Kelas::where('name', '!=', 'Lulus')->orderBy('name', 'asc')->get();

        if($kelas) {
            return $this->success($kelas, 'ok');
        }
        return $this->error('Data Tidak Di Temukan');

    }

    public function categoryKelas(Request $request)
    {
        $kelas_id = $request->kelas_id;

        $kelas = Kelas::find($kelas_id);

        if (! $kelas) {
            return $this->error('Kelas tidak ditemukan');
        }

        $raw = $kelas->category_kelas;

        if (! $raw) {
            return $this->success([], 'ok');
        }

        $decoded = json_decode($raw, true);

        if (! is_array($decoded)) {
            return $this->success([], 'ok');
        }

        $categories = collect($decoded)
            ->values()
            ->filter(fn ($v) => trim($v) !== '')
            ->values();

        return $this->success($categories, 'ok');
    }


    public function list_jadwal(Request $request)
    {
        $request->validate([
            'tahun_ajaran' => 'required|string',
            'kelas_id' => 'required|string',
            'category_kelas' => 'required|string',
        ]);

        // make lowercase
        $request->merge([
            'category_kelas' => strtolower($request->category_kelas)
        ]);

        $jadwal = Jadwal::with('jadwal_details')
                ->where('tahun_ajaran', $request->tahun_ajaran)
                ->where('kelas_id', $request->kelas_id)
                ->where('category_kelas', $request->category_kelas)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'tahun_ajaran' => $item->tahun_ajaran ?? '-',
                        'kelas' => $item->kelas->name ?? '-',
                        'category_kelas' => $item->category_kelas ?? '-',
                        'jadwal' => $item->jadwal ?? '-',
                        'jadwal_details' => $item->jadwal_details->map(function ($detail) {
                            return [
                                'id' => $detail->id,
                                'kelas_id' => $detail->kelas->name ?? '-',
                                'hari' => $detail->hari ?? '-',
                                'time_start' => $detail->time_start ?? '-',
                                'time_end' => $detail->time_end ?? '-',
                                'jadwa' => $detail->jadwal->tahun_ajaran ?? '-',
                                'pelajaran_id' => $detail->pelajaran->name ?? '-',
                                'guru_id' => $detail->guru->name ?? '-',
                                'color' => $detail->color ?? '-',
                            ];
                        }),
                    ];
                });

        if($jadwal->isNotEmpty()){
            return $this->success($jadwal, 'ok');
        }

        return $this->error('Data Tidak Di Temukan');
    }
}
