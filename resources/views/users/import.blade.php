<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Data User - Presensia</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <i class="fas fa-graduation-cap text-blue-600 text-2xl mr-3"></i>
                        <span class="text-xl font-bold text-gray-900">Presensia</span>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="{{ route('dashboard') }}" class="text-sm text-gray-700 hover:text-gray-900">
                        <i class="fas fa-home mr-1"></i>Dashboard
                    </a>
                    <a href="{{ route('users.index') }}" class="text-sm text-gray-700 hover:text-gray-900">
                        <i class="fas fa-users mr-1"></i>Users
                    </a>
                    <span class="text-sm text-gray-700">Selamat datang, <strong>{{ auth()->user()->name }}</strong></span>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-gray-700 hover:text-gray-900">
                            <i class="fas fa-sign-out-alt mr-1"></i>Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Import Data User</h1>
                        <p class="text-gray-600 mt-1">Upload file Excel/CSV untuk menambahkan data user secara massal</p>
                    </div>
                    <a href="{{ route('users.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                    </a>
                </div>
            </div>
        </div>

        <!-- Success Message -->
        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
        @endif

        <!-- Import Form -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Upload File</h2>
                <form method="POST" action="{{ route('users.import') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="type" value="{{ $type ?? 'employee' }}">
                    
                    <div class="mb-6">
                        <label for="file" class="block text-sm font-medium text-gray-700">Pilih File (CSV)</label>
                        <input type="file" name="file" id="file" accept=".csv,.txt" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-sm text-gray-500">Gunakan template CSV sesuai menu (Pegawai/Siswa).</p>
                        @error('file')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <div class="mt-2">
                            <a href="{{ route('users.import-template', ['type' => $type ?? 'employee']) }}" class="text-blue-600 hover:underline">
                                Download Template CSV {{ ($type ?? 'employee') === 'employee' ? 'Pegawai' : 'Siswa' }}
                            </a>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('users.index', ['type' => $type ?? 'employee']) }}" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                            Batal
                        </a>
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-eye mr-2"></i>Preview
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Template Download -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Download Template</h2>
                <p class="text-gray-600 mb-4">Download template CSV untuk memastikan format data yang benar.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <a href="{{ route('users.import-template', ['type' => 'employee']) }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <i class="fas fa-file-excel text-green-600 text-2xl mr-3"></i>
                        <div>
                            <h3 class="font-medium text-gray-900">Template Pegawai</h3>
                            <p class="text-sm text-gray-500">Format untuk data pegawai</p>
                        </div>
                    </a>
                    
                    <a href="{{ route('users.import-template', ['type' => 'student']) }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <i class="fas fa-file-excel text-green-600 text-2xl mr-3"></i>
                        <div>
                            <h3 class="font-medium text-gray-900">Template Siswa</h3>
                            <p class="text-sm text-gray-500">Format untuk data siswa</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Instructions -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Petunjuk Import</h2>
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center">
                                <span class="text-blue-600 font-medium text-sm">1</span>
                            </div>
                        </div>
                        <div class="ml-3">
                            <h3 class="font-medium text-gray-900">Download Template</h3>
                            <p class="text-sm text-gray-600">Download template Excel sesuai dengan tipe data yang akan diimport (pegawai atau siswa).</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center">
                                <span class="text-blue-600 font-medium text-sm">2</span>
                            </div>
                        </div>
                        <div class="ml-3">
                            <h3 class="font-medium text-gray-900">Isi Data</h3>
                            <p class="text-sm text-gray-600">Isi template dengan data yang sesuai. Pastikan format data sudah benar.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center">
                                <span class="text-blue-600 font-medium text-sm">3</span>
                            </div>
                        </div>
                        <div class="ml-3">
                            <h3 class="font-medium text-gray-900">Upload File</h3>
                            <p class="text-sm text-gray-600">Upload file yang sudah diisi ke sistem. Sistem akan memvalidasi data sebelum disimpan.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

