@extends('layouts.app')

@section('title', 'Manajemen Role - Presensia')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Manajemen Role</h1>
                    <p class="text-gray-600 mt-1">Kelola role dan permission untuk kontrol akses pengguna</p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('admin.permissions.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-key mr-2"></i>
                        Kelola Permission
                    </a>
                    <a href="{{ route('admin.roles.create') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-plus mr-2"></i>
                        Tambah Role
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Roles List -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            @if($roles->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($roles as $role)
                        <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900">{{ ucfirst($role->name) }}</h3>
                                    <p class="text-sm text-gray-500">{{ $role->permissions->count() }} permissions</p>
                                </div>
                                <div class="flex space-x-2">
                                    <a href="{{ route('admin.roles.show', $role) }}" 
                                       class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.roles.edit', $role) }}" 
                                       class="text-yellow-600 hover:text-yellow-800">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if(!in_array($role->name, ['admin', 'headmaster', 'teacher', 'tu', 'bk', 'kesiswaan', 'student']))
                                        <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="inline" 
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus role ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Permissions -->
                            <div class="mb-4">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Permissions:</h4>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($role->permissions->take(5) as $permission)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $permission->name }}
                                        </span>
                                    @endforeach
                                    @if($role->permissions->count() > 5)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            +{{ $role->permissions->count() - 5 }} more
                                        </span>
                                    @endif
                                </div>
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
                <div class="text-center py-12">
                    <i class="fas fa-shield-alt text-4xl text-gray-400 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada role</h3>
                    <p class="text-gray-500">Belum ada role yang dibuat.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

