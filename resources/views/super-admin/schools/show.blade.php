@extends('layouts.app')

@section('title', 'Detail Sekolah - Super Admin')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Detail Sekolah: {{ $school->name }}</h1>
                    <p class="text-gray-600 mt-1">Informasi lengkap tentang sekolah dan pengaturannya</p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('super-admin.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali
                    </a>
                    <a href="{{ route('super-admin.schools.edit', $school) }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Sekolah
                    </a>
                    <form action="{{ route('super-admin.schools.destroy', $school) }}" method="POST" class="inline" 
                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus sekolah {{ $school->name }}? Tindakan ini tidak dapat dibatalkan!')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <i class="fas fa-trash mr-2"></i>
                            Hapus Sekolah
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Information Alert -->
    @if(session('admin_info'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800">Admin Sekolah Berhasil Dibuat!</h3>
                    <div class="mt-2 text-sm text-green-700">
                        <p><strong>Email Admin:</strong> {{ session('admin_info.email') }}</p>
                        <p><strong>Password:</strong> {{ session('admin_info.password') }}</p>
                        <p><strong>Nama:</strong> {{ session('admin_info.name') }}</p>
                        <p class="mt-2 font-medium">Simpan informasi ini untuk login sebagai admin sekolah!</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- School Information -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Sekolah</h3>
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Nama Sekolah</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $school->name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                    <dd class="mt-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $school->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $school->is_active ? 'Aktif' : 'Tidak Aktif' }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Telepon</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $school->phone ?? 'Tidak ada' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $school->email ?? 'Tidak ada' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Website</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($school->website)
                            <a href="{{ $school->website }}" target="_blank" class="text-blue-600 hover:text-blue-800">{{ $school->website }}</a>
                        @else
                            Tidak ada
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Dibuat</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $school->created_at->format('d F Y H:i') }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Alamat</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $school->address ?? 'Tidak ada' }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Tenant Settings -->
    @if($school->tenantSettings)
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Pengaturan Aplikasi</h3>
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nama Aplikasi</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $school->tenantSettings->app_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $school->tenantSettings->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $school->tenantSettings->is_active ? 'Aktif' : 'Tidak Aktif' }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Warna Primer</dt>
                        <dd class="mt-1 flex items-center">
                            <div class="w-6 h-6 rounded border mr-2" style="background-color: {{ $school->tenantSettings->primary_color }}"></div>
                            <span class="text-sm text-gray-900">{{ $school->tenantSettings->primary_color }}</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Warna Sekunder</dt>
                        <dd class="mt-1 flex items-center">
                            <div class="w-6 h-6 rounded border mr-2" style="background-color: {{ $school->tenantSettings->secondary_color }}"></div>
                            <span class="text-sm text-gray-900">{{ $school->tenantSettings->secondary_color }}</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Warna Aksen</dt>
                        <dd class="mt-1 flex items-center">
                            <div class="w-6 h-6 rounded border mr-2" style="background-color: {{ $school->tenantSettings->accent_color }}"></div>
                            <span class="text-sm text-gray-900">{{ $school->tenantSettings->accent_color }}</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Logo</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if($school->tenantSettings->app_logo)
                                <a href="{{ Storage::url($school->tenantSettings->app_logo) }}" target="_blank" class="text-blue-600 hover:text-blue-800">Lihat Logo</a>
                            @else
                                Tidak ada
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    @endif

    <!-- Features -->
    @if($school->tenantSettings && $school->tenantSettings->features)
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Fitur yang Diaktifkan</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($school->tenantSettings->features as $feature => $enabled)
                        <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg {{ $enabled ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200' }}">
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">{{ ucwords(str_replace('_', ' ', $feature)) }}</h4>
                            </div>
                            <div class="flex items-center">
                                @if($enabled)
                                    <i class="fas fa-check-circle text-green-600"></i>
                                @else
                                    <i class="fas fa-times-circle text-gray-400"></i>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Users -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">User di Sekolah ini</h3>
            @if($school->users->count() > 0)
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($school->users as $user)
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @foreach($user->roles as $role)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 mr-1">
                                                {{ ucfirst($role->name) }}
                                            </span>
                                        @endforeach
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
                    <p class="text-gray-500">Belum ada user yang terdaftar di sekolah ini.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Classes -->
    @if($school->classes->count() > 0)
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Kelas di Sekolah ini</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($school->classes as $class)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-900">{{ $class->name }}</h4>
                            <p class="text-xs text-gray-500">{{ $class->level }} - {{ $class->major }}</p>
                            <p class="text-xs text-gray-500">Tahun: {{ $class->year }}</p>
                            @if($class->teacher)
                                <p class="text-xs text-gray-500">Wali: {{ $class->teacher->name }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
