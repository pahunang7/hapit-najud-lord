<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RenterController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\BranchOfficeController;
use App\Http\Controllers\StaffController;

/*
|--------------------------------------------------------------------------
| ROOT REDIRECT — role-based
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    $role = strtolower(auth()->user()->job_title ?? '');

    if (str_contains($role, 'manager'))    return redirect()->route('manager.dashboard');
    if (str_contains($role, 'supervisor')) return redirect()->route('supervisor.dashboard');
    if (str_contains($role, 'secretary'))  return redirect()->route('secretary.dashboard');

    return redirect()->route('staff.dashboard');
});

/*
|--------------------------------------------------------------------------
| AUTHENTICATION
|--------------------------------------------------------------------------
*/

Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout',[LoginController::class, 'logout'])->name('logout');

/*
|==========================================================================
| DASHBOARDS
|==========================================================================
| RBAC:
|   Manager    → ✔ Full
|   Supervisor → ✔ Full
|   Secretary  → ✔ Limited
|   Staff      → ✔ Limited
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:Manager'])->group(function () {
    Route::get('/dashboard/manager', [DashboardController::class, 'index'])
        ->name('manager.dashboard');
});

Route::middleware(['auth', 'role:Manager,Supervisor'])->group(function () {
    Route::get('/dashboard/supervisor', [DashboardController::class, 'index'])
        ->name('supervisor.dashboard');
});

Route::middleware(['auth', 'role:Manager,Secretary'])->group(function () {
    Route::get('/dashboard/secretary', [DashboardController::class, 'index'])
        ->name('secretary.dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard/staff', [DashboardController::class, 'index'])
        ->name('staff.dashboard');
});

/*
|==========================================================================
| VIEWINGS
|==========================================================================
| RBAC:
|   Manager    → View/Reports only  (blade hides add/edit/delete buttons)
|   Supervisor → Full CRUD
|   Secretary  → Full CRUD (including limited delete)
|   Staff      → Full CRUD (including limited delete)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:Manager,Supervisor,Secretary,Staff'])->group(function () {
    Route::view('/viewings', 'viewings.index')->name('viewings.index');
});

/*
|==========================================================================
| LEASES
|==========================================================================
| RBAC:
|   Manager    → Full CRUD
|   Supervisor → Full CRUD
|   Secretary  → View/Prepare only (blade hides add/edit/delete buttons)
|   Staff      → View only         (blade hides all action buttons)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:Manager,Supervisor,Secretary,Staff'])->group(function () {
    Route::view('/leases', 'leases.index')->name('leases.index');
});

/*
|==========================================================================
| PROPERTY MODULE
|==========================================================================
| RBAC:
|   Manager    → View/Approve only  (no add/edit/delete buttons in blade)
|   Supervisor → Full CRUD
|   Secretary  → Add/Edit/View      (no delete button in blade)
|   Staff      → View Assigned only
|--------------------------------------------------------------------------
*/

// VIEW — All roles can see the property list page
Route::middleware(['auth', 'role:Manager,Supervisor,Secretary,Staff'])->group(function () {

    Route::get('/properties', [PropertyController::class, 'webIndex'])
        ->name('properties.index');

    // ⚠️ /properties/search must be defined BEFORE /properties/{id}
    //    to avoid route conflict.
    Route::get('/properties/search', function () {
        return view('properties.search');
    })->name('properties.search');
});

// CREATE & EDIT — Supervisor and Secretary
// RBAC: Manager ❌, Staff ❌
Route::middleware(['auth', 'role:Supervisor,Secretary'])->group(function () {
    Route::post('/properties',     [PropertyController::class, 'store'])
        ->name('properties.store');
    Route::put('/properties/{id}', [PropertyController::class, 'update'])
        ->name('properties.update');
});

// DELETE — Supervisor ONLY
// RBAC: Manager ❌, Secretary ✔ Limited (no delete per property RBAC row), Staff ❌
Route::middleware(['auth', 'role:Supervisor'])->group(function () {
    Route::delete('/properties/{id}', [PropertyController::class, 'destroy'])
        ->name('properties.destroy');
});

/*
|==========================================================================
| RENTER / CLIENT MODULE
|==========================================================================
| RBAC:
|   Manager    → View only
|   Supervisor → Full CRUD
|   Secretary  → Full CRUD (Add/Edit/Delete)
|   Staff      → View only + Add Only (Create Renter per RBAC)
|--------------------------------------------------------------------------
*/

Route::prefix('renter')->middleware(['auth'])->group(function () {

    // VIEW — All roles
    Route::middleware('role:Manager,Supervisor,Secretary,Staff')->group(function () {

        Route::get('/', [RenterController::class, 'index'])
            ->name('renter.index');

        Route::get('/search', function () {
            return view('renter.search');
        })->name('renter.search');

        // ⚠️ /{id} must come AFTER named sub-routes like /create and /search
        Route::get('/{id}', [RenterController::class, 'show'])
            ->name('renter.show');
    });

    // CREATE — Supervisor, Secretary, Staff
    // RBAC: Staff = Add Only ✔, Manager ❌
    Route::middleware('role:Supervisor,Secretary,Staff')->group(function () {
        Route::get('/create', [RenterController::class, 'create'])
            ->name('renter.create');
        Route::post('/', [RenterController::class, 'store'])
            ->name('renter.store');
    });

    // EDIT — Supervisor and Secretary only
    // RBAC: Staff ❌, Manager ❌
    Route::middleware('role:Supervisor,Secretary')->group(function () {
        Route::get('/{id}/edit', [RenterController::class, 'edit'])
            ->name('renter.edit');
        Route::put('/{id}', [RenterController::class, 'update'])
            ->name('renter.update');
    });

    // DELETE — Supervisor and Secretary (both Full per RBAC)
    // RBAC: Manager ❌, Staff ❌
    Route::middleware('role:Supervisor,Secretary')->group(function () {
        Route::delete('/{id}', [RenterController::class, 'destroy'])
            ->name('renter.destroy');
    });
});

/*
|==========================================================================
| OWNER MODULE
|==========================================================================
| RBAC:
|   Manager    → View only
|   Supervisor → Full CRUD
|   Secretary  → Full CRUD (Add/Edit/Delete per RBAC)
|   Staff      → No Access ❌
|--------------------------------------------------------------------------
*/

Route::prefix('owners')->middleware(['auth'])->group(function () {

    // VIEW — Manager, Supervisor, Secretary (Staff has NO access)
    Route::middleware('role:Manager,Supervisor,Secretary')->group(function () {
        Route::get('/',     [OwnerController::class, 'index'])->name('owners.index');
        Route::get('/{id}', [OwnerController::class, 'show'])->name('owners.show');
    });

    // FULL CRUD — Supervisor and Secretary
    // RBAC: Manager ❌ create/edit/delete
    Route::middleware('role:Supervisor,Secretary')->group(function () {
        Route::get('/create',    [OwnerController::class, 'create'])->name('owners.create');
        Route::post('/',         [OwnerController::class, 'store'])->name('owners.store');
        Route::get('/{id}/edit', [OwnerController::class, 'edit'])->name('owners.edit');
        Route::put('/{id}',      [OwnerController::class, 'update'])->name('owners.update');
        Route::delete('/{id}',   [OwnerController::class, 'destroy'])->name('owners.destroy');
    });
});

/*
|==========================================================================
| BRANCH MANAGEMENT
|==========================================================================
| RBAC:
|   Manager    → Full CRUD + Reports
|   Supervisor → View only
|   Secretary  → View only
|   Staff      → View only (needed for branch context in their forms)
|--------------------------------------------------------------------------
|
| ⚠️ FIX: RBAC lists Staff as 👁 View for Branch List and Branch Details.
|         Original routes excluded Staff from branch web pages.
|         Staff added to VIEW group so their branch context pages work.
|--------------------------------------------------------------------------
*/

// VIEW — Manager, Supervisor, Secretary, Staff
/*
|--------------------------------------------------------------------------
| BRANCH MANAGEMENT
|--------------------------------------------------------------------------
*/

// =========================================================
// VIEW — Manager, Supervisor, Secretary, Staff
// =========================================================

Route::middleware(['auth', 'role:Manager,Supervisor,Secretary,Staff'])
    ->prefix('branches')
    ->name('branch.')
    ->group(function () {

        // STATIC ROUTES FIRST
        Route::get('/', [BranchOfficeController::class, 'index'])
            ->name('index');

        // MANAGER PAGES
        Route::middleware('role:Manager')->group(function () {

            Route::get('/create', [BranchOfficeController::class, 'create'])
                ->name('create');
        });

        // DYNAMIC SUB-ROUTES
        Route::get('/{branchOffice}/data', [BranchOfficeController::class, 'apiShow']);

        Route::get('/{branchOffice}/staff', [BranchOfficeController::class, 'staffPage'])
            ->name('staff');

        Route::middleware('role:Manager')->group(function () {

            Route::get('/{branchOffice}/edit', [BranchOfficeController::class, 'edit'])
                ->name('edit');
        });

        // MAIN SHOW ROUTE LAST
        Route::get('/{branchOffice}', [BranchOfficeController::class, 'showPage'])
            ->name('show');
    });

// =========================================================
// CRUD — Manager ONLY
// =========================================================

Route::middleware(['auth', 'role:Manager'])
    ->prefix('branches')
    ->name('branch.')
    ->group(function () {

        Route::post('/', [BranchOfficeController::class, 'store'])
            ->name('store');

        Route::put('/{branchOffice}', [BranchOfficeController::class, 'update'])
            ->name('update');

        Route::delete('/{branchOffice}', [BranchOfficeController::class, 'destroy'])
            ->name('destroy');
    });

/*
|==========================================================================
| STAFF MODULE
|==========================================================================
| RBAC:
|   Manager    → Full CRUD
|   Supervisor → View Supervised Team (own branch, enforced in controller)
|   Secretary  → View Only
|   Staff      → View Own Profile only (enforced in controller)
|--------------------------------------------------------------------------
|
| ⚠️ FIX: RBAC lists Staff as 👁 View Own Profile.
|         Original routes excluded Staff entirely from staff web pages.
|         Staff added to VIEW group; controller enforces own-profile-only scope.
|--------------------------------------------------------------------------
*/

// VIEW — Manager, Supervisor, Secretary, Staff
/*
|--------------------------------------------------------------------------
| STAFF MODULE
|--------------------------------------------------------------------------
*/

// =====================================================
// VIEW — Manager, Supervisor, Secretary, Staff
// =====================================================

Route::middleware(['auth', 'role:Manager,Supervisor,Secretary,Staff'])
    ->prefix('staff')
    ->name('staff.')
    ->group(function () {

        // STATIC ROUTES FIRST
        Route::get('/', [StaffController::class, 'index'])
            ->name('index');

        Route::get('/supervisors', [StaffController::class, 'supervisorList'])
            ->name('supervisor.list');

        Route::get('/branch/{branchNo}/report', [StaffController::class, 'branchReport'])
            ->name('branch.report');

        // MANAGER ONLY ROUTES
        Route::middleware('role:Manager')->group(function () {

            Route::get('/create', [StaffController::class, 'create'])
                ->name('create');
            
            Route::put('/api/staff/{id}', [StaffController::class, 'update']);

        });

        // DYNAMIC SUB-ROUTES
        Route::get('/{id}/subordinates', [StaffController::class, 'subordinates'])
            ->name('subordinates');

        Route::middleware('role:Manager')->group(function () {

            Route::get('/{id}/edit', [StaffController::class, 'edit'])
                ->name('edit');
        });

        // MAIN DYNAMIC ROUTE LAST
        Route::get('/{id}', [StaffController::class, 'showPage'])
            ->name('show');
    });

// =====================================================
// FULL CRUD — Manager ONLY
// =====================================================

Route::middleware(['auth', 'role:Manager'])
    ->prefix('staff')
    ->name('staff.')
    ->group(function () {

        Route::post('/', [StaffController::class, 'store'])
            ->name('store');

        Route::put('/{id}', [StaffController::class, 'update'])
            ->name('update');

        Route::delete('/{id}', [StaffController::class, 'destroy'])
            ->name('destroy');

        Route::post('/{id}/assign-branch', [StaffController::class, 'assignToBranch'])
            ->name('assign.branch');
    });

/*
|==========================================================================
| STAFF & DROPDOWN API HELPERS (web-accessible via JS fetch)
|==========================================================================
| These routes mirror the API routes but sit in web.php because
| the forms (blade views) call them via fetch() using session auth.
|
| ⚠️ FIX: Staff role needs /api/branches/{branch_no}/staff for the staff
|         dropdown when creating a renter (Staff = Add Only per RBAC).
|         Opening /api/branch staff helper to all roles that create renters.
|
| ⚠️ FIX: Staff web pages for branches and staff profile need branch/staff
|         API helpers for JS calls — added Staff where appropriate below.
|--------------------------------------------------------------------------
*/

// Manager, Supervisor, Secretary — staff list and supervisor helpers
// (Staff cannot view full staff lists; they see own profile only via controller scope)
Route::middleware(['auth', 'role:Manager,Supervisor,Secretary'])->group(function () {

    Route::get('/api/staff', [StaffController::class, 'apiIndex']);

    Route::get('/api/staff/{id}', [StaffController::class, 'apiShow']);

    // supervisors-for-branch: used in staff create/edit form (Manager creates staff,
    // but endpoint safely exposed to Manager+Supervisor+Secretary for dropdown use)
    Route::get('/api/supervisors', [StaffController::class, 'getSupervisors']);

    Route::get('/api/staff/supervisors-for-branch', [StaffController::class, 'getSupervisors']);

    Route::get('/api/branch/{branchNo}/staff-count', [StaffController::class, 'staffCountByBranch']);

    Route::get('/api/supervisor/{supervisorNo}/count', [StaffController::class, 'supervisorStaffCount']);
});

// Staff also needs the branch staff endpoint to populate the assigned staff
// dropdown when creating a renter (Staff = Add Only per RBAC).
// This is intentionally separate and scoped to the minimum needed.
Route::middleware(['auth', 'role:Manager,Supervisor,Secretary,Staff'])->group(function () {

    Route::get('/api/branches/{branch_no}/staff', [BranchOfficeController::class, 'getStaff']);
});