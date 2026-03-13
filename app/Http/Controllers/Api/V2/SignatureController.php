<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SignatureController extends Controller
{
    public function verify(Request $request)
    {
        $signature = $request->input("signature");
        $secret = $request->input("secret");

        if ($signature !== $secret) {
            return response()->json([
                'status' => 'error',
                'message' => 'Signature not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Signature found',
        ]);
    }
}
