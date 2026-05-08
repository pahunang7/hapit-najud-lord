<?php

namespace App\Http\Controllers;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;


class StaffController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Staff::with('branch');

        // Filter by branch_no — used to only show staff from the selected branch
        if ($request->has('branch_no')) {
            $query->where('branch_no', $request->branch_no);
        }

        $staff = $query->orderBy('first_name')->get()->map(function ($s) {
            return [
                'staff_no'    => $s->staff_no,
                'full_name'   => $s->first_name . ' ' . $s->last_name,
                'position'    => $s->position,
                'branch_no'   => $s->branch_no,
                'branch_city' => $s->branch?->city,
            ];
        });

        return response()->json(['data' => $staff], 200, [
            'Access-Control-Allow-Origin' => '*',
        ]);
    }
}
