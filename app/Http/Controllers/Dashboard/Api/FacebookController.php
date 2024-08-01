<?php

namespace App\Http\Controllers\Dashboard\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FacebookController extends Controller
{
    public function getData(Request $request)
    {
        $data = $request->all();
        return response()->json(['data' => 'data']);
    }
}
