@extends('layouts.app')

@section('title', 'Dashboard - Presensia')

@push('styles')
<style>
    /* Mobile responsive grid */
    @media (max-width: 768px) {
        .mobile\:grid-cols-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    
    /* Simple tooltip styles */
    .group:hover .opacity-0 {
        opacity: 1;
    }
</style>
@endpush

@section('content')
        
        <!-- Welcome Section -->
        <div class="bg-white overflow-hidden shadow rounded-lg mb-6 relative">
            @if($school->tenantSettings && $school->tenantSettings->banner_image)
                @php
                    $bannerScale = $school->tenantSettings->banner_scale ?? 100;
                @endphp
                <div class="banner-custom-image" style="background-image: url('{{ asset('storage/' . $school->tenantSettings->banner_image) }}'); background-size: {{ $bannerScale }}%; background-position: top; background-repeat: no-repeat;"></div>
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
                <div class="absolute inset-0 school-photo-bg" 
                     style="background-image: url('{{ asset('storage/' . $school->tenantSettings->school_photo) }}'); 
                            background-size: auto {{ $scale }}%;
                            background-position: {{ $backgroundPosition }};
                            background-repeat: no-repeat;
                            opacity: {{ $opacity / 100 }};"></div>
            @endif
            <div class="px-4 py-5 sm:p-6 welcome-hero-wrap relative z-10">
                <div class="flex items-start">
                    @if($school->logo)
                        <img src="{{ asset('storage/' . $school->logo) }}" alt="{{ $school->name }}" class="h-20 w-auto mr-4 flex-shrink-0">
                    @endif
                    <div class="flex-1 min-w-0">
                        <h1 class="text-2xl font-bold text-gray-900 mb-2">Dashboard</h1>
                        @if($school->tenantSettings && $school->tenantSettings->banner_text)
                            <p class="text-gray-600 leading-relaxed">{{ $school->tenantSettings->banner_text }}</p>
                        @else
                            <p class="text-gray-600 leading-relaxed">Selamat datang di sistem manajemen absensi sekolah, Presensia!</p>
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
        <div class="flex flex-col gap-4 mb-8 md:grid md:grid-cols-2 lg:grid-cols-4 desktop-stats-grid">
            @if(isset($stats['total_employees']))
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm flex items-center justify-between card-hover">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-blue-50 rounded-2xl">
                        <i data-lucide="users" class="h-6 w-6 text-blue-600"></i>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Pegawai</div>
                        <div class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total_employees'] }}</div>
                    </div>
                </div>
            </div>
            @endif

            @if(isset($stats['total_students']))
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm flex items-center justify-between card-hover">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-green-50 rounded-2xl">
                        <i data-lucide="graduation-cap" class="h-6 w-6 text-green-600"></i>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Siswa</div>
                        <div class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total_students'] }}</div>
                    </div>
                </div>
            </div>
            @endif

            @if(isset($stats['today_attendance']))
            @php
                $isManager = $user->hasRole('admin') || $user->hasRole('headmaster');
                $detail = $stats['today_attendance_detail'] ?? null;
                $completed = ($detail && ($detail['status'] ?? '') === 'completed' && !$isManager);
            @endphp
            @php
                $status = $detail['status'] ?? 'none';
                $styles = [
                    'none' => ['wrap' => 'bg-white', 'badge' => 'bg-gray-100 text-gray-700', 'icon' => 'text-gray-500', 'bgIcon' => 'bg-gray-50'],
                    'in_only' => ['wrap' => 'bg-white', 'badge' => 'bg-yellow-100 text-yellow-800', 'icon' => 'text-yellow-600', 'bgIcon' => 'bg-yellow-50'],
                    'completed' => ['wrap' => 'bg-green-50', 'badge' => 'bg-green-100 text-green-800', 'icon' => 'text-green-600', 'bgIcon' => 'bg-green-100'],
                ];
                $st = $styles[$status] ?? $styles['none'];
            @endphp
            <div class="rounded-2xl border {{ $completed ? 'border-green-200' : 'border-gray-100' }} {{ $st['wrap'] }} p-5 shadow-sm card-hover">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="p-3 {{ $st['bgIcon'] }} rounded-2xl">
                            <i data-lucide="calendar-check" class="h-6 w-6 {{ $st['icon'] }}"></i>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Absensi Hari Ini</div>
                            @if(!$isManager)
                                <span class="inline-flex items-center mt-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold {{ $st['badge'] }}">
                                    @if($status==='none') Belum Absen @elseif($status==='in_only') Proses Absensi @else Absensi Completed @endif
                                </span>
                            @endif
                        </div>
                    </div>
                    @if($isManager)
                        <div class="text-right">
                            <div class="text-2xl font-bold text-gray-900">{{ $stats['today_attendance_percent'] ?? 0 }}%</div>
                            <div class="text-[10px] font-medium text-gray-400 mt-1">{{ $stats['today_attendance'] ?? 0 }} / {{ $stats['today_total_users'] ?? 0 }}</div>
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
                <div class="mt-4 att-stepper bg-gray-50/50 p-3 rounded-xl border border-gray-100">
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
            @endif

            @if(isset($stats['my_students']))
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm flex items-center justify-between card-hover">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-teal-50 rounded-2xl">
                        <i data-lucide="graduation-cap" class="h-6 w-6 text-teal-600"></i>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Siswa Saya</div>
                        <div class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['my_students'] }}</div>
                    </div>
                </div>
            </div>
            @endif

            @if(isset($stats['pending_leaves']))
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm flex items-center justify-between card-hover">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-yellow-50 rounded-2xl">
                        <i data-lucide="clock" class="h-6 w-6 text-yellow-600"></i>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Izin Pending</div>
                        <div class="text-[10px] font-medium text-gray-400 mt-1">{{ $stats['pending_leaves'] ?? 0 }} / {{ $stats['pending_leaves_total'] ?? 0 }} permohonan</div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-gray-900">{{ $stats['pending_leaves_percent'] ?? 0 }}%</div>
                </div>
            </div>
            @endif

            @if(isset($stats['approved_leaves']))
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm flex items-center justify-between card-hover">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-green-50 rounded-2xl">
                        <i data-lucide="check-circle" class="h-6 w-6 text-green-600"></i>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Izin Disetujui</div>
                        <div class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['approved_leaves'] }}</div>
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
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Ringkasan Performa</h3>
                            <p class="text-xs text-gray-400 mt-0.5">Periode: {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d/m/Y') : '-' }} s/d {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d/m/Y') : '-' }}</p>
                        </div>
                        
                        <!-- Trigger Bottom Sheet -->
                        <div onclick="openDateBottomSheet()" class="flex items-center gap-2 px-3 py-2 bg-gray-50 border border-gray-100 rounded-xl cursor-pointer hover:bg-gray-100 transition-colors">
                            <i data-lucide="calendar" class="text-gray-500 h-4 w-4"></i>
                            <span class="text-xs font-semibold text-gray-600">
                                Filter Tanggal
                            </span>
                        </div>
                    </div>

                    <!-- Backdrop -->
                    <div id="date-sheet-backdrop" onclick="closeDateBottomSheet()" class="fixed inset-0 bg-black bg-opacity-40 z-50 hidden transition-opacity duration-300 opacity-0"></div>

                    <!-- Bottom Sheet -->
                    <div id="date-sheet" class="fixed bottom-0 left-0 right-0 bg-white rounded-t-3xl z-50 transform translate-y-full transition-transform duration-300 shadow-2xl safe-bottom max-h-[85vh] overflow-y-auto">
                        <!-- Drag Handle -->
                        <div class="flex justify-center py-3" onclick="closeDateBottomSheet()">
                            <div class="w-12 h-1 bg-gray-200 rounded-full"></div>
                        </div>
                        
                        <div class="px-6 pb-8">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-bold text-gray-900">Pilih Rentang Tanggal</h3>
                                <button type="button" onclick="closeDateBottomSheet()" class="p-1 text-gray-400 hover:text-gray-600">
                                    <i data-lucide="x" class="h-5 w-5"></i>
                                </button>
                            </div>
                            
                            <form method="GET" action="{{ route('dashboard') }}" id="bottom-sheet-date-form">
                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Tanggal Mulai</label>
                                        <input type="date" name="start" value="{{ $startDate ?? '' }}" class="w-full border border-gray-100 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Tanggal Selesai</label>
                                        <input type="date" name="end" value="{{ $endDate ?? '' }}" class="w-full border border-gray-100 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                                    </div>
                                </div>
                                
                                @if(isset($metrics['role_filter']))
                                <div class="mb-4">
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Filter Peran</label>
                                    <select name="role" class="w-full border border-gray-100 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="all" {{ ($metrics['role_filter'] ?? 'all') === 'all' ? 'selected' : '' }}>Semua</option>
                                        <option value="employee" {{ ($metrics['role_filter'] ?? 'all') === 'employee' ? 'selected' : '' }}>Pegawai</option>
                                        <option value="student" {{ ($metrics['role_filter'] ?? 'all') === 'student' ? 'selected' : '' }}>Siswa</option>
                                    </select>
                                </div>
                                @endif
                                
                                <!-- Presets -->
                                <div class="flex gap-2 mb-6 overflow-x-auto pb-2">
                                    <a href="{{ route('dashboard', ['start' => now()->timezone('Asia/Jakarta')->format('Y-m-d'), 'end' => now()->timezone('Asia/Jakarta')->format('Y-m-d')]) }}" class="px-3 py-1.5 bg-gray-50 hover:bg-gray-100 text-gray-600 rounded-lg text-xs font-semibold whitespace-nowrap border border-gray-100 transition-colors">Hari Ini</a>
                                    <a href="{{ route('dashboard', ['start' => now()->timezone('Asia/Jakarta')->subDays(6)->format('Y-m-d'), 'end' => now()->timezone('Asia/Jakarta')->format('Y-m-d')]) }}" class="px-3 py-1.5 bg-gray-50 hover:bg-gray-100 text-gray-600 rounded-lg text-xs font-semibold whitespace-nowrap border border-gray-100 transition-colors">7 Hari Terakhir</a>
                                    <a href="{{ route('dashboard', ['start' => now()->timezone('Asia/Jakarta')->startOfMonth()->format('Y-m-d'), 'end' => now()->timezone('Asia/Jakarta')->format('Y-m-d')]) }}" class="px-3 py-1.5 bg-gray-50 hover:bg-gray-100 text-gray-600 rounded-lg text-xs font-semibold whitespace-nowrap border border-gray-100 transition-colors">Bulan Ini</a>
                                </div>
                                
                                <div class="flex gap-3">
                                    <button type="button" onclick="closeDateBottomSheet()" class="flex-1 py-3 border border-gray-200 text-gray-600 rounded-xl text-sm font-semibold hover:bg-gray-50 transition-colors">Batal</button>
                                    <button type="submit" class="flex-1 py-3 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition-colors shadow-lg shadow-blue-500/20">Terapkan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    @php
                        $good = $metrics['thresholds']['good'] ?? 90;
                        $warn = $metrics['thresholds']['warn'] ?? 75;
                        $colorFor = function($p) use ($good, $warn) {
                            if ($p >= $good) return 'bg-green-500';
                            if ($p >= $warn) return 'bg-yellow-500';
                            return 'bg-red-500';
                        };
                        $formatPercent = function($val) {
                            $num = (float) ($val ?? 0);
                            return (floor($num) == $num ? number_format($num, 0) : number_format($num, 1)) . '%';
                        };
                        $statusColorText = function($p) {
                            $val = (float) $p;
                            if ($val >= 80) return 'text-emerald-600';
                            if ($val >= 50) return 'text-amber-600';
                            return 'text-rose-600';
                        };
                    @endphp
                    
                    <!-- Penggunaan Absensi Card -->
                    <div class="p-5 bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <div class="text-sm font-semibold text-gray-500">Penggunaan Absensi</div>
                                <i data-lucide="user-check" class="text-blue-600 h-5 w-5"></i>
                            </div>
                            <div class="mt-2 text-2xl font-bold {{ $statusColorText($metrics['usage']['percentage'] ?? 0) }}">{{ $formatPercent($metrics['usage']['percentage'] ?? 0) }}</div>
                            <div id="gauge-usage" class="mt-4" style="height:160px;"></div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-50 text-center">
                            <div class="text-xs text-gray-400 font-medium mb-3">{{ $metrics['usage']['active_users'] ?? 0 }} / {{ $metrics['usage']['total_users'] ?? 0 }} user aktif</div>
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div class="p-2 bg-gray-50 rounded-xl">
                                    <div class="text-gray-400 font-medium mb-0.5">Pegawai</div>
                                    <div class="font-bold text-gray-800">{{ $metrics['usage']['breakdown']['employee']['percentage'] ?? 0 }}%</div>
                                </div>
                                <div class="p-2 bg-gray-50 rounded-xl">
                                    <div class="text-gray-400 font-medium mb-0.5">Siswa</div>
                                    <div class="font-bold text-gray-800">{{ $metrics['usage']['breakdown']['student']['percentage'] ?? 0 }}%</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KPI Absensi Card -->
                    <div class="p-5 bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <div class="text-sm font-semibold text-gray-500 relative group cursor-help">
                                    KPI Absensi
                                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-4 py-3 bg-gray-900 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50 w-80 text-center shadow-lg">
                                        <div class="text-xs text-gray-300">
                                            🟢 Baik (≥80%) • 🟡 Sedang (60-79%) • 🔴 Perlu Perhatian (<60%)
                                        </div>
                                    </div>
                                </div>
                                <i data-lucide="gauge" class="text-emerald-600 h-5 w-5"></i>
                            </div>
                            <div class="mt-2 text-2xl font-bold {{ $statusColorText($metrics['kpi']['score'] ?? 0) }}">{{ $formatPercent($metrics['kpi']['score'] ?? 0) }}</div>
                            <div id="gauge-kpi" class="mt-4" style="height:160px;"></div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-50">
                            <!-- Mini Horizontal Progress Bars for Sub-metrics -->
                            <div class="space-y-3">
                                <div>
                                    <div class="flex justify-between text-[11px] mb-1 font-semibold">
                                        <span class="text-gray-500">Tepat Waktu</span>
                                        <span class="{{ ($metrics['kpi']['ontime_rate'] ?? 0) >= 80 ? 'text-green-600' : (($metrics['kpi']['ontime_rate'] ?? 0) >= 60 ? 'text-yellow-600' : 'text-red-600') }}">{{ $metrics['kpi']['ontime_rate'] ?? 0 }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                                        <div class="h-1.5 rounded-full {{ ($metrics['kpi']['ontime_rate'] ?? 0) >= 80 ? 'bg-green-500' : (($metrics['kpi']['ontime_rate'] ?? 0) >= 60 ? 'bg-yellow-500' : 'bg-red-500') }}" style="width: {{ $metrics['kpi']['ontime_rate'] ?? 0 }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-[11px] mb-1 font-semibold">
                                        <span class="text-gray-500">Keaktifan User</span>
                                        <span class="{{ ($metrics['kpi']['coverage_rate'] ?? 0) >= 80 ? 'text-green-600' : (($metrics['kpi']['coverage_rate'] ?? 0) >= 60 ? 'text-yellow-600' : 'text-red-600') }}">{{ $metrics['kpi']['coverage_rate'] ?? 0 }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                                        <div class="h-1.5 rounded-full {{ ($metrics['kpi']['coverage_rate'] ?? 0) >= 80 ? 'bg-green-500' : (($metrics['kpi']['coverage_rate'] ?? 0) >= 60 ? 'bg-yellow-500' : 'bg-red-500') }}" style="width: {{ $metrics['kpi']['coverage_rate'] ?? 0 }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-[11px] mb-1 font-semibold">
                                        <span class="text-gray-500">Kelengkapan Data</span>
                                        <span class="{{ ($metrics['kpi']['completeness_rate'] ?? 0) >= 80 ? 'text-green-600' : (($metrics['kpi']['completeness_rate'] ?? 0) >= 60 ? 'text-yellow-600' : 'text-red-600') }}">{{ $metrics['kpi']['completeness_rate'] ?? 0 }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                                        <div class="h-1.5 rounded-full {{ ($metrics['kpi']['completeness_rate'] ?? 0) >= 80 ? 'bg-green-500' : (($metrics['kpi']['completeness_rate'] ?? 0) >= 60 ? 'bg-yellow-500' : 'bg-red-500') }}" style="width: {{ $metrics['kpi']['completeness_rate'] ?? 0 }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-[11px] mb-1 font-semibold">
                                        <span class="text-gray-500">Konsistensi Absensi</span>
                                        <span class="{{ ($metrics['kpi']['checkout_consistency'] ?? 0) >= 80 ? 'text-green-600' : (($metrics['kpi']['checkout_consistency'] ?? 0) >= 60 ? 'text-yellow-600' : 'text-red-600') }}">{{ $metrics['kpi']['checkout_consistency'] ?? 0 }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                                        <div class="h-1.5 rounded-full {{ ($metrics['kpi']['checkout_consistency'] ?? 0) >= 80 ? 'bg-green-500' : (($metrics['kpi']['checkout_consistency'] ?? 0) >= 60 ? 'bg-yellow-500' : 'bg-red-500') }}" style="width: {{ $metrics['kpi']['checkout_consistency'] ?? 0 }}%"></div>
                                    </div>
                                </div>
                            </div>
                            @if(isset($metrics['kpi']['working_days']) && isset($metrics['kpi']['holidays_count']))
                            <div class="mt-4 text-[10px] text-gray-400 font-medium">
                                <i class="fas fa-calendar-alt mr-1"></i>{{ $metrics['kpi']['working_days'] }} hari kerja
                                @if($metrics['kpi']['holidays_count'] > 0)
                                    <span class="text-red-500 ml-2">
                                        <i class="fas fa-calendar-times mr-1"></i>{{ $metrics['kpi']['holidays_count'] }} hari libur
                                    </span>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Kelengkapan Data Pegawai Card -->
                    <div class="p-5 bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <div class="text-sm font-semibold text-gray-500">Kelengkapan Data Pegawai</div>
                                <i data-lucide="contact" class="text-indigo-600 h-5 w-5"></i>
                            </div>
                            <div class="mt-2 text-2xl font-bold {{ $statusColorText($metrics['completeness']['employees']['percentage'] ?? 0) }}">{{ $formatPercent($metrics['completeness']['employees']['percentage'] ?? 0) }}</div>
                            <div id="gauge-emp" class="mt-4" style="height:160px;"></div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-50 text-center">
                            <div class="text-xs text-gray-400 font-medium">{{ $metrics['completeness']['employees']['complete'] ?? 0 }} / {{ $metrics['completeness']['employees']['total'] ?? 0 }} lengkap</div>
                        </div>
                    </div>

                    <!-- Kelengkapan Data Siswa Card -->
                    <div class="p-5 bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <div class="text-sm font-semibold text-gray-500">Kelengkapan Data Siswa</div>
                                <i data-lucide="graduation-cap" class="text-rose-600 h-5 w-5"></i>
                            </div>
                            <div class="mt-2 text-2xl font-bold {{ $statusColorText($metrics['completeness']['students']['percentage'] ?? 0) }}">{{ $formatPercent($metrics['completeness']['students']['percentage'] ?? 0) }}</div>
                            <div id="gauge-stu" class="mt-4" style="height:160px;"></div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-50 text-center">
                            <div class="text-xs text-gray-400 font-medium">{{ $metrics['completeness']['students']['complete'] ?? 0 }} / {{ $metrics['completeness']['students']['total'] ?? 0 }} lengkap</div>
                        </div>
                    </div>
                </div>
                </div>

                <!-- Late Attendance KPI -->
                @if(isset($metrics['late_attendance']))
                <div class="mt-8">
                    <h4 class="text-base font-bold text-gray-900 mb-4">⏰ KPI Absensi Terlambat</h4>
                    <div class="flex flex-col gap-6">
                        <!-- Gauge Chart Card -->
                        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                            <div class="flex items-center justify-between mb-4">
                                <h5 class="text-sm font-bold text-gray-800">Persentase Terlambat</h5>
                                <i data-lucide="clock" class="text-orange-500 h-5 w-5"></i>
                            </div>
                            <div class="flex flex-col items-center justify-center">
                                <div class="text-3xl font-black text-orange-600 mb-1">
                                    {{ $metrics['late_attendance']['late_percentage'] }}%
                                </div>
                                <div class="text-xs text-gray-400 font-medium mb-4">
                                    {{ $metrics['late_attendance']['total_late'] }} dari {{ $metrics['late_attendance']['total_attendance'] }} absensi
                                </div>
                                <div id="gauge-late" style="height: 180px;" class="w-full"></div>
                            </div>
                        </div>

                        <!-- Recent Late Card List -->
                        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                            <div class="flex items-center justify-between mb-4">
                                <h5 class="text-sm font-bold text-gray-800">Terlambat Terbaru</h5>
                                <span class="bg-orange-50 text-orange-700 px-3 py-1 rounded-full text-xs font-bold">
                                    {{ count($metrics['late_attendance']['recent_late']) }} orang
                                </span>
                            </div>
                            <div class="space-y-3 max-h-72 overflow-y-auto pr-1">
                                @forelse($metrics['late_attendance']['recent_late'] as $late)
                                <div class="flex items-center justify-between p-4 bg-gray-50 border border-gray-100 rounded-2xl transition-all">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center">
                                            <i data-lucide="user" class="text-orange-600 h-5 w-5"></i>
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-900 text-sm">{{ $late['user_name'] }}</div>
                                            <div class="text-[10px] text-gray-400 mt-0.5">
                                                @foreach($late['user_roles'] as $role)
                                                    <span class="bg-gray-200/50 text-gray-600 px-2 py-0.5 rounded font-bold mr-1">{{ ucfirst($role) }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xs font-bold text-gray-500">{{ $late['date'] }}</div>
                                        <div class="text-xs font-extrabold text-orange-600 mt-1">{{ $late['check_in'] }}</div>
                                    </div>
                                </div>
                                @empty
                                <div class="text-center py-8 text-gray-400 text-sm">
                                    <i data-lucide="check-circle" class="text-green-500 h-8 w-8 mx-auto mb-2"></i>
                                    <p>Tidak ada absensi terlambat</p>
                                </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Late by User Analysis Card List -->
                        @if(count($metrics['late_attendance']['late_by_user']) > 0)
                        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                            <h5 class="text-sm font-bold text-gray-800 mb-4">📋 Analisis per User</h5>
                            <div class="space-y-3 max-h-96 overflow-y-auto pr-1">
                                @foreach($metrics['late_attendance']['late_by_user'] as $userLate)
                                <div class="flex items-center justify-between p-4 bg-gray-50 border border-gray-100 rounded-2xl">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center">
                                            <i data-lucide="user" class="text-orange-600 h-5 w-5"></i>
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-900 text-sm">{{ $userLate['user']['name'] }}</div>
                                            <div class="text-[10px] text-gray-400 mt-0.5 flex flex-wrap gap-1 items-center">
                                                @foreach($userLate['user']['roles'] as $role)
                                                    <span class="bg-gray-200/50 text-gray-600 px-2 py-0.5 rounded font-bold mr-1">{{ ucfirst($role) }}</span>
                                                @endforeach
                                                <span class="text-gray-400 font-medium">terakhir: {{ $userLate['latest_late'] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="bg-orange-50 text-orange-700 px-3 py-1.5 rounded-full text-xs font-extrabold border border-orange-100">
                                            {{ $userLate['late_count'] }}x
                                        </span>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-8">
                    <!-- Leak Table Card -->
                    <div class="rounded-2xl border border-gray-100 bg-white shadow-sm overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
                            <div class="flex flex-col">
                                <span class="font-bold text-gray-800 text-sm">Masuk tanpa Keluar (Leak)</span>
                                <span class="text-[10px] text-gray-400 font-semibold mt-0.5">Rate: {{ $metrics['leak']['rate'] ?? 0 }}%</span>
                            </div>
                            <a href="{{ route('dashboard.export', ['type' => 'leak', 'start' => $startDate, 'end' => $endDate]) }}" class="flex items-center gap-1 px-3 py-1.5 bg-purple-50 hover:bg-purple-100 text-purple-700 text-xs font-bold rounded-xl transition-colors">
                                <i data-lucide="download" class="h-3.5 w-3.5"></i>
                                Export
                            </a>
                        </div>
                        <div class="p-4 space-y-3 max-h-[350px] overflow-y-auto">
                            @forelse(($metrics['leak']['samples'] ?? []) as $item)
                            <div class="flex items-center justify-between p-4 bg-gray-50 border border-gray-100 rounded-2xl">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-red-50 rounded-xl flex items-center justify-center">
                                        <i data-lucide="user-minus" class="text-red-600 h-5 w-5"></i>
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-900 text-sm">{{ $item->user->name }}</div>
                                        <div class="text-[10px] text-gray-400 mt-0.5 font-semibold">
                                            {{ $item->user->user_type === 'employee' ? 'Pegawai' : 'Siswa' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs font-bold text-gray-700">{{ (string)$item->date }}</div>
                                    <div class="text-[10px] font-extrabold text-red-600 mt-1">Check In: {{ optional($item->check_in)->format('H:i') }}</div>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-12 text-gray-400 text-xs">
                                <i data-lucide="smile" class="h-8 w-8 text-gray-300 mx-auto mb-2"></i>
                                <p>Tidak ada kebocoran absensi</p>
                            </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Incomplete Profiles Table Card -->
                    <div class="rounded-2xl border border-gray-100 bg-white shadow-sm overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
                            <div class="flex flex-col">
                                <span class="font-bold text-gray-800 text-sm">Profil Tidak Lengkap</span>
                                <span class="text-[10px] text-gray-400 font-semibold mt-0.5">Top {{ ($metrics['incomplete_profiles'] ?? collect())->count() }} orang</span>
                            </div>
                            <a href="{{ route('dashboard.export', ['type' => 'incomplete', 'start' => $startDate, 'end' => $endDate]) }}" class="flex items-center gap-1 px-3 py-1.5 bg-yellow-50 hover:bg-yellow-100 text-yellow-700 text-xs font-bold rounded-xl transition-colors">
                                <i data-lucide="download" class="h-3.5 w-3.5"></i>
                                Export
                            </a>
                        </div>
                        <div class="p-4 space-y-3 max-h-[350px] overflow-y-auto">
                            @forelse(($metrics['incomplete_profiles'] ?? []) as $u)
                            <div class="flex items-center justify-between p-4 bg-gray-50 border border-gray-100 rounded-2xl">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-yellow-50 rounded-xl flex items-center justify-center">
                                        <i data-lucide="user-x" class="text-yellow-600 h-5 w-5"></i>
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-900 text-sm">{{ $u->name }}</div>
                                        <div class="text-[10px] text-gray-400 mt-0.5 font-semibold">
                                            {{ $u->user_type === 'employee' ? 'Pegawai' : 'Siswa' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="bg-red-50 border border-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-extrabold">
                                        {{ $u->missing_count ?? 0 }} kosong
                                    </span>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-12 text-gray-400 text-xs">
                                <i data-lucide="award" class="h-8 w-8 text-green-500 mx-auto mb-2"></i>
                                <p>Semua profil sudah lengkap</p>
                            </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Non Users Table Card -->
                    <div class="rounded-2xl border border-gray-100 bg-white shadow-sm overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
                            <div class="flex flex-col">
                                <span class="font-bold text-gray-800 text-sm">Tidak Aktif Menggunakan</span>
                                <span class="text-[10px] text-gray-400 font-semibold mt-0.5">Top {{ ($metrics['non_users'] ?? collect())->count() }} orang</span>
                            </div>
                            <a href="{{ route('dashboard.export', ['type' => 'non_users', 'start' => $startDate, 'end' => $endDate]) }}" class="flex items-center gap-1 px-3 py-1.5 bg-green-50 hover:bg-green-100 text-green-700 text-xs font-bold rounded-xl transition-colors">
                                <i data-lucide="download" class="h-3.5 w-3.5"></i>
                                Export
                            </a>
                        </div>
                        <div class="p-4 space-y-3 max-h-[350px] overflow-y-auto">
                            @forelse(($metrics['non_users'] ?? []) as $u)
                            <div class="flex items-center justify-between p-4 bg-gray-50 border border-gray-100 rounded-2xl">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center">
                                        <i data-lucide="user-minus" class="text-gray-400 h-5 w-5"></i>
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-900 text-sm">{{ $u->name }}</div>
                                        <div class="text-[10px] text-gray-400 mt-0.5 font-semibold">
                                            {{ $u->user_type === 'employee' ? 'Pegawai' : 'Siswa' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-12 text-gray-400 text-xs">
                                <i data-lucide="users" class="h-8 w-8 text-green-500 mx-auto mb-2"></i>
                                <p>Semua user aktif menggunakan</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>              </div>
            </div>
        </div>
        @endif

        <!-- Charts after Ringkasan Performa (role-based) -->
        

        

        <!-- Quick Actions -->
        <div class="mt-8">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-5">
                    <h3 class="text-sm font-bold text-gray-800 mb-4">Menu Aplikasi</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 quick-actions">
                        @if($user->hasRole('admin'))
                            <!-- Admin Menu -->
                            <a href="{{ route('users.index') }}" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md flex flex-col items-center text-center transition-all">
                                <div class="p-3 bg-blue-50 text-blue-600 rounded-2xl mb-2">
                                    <i data-lucide="users" class="h-6 w-6"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-700">Manajemen User</span>
                            </a>
                            <a href="{{ route('users.import') }}" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md flex flex-col items-center text-center transition-all">
                                <div class="p-3 bg-green-50 text-green-600 rounded-2xl mb-2">
                                    <i data-lucide="upload" class="h-6 w-6"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-700">Import Data</span>
                            </a>
                            <a href="{{ route('settings.attendance') }}" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md flex flex-col items-center text-center transition-all">
                                <div class="p-3 bg-purple-50 text-purple-600 rounded-2xl mb-2">
                                    <i data-lucide="settings" class="h-6 w-6"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-700">Pengaturan</span>
                            </a>
                            <a href="{{ route('attendance.reports') }}" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md flex flex-col items-center text-center transition-all">
                                <div class="p-3 bg-orange-50 text-orange-600 rounded-2xl mb-2">
                                    <i data-lucide="bar-chart-2" class="h-6 w-6"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-700">Laporan</span>
                            </a>
                            <a href="{{ route('leave-requests.create') }}" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md flex flex-col items-center text-center transition-all">
                                <div class="p-3 bg-orange-50 text-orange-600 rounded-2xl mb-2">
                                    <i data-lucide="calendar-days" class="h-6 w-6"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-700">Izin Pribadi</span>
                            </a>
                            <a href="{{ route('leave-requests.create', ['user_id' => 'student']) }}" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md flex flex-col items-center text-center transition-all">
                                <div class="p-3 bg-yellow-50 text-yellow-600 rounded-2xl mb-2">
                                    <i data-lucide="user-x" class="h-6 w-6"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-700">Izin Siswa</span>
                            </a>
                        @elseif($user->hasRole('headmaster'))
                            <!-- Headmaster Menu -->
                            <a href="{{ route('attendance.reports') }}" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md flex flex-col items-center text-center transition-all">
                                <div class="p-3 bg-blue-50 text-blue-600 rounded-2xl mb-2">
                                    <i data-lucide="bar-chart-2" class="h-6 w-6"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-700">Laporan Absensi</span>
                            </a>
                            <a href="{{ route('leave-requests.index') }}" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md flex flex-col items-center text-center transition-all">
                                <div class="p-3 bg-yellow-50 text-yellow-600 rounded-2xl mb-2">
                                    <i data-lucide="file-check" class="h-6 w-6"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-700">Persetujuan Izin</span>
                            </a>
                            <a href="{{ route('attendance.index') }}" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md flex flex-col items-center text-center transition-all">
                                <div class="p-3 bg-green-50 text-green-600 rounded-2xl mb-2">
                                    <i data-lucide="calendar-check" class="h-6 w-6"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-700">Status Absensi</span>
                            </a>
                            <a href="{{ route('attendance.export') }}" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md flex flex-col items-center text-center transition-all">
                                <div class="p-3 bg-purple-50 text-purple-600 rounded-2xl mb-2">
                                    <i data-lucide="download" class="h-6 w-6"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-700">Export Laporan</span>
                            </a>
                        @elseif($user->hasRole(['teacher', 'tu', 'bk', 'kesiswaan', 'admin']))
                            <!-- Employee Menu -->
                            <a href="{{ route('attendance.check-in') }}" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md flex flex-col items-center text-center transition-all">
                                <div class="p-3 bg-blue-50 text-blue-600 rounded-2xl mb-2">
                                    <i data-lucide="log-in" class="h-6 w-6"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-700">Absensi Masuk</span>
                            </a>
                            <a href="{{ route('attendance.check-out') }}" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md flex flex-col items-center text-center transition-all">
                                <div class="p-3 bg-green-50 text-green-600 rounded-2xl mb-2">
                                    <i data-lucide="log-out" class="h-6 w-6"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-700">Absensi Keluar</span>
                            </a>
                            @if($user->hasRole(['teacher','admin']))
                                <a href="{{ route('attendance.student-scan') }}" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md flex flex-col items-center text-center transition-all">
                                    <div class="p-3 bg-purple-50 text-purple-600 rounded-2xl mb-2">
                                        <i data-lucide="qr-code" class="h-6 w-6"></i>
                                    </div>
                                    <span class="text-xs font-bold text-gray-700">Scan Siswa</span>
                                </a>
                            @endif
                            <a href="{{ route('attendance.reports') }}" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md flex flex-col items-center text-center transition-all">
                                <div class="p-3 bg-orange-50 text-orange-600 rounded-2xl mb-2">
                                    <i data-lucide="bar-chart-2" class="h-6 w-6"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-700">Laporan</span>
                            </a>
                            <a href="{{ route('leave-requests.create') }}" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md flex flex-col items-center text-center transition-all">
                                <div class="p-3 bg-orange-50 text-orange-600 rounded-2xl mb-2">
                                    <i data-lucide="calendar-days" class="h-6 w-6"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-700">Izin Pribadi</span>
                            </a>
                            @if($user->hasRole(['teacher', 'admin']))
                                <a href="{{ route('leave-requests.create', ['user_id' => 'student']) }}" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md flex flex-col items-center text-center transition-all">
                                    <div class="p-3 bg-yellow-50 text-yellow-600 rounded-2xl mb-2">
                                        <i data-lucide="user-x" class="h-6 w-6"></i>
                                    </div>
                                    <span class="text-xs font-bold text-gray-700">Izin Siswa</span>
                                </a>
                            @endif
                        @elseif($user->hasRole('student'))
                            <!-- Student Menu -->
                            <a href="{{ route('attendance.reports') }}" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md flex flex-col items-center text-center transition-all">
                                <div class="p-3 bg-green-50 text-green-600 rounded-2xl mb-2">
                                    <i data-lucide="calendar" class="h-6 w-6"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-700">Riwayat Absensi</span>
                            </a>
                            <a href="{{ route('leave-requests.index') }}" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md flex flex-col items-center text-center transition-all">
                                <div class="p-3 bg-yellow-50 text-yellow-600 rounded-2xl mb-2">
                                    <i data-lucide="file-text" class="h-6 w-6"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-700">Izin Saya</span>
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
    position:absolute; right:0; top:-10px; height:calc(100% + 20px); width:50%; border-radius:0 12px 12px 0;
    opacity:0; 
    z-index: 1; pointer-events: none;
    transform: translateY(20px) scale(0.95);
    transition: opacity 0.8s ease, transform 0.8s ease;
}
.banner-custom-image.loaded{ 
    opacity:1; 
    transform: translateY(0) scale(1);
}

/* Hide banner image on mobile devices only */
@media (max-width: 767px) {
    .banner-custom-image {
        display: none !important;
    }
}

/* Hide banner image on mobile devices */
@media (max-width: 767px) {
    .banner-custom-image {
        display: none !important;
    }
}

@media (min-width:768px){ .banner-custom-image{ width:45%; } }
@media (min-width:1024px){ .banner-custom-image{ width:40%; } }

/* School photo background animation - lazy loading */
.school-photo-bg{
    opacity: 0;
    transform: scale(0.9) translateY(20px);
    transition: opacity 2s cubic-bezier(0.4, 0, 0.2, 1), transform 2s cubic-bezier(0.4, 0, 0.2, 1), filter 2s cubic-bezier(0.4, 0, 0.2, 1);
    filter: blur(3px);
}
.school-photo-bg.loaded{
    opacity: 1;
    transform: scale(1) translateY(0);
    filter: blur(0);
}

.welcome-hero-wrap{ position: relative; z-index: 2; }
/* Mobile adjustments: keep text readable */
@media (max-width: 639px){
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
        stu: {{ $metrics['completeness']['students']['percentage'] ?? 0 }},
        late: {{ $metrics['late_attendance']['gauge_value'] ?? 0 }}
    };
    const thresholds = {
        good: {{ $metrics['thresholds']['good'] ?? 90 }},
        warn: {{ $metrics['thresholds']['warn'] ?? 75 }}
    };
    function pickColor(value){
        const v = Number(value || 0);
        if (v >= 80) return '#10b981'; // hijau (sukses >= 80%)
        if (v >= 50) return '#f59e0b'; // amber/kuning (perlu perhatian 50-79%)
        return '#ef4444'; // merah (kritis < 50%)
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
                        value: { formatter: (v)=>{ const n = Math.round(v * 10) / 10; return (Number.isInteger(n) ? n : n.toFixed(1)) + '%'; }, fontSize: '16px' }
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
    
    // Render late attendance gauge with inverted colors (higher is worse)
    if (document.querySelector('#gauge-late')) {
        const lateOptions = {
            series: [gauges.late],
            chart: {
                type: 'radialBar',
                height: 200,
                sparkline: { enabled: true }
            },
            plotOptions: {
                radialBar: {
                    startAngle: -90,
                    endAngle: 90,
                    dataLabels: {
                        name: { show: false },
                        value: { 
                            fontSize: '16px',
                            fontWeight: 'bold',
                            color: gauges.late > 50 ? '#dc2626' : gauges.late > 25 ? '#f59e0b' : '#16a34a',
                            formatter: function(val) { return val + '%' }
                        }
                    }
                }
            },
            colors: [gauges.late > 50 ? '#dc2626' : gauges.late > 25 ? '#f59e0b' : '#16a34a'],
            labels: ['Terlambat']
        };
        new ApexCharts(document.querySelector('#gauge-late'), lateOptions).render();
    }
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
        
        // add loaded class to school photo background with delay for lazy effect
        const schoolPhoto=document.querySelector('.school-photo-bg');
        if(schoolPhoto){
            console.log('Adding loaded class to school photo with delay');
            setTimeout(() => {
                schoolPhoto.classList.add('loaded');
            }, 300); // 300ms delay for smoother transition
        }
    });

    // Date Bottom Sheet helpers
    function openDateBottomSheet() {
        const backdrop = document.getElementById('date-sheet-backdrop');
        const sheet = document.getElementById('date-sheet');
        if (backdrop && sheet) {
            backdrop.classList.remove('hidden');
            setTimeout(() => {
                backdrop.classList.add('opacity-100');
                sheet.classList.remove('translate-y-full');
            }, 50);
        }
    }

    function closeDateBottomSheet() {
        const backdrop = document.getElementById('date-sheet-backdrop');
        const sheet = document.getElementById('date-sheet');
        if (backdrop && sheet) {
            backdrop.classList.remove('opacity-100');
            sheet.classList.add('translate-y-full');
            setTimeout(() => {
                backdrop.classList.add('hidden');
            }, 300);
        }
    }
</script>
@endpush
