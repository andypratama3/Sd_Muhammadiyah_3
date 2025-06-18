<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MidtransApi extends Controller
{
    public function charge(Request $request)
    {
        $charge = Charge::where('order_id', $request->order_id)->first();

        if ($request->status == 'settlement') {

        }

        if ($charge) {
            return response()->json([
                'status' => 'success',
                'message' => 'Charge found',
                'data' => $charge,
            ]);
        }
    }
}
