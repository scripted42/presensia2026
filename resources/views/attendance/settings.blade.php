<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Absensi - Presensia</title>
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
                        <img src="{{ asset('assets/images/logo/presensia-logo.png') }}" alt="Presensia" class="h-4 w-auto" />
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="{{ route('dashboard') }}" class="text-sm text-gray-700 hover:text-gray-900">
                        <i class="fas fa-home mr-1"></i>Dashboard
                    </a>
                    <a href="{{ route('attendance.index') }}" class="text-sm text-gray-700 hover:text-gray-900">
                        <i class="fas fa-calendar-check mr-1"></i>Absensi
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
                        <h1 class="text-2xl font-bold text-gray-900">Pengaturan Absensi</h1>
                        <p class="text-gray-600 mt-1">Konfigurasi jam absensi, lokasi, dan parameter QR Code</p>
                    </div>
                    <a href="{{ route('attendance.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
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

        <!-- Settings Form -->
        <div class="bg-white shadow rounded-lg">
            <form method="POST" action="{{ route('settings.attendance') }}">
                @csrf
                
                <!-- Time Settings -->
                <div class="px-4 py-5 sm:p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Pengaturan Jam</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="check_in_time" class="block text-sm font-medium text-gray-700">Jam Masuk</label>
                            <input type="time" name="check_in_time" id="check_in_time" 
                                   value="{{ old('check_in_time', $settings->check_in_time ?? '07:00') }}" required
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            @error('check_in_time')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="check_out_time" class="block text-sm font-medium text-gray-700">Jam Keluar</label>
                            <input type="time" name="check_out_time" id="check_out_time" 
                                   value="{{ old('check_out_time', $settings->check_out_time ?? '15:00') }}" required
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            @error('check_out_time')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Location Settings -->
                <div class="px-4 py-5 sm:p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Pengaturan Lokasi</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="location_name" class="block text-sm font-medium text-gray-700">Nama Lokasi</label>
                            <input type="text" name="location_name" id="location_name" 
                                   value="{{ old('location_name', $settings->location_name ?? 'Sekolah') }}" required
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            @error('location_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="radius_meters" class="block text-sm font-medium text-gray-700">Radius (meter)</label>
                            <input type="number" name="radius_meters" id="radius_meters" min="10" max="1000"
                                   value="{{ old('radius_meters', $settings->radius_meters ?? 100) }}" required
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            @error('radius_meters')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <div>
                            <label for="location_latitude" class="block text-sm font-medium text-gray-700">Latitude</label>
                            <input type="number" name="location_latitude" id="location_latitude" step="any"
                                   value="{{ old('location_latitude', $settings->location_latitude ?? '-6.200000') }}" required
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            @error('location_latitude')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="location_longitude" class="block text-sm font-medium text-gray-700">Longitude</label>
                            <input type="number" name="location_longitude" id="location_longitude" step="any"
                                   value="{{ old('location_longitude', $settings->location_longitude ?? '106.816666') }}" required
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            @error('location_longitude')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="button" id="getCurrentLocation" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-map-marker-alt mr-2"></i>Gunakan Lokasi Saat Ini
                        </button>
                    </div>
                </div>

                <!-- QR Code Settings -->
                <div class="px-4 py-5 sm:p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Pengaturan QR Code</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="qr_code_duration" class="block text-sm font-medium text-gray-700">Durasi QR Code (detik)</label>
                            <input type="number" name="qr_code_duration" id="qr_code_duration" min="5" max="60"
                                   value="{{ old('qr_code_duration', $settings->qr_code_duration ?? 10) }}" required
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            @error('qr_code_duration')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Additional Settings -->
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Pengaturan Tambahan</h3>
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <input type="checkbox" name="require_photo" id="require_photo" value="1"
                                   {{ old('require_photo', $settings->require_photo ?? true) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="require_photo" class="ml-2 block text-sm text-gray-900">
                                Wajib foto selfie saat absensi
                            </label>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" name="require_location" id="require_location" value="1"
                                   {{ old('require_location', $settings->require_location ?? true) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="require_location" class="ml-2 block text-sm text-gray-900">
                                Wajib lokasi GPS saat absensi
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="px-4 py-5 sm:p-6 bg-gray-50">
                    <div class="flex justify-end">
                        <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors font-medium">
                            <i class="fas fa-save mr-2"></i>Simpan Pengaturan
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Current Settings Display -->
        @if($settings)
        <div class="bg-white shadow rounded-lg mt-6">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Pengaturan Saat Ini</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-medium text-gray-700">Jam Absensi</h4>
                        <p class="text-sm text-gray-600">Masuk: {{ $settings->check_in_time }}</p>
                        <p class="text-sm text-gray-600">Keluar: {{ $settings->check_out_time }}</p>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-700">Lokasi</h4>
                        <p class="text-sm text-gray-600">{{ $settings->location_name }}</p>
                        <p class="text-sm text-gray-600">Radius: {{ $settings->radius_meters }}m</p>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-700">Koordinat</h4>
                        <p class="text-sm text-gray-600">Lat: {{ $settings->location_latitude }}</p>
                        <p class="text-sm text-gray-600">Lng: {{ $settings->location_longitude }}</p>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-700">QR Code</h4>
                        <p class="text-sm text-gray-600">Durasi: {{ $settings->qr_code_duration }} detik</p>
                        <p class="text-sm text-gray-600">Foto: {{ $settings->require_photo ? 'Wajib' : 'Opsional' }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <script>
        // Get current location
        document.getElementById('getCurrentLocation').addEventListener('click', function() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        document.getElementById('location_latitude').value = position.coords.latitude;
                        document.getElementById('location_longitude').value = position.coords.longitude;
                        document.getElementById('location_name').value = 'Lokasi: ' + position.coords.latitude + ', ' + position.coords.longitude;
                    },
                    function(error) {
                        alert('Tidak dapat mendapatkan lokasi: ' + error.message);
                    }
                );
            } else {
                alert('Browser tidak mendukung geolocation');
            }
        });
    </script>
</body>
</html>


