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

                DB::raw("
                    p.street || ', ' || p.area || ', ' || p.city
                    AS property_address
                "),

                'p.property_type',
                'p.rental_status'
            );

        return response()->json([
            'status' => 'success',
            'data'   => $query->orderByDesc('v.viewing_date')->get()
        ]);
    }

    /**
     * 📌 GET SINGLE VIEWING
     */
    public function show(
        int $propertyNo,
        int $renterNo,
        string $viewingDate
    )
    {
        $viewing = DB::table('viewing')
            ->where('property_no', $propertyNo)
            ->where('renter_no', $renterNo)
            ->where('viewing_date', $viewingDate)
            ->first();

        if (!$viewing) {

            return response()->json([
                'status'  => 'error',
                'message' => 'Viewing not found.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $viewing
        ]);
    }

    /**
     * 📌 STORE VIEWING
     */
    public function store(Request $request)
    {
        try {

            // BASIC INPUT VALIDATION ONLY
            $validated = $request->validate([
                'property_no'  =>
                    'required|integer|exists:property_for_rent,property_no',

                'renter_no' =>
                    'required|integer|exists:renter,renter_no',

                'viewing_date' =>
                    'required|date',

                'comments' =>
                    'nullable|string|max:1000',
            ]);

        } catch (ValidationException $e) {

            return response()->json([
                'status' => 'error',
                'errors' => $e->errors()
            ], 422);
        }

        try {

            // DATABASE PROCEDURE HANDLES BUSINESS RULES
            DB::statement(
                'CALL record_viewing(?, ?, ?, ?)',
                [
                    $validated['property_no'],
                    $validated['renter_no'],
                    $validated['viewing_date'],
                    $validated['comments'] ?? null,
                ]
            );

            return response()->json([
                'status'  => 'success',
                'message' => 'Viewing recorded successfully.',
                'data'    => $validated
            ], 201);

        } catch (\Exception $e) {

            // CLEAN POSTGRES ERROR MESSAGE
            $message = $e->getMessage();

            if (preg_match('/ERROR:\s*(.+)/', $message, $matches)) {
                $message = trim($matches[1]);
            }

            return response()->json([
                'status'  => 'error',
                'message' => $message
            ], 400);
        }
    }

    /**
     * 📌 UPDATE VIEWING
     */
    public function update(
        Request $request,
        int $propertyNo,
        int $renterNo,
        string $viewingDate
    )
    {
        try {

            // BASIC INPUT VALIDATION ONLY
            $validated = $request->validate([
                'property_no'  =>
                    'required|integer|exists:property_for_rent,property_no',

                'renter_no' =>
                    'required|integer|exists:renter,renter_no',

                'viewing_date' =>
                    'required|date',

                'comments' =>
                    'nullable|string|max:1000',
            ]);

        } catch (ValidationException $e) {

            return response()->json([
                'status' => 'error',
                'errors' => $e->errors()
            ], 422);
        }

        // CHECK IF VIEWING EXISTS
        $viewing = DB::table('viewing')
            ->where('property_no', $propertyNo)
            ->where('renter_no', $renterNo)
            ->where('viewing_date', $viewingDate)
            ->first();

        if (!$viewing) {

            return response()->json([
                'status'  => 'error',
                'message' => 'Viewing not found.'
            ], 404);
        }

        // CHECK FOR NO CHANGES
        if (
            $propertyNo == $validated['property_no'] &&
            $renterNo == $validated['renter_no'] &&
            $viewingDate == $validated['viewing_date'] &&
            ($viewing->comments ?? '') ==
            ($validated['comments'] ?? '')
        ) {

            return response()->json([
                'status'  => 'info',
                'message' =>
                    'No changes detected. Please modify at least one field.'
            ], 200);
        }

        try {

            // UPDATE RECORD
            DB::table('viewing')
                ->where('property_no', $propertyNo)
                ->where('renter_no', $renterNo)
                ->where('viewing_date', $viewingDate)
                ->update([
                    'property_no'  => $validated['property_no'],
                    'renter_no'    => $validated['renter_no'],
                    'viewing_date' => $validated['viewing_date'],
                    'comments'     => $validated['comments'],
                ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Viewing updated successfully.'
            ]);

        } catch (\Exception $e) {

            // CLEAN POSTGRES ERROR
            $message = $e->getMessage();

            if (preg_match('/ERROR:\s*(.+)/', $message, $matches)) {
                $message = trim($matches[1]);
            }

            return response()->json([
                'status'  => 'error',
                'message' => $message
            ], 400);
        }
    }

    /**
     * 📌 DELETE VIEWING
     */
    public function destroy(
        int $propertyNo,
        int $renterNo,
        string $viewingDate
    )
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

    /**
     * 📌 LOAD DROPDOWN DATA
     */
    public function formData()
    {
        $properties = DB::table('property_for_rent')
            ->select(
                'property_no',
                'property_type',
                'street',
                'city'
            )
            ->orderBy('property_no')
            ->get();

        $renters = DB::table('renter')
            ->select(
                'renter_no',
                DB::raw("
                    first_name || ' ' || last_name
                    AS renter_name
                ")
            )
            ->orderBy('renter_no')
            ->get();

        return response()->json([
            'properties' => $properties,
            'renters'    => $renters
        ]);
    }
}