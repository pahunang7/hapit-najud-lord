<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard');
});

Route::view('/properties', 'properties.index');
Route::view('/viewings', 'viewings.index');
Route::view('/leases', 'leases.index');
