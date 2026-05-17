<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CONTROLLERS
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\ViewingController;
use App\Http\Controllers\Api\LeaseController;

use App\Http\Controllers\RenterController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\BranchOfficeController;
use App\Http\Controllers\OwnerController;

/*
|==========================================================================
| AUTHENTICATED API ROUTES
|==========================================================================
*/

Route::middleware(['auth'])->group(function () {

    /*
    |==========================================================================
    | BRANCHES API
    |==========================================================================
    | RBAC:
    |   Manager    → Full CRUD + Reports
    |   Supervisor → View
    |   Secretary  → View
    |   Staff      → View (needed for dropdowns in property/staff/renter forms)
    |--------------------------------------------------------------------------
    |
    | All authenticated roles can READ branches.
    | Staff needs /api/branches for dropdown loading in forms.
    | Secretary needs branch list for viewing purposes.
    |--------------------------------------------------------------------------
    */

    // VIEW — All authenticated roles (dropdowns depend on this)
    Route::prefix('branches')->group(function () {

        Route::get('/', [BranchOfficeController::class, 'apiIndex']);

        Route::get('/{branchOffice}/staff-count', [BranchOfficeController::class, 'staffCount']);

        Route::get('/{branchOffice}/manager', [BranchOfficeController::class, 'manager']);

        // This endpoint feeds the staff dropdown in property/renter create forms.
        // Must be accessible to anyone who can fill those forms (including Staff).
        Route::get('/{branch_no}/staff', [BranchOfficeController::class, 'getStaff']);

        Route::get('/{branchOffice}', [BranchOfficeController::class, 'apiShow']);
    });

    // FULL CRUD — Manager ONLY
    // RBAC: Create/Edit/Delete Branch → Manager ✔ Full, all others ❌
    Route::middleware('role:Manager')
        ->prefix('branches')
        ->group(function () {
            Route::post('/',                [BranchOfficeController::class, 'store']);
            Route::put('/{branchOffice}',   [BranchOfficeController::class, 'update']);
            Route::delete('/{branchOffice}',[BranchOfficeController::class, 'destroy']);
        });

    /*
    |==========================================================================
    | STAFF API
    |==========================================================================
    | RBAC:
    |   Manager    → Full CRUD
    |   Supervisor → View Supervised Team (own branch only — enforced in controller)
    |   Secretary  → View Only
    |   Staff      → View Own Profile only (enforced in controller)
    |--------------------------------------------------------------------------
    |
    | Secretary needs /api/staff for viewing staff lists and for dropdowns
    | in renter create/edit forms (Secretary can create/edit renters per RBAC).
    | supervisors-for-branch is used by the staff create/edit form
    | (Manager only creates staff, but the endpoint is safe to expose to
    |  Manager+Supervisor+Secretary for dropdown use).
    |--------------------------------------------------------------------------
    */

    // VIEW — Manager, Supervisor, Secretary
    // Staff role sees own profile via a separate scoped route below
    Route::middleware('role:Manager,Supervisor,Secretary')
        ->prefix('staff')
        ->group(function () {
            // ⚠️ Special routes MUST come BEFORE /{id}
            Route::get('/supervisors-for-branch', [StaffController::class, 'getSupervisors']);
            Route::get('/branch/{branchNo}/staff-count', [StaffController::class, 'staffCountByBranch']);
            Route::get('/{id}/supervisor-count',  [StaffController::class, 'supervisorStaffCount']);
            Route::get('/',                        [StaffController::class, 'apiIndex']);
            Route::get('/{id}',                    [StaffController::class, 'apiShow']);
        });

    // FULL CRUD — Manager ONLY
    // RBAC: Create/Edit/Delete/Assign-Branch Staff → Manager ✔ Full, all others ❌
    Route::middleware('role:Manager')
        ->prefix('staff')
        ->group(function () {
            Route::post('/',                   [StaffController::class, 'store']);
            Route::put('/{id}',                [StaffController::class, 'update']);
            Route::delete('/{id}',             [StaffController::class, 'destroy']);
            Route::post('/{id}/assign-branch', [StaffController::class, 'assignToBranch']);
        });

    /*
    |==========================================================================
    | PROPERTIES API
    |==========================================================================
    | RBAC:
    |   Manager    → View/Approve only  (no create/edit/delete)
    |   Supervisor → Full CRUD
    |   Secretary  → Add/Edit/View  (no delete)
    |   Staff      → View Assigned Only (read-only)
    |--------------------------------------------------------------------------
    */

    Route::prefix('properties')->group(function () {

        // VIEW — All roles (Staff can view assigned properties)
        // ⚠️ /search must come BEFORE /{id} to avoid route conflict
        Route::middleware('role:Manager,Supervisor,Secretary,Staff')
            ->get('/search', [PropertyController::class, 'search']);

        Route::middleware('role:Manager,Supervisor,Secretary,Staff')->group(function () {
            Route::get('/',         [PropertyController::class, 'index']);
            Route::get('/renters',  [PropertyController::class, 'renters']);
            Route::get('/staff',    [PropertyController::class, 'staff']);
            Route::get('/{id}',     [PropertyController::class, 'show']);
        });

        // CREATE & EDIT — Supervisor and Secretary
        // RBAC: Secretary ✔ Add/Edit/View, Supervisor ✔ Full CRUD
        Route::middleware('role:Supervisor,Secretary')->group(function () {
            Route::post('/',              [PropertyController::class, 'store']);
            Route::put('/{id}',           [PropertyController::class, 'update']);
            Route::patch('/{id}/status',  [PropertyController::class, 'updateStatus']);
        });

        // DELETE — Supervisor ONLY
        // RBAC: Manager ❌, Secretary ✔ Limited (no delete per property RBAC row), Supervisor ✔ Full
        Route::middleware('role:Supervisor')
            ->delete('/{id}', [PropertyController::class, 'destroy']);
    });

    /*
    |==========================================================================
    | VIEWINGS API
    |==========================================================================
    | RBAC:
    |   Manager    → View/Reports only  (no create/edit/delete)
    |   Supervisor → Full CRUD
    |   Secretary  → Full CRUD (View + Create + Edit + Delete "Limited")
    |   Staff      → Full CRUD (View + Create + Edit + Delete "Limited")
    |--------------------------------------------------------------------------
    |
    | "Delete Limited" for Secretary and Staff means they CAN delete
    | per the RBAC table (✔ Limited under Delete column).
    | Only Manager has NO delete access to viewings.
    |--------------------------------------------------------------------------
    */

    Route::prefix('viewings')->group(function () {

        // VIEW — All roles (Manager is Reports/View only)
        Route::middleware('role:Manager,Supervisor,Secretary,Staff')->group(function () {
            Route::get('/',                           [ViewingController::class, 'index']);
            Route::get('/form-data',                  [ViewingController::class, 'formData']);
            Route::get('/property/{property_no}',     [ViewingController::class, 'byProperty']);
            Route::get('/{property_no}/{renter_no}/{viewing_date}',
                        [ViewingController::class, 'show']);
        });

        // CREATE & EDIT — Supervisor, Secretary, Staff
        // RBAC: Manager ❌ Create/Edit
        Route::middleware('role:Supervisor,Secretary,Staff')->group(function () {
            Route::post('/', [ViewingController::class, 'store']);
            Route::put('/{property_no}/{renter_no}/{viewing_date}',
                        [ViewingController::class, 'update']);
        });

        // DELETE — Supervisor ✔ Full, Secretary ✔ Limited, Staff ✔ Limited
        // Manager has NO delete on viewings per RBAC
        Route::middleware('role:Supervisor,Secretary,Staff')
            ->delete('/{property_no}/{renter_no}/{viewing_date}',
                     [ViewingController::class, 'destroy']);
    });

    /*
    |==========================================================================
    | LEASES API
    |==========================================================================
    | RBAC:
    |   Manager    → Full CRUD
    |   Supervisor → Full CRUD
    |   Secretary  → View/Prepare only  (no create/edit/delete)
    |   Staff      → View Only
    |--------------------------------------------------------------------------
    */

    Route::prefix('leases')->group(function () {

        // VIEW — All roles
        Route::middleware('role:Manager,Supervisor,Secretary,Staff')->group(function () {
            Route::get('/',                         [LeaseController::class, 'index']);
            Route::get('/form-data',                [LeaseController::class, 'formData']);
            Route::get('/property/{property_no}',   [LeaseController::class, 'byProperty']);
            Route::get('/{lease_no}',               [LeaseController::class, 'show']);
        });

        // FULL CRUD — Manager and Supervisor ONLY
        // RBAC: Secretary ❌, Staff ❌ create/edit/delete
        Route::middleware('role:Manager,Supervisor')->group(function () {
            Route::post('/',             [LeaseController::class, 'store']);
            Route::put('/{lease_no}',    [LeaseController::class, 'update']);
            Route::delete('/{lease_no}', [LeaseController::class, 'destroy']);
        });
    });

    /*
    |==========================================================================
    | RENTERS API
    |==========================================================================
    | RBAC:
    |   Manager    → View only
    |   Supervisor → Full CRUD
    |   Secretary  → Full CRUD (Add/Edit/View + Delete per RBAC)
    |   Staff      → View only (Staff has Add Only on the WEB side via the
    |                renter create page, but the API store endpoint is covered
    |                by the web route POST /renter — not duplicated here)
    |--------------------------------------------------------------------------
    |
    | NOTE: Per RBAC "Delete Renter" → Supervisor ✔ Full, Secretary ✔ Full
    |       so both Supervisor and Secretary can delete renters.
    |       Staff = View only on this API (web renter/create uses web route).
    |--------------------------------------------------------------------------
    */

    Route::prefix('renters')->group(function () {

        // VIEW — All roles
        Route::middleware('role:Manager,Supervisor,Secretary,Staff')->group(function () {
            Route::get('/',           [RenterController::class, 'apiIndex']);
            Route::get('/{id}/staff', [RenterController::class, 'getRenterStaff']);
            Route::get('/{id}/logs',  [RenterController::class, 'getLogs']);
            Route::get('/{id}',       [RenterController::class, 'show']);
        });

        // CREATE & EDIT — Supervisor and Secretary
        // RBAC: Staff = Add Only (handled via web route), Manager ❌
        Route::middleware('role:Supervisor,Secretary')->group(function () {
            Route::post('/',    [RenterController::class, 'store']);
            Route::put('/{id}', [RenterController::class, 'update']);
        });

        // DELETE & ASSIGN-STAFF — Supervisor and Secretary
        // RBAC: Manager ❌, Staff ❌
        Route::middleware('role:Supervisor,Secretary')->group(function () {
            Route::delete('/{id}',            [RenterController::class, 'destroy']);
            Route::post('/{id}/assign-staff', [RenterController::class, 'assignStaff']);
        });
    });

    /*
    |==========================================================================
    | OWNERS API
    |==========================================================================
    | RBAC:
    |   Manager    → View only  (no create/edit/delete)
    |   Supervisor → Full CRUD
    |   Secretary  → Full CRUD (Add/Edit/View + Delete per RBAC)
    |   Staff      → No Access ❌
    |--------------------------------------------------------------------------
    */

    Route::prefix('owners')->group(function () {

        // VIEW — Manager, Supervisor, Secretary (Staff has NO access)
        Route::middleware('role:Manager,Supervisor,Secretary')->group(function () {
            Route::get('/',     [OwnerController::class, 'index']);
            Route::get('/{id}', [OwnerController::class, 'show']);
        });

        // FULL CRUD — Supervisor and Secretary
        // RBAC: Manager ❌ create/edit/delete
        Route::middleware('role:Supervisor,Secretary')->group(function () {
            Route::post('/',      [OwnerController::class, 'store']);
            Route::put('/{id}',   [OwnerController::class, 'update']);
            Route::delete('/{id}',[OwnerController::class, 'destroy']);
        });
    });

    /*
    |==========================================================================
    | DROPDOWN HELPERS
    |==========================================================================
    | These are used by JS forms across the system.
    | Kept under /api/dropdown/* for clean separation.
    |--------------------------------------------------------------------------
    */

    Route::prefix('dropdown')->group(function () {

        // Staff dropdown — forms that need staff list
        // Renter create/edit uses this; Secretary can create renters.
        // Staff is excluded here because Staff uses /api/branches/{branch_no}/staff
        // for the staff dropdown when adding a renter (branch-scoped).
        Route::middleware('role:Manager,Supervisor,Secretary')
            ->get('/staff', [StaffController::class, 'apiIndex']);

        // Renters dropdown — viewing and lease forms (all roles that access those)
        Route::middleware('role:Manager,Supervisor,Secretary,Staff')
            ->get('/renters', [RenterController::class, 'apiIndex']);

        // Properties dropdown — lease and viewing forms (all roles that access those)
        Route::middleware('role:Manager,Supervisor,Secretary,Staff')
            ->get('/properties', [PropertyController::class, 'index']);
    });

});