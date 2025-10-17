@extends('layouts.app')

@section('title', 'Dashboard - Presensia')

@section('content')
        
        <!-- Welcome Section -->
        <div class="bg-white overflow-hidden shadow rounded-lg mb-6 relative">
            @if($school->tenantSettings && $school->tenantSettings->banner_image)
                @php
                    $bannerScale = $school->tenantSettings->banner_scale ?? 100;
                @endphp
                <div class="banner-custom-image" style="background-image: url('{{ asset('storage/' . $school->tenantSettings->banner_image) }}'); transform: scale({{ $bannerScale / 100 }}); transform-origin: center;"></div>
            @endif
            <!-- School Photo Background -->
            @if($school->tenantSettings && $school->tenantSettings->school_photo)
                @php
                    $positionX = $school->tenantSettings->school_photo_position_x ?? 'center';
                    $positionY = $school->tenantSettings->school_photo_position_y ?? 'center';
                    $scale = $school->tenantSettings->school_photo_scale ?? 100;
                    $opacity = $school->tenantSettings->school_photo_opacity ?? 10;
                    
                    $backgroundPosition = $positionX . ' ' . $positionY;
                @endphp
                <div class="absolute inset-0 bg-cover" 
                     style="background-image: url('{{ asset('storage/' . $school->tenantSettings->school_photo) }}'); 
                            background-position: {{ $backgroundPosition }};
                            opacity: {{ $opacity / 100 }};
                            transform: scale({{ $scale / 100 }});
                            transform-origin: center;"></div>
            @endif
            <div class="px-4 py-5 sm:p-6 welcome-hero-wrap relative z-10">
                <div class="flex items-center">
                    @if($school->logo)
                        <img src="{{ asset('storage/' . $school->logo) }}" alt="{{ $school->name }}" class="h-20 w-auto mr-4">
                    @endif
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 mb-2">Dashboard</h1>
                        @if($school->tenantSettings && $school->tenantSettings->banner_text)
                            <p class="text-gray-600">{{ $school->tenantSettings->banner_text }}</p>
                        @else
                            <p class="text-gray-600">Selamat datang di sistem manajemen absensi sekolah, Presensia!</p>
                        @endif
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap gap-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        <i class="fas fa-building mr-2"></i>{{ $school->name }}
                    </span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        <i class="fas fa-user-tag mr-2"></i>{{ $user->roles->first()->display_name ?? $user->roles->first()->name ?? 'No Role' }}
                    </span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                        <i class="fas fa-user mr-2"></i>{{ $user->user_type === 'employee' ? 'Pegawai' : 'Siswa' }}
                    </span>
                </div>
                <!-- Decorative image on the right - REMOVED -->
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            @if(isset($stats['total_employees']))
            <div class="overflow-hidden shadow rounded-lg border border-gray-200 bg-white">
                <div class="p-5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-users text-blue-600 text-2xl"></i>
                            <div class="text-sm font-medium text-gray-700">Total Pegawai</div>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-semibold text-gray-900">{{ $stats['total_employees'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if(isset($stats['total_students']))
            <div class="overflow-hidden shadow rounded-lg border border-gray-200 bg-white">
                <div class="p-5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-graduation-cap text-green-600 text-2xl"></i>
                            <div class="text-sm font-medium text-gray-700">Total Siswa</div>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-semibold text-gray-900">{{ $stats['total_students'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Kartu "Total Kelas" dihapus sesuai permintaan --}}

            @if(isset($stats['today_attendance']))
            @php
                $isManager = $user->hasRole('admin') || $user->hasRole('headmaster');
                $detail = $stats['today_attendance_detail'] ?? null;
                $completed = ($detail && ($detail['status'] ?? '') === 'completed' && !$isManager);
            @endphp
            @php
                $status = $detail['status'] ?? 'none';
                $styles = [
                    'none' => ['wrap' => 'bg-white', 'badge' => 'bg-gray-100 text-gray-700', 'icon' => 'text-gray-500'],
                    'in_only' => ['wrap' => 'bg-white', 'badge' => 'bg-yellow-100 text-yellow-800', 'icon' => 'text-yellow-600'],
                    'completed' => ['wrap' => 'bg-green-50', 'badge' => 'bg-green-100 text-green-800', 'icon' => 'text-green-600'],
                ];
                $st = $styles[$status] ?? $styles['none'];
            @endphp
            <div class="overflow-hidden shadow rounded-lg border {{ $completed ? 'border-green-200' : 'border-gray-200' }} {{ $st['wrap'] }} card-hover">
                <div class="p-5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-calendar-check {{ $st['icon'] }} text-2xl"></i>
                            <div>
                                <div class="text-sm font-medium text-gray-700">Absensi Hari Ini</div>
                                @if(!$isManager)
                                    <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded-full text-xs font-medium {{ $st['badge'] }}">
                                        @if($status==='none') Belum Absen @elseif($status==='in_only') Proses Absensi @else Absensi Completed @endif
                                    </span>
                                @endif
                            </div>
                        </div>
                        @if($isManager)
                            <div class="text-right">
                                <div class="text-2xl font-semibold text-gray-900">{{ $stats['today_attendance_percent'] ?? 0 }}%</div>
                                <div class="text-xs text-gray-500">{{ $stats['today_attendance'] ?? 0 }} / {{ $stats['today_total_users'] ?? 0 }} pengguna</div>
                        </div>
                        @endif
                    </div>
                    @if(!$isManager)
                    @php
                        $inVal = $detail['check_in'] ?? '--:--:--';
                        $outVal = $detail['check_out'] ?? '--:--:--';
                        $step1Done = $status!=='none';
                        $step2Done = $status==='completed';
                    @endphp
                    <div class="mt-2 att-stepper">
                        <div class="att-step {{ $step1Done ? 'done' : '' }}">
                            <div class="dot"></div>
                            <div class="label">Masuk</div>
                            <div class="time">{{ $inVal }}</div>
                        </div>
                        @if($user->user_type !== 'student')
                        <div class="att-connector {{ $step2Done ? 'done' : ($step1Done ? 'half' : '') }}"></div>
                        <div class="att-step {{ $step2Done ? 'done' : '' }}">
                            <div class="dot"></div>
                            <div class="label">Keluar</div>
                            <div class="time">{{ $outVal }}</div>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Kartu "Kelas Saya" dihapus --}}

            @if(isset($stats['my_students']))
            <div class="bg-white overflow-hidden shadow rounded-lg card-hover">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-user-graduate text-teal-600 text-2xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Siswa Saya</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $stats['my_students'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if(isset($stats['pending_leaves']))
            <div class="overflow-hidden shadow rounded-lg border border-gray-200 bg-white card-hover">
                <div class="p-5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-clock text-yellow-600 text-2xl"></i>
                            <div class="text-sm font-medium text-gray-700">Izin Pending</div>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-semibold text-gray-900">{{ $stats['pending_leaves_percent'] ?? 0 }}%</div>
                            <div class="text-xs text-gray-500">{{ $stats['pending_leaves'] ?? 0 }} / {{ $stats['pending_leaves_total'] ?? 0 }} permohonan</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if(isset($stats['approved_leaves']))
            <div class="overflow-hidden shadow rounded-lg border border-gray-200 bg-white card-hover">
                <div class="p-5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                            <div class="text-sm font-medium text-gray-700">Izin Disetujui</div>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-semibold text-gray-900">{{ $stats['approved_leaves'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- KPI & Usage (Admin/Headmaster) -->
        @if(isset($metrics) && ($user->hasRole('admin') || $user->hasRole('headmaster') || $user->hasRole('bk') || $user->hasRole('kesiswaan')))
        <div class="bg-white overflow-hidden shadow rounded-lg mb-8 card-hover">
            <div class="px-4 py-5 sm:p-6">
                <div class="mb-4">
                    <div class="mb-2">
                        <h3 class="text-lg leading-6 font-semibold text-gray-900">Ringkasan Performa</h3>
                        <p class="text-xs text-gray-500">Periode: {{ $startDate ?? '-' }} s/d {{ $endDate ?? '-' }}</p>
                    </div>
                    <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-2 flex-wrap">
                        <input type="date" name="start" value="{{ $startDate ?? '' }}" class="border rounded px-2 py-1 text-sm" />
                        <input type="date" name="end" value="{{ $endDate ?? '' }}" class="border rounded px-2 py-1 text-sm" />
                        <select name="role" class="border rounded px-2 py-1 text-sm">
                            <option value="all" {{ ($metrics['role_filter'] ?? 'all') === 'all' ? 'selected' : '' }}>Semua</option>
                            <option value="employee" {{ ($metrics['role_filter'] ?? 'all') === 'employee' ? 'selected' : '' }}>Pegawai</option>
                            <option value="student" {{ ($metrics['role_filter'] ?? 'all') === 'student' ? 'selected' : '' }}>Siswa</option>
                        </select>
                        <button type="submit" class="px-3 py-1 bg-blue-600 text-white rounded text-sm">Terapkan</button>
                        <a href="{{ route('dashboard', ['start' => now()->timezone('Asia/Jakarta')->format('Y-m-d'), 'end' => now()->timezone('Asia/Jakarta')->format('Y-m-d')]) }}" class="px-2 py-1 border rounded text-xs hover:bg-gray-50">Hari Ini</a>
                        <a href="{{ route('dashboard', ['start' => now()->timezone('Asia/Jakarta')->subDays(6)->format('Y-m-d'), 'end' => now()->timezone('Asia/Jakarta')->format('Y-m-d')]) }}" class="px-2 py-1 border rounded text-xs hover:bg-gray-50">7 Hari</a>
                        <a href="{{ route('dashboard', ['start' => now()->timezone('Asia/Jakarta')->startOfMonth()->format('Y-m-d'), 'end' => now()->timezone('Asia/Jakarta')->endOfDay()->format('Y-m-d')]) }}" class="px-2 py-1 border rounded text-xs hover:bg-gray-50">Bulan Ini</a>
                        
                    </form>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    @php
                        $good = $metrics['thresholds']['good'] ?? 90;
                        $warn = $metrics['thresholds']['warn'] ?? 75;
                        $colorFor = function($p) use ($good, $warn) {
                            if ($p >= $good) return 'bg-green-500';
                            if ($p >= $warn) return 'bg-yellow-500';
                            return 'bg-red-500';
                        };
                    @endphp
            <div class="p-4 rounded border solution_card">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-600">Penggunaan Absensi</div>
                            <i class="fas fa-user-check text-blue-600"></i>
                        </div>
                        <div class="mt-1 text-2xl font-semibold">{{ $metrics['usage']['percentage'] ?? 0 }}%</div>
                        <div id="gauge-usage" class="mt-2" style="height:120px;"></div>
                        <div class="mt-1 text-xs text-gray-500">{{ $metrics['usage']['active_users'] ?? 0 }} / {{ $metrics['usage']['total_users'] ?? 0 }} user aktif</div>
                        <div class="mt-2 grid grid-cols-2 gap-2 text-xs">
                            <div class="p-2 bg-gray-50 rounded">
                                <div class="text-gray-500">Pegawai</div>
                                <div class="font-medium">{{ $metrics['usage']['breakdown']['employee']['percentage'] ?? 0 }}%</div>
                            </div>
                            <div class="p-2 bg-gray-50 rounded">
                                <div class="text-gray-500">Siswa</div>
                                <div class="font-medium">{{ $metrics['usage']['breakdown']['student']['percentage'] ?? 0 }}%</div>
                            </div>
                        </div>
                    </div>
            <div class="p-4 rounded border solution_card">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-600">KPI Absensi</div>
                            <i class="fas fa-gauge-high text-emerald-600"></i>
                        </div>
                        <div class="mt-1 text-2xl font-semibold">{{ $metrics['kpi']['score'] ?? 0 }}</div>
                        <div id="gauge-kpi" class="mt-2" style="height:120px;"></div>
                        <div class="mt-1 text-xs text-gray-500">Ontime {{ $metrics['kpi']['ontime_rate'] ?? 0 }}% • Coverage {{ $metrics['kpi']['coverage_rate'] ?? 0 }}% • Data {{ $metrics['kpi']['completeness_rate'] ?? 0 }}% • Checkout {{ $metrics['kpi']['checkout_consistency'] ?? 0 }}%</div>
                    </div>
            <div class="p-4 rounded border solution_card">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-600">Kelengkapan Data Pegawai</div>
                            <i class="fas fa-id-card text-indigo-600"></i>
                        </div>
                        <div class="mt-1 text-2xl font-semibold">{{ $metrics['completeness']['employees']['percentage'] ?? 0 }}%</div>
                        <div id="gauge-emp" class="mt-2" style="height:120px;"></div>
                        <div class="mt-1 text-xs text-gray-500">{{ $metrics['completeness']['employees']['complete'] ?? 0 }} / {{ $metrics['completeness']['employees']['total'] ?? 0 }} lengkap</div>
                    </div>
            <div class="p-4 rounded border solution_card">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-600">Kelengkapan Data Siswa</div>
                            <i class="fas fa-user-graduate text-rose-600"></i>
                        </div>
                        <div class="mt-1 text-2xl font-semibold">{{ $metrics['completeness']['students']['percentage'] ?? 0 }}%</div>
                        <div id="gauge-stu" class="mt-2" style="height:120px;"></div>
                        <div class="mt-1 text-xs text-gray-500">{{ $metrics['completeness']['students']['complete'] ?? 0 }} / {{ $metrics['completeness']['students']['total'] ?? 0 }} lengkap</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-6">
                    <!-- Leak Table Card -->
                    <div class="lg:col-span-1 rounded border bg-white">
                        <div class="px-4 py-3 border-b flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="font-medium">Masuk tanpa Keluar (Leak)</span>
                                <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-700">Rate {{ $metrics['leak']['rate'] ?? 0 }}%</span>
                            </div>
                            <a href="{{ route('dashboard.export', ['type' => 'leak', 'start' => $startDate, 'end' => $endDate]) }}" class="text-xs px-2 py-1 rounded bg-purple-600 text-white">Export</a>
                        </div>
                        <div class="overflow-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-medium text-gray-700">Nama</th>
                                        <th class="px-4 py-2 text-left font-medium text-gray-700">Tanggal</th>
                                        <th class="px-4 py-2 text-left font-medium text-gray-700">Check In</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                            @forelse(($metrics['leak']['samples'] ?? []) as $item)
                                        <tr>
                                            <td class="px-4 py-2">{{ $item->user->name }}</td>
                                            <td class="px-4 py-2">{{ (string)$item->date }}</td>
                                            <td class="px-4 py-2">{{ optional($item->check_in)->format('H:i') }}</td>
                                        </tr>
                            @empty
                                        <tr>
                                            <td class="px-4 py-6 text-center text-gray-500" colspan="3">Tidak ada data</td>
                                        </tr>
                            @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Incomplete Profiles Table Card -->
                    <div class="lg:col-span-1 rounded border bg-white">
                        <div class="px-4 py-3 border-b flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="font-medium">Profil Tidak Lengkap</span>
                                <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-700">Top {{ ($metrics['incomplete_profiles'] ?? collect())->count() }}</span>
                            </div>
                            <a href="{{ route('dashboard.export', ['type' => 'incomplete', 'start' => $startDate, 'end' => $endDate]) }}" class="text-xs px-2 py-1 rounded bg-yellow-600 text-white">Export</a>
                        </div>
                        <div class="overflow-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-medium text-gray-700">Nama</th>
                                        <th class="px-4 py-2 text-left font-medium text-gray-700">Tipe</th>
                                        <th class="px-4 py-2 text-left font-medium text-gray-700">Field Kosong</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                            @forelse(($metrics['incomplete_profiles'] ?? []) as $u)
                                        <tr>
                                            <td class="px-4 py-2">{{ $u->name }}</td>
                                            <td class="px-4 py-2 text-gray-600">{{ $u->user_type === 'employee' ? 'Pegawai' : 'Siswa' }}</td>
                                            <td class="px-4 py-2 text-gray-600">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">{{ $u->missing_count ?? 0 }}</span>
                                            </td>
                                        </tr>
                            @empty
                                        <tr>
                                            <td class="px-4 py-6 text-center text-gray-500" colspan="2">Semua profil sudah lengkap</td>
                                        </tr>
                            @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Non Users Table Card -->
                    <div class="lg:col-span-1 rounded border bg-white">
                        <div class="px-4 py-3 border-b flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="font-medium">Tidak Pernah Menggunakan (Periode)</span>
                                <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-700">Top {{ ($metrics['non_users'] ?? collect())->count() }}</span>
                            </div>
                            <a href="{{ route('dashboard.export', ['type' => 'non_users', 'start' => $startDate, 'end' => $endDate]) }}" class="text-xs px-2 py-1 rounded bg-green-600 text-white">Export</a>
                        </div>
                        <div class="overflow-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-medium text-gray-700">Nama</th>
                                        <th class="px-4 py-2 text-left font-medium text-gray-700">Tipe</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                            @forelse(($metrics['non_users'] ?? []) as $u)
                                        <tr>
                                            <td class="px-4 py-2">{{ $u->name }}</td>
                                            <td class="px-4 py-2 text-gray-600">{{ $u->user_type === 'employee' ? 'Pegawai' : 'Siswa' }}</td>
                                        </tr>
                            @empty
                                        <tr>
                                            <td class="px-4 py-6 text-center text-gray-500" colspan="2">Semua user aktif menggunakan</td>
                                        </tr>
                            @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Charts after Ringkasan Performa (role-based) -->
        

        

        <!-- Quick Actions -->
        <div class="mt-8">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Aksi Cepat</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 quick-actions">
                        @if($user->hasRole('admin'))
                            <!-- Admin Menu -->
                            <a href="{{ route('users.index') }}" class="flex flex-col items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                                <i class="fas fa-users text-blue-600 text-2xl mb-2"></i>
                                <span class="text-sm font-medium text-blue-900">Manajemen User</span>
                            </a>
                            <a href="{{ route('users.import') }}" class="flex flex-col items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                                <i class="fas fa-upload text-green-600 text-2xl mb-2"></i>
                                <span class="text-sm font-medium text-green-900">Import Data</span>
                            </a>
                            <a href="{{ route('settings.attendance') }}" class="flex flex-col items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                                <i class="fas fa-cog text-purple-600 text-2xl mb-2"></i>
                                <span class="text-sm font-medium text-purple-900">Pengaturan</span>
                            </a>
                            <a href="{{ route('attendance.reports') }}" class="flex flex-col items-center p-4 bg-orange-50 qa-orange rounded-lg hover:bg-orange-100 transition-colors">
                                <i class="fas fa-chart-bar text-orange-600 text-2xl mb-2"></i>
                                <span class="text-sm font-medium text-orange-900">Laporan</span>
                            </a>
                            <a href="{{ route('leave-requests.index') }}" class="flex flex-col items-center p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition-colors">
                                <i class="fas fa-calendar-times text-yellow-600 text-2xl mb-2"></i>
                                <span class="text-sm font-medium text-yellow-900">Manajemen Izin</span>
                            </a>
                        @elseif($user->hasRole('headmaster'))
                            <!-- Headmaster Menu -->
                            <a href="{{ route('attendance.reports') }}" class="flex flex-col items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                                <i class="fas fa-chart-bar text-blue-600 text-2xl mb-2"></i>
                                <span class="text-sm font-medium text-blue-900">Laporan Absensi</span>
                            </a>
                            <a href="{{ route('leave-requests.index') }}" class="flex flex-col items-center p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition-colors">
                                <i class="fas fa-calendar-times text-yellow-600 text-2xl mb-2"></i>
                                <span class="text-sm font-medium text-yellow-900">Persetujuan Izin</span>
                            </a>
                            <a href="{{ route('attendance.index') }}" class="flex flex-col items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                                <i class="fas fa-calendar-check text-green-600 text-2xl mb-2"></i>
                                <span class="text-sm font-medium text-green-900">Status Absensi</span>
                            </a>
                            <a href="{{ route('attendance.export') }}" class="flex flex-col items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                                <i class="fas fa-download text-purple-600 text-2xl mb-2"></i>
                                <span class="text-sm font-medium text-purple-900">Export Laporan</span>
                            </a>
                        @elseif($user->hasRole(['teacher', 'tu', 'bk', 'kesiswaan', 'admin']))
                            <!-- Employee Menu -->
                            <a href="{{ route('attendance.check-in') }}" class="flex flex-col items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                                <i class="fas fa-sign-in-alt text-blue-600 text-2xl mb-2"></i>
                                <span class="text-sm font-medium text-blue-900">Absensi Masuk</span>
                            </a>
                            <a href="{{ route('attendance.check-out') }}" class="flex flex-col items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                                <i class="fas fa-sign-out-alt text-green-600 text-2xl mb-2"></i>
                                <span class="text-sm font-medium text-green-900">Absensi Keluar</span>
                            </a>
                            @if($user->hasRole(['teacher','admin']))
                                <a href="{{ route('attendance.student-scan') }}" class="flex flex-col items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                                    <i class="fas fa-qrcode text-purple-600 text-2xl mb-2"></i>
                                    <span class="text-sm font-medium text-purple-900">Scan Siswa</span>
                                </a>
                            @endif
                            <a href="{{ route('attendance.reports') }}" class="flex flex-col items-center p-4 bg-orange-50 qa-orange rounded-lg hover:bg-orange-100 transition-colors">
                                <i class="fas fa-chart-bar text-orange-600 text-2xl mb-2"></i>
                                <span class="text-sm font-medium text-orange-900">Laporan</span>
                            </a>
                            <a href="{{ route('leave-requests.index') }}" class="flex flex-col items-center p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition-colors">
                                <i class="fas fa-calendar-times text-yellow-600 text-2xl mb-2"></i>
                                <span class="text-sm font-medium text-yellow-900">Manajemen Izin</span>
                            </a>
                        @elseif($user->hasRole('student'))
                            <!-- Student Menu -->
                            <a href="{{ route('attendance.reports') }}" class="flex flex-col items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                                <i class="fas fa-chart-bar text-green-600 text-2xl mb-2"></i>
                                <span class="text-sm font-medium text-green-900">Riwayat Absensi</span>
                            </a>
                            <a href="{{ route('leave-requests.index') }}" class="flex flex-col items-center p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition-colors">
                                <i class="fas fa-calendar-times text-yellow-600 text-2xl mb-2"></i>
                                <span class="text-sm font-medium text-yellow-900">Izin Saya</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
@endsection

@push('scripts')
<style>
/* Welcome image: REMOVED - using custom banner image instead */

/* Custom banner image: full height background */
.banner-custom-image{
    position:absolute; right:0; top:0; height:100%; width:50%; border-radius:0 12px 12px 0;
    opacity:1; 
    transform: translateY(0) scale(1);
    z-index: 0; pointer-events: none;
    background-size: cover; background-position: center; background-repeat: no-repeat;
}
.banner-custom-image.loaded{ opacity:1; transform: translateY(0) scale(1); }
@media (min-width:768px){ .banner-custom-image{ width:45%; } }
@media (min-width:1024px){ .banner-custom-image{ width:40%; } }

.welcome-hero-wrap{ position: relative; z-index: 1; }
/* Mobile adjustments: keep text readable */
@media (max-width: 639px){
  .welcome-hero-wrap{ padding-right: 140px; }
  .welcome-hero-image{ height:120px; right:8px; top:-6px; }
}

/* Mini stepper for Absensi Hari Ini */
.att-stepper{ display:flex; align-items:center; gap:10px; }
.att-step{ display:flex; align-items:center; gap:6px; }
.att-step .dot{ width:10px; height:10px; border-radius:9999px; background:#d1d5db; }
.att-step.done .dot{ background:#10b981; }
.att-step .label{ font-size:.75rem; color:#6b7280; }
.att-step .time{ font-size:.75rem; color:#111827; font-weight:600; }
.att-connector{ flex:1; height:2px; background:#e5e7eb; }
.att-connector.half{ background:linear-gradient(90deg,#10b981 0%,#e5e7eb 50%); }
.att-connector.done{ background:#10b981; }

/* Ensure quick action ‘Laporan’ tile not plain white in all themes */
.quick-actions .qa-orange{ background-color: #fff7ed; }
.quick-actions .qa-orange:hover{ background-color:#ffedd5; }
</style>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    const gauges = {
        usage: {{ $metrics['usage']['percentage'] ?? 0 }},
        kpi: {{ $metrics['kpi']['score'] ?? 0 }},
        emp: {{ $metrics['completeness']['employees']['percentage'] ?? 0 }},
        stu: {{ $metrics['completeness']['students']['percentage'] ?? 0 }}
    };
    const thresholds = {
        good: {{ $metrics['thresholds']['good'] ?? 90 }},
        warn: {{ $metrics['thresholds']['warn'] ?? 75 }}
    };
    function pickColor(value){
        if (value >= thresholds.good) return '#16a34a'; // green
        if (value >= thresholds.warn) return '#f59e0b'; // amber
        return '#ef4444'; // red
    }
    function renderGauge(el, value, color){
        const options = {
            chart: { height: 120, type: 'radialBar', sparkline: { enabled: true } },
            series: [Number(value || 0)],
            colors: [color || pickColor(Number(value || 0))],
            plotOptions: {
                radialBar: {
                    hollow: { size: '60%' },
                    dataLabels: {
                        name: { show: false },
                        value: { formatter: (v)=>`${Math.round(v)}%`, fontSize: '16px' }
                    }
                }
            }
        };
        const target = document.querySelector(el);
        if (target) new ApexCharts(target, options).render();
    }
    renderGauge('#gauge-usage', gauges.usage);
    renderGauge('#gauge-kpi', gauges.kpi);
    renderGauge('#gauge-emp', gauges.emp);
    renderGauge('#gauge-stu', gauges.stu);
    // add loaded class to custom banner image
    window.requestAnimationFrame(()=>{
        const bannerImg=document.querySelector('.banner-custom-image');
        console.log('Banner image element:', bannerImg);
        if(bannerImg){
            console.log('Adding loaded class to banner image');
            bannerImg.classList.add('loaded');
        } else {
            console.log('Banner image element not found!');
        }
    });
</script>
@endpush
