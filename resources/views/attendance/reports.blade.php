@extends('layouts.app')

@section('title', 'Laporan Absensi - Presensia')

@push('styles')
<style>
    /* Table container with enabled horizontal scrolling and smooth scrollbar */
    .attendance-table-container {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
        position: relative;
        max-width: 100%;
        width: 100%;
    }
    .attendance-table-container::-webkit-scrollbar {
        height: 10px;
    }
    .attendance-table-container::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 6px;
    }
    .attendance-table-container::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 6px;
    }
    .attendance-table-container::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    .attendance-table {
        min-width: max-content;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 12px;
    }
    .attendance-table th { padding: 8px 6px; }
    .attendance-table td { padding: 6px 4px; }

    /* Sticky frozen columns for No and Name */
    .attendance-table th.freeze-no,
    .attendance-table td.freeze-no {
        position: sticky !important;
        left: 0 !important;
        z-index: 20 !important;
        background-color: #ffffff !important;
        box-shadow: 1px 0 0 0 #e5e7eb;
        min-width: 48px !important;
        max-width: 48px !important;
        width: 48px !important;
    }
    .attendance-table thead th.freeze-no {
        background-color: #f9fafb !important;
        z-index: 30 !important;
    }

    .attendance-table th.freeze-name,
    .attendance-table td.freeze-name {
        position: sticky !important;
        left: 48px !important;
        z-index: 20 !important;
        background-color: #ffffff !important;
        box-shadow: 3px 0 5px -2px rgba(0, 0, 0, 0.08);
        min-width: 220px !important;
        max-width: 220px !important;
        width: 220px !important;
    }
    .attendance-table thead th.freeze-name {
        background-color: #f9fafb !important;
        z-index: 30 !important;
    }

    /* Fixed date column sizes */
    .attendance-table th.date-column,
    .attendance-table td.date-cell {
        min-width: 68px !important;
        width: 68px !important;
        max-width: 68px !important;
    }

    @media (max-width: 767px) {
        .attendance-table { font-size: 11px !important; }
        .attendance-table th, .attendance-table td { padding: 4px 2px !important; }
        .attendance-table th.freeze-name,
        .attendance-table td.freeze-name {
            min-width: 160px !important;
            max-width: 160px !important;
            width: 160px !important;
        }
    }
    
    /* Clean column styling */
    .holiday-col {
        background-color: #fff1f2 !important;
    }
    .weekend-col {
        background-color: #f8fafc !important;
    }
    .today-col-cell {
        background-color: #fefce8 !important;
    }

    /* Clean, professional status tokens */
    .token-ontime {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 2px 6px;
        border-radius: 6px;
        background-color: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
        font-size: 10px;
        font-weight: 600;
        line-height: 1.2;
        white-space: nowrap;
    }
    .token-late {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 2px 5px;
        border-radius: 6px;
        background-color: #fffbeb;
        color: #92400e;
        border: 1px solid #fcd34d;
        line-height: 1.1;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        white-space: nowrap;
    }
    .token-late-lbl {
        font-size: 8px;
        text-transform: uppercase;
        font-weight: 800;
        letter-spacing: 0.3px;
        color: #b45309;
    }
    .token-late-time {
        font-size: 10px;
        font-weight: 700;
    }
    .token-leave {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 2px 6px;
        border-radius: 6px;
        background-color: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
        font-size: 10px;
        font-weight: 600;
        white-space: nowrap;
    }
    .token-alpha {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 2px 6px;
        border-radius: 4px;
        background-color: #fef2f2;
        color: #dc2626;
        font-size: 10px;
        font-weight: 600;
        white-space: nowrap;
    }
    .token-future {
        color: #cbd5e1;
        font-size: 13px;
        font-weight: 300;
        user-select: none;
    }
    .token-weekend {
        color: #cbd5e1;
        font-size: 14px;
        user-select: none;
    }
    .token-holiday {
        display: inline-flex;
        align-items: center;
        padding: 1px 4px;
        border-radius: 3px;
        background-color: #ffe4e6;
        color: #e11d48;
        font-size: 9px;
        font-weight: 600;
        white-space: nowrap;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const today = new Date();
        const currentDay = today.getDate();
        const currentMonth = today.getMonth() + 1;
        const currentYear = today.getFullYear();
        const selectedMonth = {{ (int)$month }};
        const selectedYear = {{ (int)$year }};

        const tableContainer = document.querySelector('.attendance-table-container');
        const todayColumn = document.querySelector(`.attendance-table th.date-column:nth-of-type(${currentDay})`) || document.querySelector(`th:nth-child(${currentDay + 2})`);
        
        if (selectedMonth === currentMonth && selectedYear === currentYear && todayColumn && tableContainer) {
            todayColumn.style.backgroundColor = '#fef3c7';
            todayColumn.style.border = '2px solid #f59e0b';
            todayColumn.style.borderRadius = '4px';
            todayColumn.style.fontWeight = 'bold';
            todayColumn.style.color = '#b45309';

            setTimeout(() => {
                const leftOffset = todayColumn.offsetLeft - 270;
                tableContainer.scrollTo({ left: Math.max(0, leftOffset), behavior: 'smooth' });
            }, 300);
        }
    });
</script>
@endpush

@section('content')
<div>
    <!-- Header -->
    <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Laporan Absensi</h1>
                    <p class="text-xs text-gray-500 mt-1">{{ $startDate->format('F Y') }}</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('dashboard') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors text-sm font-medium">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Clean Minimalist Legend -->
    <div class="bg-white border border-gray-200/80 rounded-xl mb-4 p-3 shadow-xs">
        <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-gray-600">
            <span class="font-semibold text-gray-800 text-[11px] uppercase tracking-wider">Keterangan:</span>
            <div class="flex items-center gap-1.5">
                <span class="token-ontime text-[9px] py-0.5 px-1.5">✓ 06:25</span>
                <span>Hadir Tepat Waktu</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="token-late py-0 px-1.5">
                    <span class="token-late-lbl text-[7px]">Telat</span>
                    <span class="token-late-time text-[9px]">08:23</span>
                </span>
                <span>Terlambat</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="token-leave text-[10px] py-0.5 px-1.5">Izin/Sakit</span>
                <span>Izin / Sakit / Cuti</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="token-alpha text-[10px] py-0.5 px-1.5">Alpha</span>
                <span>Alpha (Hari Lewat)</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-3 text-center text-slate-400 font-bold">·</span>
                <span class="text-gray-500">Weekend</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="token-holiday py-0 px-1">Libur</span>
                <span class="text-gray-500">Hari Libur</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-3 text-center text-gray-300 font-bold">—</span>
                <span class="text-gray-400">Belum Terjadi</span>
            </div>
        </div>
    </div>

    <!-- Summary Metric Cards -->
    <x-summary-cards :cards="$summaryCards ?? []" />

    <!-- Filter Bar Component -->
    <x-filter-bar 
        :action="route('attendance.reports')" 
        :reset-url="route('attendance.reports')" 
        :active-filters="$activeFilters"
        title="Filter Laporan Absensi"
        badge-class="att-badge att-ontime"
    >
        {{-- 1. Bulan --}}
        <div>
            <label for="month" class="block text-sm text-gray-500 font-normal">Bulan</label>
            <select name="month" id="month" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                @for($i = 1; $i <= 12; $i++)
                <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::create(null, $i, 1)->format('F') }}
                </option>
                @endfor
            </select>
        </div>

        {{-- 2. Tahun --}}
        <div>
            <label for="year" class="block text-sm text-gray-500 font-normal">Tahun</label>
            <select name="year" id="year" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                @for($i = now()->year - 2; $i <= now()->year + 1; $i++)
                <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
            </select>
        </div>

        {{-- 3. Tipe Data --}}
        @if($user->hasRole('admin') || $user->hasRole('teacher') || $user->hasRole('headmaster'))
        <div>
            <label for="type" class="block text-sm text-gray-500 font-normal">Tipe Pengguna</label>
            <select name="type" id="type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                <option value="all" {{ $type == 'all' ? 'selected' : '' }}>Semua Pengguna</option>
                <option value="employees" {{ $type == 'employees' ? 'selected' : '' }}>Pegawai Saja</option>
                <option value="students" {{ $type == 'students' ? 'selected' : '' }}>Siswa Saja</option>
            </select>
        </div>
        @endif

        {{-- 4. Filter Kelas --}}
        <div>
            <label for="class_id" class="block text-sm text-gray-500 font-normal">Kelas</label>
            <select name="class_id" id="class_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                <option value="">Semua Kelas</option>
                <option value="none" {{ request('class_id') === 'none' ? 'selected' : '' }}>-- Tanpa Kelas --</option>
                @foreach($classes as $c)
                    <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- 5. Status Kehadiran --}}
        <div>
            <label for="status" class="block text-sm text-gray-500 font-normal">Status Kehadiran</label>
            <select name="status" id="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                <option value="">Semua Status</option>
                <option value="ontime" {{ request('status') === 'ontime' ? 'selected' : '' }}>Tepat Waktu</option>
                <option value="late" {{ request('status') === 'late' ? 'selected' : '' }}>Terlambat</option>
                <option value="sick" {{ request('status') === 'sick' ? 'selected' : '' }}>Sakit</option>
                <option value="permit" {{ request('status') === 'permit' ? 'selected' : '' }}>Izin</option>
                <option value="alpha" {{ request('status') === 'alpha' ? 'selected' : '' }}>Alpha</option>
            </select>
        </div>

        {{-- 6. Cari Nama / NIS --}}
        <div>
            <label for="q" class="block text-sm text-gray-500 font-normal">Cari Nama / NIS</label>
            <input type="text" name="q" id="q" value="{{ request('q') }}" placeholder="Ketik nama atau NIS..." 
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
        </div>
    </x-filter-bar>

    <!-- Data Type Info -->
    @if($type !== 'all')
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="flex items-center">
            <i class="fas fa-info-circle text-blue-600 mr-2"></i>
            <span class="text-blue-800 text-xs font-medium">
                @if($type === 'employees')
                    Menampilkan data absensi pegawai
                @elseif($type === 'students')
                    Menampilkan data absensi siswa
                @endif
            </span>
        </div>
    </div>
    @endif

    <!-- Attendance Report Table with Grid Cards -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">
                        @if($type === 'employees')
                            Laporan Absensi Pegawai
                        @elseif($type === 'students')
                            Laporan Absensi Siswa
                        @else
                            Laporan Absensi
                        @endif
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $attendances->count() }} {{ $type === 'employees' ? 'pegawai' : ($type === 'students' ? 'siswa' : 'pengguna') }}</p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('attendance.export', request()->all()) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <i class="fas fa-download mr-2"></i>
                        Download Excel
                    </a>
                    <a href="{{ route('attendance.export-detail', request()->all()) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-file-alt mr-2"></i>
                        Download Detail
                    </a>
                </div>
            </div>

            <!-- Attendance Table with Sticky Freeze Panes -->
            <div class="attendance-table-container overflow-x-auto force-scroll-x">
                <table class="min-w-full attendance-table">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-12 freeze-no">No.</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[200px] freeze-name">
                                @if($type === 'employees')
                                    Pegawai
                                @elseif($type === 'students')
                                    Siswa
                                @else
                                    Nama
                                @endif
                            </th>
                            @for($day = 1; $day <= $endDate->day; $day++)
                            @php
                                $headerDate = \Carbon\Carbon::create($year, $month, $day);
                                $headerDateKey = $headerDate->format('Y-m-d');
                                $isHeaderHoliday = isset($holidays[$headerDateKey]);
                                $isHeaderWeekend = $headerDate->isWeekend();
                                $dayNameMap = ['Sun'=>'Min', 'Mon'=>'Sen', 'Tue'=>'Sel', 'Wed'=>'Rab', 'Thu'=>'Kam', 'Fri'=>'Jum', 'Sat'=>'Sab'];
                                $shortDayName = $dayNameMap[$headerDate->format('D')] ?? $headerDate->format('D');
                            @endphp
                            <th class="px-1 py-2 text-center text-xs font-medium uppercase tracking-wider date-column {{ $isHeaderHoliday ? 'bg-rose-50 text-rose-600' : ($isHeaderWeekend ? 'bg-slate-100 text-slate-500' : 'text-gray-600') }}">
                                <span class="block text-[9px] font-normal leading-tight {{ $isHeaderWeekend || $isHeaderHoliday ? 'opacity-85' : 'text-gray-400' }}">{{ $shortDayName }}</span>
                                <span class="block text-xs font-bold leading-tight">{{ $day }}</span>
                            </th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($attendances as $userId => $userAttendances)
                        @php 
                            $user = $userAttendances->first()->user; 
                            $userTypeLabel = $user->user_type === 'student' ? 'Siswa' : 'Pegawai';
                            $todayDate = \Carbon\Carbon::today('Asia/Jakarta');
                        @endphp
                        <tr>
                            <td class="px-2 py-1 text-center text-sm text-gray-600 freeze-no">{{ $loop->iteration }}</td>
                            <td class="px-3 py-1 whitespace-nowrap freeze-name">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <img class="h-10 w-10 rounded-full" src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background={{ $user->user_type === 'student' ? '10B981' : '3B82F6' }}&color=fff" alt="{{ $user->name }}">
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                        @if($type === 'all')
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $user->user_type === 'student' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">{{ $userTypeLabel }}</span>
                                        </p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            @for($day = 1; $day <= $endDate->day; $day++)
                            @php
                                $date = \Carbon\Carbon::create($year, $month, $day);
                                $dateKey = $date->format('Y-m-d');
                                $attendance = $userAttendances->where(function($item) use ($dateKey) { return $item->date->format('Y-m-d') === $dateKey; })->first();
                                $overlayLeave = isset($leaveByUserDate[$userId][$dateKey]) ? $leaveByUserDate[$userId][$dateKey] : null;
                                
                                $isHoliday = isset($holidays[$dateKey]);
                                $holidayName = $isHoliday ? $holidays[$dateKey]->holiday_name : null;
                                
                                $isWeekend = $date->isWeekend();
                                $isFuture = $date->gt($todayDate);
                                $isToday = $date->isSameDay($todayDate);
                                
                                $status = $attendance ? $attendance->status : ($overlayLeave ?: null);
                                $time = $attendance && $attendance->check_in ? $attendance->check_in->format('H:i') : '';
                                
                                $cellClass = 'bg-white';
                                if ($isToday) {
                                    $cellClass = 'today-col-cell';
                                } elseif ($isHoliday) {
                                    $cellClass = 'holiday-col';
                                } elseif ($isWeekend) {
                                    $cellClass = 'weekend-col';
                                }
                            @endphp
                            <td class="px-1 py-1.5 text-center date-cell {{ $cellClass }}">
                                @if($attendance)
                                    @if($status === 'late')
                                        <div class="token-late" title="Terlambat ({{ $time }})">
                                            <span class="token-late-lbl">Telat</span>
                                            <span class="token-late-time">{{ $time }}</span>
                                        </div>
                                    @elseif($status === 'ontime')
                                        <div class="token-ontime" title="Tepat Waktu ({{ $time }})">
                                            <span>✓ {{ $time }}</span>
                                        </div>
                                    @elseif(in_array($status, ['sick', 'permit', 'duty', 'leave']))
                                        @php
                                            $labels = ['sick'=>'Sakit', 'permit'=>'Izin', 'duty'=>'Dinas Luar', 'leave'=>'Cuti'];
                                        @endphp
                                        <div class="token-leave" title="{{ $labels[$status] ?? 'Izin' }}">
                                            {{ $labels[$status] ?? 'Izin' }}
                                        </div>
                                    @else
                                        <div class="token-alpha" title="Alpha">
                                            Alpha
                                        </div>
                                    @endif
                                @elseif($overlayLeave)
                                    @php
                                        $labels = ['sick'=>'Sakit', 'permit'=>'Izin', 'duty'=>'Dinas Luar', 'leave'=>'Cuti'];
                                    @endphp
                                    <div class="token-leave" title="{{ $labels[$overlayLeave] ?? 'Izin/Cuti' }}">
                                        {{ $labels[$overlayLeave] ?? 'Izin' }}
                                    </div>
                                @elseif($isHoliday)
                                    <span class="token-holiday" title="{{ $holidayName }}">Libur</span>
                                @elseif($isWeekend)
                                    <span class="token-weekend">·</span>
                                @elseif($isFuture)
                                    <span class="token-future">—</span>
                                @else
                                    <span class="token-alpha" title="Alpha (Tidak Hadir)">
                                        Alpha
                                    </span>
                                @endif
                            </td>
                            @endfor
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $endDate->day + 2 }}" class="px-6 py-4 text-center text-gray-500">
                                <i class="fas fa-calendar-times text-4xl mb-2"></i>
                                <p>Tidak ada data absensi untuk periode ini.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($attendances->count() > 0)
        <div class="bg-white shadow rounded-lg mt-6">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Ringkasan Statistik</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    @php
                        $totalDays = $endDate->day; $totalRecords = 0; $ontimeCount = 0; $lateCount = 0; $sickCount = 0; $alphaCount = 0;
                        foreach($attendances as $userAttendances) { foreach($userAttendances as $attendance) { $totalRecords++; switch($attendance->status) { case 'ontime': $ontimeCount++; break; case 'late': $lateCount++; break; case 'sick': case 'permit': case 'duty': $sickCount++; break; default: $alphaCount++; break; } } }
                    @endphp
                    <div class="text-center"><div class="text-2xl font-bold text-green-600">{{ $ontimeCount }}</div><div class="text-sm text-gray-500">Ontime</div></div>
                    <div class="text-center"><div class="text-2xl font-bold text-orange-600">{{ $lateCount }}</div><div class="text-sm text-gray-500">Terlambat</div></div>
                    <div class="text-center"><div class="text-2xl font-bold text-yellow-600">{{ $sickCount }}</div><div class="text-sm text-gray-500">Izin/Sakit</div></div>
                    <div class="text-center"><div class="text-2xl font-bold text-red-600">{{ $alphaCount }}</div><div class="text-sm text-gray-500">Alpha</div></div>
                </div>
            </div>
        </div>
        @endif
    </div>
    </div>
@endsection

