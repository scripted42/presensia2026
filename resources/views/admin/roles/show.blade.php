@extends('layouts.app')

@section('title', 'Detail Role - Presensia')

@section('content')
<div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Detail Role: {{ ucfirst($role->name) }}</h1>
                    <p class="text-gray-600 mt-1">Informasi lengkap tentang role dan permission</p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('admin.roles.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali
                    </a>
                    <a href="{{ route('admin.roles.edit', $role) }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Role
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Role Information -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Role</h3>
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Nama Role</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $role->name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Guard Name</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $role->guard_name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Jumlah Permission</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $role->permissions->count() }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Jumlah User</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $role->users->count() }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Dibuat</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $role->created_at->format('d F Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Diperbarui</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $role->updated_at->format('d F Y H:i') }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Permissions -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Permissions</h3>
            @if($role->permissions->count() > 0)
                @foreach($permissions as $group => $groupPermissions)
                    @php
                        $rolePermissions = $role->permissions->whereIn('id', $groupPermissions->pluck('id'));
                    @endphp
                    @if($rolePermissions->count() > 0)
                        <div class="mb-6">
                            <h4 class="text-md font-medium text-gray-800 mb-3">{{ ucfirst($group) }}</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($rolePermissions as $permission)
                                    <div class="flex items-center p-3 border border-gray-200 rounded-lg bg-green-50">
                                        <i class="fas fa-check-circle text-green-600 mr-3"></i>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $permission->name }}</div>
                                            <div class="text-xs text-gray-500">{{ ucwords(str_replace('-', ' ', $permission->name)) }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            @else
                <div class="text-center py-8">
                    <i class="fas fa-key text-4xl text-gray-400 mb-4"></i>
                    <h4 class="text-lg font-medium text-gray-900 mb-2">Tidak ada permission</h4>
                    <p class="text-gray-500">Role ini belum memiliki permission apapun.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Users with this role -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Users dengan Role ini</h3>
            @if($role->users->count() > 0)
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($role->users as $user)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                    <span class="text-sm font-medium text-gray-700">{{ substr($user->name, 0, 1) }}</span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $user->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->user_type === 'employee' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                            {{ $user->user_type === 'employee' ? 'Pegawai' : 'Siswa' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $user->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-users text-4xl text-gray-400 mb-4"></i>
                    <h4 class="text-lg font-medium text-gray-900 mb-2">Tidak ada user</h4>
                    <p class="text-gray-500">Belum ada user yang memiliki role ini.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection


