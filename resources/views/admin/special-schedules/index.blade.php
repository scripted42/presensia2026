@extends('layouts.app')

@section('title', 'Kelola Jadwal Khusus - Presensia')

@section('content')
<div>
    <!-- Header -->
    <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Kelola Jadwal Khusus</h1>
                    <p class="text-xs text-gray-500 mt-1">Atur jadwal khusus seperti upacara Senin, rapat guru, dll</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.special-schedules.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                        <i class="fas fa-plus mr-2"></i>Tambah Jadwal Khusus
                    </a>
                    <a href="{{ route('dashboard') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors text-sm font-medium">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Box -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-400"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-semibold text-blue-800">Cara Kerja Jadwal Khusus</h3>
                <div class="mt-2 text-xs text-blue-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li><strong>Upacara Senin:</strong> Max check-in 07:30 (bukan 06:30) untuk semua role</li>
                        <li><strong>Rapat Guru:</strong> Max check-in 07:15 hanya untuk guru dan pegawai</li>
                        <li><strong>Prioritas:</strong> Daily Override > Special Schedule > Regular Settings</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Special Schedules List -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-4 py-5 sm:p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Daftar Jadwal Khusus</h2>
            
            @if($specialSchedules->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Jadwal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hari</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Max Check-in</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periode</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($specialSchedules as $schedule)
                        <tr class="{{ $schedule->is_active ? '' : 'bg-gray-50' }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $schedule->name }}</div>
                                @if($schedule->description)
                                <div class="text-sm text-gray-500">{{ $schedule->description }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <i class="fas fa-calendar-day mr-1"></i>{{ ucfirst($schedule->day_of_week) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="font-mono">{{ $schedule->max_check_in_time->format('H:i') }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($schedule->affected_roles && count($schedule->affected_roles) > 0)
                                    @foreach($schedule->affected_roles as $role)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 mr-1">
                                            {{ ucfirst($role) }}
                                        </span>
                                    @endforeach
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                        Semua Role
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($schedule->start_date || $schedule->end_date)
                                    <div>
                                        @if($schedule->start_date)
                                            <div>Mulai: {{ $schedule->start_date->format('d/m/Y') }}</div>
                                        @endif
                                        @if($schedule->end_date)
                                            <div>Selesai: {{ $schedule->end_date->format('d/m/Y') }}</div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-400">Selamanya</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($schedule->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-times-circle mr-1"></i>Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="{{ route('admin.special-schedules.edit', $schedule) }}" class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.special-schedules.toggle', $schedule) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-yellow-600 hover:text-yellow-900">
                                            <i class="fas fa-toggle-{{ $schedule->is_active ? 'on' : 'off' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.special-schedules.destroy', $schedule) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal khusus ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-8">
                <i class="fas fa-calendar-alt text-4xl text-gray-400 mb-4"></i>
                <p class="text-gray-500">Belum ada jadwal khusus</p>
                <a href="{{ route('admin.special-schedules.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>Tambah Jadwal Khusus Pertama
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

