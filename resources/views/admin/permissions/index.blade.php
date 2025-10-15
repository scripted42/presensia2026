@extends('layouts.app')

@section('title', 'Manajemen Permission - Presensia')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Manajemen Permission</h1>
                    <p class="text-gray-600 mt-1">Kelola permission untuk kontrol akses yang lebih detail</p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('admin.roles.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-shield-alt mr-2"></i>
                        Kelola Role
                    </a>
                    <a href="{{ route('admin.permissions.create') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-plus mr-2"></i>
                        Tambah Permission
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Permissions List -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            @if($permissions->count() > 0)
                @foreach($permissions as $group => $groupPermissions)
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ ucfirst($group) }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($groupPermissions as $permission)
                                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-900">{{ $permission->name }}</h4>
                                            <p class="text-xs text-gray-500">{{ ucwords(str_replace('-', ' ', $permission->name)) }}</p>
                                        </div>
                                        <div class="flex space-x-2">
                                            <a href="{{ route('admin.permissions.show', $permission) }}" 
                                               class="text-blue-600 hover:text-blue-800">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.permissions.edit', $permission) }}" 
                                               class="text-yellow-600 hover:text-yellow-800">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if(!in_array($permission->name, [
                                                'view-dashboard', 'view-users', 'create-users', 'edit-users', 'delete-users',
                                                'view-students', 'create-students', 'edit-students', 'delete-students',
                                                'view-classes', 'create-classes', 'edit-classes', 'delete-classes',
                                                'view-attendance', 'create-attendance', 'edit-attendance', 'delete-attendance',
                                                'view-own-attendance', 'create-own-attendance',
                                                'view-student-attendance', 'create-student-attendance',
                                                'view-reports', 'export-reports',
                                                'view-leaves', 'create-leaves', 'edit-leaves', 'approve-leaves',
                                                'view-own-leaves', 'create-own-leaves',
                                                'view-settings', 'edit-settings',
                                                'bulk-import-users', 'bulk-import-students'
                                            ]))
                                                <form action="{{ route('admin.permissions.destroy', $permission) }}" method="POST" class="inline" 
                                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus permission ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-800">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <!-- Roles -->
                                    <div class="mb-3">
                                        <h5 class="text-xs font-medium text-gray-700 mb-1">Roles:</h5>
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($permission->roles->take(3) as $role)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    {{ $role->name }}
                                                </span>
                                            @endforeach
                                            @if($permission->roles->count() > 3)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    +{{ $permission->roles->count() - 3 }} more
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center py-12">
                    <i class="fas fa-key text-4xl text-gray-400 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada permission</h3>
                    <p class="text-gray-500">Belum ada permission yang dibuat.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

