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

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-school text-blue-600 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Sekolah</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $schools->count() }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-users text-green-600 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total User</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $schools->sum('users_count') }}</dd>
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
                            <dt class="text-sm font-medium text-gray-500 truncate">Sekolah Aktif</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $schools->where('is_active', true)->count() }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
@endsection
