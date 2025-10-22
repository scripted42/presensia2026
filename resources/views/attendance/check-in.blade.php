@extends('layouts.app')

@section('title', 'Check In - Presensia')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Check In</h1>
            <p class="mt-2 text-gray-600">Lakukan absensi masuk untuk hari ini</p>
        </div>

        <!-- Check In Form -->
        <div class="bg-white shadow rounded-lg p-6">
            <form method="POST" action="{{ route('attendance.checkin') }}">
                @csrf
                
                <!-- Current Time Display -->
                <div class="mb-6 text-center">
                    <div class="text-4xl font-bold text-blue-600" id="current-time"></div>
                    <div class="text-lg text-gray-600" id="current-date"></div>
                </div>

                <!-- QR Code Section -->
                <div class="mb-6">
                    <label for="qr_code" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-qrcode mr-2"></i>Scan QR Code
                    </label>
                    <input type="text" id="qr_code" name="qr_code" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Scan QR Code atau masukkan manual">
                </div>

                <!-- Location Section -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-map-marker-alt mr-2"></i>Lokasi Saat Ini
                    </label>
                    <div class="flex items-center space-x-2">
                        <input type="text" id="location-input" class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Mengambil lokasi..." readonly>
                        <button type="button" id="get-location-btn" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <i class="fas fa-crosshairs"></i>
                        </button>
                    </div>
                    <div class="mt-2 text-sm text-gray-500" id="location-status"></div>
                </div>

                <!-- GPS Coordinates (Hidden) -->
                <input type="hidden" id="latitude" name="latitude">
                <input type="hidden" id="longitude" name="longitude">
                <input type="hidden" id="location_name" name="location_name">

                <!-- Notes Section -->
                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-sticky-note mr-2"></i>Catatan (Opsional)
                    </label>
                    <textarea id="notes" name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-center">
                    <button type="submit" class="w-full sm:w-auto px-8 py-3 bg-green-500 text-white font-medium rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500">
                        <i class="fas fa-check mr-2"></i>Check In
                    </button>
                </div>
            </form>
        </div>

        <!-- Recent Check Ins -->
        <div class="mt-8 bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
                <i class="fas fa-history mr-2"></i>Riwayat Check In Hari Ini
            </h3>
            <div id="recent-checkins">
                <div class="text-center text-gray-500 py-4">
                    <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                    <p>Memuat riwayat...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update time every second
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('id-ID', { 
            hour: '2-digit', 
            minute: '2-digit', 
            second: '2-digit' 
        });
        const dateString = now.toLocaleDateString('id-ID', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        
        document.getElementById('current-time').textContent = timeString;
        document.getElementById('current-date').textContent = dateString;
    }
    
    updateTime();
    setInterval(updateTime, 1000);

    // Get location
    const getLocationBtn = document.getElementById('get-location-btn');
    const locationInput = document.getElementById('location-input');
    const locationStatus = document.getElementById('location-status');
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');
    const locationNameInput = document.getElementById('location_name');

    getLocationBtn.addEventListener('click', function() {
        if (!navigator.geolocation) {
            locationStatus.textContent = 'Geolocation tidak didukung oleh browser ini';
            locationStatus.className = 'mt-2 text-sm text-red-500';
            return;
        }

        locationStatus.textContent = 'Mengambil lokasi...';
        locationStatus.className = 'mt-2 text-sm text-blue-500';
        getLocationBtn.disabled = true;

        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                latitudeInput.value = lat;
                longitudeInput.value = lng;
                
                // Get location name using reverse geocoding
                fetch(`https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${lat}&longitude=${lng}&localityLanguage=id`)
                    .then(response => response.json())
                    .then(data => {
                        const locationName = `${data.locality || ''} ${data.city || ''} ${data.principalSubdivision || ''}`.trim();
                        locationInput.value = locationName;
                        locationNameInput.value = locationName;
                        locationStatus.textContent = 'Lokasi berhasil didapatkan';
                        locationStatus.className = 'mt-2 text-sm text-green-500';
                    })
                    .catch(error => {
                        locationInput.value = `Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`;
                        locationNameInput.value = `Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`;
                        locationStatus.textContent = 'Lokasi didapatkan (nama lokasi tidak tersedia)';
                        locationStatus.className = 'mt-2 text-sm text-yellow-500';
                    });
                
                getLocationBtn.disabled = false;
            },
            function(error) {
                let errorMessage = 'Gagal mendapatkan lokasi: ';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMessage += 'Akses lokasi ditolak';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMessage += 'Lokasi tidak tersedia';
                        break;
                    case error.TIMEOUT:
                        errorMessage += 'Timeout mendapatkan lokasi';
                        break;
                    default:
                        errorMessage += 'Error tidak diketahui';
                        break;
                }
                
                locationStatus.textContent = errorMessage;
                locationStatus.className = 'mt-2 text-sm text-red-500';
                getLocationBtn.disabled = false;
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 60000
            }
        );
    });

    // Load recent checkins
    function loadRecentCheckins() {
        fetch('{{ route("attendance.recent") }}')
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('recent-checkins');
                if (data.length === 0) {
                    container.innerHTML = '<div class="text-center text-gray-500 py-4"><i class="fas fa-inbox text-2xl mb-2"></i><p>Belum ada check in hari ini</p></div>';
                } else {
                    let html = '<div class="space-y-3">';
                    data.forEach(checkin => {
                        html += `
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <i class="fas fa-check-circle text-green-500"></i>
                                    <div>
                                        <p class="font-medium">Check In</p>
                                        <p class="text-sm text-gray-600">${checkin.time}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-600">${checkin.location}</p>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                    container.innerHTML = html;
                }
            })
            .catch(error => {
                document.getElementById('recent-checkins').innerHTML = '<div class="text-center text-red-500 py-4"><i class="fas fa-exclamation-triangle text-2xl mb-2"></i><p>Gagal memuat riwayat</p></div>';
            });
    }

    loadRecentCheckins();
});
</script>
@endsection