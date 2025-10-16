@extends('layouts.app')

@section('title', 'Laporan Absensi - Presensia')

@push('styles')
<style>
    @media (min-width: 768px) {
        .freeze-no { position: sticky !important; left: 0 !important; z-index: 100 !important; background: white !important; min-width: 48px !important; }
        .freeze-name { position: sticky !important; left: 45px !important; z-index: 100 !important; background: white !important; min-width: 200px !important; }
    }
    @media (max-width: 767px) {
        .attendance-table { font-size: 12px !important; }
        .attendance-table th, .attendance-table td { padding: 4px 2px !important; white-space: nowrap !important; }
        .date-column { min-width: 60px !important; max-width: 60px !important; }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const today = new Date();
        const currentDay = today.getDate();
        const todayColumn = document.querySelector(`th:nth-child(${currentDay + 2})`);
        if (todayColumn) {
            const tableContainer = document.querySelector('.overflow-x-auto');
            const columnIndex = currentDay + 1;
            const columnWidth = 80;
            const containerWidth = tableContainer.clientWidth;
            const scrollPosition = (columnIndex * columnWidth) - (containerWidth / 2) + (columnWidth / 2);
            tableContainer.scrollTo({ left: Math.max(0, scrollPosition), behavior: 'smooth' });
            todayColumn.style.backgroundColor = '#fef3c7';
            todayColumn.style.border = '2px solid #f59e0b';
            todayColumn.style.borderRadius = '4px';
        }
    });
</script>
@endpush

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Laporan Absensi</h1>
                    <p class="text-gray-600 mt-1">{{ $startDate->format('F Y') }}</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('dashboard') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <form method="GET" action="{{ route('attendance.reports') }}" class="flex flex-wrap gap-4">
                <div>
                    <label for="month" class="block text-sm font-medium text-gray-700">Bulan</label>
                    <select name="month" id="month" class="mt-1 block border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $i, 1)->format('F') }}
                        </option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label for="year" class="block text-sm font-medium text-gray-700">Tahun</label>
                    <select name="year" id="year" class="mt-1 block border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @for($i = now()->year - 2; $i <= now()->year + 1; $i++)
                        <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                @if($user->hasRole('admin') || $user->hasRole('teacher'))
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700">Tipe Data</label>
                    <select name="type" id="type" class="mt-1 block border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="all" {{ $type == 'all' ? 'selected' : '' }}>Semua</option>
                        <option value="employees" {{ $type == 'employees' ? 'selected' : '' }}>Pegawai</option>
                        <option value="students" {{ $type == 'students' ? 'selected' : '' }}>Siswa</option>
                    </select>
                </div>
                @endif
                <div class="flex items-end">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-search mr-2"></i>Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Legend -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Keterangan Status</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="flex items-center">
                    <div class="w-4 h-4 rounded mr-2" style="background-color: #10b981 !important;"></div>
                    <span class="text-sm text-gray-700">Ontime</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 rounded mr-2" style="background-color: #eab308 !important;"></div>
                    <span class="text-sm text-gray-700">Terlambat</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 rounded mr-2" style="background-color: #f97316 !important;"></div>
                    <span class="text-sm text-gray-700">Sakit/Izin/Cuti/Dinas</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 rounded mr-2" style="background-color: #ef4444 !important;"></div>
                    <span class="text-sm text-gray-700">Alpha</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Type Info -->
    @if($type !== 'all')
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="flex items-center">
            <i class="fas fa-info-circle text-blue-600 mr-2"></i>
            <span class="text-blue-800 font-medium">
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
                    <h3 class="text-lg font-medium text-gray-900">
                        @if($type === 'employees')
                            Laporan Absensi Pegawai
                        @elseif($type === 'students')
                            Laporan Absensi Siswa
                        @else
                            Laporan Absensi
                        @endif
                    </h3>
                    <span class="text-sm text-gray-500">{{ $attendances->count() }} {{ $type === 'employees' ? 'pegawai' : ($type === 'students' ? 'siswa' : 'pengguna') }}</span>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('attendance.export', ['month' => $month, 'year' => $year, 'type' => $type]) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <i class="fas fa-download mr-2"></i>
                        Download Excel
                    </a>
                    <a href="{{ route('attendance.export-detail', ['month' => $month, 'year' => $year, 'type' => $type]) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-file-alt mr-2"></i>
                        Download Detail
                    </a>
                </div>
            </div>

            <!-- Simple Table without Freeze Pane -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 attendance-table">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-12">No.</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[200px]">
                                @if($type === 'employees')
                                    Pegawai
                                @elseif($type === 'students')
                                    Siswa
                                @else
                                    Nama
                                @endif
                            </th>
                            @for($day = 1; $day <= $endDate->day; $day++)
                            <th class="px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-20 date-column">{{ $day }}</th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($attendances as $userId => $userAttendances)
                        @php $user = $userAttendances->first()->user; $userTypeLabel = $user->user_type === 'student' ? 'Siswa' : 'Pegawai'; @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-4 text-center text-sm text-gray-600">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <img class="h-10 w-10 rounded-full" src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background={{ $user->user_type === 'student' ? '10B981' : '3B82F6' }}&color=fff" alt="{{ $user->name }}">
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                        @if($type === 'all')
                                        <div class="text-sm text-gray-500">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $user->user_type === 'student' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">{{ $userTypeLabel }}</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            @for($day = 1; $day <= $endDate->day; $day++)
                            @php
                                $date = \Carbon\Carbon::create($year, $month, $day);
                                $attendance = $userAttendances->where(function($item) use ($date) { return $item->date->format('Y-m-d') === $date->format('Y-m-d'); })->first();
                                $status = $attendance ? $attendance->status : 'alpha';
                                $time = $attendance && $attendance->check_in ? $attendance->check_in->format('H:i') : '';
                                $colors = [ 'ontime'=>'bg-green-100 text-green-800 border-green-200', 'late'=>'bg-yellow-100 text-yellow-800 border-yellow-200', 'sick'=>'bg-orange-100 text-orange-800 border-orange-200', 'permit'=>'bg-orange-100 text-orange-800 border-orange-200', 'duty'=>'bg-orange-100 text-orange-800 border-orange-200', 'leave'=>'bg-orange-100 text-orange-800 border-orange-200', 'alpha'=>'bg-red-100 text-red-800 border-red-200' ];
                            @endphp
                            <td class="px-2 py-4 text-center">
                                <div class="rounded-lg border-2 {{ $colors[$status] ?? 'bg-red-100 text-red-800 border-red-200' }} p-2 min-h-[60px] flex flex-col justify-center">
                                    @if($attendance)
                                        <div class="text-xs font-semibold text-center">{{ ucfirst($status) }}</div>
                                        @if(in_array($status, ['ontime', 'late']) && $time)
                                            <div class="text-xs text-center mt-1 opacity-75">{{ $time }}</div>
                                        @endif
                                    @else
                                        <div class="text-xs font-semibold text-center">Alpha</div>
                                    @endif
                                </div>
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

