<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BranchOffice;
use Illuminate\Http\JsonResponse;


class BranchController extends Controller
{
    public function index(): JsonResponse
    {
        $branches = BranchOffice::orderBy('branch_no')->get([
            'branch_no', 'street', 'area', 'city', 'postcode', 'telephone_no'
        ]);

        return response()->json(['data' => $branches], 200, [
            'Access-Control-Allow-Origin' => '*'
        ]);
    }
}