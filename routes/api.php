<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\ViewingController;
use App\Http\Controllers\Api\LeaseController;

//  CODE NI NI MARIEL MODULE 2
use App\Http\Controllers\RenterController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\Api\PropertyForRentController;




Route::prefix('properties')->group(function () {
    Route::get('/',              [PropertyController::class, 'index']);
    Route::get('/renters',       [PropertyController::class, 'renters']);
    Route::get('/staff',         [PropertyController::class, 'staff']);
    Route::get('/{id}',          [PropertyController::class, 'show']);
    Route::patch('/{id}/status', [PropertyController::class, 'updateStatus']);
});


Route::prefix('viewings')->group(function () {
    Route::get('/form-data', [ViewingController::class, 'formData']);
    Route::get('/',                             [ViewingController::class, 'index']);
    Route::post('/',                            [ViewingController::class, 'store']);
    Route::get('/property/{property_no}',       [ViewingController::class, 'byProperty']);
    Route::get('/{property_no}/{renter_no}/{viewing_date}/{comments}', [ViewingController::class, 'show']);
    Route::put('/{property_no}/{renter_no}/{viewing_date}/{comments}',[ViewingController::class, 'update']);
    Route::delete('/{property_no}/{renter_no}/{viewing_date}', [ViewingController::class, 'destroy']);
});


Route::prefix('leases')->group(function () {
    Route::get('/form-data', [LeaseController::class, 'formData']);
    Route::get('/',                          [LeaseController::class, 'index']);
    Route::post('/',                         [LeaseController::class, 'store']);
    Route::get('/property/{property_no}',    [LeaseController::class, 'byProperty']);
    Route::get('/{lease_no}',                [LeaseController::class, 'show']);
    Route::put('/{lease_no}',                [LeaseController::class, 'update']);
    Route::delete('/{lease_no}',             [LeaseController::class, 'destroy']);
});







// CODE NI NI MARIEL MODULE 2

// FILE: routes/api.php
// Replace the contents of your existing routes/api.php with this.


// ── Handle preflight OPTIONS requests (for CORS from browser) ─────────────────
Route::options('{any}', function () {
    return response('', 200)->withHeaders([
        'Access-Control-Allow-Origin'  => '*',
        'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
        'Access-Control-Allow-Headers' => 'Content-Type, Accept, X-Requested-With',
    ]);
})->where('any', '.*');

// ── BRANCH ROUTES ─────────────────────────────────────────────────────────────
Route::get('/branches', [BranchController::class, 'index']);

// ── STAFF ROUTES ──────────────────────────────────────────────────────────────
// GET /api/staff              → all staff
// GET /api/staff?branch_no=1  → staff filtered by branch
Route::get('/staff', [StaffController::class, 'index']);

// ── RENTER (CLIENT) ROUTES ────────────────────────────────────────────────────
Route::prefix('renters')->group(function () {
    Route::get('/',           [RenterController::class, 'index']);   // List all clients
    Route::post('/',          [RenterController::class, 'store']);   // Register new client
    Route::get('/{id}',       [RenterController::class, 'show']);    // View single client
    Route::put('/{id}',       [RenterController::class, 'update']);  // Update client
    Route::delete('/{id}',    [RenterController::class, 'destroy']); // Delete client

    // Staff assignment — calls PostgreSQL PROCEDURE
    Route::post('/{id}/assign-staff', [RenterController::class, 'assignStaff']);

    // Get assigned staff — calls PostgreSQL FUNCTION
    Route::get('/{id}/staff',         [RenterController::class, 'getRenterStaff']);

    // Activity log for a client (populated by triggers)
    Route::get('/{id}/logs',          [RenterController::class, 'getLogs']);
});

/*
|--------------------------------------------------------------------------
| PROPERTY SEARCH
|--------------------------------------------------------------------------
*/

Route::get(
    '/properties/search',
    [PropertyController::class, 'search']
);


// ── IMPORTANT: Also update config/cors.php ────────────────────────────────────
// In config/cors.php, set:
//   'paths'             => ['api/*'],
//   'allowed_origins'   => ['*'],
//   'allowed_methods'   => ['*'],
//   'allowed_headers'   => ['*'],


