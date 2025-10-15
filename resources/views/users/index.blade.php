@extends('layouts.app')

@section('title', 'Manajemen User - Presensia')

@section('content')
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">
                            @if($type === 'employee')
                                Manajemen Pegawai
                            @else
                                Manajemen Siswa
                            @endif
                        </h1>
                        <p class="text-gray-600 mt-1">
                            @if($type === 'employee')
                                Kelola data pegawai sekolah
                            @else
                                Kelola data siswa sekolah
                            @endif
                        </p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('users.import', ['type' => $type]) }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                            <i class="fas fa-upload mr-2"></i>Import Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>

        

        <!-- Success Message -->
        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
        @endif
        @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <i class="fas fa-exclamation-triangle mr-2"></i>Terjadi kesalahan saat menyimpan data.
        </div>
        @endif

        <!-- Users Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-4 py-5 sm:p-6">
                <!-- Page size + Search -->
                <form method="GET" action="{{ route('users.index') }}" class="mb-4 flex flex-col md:flex-row md:items-center md:space-x-4 space-y-2 md:space-y-0">
                    <input type="hidden" name="type" value="{{ $type }}">
                    <div class="flex items-center space-x-2">
                        <label for="per_page" class="text-sm text-gray-600">Tampilkan</label>
                        <select name="per_page" id="per_page" onchange="this.form.submit()"
                                class="border-gray-300 rounded-md text-sm">
                            <option value="10" {{ ($perPageParam ?? '10') == '10' ? 'selected' : '' }}>10</option>
                            <option value="25" {{ ($perPageParam ?? '') == '25' ? 'selected' : '' }}>25</option>
                            <option value="50" {{ ($perPageParam ?? '') == '50' ? 'selected' : '' }}>50</option>
                            <option value="all" {{ ($perPageParam ?? '') == 'all' ? 'selected' : '' }}>All</option>
                        </select>
                        <span class="text-sm text-gray-600">data</span>
                    </div>
                    <div class="flex items-center space-x-2 md:ml-auto">
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama, email, NIK/NIS"
                               class="border-gray-300 rounded-md text-sm w-64" />
                        <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded-md text-sm">Cari</button>
                    </div>
                </form>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    @if($type === 'employee')
                                        NIK/NIP
                                    @else
                                        NIS
                                    @endif
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($users as $user)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($type === 'employee')
                                        {{ $user->nik ?? '-' }}
                                    @else
                                        {{ $user->nis ?? '-' }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-full" src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=3B82F6&color=fff" alt="{{ $user->name }}">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $user->roles->first()->display_name ?? $user->roles->first()->name ?? 'No Role' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->user_type === 'employee' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800' }}">
                                        {{ $user->user_type === 'employee' ? 'Pegawai' : 'Siswa' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $user->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('users.show', $user) }}" class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route('users.destroy', $user) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    <i class="fas fa-users text-4xl mb-2"></i>
                                    <p>Tidak ada user ditemukan.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($users->hasPages())
                <div class="mt-4">
                    {{ $users->links() }}
                </div>
                @endif
            </div>
        </div>
@endsection
