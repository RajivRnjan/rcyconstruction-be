<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StaffSalaryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SupplierTransactionController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\ExpensesHeadController;
use App\Http\Controllers\Api\AdminAuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('admin')->group(function () {
    // Auth routes
    Route::post('login', [AdminAuthController::class, 'login']);
    
    // Protected Admin routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AdminAuthController::class, 'logout']);
        Route::post('change-password', [AdminAuthController::class, 'changePassword']);
        Route::get('user', function (Request $request) {
            return $request->user('admin');
        });
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/master-sheets', [\App\Http\Controllers\Api\MasterSheetController::class, 'index']);
    Route::post('/master-sheets', [\App\Http\Controllers\Api\MasterSheetController::class, 'store']);
    Route::get('/master-sheets/{id}', [\App\Http\Controllers\Api\MasterSheetController::class, 'show']);
    Route::put('/master-sheets/{id}', [\App\Http\Controllers\Api\MasterSheetController::class, 'update']);
    Route::delete('/master-sheets/{id}', [\App\Http\Controllers\Api\MasterSheetController::class, 'destroy']);

    Route::get('/accounts', [\App\Http\Controllers\AccountController::class, 'index']);
    Route::post('/accounts', [\App\Http\Controllers\AccountController::class, 'store']);
    Route::get('/accounts/{id}', [\App\Http\Controllers\AccountController::class, 'show']);
    Route::put('/accounts/{id}', [\App\Http\Controllers\AccountController::class, 'update']);
    Route::delete('/accounts/{id}', [\App\Http\Controllers\AccountController::class, 'destroy']);

    Route::get('staff-attendance', [StaffSalaryController::class, 'getAttendance']);
    Route::apiResource('staff-salaries', StaffSalaryController::class);
    Route::get('supplier-payments', [\App\Http\Controllers\SupplierController::class, 'payments']);
    Route::apiResource('suppliers', SupplierController::class);
    Route::get('suppliers/{supplier}/transactions', [SupplierTransactionController::class, 'index']);
    Route::post('suppliers/{supplier}/transactions', [SupplierTransactionController::class, 'store']);
    Route::apiResource('materials', MaterialController::class);
    Route::apiResource('expenses-heads', ExpensesHeadController::class);
    Route::apiResource('material-ins', \App\Http\Controllers\MaterialInController::class);
    Route::apiResource('material-outs', \App\Http\Controllers\MaterialOutController::class);
    Route::apiResource('subcontractors', \App\Http\Controllers\SubcontractorController::class);
    Route::apiResource('site-incharges', \App\Http\Controllers\SiteInchargeController::class);
    Route::apiResource('sites', \App\Http\Controllers\SiteController::class);
    Route::get('daily-reports', [\App\Http\Controllers\DailyReportController::class, 'index']);
    Route::get('daily-reports/expense-suggestions', [\App\Http\Controllers\DailyReportController::class, 'getExpenseSuggestions']);
    Route::get('daily-reports/{id}', [\App\Http\Controllers\DailyReportController::class, 'show']);
    Route::post('daily-reports', [\App\Http\Controllers\DailyReportController::class, 'store']);
    Route::delete('daily-reports/{id}', [\App\Http\Controllers\DailyReportController::class, 'destroy']);
    Route::post('sites/{id}/staff', [\App\Http\Controllers\SiteController::class, 'addStaff']);
    Route::delete('sites/{id}/staff/{staff_id}', [\App\Http\Controllers\SiteController::class, 'removeStaff']);
    Route::get('sites/{id}/daily-reports', [\App\Http\Controllers\DailyReportController::class, 'indexBySite']);
    Route::get('dashboard/stats', [\App\Http\Controllers\DashboardController::class, 'stats']);

    Route::apiResource('head-office-incomes', \App\Http\Controllers\HeadOfficeIncomeController::class);
    Route::apiResource('head-office-expenses', \App\Http\Controllers\HeadOfficeExpenseController::class);
});
