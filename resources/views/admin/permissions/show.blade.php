@extends('layouts.app')

@section('title', 'Detail Permission - Presensia')

@section('content')
<div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Detail Permission: {{ $permission->name }}</h1>
                    <p class="text-gray-600 mt-1">Informasi lengkap tentang permission dan role yang memilikinya</p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('admin.permissions.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali
                    </a>
                    <a href="{{ route('admin.permissions.edit', $permission) }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Permission
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Permission Information -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Permission</h3>
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Nama Permission</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $permission->name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Guard Name</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $permission->guard_name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Jumlah Role</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $permission->roles->count() }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Dibuat</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $permission->created_at->format('d F Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Diperbarui</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $permission->updated_at->format('d F Y H:i') }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Roles with this permission -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Role yang Memiliki Permission ini</h3>
            @if($permission->roles->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($permission->roles as $role)
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900">{{ ucfirst($role->name) }}</h4>
                                    <p class="text-xs text-gray-500">{{ $role->permissions->count() }} permissions</p>
                                </div>
                                <a href="{{ route('admin.roles.show', $role) }}" 
                                   class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                            
                            <!-- Users Count -->
                            <div class="text-sm text-gray-500">
                                <i class="fas fa-users mr-1"></i>
                                {{ $role->users->count() }} users
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-shield-alt text-4xl text-gray-400 mb-4"></i>
                    <h4 class="text-lg font-medium text-gray-900 mb-2">Tidak ada role</h4>
                    <p class="text-gray-500">Permission ini belum diberikan ke role apapun.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

