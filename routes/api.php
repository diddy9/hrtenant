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

    //Configurations for Admin
    Route::post('/department/store', [App\Http\Controllers\ConfigurationsController::class, 'department_store']);
    Route::get('/department/show', [App\Http\Controllers\ConfigurationsController::class, 'department_show']);
    Route::put('/department/update/{id}', [App\Http\Controllers\ConfigurationsController::class, 'department_update']);
    Route::delete('/department/delete/{id}', [App\Http\Controllers\ConfigurationsController::class, 'department_delete']);
    Route::post('/department/restore/{id}', [App\Http\Controllers\ConfigurationsController::class, 'department_restore']);
    Route::post('/unit/store', [App\Http\Controllers\ConfigurationsController::class, 'unit_store']);
    Route::get('/unit/show', [App\Http\Controllers\ConfigurationsController::class, 'unit_show']);
    Route::put('/unit/update/{id}', [App\Http\Controllers\ConfigurationsController::class, 'unit_update']);
    Route::delete('/unit/delete/{id}', [App\Http\Controllers\ConfigurationsController::class, 'unit_delete']);
    Route::post('/unit/restore/{id}', [App\Http\Controllers\ConfigurationsController::class, 'unit_restore']);
    Route::post('/designation/store', [App\Http\Controllers\ConfigurationsController::class, 'designation_store']);
    Route::get('/designation/show', [App\Http\Controllers\ConfigurationsController::class, 'designation_show']);
    Route::put('/designation/update/{id}', [App\Http\Controllers\ConfigurationsController::class, 'designation_update']);
    Route::delete('/designation/delete/{id}', [App\Http\Controllers\ConfigurationsController::class, 'designation_delete']);
    Route::post('/designation/restore/{id}', [App\Http\Controllers\ConfigurationsController::class, 'designation_restore']);
    Route::post('/manager/add', [App\Http\Controllers\ConfigurationsController::class, 'role_add']);
    Route::get('/manager/show', [App\Http\Controllers\ConfigurationsController::class, 'role_show']);
    Route::delete('manager/delete/{id}', [App\Http\Controllers\ConfigurationsController::class, 'role_delete']);


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