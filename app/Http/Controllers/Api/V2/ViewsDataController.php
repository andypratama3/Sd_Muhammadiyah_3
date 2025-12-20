<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;

class ViewsDataController extends Controller
{
    public function viewData()
    {
        $visitor_by_day = Visitor::whereDate('created_at', Carbon::now()->format('Y-m-d'))->count();
        $visitor_by_month = Visitor::whereDate('created_at', '>=', Carbon::now()->startOfMonth()->format('Y-m-d'))->count();
        $visitor_by_year = Visitor::whereDate('created_at', '>=', Carbon::now()->startOfYear()->format('Y-m-d'))->count();

        if($visitor_by_day >= 1){
           return $this->success([
            'visitor_by_day' => $visitor_by_day,
            'visitor_by_month' => $visitor_by_month,
            'visitor_by_year' => $visitor_by_year,
           ]);
        }
    }
}
