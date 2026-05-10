<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Api\RenterController;
use App\Http\Controllers\OwnerController;

Route::get('/', function () {
    return view('dashboard');
});

Route::get('/', [DashboardController::class, 'index']);
Route::view('/properties', 'properties.index');
Route::view('/viewings', 'viewings.index');
Route::view('/leases', 'leases.index');


Route::view('/renter',  'renter.index');

Route::get('/owner', [OwnerController::class, 'index'])->name('owners.index');
Route::resource('owners', OwnerController::class);

