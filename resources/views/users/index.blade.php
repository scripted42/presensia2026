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
                        <p class="text-xs text-gray-500 mt-1">
                            @if($type === 'employee')
                                Kelola data pegawai sekolah
                            @else
                                Kelola data siswa sekolah
                            @endif
                        </p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('users.export', array_merge(['type' => $type], request()->except('page'))) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium flex items-center">
                            <i class="fas fa-download mr-2"></i>Export Excel
                        </a>
                        <a href="{{ route('users.import', ['type' => $type]) }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors text-sm font-medium flex items-center">
                            <i class="fas fa-upload mr-2"></i>Import Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 text-sm flex items-center">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-sm flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
        @endif
        @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-sm">
            <i class="fas fa-exclamation-triangle mr-2"></i>Terjadi kesalahan saat menyimpan data.
        </div>
        @endif

        <!-- Summary Cards -->
        <x-summary-cards :cards="$summaryCards ?? []" />

        <!-- Filter Bar Component -->
        <x-filter-bar 
            :action="route('users.index')" 
            :reset-url="route('users.index', ['type' => $type])" 
            :active-filters="$activeFilters"
            :hidden-inputs="['type' => $type]"
            :title="$type === 'employee' ? 'Filter Data Pegawai' : 'Filter Data Siswa'"
            badge-class="badge badge-info badge-sm text-xs font-medium"
        >
            {{-- 1. Search Input --}}
            <div>
                <label for="q" class="block text-sm text-gray-500 font-normal">Cari Data</label>
                <input type="text" name="q" id="q" value="{{ request('q') }}" 
                       placeholder="{{ $type === 'employee' ? 'Nama, NIK, email...' : 'Nama, NIS, NISN...' }}"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
            </div>

            @if($type === 'student')
                {{-- 2. Tingkat / Level --}}
                <div>
                    <label for="level" class="block text-sm text-gray-500 font-normal">Tingkat</label>
                    <select name="level" id="level" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <option value="">Semua Tingkat</option>
                        @foreach($levels as $lvl)
                            <option value="{{ $lvl }}" {{ request('level') == $lvl ? 'selected' : '' }}>Tingkat {{ $lvl }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 3. Kelas / Rombel --}}
                <div>
                    <label for="class_id" class="block text-sm text-gray-500 font-normal">Kelas</label>
                    <select name="class_id" id="class_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <option value="">Semua Kelas</option>
                        <option value="none" {{ request('class_id') === 'none' ? 'selected' : '' }}>-- Tanpa Kelas --</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 4. Gender --}}
                <div>
                    <label for="gender" class="block text-sm text-gray-500 font-normal">Jenis Kelamin</label>
                    <select name="gender" id="gender" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <option value="">Semua Gender</option>
                        <option value="L" {{ request('gender') === 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                        <option value="P" {{ request('gender') === 'P' ? 'selected' : '' }}>Perempuan (P)</option>
                    </select>
                </div>
            @else
                {{-- 2. Role / Jabatan --}}
                <div>
                    <label for="role" class="block text-sm text-gray-500 font-normal">Jabatan</label>
                    <select name="role" id="role" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <option value="">Semua Role</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->name }}" {{ request('role') === $r->name ? 'selected' : '' }}>
                                {{ $r->display_name ?? $r->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- 3. Status Kepegawaian --}}
                <div>
                    <label for="employment_status" class="block text-sm text-gray-500 font-normal">Status Pegawai</label>
                    <select name="employment_status" id="employment_status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <option value="">Semua Status</option>
                        @foreach($employmentStatuses as $es)
                            <option value="{{ $es }}" {{ request('employment_status') === $es ? 'selected' : '' }}>{{ $es }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 4. Gender --}}
                <div>
                    <label for="gender" class="block text-sm text-gray-500 font-normal">Jenis Kelamin</label>
                    <select name="gender" id="gender" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <option value="">Semua Gender</option>
                        <option value="L" {{ request('gender') === 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                        <option value="P" {{ request('gender') === 'P' ? 'selected' : '' }}>Perempuan (P)</option>
                    </select>
                </div>
            @endif

            {{-- 5. Status Akun --}}
            <div>
                <label for="is_active" class="block text-sm text-gray-500 font-normal">Status Akun</label>
                <select name="is_active" id="is_active" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                    <option value="">Semua Status</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Tidak Aktif</option>
                </select>
            </div>

            {{-- 6. Per Page --}}
            <div>
                <label for="per_page" class="block text-sm text-gray-500 font-normal">Tampilkan</label>
                <select name="per_page" id="per_page" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                    <option value="10" {{ ($perPageParam ?? '10') == '10' ? 'selected' : '' }}>10 data</option>
                    <option value="25" {{ ($perPageParam ?? '') == '25' ? 'selected' : '' }}>25 data</option>
                    <option value="50" {{ ($perPageParam ?? '') == '50' ? 'selected' : '' }}>50 data</option>
                    <option value="all" {{ ($perPageParam ?? '') == 'all' ? 'selected' : '' }}>Semua data</option>
                </select>
            </div>
        </x-filter-bar>

        <!-- Users Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-4 py-5 sm:p-6">
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ $type === 'student' ? 'Kelas' : 'Type' }}
                                </th>
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
                                            <p class="text-sm font-medium text-gray-900 leading-tight">{{ $user->name }}</p>
                                            <p class="text-xs text-gray-500 mt-0.5">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="badge badge-info badge-sm text-xs font-medium">
                                        {{ $user->roles->first()->display_name ?? $user->roles->first()->name ?? 'No Role' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($type === 'student')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $user->studentClasses->first()->name ?? 'Tanpa Kelas' }}
                                        </span>
                                    @else
                                        <span class="badge {{ $user->user_type === 'employee' ? 'badge-success' : 'badge-neutral' }} badge-sm text-xs font-medium">
                                            {{ $user->user_type === 'employee' ? 'Pegawai' : 'Siswa' }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="badge {{ $user->is_active ? 'badge-success' : 'badge-error' }} badge-sm text-xs font-medium">
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
