@extends('layouts.app')

@section('title', 'Network Monitoring - Presensia')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Network Monitoring</h1>
                    <p class="text-gray-600 mt-1">Monitor network traffic, bandwidth, and server performance</p>
                </div>
                <div class="flex items-center space-x-2">
                    <button onclick="refreshData()" 
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-sync-alt mr-2"></i>
                        Refresh
                    </button>
                    <a href="{{ route('super-admin.index') }}" 
                       class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Network Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-globe text-blue-600 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Active Connections</dt>
                            <dd class="text-lg font-medium text-gray-900" id="active-connections">{{ $networkStats['active_connections'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-tachometer-alt text-green-600 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Bandwidth Usage</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $networkStats['bandwidth_usage'] }}%</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-clock text-purple-600 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Latency</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $networkStats['latency'] }}ms</dd>
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
                            <dt class="text-sm font-medium text-gray-500 truncate">Uptime</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $networkStats['uptime'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Server Performance -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-microchip text-red-600 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">CPU Usage</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $serverPerformance['cpu_usage'] }}%</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-memory text-orange-600 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Memory Usage</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $serverPerformance['memory_usage'] }}%</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-hdd text-yellow-600 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Disk Usage</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $serverPerformance['disk_usage'] }}%</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-database text-indigo-600 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">DB Connections</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $serverPerformance['database_connections'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Traffic Chart -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Traffic by Hour (Last 24 Hours)</h3>
                <div id="traffic-chart" style="height: 300px;"></div>
            </div>
        </div>

        <!-- Bandwidth Usage Chart -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Bandwidth Usage</h3>
                <div id="bandwidth-chart" style="height: 300px;"></div>
            </div>
        </div>
    </div>

    <!-- Traffic Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Traffic by School -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Top Schools by Traffic</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">School</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requests</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Percentage</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($trafficData['by_school'] as $school)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $school->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($school->requests) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ ($school->requests / $trafficData['by_school']->max('requests')) * 100 }}%"></div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Traffic by IP -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Top IPs by Traffic</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requests</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Percentage</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($trafficData['by_ip'] as $ip)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <code class="bg-gray-100 px-2 py-1 rounded">{{ $ip->ip_address }}</code>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($ip->requests) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-green-600 h-2 rounded-full" style="width: {{ ($ip->requests / $trafficData['by_ip']->max('requests')) * 100 }}%"></div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Connection Statistics -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Top Countries -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Traffic by Country</h3>
                <div class="space-y-3">
                    @foreach($connectionStats['top_countries'] as $country)
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-900">{{ $country['country'] }}</span>
                        <div class="flex items-center">
                            <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ ($country['requests'] / max(array_column($connectionStats['top_countries'], 'requests'))) * 100 }}%"></div>
                            </div>
                            <span class="text-sm text-gray-500">{{ number_format($country['requests']) }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Connection Types -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Connection Types</h3>
                <div class="space-y-3">
                    @foreach($connectionStats['connection_types'] as $type)
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-900">{{ $type['type'] }}</span>
                        <div class="flex items-center">
                            <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                <div class="bg-green-600 h-2 rounded-full" style="width: {{ $type['percentage'] }}%"></div>
                            </div>
                            <span class="text-sm text-gray-500">{{ $type['percentage'] }}%</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Bandwidth Summary -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Bandwidth Summary (Last 24 Hours)</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ number_format($bandwidthUsage['total_inbound']) }} MB</div>
                    <div class="text-sm text-gray-500">Total Inbound</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ number_format($bandwidthUsage['total_outbound']) }} MB</div>
                    <div class="text-sm text-gray-500">Total Outbound</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600">{{ number_format($bandwidthUsage['peak_inbound']) }} MB</div>
                    <div class="text-sm text-gray-500">Peak Inbound</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-orange-600">{{ number_format($bandwidthUsage['peak_outbound']) }} MB</div>
                    <div class="text-sm text-gray-500">Peak Outbound</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ApexCharts CDN -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize traffic chart
    const trafficOptions = {
        series: [{
            name: 'Requests',
            data: @json(array_values($trafficData['by_hour']->toArray()))
        }],
        chart: {
            type: 'line',
            height: 300,
            toolbar: {
                show: false
            }
        },
        xaxis: {
            categories: @json(array_keys($trafficData['by_hour']->toArray()))
        },
        colors: ['#3B82F6'],
        stroke: {
            curve: 'smooth'
        }
    };

    const trafficChart = new ApexCharts(document.querySelector("#traffic-chart"), trafficOptions);
    trafficChart.render();

    // Initialize bandwidth chart
    const bandwidthOptions = {
        series: [{
            name: 'Inbound',
            data: @json(array_column($bandwidthUsage['data'], 'inbound'))
        }, {
            name: 'Outbound',
            data: @json(array_column($bandwidthUsage['data'], 'outbound'))
        }],
        chart: {
            type: 'area',
            height: 300,
            toolbar: {
                show: false
            }
        },
        xaxis: {
            categories: @json(array_column($bandwidthUsage['data'], 'hour'))
        },
        colors: ['#3B82F6', '#10B981'],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.7,
                opacityTo: 0.3,
            }
        }
    };

    const bandwidthChart = new ApexCharts(document.querySelector("#bandwidth-chart"), bandwidthOptions);
    bandwidthChart.render();

    // Auto-refresh every 30 seconds
    setInterval(refreshData, 30000);
});

function refreshData() {
    // Refresh real-time data
    fetch('{{ route("super-admin.network.realtime") }}')
        .then(response => response.json())
        .then(data => {
            document.getElementById('active-connections').textContent = data.active_connections;
            // Update other real-time metrics here
        })
        .catch(error => console.error('Error refreshing data:', error));
}
</script>
@endsection

