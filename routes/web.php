<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\AuditTrailController;
use App\Http\Controllers\SecurityController;

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Super Admin routes (separate from main app)
Route::prefix('super-admin')->name('super-admin.')->middleware(['auth','super.admin'])->group(function () {
    Route::get('/', [SuperAdminController::class, 'index'])->name('index');
    Route::get('/password', [SuperAdminController::class, 'showChangePassword'])->name('password');
    Route::put('/password', [SuperAdminController::class, 'updatePassword'])->name('password.update');
    Route::get('/schools/create', [SuperAdminController::class, 'create'])->name('schools.create');
    Route::post('/schools', [SuperAdminController::class, 'store'])->name('schools.store');
    Route::get('/schools/{school}', [SuperAdminController::class, 'show'])->name('schools.show');
    Route::get('/schools/{school}/edit', [SuperAdminController::class, 'edit'])->name('schools.edit');
    Route::put('/schools/{school}', [SuperAdminController::class, 'update'])->name('schools.update');
    Route::delete('/schools/{school}', [SuperAdminController::class, 'destroy'])->name('schools.destroy');
    Route::post('/schools/{school}/toggle-status', [SuperAdminController::class, 'toggleStatus'])->name('schools.toggle-status');
    Route::get('/schools/{school}/tenant-settings', [SuperAdminController::class, 'tenantSettings'])->name('schools.tenant-settings');
    Route::put('/schools/{school}/tenant-settings', [SuperAdminController::class, 'updateTenantSettings'])->name('schools.tenant-settings.update');
    
    // Audit Trail routes
    Route::prefix('audit-trails')->name('audit-trails.')->group(function () {
        Route::get('/', [AuditTrailController::class, 'index'])->name('index');
        Route::get('/school/{schoolId}', [AuditTrailController::class, 'school'])->name('school');
        Route::get('/{auditTrail}', [AuditTrailController::class, 'show'])->name('show');
        Route::get('/export/csv', [AuditTrailController::class, 'export'])->name('export');
        Route::get('/statistics/data', [AuditTrailController::class, 'statistics'])->name('statistics');
    });
    
    // Security routes
    Route::prefix('security')->name('security.')->group(function () {
        Route::get('/', [SecurityController::class, 'index'])->name('index');
        Route::get('/{securityLog}', [SecurityController::class, 'show'])->name('show');
        Route::get('/banned-ips', [SecurityController::class, 'bannedIps'])->name('banned-ips');
        Route::post('/ban-ip', [SecurityController::class, 'banIp'])->name('ban-ip');
        Route::post('/unban-ip/{bannedIp}', [SecurityController::class, 'unbanIp'])->name('unban-ip');
        Route::post('/block/{securityLog}', [SecurityController::class, 'blockSecurityLog'])->name('block');
        Route::get('/export/csv', [SecurityController::class, 'exportSecurityLogs'])->name('export');
        Route::get('/statistics/data', [SecurityController::class, 'statistics'])->name('statistics');
        Route::get('/alerts/data', [SecurityController::class, 'alerts'])->name('alerts');
    });
});

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware(['auth', 'school.isolation'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');
    
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
        // Hanya non-student yang boleh mengakses menu operasional absensi
        Route::middleware('role:teacher|tu|bk|kesiswaan|admin|headmaster')->group(function () {
            Route::get('/', [AttendanceController::class, 'index'])->name('index');
            Route::get('/check-in', [AttendanceController::class, 'showCheckIn'])->name('check-in');
            Route::post('/check-in', [AttendanceController::class, 'checkIn']);
            Route::get('/check-out', [AttendanceController::class, 'showCheckOut'])->name('check-out');
            Route::post('/check-out', [AttendanceController::class, 'checkOut']);
            Route::get('/qr-code', [AttendanceController::class, 'getQrCode'])->name('qr-code')->middleware('role:admin');
            Route::get('/display-qr', [AttendanceController::class, 'showDisplayQr'])->name('display-qr')->middleware('role:admin');
            Route::get('/student-scan', [AttendanceController::class, 'showStudentScan'])->name('student-scan')->middleware('role:teacher');
            Route::post('/student-scan', [AttendanceController::class, 'scanStudent'])->middleware('role:teacher');
        });

        // Laporan boleh diakses semua role termasuk student
        Route::get('/reports', [AttendanceController::class, 'reports'])->name('reports');
        Route::get('/reports/export', [AttendanceController::class, 'exportReport'])->name('reports.export');
    });
    
    // Settings (Admin only)
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/settings/attendance', [AttendanceController::class, 'showSettings'])->name('settings.attendance');
        Route::post('/settings/attendance', [AttendanceController::class, 'updateSettings']);
        
        // RBAC Management (Admin only)
        Route::prefix('admin')->name('admin.')->group(function () {
            Route::resource('roles', RoleController::class);
            Route::resource('permissions', PermissionController::class);
            Route::post('/roles/assign', [RoleController::class, 'assignToUser'])->name('roles.assign');
            Route::post('/roles/remove', [RoleController::class, 'removeFromUser'])->name('roles.remove');
        });
    });
    
    // Export attendance report
    Route::get('/attendance/export', [AttendanceController::class, 'export'])->name('attendance.export');
    Route::get('/attendance/export-detail', [AttendanceController::class, 'exportDetail'])->name('attendance.export-detail');
    
    // Tenant Settings (Admin only)
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/tenant/settings', [TenantController::class, 'index'])->name('tenant.settings');
        Route::put('/tenant/settings', [TenantController::class, 'update'])->name('tenant.settings.update');
        Route::put('/tenant/branding', [TenantController::class, 'updateBranding'])->name('tenant.branding.update');
        Route::put('/tenant/features', [TenantController::class, 'updateFeatures'])->name('tenant.features.update');
        Route::get('/api/tenant/settings', [TenantController::class, 'getSettings'])->name('tenant.api.settings');
    });

    // QR Code Management (Admin & TU)
    Route::middleware(['role:admin|tu'])->group(function () {
        Route::get('/qr', [\App\Http\Controllers\QrManagementController::class, 'index'])->name('qr.index');
        Route::get('/qr/download/{user}', [\App\Http\Controllers\QrManagementController::class, 'download'])->name('qr.download');
        Route::get('/qr/download-zip', [\App\Http\Controllers\QrManagementController::class, 'downloadZip'])->name('qr.zip');
        Route::get('/qr/card/{user}', [\App\Http\Controllers\QrManagementController::class, 'card'])->name('qr.card');
    });
    
    // Leave Request routes
    Route::resource('leave-requests', LeaveRequestController::class);
    Route::post('/leave-requests/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])->name('leave-requests.approve');
    Route::post('/leave-requests/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])->name('leave-requests.reject');
    
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
