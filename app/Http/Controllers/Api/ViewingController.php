<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ViewingController extends Controller
{
    /**
     * 📌 GET ALL VIEWINGS
     */
    public function index(Request $request)
    {
        $query = DB::table('viewing as v')
            ->join('renter as r', 'r.renter_no', '=', 'v.renter_no')
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

        return response()->json([
            'status' => 'success',
            'data'   => $query->orderByDesc('v.viewing_date')->get()
        ]);
    }

    /**
     * 📌 STORE VIEWING
     */
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
        return response()->json([
            'status' => 'error',
            'errors' => $e->errors()
        ], 422);
    }

    // ✅ FIX 1: PREVENT DUPLICATE VIEWING
    $exists = DB::table('viewing')
        ->where('property_no', $validated['property_no'])
        ->where('renter_no', $validated['renter_no'])
        ->where('viewing_date', $validated['viewing_date'])
        ->exists();

    if ($exists) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Viewing already exists for this renter, property, and date.'
        ], 400);
    }

    // ✅ EXISTING RENT CHECK (your logic)
    $hasLease = DB::table('lease_agreement')
        ->where('property_no', $validated['property_no'])
        ->whereRaw('? BETWEEN start_date AND end_date', [$validated['viewing_date']])
        ->exists();

    if ($hasLease) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Cannot create viewing. Property is rented on this date.'
        ], 400);
    }

    try {
        DB::statement('CALL record_viewing(?, ?, ?, ?)', [
            $validated['property_no'],
            $validated['renter_no'],
            $validated['viewing_date'],
            $validated['comments'] ?? null,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Viewing recorded successfully.',
            'data'    => $validated
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Unexpected error while saving viewing.'
        ], 400);
    }
}
    /**
     * 📌 UPDATE VIEWING
     */
    public function update(Request $request, int $propertyNo, int $renterNo, string $viewingDate)
    {
        try {
            $validated = $request->validate([
                'comments' => 'required|string|max:1000',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'errors' => $e->errors()
            ], 422);
        }

        // ✅ OPTIONAL SAFETY: prevent updating if date is rented
        $hasLease = DB::table('lease_agreement')
            ->where('property_no', $propertyNo)
            ->whereRaw('? BETWEEN start_date AND end_date', [$viewingDate])
            ->exists();

        if ($hasLease) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Cannot update viewing. Property is rented on this date.'
            ], 400);
        }

        $updated = DB::table('viewing')
            ->where('property_no', $propertyNo)
            ->where('renter_no', $renterNo)
            ->where('viewing_date', $viewingDate)
            ->update([
                'comments'   => $validated['comments'],
                'updated_at' => now(),
            ]);

        if (!$updated) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Viewing not found.'
            ], 404);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Viewing updated successfully.'
        ]);
    }

    /**
     * 📌 DELETE VIEWING
     */
    public function destroy(int $propertyNo, int $renterNo, string $viewingDate)
    {
        $deleted = DB::table('viewing')
            ->where('property_no', $propertyNo)
            ->where('renter_no', $renterNo)
            ->where('viewing_date', $viewingDate)
            ->delete();

        if (!$deleted) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Viewing not found.'
            ], 404);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Viewing deleted successfully.'
        ]);
    }
}