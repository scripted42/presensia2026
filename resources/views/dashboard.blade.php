@extends('layouts.app')

@section('title', 'Dashboard - Presensia')

@section('content')
        <!-- Welcome Section -->
        <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Dashboard</h1>
                <p class="text-gray-600">Selamat datang di sistem manajemen sekolah Presensia</p>
                <div class="mt-4 flex flex-wrap gap-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        <i class="fas fa-building mr-2"></i>{{ $school->name }}
                    </span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        <i class="fas fa-user-tag mr-2"></i>{{ $user->roles->first()->name ?? 'No Role' }}
                    </span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                        <i class="fas fa-user mr-2"></i>{{ $user->user_type === 'employee' ? 'Pegawai' : 'Siswa' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            @if(isset($stats['total_employees']))
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-users text-blue-600 text-2xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Pegawai</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $stats['total_employees'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if(isset($stats['total_students']))
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-graduation-cap text-green-600 text-2xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Siswa</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $stats['total_students'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if(isset($stats['total_classes']))
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-chalkboard-teacher text-purple-600 text-2xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Kelas</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $stats['total_classes'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if(isset($stats['today_attendance']))
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-calendar-check text-orange-600 text-2xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Absensi Hari Ini</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $stats['today_attendance'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if(isset($stats['my_classes']))
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-chalkboard text-indigo-600 text-2xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Kelas Saya</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $stats['my_classes'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if(isset($stats['my_students']))
            <div class="bg-white overflow-hidden shadow rounded-lg">
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
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-clock text-yellow-600 text-2xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Izin Pending</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $stats['pending_leaves'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if(isset($stats['approved_leaves']))
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Izin Disetujui</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $stats['approved_leaves'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Charts and Activities -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Attendance Chart -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Grafik Absensi 7 Hari Terakhir</h3>
                    <div class="h-64">
                        <canvas id="attendanceChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Aktivitas Terbaru</h3>
                    <div class="space-y-4">
                        @forelse($recentActivities as $activity)
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-{{ $activity['color'] }}-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-{{ $activity['icon'] }} text-{{ $activity['color'] }}-600 text-sm"></i>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-900">{{ $activity['message'] }}</p>
                                <p class="text-xs text-gray-500">{{ $activity['time']->diffForHumans() }}</p>
                            </div>
                        </div>
                        @empty
                        <p class="text-sm text-gray-500">Tidak ada aktivitas terbaru</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-8">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Aksi Cepat</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
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
                            <a href="{{ route('attendance.reports') }}" class="flex flex-col items-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors">
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
                        @elseif($user->hasRole(['teacher', 'tu', 'bk', 'kesiswaan']))
                            <!-- Employee Menu -->
                            <a href="{{ route('attendance.check-in') }}" class="flex flex-col items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                                <i class="fas fa-sign-in-alt text-blue-600 text-2xl mb-2"></i>
                                <span class="text-sm font-medium text-blue-900">Absensi Masuk</span>
                            </a>
                            <a href="{{ route('attendance.check-out') }}" class="flex flex-col items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                                <i class="fas fa-sign-out-alt text-green-600 text-2xl mb-2"></i>
                                <span class="text-sm font-medium text-green-900">Absensi Keluar</span>
                            </a>
                            @if($user->hasRole('teacher'))
                                <a href="{{ route('attendance.student-scan') }}" class="flex flex-col items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                                    <i class="fas fa-qrcode text-purple-600 text-2xl mb-2"></i>
                                    <span class="text-sm font-medium text-purple-900">Scan Siswa</span>
                                </a>
                            @endif
                            <a href="{{ route('attendance.reports') }}" class="flex flex-col items-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors">
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Attendance Chart
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    const chartData = @json($attendanceChart);
    
    const attendanceChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.map(item => item.date),
            datasets: [{
                label: 'Status Absensi',
                data: chartData.map(item => {
                    const statusMap = {
                        'ontime': 1,
                        'late': 0.5,
                        'sick': 0.3,
                        'permit': 0.3,
                        'duty': 0.3,
                        'leave': 0.3,
                        'alpha': 0
                    };
                    return statusMap[item.status] || 0;
                }),
                backgroundColor: chartData.map(item => {
                    const colorMap = {
                        'green': 'rgba(34, 197, 94, 0.8)',
                        'orange': 'rgba(249, 115, 22, 0.8)',
                        'yellow': 'rgba(234, 179, 8, 0.8)',
                        'red': 'rgba(239, 68, 68, 0.8)'
                    };
                    return colorMap[item.color] || 'rgba(156, 163, 175, 0.8)';
                }),
                borderColor: chartData.map(item => {
                    const colorMap = {
                        'green': 'rgb(34, 197, 94)',
                        'orange': 'rgb(249, 115, 22)',
                        'yellow': 'rgb(234, 179, 8)',
                        'red': 'rgb(239, 68, 68)'
                    };
                    return colorMap[item.color] || 'rgb(156, 163, 175)';
                }),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 1,
                    ticks: {
                        callback: function(value) {
                            const labels = {
                                0: 'Alpha',
                                0.3: 'Izin/Sakit',
                                0.5: 'Terlambat',
                                1: 'Ontime'
                            };
                            return labels[value] || '';
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
</script>
@endpush
