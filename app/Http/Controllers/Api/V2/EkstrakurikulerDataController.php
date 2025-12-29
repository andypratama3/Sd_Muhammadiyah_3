<?php

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
use App\Models\Esktrakurikuler;
use App\Http\Controllers\Controller;

class EkstrakurikulerDataController extends Controller
{
    public function list()
    {
        try {
            $ekstrakurikuler = Esktrakurikuler::orderBy('name','asc')->get();

            if ($ekstrakurikuler->count() > 0) {
                return $this->success($ekstrakurikuler, 'OK');
            }

        } catch(\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->success([],'Data Tidak Di Temukan');
        } catch (\Exception $e) {
            return $this->serverError('Gagal mengambil data ekstrakurikuler: ' . $e->getMessage(), 500);
        }
    }
}
