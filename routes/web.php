<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RenterController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\ViewingController;
use App\Http\Controllers\Api\LeaseController;
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
*/

Route::middleware(['auth', 'role:Manager,Supervisor,Secretary,Staff'])->group(function () {
    Route::view('/viewings', 'viewings.index')->name('viewings.index');
});

/*
|==========================================================================
| LEASES
|==========================================================================
*/

Route::middleware(['auth', 'role:Manager,Supervisor,Secretary,Staff'])->group(function () {
    Route::view('/leases', 'leases.index')->name('leases.index');
});

/*
|==========================================================================
| PROPERTY MODULE
|==========================================================================
*/

Route::middleware(['auth', 'role:Manager,Supervisor,Secretary,Staff'])->group(function () {

    Route::get('/properties', [PropertyController::class, 'webIndex'])
        ->name('properties.index');

    Route::get('/properties/search', function () {
        return view('properties.search');
    })->name('properties.search');
});

Route::middleware(['auth', 'role:Supervisor,Secretary'])->group(function () {
    Route::post('/properties',     [PropertyController::class, 'store'])
        ->name('properties.store');
    Route::put('/properties/{id}', [PropertyController::class, 'update'])
        ->name('properties.update');
});

Route::middleware(['auth', 'role:Supervisor'])->group(function () {
    Route::delete('/properties/{id}', [PropertyController::class, 'destroy'])
        ->name('properties.destroy');
});

/*
|==========================================================================
| RENTER / CLIENT MODULE
|==========================================================================
*/

Route::prefix('renter')->middleware(['auth'])->group(function () {

    // ── STATIC & SUB-ROUTES FIRST ──────────────────────────────
    Route::middleware('role:Manager,Supervisor,Secretary,Staff')->group(function () {
        Route::get('/', [RenterController::class, 'index'])->name('renter.index');
        Route::get('/search', function () {
            return view('renter.search');
        })->name('renter.search');
    });

    // /create must come before /{id}
    Route::middleware('role:Supervisor,Secretary,Staff')->group(function () {
        Route::get('/create', [RenterController::class, 'create'])->name('renter.create');
        Route::post('/', [RenterController::class, 'store'])->name('renter.store');
    });

    // /{id}/edit must come before /{id}
    Route::middleware('role:Supervisor,Secretary')->group(function () {
        Route::get('/{id}/edit', [RenterController::class, 'edit'])->name('renter.edit');
        Route::put('/{id}', [RenterController::class, 'update'])->name('renter.update');
        Route::delete('/{id}', [RenterController::class, 'destroy'])->name('renter.destroy');
    });

    // ── DYNAMIC CATCH-ALL LAST ─────────────────────────────────
    Route::middleware('role:Manager,Supervisor,Secretary,Staff')->group(function () {
        Route::get('/{id}', [RenterController::class, 'show'])->name('renter.show');
    });
});

/*
|==========================================================================
| OWNER MODULE
|==========================================================================
*/

Route::prefix('owners')->middleware(['auth'])->group(function () {

    Route::middleware('role:Manager,Supervisor,Secretary')->group(function () {
        Route::get('/',     [OwnerController::class, 'index'])->name('owners.index');
        Route::get('/{id}', [OwnerController::class, 'show'])->name('owners.show');
    });

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
*/

Route::middleware(['auth', 'role:Manager,Supervisor,Secretary,Staff'])
    ->prefix('branches')
    ->name('branch.')
    ->group(function () {

        Route::get('/', [BranchOfficeController::class, 'index'])
            ->name('index');

        Route::middleware('role:Manager')->group(function () {
            Route::get('/create', [BranchOfficeController::class, 'create'])
                ->name('create');
        });

        Route::get('/{branchOffice}/data', [BranchOfficeController::class, 'apiShow']);

        Route::get('/{branchOffice}/staff', [BranchOfficeController::class, 'staffPage'])
            ->name('staff');

        Route::middleware('role:Manager')->group(function () {
            Route::get('/{branchOffice}/edit', [BranchOfficeController::class, 'edit'])
                ->name('edit');
        });

        Route::get('/{branchOffice}', [BranchOfficeController::class, 'showPage'])
            ->name('show');
    });

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
*/

Route::middleware(['auth', 'role:Manager,Supervisor,Secretary,Staff'])
    ->prefix('staff')
    ->name('staff.')
    ->group(function () {

        Route::get('/', [StaffController::class, 'index'])
            ->name('index');

        Route::get('/supervisors', [StaffController::class, 'supervisorList'])
            ->name('supervisor.list');

        Route::get('/branch/{branchNo}/report', [StaffController::class, 'branchReport'])
            ->name('branch.report');

        Route::middleware('role:Manager')->group(function () {
            Route::get('/create', [StaffController::class, 'create'])
                ->name('create');
        });

        Route::get('/{id}/subordinates', [StaffController::class, 'subordinates'])
            ->name('subordinates');

        Route::middleware('role:Manager')->group(function () {
            Route::get('/{id}/edit', [StaffController::class, 'edit'])
                ->name('edit');
        });

        Route::get('/{id}', [StaffController::class, 'showPage'])
            ->name('show');
    });

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
| WEB-ACCESSIBLE API HELPERS (called via JS fetch using session auth)
|==========================================================================
|
| These mirror the api.php routes but live in web.php so that blade
| views can call them via fetch() using cookie/session authentication
| instead of token auth (which api.php requires).
|--------------------------------------------------------------------------
*/

// -----------------------------------------------------------------------
// STAFF API HELPERS — READ (Manager, Supervisor, Secretary)
// -----------------------------------------------------------------------
Route::middleware(['auth', 'role:Manager,Supervisor,Secretary'])->group(function () {

    Route::get('/api/staff', [StaffController::class, 'apiIndex']);

    // ⚠️ /supervisors-for-branch MUST come BEFORE /api/staff/{id}
    //    to avoid Laravel matching "supervisors-for-branch" as an {id}
    Route::get('/api/staff/supervisors-for-branch', [StaffController::class, 'getSupervisors']);

    Route::get('/api/staff/{id}', [StaffController::class, 'apiShow']);

    Route::get('/api/supervisors', [StaffController::class, 'getSupervisors']);

    Route::get('/api/branch/{branchNo}/staff-count', [StaffController::class, 'staffCountByBranch']);

    Route::get('/api/supervisor/{supervisorNo}/count', [StaffController::class, 'supervisorStaffCount']);
});

// -----------------------------------------------------------------------
// STAFF API HELPERS — WRITE (Manager only)
// -----------------------------------------------------------------------
Route::middleware(['auth', 'role:Manager'])->group(function () {

    Route::post('/api/staff',                    [StaffController::class, 'store']);
    Route::put('/api/staff/{id}',                [StaffController::class, 'update']);
    Route::delete('/api/staff/{id}',             [StaffController::class, 'destroy']);
    Route::post('/api/staff/{id}/assign-branch', [StaffController::class, 'assignToBranch']);
});

// -----------------------------------------------------------------------
// BRANCH STAFF HELPER — All roles that create renters need this
// -----------------------------------------------------------------------
Route::middleware(['auth', 'role:Manager,Supervisor,Secretary,Staff'])->group(function () {

    Route::get('/api/branches/{branch_no}/staff', [BranchOfficeController::class, 'getStaff']);
});

// -----------------------------------------------------------------------
// VIEWINGS API HELPERS — READ (all roles)
// -----------------------------------------------------------------------
Route::middleware(['auth', 'role:Manager,Supervisor,Secretary,Staff'])->group(function () {

    Route::get('/api/viewings', [ViewingController::class, 'index']);

    // ⚠️ /form-data MUST come BEFORE the composite /{property_no}/{renter_no}/{viewing_date}
    //    route to avoid Laravel treating "form-data" as a {property_no} parameter
    Route::get('/api/viewings/form-data', [ViewingController::class, 'formData']);

    Route::get('/api/viewings/property/{property_no}', [ViewingController::class, 'byProperty']);

    Route::get('/api/viewings/{property_no}/{renter_no}/{viewing_date}', [ViewingController::class, 'show']);
});

// -----------------------------------------------------------------------
// VIEWINGS API HELPERS — WRITE (Supervisor, Secretary, Staff)
// Manager has no create/edit/delete on viewings per RBAC
// -----------------------------------------------------------------------
Route::middleware(['auth', 'role:Supervisor,Secretary,Staff'])->group(function () {

    Route::post('/api/viewings', [ViewingController::class, 'store']);

    Route::put('/api/viewings/{property_no}/{renter_no}/{viewing_date}',    [ViewingController::class, 'update']);

    Route::delete('/api/viewings/{property_no}/{renter_no}/{viewing_date}', [ViewingController::class, 'destroy']);
});

// -----------------------------------------------------------------------
// LEASES API HELPERS — READ (all roles)
// -----------------------------------------------------------------------
Route::middleware(['auth', 'role:Manager,Supervisor,Secretary,Staff'])->group(function () {

    Route::get('/api/leases', [LeaseController::class, 'index']);

    // ⚠️ /form-data MUST come BEFORE /{lease_no} to avoid route conflict
    Route::get('/api/leases/form-data', [LeaseController::class, 'formData']);

    Route::get('/api/leases/property/{property_no}', [LeaseController::class, 'byProperty']);

    Route::get('/api/leases/{lease_no}', [LeaseController::class, 'show']);
});

// -----------------------------------------------------------------------
// LEASES API HELPERS — WRITE (Manager and Supervisor only)
// Secretary and Staff have no create/edit/delete on leases per RBAC
// -----------------------------------------------------------------------
Route::middleware(['auth', 'role:Manager,Supervisor'])->group(function () {

    Route::post('/api/leases',             [LeaseController::class, 'store']);
    Route::put('/api/leases/{lease_no}',   [LeaseController::class, 'update']);
    Route::delete('/api/leases/{lease_no}',[LeaseController::class, 'destroy']);
});

// -----------------------------------------------------------------------
// RENTERS API HELPERS — READ (all roles)
// -----------------------------------------------------------------------
Route::middleware(['auth', 'role:Manager,Supervisor,Secretary,Staff'])->group(function () {

    Route::get('/api/renters',          [RenterController::class, 'apiIndex']);
    Route::get('/api/renters/{id}',     [RenterController::class, 'show']);
    Route::get('/api/renters/{id}/staff', [RenterController::class, 'getRenterStaff']);
    Route::get('/api/renters/{id}/logs',  [RenterController::class, 'getLogs']);
});

// -----------------------------------------------------------------------
// RENTERS API HELPERS — WRITE (Supervisor and Secretary only)
// Manager and Staff have no create/edit/delete on renters via API per RBAC
// -----------------------------------------------------------------------
Route::middleware(['auth', 'role:Supervisor,Secretary'])->group(function () {

    Route::post('/api/renters',              [RenterController::class, 'store']);
    Route::put('/api/renters/{id}',          [RenterController::class, 'update']);
    Route::delete('/api/renters/{id}',       [RenterController::class, 'destroy']);
    Route::post('/api/renters/{id}/assign-staff', [RenterController::class, 'assignStaff']);
});

// -----------------------------------------------------------------------
// PROPERTIES API HELPERS — READ (all roles)
// -----------------------------------------------------------------------
Route::middleware(['auth', 'role:Manager,Supervisor,Secretary,Staff'])->group(function () {

    Route::get('/api/properties',         [PropertyController::class, 'index']);

    // ⚠️ /search MUST come BEFORE /{id} to avoid route conflict
    Route::get('/api/properties/search',  [PropertyController::class, 'search']);
    Route::get('/api/properties/renters', [PropertyController::class, 'renters']);
    Route::get('/api/properties/staff',   [PropertyController::class, 'staff']);

    Route::get('/api/properties/{id}',    [PropertyController::class, 'show']);
});

// -----------------------------------------------------------------------
// PROPERTIES API HELPERS — WRITE (Supervisor and Secretary)
// -----------------------------------------------------------------------
Route::middleware(['auth', 'role:Supervisor,Secretary'])->group(function () {

    Route::post('/api/properties',            [PropertyController::class, 'store']);
    Route::put('/api/properties/{id}',        [PropertyController::class, 'update']);
    Route::patch('/api/properties/{id}/status',[PropertyController::class, 'updateStatus']);
});

// -----------------------------------------------------------------------
// PROPERTIES API HELPERS — DELETE (Supervisor only)
// -----------------------------------------------------------------------
Route::middleware(['auth', 'role:Supervisor'])->group(function () {

    Route::delete('/api/properties/{id}', [PropertyController::class, 'destroy']);
});

// -----------------------------------------------------------------------
// BRANCHES API HELPERS — READ (all roles)
// -----------------------------------------------------------------------
Route::middleware(['auth', 'role:Manager,Supervisor,Secretary,Staff'])->group(function () {

    Route::get('/api/branches',            [BranchOfficeController::class, 'apiIndex']);
    Route::get('/api/branches/{branchOffice}/staff-count', [BranchOfficeController::class, 'staffCount']);
    Route::get('/api/branches/{branchOffice}/manager',     [BranchOfficeController::class, 'manager']);
    Route::get('/api/branches/{branchOffice}',             [BranchOfficeController::class, 'apiShow']);
});

// WRITE — Manager only
Route::middleware(['auth', 'role:Manager'])->group(function () {

    Route::post('/api/branches',                [BranchOfficeController::class, 'store']);
    Route::put('/api/branches/{branchOffice}',  [BranchOfficeController::class, 'update']);
    Route::delete('/api/branches/{branchOffice}',[BranchOfficeController::class, 'destroy']);
});