<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AttendancesController extends Controller
{
    public function webhook(Request $request)
    {
        $request->validate([

        ]);
    }
}
