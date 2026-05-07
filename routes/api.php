<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\ViewingController;
use App\Http\Controllers\Api\LeaseController;



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