@extends('layouts.app')

@section('title', 'Security Monitoring - Presensia')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Security Monitoring</h1>
                    <p class="text-gray-600 mt-1">Monitor security threats and manage IP bans</p>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('super-admin.security.export') }}" 
                       class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-download mr-2"></i>
                        Export CSV
                    </a>
                    <a href="{{ route('super-admin.security.banned-ips') }}" 
                       class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <i class="fas fa-ban mr-2"></i>
                        Banned IPs
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-shield-alt text-red-600 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Attacks</dt>
                            <dd class="text-lg font-medium text-gray-900" id="total-attacks">-</dd>
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
                        <i class="fas fa-ban text-orange-600 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Blocked Attacks</dt>
                            <dd class="text-lg font-medium text-gray-900" id="blocked-attacks">-</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-network-wired text-blue-600 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Unique IPs</dt>
                            <dd class="text-lg font-medium text-gray-900" id="unique-ips">-</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <div>
                    <label for="school_id" class="block text-sm font-medium text-gray-700">School</label>
                    <select name="school_id" id="school_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Schools</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->id }}" {{ request('school_id') == $school->id ? 'selected' : '' }}>
                                {{ $school->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="attack_type" class="block text-sm font-medium text-gray-700">Attack Type</label>
                    <select name="attack_type" id="attack_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Types</option>
                        <option value="brute_force" {{ request('attack_type') == 'brute_force' ? 'selected' : '' }}>Brute Force</option>
                        <option value="ddos" {{ request('attack_type') == 'ddos' ? 'selected' : '' }}>DDoS</option>
                        <option value="sql_injection" {{ request('attack_type') == 'sql_injection' ? 'selected' : '' }}>SQL Injection</option>
                        <option value="xss" {{ request('attack_type') == 'xss' ? 'selected' : '' }}>XSS</option>
                        <option value="directory_traversal" {{ request('attack_type') == 'directory_traversal' ? 'selected' : '' }}>Directory Traversal</option>
                        <option value="suspicious_activity" {{ request('attack_type') == 'suspicious_activity' ? 'selected' : '' }}>Suspicious Activity</option>
                    </select>
                </div>

                <div>
                    <label for="severity" class="block text-sm font-medium text-gray-700">Severity</label>
                    <select name="severity" id="severity" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Severities</option>
                        <option value="low" {{ request('severity') == 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ request('severity') == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ request('severity') == 'high' ? 'selected' : '' }}>High</option>
                        <option value="critical" {{ request('severity') == 'critical' ? 'selected' : '' }}>Critical</option>
                    </select>
                </div>

                <div>
                    <label for="ip_address" class="block text-sm font-medium text-gray-700">IP Address</label>
                    <input type="text" name="ip_address" id="ip_address" value="{{ request('ip_address') }}" 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                           placeholder="192.168.1.1">
                </div>

                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                    <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                    <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="md:col-span-6 flex justify-end">
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-search mr-2"></i>
                        Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Security Logs Table -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Security Logs</h3>
            
            @if($securityLogs->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">School</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attack Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Severity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attempts</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($securityLogs as $log)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $log->created_at->format('Y-m-d H:i:s') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $log->school ? $log->school->name : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <code class="bg-gray-100 px-2 py-1 rounded">{{ $log->ip_address }}</code>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $log->formatted_attack_type }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-{{ $log->severity_color }}-100 text-{{ $log->severity_color }}-800">
                                        {{ $log->formatted_severity }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $log->attempt_count }}
                                    @if($log->isRepeatedAttack())
                                        <span class="text-red-500 ml-1">(Repeated)</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $log->is_blocked ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                        {{ $log->is_blocked ? 'Blocked' : 'Active' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('super-admin.security.show', $log) }}" 
                                       class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                                    @if(!$log->is_blocked)
                                        <form action="{{ route('super-admin.security.block', $log) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-red-600 hover:text-red-900">Block</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $securityLogs->links() }}
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-shield-alt text-4xl text-gray-400 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No security logs found</h3>
                    <p class="text-gray-500">No security events match your current filters.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load security statistics
    fetch('{{ route("super-admin.security.statistics") }}')
        .then(response => response.json())
        .then(data => {
            document.getElementById('total-attacks').textContent = data.total_attacks || 0;
            document.getElementById('critical-attacks').textContent = data.critical_attacks || 0;
            document.getElementById('blocked-attacks').textContent = data.blocked_attacks || 0;
            document.getElementById('unique-ips').textContent = data.unique_ips || 0;
        })
        .catch(error => console.error('Error loading statistics:', error));
});
</script>
@endsection

