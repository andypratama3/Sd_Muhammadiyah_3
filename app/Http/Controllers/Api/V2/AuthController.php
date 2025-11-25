<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'nisn'     => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('nisn', 'password');

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['status'=>'error', 'message'=>'Unauthorized'], 401);
        }

        return response()->json([
            'status'     => 'success',
            'token'      => $token,
            'nisn'       => $request['nisn'],
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'status' => 'success',
            'user' => $user
        ]);
    }

    public function logout()
    {
        auth('api')->logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully'
        ]);
    }



}
