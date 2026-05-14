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
|--------------------------------------------------------------------------
| AUTHENTICATED API ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | PROPERTIES API
    |--------------------------------------------------------------------------
    | Manager + Supervisor
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:Manager,Supervisor')
    ->prefix('properties')
    ->group(function () {

        // VIEW
        Route::get('/', [PropertyController::class, 'index']);

        Route::get('/search', [PropertyController::class, 'search']);

        Route::get('/renters', [PropertyController::class, 'renters']);

        Route::get('/staff', [PropertyController::class, 'staff']);

        Route::get('/{id}', [PropertyController::class, 'show']);

        // CREATE / UPDATE
        Route::post('/', [PropertyController::class, 'store']);

        Route::put('/{id}', [PropertyController::class, 'update']);

        Route::patch('/{id}/status', [PropertyController::class, 'updateStatus']);
    });

    /*
    |--------------------------------------------------------------------------
    | PROPERTY DELETE
    |--------------------------------------------------------------------------
    | Manager ONLY
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:Manager')
    ->prefix('properties')
    ->group(function () {

        Route::delete('/{id}', [PropertyController::class, 'destroy']);
    });

    /*
    |--------------------------------------------------------------------------
    | VIEWINGS API
    |--------------------------------------------------------------------------
    | Manager + Supervisor + Secretary
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:Manager,Supervisor,Secretary')
    ->prefix('viewings')
    ->group(function () {

        Route::get('/', [ViewingController::class, 'index']);

        Route::get('/form-data', [ViewingController::class, 'formData']);

        Route::post('/', [ViewingController::class, 'store']);

        Route::get('/property/{property_no}', [ViewingController::class, 'byProperty']);

        Route::get(
            '/{property_no}/{renter_no}/{viewing_date}/{comments}',
            [ViewingController::class, 'show']
        );

        Route::put(
            '/{property_no}/{renter_no}/{viewing_date}/{comments}',
            [ViewingController::class, 'update']
        );

        Route::delete(
            '/{property_no}/{renter_no}/{viewing_date}',
            [ViewingController::class, 'destroy']
        );
    });

    /*
    |--------------------------------------------------------------------------
    | LEASES API
    |--------------------------------------------------------------------------
    | Manager + Supervisor + Secretary
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:Manager,Supervisor,Secretary')
    ->prefix('leases')
    ->group(function () {

        Route::get('/', [LeaseController::class, 'index']);

        Route::get('/form-data', [LeaseController::class, 'formData']);

        Route::post('/', [LeaseController::class, 'store']);

        Route::get('/property/{property_no}', [LeaseController::class, 'byProperty']);

        Route::get('/{lease_no}', [LeaseController::class, 'show']);

        Route::put('/{lease_no}', [LeaseController::class, 'update']);

        Route::delete('/{lease_no}', [LeaseController::class, 'destroy']);
    });

    /*
    |--------------------------------------------------------------------------
    | BRANCHES API
    |--------------------------------------------------------------------------
    | VIEW ACCESS: Manager + Supervisor
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:Manager,Supervisor')
    ->prefix('branches')
    ->group(function () {

        // VIEW
        Route::get('/', [BranchOfficeController::class, 'apiIndex']);

        Route::get('/{branchOffice}/staff-count', [
            BranchOfficeController::class,
            'staffCount'
        ]);

        Route::get('/{branchOffice}/manager', [
            BranchOfficeController::class,
            'manager'
        ]);

        Route::get('/{branch_no}/staff', [
            BranchOfficeController::class,
            'getStaff'
        ]);

        Route::get('/{branchOffice}', [
            BranchOfficeController::class,
            'apiShow'
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | BRANCH CRUD
    |--------------------------------------------------------------------------
    | Manager ONLY
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:Manager')
    ->prefix('branches')
    ->group(function () {

        Route::post('/', [BranchOfficeController::class, 'store']);

        Route::put('/{branchOffice}', [
            BranchOfficeController::class,
            'update'
        ]);

        Route::delete('/{branchOffice}', [
            BranchOfficeController::class,
            'destroy'
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | STAFF API
    |--------------------------------------------------------------------------
    | VIEW ACCESS: Manager + Supervisor
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:Manager,Supervisor')
    ->prefix('staff')
    ->group(function () {

        // IMPORTANT:
        // Put special routes BEFORE /{id}

        Route::get('/supervisors-for-branch', [
            StaffController::class,
            'getSupervisors'
        ]);

        Route::get('/branch/{branchNo}/staff-count', [
            StaffController::class,
            'staffCountByBranch'
        ]);

        Route::get('/{id}/supervisor-count', [
            StaffController::class,
            'supervisorStaffCount'
        ]);

        Route::get('/', [StaffController::class, 'apiIndex']);

        Route::get('/{id}', [StaffController::class, 'apiShow']);
    });

    /*
    |--------------------------------------------------------------------------
    | STAFF CRUD
    |--------------------------------------------------------------------------
    | Manager ONLY
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:Manager')
    ->prefix('staff')
    ->group(function () {

        Route::post('/', [StaffController::class, 'store']);

        Route::put('/{id}', [StaffController::class, 'update']);

        Route::delete('/{id}', [StaffController::class, 'destroy']);

        Route::post('/{id}/assign-branch', [
            StaffController::class,
            'assignToBranch'
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | RENTERS API
    |--------------------------------------------------------------------------
    | VIEW ACCESS: Manager + Supervisor + Secretary
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:Manager,Supervisor,Secretary')
    ->prefix('renters')
    ->group(function () {

        Route::get('/', [RenterController::class, 'apiIndex']);

        Route::get('/{id}/staff', [
            RenterController::class,
            'getRenterStaff'
        ]);

        Route::get('/{id}/logs', [
            RenterController::class,
            'getLogs'
        ]);

        Route::get('/{id}', [RenterController::class, 'show']);
    });

    /*
    |--------------------------------------------------------------------------
    | RENTER CRUD
    |--------------------------------------------------------------------------
    | Manager + Secretary ONLY
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:Manager,Secretary')
    ->prefix('renters')
    ->group(function () {

        Route::post('/', [RenterController::class, 'store']);

        Route::put('/{id}', [RenterController::class, 'update']);

        Route::delete('/{id}', [RenterController::class, 'destroy']);

        Route::post('/{id}/assign-staff', [
            RenterController::class,
            'assignStaff'
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | OWNERS API
    |--------------------------------------------------------------------------
    | VIEW/ADD/EDIT: Manager + Supervisor
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:Manager,Supervisor')
    ->prefix('owners')
    ->group(function () {

        Route::get('/', [OwnerController::class, 'index']);

        Route::post('/', [OwnerController::class, 'store']);

        Route::get('/{id}', [OwnerController::class, 'show']);

        Route::put('/{id}', [OwnerController::class, 'update']);
    });

    /*
    |--------------------------------------------------------------------------
    | OWNER DELETE
    |--------------------------------------------------------------------------
    | Manager ONLY
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:Manager')
    ->prefix('owners')
    ->group(function () {

        Route::delete('/{id}', [OwnerController::class, 'destroy']);
    });
});