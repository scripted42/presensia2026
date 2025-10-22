<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Presensia')</title>
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
        /* Inspired by Bedimcode responsive sidebar dark/light [1] */
        :root{
            --first-color:#3B82F6; /* blue-500 */
            --text-color:#374151; /* gray-700 */
            --body-color:#F9FAFB; /* gray-50 */
            --container-color:#FFFFFF;
            --hover-color:#EEF2FF; /* indigo-50 */
        }
        body.dark-theme{
            --text-color:#D1D5DB; /* gray-300 */
            --body-color:#0F172A; /* slate-900 */
            --container-color:#111827; /* gray-900 */
            --hover-color:#1F2937; /* gray-800 */
        }
        body{background:var(--body-color)}
        /* Sidebar look & active indicator */
        nav[aria-label='Sidebar'] a{
            color:var(--text-color);
            border-radius:12px;
            position:relative;
            display:flex;align-items:center;gap:10px;
            padding:10px 12px;
        }
        nav[aria-label='Sidebar'] a:hover{background:var(--hover-color)}
        nav[aria-label='Sidebar'] a[aria-current='page']{
            background:rgba(59,130,246,0.12);
            box-shadow:0 1px 0 rgba(0,0,0,.03) inset;
        }
        /* Active indicator bar */
        nav[aria-label='Sidebar'] a[aria-current='page']::before{
            content:'';position:absolute;left:-10px;top:10px;bottom:10px;width:3px;background:var(--first-color);border-radius:8px;
        }
        /* Section divider subtle */
        .sb-divider{height:1px;background:rgba(107,114,128,.15);margin:12px 8px}
        .sb-icon{width:18px;text-align:center;color:#6B7280}
        nav[aria-label='Sidebar'] a:hover .sb-icon{color:#4B5563}
        nav[aria-label='Sidebar'] a[aria-current='page'] .sb-icon{color:#2563EB}
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
        /* removed color accents */
        /* Desktop sidebar collapse */
        .desktop-sidebar{transition:width .2s ease;}
        body.desktop-sidebar-collapsed #desktop-sidebar{display:none !important;}
        /* Icon rail when collapsed */
        .sidebar-rail{display:none}
        body.desktop-sidebar-collapsed .sidebar-rail{display:flex}
        .rail-tooltip{position:absolute;left:56px;top:50%;transform:translateY(-50%);white-space:nowrap;background:#111827;color:#fff;font-size:12px;padding:4px 8px;border-radius:6px;opacity:0;pointer-events:none;transition:opacity .12s ease}
        .rail-item{position:relative}
        .rail-item:hover .rail-tooltip{opacity:1}
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50">
    <a href="#main-content" class="sr-only skip-link">Lewati ke konten</a>
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="hidden md:flex md:w-64 md:flex-col desktop-sidebar" id="desktop-sidebar" aria-label="Sidebar">
            <div class="flex flex-col flex-grow pt-5 bg-white overflow-y-auto border-r border-gray-200" role="complementary">
                <!-- Logo -->
                <div class="flex items-center flex-shrink-0 px-4">
                    <img src="{{ asset('assets/images/logo/presensia-logo.png') }}" alt="Presensia" class="h-4 w-auto" />
                </div>
                
                <!-- Navigation -->
                <nav class="mt-5 flex-1 px-2 space-y-1" role="navigation" aria-label="Sidebar">
                    <!-- Dashboard - Always visible -->
                    @php($isSuper = strtolower(auth()->user()->email) === strtolower(config('app.super_admin_email', env('APP_SUPER_ADMIN_EMAIL', 'superadmin@presensia.com'))))
                    <a href="{{ $isSuper ? route('super-admin.index') : route('dashboard') }}" class="group flex items-center text-sm font-medium rounded-md {{ request()->routeIs($isSuper ? 'super-admin.index' : 'dashboard') ? 'text-blue-900' : '' }}" aria-current="{{ request()->routeIs($isSuper ? 'super-admin.index' : 'dashboard') ? 'page' : 'false' }}">
                        <i class="sb-icon fas fa-home"></i>
                        Dashboard
                    </a>
                    
                    <!-- SUPER ADMIN MENU -->
                    @if($isSuper)
                        <div class="space-y-1">
                            <button type="button" class="w-full text-left px-2 py-1 text-xs font-semibold text-gray-500 uppercase tracking-wider flex items-center justify-between hover:text-gray-700" onclick="toggleSidebarGroup('group-super-admin')">
                                <span>Super Admin</span>
                                <span id="chev-group-super-admin">▾</span>
                            </button>
                            <div id="group-super-admin" class="mt-1 space-y-1">
                            <a href="{{ route('super-admin.schools.index') }}" class="group flex items-center text-sm font-medium rounded-md {{ request()->routeIs('super-admin.schools.*') ? 'text-blue-900' : '' }}" aria-current="{{ request()->routeIs('super-admin.schools.*') ? 'page' : 'false' }}">
                                <i class="sb-icon fas fa-school"></i>
                                Manajemen Sekolah
                            </a>
                            
                            <a href="{{ route('super-admin.audit-trails.index') }}" class="group flex items-center text-sm font-medium rounded-md {{ request()->routeIs('super-admin.audit-trails.*') ? 'text-blue-900' : '' }}" aria-current="{{ request()->routeIs('super-admin.audit-trails.*') ? 'page' : 'false' }}">
                                <i class="sb-icon fas fa-history"></i>
                                Audit Trail
                            </a>
                            
                            <a href="{{ route('super-admin.security.index') }}" class="group flex items-center text-sm font-medium rounded-md {{ request()->routeIs('super-admin.security.*') ? 'text-blue-900' : '' }}" aria-current="{{ request()->routeIs('super-admin.security.*') ? 'page' : 'false' }}">
                                <i class="sb-icon fas fa-shield-alt"></i>
                                Security Monitoring
                            </a>
                            
                    <a href="{{ route('super-admin.security.banned-ips') }}" class="group flex items-center text-sm font-medium rounded-md {{ request()->routeIs('super-admin.security.banned-ips') ? 'text-blue-900' : '' }}" aria-current="{{ request()->routeIs('super-admin.security.banned-ips') ? 'page' : 'false' }}">
                        <i class="sb-icon fas fa-ban"></i>
                        Banned IPs
                    </a>
                    
                            </div>
                        </div>
                    @endif

                    <!-- 1. ABSENSI (Priority 1 - Most Used) -->
                    @if(auth()->user()->hasRole(['teacher', 'tu', 'bk', 'kesiswaan', 'admin', 'headmaster']) && strtolower(auth()->user()->email) !== strtolower(config('app.super_admin_email', env('APP_SUPER_ADMIN_EMAIL', 'superadmin@presensia.com'))))
                        <div class="space-y-1">
                            <button type="button" class="w-full text-left px-2 py-1 text-xs font-semibold text-gray-500 uppercase tracking-wider flex items-center justify-between hover:text-gray-700" onclick="toggleSidebarGroup('group-absensi')">
                                <span>📋 Absensi</span>
                                <span id="chev-group-absensi">▾</span>
                            </button>
                            <div id="group-absensi" class="mt-1 space-y-1">
                            
                            <a href="{{ route('attendance.index') }}" class="group flex items-center text-sm font-medium rounded-md {{ request()->routeIs('attendance.index') ? 'text-blue-900' : '' }}" aria-current="{{ request()->routeIs('attendance.index') ? 'page' : 'false' }}">
                                <i class="sb-icon fas fa-list-check"></i>
                                Status Absensi
                            </a>
                            
                            @if(auth()->user()->hasRole(['teacher', 'tu', 'bk', 'kesiswaan', 'admin']))
                            <a href="{{ route('attendance.check-in') }}" class="group flex items-center text-sm font-medium rounded-md {{ request()->routeIs('attendance.check-in') ? 'text-blue-900' : '' }}" aria-current="{{ request()->routeIs('attendance.check-in') ? 'page' : 'false' }}">
                                <i class="sb-icon fas fa-sign-in-alt"></i>
                                    Absensi Masuk
                                </a>
                                
                            <a href="{{ route('attendance.check-out') }}" class="group flex items-center text-sm font-medium rounded-md {{ request()->routeIs('attendance.check-out') ? 'text-blue-900' : '' }}" aria-current="{{ request()->routeIs('attendance.check-out') ? 'page' : 'false' }}">
                                <i class="sb-icon fas fa-sign-out-alt"></i>
                                    Absensi Keluar
                                </a>
                                
                                @if(auth()->user()->hasRole(['teacher','admin']))
                                    <a href="{{ route('attendance.student-scan') }}" class="group flex items-center text-sm font-medium rounded-md {{ request()->routeIs('attendance.student-scan') ? 'text-blue-900' : '' }}" aria-current="{{ request()->routeIs('attendance.student-scan') ? 'page' : 'false' }}">
                                        <i class="sb-icon fas fa-qrcode"></i>
                                        Scan Siswa
                                </a>
                            @endif
                            
                            @if(auth()->user()->hasRole('admin'))
                            <a href="{{ route('attendance.display-qr') }}" target="_blank" class="group flex items-center text-sm font-medium rounded-md text-gray-600" aria-current="false">
                                <i class="sb-icon fas fa-qrcode"></i>
                                QR Code Absensi
                                    </a>
                                @endif
                            @endif
                            
                            <a href="{{ route('attendance.reports') }}" class="group flex items-center text-sm font-medium rounded-md {{ request()->routeIs('attendance.reports') ? 'text-blue-900' : '' }}" aria-current="{{ request()->routeIs('attendance.reports') ? 'page' : 'false' }}">
                                <i class="sb-icon fas fa-chart-bar"></i>
                                Laporan Absensi
                            </a>
                            </div>
                        </div>
                    @endif

                    <!-- 2. JADWAL & INFORMASI (Priority 2 - Schedule & Info) -->
                    @if(strtolower(auth()->user()->email) !== strtolower(config('app.super_admin_email', env('APP_SUPER_ADMIN_EMAIL', 'superadmin@presensia.com'))))
                        <div class="space-y-1">
                            <button type="button" class="w-full text-left px-2 py-1 text-xs font-semibold text-gray-500 uppercase tracking-wider flex items-center justify-between hover:text-gray-700" onclick="toggleSidebarGroup('group-jadwal')">
                                <span>📅 Jadwal & Info</span>
                                <span id="chev-group-jadwal">▾</span>
                            </button>
                            <div id="group-jadwal" class="mt-1 space-y-1">
                            
                            <a href="{{ route('schedule.index') }}" class="group flex items-center text-sm font-medium rounded-md {{ request()->routeIs('schedule.*') ? 'text-blue-900' : '' }}" aria-current="{{ request()->routeIs('schedule.*') ? 'page' : 'false' }}">
                                <i class="sb-icon fas fa-calendar-check"></i>
                                Jadwal Absensi
                            </a>
                            
                            @if(auth()->user()->hasRole('admin'))
                            <a href="{{ route('admin.holidays.index') }}" class="group flex items-center text-sm font-medium rounded-md {{ request()->routeIs('admin.holidays.*') ? 'text-blue-900' : '' }}">
                                <i class="sb-icon fas fa-calendar-times"></i>
                                Kelola Hari Libur
                            </a>
                            
                            <a href="{{ route('admin.special-schedules.index') }}" class="group flex items-center text-sm font-medium rounded-md {{ request()->routeIs('admin.special-schedules.*') ? 'text-blue-900' : '' }}">
                                <i class="sb-icon fas fa-calendar-alt"></i>
                                Kelola Jadwal Khusus
                            </a>
                            @endif
                            </div>
                        </div>
                    @endif

                    <!-- 3. IZIN & CUTI (Priority 3 - Leave Management) -->
                    @if(auth()->user()->hasRole(['teacher', 'tu', 'bk', 'kesiswaan', 'admin', 'headmaster']) && strtolower(auth()->user()->email) !== strtolower(config('app.super_admin_email', env('APP_SUPER_ADMIN_EMAIL', 'superadmin@presensia.com'))))
                        <div class="space-y-1">
                            <button type="button" class="w-full text-left px-2 py-1 text-xs font-semibold text-gray-500 uppercase tracking-wider flex items-center justify-between hover:text-gray-700" onclick="toggleSidebarGroup('group-izin')">
                                <span>📝 Izin & Cuti</span>
                                <span id="chev-group-izin">▾</span>
                            </button>
                            <div id="group-izin" class="mt-1 space-y-1">
                            
                            @php($pendingApprovals = (auth()->user()->hasRole('headmaster') ? \App\Models\LeaveRequest::where('status','pending')->where('school_id', auth()->user()->school_id)->count() : 0))
                            <a href="{{ route('leave-requests.index') }}" class="group flex items-center text-sm font-medium rounded-md {{ request()->routeIs('leave-requests.*') ? 'text-blue-900' : '' }}" aria-current="{{ request()->routeIs('leave-requests.*') ? 'page' : 'false' }}">
                                <i class="sb-icon fas fa-clipboard-list"></i>
                                @if(auth()->user()->hasRole('headmaster'))
                                    Persetujuan Izin
                                    @if($pendingApprovals > 0)
                                        <span class="ml-auto inline-flex items-center justify-center px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-800">{{ $pendingApprovals }}</span>
                                    @endif
                                @else
                                    Manajemen Izin
                                @endif
                            </a>
                            </div>
                        </div>
                    @endif

                    <!-- 4. MENU SISWA (Student Only) -->
                    @if(auth()->user()->hasRole('student') && strtolower(auth()->user()->email) !== strtolower(config('app.super_admin_email', env('APP_SUPER_ADMIN_EMAIL', 'superadmin@presensia.com'))))
                        <div class="space-y-1">
                            <button type="button" class="w-full text-left px-2 py-1 text-xs font-semibold text-gray-500 uppercase tracking-wider flex items-center justify-between hover:text-gray-700" onclick="toggleSidebarGroup('group-siswa')">
                                <span>🎓 Menu Siswa</span>
                                <span id="chev-group-siswa">▾</span>
                            </button>
                            <div id="group-siswa" class="mt-1 space-y-1">
                            
                            <a href="{{ route('attendance.reports') }}" class="group flex items-center text-sm font-medium rounded-md {{ request()->routeIs('attendance.reports') ? 'text-blue-900' : '' }}" aria-current="{{ request()->routeIs('attendance.reports') ? 'page' : 'false' }}">
                                <i class="sb-icon fas fa-chart-bar"></i>
                                Riwayat Absensi
                            </a>
                            
                            <a href="{{ route('leave-requests.index') }}" class="group flex items-center text-sm font-medium rounded-md {{ request()->routeIs('leave-requests.*') ? 'text-blue-900' : '' }}" aria-current="{{ request()->routeIs('leave-requests.*') ? 'page' : 'false' }}">
                                <i class="sb-icon fas fa-clipboard-list"></i>
                                Izin Saya
                            </a>
                            </div>
                        </div>
                    @endif

                    <!-- 5. MANAJEMEN DATA (Admin Only) -->
                    @if(auth()->user()->hasRole('admin') && strtolower(auth()->user()->email) !== strtolower(config('app.super_admin_email', env('APP_SUPER_ADMIN_EMAIL', 'superadmin@presensia.com'))))
                        <div class="space-y-1">
                            <button type="button" class="w-full text-left px-2 py-1 text-xs font-semibold text-gray-500 uppercase tracking-wider flex items-center justify-between hover:text-gray-700" onclick="toggleSidebarGroup('group-manajemen-data')">
                                <span>👥 Manajemen Data</span>
                                <span id="chev-group-manajemen-data">▾</span>
                            </button>
                            <div id="group-manajemen-data" class="mt-1 space-y-1">
                            
                            <a href="{{ route('users.index', ['type' => 'employee']) }}" class="group flex items-center text-sm font-medium rounded-md {{ request()->routeIs('users.*') && request('type') == 'employee' ? 'text-blue-900' : '' }}" aria-current="{{ request()->routeIs('users.*') && request('type') == 'employee' ? 'page' : 'false' }}">
                                <i class="sb-icon fas fa-user-tie"></i>
                                Data Pegawai
                            </a>
                            
                            <a href="{{ route('users.create', ['type' => 'employee']) }}" class="group flex items-center text-sm font-medium rounded-md {{ request()->routeIs('users.create') && request('type') == 'employee' ? 'text-blue-900' : '' }}" aria-current="{{ request()->routeIs('users.create') && request('type') == 'employee' ? 'page' : 'false' }}">
                                <i class="sb-icon fas fa-user-plus"></i>
                                Tambah Pegawai
                            </a>
                            
                            <a href="{{ route('users.index', ['type' => 'student']) }}" class="group flex items-center text-sm font-medium rounded-md {{ request()->routeIs('users.*') && request('type') == 'student' ? 'text-blue-900' : '' }}" aria-current="{{ request()->routeIs('users.*') && request('type') == 'student' ? 'page' : 'false' }}">
                                <i class="sb-icon fas fa-user-graduate"></i>
                                Data Siswa
                            </a>
                            
                            <a href="{{ route('users.create', ['type' => 'student']) }}" class="group flex items-center text-sm font-medium rounded-md {{ request()->routeIs('users.create') && request('type') == 'student' ? 'text-blue-900' : '' }}" aria-current="{{ request()->routeIs('users.create') && request('type') == 'student' ? 'page' : 'false' }}">
                                <i class="sb-icon fas fa-user-plus"></i>
                                Tambah Siswa
                            </a>
                            </div>
                        </div>
                    @endif

                    <!-- 6. PENGATURAN (Admin Only) -->
                    @if(auth()->user()->hasRole('admin') && strtolower(auth()->user()->email) !== strtolower(config('app.super_admin_email', env('APP_SUPER_ADMIN_EMAIL', 'superadmin@presensia.com'))))
                        <div class="space-y-1">
                            <button type="button" class="w-full text-left px-2 py-1 text-xs font-semibold text-gray-500 uppercase tracking-wider flex items-center justify-between hover:text-gray-700" onclick="toggleSidebarGroup('group-settings')">
                                <span>⚙️ Pengaturan</span>
                                <span id="chev-group-settings">▾</span>
                            </button>
                            <div id="group-settings" class="mt-1 space-y-1">
                            
                            <a href="{{ route('settings.attendance') }}" class="group flex items-center text-sm font-medium rounded-md {{ request()->routeIs('settings.*') ? 'text-blue-900' : '' }}">
                                <i class="sb-icon fas fa-sliders-h"></i>
                                Pengaturan Absensi
                            </a>
                            
                            <a href="{{ route('qr.index') }}" class="group flex items-center text-sm font-medium rounded-md {{ request()->routeIs('qr.*') ? 'text-blue-900' : '' }}">
                                <i class="sb-icon fas fa-qrcode"></i>
                                QR Code Management
                            </a>
                            
                            <a href="{{ route('admin.roles.index') }}" class="group flex items-center text-sm font-medium rounded-md {{ request()->routeIs('admin.roles.*') ? 'text-blue-900' : '' }}">
                                <i class="sb-icon fas fa-user-shield"></i>
                                Manajemen Role
                            </a>
                            
                            <a href="{{ route('admin.permissions.index') }}" class="group flex items-center text-sm font-medium rounded-md {{ request()->routeIs('admin.permissions.*') ? 'text-blue-900' : '' }}">
                                <i class="sb-icon fas fa-key"></i>
                                Manajemen Permission
                            </a>
                            
                            <a href="{{ route('tenant.settings') }}" class="group flex items-center text-sm font-medium rounded-md {{ request()->routeIs('tenant.*') ? 'text-blue-900' : '' }}">
                                <i class="sb-icon fas fa-palette"></i>
                                Kustomisasi Aplikasi
                            </a>
                            
                            <a href="{{ route('performance.index') }}" class="group flex items-center text-sm font-medium rounded-md {{ request()->routeIs('performance.*') ? 'text-blue-900' : '' }}">
                                <i class="sb-icon fas fa-tachometer-alt"></i>
                                Performance Dashboard
                            </a>
                            </div>
                        </div>
                    @endif
                </nav>
            </div>
        </div>

        <!-- Icon rail (collapsed) -->
        <div class="sidebar-rail hidden md:flex md:flex-col bg-white border-r border-gray-200" aria-label="Collapsed Sidebar" style="width:56px">
            <div class="pt-5 flex flex-col items-center space-y-3">
                <a href="{{ route('dashboard') }}" class="rail-item text-gray-500 hover:text-gray-800" title="Dashboard" aria-label="Dashboard">
                    <i class="fas fa-home"></i>
                    <span class="rail-tooltip">Dashboard</span>
                </a>
                <a href="{{ route('schedule.index') }}" class="rail-item text-gray-500 hover:text-gray-800" title="Jadwal Absensi" aria-label="Jadwal Absensi">
                    <i class="fas fa-calendar-check"></i>
                    <span class="rail-tooltip">Jadwal Absensi</span>
                </a>
                @if(auth()->user()->hasRole(['teacher','tu','bk','kesiswaan','admin','headmaster']))
                <a href="{{ route('attendance.index') }}" class="rail-item text-gray-500 hover:text-gray-800" title="Status Absensi" aria-label="Status Absensi">
                    <i class="fas fa-list-check"></i>
                    <span class="rail-tooltip">Status Absensi</span>
                </a>
                <a href="{{ route('attendance.check-in') }}" class="rail-item text-gray-500 hover:text-gray-800" title="Absensi Masuk" aria-label="Absensi Masuk">
                    <i class="fas fa-sign-in-alt"></i>
                    <span class="rail-tooltip">Absensi Masuk</span>
                </a>
                <a href="{{ route('attendance.check-out') }}" class="rail-item text-gray-500 hover:text-gray-800" title="Absensi Keluar" aria-label="Absensi Keluar">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="rail-tooltip">Absensi Keluar</span>
                </a>
                @if(auth()->user()->hasRole(['teacher','admin']))
                <a href="{{ route('attendance.student-scan') }}" class="rail-item text-gray-500 hover:text-gray-800" title="Scan Siswa" aria-label="Scan Siswa">
                    <i class="fas fa-qrcode"></i>
                    <span class="rail-tooltip">Scan Siswa</span>
                </a>
                @endif
                <a href="{{ route('attendance.reports') }}" class="rail-item text-gray-500 hover:text-gray-800" title="Laporan Absensi" aria-label="Laporan Absensi">
                    <i class="fas fa-chart-bar"></i>
                    <span class="rail-tooltip">Laporan</span>
                </a>
                @endif
                @if(strtolower(auth()->user()->email) !== strtolower(config('app.super_admin_email', env('APP_SUPER_ADMIN_EMAIL', 'superadmin@presensia.com'))))
                <a href="{{ route('schedule.index') }}" class="rail-item text-gray-500 hover:text-gray-800" title="Jadwal Absensi" aria-label="Jadwal Absensi">
                    <i class="fas fa-calendar-check"></i>
                    <span class="rail-tooltip">Jadwal</span>
                </a>
                @endif
                @if(auth()->user()->hasRole(['admin','headmaster']))
                <a href="{{ route('leave-requests.index') }}" class="rail-item text-gray-500 hover:text-gray-800" title="Izin & Cuti" aria-label="Izin & Cuti">
                    <i class="fas fa-clipboard-list"></i>
                    <span class="rail-tooltip">Izin & Cuti</span>
                </a>
                @endif
            </div>
        </div>

        <!-- Mobile sidebar - Hidden since we use direct home link -->
        <div class="md:hidden hidden" id="mobile-sidebar" style="display: none;" role="dialog" aria-modal="true" aria-label="Menu">
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
                            <img src="{{ asset('assets/images/logo/presensia-logo.png') }}" alt="Presensia" class="h-4 w-auto" />
                        </div>
                        <nav class="mt-5 px-2 space-y-1">
                            <!-- Same navigation as desktop -->
                            <a href="{{ route('dashboard') }}" class="group flex items-center px-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('dashboard') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
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
                <a href="{{ route('dashboard') }}" aria-label="Dashboard" class="flex items-center justify-center px-4 border-r border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500 md:hidden min-h-[64px]">
                    <i class="fas fa-home text-lg"></i>
                </a>
                <button type="button" id="desktop-collapse-btn" onclick="toggleDesktopSidebar()" aria-label="Collapse sidebar" aria-pressed="false" class="ml-2 px-3 text-gray-500 hover:text-gray-700 hidden md:inline-flex items-center focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500">
                    <i class="fas fa-columns"></i>
                </button>
                
                <div class="flex-1 px-4 flex justify-end">
                    <div class="flex items-center space-x-3">
                        <!-- User info minimal -->
                        <div class="text-right">
                            <div class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</div>
                            <div class="text-xs text-gray-500">{{ auth()->user()->roles->first()->display_name ?? auth()->user()->roles->first()->name ?? 'No Role' }}</div>
                        </div>
                        
                        <!-- Avatar -->
                        <img class="h-8 w-8 rounded-full" src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=3B82F6&color=fff" alt="{{ auth()->user()->name }}">
                        
                        <!-- Logout button minimal -->
                        <form method="POST" action="{{ route('logout') }}" id="logout-form">
                            @csrf
                            <button type="submit" class="p-2 text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-sign-out-alt"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Announcement Bar -->
            @if(auth()->user()->school->tenantSettings && auth()->user()->school->tenantSettings->show_announcement && auth()->user()->school->tenantSettings->topbar_announcement)
                <div class="bg-blue-50 border-b border-blue-200 px-4 py-2">
                    <div class="flex items-center justify-center">
                        <i class="fas fa-bullhorn text-blue-600 mr-2"></i>
                        <p class="text-sm text-blue-800 text-center">{{ auth()->user()->school->tenantSettings->topbar_announcement }}</p>
                    </div>
                </div>
            @endif

            <!-- Page content -->
            <main id="main-content" class="flex-1 relative overflow-y-auto focus:outline-none" tabindex="-1">
                <div class="py-6">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
                        @yield('content')
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
    
    @stack('scripts')
</body>
</html>
