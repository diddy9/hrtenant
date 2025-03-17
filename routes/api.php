<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


// API route for hosts
Route::get('/hosts', [App\Http\Controllers\HostnameController::class, 'index']);

// API route to identify tenant
Route::post('/tenant/identify', [App\Http\Controllers\HostnameController::class, 'identifyTenant']);

Route::group(['middleware' => ['cors', 'tenant.exists']], function () {
    // API for tenant login
    Route::post('/tenant/login', [App\Http\Controllers\HostnameController::class, 'login']);
});

Route::group(['middleware' => ['cors', 'tenant.exists','auth:sanctum']], function () {
    //Profile update
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'index']);
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'updateProfile']);
    Route::put('/profile/password-update', [App\Http\Controllers\ProfileController::class, 'passwordUpdate']);

    //Tenant detail for logged in user
    Route::get('/tenant/view/{id}', [App\Http\Controllers\HostnameController::class, 'view']);
    Route::post('/tenant/logout', [App\Http\Controllers\HostnameController::class, 'logout']);
});

Route::middleware(['auth:sanctum', 'module.access:Leave Management'])->group(function () {
    Route::get('/leave', [LeaveController::class, 'index']);    // View Leaves
    Route::post('/leave', [LeaveController::class, 'store']);   // Add Leave
    Route::put('/leave/{id}', [LeaveController::class, 'update']); // Edit Leave
});

Route::fallback(function () {
    return response()->json([
        'status' => false,
        'message' => 'Endpoint Not Found.',
    ], 404);
});