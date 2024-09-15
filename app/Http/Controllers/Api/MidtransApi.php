<?php

namespace App\Http\Controllers\Api;

use App\Models\Siswa;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MidtransApi extends Controller
{
    public function charge(Request $request)
    {
        $charge = Charge::where('order_id', $request->order_id)->first();

        if($request->status == 'settlement'){
            
        }

        if ($charge) {
            return response()->json([
                'status' => 'success',
                'message' => 'Charge found',
                'data' => $charge
            ]);
        }
    }
}
