@extends('layouts.app')

@section('title', 'Performance Dashboard - Presensia')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Performance Dashboard</h1>
        <p class="text-gray-600 mt-2">Monitor and optimize application performance</p>
    </div>

    <!-- Performance Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Database Statistics</h3>
            <div class="space-y-2">
                @foreach($stats as $table => $count)
                <div class="flex justify-between">
                    <span class="text-gray-600">{{ ucfirst($table) }}</span>
                    <span class="font-semibold">{{ number_format($count) }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Memory Usage</h3>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-600">Current</span>
                    <span class="font-semibold">{{ $memoryUsage['current'] }} MB</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Peak</span>
                    <span class="font-semibold">{{ $memoryUsage['peak'] }} MB</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Limit</span>
                    <span class="font-semibold">{{ $memoryUsage['limit'] }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Cache Status</h3>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-600">Config</span>
                    <span class="font-semibold {{ $cacheStats['config_cached'] ? 'text-green-600' : 'text-red-600' }}">
                        {{ $cacheStats['config_cached'] ? 'Cached' : 'Not Cached' }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Routes</span>
                    <span class="font-semibold {{ $cacheStats['routes_cached'] ? 'text-green-600' : 'text-red-600' }}">
                        {{ $cacheStats['routes_cached'] ? 'Cached' : 'Not Cached' }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Views</span>
                    <span class="font-semibold {{ $cacheStats['views_cached'] ? 'text-green-600' : 'text-red-600' }}">
                        {{ $cacheStats['views_cached'] ? 'Cached' : 'Not Cached' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Actions -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Performance Actions</h3>
        <div class="flex flex-wrap gap-4">
            <form method="POST" action="{{ route('performance.optimize') }}" class="inline">
                @csrf
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-rocket mr-2"></i>Optimize Performance
                </button>
            </form>
            
            <form method="POST" action="{{ route('performance.clear-cache') }}" class="inline">
                @csrf
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors">
                    <i class="fas fa-trash mr-2"></i>Clear All Caches
                </button>
            </form>
            
            <a href="{{ route('dashboard') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
