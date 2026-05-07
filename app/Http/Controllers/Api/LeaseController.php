<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class LeaseController extends Controller
{
    /**
     * 📌 GET ALL LEASES
     */
    public function index(Request $request)
    {
        $query = DB::table('lease_agreement as la')
            ->join('renter as r', 'r.renter_no', '=', 'la.renter_no')
            ->join('property_for_rent as p', 'p.property_no', '=', 'la.property_no')
            ->join('staff as s', 's.staff_no', '=', 'la.staff_no')
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

        return response()->json([
            'status' => 'success',
            'data'   => $query->orderByDesc('la.start_date')->get()
        ]);
    }

    /**
     * 📌 SHOW SINGLE LEASE
     */
    public function show(int $leaseNo)
    {
        $lease = DB::table('lease_agreement')
            ->where('lease_no', $leaseNo)
            ->first();

        if (!$lease) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Lease not found.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $lease
        ]);
    }

    /**
     * 📌 CREATE LEASE
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'lease_no'       => 'required|integer|unique:lease_agreement,lease_no',
                'start_date'     => 'required|date',
                'end_date'       => 'required|date|after:start_date',
                'deposit'        => 'required|numeric|min:0',
                'deposit_paid'   => 'required|in:Yes,No',
                'payment_method' => 'required|string|max:50',
                'property_no'    => 'required|integer|exists:property_for_rent,property_no',
                'renter_no'      => 'required|integer|exists:renter,renter_no',
                'staff_no'       => 'required|integer|exists:staff,staff_no',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'errors' => $e->errors()
            ], 422);
        }

        $start = Carbon::parse($validated['start_date']);
        $end   = Carbon::parse($validated['end_date']);
        $duration = (int) $start->diffInMonths($end);

    

        try {
            DB::statement('CALL create_lease_agreement(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $validated['lease_no'],
                $validated['start_date'],
                $validated['end_date'],
                $duration,
                $validated['deposit'],
                $validated['deposit_paid'],
                $validated['payment_method'],
                $validated['property_no'],
                $validated['renter_no'],
                $validated['staff_no'],
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Lease created successfully.',
                'data'    => $validated
            ], 201);

        } catch (\Exception $e) {

    $message = $e->getMessage();

    if (str_contains($message, 'SQLSTATE')) {
        preg_match('/ERROR:\s*(.*)/', $message, $matches);
        $message = $matches[1] ?? $message;
    }

    return response()->json([
        'status' => 'error',
        'message' => trim($message)
    ], 400);
}
    }

    /**
     * 📌 UPDATE LEASE (FIXED)
     */
    public function update(Request $request, int $leaseNo)
{
    try {

        $validated = $request->validate([
            'start_date'     => 'sometimes|date',
            'end_date'       => 'sometimes|date|after:start_date',
            'deposit'        => 'sometimes|numeric|min:0',
            'deposit_paid'   => 'sometimes|in:Yes,No',
            'payment_method' => 'sometimes|string|max:50',
        ]);

    } catch (ValidationException $e) {

        return response()->json([
            'status' => 'error',
            'errors' => $e->errors()
        ], 422);
    }

    $lease = DB::table('lease_agreement')
        ->where('lease_no', $leaseNo)
        ->first();

    if (!$lease) {

        return response()->json([
            'status'  => 'error',
            'message' => 'Lease not found.'
        ], 404);
    }

    // ✅ Merge old + new values
    $newData = [

        'start_date'     => $request->start_date ?? $lease->start_date,
        'end_date'       => $request->end_date ?? $lease->end_date,
        'deposit'        => (float) ($request->deposit ?? $lease->deposit),
        'deposit_paid'   => $request->deposit_paid ?? $lease->deposit_paid,
        'payment_method' => $request->payment_method ?? $lease->payment_method,
        'property_no'    => $request->property_no ?? $lease->property_no,
        'renter_no'      => $request->renter_no ?? $lease->renter_no,
        'staff_no'       => $request->staff_no ?? $lease->staff_no,
    ];

    // ✅ Recalculate duration
    $start = Carbon::parse($newData['start_date']);
    $end   = Carbon::parse($newData['end_date']);

    $duration = (int) $start->diffInMonths($end);

    $newData['duration'] = $duration;

    // ✅ Check if anything changed
    $hasChanges = false;

    foreach ($newData as $key => $value) {

        if ($lease->$key != $value) {
            $hasChanges = true;
            break;
        }
    }

    if (!$hasChanges) {

        return response()->json([
            'status'  => 'info',
            'message' => 'No changes detected. Please modify at least one field.'
        ], 200);
    }

    // ✅ CALL POSTGRESQL PROCEDURE
    try {

        DB::statement(
            'CALL update_lease_agreement(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $leaseNo,
                $newData['start_date'],
                $newData['end_date'],
                $newData['duration'],
                $newData['deposit'],
                $newData['deposit_paid'],
                $newData['payment_method'],
                $newData['property_no'],
                $newData['renter_no'],
                $newData['staff_no'],
            ]
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Lease updated successfully.'
        ]);

    } catch (\Exception $e) {

        $message = $e->getMessage();

        // ✅ Clean PostgreSQL error
        if (str_contains($message, 'ERROR:')) {

            preg_match('/ERROR:\s*(.*)/', $message, $matches);

            $message = $matches[1] ?? $message;
        }

        return response()->json([
            'status'  => 'error',
            'message' => trim($message)
        ], 400);
    }
}
    /**
     * 📌 DELETE LEASE
     */
    public function destroy(int $leaseNo)
    {
        $deleted = DB::table('lease_agreement')
            ->where('lease_no', $leaseNo)
            ->delete();

        if (!$deleted) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Lease not found.'
            ], 404);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Lease deleted successfully.'
        ]);
    }

    /**
     * 📌 GET BY PROPERTY
     */
    public function byProperty(int $propertyNo)
    {
        $leases = DB::select('SELECT * FROM get_lease_by_property(?)', [$propertyNo]);

        return response()->json([
            'status' => 'success',
            'data'   => $leases
        ]);
    }
}