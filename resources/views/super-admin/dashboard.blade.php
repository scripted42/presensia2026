@extends('layouts.app')

@section('title', 'Super Admin Dashboard - Presensia')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Super Admin Dashboard</h1>
                    <p class="text-gray-600 mt-1">Kelola semua sekolah dan tenant dalam sistem SaaS</p>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('super-admin.password') }}" 
                       class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        <i class="fas fa-key mr-2"></i>
                        Ubah Password
                    </a>
                    <a href="{{ route('super-admin.schools.create') }}" 
                       class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-plus mr-2"></i>
                        Tambah Sekolah
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Access Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
        <!-- Audit Trail Card -->
        <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow cursor-pointer" onclick="window.location.href='{{ route('super-admin.audit-trails.index') }}'">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-history text-blue-600 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Audit Trail</dt>
                            <dd class="text-lg font-medium text-gray-900" id="audit-total">-</dd>
                            <dd class="text-xs text-gray-500">Total Activities</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Monitoring Card -->
        <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow cursor-pointer" onclick="window.location.href='{{ route('super-admin.security.index') }}'">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-shield-alt text-red-600 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Security Monitoring</dt>
                            <dd class="text-lg font-medium text-gray-900" id="security-total">-</dd>
                            <dd class="text-xs text-gray-500">Total Attacks</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Banned IPs Card -->
        <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow cursor-pointer" onclick="window.location.href='{{ route('super-admin.security.banned-ips') }}'">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-ban text-orange-600 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Banned IPs</dt>
                            <dd class="text-lg font-medium text-gray-900" id="banned-total">-</dd>
                            <dd class="text-xs text-gray-500">Active Bans</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        

        <!-- Schools Card -->
        <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow cursor-pointer" onclick="window.location.href='{{ route('super-admin.schools.index') }}'">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-school text-green-600 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Schools</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $analytics['total_schools'] }}</dd>
                            <dd class="text-xs text-gray-500">Total Schools</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Database Card -->
        <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow cursor-pointer">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-database text-indigo-600 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Database</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $analytics['database_stats']['total_size_gb'] }} GB</dd>
                            <dd class="text-xs text-gray-500">Total Size</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-users text-green-600 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Users</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($analytics['total_users']) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Active Schools</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $analytics['active_schools'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Critical Attacks</dt>
                            <dd class="text-lg font-medium text-gray-900" id="critical-attacks">-</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-clock text-blue-600 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Recent Activity</dt>
                            <dd class="text-lg font-medium text-gray-900" id="recent-activity">-</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Dashboard -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Database Usage Gauge -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Database Usage</h3>
                <div class="flex items-center justify-center">
                    <div id="database-gauge" class="w-48 h-48"></div>
                </div>
                <div class="mt-4 text-center">
                    <p class="text-sm text-gray-600">
                        Total: <span class="font-semibold">{{ $analytics['database_stats']['total_size_gb'] }} GB</span> | 
                        Average: <span class="font-semibold">{{ $analytics['database_stats']['average_size_mb'] }} MB</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- School Usage Chart -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Top Schools by Users</h3>
                <div id="school-usage-chart" class="h-64"></div>
            </div>
        </div>
    </div>

    <!-- Database Size by School -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Database Size by School</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sekolah</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Users</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Database Size</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usage Score</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($analytics['school_analytics']->sortByDesc('database_size_mb') as $school)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $school['name'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ number_format($school['user_count']) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $school['database_size_mb'] }} MB
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-{{ $school['usage_score'] > 80 ? 'red' : ($school['usage_score'] > 50 ? 'yellow' : 'green') }}-500 h-2 rounded-full" 
                                             style="width: {{ $school['usage_score'] }}%"></div>
                                    </div>
                                    <span class="text-sm text-gray-500">{{ round($school['usage_score'], 1) }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                    {{ $school['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $school['is_active'] ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recommendations -->
    @if(count($analytics['recommendations']) > 0)
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Rekomendasi Sistem</h3>
            <div class="space-y-4">
                @foreach($analytics['recommendations'] as $recommendation)
                <div class="border-l-4 border-{{ $recommendation['type'] === 'warning' ? 'red' : ($recommendation['type'] === 'info' ? 'blue' : 'green') }}-400 bg-{{ $recommendation['type'] === 'warning' ? 'red' : ($recommendation['type'] === 'info' ? 'blue' : 'green') }}-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-{{ $recommendation['type'] === 'warning' ? 'exclamation-triangle' : ($recommendation['type'] === 'info' ? 'info-circle' : 'check-circle') }} text-{{ $recommendation['type'] === 'warning' ? 'red' : ($recommendation['type'] === 'info' ? 'blue' : 'green') }}-400"></i>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-{{ $recommendation['type'] === 'warning' ? 'red' : ($recommendation['type'] === 'info' ? 'blue' : 'green') }}-800">
                                {{ $recommendation['title'] }}
                            </h4>
                            <p class="mt-1 text-sm text-{{ $recommendation['type'] === 'warning' ? 'red' : ($recommendation['type'] === 'info' ? 'blue' : 'green') }}-700">
                                {{ $recommendation['message'] }}
                            </p>
                            @if(count($recommendation['schools']) > 0)
                            <div class="mt-2">
                                <p class="text-xs text-{{ $recommendation['type'] === 'warning' ? 'red' : ($recommendation['type'] === 'info' ? 'blue' : 'green') }}-600">
                                    Sekolah: {{ implode(', ', $recommendation['schools']) }}
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Schools List -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Daftar Sekolah</h3>
            
            @if($schools->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($schools as $school)
                        <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h4 class="text-lg font-medium text-gray-900">{{ $school->name }}</h4>
                                    <p class="text-sm text-gray-500">{{ $school->address }}</p>
                                </div>
                                <div class="flex space-x-2">
                                    <a href="{{ route('super-admin.schools.show', $school) }}" 
                                       class="text-blue-600 hover:text-blue-800" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('super-admin.schools.edit', $school) }}" 
                                       class="text-yellow-600 hover:text-yellow-800" title="Edit Sekolah">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('super-admin.schools.toggle-status', $school) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-{{ $school->is_active ? 'red' : 'green' }}-600 hover:text-{{ $school->is_active ? 'red' : 'green' }}-800" 
                                                title="{{ $school->is_active ? 'Nonaktifkan' : 'Aktifkan' }} Sekolah">
                                            <i class="fas fa-{{ $school->is_active ? 'pause' : 'play' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('super-admin.schools.destroy', $school) }}" method="POST" class="inline" 
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus sekolah {{ $school->name }}? Tindakan ini tidak dapat dibatalkan!')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800" title="Hapus Sekolah">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            
                            <!-- School Info -->
                            <div class="mb-4">
                                <div class="flex items-center text-sm text-gray-500 mb-1">
                                    <i class="fas fa-phone mr-2"></i>
                                    {{ $school->phone }}
                                </div>
                                <div class="flex items-center text-sm text-gray-500 mb-1">
                                    <i class="fas fa-envelope mr-2"></i>
                                    {{ $school->email }}
                                </div>
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-globe mr-2"></i>
                                    {{ $school->website }}
                                </div>
                            </div>

                            <!-- Tenant Settings -->
                            @if($school->tenantSettings)
                                <div class="mb-4">
                                    <h5 class="text-sm font-medium text-gray-700 mb-2">Branding:</h5>
                                    <div class="flex items-center space-x-2">
                                        <div class="w-4 h-4 rounded" style="background-color: {{ $school->tenantSettings->primary_color }}"></div>
                                        <span class="text-sm text-gray-600">{{ $school->tenantSettings->app_name }}</span>
                                    </div>
                                </div>
                            @endif

                            <!-- Stats -->
                            <div class="flex justify-between text-sm text-gray-500">
                                <span><i class="fas fa-users mr-1"></i>{{ $school->users->count() }} users</span>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $school->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $school->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-school text-4xl text-gray-400 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada sekolah</h3>
                    <p class="text-gray-500">Belum ada sekolah yang terdaftar dalam sistem.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- ApexCharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Database Usage Gauge
    const databaseGauge = {
        series: [{{ $analytics['database_stats']['total_size_gb'] }}],
        chart: {
            type: 'radialBar',
            height: 200,
        },
        plotOptions: {
            radialBar: {
                startAngle: -90,
                endAngle: 90,
                track: {
                    background: '#e7e7e7',
                    strokeWidth: '97%',
                    margin: 5,
                },
                dataLabels: {
                    name: {
                        show: true,
                        fontSize: '16px',
                        fontWeight: 'bold',
                        color: '#333',
                        offsetY: -10,
                    },
                    value: {
                        show: true,
                        fontSize: '30px',
                        fontWeight: 'bold',
                        color: '#333',
                        offsetY: 16,
                        formatter: function (val) {
                            return val + ' GB';
                        }
                    }
                }
            }
        },
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'light',
                type: 'horizontal',
                shadeIntensity: 0.5,
                gradientToColors: ['#3B82F6'],
                inverseColors: true,
                opacityFrom: 1,
                opacityTo: 1,
                stops: [0, 100]
            }
        },
        colors: ['#3B82F6'],
        labels: ['Database Size']
    };

    const gaugeChart = new ApexCharts(document.querySelector("#database-gauge"), databaseGauge);
    gaugeChart.render();

    // School Usage Chart
    const schoolData = @json($analytics['top_schools']);
    const schoolNames = schoolData.map(school => school.name);
    const userCounts = schoolData.map(school => school.user_count);

    const schoolChart = {
        series: [{
            name: 'Users',
            data: userCounts
        }],
        chart: {
            type: 'bar',
            height: 300,
        },
        plotOptions: {
            bar: {
                horizontal: true,
                borderRadius: 4,
            }
        },
        dataLabels: {
            enabled: true,
            formatter: function (val) {
                return val + ' users';
            }
        },
        xaxis: {
            categories: schoolNames,
        },
        colors: ['#3B82F6'],
        title: {
            text: 'Top Schools by User Count',
            align: 'left'
        }
    };

    const chart = new ApexCharts(document.querySelector("#school-usage-chart"), schoolChart);
    chart.render();

    // Load real-time data for dashboard cards
    loadDashboardData();
    
    // Auto-refresh every 30 seconds
    setInterval(loadDashboardData, 30000);
});

function loadDashboardData() {
    // Load audit trail statistics
    fetch('{{ route("super-admin.audit-trails.statistics") }}')
        .then(response => response.json())
        .then(data => {
            document.getElementById('audit-total').textContent = data.total_actions || 0;
        })
        .catch(error => console.error('Error loading audit data:', error));

    // Load security statistics
    fetch('{{ route("super-admin.security.statistics") }}')
        .then(response => response.json())
        .then(data => {
            document.getElementById('security-total').textContent = data.total_attacks || 0;
            document.getElementById('critical-attacks').textContent = data.critical_attacks || 0;
        })
        .catch(error => console.error('Error loading security data:', error));

    // Load banned IPs count
    fetch('{{ route("super-admin.security.banned-ips") }}')
        .then(response => response.text())
        .then(html => {
            // Extract count from the page (this is a simple approach)
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const bannedCount = doc.querySelectorAll('tbody tr').length;
            document.getElementById('banned-total').textContent = bannedCount;
        })
        .catch(error => console.error('Error loading banned IPs:', error));

    

    // Load recent activity
    fetch('{{ route("super-admin.audit-trails.statistics") }}')
        .then(response => response.json())
        .then(data => {
            document.getElementById('recent-activity').textContent = data.recent_activity || 0;
        })
        .catch(error => console.error('Error loading recent activity:', error));
}
</script>
@endsection
