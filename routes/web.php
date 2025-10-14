<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AttendanceController;

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // User management (Admin, TU only)
            Route::middleware(['role:admin|tu'])->group(function () {
                // Pastikan route spesifik didefinisikan sebelum resource agar tidak ditangkap oleh {user}
                Route::get('/users/import', [UserController::class, 'showImportForm'])->name('users.import');
                Route::post('/users/import', [UserController::class, 'import']);
                Route::get('/users/import-template', [UserController::class, 'downloadTemplate'])->name('users.import-template');
                Route::get('/users/export', [UserController::class, 'export'])->name('users.export');
                Route::resource('users', UserController::class);
            });
    
    // Attendance routes
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/', [AttendanceController::class, 'index'])->name('index');
        Route::get('/check-in', [AttendanceController::class, 'showCheckIn'])->name('check-in');
        Route::post('/check-in', [AttendanceController::class, 'checkIn']);
        Route::get('/check-out', [AttendanceController::class, 'showCheckOut'])->name('check-out');
        Route::post('/check-out', [AttendanceController::class, 'checkOut']);
        Route::get('/qr-code', [AttendanceController::class, 'getQrCode'])->name('qr-code');
        Route::get('/display-qr', [AttendanceController::class, 'showDisplayQr'])->name('display-qr');
        Route::get('/student-scan', [AttendanceController::class, 'showStudentScan'])->name('student-scan');
        Route::post('/student-scan', [AttendanceController::class, 'scanStudent']);
        Route::get('/reports', [AttendanceController::class, 'reports'])->name('reports');
        Route::get('/reports/export', [AttendanceController::class, 'exportReport'])->name('reports.export');
    });
    
    // Settings (Admin only)
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/settings/attendance', [AttendanceController::class, 'showSettings'])->name('settings.attendance');
        Route::post('/settings/attendance', [AttendanceController::class, 'updateSettings']);
    });
    
    // Export attendance report
    Route::get('/attendance/export', [AttendanceController::class, 'export'])->name('attendance.export');
    Route::get('/attendance/export-detail', [AttendanceController::class, 'exportDetail'])->name('attendance.export-detail');

    // QR Code Management (Admin & TU)
    Route::middleware(['role:admin|tu'])->group(function () {
        Route::get('/qr', [\App\Http\Controllers\QrManagementController::class, 'index'])->name('qr.index');
        Route::get('/qr/download/{user}', [\App\Http\Controllers\QrManagementController::class, 'download'])->name('qr.download');
        Route::get('/qr/download-zip', [\App\Http\Controllers\QrManagementController::class, 'downloadZip'])->name('qr.zip');
        Route::get('/qr/card/{user}', [\App\Http\Controllers\QrManagementController::class, 'card'])->name('qr.card');
    });
    
    // API for student lookup
    Route::get('/api/student/{nis}', function($nis) {
        $student = \App\Models\User::where('nis', $nis)
            ->where('user_type', 'student')
            ->where('school_id', auth()->user()->school_id)
            ->first();
            
        if ($student) {
            return response()->json(['name' => $student->name]);
        }
        
        return response()->json(['name' => null], 404);
    });
});

// Demo routes for template
Route::view('layout-light', 'starter_kit.color_version.layout_light')->name('layout_light');
Route::view('layout-dark', 'starter_kit.color_version.layout_dark')->name('layout_dark');
Route::view('box-layout', 'starter_kit.page_layout.box_layout')->name('box_layout');
Route::view('rtl-layout', 'starter_kit.page_layout.rtl_layout')->name('rtl_layout');
Route::view('hide-menu-on-scroll', 'starter_kit.hide_menu_on_scroll')->name('hide_menu_on_scroll');
Route::view('footer-light', 'starter_kit.footers.footer_light')->name('footer_light');
Route::view('footer-dark', 'starter_kit.footers.footer_dark')->name('footer_dark');
Route::view('footer-fixed', 'starter_kit.footers.footer_fixed')->name('footer_fixed');
