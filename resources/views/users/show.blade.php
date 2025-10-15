@extends('layouts.app')

@section('title', 'Detail User - Presensia')

@section('content')
    <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Detail User</h1>
                <p class="text-gray-600 mt-1">Informasi lengkap pengguna</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('users.index', ['type' => $user->user_type]) }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                    Kembali
                </a>
                <a href="{{ route('users.edit', $user) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    Edit
                </a>
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-6 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="col-span-1">
                <img class="w-40 h-40 rounded-full object-cover border" src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=3B82F6&color=fff" alt="{{ $user->name }}">
                <div class="mt-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->user_type === 'employee' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800' }}">
                        {{ $user->user_type === 'employee' ? 'Pegawai' : 'Siswa' }}
                    </span>
                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ $user->roles->first()->display_name ?? $user->roles->first()->name ?? 'No Role' }}
                    </span>
                </div>
            </div>
            <div class="col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <div class="text-sm text-gray-500">Nama</div>
                    <div class="text-base text-gray-900 font-medium">{{ $user->name }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Email</div>
                    <div class="text-base text-gray-900">{{ $user->email }}</div>
                </div>
                @if($user->user_type === 'employee')
                    <div>
                        <div class="text-sm text-gray-500">NIK/NIP</div>
                        <div class="text-base text-gray-900">{{ $user->nik ?? $user->employeeProfile?->nip ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">NUPTK</div>
                        <div class="text-base text-gray-900">{{ $user->employeeProfile?->nuptk ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Jenis PTK</div>
                        <div class="text-base text-gray-900">{{ $user->employeeProfile?->ptk_type ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Status Kepegawaian</div>
                        <div class="text-base text-gray-900">{{ $user->employeeProfile?->employment_status ?? '-' }}</div>
                    </div>
                    <div class="md:col-span-2">
                        <div class="text-sm text-gray-500">Alamat</div>
                        <div class="text-base text-gray-900">{{ $user->employeeProfile?->address_line ?? $user->address ?? '-' }}</div>
                    </div>
                @else
                    <div>
                        <div class="text-sm text-gray-500">NIS</div>
                        <div class="text-base text-gray-900">{{ $user->studentProfile?->nis ?? $user->nis ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">NISN</div>
                        <div class="text-base text-gray-900">{{ $user->studentProfile?->nisn ?? $user->nisn ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Nama Ayah</div>
                        <div class="text-base text-gray-900">{{ $user->studentProfile?->father_name ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Nama Ibu</div>
                        <div class="text-base text-gray-900">{{ $user->studentProfile?->mother_name ?? '-' }}</div>
                    </div>
                    <div class="md:col-span-2">
                        <div class="text-sm text-gray-500">Alamat</div>
                        <div class="text-base text-gray-900">{{ $user->studentProfile?->address_line ?? $user->address ?? '-' }}</div>
                    </div>
                @endif
                <div>
                    <div class="text-sm text-gray-500">No. Telepon</div>
                    <div class="text-base text-gray-900">{{ $user->phone ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Tanggal Lahir</div>
                    <div class="text-base text-gray-900">{{ $user->birth_date?->format('d-m-Y') ?? '-' }}</div>
                </div>
                <div class="md:col-span-2">
                    <div class="text-sm text-gray-500">Alamat</div>
                    <div class="text-base text-gray-900">{{ $user->address ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection


