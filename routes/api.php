<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Mobile\MobileAuthController;
use App\Http\Controllers\Mobile\MobileAttendanceController;
use App\Http\Controllers\Mobile\MobileSettingsController;
use App\Http\Controllers\Mobile\MobileReportController;

/*
|--------------------------------------------------------------------------
| Mobile API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register mobile API routes for your application.
| These routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Mobile API Routes
Route::prefix('mobile')->group(function () {
    
    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('/login', [MobileAuthController::class, 'login']);
        Route::post('/logout', [MobileAuthController::class, 'logout'])->middleware('auth:sanctum');
        Route::get('/me', [MobileAuthController::class, 'me'])->middleware('auth:sanctum');
    });
    
    // Protected routes (require authentication)
    Route::middleware(['auth:sanctum'])->group(function () {
        
        // Attendance routes
        Route::prefix('attendance')->group(function () {
            Route::post('/check-in', [MobileAttendanceController::class, 'checkIn']);
            Route::post('/check-out', [MobileAttendanceController::class, 'checkOut']);
            Route::get('/history', [MobileAttendanceController::class, 'history']);
            Route::get('/today', [MobileAttendanceController::class, 'todayStatus']);
        });
        
        // Settings routes
        Route::prefix('settings')->group(function () {
            Route::get('/attendance', [MobileSettingsController::class, 'attendanceSettings']);
        });
        
        // Reports routes
        Route::prefix('reports')->group(function () {
            Route::get('/monthly', [MobileReportController::class, 'monthly']);
            Route::get('/export', [MobileReportController::class, 'export']);
        });
    });
});





