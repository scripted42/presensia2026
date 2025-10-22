@extends('layouts.app')

@section('title', 'Absensi Masuk - Presensia')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Absensi Masuk</h1>
                    <p class="text-sm text-gray-600">{{ now()->format('d F Y - H:i:s') }}</p>
                </div>
                <a href="{{ route('attendance.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Left Panel: Location Map -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="p-4 border-b">
                    <h2 class="text-lg font-semibold text-gray-900">Lokasi Absensi</h2>
                </div>
                <div class="p-4">
                    <div id="map" class="w-full h-96 rounded-lg border" style="background: #f8f9fa;">
                        <!-- Map will be loaded here -->
                        <div class="flex items-center justify-center h-full text-gray-500">
                            <div class="text-center">
                                <i class="fas fa-map-marker-alt text-4xl mb-2"></i>
                                <p>Memuat peta...</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Location Status -->
                    <div class="mt-4 space-y-2">
                        <div class="flex items-center text-sm">
                            <span class="w-20 text-gray-600">Lokasi:</span>
                            <span id="location-status" class="text-gray-900">Mengambil lokasi...</span>
                        </div>
                        <div class="flex items-center text-sm">
                            <span class="w-20 text-gray-600">Radius:</span>
                            <span id="radius-status" class="text-gray-900">Menunggu...</span>
                        </div>
                        <div class="flex items-center text-sm">
                            <span class="w-20 text-gray-600">QR:</span>
                            <span id="qr-status" class="text-gray-900">Menunggu...</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Panel: QR Scanner -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="p-4 border-b">
                    <h2 class="text-lg font-semibold text-gray-900">Scan QR</h2>
                </div>
                <div class="p-4">
                    <!-- QR Scanner Area -->
                    <div id="qr-scanner" class="w-full h-64 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center mb-4">
                        <div class="text-center text-gray-500">
                            <i class="fas fa-qrcode text-4xl mb-2"></i>
                            <p>Kamera akan aktif saat tombol scan ditekan</p>
                        </div>
                    </div>
                    
                    <!-- Scan Button -->
                    <button id="start-scan" class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center">
                        <i class="fas fa-qrcode mr-2"></i>Mulai Scan
                    </button>
                    
                    <!-- Manual QR Input -->
                    <div class="mt-4">
                        <label for="manual-qr" class="block text-sm font-medium text-gray-700 mb-2">Atau masukkan QR Code manual:</label>
                        <input type="text" id="manual-qr" name="qr_code" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Masukkan QR Code...">
                    </div>
                </div>
            </div>
        </div>

        <!-- Check In Form -->
        <div class="mt-6 bg-white rounded-lg shadow-sm border">
            <div class="p-4 border-b">
                <h2 class="text-lg font-semibold text-gray-900">Form Absensi</h2>
            </div>
            <div class="p-4">
                <form id="checkin-form" method="POST" action="{{ route('attendance.checkin') }}">
                    @csrf
                    
                    <!-- Hidden fields for location -->
                    <input type="hidden" id="latitude" name="latitude">
                    <input type="hidden" id="longitude" name="longitude">
                    <input type="hidden" id="location_name" name="location_name">
                    
                    <!-- Notes -->
                    <div class="mb-4">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
                        <textarea id="notes" name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="flex justify-end">
                        <button type="submit" id="submit-btn" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition-colors flex items-center" disabled>
                            <i class="fas fa-check mr-2"></i>Check In
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Include Leaflet CSS and JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let map;
    let marker;
    let circle;
    let qrScanner;
    let currentLocation = null;
    
    // Initialize map
    function initMap() {
        map = L.map('map').setView([-7.23751, 112.62766], 15);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: 'Leaflet | © OpenStreetMap'
        }).addTo(map);
        
        // Add zoom controls
        L.control.zoom({
            position: 'topleft'
        }).addTo(map);
    }
    
    // Get current location
    function getCurrentLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    currentLocation = { lat, lng };
                    
                    // Update map
                    map.setView([lat, lng], 15);
                    
                    // Add marker
                    if (marker) {
                        map.removeLayer(marker);
                    }
                    marker = L.marker([lat, lng]).addTo(map);
                    
                    // Add radius circle
                    if (circle) {
                        map.removeLayer(circle);
                    }
                    circle = L.circle([lat, lng], {
                        color: 'blue',
                        fillColor: 'lightblue',
                        fillOpacity: 0.2,
                        radius: 100
                    }).addTo(map);
                    
                    // Update status
                    document.getElementById('location-status').textContent = `OK (${lat.toFixed(5)}, ${lng.toFixed(5)})`;
                    document.getElementById('radius-status').textContent = 'Dalam radius';
                    
                    // Update hidden fields
                    document.getElementById('latitude').value = lat;
                    document.getElementById('longitude').value = lng;
                    document.getElementById('location_name').value = `Lat: ${lat.toFixed(5)}, Lng: ${lng.toFixed(5)}`;
                    
                    // Enable submit button if QR is scanned
                    checkFormReady();
                },
                function(error) {
                    console.error('Error getting location:', error);
                    document.getElementById('location-status').textContent = 'Gagal mendapatkan lokasi';
                    document.getElementById('radius-status').textContent = 'Error';
                }
            );
        } else {
            document.getElementById('location-status').textContent = 'Geolocation tidak didukung';
        }
    }
    
    // Initialize QR Scanner
    function initQRScanner() {
        qrScanner = new Html5QrcodeScanner("qr-scanner", {
            qrbox: { width: 250, height: 250 },
            fps: 5
        });
    }
    
    // Start QR Scan
    document.getElementById('start-scan').addEventListener('click', function() {
        if (qrScanner) {
            qrScanner.render(onScanSuccess, onScanFailure);
            document.getElementById('start-scan').style.display = 'none';
            document.getElementById('qr-status').textContent = 'Scanning...';
        }
    });
    
    // QR Scan Success
    function onScanSuccess(decodedText, decodedResult) {
        console.log('QR Code detected:', decodedText);
        document.getElementById('manual-qr').value = decodedText;
        document.getElementById('qr-status').textContent = 'QR Code terdeteksi';
        checkFormReady();
    }
    
    // QR Scan Failure
    function onScanFailure(error) {
        // Handle scan failure
    }
    
    // Manual QR Input
    document.getElementById('manual-qr').addEventListener('input', function() {
        if (this.value.trim()) {
            document.getElementById('qr-status').textContent = 'QR Code dimasukkan';
            checkFormReady();
        } else {
            document.getElementById('qr-status').textContent = 'Menunggu...';
            checkFormReady();
        }
    });
    
    // Check if form is ready
    function checkFormReady() {
        const hasLocation = currentLocation !== null;
        const hasQR = document.getElementById('manual-qr').value.trim() !== '';
        
        if (hasLocation && hasQR) {
            document.getElementById('submit-btn').disabled = false;
        } else {
            document.getElementById('submit-btn').disabled = true;
        }
    }
    
    // Form submission
    document.getElementById('checkin-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!currentLocation) {
            alert('Lokasi belum didapatkan. Silakan tunggu atau refresh halaman.');
            return;
        }
        
        if (!document.getElementById('manual-qr').value.trim()) {
            alert('QR Code belum diisi. Silakan scan atau masukkan QR Code manual.');
            return;
        }
        
        // Submit form
        this.submit();
    });
    
    // Initialize everything
    initMap();
    getCurrentLocation();
    initQRScanner();
});
</script>
@endsection