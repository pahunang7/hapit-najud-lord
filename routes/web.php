<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RenterController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\BranchOfficeController;
use App\Http\Controllers\StaffController;

Route::get('/', function () {
    return view('dashboard');
});

Route::get('/', [DashboardController::class, 'index']);
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
