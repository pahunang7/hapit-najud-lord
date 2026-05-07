<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $propertyCount = DB::table('property_for_rent')->count();
        $viewingCount = DB::table('viewing')->count();

        $activeLeaseCount = DB::table('lease_agreement')
            ->whereRaw('CURRENT_DATE BETWEEN start_date AND end_date')
            ->count();

        return view('dashboard', compact(
            'propertyCount',
            'viewingCount',
            'activeLeaseCount'
        ));
    }
} 

