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

    //Usermanagement module
    Route::post('/user/create', [App\Http\Controllers\UserController::class, 'createUser']);
    Route::get('/user/all', [App\Http\Controllers\UserController::class, 'getAllUsers']);
    Route::get('/user/{slug}', [App\Http\Controllers\UserController::class, 'index']);
    Route::delete('/user/delete/{id}', [App\Http\Controllers\UserController::class, 'deleteUser']);
    Route::put('/user/update/{id}', [App\Http\Controllers\UserController::class, 'editUser']);


    //Tenant detail for logged in user
    Route::get('/tenant/view/{id}', [App\Http\Controllers\HostnameController::class, 'view']);
    Route::post('/tenant/logout', [App\Http\Controllers\HostnameController::class, 'logout']);
});

Route::prefix('leave')->middleware(['cors','auth:sanctum', 'module.access:Leave Management'])->group(function () {
    //Leave Categories
    Route::get('/categories', [App\Http\Controllers\LeaveController::class, 'getCategories']);    
    Route::post('/categories', [App\Http\Controllers\LeaveController::class, 'storeCategory']);   
    Route::put('/categories/{id}', [App\Http\Controllers\LeaveController::class, 'updateCategory']); 
    Route::delete('/categories/{id}', [App\Http\Controllers\LeaveController::class, 'deleteCategory']); 

    //Leave Applications
    Route::get('/applications', [App\Http\Controllers\LeaveController::class, 'getApplications']);
    Route::post('/apply', [App\Http\Controllers\LeaveController::class, 'apply']);
    Route::get('/applications/{id}/status', [App\Http\Controllers\LeaveController::class, 'getStatus']);
    Route::post('/applications/{id}/accept', [App\Http\Controllers\LeaveController::class, 'acceptLeave']);
    Route::post('/applications/{id}/reject', [App\Http\Controllers\LeaveController::class, 'rejectLeave']);

    // Financial Year Management
    Route::get('/financial-years', [App\Http\Controllers\LeaveController::class, 'getFinancialYears']);
    Route::post('/financial-years', [App\Http\Controllers\LeaveController::class, 'storeFinancialYear']);
    Route::put('/financial-years', [App\Http\Controllers\LeaveController::class, 'updateFinancialYear']);
    Route::delete('/financial-years', [App\Http\Controllers\LeaveController::class, 'deleteFinancialYear']);
});

Route::prefix('payroll')->middleware(['cors','auth:sanctum', 'module.access:Payroll Management'])->group(function () {
    //PaySlip Items
    Route::get('/items/{designation_id}', [App\Http\Controllers\PayrollController::class, 'itemByDesignation']);
    Route::post('/items', [App\Http\Controllers\PayrollController::class, 'storeItem']);
    Route::put('/items/{id}', [App\Http\Controllers\PayrollController::class, 'updateItem']);
    Route::delete('/items/{id}', [App\Http\Controllers\PayrollController::class, 'deleteItem']);

    //Pay Salary
    Route::get('summary', [App\Http\Controllers\PayrollController::class, 'paySummary']);
    Route::post('validate', [App\Http\Controllers\PayrollController::class, 'validateSalary']);
    Route::post('process/{date}', [App\Http\Controllers\PayrollController::class, 'processPayroll']);
    Route::get('processed/{date}', [App\Http\Controllers\PayrollController::class, 'getProcessedPayroll']);
    Route::get('view/{id}', [App\Http\Controllers\PayrollController::class, 'viewPayslip']);
    Route::post('line-item', [App\Http\Controllers\PayrollController::class, 'storePayslipItem']);
    Route::delete('line-item/{id}', [App\Http\Controllers\PayrollController::class, 'deletePayslipItem']);
    Route::post('pay/{date}', [App\Http\Controllers\PayrollController::class, 'paySalary']);
    Route::get('export/{date}', [App\Http\Controllers\PayrollController::class, 'exportSchedule']);
});



Route::fallback(function () {
    return response()->json([
        'status' => false,
        'message' => 'Endpoint Not Found.',
    ], 404);
});