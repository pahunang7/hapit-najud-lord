<?php

namespace App\Http\Controllers;

use App\Models\Renter;
use App\Models\Staff;
use App\Models\BranchOffice;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;



class RenterController extends Controller
{
    public function create()
{
    $branches = BranchOffice::orderBy('branch_no')->get();

    return view('renter.create', compact('branches'));
}
    // ─── CORS Helper ──────────────────────────────────────────────────────────
    // NOTE: For full CORS support, also add the middleware shown in routes/api.php
    private function corsHeaders(): array
    {
        return [
            'Access-Control-Allow-Origin'  => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Accept',
        ];
    }

    // ─── GET /api/renters ─────────────────────────────────────────────────────
    public function index()
{
    return view('renter.index');
}

    // ─── POST /api/renters ────────────────────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'first_name'         => 'required|string|max:50',
            'last_name'          => 'required|string|max:50',
            'address'            => 'required|string|max:150',
            'telephone_no'       => 'required|string|max:20',
            'preferred_type'     => 'nullable|string|max:50',
            'preferred_location' => 'nullable|string|max:100',
            'max_rent'           => 'nullable|numeric|min:0',
            'staff_no'           => 'required|integer|exists:staff,staff_no',
            'branch_no'          => 'required|integer|exists:branch_office,branch_no',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422, $this->corsHeaders());
        }

        // Validate staff belongs to chosen branch
        $staff = Staff::find($request->staff_no);
        if ($staff->branch_no !== (int) $request->branch_no) {
            return response()->json([
                'message' => 'The selected staff member does not belong to the chosen branch.'
            ], 422, $this->corsHeaders());
        }

        // ← FIXED: manually assign renter_no since table has no sequence
        $nextNo = DB::table('renter')->max('renter_no') + 1;

        $renter = Renter::create(array_merge(
            $request->only([
                'first_name', 'last_name', 'address', 'telephone_no',
                'preferred_type', 'preferred_location', 'max_rent',
                'staff_no', 'branch_no'
            ]),
            ['renter_no' => $nextNo]
        ));

        return response()->json([
            'message' => 'Client registered successfully.',
            'data'    => $renter
        ], 201, $this->corsHeaders());
    }

        // ─── GET /api/renters/{id} ────────────────────────────────────────────────
        public function show(int $id): JsonResponse
        {
            $renter = Renter::with(['branch', 'staff'])->find($id);

            if (!$renter) {
                return response()->json(['message' => 'Client not found.'], 404, $this->corsHeaders());
            }

            return response()->json(['data' => $renter], 200, $this->corsHeaders());
        }

    // ─── PUT /api/renters/{id} ────────────────────────────────────────────────
    public function update(Request $request, int $id): JsonResponse
    {
        $renter = Renter::find($id);

        if (!$renter) {
            return response()->json(['message' => 'Client not found.'], 404, $this->corsHeaders());
        }

        $validator = Validator::make($request->all(), [
            'first_name'         => 'sometimes|string|max:50',
            'last_name'          => 'sometimes|string|max:50',
            'address'            => 'sometimes|string|max:150',
            'telephone_no'       => 'sometimes|string|max:20',
            'preferred_type'     => 'nullable|string|max:50',
            'preferred_location' => 'nullable|string|max:100',
            'max_rent'           => 'nullable|numeric|min:0',
            'staff_no'           => 'sometimes|integer|exists:staff,staff_no',
            'branch_no'          => 'sometimes|integer|exists:branch_office,branch_no',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422, $this->corsHeaders());
        }

        $renter->update($request->only([
            'first_name', 'last_name', 'address', 'telephone_no',
            'preferred_type', 'preferred_location', 'max_rent',
            'staff_no', 'branch_no'
        ]));

        return response()->json([
            'message' => 'Client updated successfully.',
            'data'    => $renter
        ], 200, $this->corsHeaders());
    }

    // ─── DELETE /api/renters/{id} ─────────────────────────────────────────────
    public function destroy(int $id): JsonResponse
    {
        $renter = Renter::find($id);

        if (!$renter) {
            return response()->json(['message' => 'Client not found.'], 404, $this->corsHeaders());
        }

        $renter->delete();

        return response()->json([
            'message' => 'Client deleted successfully.'
        ], 200, $this->corsHeaders());
    }

    // ─── POST /api/renters/{id}/assign-staff ──────────────────────────────────
    // Calls the PostgreSQL PROCEDURE assign_staff_to_renter
    public function assignStaff(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'staff_no'    => 'required|integer|exists:staff,staff_no',
            'assigned_by' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422, $this->corsHeaders());
        }

        try {
            // Call the PostgreSQL stored procedure
            DB::statement('CALL assign_staff_to_renter(?, ?, ?)', [
                $id,
                $request->staff_no,
                $request->assigned_by ?? 'System'
            ]);

            return response()->json([
                'message' => 'Staff assigned to client successfully.'
            ], 200, $this->corsHeaders());

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Assignment failed: ' . $e->getMessage()
            ], 400, $this->corsHeaders());
        }
    }

    // ─── GET /api/renters/{id}/staff ──────────────────────────────────────────
    // Calls the PostgreSQL FUNCTION get_renter_staff
    public function getRenterStaff(int $id): JsonResponse
    {
        $renter = Renter::find($id);
        if (!$renter) {
            return response()->json(['message' => 'Client not found.'], 404, $this->corsHeaders());
        }

        // Call the PostgreSQL function
        $staff = DB::select('SELECT * FROM get_renter_staff(?)', [$id]);

        return response()->json([
            'renter_no'   => $id,
            'renter_name' => $renter->first_name . ' ' . $renter->last_name,
            'staff'       => $staff
        ], 200, $this->corsHeaders());
    }

    // ─── GET /api/renters/{id}/logs ───────────────────────────────────────────
    public function getLogs(int $id): JsonResponse
    {
        $logs = DB::table('renter_activity_log')
            ->where('renter_no', $id)
            ->orderBy('performed_at', 'desc')
            ->get();

        return response()->json(['data' => $logs], 200, $this->corsHeaders());
    }

    public function apiIndex()
{
    $renters = DB::table('renter as r')
        ->join('branch_office as b', 'b.branch_no', '=', 'r.branch_no')
        ->join('staff as s', 's.staff_no', '=', 'r.staff_no')
        ->select(
            'r.renter_no',
            'r.first_name',
            'r.last_name',
            'r.address',
            'r.telephone_no',
            'r.preferred_type',
            'r.preferred_location',
            'r.max_rent',
            'r.branch_no',
            'b.city as branch_city',
            'r.staff_no',
            DB::raw("s.first_name || ' ' || s.last_name AS staff_name")
        )
        ->get();

    return response()->json([
        'data' => $renters
    ]);
}


}
