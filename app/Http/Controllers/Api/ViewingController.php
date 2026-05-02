<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ViewingController extends Controller
{
   
    public function index(Request $request)
    {
        $query = DB::table('viewing as v')
            ->join('renter as r',           'r.renter_no',   '=', 'v.renter_no')
            ->join('property_for_rent as p', 'p.property_no', '=', 'v.property_no')
            ->select(
                'v.property_no',
                'v.renter_no',
                'v.viewing_date',
                'v.comments',
                DB::raw("r.first_name || ' ' || r.last_name AS renter_name"),
                DB::raw("p.street || ', ' || p.area || ', ' || p.city AS property_address"),
                'p.property_type',
                'p.rental_status'
            );

        if ($request->filled('property_no')) {
            $query->where('v.property_no', $request->property_no);
        }
        if ($request->filled('renter_no')) {
            $query->where('v.renter_no', $request->renter_no);
        }

        $viewings = $query->orderByDesc('v.viewing_date')->get();

        return response()->json(['data' => $viewings], 200);
    }

   
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'property_no'  => 'required|integer|exists:property_for_rent,property_no',
                'renter_no'    => 'required|integer|exists:renter,renter_no',
                'viewing_date' => 'required|date',
                'comments'     => 'nullable|string|max:1000',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }

        try {
          
            DB::statement('CALL record_viewing(?, ?, ?, ?)', [
                $validated['property_no'],
                $validated['renter_no'],
                $validated['viewing_date'],
                $validated['comments'] ?? null,
            ]);

        
            $property = DB::table('property_for_rent')
                ->where('property_no', $validated['property_no'])
                ->first();

            return response()->json([
                'message'        => 'Viewing recorded successfully.',
                'rental_status'  => $property->rental_status,
                'data'           => $validated,
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    
    public function byProperty(int $propertyNo)
    {
        $viewings = DB::select('SELECT * FROM get_viewings_for_property(?)', [$propertyNo]);

        return response()->json(['data' => $viewings], 200);
    }

    
    public function update(Request $request, int $propertyNo, int $renterNo, string $viewingDate)
    {
        try {
            $validated = $request->validate([
                'comments' => 'required|string|max:1000',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }

        $updated = DB::table('viewing')
            ->where('property_no',  $propertyNo)
            ->where('renter_no',    $renterNo)
            ->where('viewing_date', $viewingDate)
            ->update([
                'comments'   => $validated['comments'],
                'updated_at' => now(),
            ]);

        if (!$updated) {
            return response()->json(['error' => 'Viewing not found.'], 404);
        }

        return response()->json(['message' => 'Viewing updated successfully.'], 200);
    }

    
    public function destroy(int $propertyNo, int $renterNo, string $viewingDate)
    {
        $deleted = DB::table('viewing')
            ->where('property_no',  $propertyNo)
            ->where('renter_no',    $renterNo)
            ->where('viewing_date', $viewingDate)
            ->delete();

        if (!$deleted) {
            return response()->json(['error' => 'Viewing not found.'], 404);
        }

        return response()->json(['message' => 'Viewing deleted successfully.'], 200);
    }
}