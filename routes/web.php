<?php

use Illuminate\Support\Facades\Route;


header("Cache-Control: no-cache, must-revalidate");
header('Access-Control-Allow-Origin:  *');
header('Access-Control-Allow-Methods:  POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers:  Content-Type, X-Auth-Token, Origin, Authorization');

Route::domain('localhost')->group(function () { 
    // Landing Page Routes
    Route::get('/', function () {
        return view('welcome');
    });

    // login routes
    Route::get('/login', [App\Http\Controllers\LoginController::class, 'index'])->name('signin');
    Route::post('/login', [App\Http\Controllers\LoginController::class, 'routeToTenant']);

});

Route::middleware('tenant.exists')->group(function () {

    // Landing Page Routes
    Route::get('/', function () {
        return view('auth.login');
    });

    Auth::routes(['register' => false]);
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

});


