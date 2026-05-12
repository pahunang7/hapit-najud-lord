<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PropertyForRent;
use App\Models\Renter;

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

                // ✅ FIXED: dynamic rental status
                DB::raw("
    CASE
        WHEN EXISTS (
            SELECT 1
            FROM lease_agreement l
            WHERE l.property_no = p.property_no
            AND CURRENT_DATE BETWEEN l.start_date AND l.end_date
        ) THEN 'rented'

        WHEN EXISTS (
            SELECT 1
            FROM viewing v
            WHERE v.property_no = p.property_no
            AND v.viewing_date >= CURRENT_DATE
        ) THEN 'reserved'

        WHEN EXISTS (
            SELECT 1
            FROM lease_agreement l
            WHERE l.property_no = p.property_no
            AND l.end_date < CURRENT_DATE
        ) THEN 'lease expired'

        ELSE 'available'
    END AS rental_status
"),
                

                'o.full_name AS owner_name',
                'b.city AS branch_city',
                DB::raw("s.first_name || ' ' || s.last_name AS assigned_staff")
            );

        
        // ✅ FIXED: filter using computed field
        if ($request->filled('rental_status')) {
            $query->having('rental_status', '=', $request->rental_status);
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

    
    // ⚠️ OPTIONAL: You can keep this, but it’s now useless
    public function updateStatus(Request $request, int $propertyNo)
    {
        return response()->json([
            'message' => 'Rental status is now computed dynamically and cannot be manually updated.'
        ], 400);
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




  public function search(Request $request)
    {
        $renterId = $request->renter_no;

        $renter = Renter::find($renterId);

        if (!$renter) {
            return response()->json([
                'message' => 'Renter not found.'
            ], 404);
        }

        $properties = PropertyForRent::query()

            ->when($renter->preferred_type, function ($query) use ($renter) {

                $query->where(
                    'property_type',
                    $renter->preferred_type
                );

            })

            ->when($renter->preferred_location, function ($query) use ($renter) {

    $query->where(function ($q) use ($renter) {

        $q->where(
            'city',
            'ILIKE',
            '%' . $renter->preferred_location . '%'
        )

        ->orWhere(
            'area',
            'ILIKE',
            '%' . $renter->preferred_location . '%'
        );

    });

})

            ->when($renter->max_rent, function ($query) use ($renter) {

                $query->where(
                    'monthly_rent',
                    '<=',
                    $renter->max_rent
                );

            })

            ->get();

        return response()->json([

            'renter' => $renter,

            'properties' => $properties

        ]);
    }

  public function webIndex()
{
    $properties = DB::table('property_for_rent')
        ->orderBy('property_no')
        ->get();

    $owners = DB::table('owner')
        ->orderBy('owner_no')
        ->get();

    $branches = DB::table('branch_office')
        ->orderBy('branch_no')
        ->get();

    return view(
        'properties.index',
        compact('properties', 'owners', 'branches')
    );
}

public function store(Request $request)
{
    $request->validate([
        'street'        => 'required|string|max:100',
        'area'          => 'required|string|max:100',
        'city'          => 'required|string|max:100',
        'postcode'      => 'required|string|max:20',
        'property_type' => 'required|string',
        'no_of_rooms'   => 'required|integer|min:1',
        'monthly_rent'  => 'required|numeric|min:1',
        'owner_no'      => 'required|integer',
        'branch_no'     => 'required|integer',
        'staff_no'      => 'required|integer',
    ]);

    $nextNo = DB::table('property_for_rent')->max('property_no') + 1;

    DB::table('property_for_rent')->insert([
        'property_no'   => $nextNo,
        'street'        => $request->street,
        'area'          => $request->area,
        'city'          => $request->city,
        'postcode'      => $request->postcode,
        'property_type' => $request->property_type,
        'no_of_rooms'   => $request->no_of_rooms,
        'monthly_rent'  => $request->monthly_rent,
        'owner_no'      => $request->owner_no,
        'branch_no'     => $request->branch_no,
        'staff_no'      => $request->staff_no,
    ]);

    return redirect()->back()->with('success', 'Property added successfully.');
}

public function destroy($id)
{
    DB::table('property_for_rent')
        ->where('property_no', $id)
        ->delete();

    return redirect()->back()->with('success', 'Property deleted successfully.');
}
}
