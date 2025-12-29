<?php

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
use App\Models\TenagaPendidikan;
use App\Http\Controllers\Controller;

class TenagaKependidikanDataController extends Controller
{
    public function list()
    {
        try {
            $tenagaPendidikan = TenagaPendidikan::orderBy('name','asc')->get();

            if($tenagaPendidikan){
                return $this->success($tenagaPendidikan, 'OK');
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->success([],'Data Tidak Di Temukan');
        } catch (\Throwable $e) {
            return $this->serverError('Gagal mengambil data tenaga pendidikan: ' . $e->getMessage(), 500);
        }
    }
}
