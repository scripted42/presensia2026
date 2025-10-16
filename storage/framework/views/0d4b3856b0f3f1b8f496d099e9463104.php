<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Presensia'); ?></title>
    <!-- Resource Hints: speed up hard refresh by preconnecting to CDNs -->
    <link rel="dns-prefetch" href="//cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="dns-prefetch" href="//cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Perjelas area input form agar lebih nyaman dipandang */
        form input[type="text"],
        form input[type="email"],
        form input[type="password"],
        form input[type="number"],
        form input[type="date"],
        form input[type="tel"],
        form select,
        form textarea {
            padding: 0.625rem 0.875rem; /* py-2.5 px-3.5 */
            border-width: 2px; /* border-2 */
            border-color: #D1D5DB; /* gray-300 */
            border-radius: 0.5rem; /* rounded-lg */
            font-size: 1rem; /* text-base */
            line-height: 1.5rem; /* leading-6 */
            min-height: 2.75rem; /* ~h-11 */
        }

        form textarea {
            min-height: 6rem; /* tinggi default textarea */
        }

        form input:focus,
        form select:focus,
        form textarea:focus {
            outline: none;
            border-color: #3B82F6; /* blue-500 */
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25); /* ring */
        }
        /* Accessibility helpers */
        .sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
        .skip-link:focus{position:static;width:auto;height:auto;margin:8px;clip:auto;padding:8px 12px;background:#1f2937;color:#fff;border-radius:6px}
        /* Sidebar color accents per group */
        .sb-header{border-left:4px solid transparent;border-radius:8px;padding-left:10px;}
        .sb-header:hover{background:#f9fafb}
        .sb-c-purple a{border-left:3px solid transparent;border-radius:8px}
        .sb-c-purple a:hover{background:#f5f3ff;border-left-color:#A78BFA}
        .sb-c-purple a[aria-current="page"]{background:#ede9fe;border-left-color:#7c3aed;color:#3730a3}
        .sb-c-teal a{border-left:3px solid transparent;border-radius:8px}
        .sb-c-teal a:hover{background:#f0fdfa;border-left-color:#14B8A6}
        .sb-c-teal a[aria-current="page"]{background:#ccfbf1;border-left-color:#0d9488;color:#115e59}
        .sb-c-blue a{border-left:3px solid transparent;border-radius:8px}
        .sb-c-blue a:hover{background:#eff6ff;border-left-color:#3B82F6}
        .sb-c-blue a[aria-current="page"]{background:#dbeafe;border-left-color:#2563eb;color:#1e3a8a}
        .sb-c-amber a{border-left:3px solid transparent;border-radius:8px}
        .sb-c-amber a:hover{background:#fffbeb;border-left-color:#f59e0b}
        .sb-c-amber a[aria-current="page"]{background:#fef3c7;border-left-color:#d97706;color:#92400e}
        .sb-c-indigo a{border-left:3px solid transparent;border-radius:8px}
        .sb-c-indigo a:hover{background:#eef2ff;border-left-color:#6366f1}
        .sb-c-indigo a[aria-current="page"]{background:#e0e7ff;border-left-color:#4f46e5;color:#3730a3}
        /* Desktop sidebar collapse */
        .desktop-sidebar{transition:width .2s ease;}
        body.desktop-sidebar-collapsed #desktop-sidebar{display:none !important;}
    </style>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="bg-gray-50">
    <a href="#main-content" class="sr-only skip-link">Lewati ke konten</a>
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="hidden md:flex md:w-64 md:flex-col desktop-sidebar" id="desktop-sidebar" aria-label="Sidebar">
            <div class="flex flex-col flex-grow pt-5 bg-white overflow-y-auto border-r border-gray-200" role="complementary">
                <!-- Logo -->
                <div class="flex items-center flex-shrink-0 px-4">
                    <img src="<?php echo e(asset('assets/images/logo/presensia-logo.png')); ?>" alt="Presensia" class="h-4 w-auto" />
                </div>
                
                <!-- Navigation -->
                <nav class="mt-5 flex-1 px-2 space-y-1" role="navigation" aria-label="Sidebar">
                    <!-- Dashboard - tenant or SaaS -->
                    <?php ($isSuper = strtolower(auth()->user()->email) === strtolower(config('app.super_admin_email', env('APP_SUPER_ADMIN_EMAIL', 'superadmin@presensia.com')))); ?>
                    <a href="<?php echo e($isSuper ? route('super-admin.index') : route('dashboard')); ?>" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md <?php echo e(request()->routeIs($isSuper ? 'super-admin.index' : 'dashboard') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'); ?>" aria-current="<?php echo e(request()->routeIs($isSuper ? 'super-admin.index' : 'dashboard') ? 'page' : 'false'); ?>">
                        <i class="fas fa-home mr-3 text-gray-400 group-hover:text-gray-500"></i>
                        Dashboard
                    </a>
                    
                    <!-- SUPER ADMIN MENU -->
                    <?php if($isSuper): ?>
                        <div class="space-y-1 sb-c-indigo">
                            <button type="button" class="sb-header w-full text-left px-2 py-1 text-xs font-semibold text-gray-500 uppercase tracking-wider flex items-center justify-between hover:text-gray-700" onclick="toggleSidebarGroup('group-super-admin')">
                                <span>Super Admin</span>
                                <span id="chev-group-super-admin">▾</span>
                            </button>
                            <div id="group-super-admin" class="mt-1 space-y-1">
                            <a href="<?php echo e(route('super-admin.schools.index')); ?>" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md <?php echo e(request()->routeIs('super-admin.schools.*') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'); ?>" aria-current="<?php echo e(request()->routeIs('super-admin.schools.*') ? 'page' : 'false'); ?>">
                                Manajemen Sekolah
                            </a>
                            
                            <a href="<?php echo e(route('super-admin.audit-trails.index')); ?>" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md <?php echo e(request()->routeIs('super-admin.audit-trails.*') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'); ?>" aria-current="<?php echo e(request()->routeIs('super-admin.audit-trails.*') ? 'page' : 'false'); ?>">
                                Audit Trail
                            </a>
                            
                            <a href="<?php echo e(route('super-admin.security.index')); ?>" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md <?php echo e(request()->routeIs('super-admin.security.*') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'); ?>" aria-current="<?php echo e(request()->routeIs('super-admin.security.*') ? 'page' : 'false'); ?>">
                                Security Monitoring
                            </a>
                            
                    <a href="<?php echo e(route('super-admin.security.banned-ips')); ?>" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md <?php echo e(request()->routeIs('super-admin.security.banned-ips') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'); ?>" aria-current="<?php echo e(request()->routeIs('super-admin.security.banned-ips') ? 'page' : 'false'); ?>">
                        Banned IPs
                    </a>
                    
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- 1. MANAJEMEN DATA (Admin Only) -->
                    <?php if(auth()->user()->hasRole('admin') && strtolower(auth()->user()->email) !== strtolower(config('app.super_admin_email', env('APP_SUPER_ADMIN_EMAIL', 'superadmin@presensia.com')))): ?>
                        <div class="space-y-1 sb-c-purple">
                            <button type="button" class="sb-header w-full text-left px-2 py-1 text-xs font-semibold text-gray-500 uppercase tracking-wider flex items-center justify-between hover:text-gray-700" onclick="toggleSidebarGroup('group-manajemen-data')">
                                <span>Manajemen Data</span>
                                <span id="chev-group-manajemen-data">▾</span>
                            </button>
                            <div id="group-manajemen-data" class="mt-1 space-y-1">
                            
                            <a href="<?php echo e(route('users.index', ['type' => 'employee'])); ?>" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md <?php echo e(request()->routeIs('users.*') && request('type') == 'employee' ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'); ?>" aria-current="<?php echo e(request()->routeIs('users.*') && request('type') == 'employee' ? 'page' : 'false'); ?>">
                                Data Pegawai
                            </a>
                            
                            <a href="<?php echo e(route('users.create', ['type' => 'employee'])); ?>" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md <?php echo e(request()->routeIs('users.create') && request('type') == 'employee' ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'); ?>" aria-current="<?php echo e(request()->routeIs('users.create') && request('type') == 'employee' ? 'page' : 'false'); ?>">
                                Tambah Pegawai
                            </a>
                            
                            <a href="<?php echo e(route('users.index', ['type' => 'student'])); ?>" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md <?php echo e(request()->routeIs('users.*') && request('type') == 'student' ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'); ?>" aria-current="<?php echo e(request()->routeIs('users.*') && request('type') == 'student' ? 'page' : 'false'); ?>">
                                Data Siswa
                            </a>
                            
                            <a href="<?php echo e(route('users.create', ['type' => 'student'])); ?>" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md <?php echo e(request()->routeIs('users.create') && request('type') == 'student' ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'); ?>" aria-current="<?php echo e(request()->routeIs('users.create') && request('type') == 'student' ? 'page' : 'false'); ?>">
                                Tambah Siswa
                            </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- 2. ABSENSI (All roles except student) -->
                    <?php if(auth()->user()->hasRole(['teacher', 'tu', 'bk', 'kesiswaan', 'admin', 'headmaster']) && strtolower(auth()->user()->email) !== strtolower(config('app.super_admin_email', env('APP_SUPER_ADMIN_EMAIL', 'superadmin@presensia.com')))): ?>
                        <div class="space-y-1 sb-c-blue">
                            <button type="button" class="sb-header w-full text-left px-2 py-1 text-xs font-semibold text-gray-500 uppercase tracking-wider flex items-center justify-between hover:text-gray-700" onclick="toggleSidebarGroup('group-absensi')">
                                <span>Absensi</span>
                                <span id="chev-group-absensi">▾</span>
                            </button>
                            <div id="group-absensi" class="mt-1 space-y-1">
                            
                            <a href="<?php echo e(route('attendance.index')); ?>" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md <?php echo e(request()->routeIs('attendance.index') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'); ?>" aria-current="<?php echo e(request()->routeIs('attendance.index') ? 'page' : 'false'); ?>">
                                Status Absensi
                            </a>
                            
                            <?php if(auth()->user()->hasRole(['teacher', 'tu', 'bk', 'kesiswaan', 'admin'])): ?>
                                <a href="<?php echo e(route('attendance.check-in')); ?>" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md <?php echo e(request()->routeIs('attendance.check-in') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'); ?>" aria-current="<?php echo e(request()->routeIs('attendance.check-in') ? 'page' : 'false'); ?>">
                                    Absensi Masuk
                                </a>
                                
                                <?php if(auth()->user()->hasRole('admin')): ?>
                                    <a href="<?php echo e(route('attendance.display-qr')); ?>" target="_blank" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-600 hover:bg-gray-50 hover:text-gray-900" aria-current="false">
                                        QR Code Absensi
                                    </a>
                                <?php endif; ?>
                                
                                <a href="<?php echo e(route('attendance.check-out')); ?>" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md <?php echo e(request()->routeIs('attendance.check-out') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'); ?>" aria-current="<?php echo e(request()->routeIs('attendance.check-out') ? 'page' : 'false'); ?>">
                                    Absensi Keluar
                                </a>
                                
                                <?php if(auth()->user()->hasRole(['teacher','admin'])): ?>
                                    <a href="<?php echo e(route('attendance.student-scan')); ?>" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md <?php echo e(request()->routeIs('attendance.student-scan') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'); ?>" aria-current="<?php echo e(request()->routeIs('attendance.student-scan') ? 'page' : 'false'); ?>">
                                        Scan Siswa
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <a href="<?php echo e(route('attendance.reports')); ?>" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md <?php echo e(request()->routeIs('attendance.reports') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'); ?>" aria-current="<?php echo e(request()->routeIs('attendance.reports') ? 'page' : 'false'); ?>">
                                Laporan Absensi
                            </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- 3. IZIN & CUTI (All roles except student) -->
                    <?php if(auth()->user()->hasRole(['teacher', 'tu', 'bk', 'kesiswaan', 'admin', 'headmaster']) && strtolower(auth()->user()->email) !== strtolower(config('app.super_admin_email', env('APP_SUPER_ADMIN_EMAIL', 'superadmin@presensia.com')))): ?>
                        <div class="space-y-1 sb-c-amber">
                            <button type="button" class="sb-header w-full text-left px-2 py-1 text-xs font-semibold text-gray-500 uppercase tracking-wider flex items-center justify-between hover:text-gray-700" onclick="toggleSidebarGroup('group-izin')">
                                <span>Izin & Cuti</span>
                                <span id="chev-group-izin">▾</span>
                            </button>
                            <div id="group-izin" class="mt-1 space-y-1">
                            
                            <?php ($pendingApprovals = (auth()->user()->hasRole('headmaster') ? \App\Models\LeaveRequest::where('status','pending')->where('school_id', auth()->user()->school_id)->count() : 0)); ?>
                            <a href="<?php echo e(route('leave-requests.index')); ?>" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md <?php echo e(request()->routeIs('leave-requests.*') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'); ?>" aria-current="<?php echo e(request()->routeIs('leave-requests.*') ? 'page' : 'false'); ?>">
                                <?php if(auth()->user()->hasRole('headmaster')): ?>
                                    Persetujuan Izin
                                    <?php if($pendingApprovals > 0): ?>
                                        <span class="ml-auto inline-flex items-center justify-center px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-800"><?php echo e($pendingApprovals); ?></span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    Manajemen Izin
                                <?php endif; ?>
                            </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- 4. SISWA (Student only) -->
                    <?php if(auth()->user()->hasRole('student') && strtolower(auth()->user()->email) !== strtolower(config('app.super_admin_email', env('APP_SUPER_ADMIN_EMAIL', 'superadmin@presensia.com')))): ?>
                        <div class="space-y-1">
                            <div class="px-2 py-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">Siswa</div>
                            
                            <a href="<?php echo e(route('attendance.reports')); ?>" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md <?php echo e(request()->routeIs('attendance.reports') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'); ?>">
                                Riwayat Absensi
                            </a>
                            
                            <a href="<?php echo e(route('leave-requests.index')); ?>" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md <?php echo e(request()->routeIs('leave-requests.*') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'); ?>">
                                Izin Saya
                            </a>
                        </div>
                    <?php endif; ?>

                    <!-- 5. PENGATURAN (Admin Only) -->
                    <?php if(auth()->user()->hasRole('admin') && strtolower(auth()->user()->email) !== strtolower(config('app.super_admin_email', env('APP_SUPER_ADMIN_EMAIL', 'superadmin@presensia.com')))): ?>
                        <div class="space-y-1 sb-c-teal">
                            <button type="button" class="sb-header w-full text-left px-2 py-1 text-xs font-semibold text-gray-500 uppercase tracking-wider flex items-center justify-between hover:text-gray-700" onclick="toggleSidebarGroup('group-settings')">
                                <span>Pengaturan</span>
                                <span id="chev-group-settings">▾</span>
                            </button>
                            <div id="group-settings" class="mt-1 space-y-1">
                            
                            <a href="<?php echo e(route('settings.attendance')); ?>" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md <?php echo e(request()->routeIs('settings.*') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'); ?>">
                                Pengaturan Absensi
                            </a>
                            
                            <a href="<?php echo e(route('qr.index')); ?>" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md <?php echo e(request()->routeIs('qr.*') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'); ?>">
                                QR Code Management
                            </a>
                            
                            <a href="<?php echo e(route('admin.roles.index')); ?>" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md <?php echo e(request()->routeIs('admin.roles.*') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'); ?>">
                                Manajemen Role
                            </a>
                            
                            <a href="<?php echo e(route('admin.permissions.index')); ?>" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md <?php echo e(request()->routeIs('admin.permissions.*') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'); ?>">
                                Manajemen Permission
                            </a>
                            
                            <a href="<?php echo e(route('tenant.settings')); ?>" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md <?php echo e(request()->routeIs('tenant.*') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'); ?>">
                                Kustomisasi Aplikasi
                            </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </nav>
            </div>
        </div>

        <!-- Mobile sidebar -->
        <div class="md:hidden" id="mobile-sidebar" style="display: none;" role="dialog" aria-modal="true" aria-label="Menu">
            <div class="fixed inset-0 flex z-40">
                <div class="fixed inset-0 bg-gray-600 bg-opacity-75" onclick="toggleMobileSidebar()" aria-hidden="true"></div>
                <div class="relative flex-1 flex flex-col max-w-xs w-full bg-white">
                    <div class="absolute top-0 right-0 -mr-12 pt-2">
                        <button onclick="toggleMobileSidebar()" aria-label="Tutup sidebar" class="ml-1 flex items-center justify-center h-10 w-10 rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white">
                            <i class="fas fa-times text-white"></i>
                        </button>
                    </div>
                    <div class="flex-1 h-0 pt-5 pb-4 overflow-y-auto">
                        <div class="flex-shrink-0 flex items-center px-4">
                            <img src="<?php echo e(asset('assets/images/logo/presensia-logo.png')); ?>" alt="Presensia" class="h-4 w-auto" />
                        </div>
                        <nav class="mt-5 px-2 space-y-1">
                            <!-- Same navigation as desktop -->
                            <a href="<?php echo e(route('dashboard')); ?>" class="group flex items-center px-2 py-2 text-base font-medium rounded-md <?php echo e(request()->routeIs('dashboard') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'); ?>">
                                <i class="fas fa-home mr-3 text-gray-400 group-hover:text-gray-500"></i>
                                Dashboard
                            </a>
                            <!-- Add other menu items here for mobile -->
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <div class="flex flex-col w-0 flex-1 overflow-hidden">
            <!-- Top navigation -->
            <div class="relative z-10 flex-shrink-0 flex h-16 bg-white shadow" role="banner">
                <button onclick="toggleMobileSidebar()" aria-label="Buka sidebar" aria-controls="mobile-sidebar" aria-expanded="false" class="px-4 border-r border-gray-200 text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500 md:hidden">
                    <i class="fas fa-bars"></i>
                </button>
                <button type="button" id="desktop-collapse-btn" onclick="toggleDesktopSidebar()" aria-label="Collapse sidebar" aria-pressed="false" class="ml-2 px-3 text-gray-500 hover:text-gray-700 hidden md:inline-flex items-center focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500">
                    <i class="fas fa-columns"></i>
                </button>
                
                <div class="flex-1 px-4 flex justify-end">
                    <div class="flex items-center space-x-3">
                        <!-- User info minimal -->
                        <div class="text-right">
                            <div class="text-sm font-medium text-gray-900"><?php echo e(auth()->user()->name); ?></div>
                            <div class="text-xs text-gray-500"><?php echo e(auth()->user()->roles->first()->display_name ?? auth()->user()->roles->first()->name ?? 'No Role'); ?></div>
                        </div>
                        
                        <!-- Avatar -->
                        <img class="h-8 w-8 rounded-full" src="https://ui-avatars.com/api/?name=<?php echo e(urlencode(auth()->user()->name)); ?>&background=3B82F6&color=fff" alt="<?php echo e(auth()->user()->name); ?>">
                        
                        <!-- Logout button minimal -->
                        <form method="POST" action="<?php echo e(route('logout')); ?>" id="logout-form">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="p-2 text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-sign-out-alt"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Page content -->
            <main id="main-content" class="flex-1 relative overflow-y-auto focus:outline-none" tabindex="-1">
                <div class="py-6">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
                        <?php echo $__env->yieldContent('content'); ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        function toggleMobileSidebar() {
            const sidebar = document.getElementById('mobile-sidebar');
            sidebar.style.display = sidebar.style.display === 'none' ? 'block' : 'none';
        }
        function toggleDesktopSidebar(){
            const key = 'desktopSidebarCollapsed';
            const collapsed = document.body.classList.toggle('desktop-sidebar-collapsed');
            const btn = document.getElementById('desktop-collapse-btn');
            if (btn) btn.setAttribute('aria-pressed', collapsed ? 'true' : 'false');
            try { localStorage.setItem(key, collapsed ? '1' : '0'); } catch(e) {}
        }
        function toggleSidebarGroup(id){
            const key = 'sbgrp:' + id;
            const el = document.getElementById(id);
            const chev = document.getElementById('chev-' + id);
            if (!el) return;
            const hidden = el.classList.toggle('hidden');
            if (chev) chev.textContent = hidden ? '▸' : '▾';
            try { localStorage.setItem(key, hidden ? '0' : '1'); } catch(e) {}
        }
        (function(){
            try{
                const collapsed = localStorage.getItem('desktopSidebarCollapsed') === '1';
                if (collapsed) { document.body.classList.add('desktop-sidebar-collapsed'); const btn=document.getElementById('desktop-collapse-btn'); if(btn) btn.setAttribute('aria-pressed','true'); }
                // restore groups
                ['group-super-admin','group-manajemen-data','group-absensi','group-izin','group-settings'].forEach(id=>{
                    const val = localStorage.getItem('sbgrp:' + id);
                    const el = document.getElementById(id); const chev = document.getElementById('chev-' + id);
                    if (el && val === '0') { el.classList.add('hidden'); if (chev) chev.textContent = '▸'; }
                });
            }catch(e){}
        })();
    </script>
    
    <script>
        // Handle logout with CSRF token refresh
        document.addEventListener('DOMContentLoaded', function() {
            const logoutForm = document.getElementById('logout-form');
            if (logoutForm) {
                logoutForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Get fresh CSRF token
                    fetch('/login', {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }).then(response => {
                        if (response.ok) {
                            // Update CSRF token in form
                            const csrfToken = document.querySelector('meta[name="csrf-token"]');
                            if (csrfToken) {
                                const csrfInput = logoutForm.querySelector('input[name="_token"]');
                                if (csrfInput) {
                                    csrfInput.value = csrfToken.getAttribute('content');
                                }
                            }
                            
                            // Submit form
                            logoutForm.submit();
                        } else {
                            // Fallback: submit form anyway
                            logoutForm.submit();
                        }
                    }).catch(error => {
                        console.log('CSRF refresh failed, submitting anyway:', error);
                        logoutForm.submit();
                    });
                });
            }
        });
    </script>
    
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\Users\FHL\.cursor\presensia-v2\starter-kit\resources\views/layouts/app.blade.php ENDPATH**/ ?>