<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RenterController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\BranchOfficeController;
use App\Http\Controllers\StaffController;

// ── Public ──────────────────────────────────────────────
Route::get('/', function () {

    if (!auth()->check()) {
        return redirect()->route('login');
    }

    $role = auth()->user()->job_title;

    if ($role === 'Manager') {
        return redirect()->route('manager.dashboard');
    }

    if ($role === 'Supervisor') {
        return redirect()->route('supervisor.dashboard');
    }

    return redirect()->route('staff.dashboard');
});
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ── Manager only (operations a, b, p, q) ────────────────
Route::middleware(['auth', 'role:Manager'])->group(function () {
    Route::get('/manager/dashboard', [DashboardController::class, 'index'])
    ->name('manager.dashboard');

    // Your existing StaffController routes (operation a = CRUD, b = report)
    Route::resource('staff', StaffController::class);
    Route::get('staff/{id}/page',        [StaffController::class, 'showPage'])->name('staff.page');
    Route::get('staff/{id}/edit-page',   [StaffController::class, 'edit'])->name('staff.edit.page');
    Route::get('branch/{branchNo}/report', [StaffController::class, 'branchReport'])->name('staff.branch.report');
    Route::post('staff/{id}/assign-branch', [StaffController::class, 'assignToBranch'])->name('staff.assign.branch');
});

// ── Manager + Supervisor (operations c, d, e, h, n, o, s) ──
Route::middleware(['auth', 'role:Manager,Supervisor'])->group(function () {
    Route::get('/supervisor/dashboard', [DashboardController::class, 'index'])
    ->name('supervisor.dashboard');
    // operation c — subordinates list
    Route::get('staff/{id}/subordinates', [StaffController::class, 'subordinates'])->name('staff.subordinates');

    // operation d — supervisor list (all branches)
    Route::get('staff/supervisors', [StaffController::class, 'supervisorList'])->name('staff.supervisors');

    // API helpers your JS uses
    Route::get('api/staff',                      [StaffController::class, 'apiIndex']);
    Route::get('api/staff/{id}',                 [StaffController::class, 'apiShow']);
    Route::get('api/supervisors',                [StaffController::class, 'getSupervisors']);
    Route::get('api/branch/{branchNo}/staff-count', [StaffController::class, 'staffCountByBranch']);
    Route::get('api/supervisor/{supervisorNo}/count', [StaffController::class, 'supervisorStaffCount']);
});

// ── All authenticated staff ──────────────────────────────
Route::middleware(['auth'])->group(function () {
    Route::get('/staff/dashboard', [DashboardController::class, 'index'])
    ->name('staff.dashboard');
});



Route::view('/properties', 'property.index');
Route::view('/viewings', 'viewings.index');
Route::view('/leases', 'leases.index');


Route::get('/renter',           [RenterController::class, 'index'])->name('renter.index');
Route::get('/renter/create',    [RenterController::class, 'create'])->name('renter.create');
Route::get('/renter/search',    fn() => view('renter.search'))->name('renter.search');
Route::get('/renter/{id}/edit', fn($id) => view('renter.edit', ['renterId' => $id]))->name('renter.edit');
Route::view('/renter', 'renter.index');



Route::get('/owner', [OwnerController::class, 'index'])->name('owners.index');
Route::resource('owners', OwnerController::class);

Route::get('/properties', [PropertyController::class, 'webIndex']);
Route::post('/properties', [PropertyController::class, 'store']);
Route::delete('/properties/{id}', [PropertyController::class, 'destroy']);


Route::prefix('branches')->name('branch.')->group(function () {
    Route::get('/',                         [BranchOfficeController::class, 'index']     )->name('index');
    Route::get('/create',                   [BranchOfficeController::class, 'create']    )->name('create');
    Route::get('/{branchOffice}',           [BranchOfficeController::class, 'showPage']  )->name('show');
    Route::get('/{branchOffice}/edit',      [BranchOfficeController::class, 'edit']      )->name('edit');
    Route::delete('/{branchOffice}',        [BranchOfficeController::class, 'destroy']   )->name('destroy');
    Route::get('/{branchOffice}/staff',     [BranchOfficeController::class, 'staffPage'] )->name('staff');
    Route::get('/{branchNo}/report',        [StaffController::class, 'branchReport']     )->name('report');
});

Route::prefix('staff')->name('staff.')->group(function () {
    Route::get('/',                      [StaffController::class, 'index']        )->name('index');
    Route::get('/create',                [StaffController::class, 'create']       )->name('create');
    Route::get('/supervisors',           [StaffController::class, 'supervisorList'])->name('supervisor.list');   // listed here so it's caught before /{id}
    Route::get('/{id}',                  [StaffController::class, 'showPage']     )->name('show');
    Route::get('/{id}/edit',             [StaffController::class, 'edit']         )->name('edit');
    Route::delete('/{id}',               [StaffController::class, 'destroy']      )->name('destroy');
    Route::get('/{id}/subordinates',     [StaffController::class, 'subordinates'] )->name('subordinates');
});

// Named route aliases expected by blades
Route::get('/supervisors', [StaffController::class, 'supervisorList'])->name('supervisor.list');
