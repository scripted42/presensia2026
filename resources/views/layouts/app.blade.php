<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Presensia')</title>
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
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="hidden md:flex md:w-64 md:flex-col">
            <div class="flex flex-col flex-grow pt-5 bg-white overflow-y-auto border-r border-gray-200">
                <!-- Logo -->
                <div class="flex items-center flex-shrink-0 px-4">
                    <i class="fas fa-graduation-cap text-blue-600 text-2xl mr-3"></i>
                    <span class="text-xl font-bold text-gray-900">Presensia</span>
                </div>
                
                <!-- Navigation -->
                <nav class="mt-5 flex-1 px-2 space-y-1">
                    <!-- Dashboard - tenant or SaaS -->
                    @php($isSuper = strtolower(auth()->user()->email) === strtolower(config('app.super_admin_email', env('APP_SUPER_ADMIN_EMAIL', 'superadmin@presensia.com'))))
                    <a href="{{ $isSuper ? route('super-admin.index') : route('dashboard') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs($isSuper ? 'super-admin.*' : 'dashboard') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <i class="fas fa-home mr-3 text-gray-400 group-hover:text-gray-500"></i>
                        Dashboard
                    </a>

                    <!-- 1. MANAJEMEN DATA (Admin Only) -->
                    @if(auth()->user()->hasRole('admin') && strtolower(auth()->user()->email) !== strtolower(config('app.super_admin_email', env('APP_SUPER_ADMIN_EMAIL', 'superadmin@presensia.com'))))
                        <div class="space-y-1">
                            <div class="px-2 py-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">Manajemen Data</div>
                            
                            <a href="{{ route('users.index', ['type' => 'employee']) }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('users.*') && request('type') == 'employee' ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <i class="fas fa-user-tie mr-3 text-gray-400 group-hover:text-gray-500"></i>
                                Data Pegawai
                            </a>
                            
                            <a href="{{ route('users.create', ['type' => 'employee']) }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('users.create') && request('type') == 'employee' ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <i class="fas fa-user-plus mr-3 text-gray-400 group-hover:text-gray-500"></i>
                                Tambah Pegawai
                            </a>
                            
                            <a href="{{ route('users.index', ['type' => 'student']) }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('users.*') && request('type') == 'student' ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <i class="fas fa-user-graduate mr-3 text-gray-400 group-hover:text-gray-500"></i>
                                Data Siswa
                            </a>
                            
                            <a href="{{ route('users.create', ['type' => 'student']) }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('users.create') && request('type') == 'student' ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <i class="fas fa-user-plus mr-3 text-gray-400 group-hover:text-gray-500"></i>
                                Tambah Siswa
                            </a>
                        </div>
                    @endif

                    <!-- 2. ABSENSI (All roles except student) -->
                    @if(auth()->user()->hasRole(['teacher', 'tu', 'bk', 'kesiswaan', 'admin', 'headmaster']) && strtolower(auth()->user()->email) !== strtolower(config('app.super_admin_email', env('APP_SUPER_ADMIN_EMAIL', 'superadmin@presensia.com'))))
                        <div class="space-y-1">
                            <div class="px-2 py-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">Absensi</div>
                            
                            <a href="{{ route('attendance.index') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('attendance.index') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <i class="fas fa-calendar-check mr-3 text-gray-400 group-hover:text-gray-500"></i>
                                Status Absensi
                            </a>
                            
                            @if(auth()->user()->hasRole(['teacher', 'tu', 'bk', 'kesiswaan', 'admin']))
                                <a href="{{ route('attendance.check-in') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('attendance.check-in') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                    <i class="fas fa-sign-in-alt mr-3 text-gray-400 group-hover:text-gray-500"></i>
                                    Absensi Masuk
                                </a>
                                
                                @if(auth()->user()->hasRole('admin'))
                                    <a href="{{ route('attendance.display-qr') }}" target="_blank" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                                        <i class="fas fa-qrcode mr-3 text-gray-400 group-hover:text-gray-500"></i>
                                        QR Code Absensi
                                    </a>
                                @endif
                                
                                <a href="{{ route('attendance.check-out') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('attendance.check-out') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                    <i class="fas fa-sign-out-alt mr-3 text-gray-400 group-hover:text-gray-500"></i>
                                    Absensi Keluar
                                </a>
                                
                                @if(auth()->user()->hasRole('teacher'))
                                    <a href="{{ route('attendance.student-scan') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('attendance.student-scan') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                        <i class="fas fa-qrcode mr-3 text-gray-400 group-hover:text-gray-500"></i>
                                        Scan Siswa
                                    </a>
                                @endif
                            @endif
                            
                            <a href="{{ route('attendance.reports') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('attendance.reports') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <i class="fas fa-chart-bar mr-3 text-gray-400 group-hover:text-gray-500"></i>
                                Laporan Absensi
                            </a>
                        </div>
                    @endif

                    <!-- 3. IZIN & CUTI (All roles except student) -->
                    @if(auth()->user()->hasRole(['teacher', 'tu', 'bk', 'kesiswaan', 'admin', 'headmaster']) && strtolower(auth()->user()->email) !== strtolower(config('app.super_admin_email', env('APP_SUPER_ADMIN_EMAIL', 'superadmin@presensia.com'))))
                        <div class="space-y-1">
                            <div class="px-2 py-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">Izin & Cuti</div>
                            
                            <a href="{{ route('leave-requests.index') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('leave-requests.*') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <i class="fas fa-calendar-times mr-3 text-gray-400 group-hover:text-gray-500"></i>
                                @if(auth()->user()->hasRole('headmaster'))
                                    Persetujuan Izin
                                @else
                                    Manajemen Izin
                                @endif
                            </a>
                        </div>
                    @endif

                    <!-- 4. SISWA (Student only) -->
                    @if(auth()->user()->hasRole('student') && strtolower(auth()->user()->email) !== strtolower(config('app.super_admin_email', env('APP_SUPER_ADMIN_EMAIL', 'superadmin@presensia.com'))))
                        <div class="space-y-1">
                            <div class="px-2 py-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">Siswa</div>
                            
                            <a href="{{ route('attendance.reports') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('attendance.reports') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <i class="fas fa-chart-bar mr-3 text-gray-400 group-hover:text-gray-500"></i>
                                Riwayat Absensi
                            </a>

                            <a href="{{ route('leave-requests.index') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('leave-requests.*') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <i class="fas fa-calendar-times mr-3 text-gray-400 group-hover:text-gray-500"></i>
                                Izin Saya
                            </a>
                        </div>
                    @endif

                    <!-- 5. PENGATURAN (Admin Only) -->
                    @if(auth()->user()->hasRole('admin') && strtolower(auth()->user()->email) !== strtolower(config('app.super_admin_email', env('APP_SUPER_ADMIN_EMAIL', 'superadmin@presensia.com'))))
                        <div class="space-y-1">
                            <div class="px-2 py-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">Pengaturan</div>
                            
                            <a href="{{ route('settings.attendance') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('settings.*') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <i class="fas fa-cog mr-3 text-gray-400 group-hover:text-gray-500"></i>
                                Pengaturan Absensi
                            </a>

                            <a href="{{ route('qr.index') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('qr.*') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <i class="fas fa-qrcode mr-3 text-gray-400 group-hover:text-gray-500"></i>
                                QR Code Management
                            </a>
                            
                            <a href="{{ route('admin.roles.index') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.roles.*') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <i class="fas fa-shield-alt mr-3 text-gray-400 group-hover:text-gray-500"></i>
                                Manajemen Role
                            </a>
                            
                            <a href="{{ route('admin.permissions.index') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.permissions.*') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <i class="fas fa-key mr-3 text-gray-400 group-hover:text-gray-500"></i>
                                Manajemen Permission
                            </a>
                            
                            <a href="{{ route('tenant.settings') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('tenant.*') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <i class="fas fa-palette mr-3 text-gray-400 group-hover:text-gray-500"></i>
                                Kustomisasi Aplikasi
                            </a>
                        </div>
                    @endif
                </nav>
            </div>
        </div>

        <!-- Mobile sidebar -->
        <div class="md:hidden" id="mobile-sidebar" style="display: none;">
            <div class="fixed inset-0 flex z-40">
                <div class="fixed inset-0 bg-gray-600 bg-opacity-75" onclick="toggleMobileSidebar()"></div>
                <div class="relative flex-1 flex flex-col max-w-xs w-full bg-white">
                    <div class="absolute top-0 right-0 -mr-12 pt-2">
                        <button onclick="toggleMobileSidebar()" class="ml-1 flex items-center justify-center h-10 w-10 rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white">
                            <i class="fas fa-times text-white"></i>
                        </button>
                    </div>
                    <div class="flex-1 h-0 pt-5 pb-4 overflow-y-auto">
                        <div class="flex-shrink-0 flex items-center px-4">
                            <i class="fas fa-graduation-cap text-blue-600 text-2xl mr-3"></i>
                            <span class="text-xl font-bold text-gray-900">Presensia</span>
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
            <div class="relative z-10 flex-shrink-0 flex h-16 bg-white shadow">
                <button onclick="toggleMobileSidebar()" class="px-4 border-r border-gray-200 text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500 md:hidden">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="flex-1 px-4 flex justify-between">
                    <div class="flex-1 flex">
                        <div class="w-full flex md:ml-0">
                            <div class="relative w-full text-gray-400 focus-within:text-gray-600">
                                <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none">
                                    <i class="fas fa-search"></i>
                                </div>
                                <input class="block w-full h-full pl-8 pr-3 py-2 border-transparent text-gray-900 placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-0 focus:border-transparent" placeholder="Search..." type="search">
                            </div>
                        </div>
                    </div>
                    
                    <div class="ml-4 flex items-center md:ml-6">
                        <!-- User menu -->
                        <div class="ml-3 relative">
                            <div class="flex items-center">
                                <img class="h-8 w-8 rounded-full" src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=3B82F6&color=fff" alt="{{ auth()->user()->name }}">
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-700">{{ auth()->user()->name }}</div>
                                    <div class="text-xs text-gray-500">{{ auth()->user()->roles->first()->display_name ?? auth()->user()->roles->first()->name ?? 'No Role' }}</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Logout button -->
                        <form method="POST" action="{{ route('logout') }}" class="ml-3">
                            @csrf
                            <button type="submit" class="bg-white p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-sign-out-alt"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Page content -->
            <main class="flex-1 relative overflow-y-auto focus:outline-none">
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
    </script>
    @stack('scripts')
</body>
</html>
