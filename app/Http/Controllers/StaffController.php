<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\BranchOffice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    // =====================================================
    // HELPER: Job titles that can supervise others
    // =====================================================
    private const SUPERVISORY_ROLES = ['Supervisor'];

    // =====================================================
    // LIST + SEARCH + FILTER
    // =====================================================
    public function index(Request $request)
    {
        $query = Staff::query();
        $user  = auth()->user();

        // ── RBAC SCOPE ────────────────────────────────────────
        // Supervisor sees only the staff they directly supervise
        if ($user->job_title === 'Supervisor') {
            $query->where('supervisor_staff_no', $user->staff_no);
        }
        // Manager and Secretary see all staff — no scope needed
        // ─────────────────────────────────────────────────────

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'ILIKE', "%$search%")
                  ->orWhere('last_name', 'ILIKE', "%$search%")
                  ->orWhere('nin', 'ILIKE', "%$search%");
            });
        }

        if ($request->filled('branch_no')) {
            $query->where('branch_no', $request->branch_no);
        }

        if ($request->filled('job_title')) {
            $query->where('job_title', $request->job_title);
        }

        if ($request->filled('supervisor_staff_no')) {
            $query->where('supervisor_staff_no', $request->supervisor_staff_no);
        }

        $staff = $query->with(['branch', 'supervisor'])
            ->orderBy('last_name')
            ->paginate(10);

        return view('staff.index', compact('staff'));
    }

    // =====================================================
    // CREATE FORM
    // =====================================================
    public function create()
    {
        return view('staff.create', [
            'branches'    => BranchOffice::orderBy('city')->get(),
            'supervisors' => Staff::whereIn('job_title', self::SUPERVISORY_ROLES)
                                  ->orderBy('last_name')
                                  ->get(),
        ]);
    }

    // =====================================================
    // STORE
    // =====================================================
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name'          => 'required|string|max:50',
            'last_name'           => 'required|string|max:50',
            'address'             => 'required|string|max:150',
            'telephone_no'        => 'required|string|max:20',
            'sex'                 => 'required|in:Male,Female',
            'date_of_birth'       => 'required|date',
            'nin'                 => 'required|string|max:20|unique:staff',
            'job_title'           => 'required|in:Manager,Supervisor,Secretary,Staff',
            'salary'              => 'required|numeric|min:1',
            'date_joined'         => 'required|date',
            'branch_no'           => 'required|exists:branch_office,branch_no',
            'supervisor_staff_no' => 'nullable|exists:staff,staff_no',
            'date_start'          => 'nullable|date',
            'car_allowance'       => 'nullable|numeric|min:0',
            'bonus'               => 'nullable|numeric|min:0',
            'typing_speed'        => 'nullable|integer|min:1',
        ]);

        if ($validated['job_title'] !== 'Manager') {
            $validated['date_start']    = null;
            $validated['car_allowance'] = null;
            $validated['bonus']         = null;
        }
        if ($validated['job_title'] !== 'Secretary') {
            $validated['typing_speed'] = null;
        }

        try {
            $validated['password'] = Hash::make($validated['nin']);

            if (!empty($validated['supervisor_staff_no'])) {
                $supervisor = Staff::find($validated['supervisor_staff_no']);
                if (
                    !$supervisor ||
                    $supervisor->branch_no != $validated['branch_no'] ||
                    $supervisor->job_title !== 'Supervisor'
                ) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Selected supervisor does not belong to the chosen branch.',
                    ], 422);
                }
            }

            $staff = Staff::create($validated);

        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => $this->parseTriggerMessage($e->getMessage()),
            ], 422);
        }

        if ($request->filled('nok_name')) {
            \App\Models\NextOfKin::updateOrCreate(
                ['staff_no' => $staff->staff_no],
                [
                    'full_name'    => $request->nok_name,
                    'relationship' => $request->nok_relationship,
                    'address'      => $request->nok_address,
                    'telephone_no' => $request->nok_phone,
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Staff created successfully',
            'data'    => $staff,
        ], 201);
    }

    // =====================================================
    // SHOW (BLADE page)
    // =====================================================
    public function showPage($id)
    {
        $user  = auth()->user();
        $staff = Staff::findOrFail($id);

        // Supervisor can only view their own team members
        if ($user->job_title === 'Supervisor' &&
            $staff->supervisor_staff_no != $user->staff_no) {
            abort(403, 'You do not have permission to view this staff member.');
        }

        return view('staff.show', compact('staff'));
    }

    // =====================================================
    // SHOW (API — returns JSON for JS fetch)
    // =====================================================
    public function apiShow($id)
    {
        $user  = auth()->user();
        $staff = Staff::with(['branch', 'supervisor', 'nextOfKin'])->findOrFail($id);

        // Supervisor can only view their own team members
        if ($user->job_title === 'Supervisor' &&
            $staff->supervisor_staff_no != $user->staff_no) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view this staff member.',
            ], 403);
        }

        return response()->json(['success' => true, 'data' => $staff]);
    }

    // =====================================================
    // EDIT (BLADE)
    // =====================================================
    public function edit($id)
    {
        return view('staff.edit', [
            'staff'       => Staff::findOrFail($id),
            'branches'    => BranchOffice::orderBy('city')->get(),
            'supervisors' => Staff::whereIn('job_title', self::SUPERVISORY_ROLES)
                                  ->orderBy('last_name')
                                  ->get(),
        ]);
    }

    // =====================================================
    // UPDATE
    // =====================================================
    public function update(Request $request, $id)
    {
        $staff = Staff::findOrFail($id);

        $validated = $request->validate([
            'first_name'          => 'required|string|max:50',
            'last_name'           => 'required|string|max:50',
            'address'             => 'required|string|max:150',
            'telephone_no'        => 'required|string|max:20',
            'sex'                 => 'required|in:Male,Female',
            'date_of_birth'       => 'required|date',
            'nin'                 => 'required|string|max:20|unique:staff,nin,' . $id . ',staff_no',
            'job_title'           => 'required|in:Manager,Supervisor,Secretary,Staff',
            'salary'              => 'required|numeric|min:1',
            'date_joined'         => 'required|date',
            'branch_no'           => 'required|exists:branch_office,branch_no',
            'supervisor_staff_no' => 'nullable|exists:staff,staff_no',
            'date_start'          => 'nullable|date',
            'car_allowance'       => 'nullable|numeric|min:0',
            'bonus'               => 'nullable|numeric|min:0',
            'typing_speed'        => 'nullable|integer|min:1',
            'nok_name'            => 'nullable|string|max:100',
            'nok_relationship'    => 'nullable|string|max:50',
            'nok_address'         => 'nullable|string|max:150',
            'nok_phone'           => 'nullable|string|max:20',
        ]);

        if ($validated['job_title'] !== 'Manager') {
            $validated['date_start']    = null;
            $validated['car_allowance'] = null;
            $validated['bonus']         = null;
        }
        if ($validated['job_title'] !== 'Secretary') {
            $validated['typing_speed'] = null;
        }

        $validated['password'] = Hash::make($validated['nin']);

        if (!empty($validated['supervisor_staff_no'])) {
            $supervisor = Staff::find($validated['supervisor_staff_no']);
            if (
                !$supervisor ||
                $supervisor->branch_no != $validated['branch_no'] ||
                $supervisor->job_title !== 'Supervisor'
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected supervisor does not belong to the chosen branch.',
                ], 422);
            }
        }

        try {
            $staff->update(collect($validated)->except([
                'nok_name',
                'nok_relationship',
                'nok_address',
                'nok_phone',
            ])->toArray());

        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => $this->parseTriggerMessage($e->getMessage()),
            ], 422);
        }

        if ($request->filled('nok_name')) {
            \App\Models\NextOfKin::updateOrCreate(
                ['staff_no' => $staff->staff_no],
                [
                    'full_name'    => $request->nok_name,
                    'relationship' => $request->nok_relationship,
                    'address'      => $request->nok_address,
                    'telephone_no' => $request->nok_phone,
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Staff updated successfully',
            'data'    => $staff->fresh(['nextOfKin']),
        ]);
    }

    // =====================================================
    // DELETE
    // =====================================================
    public function destroy(Request $request, $id)
    {
        $staff = Staff::findOrFail($id);

        if ($staff->job_title === 'Manager') {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Managers cannot be deleted.',
                ], 422);
            }
            return redirect()->route('staff.index')
                ->with('error', 'Managers cannot be deleted.');
        }

        if ($staff->subordinates()->count() > 0) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete supervisor with assigned subordinates. Reassign them first.',
                ], 422);
            }
            return redirect()->route('staff.index')
                ->with('error', 'Cannot delete supervisor with assigned subordinates.');
        }

        $staff->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Staff deleted successfully',
            ]);
        }

        return redirect()->route('staff.index')
            ->with('success', 'Staff deleted successfully');
    }

    // =====================================================
    // DB FUNCTION: staff count by branch
    // =====================================================
    public function staffCountByBranch($branchNo)
    {
        $count = DB::selectOne('SELECT get_staff_count(?) AS total', [$branchNo]);

        return response()->json([
            'branch_no'   => $branchNo,
            'staff_count' => $count->total ?? 0,
        ]);
    }

    // =====================================================
    // API: GET SUPERVISORS FOR A BRANCH
    // =====================================================
    public function getSupervisors(Request $request)
    {
        $request->validate([
            'branch_no' => 'required|exists:branch_office,branch_no',
        ]);

        $supervisors = Staff::where('branch_no', $request->branch_no)
            ->whereIn('job_title', self::SUPERVISORY_ROLES)
            ->orderBy('last_name')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $supervisors,
        ]);
    }

    // =====================================================
    // API INDEX (for JS frontend)
    // =====================================================
    public function apiIndex(Request $request)
    {
        $query = Staff::with(['branch', 'supervisor']);
        $user  = auth()->user();

        // ── RBAC SCOPE ────────────────────────────────────────
        // Supervisor sees only the staff they directly supervise
        if ($user->job_title === 'Supervisor') {
            $query->where('supervisor_staff_no', $user->staff_no);
        }
        // Manager and Secretary see all — no scope needed
        // ─────────────────────────────────────────────────────

        if ($request->filled('branch_no')) {
            $query->where('branch_no', $request->branch_no);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'ILIKE', "%$search%")
                  ->orWhere('last_name', 'ILIKE', "%$search%");
            });
        }

        return response()->json([
            'success' => true,
            'data'    => $query->orderBy('last_name')->get(),
        ]);
    }

    // =====================================================
    // BRANCH REPORT (Blade view)
    // =====================================================
    public function branchReport($branchNo)
    {
        $branchOffice = BranchOffice::findOrFail($branchNo);

        $staffCount = DB::selectOne(
            'SELECT get_staff_count(?) AS total',
            [$branchNo]
        )->total ?? 0;

        $manager = Staff::where('branch_no', $branchNo)
            ->where('job_title', 'Manager')
            ->first();

        $supervisors = Staff::with('subordinates')
            ->where('branch_no', $branchNo)
            ->where('job_title', 'Supervisor')
            ->get();

        foreach ($supervisors as $supervisor) {
            $result = DB::selectOne(
                'SELECT get_supervisor_staff_count(?) AS total',
                [$supervisor->staff_no]
            );
            $supervisor->db_staff_count = $result->total ?? 0;
        }

        $staff = Staff::with('supervisor')
            ->where('branch_no', $branchNo)
            ->orderBy('last_name')
            ->get();

        return view('staff.branchreport', compact(
            'branchOffice', 'staffCount', 'manager', 'supervisors', 'staff'
        ));
    }

    // =====================================================
    // SUPERVISOR LIST (all branches)
    // =====================================================
    public function supervisorList()
{
    $user  = auth()->user();
    $query = Staff::with(['branch', 'subordinates'])
        ->where('job_title', 'Supervisor');

    // Supervisor sees only themselves in this list
    if ($user->job_title === 'Supervisor') {
        $query->where('staff_no', $user->staff_no);
    }

    $supervisors = $query->orderBy('last_name')->get();

    foreach ($supervisors as $supervisor) {
        $result = DB::selectOne(
            'SELECT get_supervisor_staff_count(?) AS total',
            [$supervisor->staff_no]
        );
        $supervisor->db_staff_count = $result->total ?? 0;
    }

    return view('staff.supervisorlist', compact('supervisors'));
}

    // =====================================================
    // SUBORDINATES for a supervisor
    // =====================================================
    public function subordinates($id)
{
    $user       = auth()->user();
    $supervisor = Staff::with('subordinates')->findOrFail($id);

    // Supervisor can only view their own team
    if ($user->job_title === 'Supervisor' &&
        $supervisor->staff_no != $user->staff_no) {
        abort(403, 'You do not have permission to view this team.');
    }

    return view('staff.subordinates', compact('supervisor'));
}

    // =====================================================
    // ASSIGN STAFF TO BRANCH (stored procedure)
    // =====================================================
    public function assignToBranch(Request $request, $id)
    {
        $validated = $request->validate([
            'branch_no'           => 'required|exists:branch_office,branch_no',
            'supervisor_staff_no' => 'nullable|exists:staff,staff_no',
        ]);

        try {
            DB::statement('CALL assign_staff_to_branch(?, ?, ?)', [
                $id,
                $validated['branch_no'],
                $validated['supervisor_staff_no'] ?? null,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => $this->parseTriggerMessage($e->getMessage()),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Staff assigned to branch successfully',
        ]);
    }

    // =====================================================
    // HELPER: extract human-readable message from DB trigger exceptions
    // =====================================================
    private function parseTriggerMessage(string $message): string
    {
        if (preg_match('/ERROR:\s*(.+?)(?:\nDETAIL|$)/s', $message, $matches)) {
            return trim($matches[1]);
        }
        return 'A database rule was violated. Please check your input.';
    }
}