<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Presensia')</title>
    
    <!-- PWA Meta Tags -->
    <meta name="application-name" content="Presensia">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Presensia">
    <meta name="description" content="Sistem absensi digital untuk sekolah dengan fitur GPS, QR Code, dan laporan real-time">
    <meta name="format-detection" content="telephone=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="msapplication-config" content="/icons/browserconfig.xml">
    <meta name="msapplication-TileColor" content="#3b82f6">
    <meta name="msapplication-tap-highlight" content="no">
    <meta name="theme-color" content="#3b82f6">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.json">
    
    <!-- Apple Touch Icons -->
    <link rel="apple-touch-icon" href="/icons/icon-152x152.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/icons/icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/icons/icon-192x192.png">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="/icons/icon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/icons/icon-16x16.png">
    <link rel="shortcut icon" href="/icons/icon-32x32.png">
    
    <!-- Microsoft Tiles -->
    <meta name="msapplication-TileImage" content="/icons/icon-144x144.png">
    <meta name="msapplication-square70x70logo" content="/icons/icon-72x72.png">
    <meta name="msapplication-square150x150logo" content="/icons/icon-144x144.png">
    <meta name="msapplication-wide310x150logo" content="/icons/icon-192x192.png">
    <meta name="msapplication-square310x310logo" content="/icons/icon-512x512.png">
    <!-- Resource Hints: speed up hard refresh by preconnecting to CDNs -->
    <link rel="dns-prefetch" href="//cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="dns-prefetch" href="//cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- Google Fonts: Inter, Baloo 2, Manrope -->
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;600;700;800&family=Inter:wght@300;400;500;600;700;800&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/daisyui@latest/dist/full.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
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
        body {
            font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: var(--body-color);
            padding-top: env(safe-area-inset-top, 0px);
            padding-bottom: env(safe-area-inset-bottom, 0px);
            padding-left: env(safe-area-inset-left, 0px);
            padding-right: env(safe-area-inset-right, 0px);
        }
        @media (max-width: 768px) {
            #main-content {
                padding-bottom: 80px; /* space for bottom tab bar */
            }
        }
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

        /* ============================================================
           DESKTOP LAYOUT OPTIMIZATION — memanfaatkan lebar layar penuh
           Hanya berlaku untuk breakpoint desktop (≥1024px)
           Mobile/PWA TIDAK terpengaruh (semua di bawah @media min-width)
           ============================================================ */

        /* Container konten utama: hapus max-width sempit, gunakan lebar penuh */
        @media (min-width: 1024px) {
            .desktop-content-wrapper {
                width: 100%;
                max-width: 100%;
                padding-left: 1.5rem;   /* 24px */
                padding-right: 1.5rem;
                margin-left: 0;
                margin-right: 0;
            }
        }

        /* Layar ekstra lebar (≥1440px): padding sedikit lebih besar untuk breathing room */
        @media (min-width: 1440px) {
            .desktop-content-wrapper {
                padding-left: 2rem;   /* 32px */
                padding-right: 2rem;
            }
        }

        /* Override max-w-7xl yang ada di dalam halaman konten super-admin, dll */
        @media (min-width: 1024px) {
            .desktop-content-wrapper .max-w-7xl {
                max-width: 100% !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }
        }

        /* Grid cards dashboard: lebih banyak kolom di layar lebar */
        @media (min-width: 1280px) {
            .desktop-stats-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }
        @media (min-width: 1536px) {
            .desktop-stats-grid {
                grid-template-columns: repeat(5, minmax(0, 1fr));
            }
        }

        /* Tabel: kolom proporsional & sticky header untuk scroll nyaman */
        @media (min-width: 1024px) {
            .desktop-table thead th {
                position: sticky;
                top: 0;
                z-index: 1;
                background: #F9FAFB;
            }
            .desktop-table {
                width: 100%;
            }
            /* Auto-apply ke semua tabel min-w-full di dalam content wrapper */
            .desktop-content-wrapper table.min-w-full {
                width: 100%;
                table-layout: auto;
            }
            .desktop-content-wrapper .overflow-x-auto {
                overflow-x: visible; /* tabel bisa pakai lebar penuh tanpa scroll horizontal */
            }
        }

        /* Form grid: lebih banyak kolom input di desktop lebar */
        @media (min-width: 1280px) {
            .desktop-form-grid-3 {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
            .desktop-form-grid-4 {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        /* Empty state: proporsional di layar lebar */
        @media (min-width: 1024px) {
            .desktop-empty-state {
                padding: 4rem 2rem;
            }
            .desktop-empty-state .empty-icon {
                font-size: 4rem;
            }
            .desktop-empty-state .empty-title {
                font-size: 1.25rem;
            }
        }

        /* Card hover effect */
        .card-hover {
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }
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
                        @php($actAbsensi = request()->routeIs('attendance.*'))
                        <div class="space-y-1">
                            <button type="button" class="w-full text-left px-2 py-1 text-xs font-semibold text-gray-500 uppercase tracking-wider flex items-center justify-between hover:text-gray-700" onclick="toggleSidebarGroup('group-absensi')">
                                <span>📋 Absensi</span>
                                <span id="chev-group-absensi">{{ $actAbsensi ? '▾' : '▸' }}</span>
                            </button>
                            <div id="group-absensi" class="mt-1 space-y-1 {{ $actAbsensi ? '' : 'hidden' }}">
                            
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
                        @php($actJadwal = request()->routeIs('schedule.*') || request()->routeIs('admin.holidays.*') || request()->routeIs('admin.special-schedules.*'))
                        <div class="space-y-1">
                            <button type="button" class="w-full text-left px-2 py-1 text-xs font-semibold text-gray-500 uppercase tracking-wider flex items-center justify-between hover:text-gray-700" onclick="toggleSidebarGroup('group-jadwal')">
                                <span>📅 Jadwal & Info</span>
                                <span id="chev-group-jadwal">{{ $actJadwal ? '▾' : '▸' }}</span>
                            </button>
                            <div id="group-jadwal" class="mt-1 space-y-1 {{ $actJadwal ? '' : 'hidden' }}">
                            
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
                        @php($actIzin = request()->routeIs('leave-requests.*'))
                        <div class="space-y-1">
                            <button type="button" class="w-full text-left px-2 py-1 text-xs font-semibold text-gray-500 uppercase tracking-wider flex items-center justify-between hover:text-gray-700" onclick="toggleSidebarGroup('group-izin')">
                                <span>📝 Izin & Cuti</span>
                                <span id="chev-group-izin">{{ $actIzin ? '▾' : '▸' }}</span>
                            </button>
                            <div id="group-izin" class="mt-1 space-y-1 {{ $actIzin ? '' : 'hidden' }}">
                            
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
                        @php($actSiswa = request()->routeIs('attendance.reports') || request()->routeIs('leave-requests.*'))
                        <div class="space-y-1">
                            <button type="button" class="w-full text-left px-2 py-1 text-xs font-semibold text-gray-500 uppercase tracking-wider flex items-center justify-between hover:text-gray-700" onclick="toggleSidebarGroup('group-siswa')">
                                <span>🎓 Menu Siswa</span>
                                <span id="chev-group-siswa">{{ $actSiswa ? '▾' : '▸' }}</span>
                            </button>
                            <div id="group-siswa" class="mt-1 space-y-1 {{ $actSiswa ? '' : 'hidden' }}">
                            
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
                        @php($actData = request()->routeIs('users.*'))
                        <div class="space-y-1">
                            <button type="button" class="w-full text-left px-2 py-1 text-xs font-semibold text-gray-500 uppercase tracking-wider flex items-center justify-between hover:text-gray-700" onclick="toggleSidebarGroup('group-manajemen-data')">
                                <span>👥 Manajemen Data</span>
                                <span id="chev-group-manajemen-data">{{ $actData ? '▾' : '▸' }}</span>
                            </button>
                            <div id="group-manajemen-data" class="mt-1 space-y-1 {{ $actData ? '' : 'hidden' }}">
                            
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
                        @php($actSettings = request()->routeIs('settings.*') || request()->routeIs('qr.*') || request()->routeIs('admin.roles.*') || request()->routeIs('admin.permissions.*') || request()->routeIs('tenant.*'))
                        <div class="space-y-1">
                            <button type="button" class="w-full text-left px-2 py-1 text-xs font-semibold text-gray-500 uppercase tracking-wider flex items-center justify-between hover:text-gray-700" onclick="toggleSidebarGroup('group-settings')">
                                <span>⚙️ Pengaturan</span>
                                <span id="chev-group-settings">{{ $actSettings ? '▾' : '▸' }}</span>
                            </button>
                            <div id="group-settings" class="mt-1 space-y-1 {{ $actSettings ? '' : 'hidden' }}">
                            
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
                            
                            </div>
                        </div>
                    @endif

                    <!-- 7. AKUN (Semua Role) -->
                    <div class="space-y-1 pt-3 border-t border-gray-100">
                        <div class="px-2 py-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                            Akun
                        </div>
                        <a href="{{ route('profile.password') }}" class="group flex items-center text-sm font-medium rounded-md {{ request()->routeIs('profile.password') ? 'text-blue-900 font-semibold' : 'text-gray-600 hover:text-gray-900' }}">
                            <i class="sb-icon fas fa-key"></i>
                            Ganti Password
                        </a>
                    </div>
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
                <a href="{{ route('profile.password') }}" class="rail-item text-gray-500 hover:text-gray-800" title="Ganti Password" aria-label="Ganti Password">
                    <i class="fas fa-key"></i>
                    <span class="rail-tooltip">Ganti Password</span>
                </a>
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
            <div class="relative z-10 flex-shrink-0 flex h-16 bg-white shadow-sm border-b border-gray-100" role="banner">
                <!-- Mobile App Logo -->
                <div class="flex items-center px-4 md:hidden">
                    <a href="{{ route('dashboard') }}">
                        <img src="{{ asset('assets/images/logo/presensia-logo.png') }}" alt="Presensia" class="h-4 w-auto object-contain">
                    </a>
                </div>
                
                <button type="button" id="desktop-collapse-btn" onclick="toggleDesktopSidebar()" aria-label="Collapse sidebar" aria-pressed="false" class="ml-2 px-3 text-gray-500 hover:text-gray-700 hidden md:inline-flex items-center focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500">
                    <i class="fas fa-columns"></i>
                </button>
                
                <div class="flex-1 px-4 flex justify-end">
                    <div class="flex items-center space-x-3">
                        <!-- User info minimal -->
                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-medium text-gray-900 leading-tight">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-500 font-normal mt-0.5">{{ auth()->user()->roles->first()->display_name ?? auth()->user()->roles->first()->name ?? 'No Role' }}</p>
                        </div>
                        
                        <!-- Clickable Avatar on Mobile / Link Profile -->
                        <a href="{{ route('profile.password') }}" title="Ganti Password">
                            <img class="h-8 w-8 rounded-full border border-gray-100 cursor-pointer hover:ring-2 hover:ring-blue-400 transition" src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=3B82F6&color=fff" alt="{{ auth()->user()->name }}">
                        </a>
                        
                        <!-- Ganti Password icon button -->
                        <a href="{{ route('profile.password') }}" class="p-2 text-gray-400 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition rounded-lg hover:bg-gray-100" title="Ganti Password" aria-label="Ganti Password">
                            <i class="fas fa-key text-sm"></i>
                        </a>
                        
                        <!-- Logout button minimal (Desktop only) -->
                        <form method="POST" action="{{ route('logout') }}" id="logout-form" class="hidden md:block">
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
                <div class="py-4 md:py-6 pb-24 md:pb-24">
                    <!-- Mobile: padding standar. Desktop (≥1024px): lebar penuh via .desktop-content-wrapper -->
                    <div class="px-4 sm:px-6 desktop-content-wrapper pb-6">
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
        const sidebarGroups = [
            'group-super-admin',
            'group-absensi',
            'group-jadwal',
            'group-izin',
            'group-siswa',
            'group-manajemen-data',
            'group-settings'
        ];

        function toggleSidebarGroup(id){
            const key = 'sbgrp:' + id;
            const el = document.getElementById(id);
            const chev = document.getElementById('chev-' + id);
            if (!el) return;
            const hidden = el.classList.toggle('hidden');
            if (chev) chev.textContent = hidden ? '▸' : '▾';
            try { localStorage.setItem(key, hidden ? '0' : '1'); } catch(e) {}
        }
        window.toggleSidebarGroup = toggleSidebarGroup;

        function initSidebarGroups() {
            try {
                sidebarGroups.forEach(id => {
                    const el = document.getElementById(id);
                    const chev = document.getElementById('chev-' + id);
                    if (!el) return;

                    // Cek apakah grup berisi link halaman yang sedang aktif
                    const hasActiveChild = el.querySelector('.text-blue-900, [aria-current="page"]') !== null;
                    const storedVal = localStorage.getItem('sbgrp:' + id);

                    let shouldBeOpen = false;
                    if (storedVal !== null) {
                        shouldBeOpen = (storedVal === '1');
                    } else {
                        // Default: HANYA grup yang berisi halaman aktif yang expanded
                        shouldBeOpen = hasActiveChild;
                    }

                    if (shouldBeOpen) {
                        el.classList.remove('hidden');
                        if (chev) chev.textContent = '▾';
                    } else {
                        el.classList.add('hidden');
                        if (chev) chev.textContent = '▸';
                    }
                });
            } catch(e) {}
        }

        (function(){
            try{
                const collapsed = localStorage.getItem('desktopSidebarCollapsed') === '1';
                if (collapsed) { document.body.classList.add('desktop-sidebar-collapsed'); const btn=document.getElementById('desktop-collapse-btn'); if(btn) btn.setAttribute('aria-pressed','true'); }
            }catch(e){}
        })();

        document.addEventListener('DOMContentLoaded', initSidebarGroups);
    </script>
    
    <script>
        // Handle logout
        document.addEventListener('DOMContentLoaded', function() {
            const logoutForm = document.getElementById('logout-form');
            if (logoutForm) {
                logoutForm.addEventListener('submit', function(e) {
                    // Let the form submit normally with POST method
                    // No need to prevent default or refresh CSRF token
                });
            }
        });
    </script>
    
    <!-- PWA JavaScript -->
    <script>
        // PWA Installation and Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => {
                        console.log('PWA: Service Worker registered successfully');
                        
                        // Check for updates
                        registration.addEventListener('updatefound', () => {
                            const newWorker = registration.installing;
                            newWorker.addEventListener('statechange', () => {
                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    // New version available
                                    if (confirm('Versi baru tersedia. Muat ulang halaman?')) {
                                        window.location.reload();
                                    }
                                }
                            });
                        });
                    })
                    .catch(error => {
                        console.log('PWA: Service Worker registration failed:', error);
                    });
            });
        }
        
        // PWA Install Prompt
        let deferredPrompt;
        let installButton;
        
        window.addEventListener('beforeinstallprompt', (e) => {
            console.log('PWA: Install prompt triggered');
            e.preventDefault();
            deferredPrompt = e;
            
            // Show install button if not already installed
            if (!window.matchMedia('(display-mode: standalone)').matches) {
                showInstallButton();
            }
        });
        
        window.addEventListener('appinstalled', () => {
            console.log('PWA: App installed successfully');
            hideInstallButton();
            deferredPrompt = null;
        });
        
        function showInstallButton() {
            // Create install button
            if (!document.getElementById('pwa-install-button')) {
                const installBtn = document.createElement('button');
                installBtn.id = 'pwa-install-button';
                installBtn.innerHTML = '📱 Install Aplikasi';
                installBtn.className = 'fixed bottom-4 right-4 bg-blue-500 text-white px-4 py-2 rounded-full shadow-lg hover:bg-blue-600 transition-all duration-300 z-50';
                installBtn.onclick = installApp;
                document.body.appendChild(installBtn);
            }
        }
        
        function hideInstallButton() {
            const installBtn = document.getElementById('pwa-install-button');
            if (installBtn) {
                installBtn.remove();
            }
        }
        
        function installApp() {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('PWA: User accepted install prompt');
                    } else {
                        console.log('PWA: User dismissed install prompt');
                    }
                    deferredPrompt = null;
                });
            }
        }
        
        // Check if app is running in standalone mode
        if (window.matchMedia('(display-mode: standalone)').matches) {
            console.log('PWA: Running in standalone mode');
            // Hide address bar on mobile
            if (window.innerHeight < window.innerWidth) {
                document.body.classList.add('landscape-mode');
            }
        }
        
        // Handle orientation change
        window.addEventListener('orientationchange', () => {
            setTimeout(() => {
                if (window.matchMedia('(display-mode: standalone)').matches) {
                    if (window.innerHeight < window.innerWidth) {
                        document.body.classList.add('landscape-mode');
                    } else {
                        document.body.classList.remove('landscape-mode');
                    }
                }
            }, 100);
        });
        
        // Offline detection
        window.addEventListener('online', () => {
            console.log('PWA: Back online');
            // Show online indicator
            showNotification('Koneksi internet tersedia', 'success');
        });
        
        window.addEventListener('offline', () => {
            console.log('PWA: Gone offline');
            // Show offline indicator
            showNotification('Tidak ada koneksi internet', 'warning');
        });
        
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 px-4 py-2 rounded-lg shadow-lg z-50 ${
                type === 'success' ? 'bg-green-500' : 
                type === 'warning' ? 'bg-yellow-500' : 
                'bg-blue-500'
            } text-white`;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
        
        // Background sync for attendance data
        function syncAttendanceData() {
            if ('serviceWorker' in navigator && 'sync' in window.ServiceWorkerRegistration.prototype) {
                navigator.serviceWorker.ready.then(registration => {
                    return registration.sync.register('attendance-sync');
                });
            }
        }
        
        // Expose sync function globally
        window.syncAttendanceData = syncAttendanceData;
        
        // Force clear PWA cache for icon update
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then(function(registrations) {
                for(let registration of registrations) {
                    registration.unregister();
                }
                // Clear all caches
                if ('caches' in window) {
                    caches.keys().then(function(names) {
                        for (let name of names) {
                            caches.delete(name);
                        }
                    });
                }
            });
        }
        
        // Initialize Lucide Icons
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });

        // Mobile Menu Bottom Sheet toggles
        function openMenuBottomSheet() {
            const backdrop = document.getElementById('mobile-menu-backdrop');
            const sheet = document.getElementById('mobile-menu-sheet');
            if (backdrop && sheet) {
                backdrop.classList.remove('hidden');
                setTimeout(() => {
                    backdrop.classList.add('opacity-100');
                    sheet.classList.remove('translate-y-full');
                }, 50);
            }
        }

        // Expose to window for inline calls
        window.openMenuBottomSheet = openMenuBottomSheet;

        function closeMenuBottomSheet() {
            const backdrop = document.getElementById('mobile-menu-backdrop');
            const sheet = document.getElementById('mobile-menu-sheet');
            if (backdrop && sheet) {
                backdrop.classList.remove('opacity-100');
                sheet.classList.add('translate-y-full');
                setTimeout(() => {
                    backdrop.classList.add('hidden');
                }, 300);
            }
        }
        window.closeMenuBottomSheet = closeMenuBottomSheet;
    </script>
    
    <!-- Bottom Tab Bar (Mobile Only) -->
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-100 flex justify-around items-center py-2.5 md:hidden z-40 safe-bottom">
        <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-0.5 text-xs font-medium {{ request()->routeIs('dashboard') ? 'text-blue-600' : 'text-gray-400' }}">
            <i data-lucide="home" class="h-5 w-5"></i>
            <span class="mt-0.5">Home</span>
        </a>
        
        @if(auth()->user()->hasRole('student'))
            <a href="{{ route('attendance.reports') }}" class="flex flex-col items-center gap-0.5 text-xs font-medium {{ request()->routeIs('attendance.reports') ? 'text-blue-600' : 'text-gray-400' }}">
                <i data-lucide="calendar" class="h-5 w-5"></i>
                <span class="mt-0.5">Riwayat</span>
            </a>
        @else
            <a href="{{ route('attendance.index') }}" class="flex flex-col items-center gap-0.5 text-xs font-medium {{ request()->routeIs('attendance.index') ? 'text-blue-600' : 'text-gray-400' }}">
                <i data-lucide="calendar" class="h-5 w-5"></i>
                <span class="mt-0.5">Absensi</span>
            </a>
        @endif
        
        <a href="{{ route('leave-requests.index') }}" class="flex flex-col items-center gap-0.5 text-xs font-medium {{ request()->routeIs('leave-requests.*') ? 'text-blue-600' : 'text-gray-400' }}">
            <i data-lucide="file-text" class="h-5 w-5"></i>
            <span class="mt-0.5">Izin</span>
        </a>
        
        <button type="button" onclick="openMenuBottomSheet()" class="flex flex-col items-center gap-0.5 text-xs font-medium text-gray-400 focus:outline-none">
            <i data-lucide="menu" class="h-5 w-5"></i>
            <span class="mt-0.5">Menu</span>
        </button>
    </div>

    <!-- Mobile Menu Bottom Sheet Backdrop -->
    <div id="mobile-menu-backdrop" onclick="closeMenuBottomSheet()" class="fixed inset-0 bg-black bg-opacity-40 z-50 hidden transition-opacity duration-300 opacity-0"></div>

    <!-- Mobile Menu Bottom Sheet -->
    <div id="mobile-menu-sheet" class="fixed bottom-0 left-0 right-0 bg-white rounded-t-3xl z-50 transform translate-y-full transition-transform duration-300 shadow-2xl safe-bottom max-h-[80vh] overflow-y-auto">
        <div class="flex justify-center py-3" onclick="closeMenuBottomSheet()">
            <div class="w-12 h-1 bg-gray-200 rounded-full"></div>
        </div>
        
        <div class="px-6 pb-8">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-50">
                <img class="h-10 w-10 rounded-full border border-gray-100" src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=3B82F6&color=fff" alt="{{ auth()->user()->name }}">
                <div>
                    <p class="text-sm font-medium text-gray-900 leading-tight">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-500 font-normal mt-0.5">{{ auth()->user()->roles->first()->display_name ?? auth()->user()->roles->first()->name ?? 'No Role' }}</p>
                </div>
            </div>
            
            <h5 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Semua Fitur</h5>
            
            <div class="grid grid-cols-3 gap-4">
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center p-3 bg-gray-50 hover:bg-gray-100 rounded-2xl transition-colors">
                    <div class="p-2.5 bg-blue-50 text-blue-600 rounded-xl mb-1.5">
                        <i data-lucide="home" class="h-5 w-5"></i>
                    </div>
                    <span class="text-xs font-medium text-gray-700 text-center">Dashboard</span>
                </a>
                
                @if(auth()->user()->hasRole(['teacher','tu','bk','kesiswaan','admin','headmaster']))
                    <a href="{{ route('attendance.index') }}" class="flex flex-col items-center p-3 bg-gray-50 hover:bg-gray-100 rounded-2xl transition-colors">
                        <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-xl mb-1.5">
                            <i data-lucide="calendar-check" class="h-5 w-5"></i>
                        </div>
                        <span class="text-xs font-medium text-gray-700 text-center">Status Absensi</span>
                    </a>
                @endif
                
                @if(auth()->user()->hasRole(['teacher','admin']))
                    <a href="{{ route('attendance.student-scan') }}" class="flex flex-col items-center p-3 bg-gray-50 hover:bg-gray-100 rounded-2xl transition-colors">
                        <div class="p-2.5 bg-purple-50 text-purple-600 rounded-xl mb-1.5">
                            <i data-lucide="qr-code" class="h-5 w-5"></i>
                        </div>
                        <span class="text-xs font-medium text-gray-700 text-center">Scan Siswa</span>
                    </a>
                @endif

                <a href="{{ route('leave-requests.index') }}" class="flex flex-col items-center p-3 bg-gray-50 hover:bg-gray-100 rounded-2xl transition-colors">
                    <div class="p-2.5 bg-amber-50 text-amber-600 rounded-xl mb-1.5">
                        <i data-lucide="file-text" class="h-5 w-5"></i>
                    </div>
                    <span class="text-xs font-medium text-gray-700 text-center">Izin & Cuti</span>
                </a>
                
                <a href="{{ route('attendance.reports') }}" class="flex flex-col items-center p-3 bg-gray-50 hover:bg-gray-100 rounded-2xl transition-colors">
                    <div class="p-2.5 bg-indigo-50 text-indigo-600 rounded-xl mb-1.5">
                        <i data-lucide="bar-chart-2" class="h-5 w-5"></i>
                    </div>
                    <span class="text-xs font-medium text-gray-700 text-center">Laporan</span>
                </a>

                @if(auth()->user()->hasRole('admin'))
                    <a href="{{ route('users.index') }}" class="flex flex-col items-center p-3 bg-gray-50 hover:bg-gray-100 rounded-2xl transition-colors">
                        <div class="p-2.5 bg-sky-50 text-sky-600 rounded-xl mb-1.5">
                            <i data-lucide="users" class="h-5 w-5"></i>
                        </div>
                        <span class="text-xs font-medium text-gray-700 text-center">Manajemen User</span>
                    </a>
                    <a href="{{ route('tenant.settings') }}" class="flex flex-col items-center p-3 bg-gray-50 hover:bg-gray-100 rounded-2xl transition-colors">
                        <div class="p-2.5 bg-rose-50 text-rose-600 rounded-xl mb-1.5">
                            <i data-lucide="settings" class="h-5 w-5"></i>
                        </div>
                        <span class="text-xs font-medium text-gray-700 text-center">Kustomisasi</span>
                    </a>
                @endif
                
                <button type="button" onclick="document.getElementById('logout-form').submit();" class="flex flex-col items-center p-3 bg-red-50 hover:bg-red-100 rounded-2xl transition-colors w-full focus:outline-none">
                    <div class="p-2.5 bg-red-100 text-red-600 rounded-xl mb-1.5">
                        <i data-lucide="log-out" class="h-5 w-5"></i>
                    </div>
                    <span class="text-xs font-medium text-red-700 text-center">Logout</span>
                </button>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
