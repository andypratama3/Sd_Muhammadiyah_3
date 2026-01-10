<?php

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
use App\Models\TenagaPendidikan;
use App\Models\StrukturTenagaPendidikan;
use App\Http\Controllers\Controller;

class TenagaKependidikanDataController extends Controller
{
    public function list()
    {
        try {
            $strukturTenagaPendidikan = StrukturTenagaPendidikan::with('children')
                ->whereNull('struktur_tenaga_pendidikan_id')
                ->orderBy('name', 'asc')
                ->get();

            // Ambil semua tenaga pendidikan dengan relasi struktur
            $tenagaPendidikan = TenagaPendidikan::with('struktur_tenaga_pendidikan')
                ->orderBy('name', 'asc')
                ->get();

            // Build hierarchical structure
            $hierarchicalData = $this->buildHierarchy($strukturTenagaPendidikan, $tenagaPendidikan);

            return $this->success($hierarchicalData, 'OK');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->success([], 'Data Tidak Di Temukan');
        } catch (\Throwable $e) {
            return $this->serverError('Gagal mengambil data tenaga pendidikan: ' . $e->getMessage(), 500);
        }
    }

    private function buildHierarchy($strukturList, $tenagaPendidikan)
    {
        $result = [];

        foreach ($strukturList as $struktur) {
            $node = [
                'id' => $struktur->id,
                'name' => $struktur->name,
                'slug' => $struktur->slug,
                'staff' => [],
                'children' => []
            ];

            // Tambahkan staff yang memiliki struktur ini
            foreach ($tenagaPendidikan as $staff) {
                if ($staff->struktur_tenaga_pendidikan_id === $struktur->id) {
                    $node['staff'][] = [
                        'id' => $staff->id,
                        'name' => $staff->name,
                        'jabatan' => $staff->jabatan,
                        'foto' => $staff->foto,
                        'slug' => $staff->slug,
                    ];
                }
            }

            // Rekursif untuk children
            if ($struktur->children && $struktur->children->count() > 0) {
                $node['children'] = $this->buildHierarchy($struktur->children, $tenagaPendidikan);
            }

            $result[] = $node;
        }

        return $result;
    }
}
