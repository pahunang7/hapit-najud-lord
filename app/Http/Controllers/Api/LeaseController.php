<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeaseController extends Controller
{
    
    public function index(Request $request)
    {
        $query = DB::table('lease_agreement as la')
            ->join('renter as r',            'r.renter_no',   '=', 'la.renter_no')
            ->join('property_for_rent as p',  'p.property_no', '=', 'la.property_no')
            ->join('staff as s',              's.staff_no',    '=', 'la.staff_no')
            ->select(
                'la.lease_no',
                'la.start_date',
                'la.end_date',
                'la.duration',
                'la.deposit',
                'la.deposit_paid',
                'la.payment_method',
                'la.property_no',
                'la.renter_no',
                'la.staff_no',
                DB::raw("r.first_name || ' ' || r.last_name AS renter_name"),
                DB::raw("p.street || ', ' || p.city AS property_address"),
                'p.property_type',
                'p.monthly_rent',
                'p.rental_status',
                DB::raw("s.first_name || ' ' || s.last_name AS staff_name")
            );

        if ($request->filled('property_no')) {
            $query->where('la.property_no', $request->property_no);
        }
        if ($request->filled('renter_no')) {
            $query->where('la.renter_no', $request->renter_no);
        }

        return response()->json(['data' => $query->orderByDesc('la.start_date')->get()], 200);
    }

    
    public function show(int $leaseNo)
    {
        $lease = DB::table('lease_agreement as la')
            ->join('renter as r',           'r.renter_no',   '=', 'la.renter_no')
            ->join('property_for_rent as p', 'p.property_no', '=', 'la.property_no')
            ->join('staff as s',             's.staff_no',    '=', 'la.staff_no')
            ->select(
                'la.*',
                DB::raw("r.first_name || ' ' || r.last_name AS renter_name"),
                'r.telephone_no AS renter_phone',
                DB::raw("p.street || ', ' || p.area || ', ' || p.city AS property_address"),
                'p.property_type',
                'p.monthly_rent',
                'p.rental_status',
                DB::raw("s.first_name || ' ' || s.last_name AS staff_name")
            )
            ->where('la.lease_no', $leaseNo)
            ->first();

        if (!$lease) {
            return response()->json(['error' => 'Lease not found.'], 404);
        }

        return response()->json(['data' => $lease], 200);
    }

    
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'lease_no'       => 'required|integer|unique:lease_agreement,lease_no',
                'start_date'     => 'required|date',
                'end_date'       => 'required|date|after:start_date',
                'duration'       => 'required|integer|min:3|max:12',
                'deposit'        => 'required|numeric|min:0',
                'deposit_paid'   => 'required|in:Yes,No',
                'payment_method' => 'required|string|max:50',
                'property_no'    => 'required|integer|exists:property_for_rent,property_no',
                'renter_no'      => 'required|integer|exists:renter,renter_no',
                'staff_no'       => 'required|integer|exists:staff,staff_no',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }

        try {
            
            DB::statement('CALL create_lease_agreement(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $validated['lease_no'],
                $validated['start_date'],
                $validated['end_date'],
                $validated['duration'],
                $validated['deposit'],
                $validated['deposit_paid'],
                $validated['payment_method'],
                $validated['property_no'],
                $validated['renter_no'],
                $validated['staff_no'],
            ]);

            return response()->json([
                'message' => 'Lease created. Property status updated to rented.',
                'data'    => $validated,
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    
    public function update(Request $request, int $leaseNo)
    {
        try {
            $validated = $request->validate([
                'deposit_paid'   => 'sometimes|in:Yes,No',
                'payment_method' => 'sometimes|string|max:50',
                'end_date'       => 'sometimes|date',
                'duration'       => 'sometimes|integer|min:3|max:12',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }

        $updated = DB::table('lease_agreement')
            ->where('lease_no', $leaseNo)
            ->update(array_merge($validated, ['updated_at' => now()]));

        if (!$updated) {
            return response()->json(['error' => 'Lease not found.'], 404);
        }

        return response()->json(['message' => 'Lease updated successfully.'], 200);
    }

   
    public function destroy(int $leaseNo)
    {
        $lease = DB::table('lease_agreement')->where('lease_no', $leaseNo)->first();
        if (!$lease) {
            return response()->json(['error' => 'Lease not found.'], 404);
        }

        DB::table('lease_agreement')->where('lease_no', $leaseNo)->delete();

        return response()->json([
            'message' => 'Lease deleted. Property status reverted to available.',
        ], 200);
    }

    
    public function byProperty(int $propertyNo)
    {
        $leases = DB::select('SELECT * FROM get_lease_by_property(?)', [$propertyNo]);
        return response()->json(['data' => $leases], 200);
    }
}