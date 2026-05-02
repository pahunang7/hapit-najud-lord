<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PropertyController extends Controller
{
    
    public function index(Request $request)
    {
        $query = DB::table('property_for_rent as p')
            ->join('owner as o',        'o.owner_no',   '=', 'p.owner_no')
            ->join('branch_office as b', 'b.branch_no',  '=', 'p.branch_no')
            ->join('staff as s',         's.staff_no',   '=', 'p.staff_no')
            
            ->select(
                'p.property_no',
                DB::raw("p.street || ', ' || p.area || ', ' || p.city || ' ' || p.postcode AS full_address"),
                'p.property_type',
                'p.no_of_rooms',
                'p.monthly_rent',
                'p.rental_status',
                'o.full_name AS owner_name',
                'b.city AS branch_city',
                DB::raw("s.first_name || ' ' || s.last_name AS assigned_staff")
            );

        
        if ($request->filled('rental_status')) {
            $query->where('p.rental_status', $request->rental_status);
        }

      
        if ($request->filled('city')) {
            $query->where('p.city', 'ilike', '%' . $request->city . '%');
        }

      
        if ($request->filled('property_type')) {
            $query->where('p.property_type', 'ilike', '%' . $request->property_type . '%');
        }

      
        if ($request->filled('max_rent')) {
            $query->where('p.monthly_rent', '<=', $request->max_rent);
        }

        $properties = $query->orderBy('p.property_no')->get();

        return response()->json(['data' => $properties], 200);
    }

    
    public function show(int $propertyNo)
    {
       
        $status = DB::select('SELECT * FROM get_property_rental_status(?)', [$propertyNo]);

        if (empty($status)) {
            return response()->json(['error' => 'Property not found.'], 404);
        }

        return response()->json(['data' => $status[0]], 200);
    }

    
    public function updateStatus(Request $request, int $propertyNo)
    {
        $request->validate([
            'rental_status' => 'required|in:available,reserved,rented',
        ]);

        $updated = DB::table('property_for_rent')
            ->where('property_no', $propertyNo)
            ->update([
                'rental_status' => $request->rental_status,
                'updated_at'    => now(),
            ]);

        if (!$updated) {
            return response()->json(['error' => 'Property not found.'], 404);
        }

        return response()->json([
            'message'       => 'Rental status updated.',
            'rental_status' => $request->rental_status,
        ], 200);
    }

    
    public function renters()
    {
        $renters = DB::table('renter')
            ->select(
                'renter_no',
                DB::raw("first_name || ' ' || last_name AS full_name"),
                'telephone_no',
                'preferred_type',
                'preferred_location',
                'max_rent'
            )
            ->orderBy('last_name')
            ->get();

        return response()->json(['data' => $renters], 200);
    }

  
    public function staff()
    {
        $staff = DB::table('staff')
            ->select(
                'staff_no',
                DB::raw("first_name || ' ' || last_name AS full_name"),
                'position',
                'branch_no'
            )
            ->orderBy('last_name')
            ->get();

        return response()->json(['data' => $staff], 200);
    }
}