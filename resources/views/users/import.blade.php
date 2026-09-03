@extends('layouts.app')

@section('title', 'Import Data User - Presensia')

@section('content')
<div>
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Import Data User</h1>
                        <p class="text-xs text-gray-500 mt-1">Upload file Excel/CSV untuk menambahkan data user secara massal</p>
                    </div>
                    <a href="{{ route('users.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors text-sm font-medium">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                    </a>
                </div>
            </div>
        </div>

        <!-- Success Message -->
        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 text-sm">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
        @endif

        <!-- Import Form -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Upload File</h2>
                <form method="POST" action="{{ route('users.import') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="type" value="{{ $type ?? 'employee' }}">
                    
                    <div class="mb-6">
                        <label for="file" class="block text-sm text-gray-500 font-normal">Pilih File (CSV)</label>
                        <input type="file" name="file" id="file" accept=".csv,.txt" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <p class="mt-1 text-xs text-gray-500">Gunakan template CSV sesuai menu (Pegawai/Siswa).</p>
                        @error('file')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <div class="mt-2">
                            <a href="{{ route('users.import-template', ['type' => $type ?? 'employee']) }}" class="text-xs text-blue-600 hover:underline">
                                Download Template CSV {{ ($type ?? 'employee') === 'employee' ? 'Pegawai' : 'Siswa' }}
                            </a>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('users.index', ['type' => $type ?? 'employee']) }}" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition-colors text-sm font-medium">
                            Batal
                        </a>
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                            <i class="fas fa-eye mr-2"></i>Preview
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Template Download -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Download Template</h2>
                <p class="text-xs text-gray-500 mb-4">Download template CSV untuk memastikan format data yang benar.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <a href="{{ route('users.import-template', ['type' => 'employee']) }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <i class="fas fa-file-excel text-green-600 text-2xl mr-3"></i>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Template Pegawai</p>
                            <p class="text-xs text-gray-500">Format untuk data pegawai</p>
                        </div>
                    </a>
                    
                    <a href="{{ route('users.import-template', ['type' => 'student']) }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <i class="fas fa-file-excel text-green-600 text-2xl mr-3"></i>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Template Siswa</p>
                            <p class="text-xs text-gray-500">Format untuk data siswa</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Instructions -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Petunjuk Import</h2>
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center">
                                <span class="text-blue-600 font-medium text-sm">1</span>
                            </div>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">Download Template</p>
                            <p class="text-xs text-gray-500">Download template Excel sesuai dengan tipe data yang akan diimport (pegawai atau siswa).</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center">
                                <span class="text-blue-600 font-medium text-sm">2</span>
                            </div>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">Isi Data</p>
                            <p class="text-xs text-gray-500">Isi template dengan data yang sesuai. Pastikan format data sudah benar.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center">
                                <span class="text-blue-600 font-medium text-sm">3</span>
                            </div>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">Upload File</p>
                            <p class="text-xs text-gray-500">Upload file yang sudah diisi ke sistem. Sistem akan memvalidasi data sebelum disimpan.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

