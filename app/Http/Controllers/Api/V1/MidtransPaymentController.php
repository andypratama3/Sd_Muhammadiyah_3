<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MidtransPaymentController extends Controller
{
    public function callback(Request $request)
    {
        $charge = Charge::where('order_id',$request->order_id)->first();

        if($charge->isEmpty())
        {
            return response()->json([
                'message' => 'Order not found'
            ], 404);
        }
        $status_transaction = $request->transaction_status;
        if($status_transaction == 'settlement'){
            $charge = $charge->transaction_status = $request->transaction_status;
        }elseif($status_transaction == 'pending'){
            $charge = $charge->transaction_status = $request->transaction_status;
        }elseif($status_transaction == 'deny'){
            $charge = $charge->transaction_status = $request->transaction_status;
        }else{
            $charge = $charge->transaction_status = $request->transaction_status;
        }

        $change_transaction_status = $charge->save();

        if($change_transaction_status){
            return response()->json([
                'status' => 'success',
                'message' => 'Transaction status updated',
                'data' => $charge
            ]);
        }
    }

    public function webhook(Request $request)
    {
        $data = $request->all();
        return response()->json(['data' => 'data']);
    }
}
