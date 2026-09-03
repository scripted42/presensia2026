@extends('layouts.app')

@section('title', 'Jadwal Absensi - Presensia')

@section('content')
<div>
    <!-- Header -->
    <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Jadwal Absensi</h1>
                    <p class="text-xs text-gray-500 mt-1">Lihat jadwal khusus dan hari libur</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('dashboard') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors text-sm font-medium">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Schedule Info -->
    <div id="today-schedule" class="bg-white shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Jadwal Hari Ini</h2>
            <div id="today-info" class="text-center py-4">
                <i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i>
                <p class="mt-2 text-xs text-gray-500">Memuat informasi jadwal...</p>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <form method="GET" action="{{ route('schedule.index') }}" class="flex items-center gap-4">
                <div>
                    <label for="month" class="block text-sm text-gray-500 font-normal">Bulan</label>
                    <select name="month" id="month" class="mt-1 block border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                        @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $i, 1)->format('F') }}
                        </option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label for="year" class="block text-sm text-gray-500 font-normal">Tahun</label>
                    <select name="year" id="year" class="mt-1 block border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                        @for($i = now()->year - 1; $i <= now()->year + 1; $i++)
                        <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="mt-6">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                        <i class="fas fa-filter mr-2"></i>Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Special Schedules -->
    @if($specialSchedules->count() > 0)
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Jadwal Khusus</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($specialSchedules as $schedule)
                <div class="border rounded-lg p-4 {{ $schedule->is_active ? 'border-green-200 bg-green-50' : 'border-gray-200 bg-gray-50' }}">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-medium text-gray-900">{{ $schedule->name }}</p>
                        @if($schedule->is_active)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i>Aktif
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                <i class="fas fa-times-circle mr-1"></i>Nonaktif
                            </span>
                        @endif
                    </div>
                    <div class="text-xs text-gray-500 space-y-1">
                        <div><i class="fas fa-calendar-day mr-2"></i>{{ ucfirst($schedule->day_of_week) }}</div>
                        <div><i class="fas fa-clock mr-2"></i>Max Check-in: {{ $schedule->max_check_in_time->format('H:i') }}</div>
                        @if($schedule->affected_roles && count($schedule->affected_roles) > 0)
                        <div><i class="fas fa-users mr-2"></i>Role: {{ implode(', ', $schedule->affected_roles) }}</div>
                        @else
                        <div><i class="fas fa-users mr-2"></i>Role: Semua</div>
                        @endif
                        @if($schedule->description)
                        <div><i class="fas fa-info-circle mr-2"></i>{{ $schedule->description }}</div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Daily Overrides -->
    @if($dailyOverrides->count() > 0)
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Jadwal Khusus Tanggal</h2>
            <div class="space-y-3">
                @foreach($dailyOverrides as $override)
                <div class="border rounded-lg p-4 border-blue-200 bg-blue-50">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $override->reason }}</p>
                            <div class="text-xs text-gray-500 mt-1">
                                <span><i class="fas fa-calendar mr-2"></i>{{ \Carbon\Carbon::parse($override->date)->format('d/m/Y') }}</span>
                                <span class="ml-4"><i class="fas fa-clock mr-2"></i>Max Check-in: {{ $override->max_check_in_time->format('H:i') }}</span>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                            <i class="fas fa-star mr-1"></i>Khusus
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Holidays -->
    @if($holidays->count() > 0)
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Hari Libur</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($holidays as $holiday)
                <div class="border rounded-lg p-4 border-red-200 bg-red-50">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-medium text-gray-900">{{ $holiday->holiday_name }}</p>
                        @if($holiday->is_national_holiday)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                <i class="fas fa-flag mr-1"></i>Nasional
                            </span>
                        @elseif($holiday->is_weekend)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                <i class="fas fa-calendar-week mr-1"></i>Weekend
                            </span>
                        @endif
                    </div>
                    <div class="text-xs text-gray-500">
                        <div><i class="fas fa-calendar mr-2"></i>{{ \Carbon\Carbon::parse($holiday->date)->format('d/m/Y') }} ({{ $holiday->day_name }})</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Empty State -->
    @if($specialSchedules->count() == 0 && $dailyOverrides->count() == 0 && $holidays->count() == 0)
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6 text-center">
            <i class="fas fa-calendar-alt text-4xl text-gray-400 mb-4"></i>
            <h2 class="text-lg font-semibold text-gray-900 mb-2">Tidak Ada Jadwal Khusus</h2>
            <p class="text-xs text-gray-500">Tidak ada jadwal khusus atau hari libur untuk periode ini.</p>
        </div>
    </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load today's schedule info
    loadScheduleInfo();
});

function loadScheduleInfo() {
    // Show loading state
    const todayInfo = document.getElementById('today-info');
    todayInfo.innerHTML = `
        <i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i>
        <p class="mt-2 text-gray-500">Memuat informasi jadwal...</p>
    `;
    
    // Add timeout to prevent infinite loading
    const timeoutId = setTimeout(() => {
        todayInfo.innerHTML = `
            <div class="text-center">
                <i class="fas fa-clock text-4xl text-blue-500 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Jadwal Hari Ini</h3>
                <p class="text-2xl font-bold text-blue-600 mb-2">Max Check-in: 07:00</p>
                <p class="text-sm text-gray-600">Jadwal default (tidak ada jadwal khusus)</p>
                <button onclick="loadScheduleInfo()" class="mt-2 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    Refresh
                </button>
            </div>
        `;
    }, 5000); // 5 second timeout
    
    fetch('{{ route("schedule.today") }}', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        credentials: 'include',
        mode: 'cors'
    })
        .then(response => {
            console.log('Response status:', response.status);
            clearTimeout(timeoutId); // Clear timeout on successful response
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Schedule data:', data);
            clearTimeout(timeoutId); // Clear timeout on successful data
            const todayInfo = document.getElementById('today-info');
            
            if (data.is_holiday) {
                todayInfo.innerHTML = `
                    <div class="text-center">
                        <i class="fas fa-calendar-times text-4xl text-red-500 mb-4"></i>
                        <h3 class="text-lg font-medium text-red-800 mb-2">Hari Libur</h3>
                        <p class="text-red-600">${data.holiday_name}</p>
                        <p class="text-sm text-gray-500 mt-2">Absensi tidak diperbolehkan pada hari libur</p>
                    </div>
                `;
            } else {
                let scheduleInfo = `
                    <div class="text-center">
                        <i class="fas fa-clock text-4xl text-blue-500 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Jadwal Hari Ini</h3>
                        <p class="text-2xl font-bold text-blue-600 mb-2">Max Check-in: ${data.max_check_in_time}</p>
                `;
                
                if (data.reason) {
                    scheduleInfo += `<p class="text-sm text-gray-600">Karena: ${data.reason}</p>`;
                }
                
                scheduleInfo += `</div>`;
                todayInfo.innerHTML = scheduleInfo;
            }
        })
        .catch(error => {
            console.error('Schedule loading error:', error);
            clearTimeout(timeoutId); // Clear timeout on error
            
            // Try fallback for ngrok issues
            if (error.message.includes('Failed to fetch') || error.message.includes('CORS')) {
                console.log('Attempting fallback for ngrok/CORS issue...');
                
                // Show fallback content instead of retrying
                document.getElementById('today-info').innerHTML = `
                    <div class="text-center">
                        <i class="fas fa-clock text-4xl text-blue-500 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Jadwal Hari Ini</h3>
                        <p class="text-2xl font-bold text-blue-600 mb-2">Max Check-in: 07:00</p>
                        <p class="text-sm text-gray-600">Jadwal default (tidak ada jadwal khusus)</p>
                        <button onclick="loadScheduleInfo()" class="mt-2 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                            Refresh
                        </button>
                    </div>
                `;
                return;
            }
            
            document.getElementById('today-info').innerHTML = `
                <div class="text-center">
                    <i class="fas fa-exclamation-triangle text-4xl text-yellow-500 mb-4"></i>
                    <p class="text-gray-500">Gagal memuat informasi jadwal</p>
                    <p class="text-xs text-gray-400 mt-2">Error: ${error.message || 'Unknown error'}</p>
                    <button onclick="loadScheduleInfo()" class="mt-2 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                        Coba Lagi
                    </button>
                </div>
            `;
        });
}
</script>
@endsection

