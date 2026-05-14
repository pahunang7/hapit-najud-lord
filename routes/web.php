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
| ROOT REDIRECT
|--------------------------------------------------------------------------
*/

Route::get('/', function () {

    if (!auth()->check()) {
        return redirect()->route('login');
    }

    $role = strtolower(auth()->user()->job_title);

    if (str_contains($role, 'manager')) {
        return redirect()->route('manager.dashboard');
    }

    if (str_contains($role, 'supervisor')) {
        return redirect()->route('supervisor.dashboard');
    }

    if (str_contains($role, 'secretary')) {
        return redirect()->route('secretary.dashboard');
    }

    return redirect()->route('staff.dashboard');
});

/*
|--------------------------------------------------------------------------
| AUTHENTICATION
|--------------------------------------------------------------------------
*/

Route::get('/login', [LoginController::class, 'showLoginForm'])
    ->name('login');

Route::post('/login', [LoginController::class, 'login']);

Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout');

/*
|--------------------------------------------------------------------------
| DASHBOARDS
|--------------------------------------------------------------------------
*/

// MANAGER DASHBOARD
Route::middleware(['auth', 'role:Manager'])
->group(function () {

    Route::get('/dashboard/manager', [DashboardController::class, 'index'])
        ->name('manager.dashboard');
});

// SUPERVISOR DASHBOARD
Route::middleware(['auth', 'role:Manager,Supervisor'])
->group(function () {

    Route::get('/dashboard/supervisor', [DashboardController::class, 'index'])
        ->name('supervisor.dashboard');
});

// SECRETARY DASHBOARD
Route::middleware(['auth', 'role:Manager,Secretary'])
->group(function () {

    Route::get('/dashboard/secretary', [DashboardController::class, 'index'])
        ->name('secretary.dashboard');
});

// STAFF DASHBOARD
Route::middleware(['auth'])
->group(function () {

    Route::get('/dashboard/staff', [DashboardController::class, 'index'])
        ->name('staff.dashboard');
});

/*
|--------------------------------------------------------------------------
| VIEWINGS + LEASES
|--------------------------------------------------------------------------
| Manager + Supervisor + Secretary
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:Manager,Supervisor,Secretary'])
->group(function () {

    Route::view('/viewings', 'viewings.index')
        ->name('viewings.index');

    Route::view('/leases', 'leases.index')
        ->name('leases.index');
});

/*
|--------------------------------------------------------------------------
| PROPERTY MODULE
|--------------------------------------------------------------------------
| Manager + Supervisor
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:Manager,Supervisor'])
->group(function () {

    Route::get('/properties', [PropertyController::class, 'webIndex'])
        ->name('properties.index');

    Route::post('/properties', [PropertyController::class, 'store'])
        ->name('properties.store');

    Route::put('/properties/{id}', [PropertyController::class, 'update'])
        ->name('properties.update');

    Route::get('/properties/search', function () {
        return view('properties.search');
    })->name('properties.search');
});

/*
|--------------------------------------------------------------------------
| PROPERTY DELETE
|--------------------------------------------------------------------------
| Manager ONLY
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:Manager'])
->group(function () {

    Route::delete('/properties/{id}', [PropertyController::class, 'destroy'])
        ->name('properties.destroy');
});

/*
|--------------------------------------------------------------------------
| RENTER / CLIENT MODULE
|--------------------------------------------------------------------------
*/

Route::prefix('renter')
->middleware(['auth'])
->group(function () {

    /*
    |--------------------------------------------------------------------------
    | VIEW ACCESS
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:Manager,Supervisor,Secretary')
    ->group(function () {

        Route::get('/', [RenterController::class, 'index'])
            ->name('renter.index');

        Route::get('/search', function () {
            return view('renter.search');
        })->name('renter.search');
    });

    /*
    |--------------------------------------------------------------------------
    | CRUD ACCESS
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:Manager,Secretary')
    ->group(function () {

        Route::get('/create', [RenterController::class, 'create'])
            ->name('renter.create');

        Route::post('/', [RenterController::class, 'store'])
            ->name('renter.store');

        Route::get('/{id}/edit', [RenterController::class, 'edit'])
            ->name('renter.edit');

        Route::put('/{id}', [RenterController::class, 'update'])
            ->name('renter.update');

        Route::delete('/{id}', [RenterController::class, 'destroy'])
            ->name('renter.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | VIEW SINGLE RENTER
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:Manager,Supervisor,Secretary')
    ->group(function () {

        Route::get('/{id}', [RenterController::class, 'show'])
            ->name('renter.show');
    });


    
});

/*
|--------------------------------------------------------------------------
| OWNER MODULE
|--------------------------------------------------------------------------
*/

Route::prefix('owners')
->middleware(['auth'])
->group(function () {

    /*
    |--------------------------------------------------------------------------
    | MANAGER + SUPERVISOR
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:Manager,Supervisor')
    ->group(function () {

        Route::get('/', [OwnerController::class, 'index'])
            ->name('owners.index');

        Route::get('/create', [OwnerController::class, 'create'])
            ->name('owners.create');

        Route::post('/', [OwnerController::class, 'store'])
            ->name('owners.store');

        Route::get('/{id}', [OwnerController::class, 'show'])
            ->name('owners.show');

        Route::get('/{id}/edit', [OwnerController::class, 'edit'])
            ->name('owners.edit');

        Route::put('/{id}', [OwnerController::class, 'update'])
            ->name('owners.update');
    });

    /*
    |--------------------------------------------------------------------------
    | MANAGER ONLY
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:Manager')
    ->group(function () {

        Route::delete('/{id}', [OwnerController::class, 'destroy'])
            ->name('owners.destroy');
    });
});

/*
|--------------------------------------------------------------------------
| BRANCH MANAGEMENT
|--------------------------------------------------------------------------
| VIEW ACCESS: Manager + Supervisor
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:Manager,Supervisor'])
->prefix('branches')
->name('branch.')
->group(function () {

    // VIEW ALL BRANCHES
    Route::get('/', [BranchOfficeController::class, 'index'])
        ->name('index');

    // VIEW SINGLE BRANCH
    Route::get('/{branchOffice}', [BranchOfficeController::class, 'showPage'])
        ->name('show');

    // VIEW BRANCH STAFF
    Route::get('/{branchOffice}/staff', [BranchOfficeController::class, 'staffPage'])
        ->name('staff');
});

/*
|--------------------------------------------------------------------------
| BRANCH CRUD
|--------------------------------------------------------------------------
| Manager ONLY
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:Manager'])
->prefix('branches')
->name('branch.')
->group(function () {

    Route::get('/create', [BranchOfficeController::class, 'create'])
        ->name('create');

    Route::post('/', [BranchOfficeController::class, 'store'])
        ->name('store');

    Route::get('/{branchOffice}/edit', [BranchOfficeController::class, 'edit'])
        ->name('edit');

    Route::put('/{branchOffice}', [BranchOfficeController::class, 'update'])
        ->name('update');

    Route::delete('/{branchOffice}', [BranchOfficeController::class, 'destroy'])
        ->name('destroy');
});

/*
|--------------------------------------------------------------------------
| STAFF MODULE
|--------------------------------------------------------------------------
| VIEW ACCESS: Manager + Supervisor
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:Manager,Supervisor'])
->prefix('staff')
->name('staff.')
->group(function () {

    // STAFF LIST
    Route::get('/', [StaffController::class, 'index'])
        ->name('index');

    // SUPERVISOR LIST
    Route::get('/supervisors', [StaffController::class, 'supervisorList'])
        ->name('supervisor.list');

    // BRANCH REPORT
    Route::get('/branch/{branchNo}/report', [StaffController::class, 'branchReport'])
        ->name('branch.report');

    // SUBORDINATES
    Route::get('/{id}/subordinates', [StaffController::class, 'subordinates'])
        ->name('subordinates');

    // STAFF DETAILS
    Route::get('/{id}', [StaffController::class, 'showPage'])
        ->name('show');
});

/*
|--------------------------------------------------------------------------
| STAFF CRUD
|--------------------------------------------------------------------------
| Manager ONLY
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:Manager'])
->prefix('staff')
->name('staff.')
->group(function () {

    Route::get('/create', [StaffController::class, 'create'])
        ->name('create');

    Route::post('/', [StaffController::class, 'store'])
        ->name('store');

    Route::get('/{id}/edit', [StaffController::class, 'edit'])
        ->name('edit');

    Route::put('/{id}', [StaffController::class, 'update'])
        ->name('update');

    Route::delete('/{id}', [StaffController::class, 'destroy'])
        ->name('destroy');

    Route::post('/{id}/assign-branch', [StaffController::class, 'assignToBranch'])
        ->name('assign.branch');
});

/*
|--------------------------------------------------------------------------
| STAFF API HELPERS
|--------------------------------------------------------------------------
| Manager + Supervisor
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:Manager,Supervisor'])
->group(function () {

    Route::get('/api/staff', [StaffController::class, 'apiIndex']);

    Route::get('/api/staff/{id}', [StaffController::class, 'apiShow']);

    Route::get('/api/supervisors', [StaffController::class, 'getSupervisors']);

    Route::get('/api/branch/{branchNo}/staff-count', [StaffController::class, 'staffCountByBranch']);

    Route::get('/api/supervisor/{supervisorNo}/count', [StaffController::class, 'supervisorStaffCount']);
});