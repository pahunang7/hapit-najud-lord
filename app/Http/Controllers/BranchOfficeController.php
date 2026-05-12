<?php

namespace App\Http\Controllers;

use App\Models\BranchOffice;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchOfficeController extends Controller
{
    // =========================================================
    // SHARED FILTER LOGIC (avoids duplication between index & apiIndex)
    // =========================================================
    private function applyFilters($query, Request $request)
    {
        // Search by city, street, or postcode
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('city', 'ILIKE', "%{$search}%")
                  ->orWhere('street', 'ILIKE', "%{$search}%")
                  ->orWhere('postcode', 'ILIKE', "%{$search}%");
            });
        }

        // Filter: show only a specific branch
        if ($request->filled('branch_no')) {
            $query->where('branch_no', $request->branch_no);
        }

        // Filter: show only branches that have at least one staff with this job title
        if ($request->filled('job_title')) {
            $query->whereHas('staff', function ($q) use ($request) {
                $q->where('job_title', $request->job_title);
            });
        }

        return $query;
    }

    // =========================================================
    // LIST BRANCHES (BLADE PAGE + SEARCH + FILTERS)
    // =========================================================
    public function index(Request $request)
    {
        $query = $this->applyFilters(BranchOffice::query(), $request);

        $branches = $query->withCount('staff')
            ->orderBy('city')
            ->paginate(10);

        return view('branch.index', compact('branches'));
    }

    // =========================================================
    // SHOW CREATE FORM (BLADE)
    // =========================================================
    public function create()
    {
        return view('branch.create');
    }

    // =========================================================
    // STORE BRANCH
    // =========================================================
    public function store(Request $request)
    {
        $validated = $request->validate([
            'street'       => 'required|string|max:100',
            'area'         => 'nullable|string|max:100',
            'city'         => 'required|string|max:100',
            'postcode'     => 'required|string|max:20',
            'telephone_no' => 'required|string|max:20',
            'fax_no'       => 'nullable|string|max:20',
        ]);

        $branch = BranchOffice::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Branch created successfully',
            'data'    => $branch,
        ], 201);
    }

    // =========================================================
    // SHOW BRANCH BLADE PAGE
    // =========================================================
    public function showPage(BranchOffice $branchOffice)
    {
        return view('branch.show', compact('branchOffice'));
    }

    // =========================================================
    // SHOW BRANCH — was an empty stub, now returns proper JSON
    // =========================================================
    public function show(BranchOffice $branchOffice)
    {
        return $this->apiShow($branchOffice);
    }

    // =========================================================
    // EDIT FORM (BLADE)
    // =========================================================
    public function edit(BranchOffice $branchOffice)
    {
        return view('branch.edit', compact('branchOffice'));
    }

    // =========================================================
    // UPDATE BRANCH
    // =========================================================
    public function update(Request $request, BranchOffice $branchOffice)
    {
        $validated = $request->validate([
            'street'       => 'required|string|max:100',
            'area'         => 'nullable|string|max:100',
            'city'         => 'required|string|max:100',
            'postcode'     => 'required|string|max:20',
            'telephone_no' => 'required|string|max:20',
            'fax_no'       => 'nullable|string|max:20',
        ]);

        $branchOffice->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Branch updated successfully',
            'data'    => $branchOffice,
        ]);
    }

    // =========================================================
    // DELETE BRANCH — fixed: handles both JSON and redirect
    // =========================================================
    public function destroy(Request $request, BranchOffice $branchOffice)
    {
        if ($branchOffice->staff()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete branch with assigned staff. Reassign or remove staff first.',
            ], 422);
        }

        $branchOffice->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Branch deleted successfully',
            ]);
        }

        return redirect()->route('branch.index')->with('success', 'Branch deleted');
    }

    // =========================================================
    // API: STAFF COUNT (uses PostgreSQL function)
    // =========================================================
    public function staffCount(BranchOffice $branchOffice)
    {
        $result = DB::selectOne('SELECT get_staff_count(?) AS total', [$branchOffice->branch_no]);

        return response()->json([
            'success'     => true,
            'branch_no'   => $branchOffice->branch_no,
            'staff_count' => $result->total ?? 0,
        ]);
    }

    // =========================================================
    // API: MANAGER
    // =========================================================
    public function manager(BranchOffice $branchOffice)
    {
        $manager = Staff::where('branch_no', $branchOffice->branch_no)
            ->where('job_title', 'Manager')
            ->first();

        return response()->json([
            'success'   => true,
            'branch_no' => $branchOffice->branch_no,
            'manager'   => $manager,
        ]);
    }

    // =========================================================
    // API INDEX (for JS frontend)
    // =========================================================
    public function apiIndex(Request $request)
    {
        $query = $this->applyFilters(BranchOffice::query(), $request);

        $branches = $query->withCount('staff')
            ->orderBy('city')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $branches,
        ]);
    }

    // =========================================================
    // API SHOW — full branch detail with staff, manager, count
    // =========================================================
    public function apiShow(BranchOffice $branchOffice)
    {
        $staff = Staff::where('branch_no', $branchOffice->branch_no)->get();

        $staffCount = DB::selectOne(
            'SELECT get_staff_count(?) AS total',
            [$branchOffice->branch_no]
        )->total ?? 0;

        $manager = Staff::where('branch_no', $branchOffice->branch_no)
            ->where('job_title', 'Manager')
            ->first();

        return response()->json([
            'success' => true,
            'data'    => [
                'branch'      => $branchOffice,
                'staff_count' => $staffCount,
                'manager'     => $manager,
                'staff'       => $staff,
            ],
        ]);
    }

    // =========================================================
    // BLADE: STAFF PAGE FOR A BRANCH
    // =========================================================
    public function staffPage(BranchOffice $branchOffice)
    {
        return view('branch.staff', compact('branchOffice'));
    }

    public function getStaff($branchNo)
    {
        $staff = \App\Models\Staff::where('branch_no', $branchNo)
                    ->get(['staff_no', 'first_name', 'last_name']);

        $mapped = $staff->map(fn($s) => [
            'staff_no'  => $s->staff_no,
            'full_name' => $s->first_name . ' ' . $s->last_name,
        ]);

        return response()->json($mapped);
    }
}