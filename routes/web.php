<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Api\RenterController;

Route::get('/', function () {
    return view('dashboard');
});

Route::get('/', [DashboardController::class, 'index']);
Route::view('/properties', 'properties.index');
Route::view('/viewings', 'viewings.index');
Route::view('/leases', 'leases.index');


Route::view('/renter',  'renter.index');
