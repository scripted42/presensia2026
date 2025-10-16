@extends('layouts.app')

@section('title', 'Absensi Keluar - Presensia')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
@endpush

@section('content')
<div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Absensi Keluar</h1>
                    <p class="text-gray-600 mt-1">{{ $todayCarbon->format('d F Y') }} - <span id="currentTime">{{ now()->setTimezone('Asia/Jakarta')->format('H:i:s') }}</span></p>
                </div>
                <a href="{{ route('attendance.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Map + Scanner + Status -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Map -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-3">Lokasi Absensi</h2>
                <div id="map" style="width:100%;min-height:320px;height:50vh;border-radius:12px;overflow:hidden;background:#eef2ff"></div>
            </div>
        </div>
        <!-- Scanner/Status (match map width/height) -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-3">Scan QR</h2>
                <div id="scanner" class="w-full bg-gray-100 rounded-lg flex items-center justify-center" style="min-height:320px;height:50vh;">
                    <div class="text-center text-gray-500 text-sm"> Kamera akan aktif saat tombol scan ditekan </div>
                </div>
                <div class="grid grid-cols-1 gap-2 mt-3">
                    <button id="startScanner" type="button" class="bg-blue-600 text-white px-3 py-2 rounded hover:bg-blue-700">Mulai Scan</button>
                </div>
                <div class="mt-4 space-y-1 text-sm">
                    <div>Lokasi: <span id="locStatus" class="font-medium text-gray-700">-</span></div>
                    <div>Radius: <span id="radStatus" class="font-medium text-gray-700">-</span></div>
                    <div>QR: <span id="qrStatus" class="font-medium text-gray-700">Menunggu...</span></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden inputs for submission -->
    <input type="hidden" name="qr_code" id="qr_code">
    <input type="hidden" name="latitude" id="latitude">
    <input type="hidden" name="longitude" id="longitude">
    <input type="hidden" name="location_name" id="location_name">
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    let leafletMap, youMarker, centerMarker, radiusCircle;
    function updateRadiusStatus(position) {
        const lat = position.coords.latitude, lng = position.coords.longitude;
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;
        document.getElementById('location_name').value = 'Lat:'+lat.toFixed(5)+', Lng:'+lng.toFixed(5);
        document.getElementById('locStatus').textContent = 'OK ('+lat.toFixed(5)+', '+lng.toFixed(5)+')';
        @if(isset($settings))
            const R = 6371000; // m
            const toRad = (d)=> d*Math.PI/180;
            const dLat = toRad({{ $settings->location_latitude }} - lat);
            const dLng = toRad({{ $settings->location_longitude }} - lng);
            const a = Math.sin(dLat/2)**2 + Math.cos(toRad(lat))*Math.cos(toRad({{ $settings->location_latitude }}))*Math.sin(dLng/2)**2;
            const c = 2*Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            const dist = R*c;
            document.getElementById('radStatus').textContent = (dist <= {{ $settings->radius_meters }}) ? 'Dalam radius' : 'Di luar radius ('+Math.round(dist)+' m)';
            if (!leafletMap) {
                leafletMap = L.map('map').setView([lat, lng], 17);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(leafletMap);
                youMarker = L.marker([lat, lng]).addTo(leafletMap).bindPopup('Lokasi Anda');
                centerMarker = L.marker([{{ $settings->location_latitude }}, {{ $settings->location_longitude }}], {icon: L.icon({iconUrl:'https://maps.google.com/mapfiles/ms/icons/blue-dot.png', iconSize:[32,32], iconAnchor:[16,32]})}).addTo(leafletMap).bindPopup('Titik Absensi');
                radiusCircle = L.circle([{{ $settings->location_latitude }}, {{ $settings->location_longitude }}], {radius: {{ $settings->radius_meters }}, color:'#2563eb', weight:2, fillColor:'#3b82f6', fillOpacity:0.15}).addTo(leafletMap);
            } else { youMarker.setLatLng([lat, lng]); }
        @endif
    }
    if (navigator.geolocation) { navigator.geolocation.getCurrentPosition(updateRadiusStatus); }
    function updateClock() {
        const now = new Date();
        const jakartaTime = new Date(now.toLocaleString("en-US", {timeZone: "Asia/Jakarta"}));
        const timeString = jakartaTime.toLocaleTimeString('en-GB', { hour12: false });
        const timeElement = document.getElementById('currentTime');
        if (timeElement) { timeElement.textContent = timeString; }
    }
    setInterval(updateClock, 1000);
    const scannerDiv = document.getElementById('scanner');
    let streamRef; let scanTimer; let lastDecoded = '';
    const startBtn = document.getElementById('startScanner');
    if (startBtn) startBtn.addEventListener('click', async () => {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
            streamRef = stream;
            const video = document.createElement('video');
            video.srcObject = stream; video.playsInline = true; await video.play();
            video.style.width='100%'; video.style.height='100%'; video.style.objectFit='cover';
            scannerDiv.innerHTML=''; scannerDiv.appendChild(video);
            const off = document.createElement('canvas');
            const ctx = off.getContext('2d');
            scanTimer = setInterval(async () => {
                if (video.readyState !== video.HAVE_ENOUGH_DATA) return;
                off.width = video.videoWidth; off.height = video.videoHeight;
                ctx.drawImage(video, 0, 0, off.width, off.height);
                const img = ctx.getImageData(0, 0, off.width, off.height);
                const code = jsQR(img.data, off.width, off.height);
                if (code && code.data && code.data !== lastDecoded) {
                    lastDecoded = code.data;
                    document.getElementById('qrStatus').textContent = 'QR terdeteksi';
                    document.getElementById('qr_code').value = lastDecoded;
                    try {
                        const fd = new FormData();
                        fd.append('_token', '{{ csrf_token() }}');
                        fd.append('qr_code', document.getElementById('qr_code').value);
                        fd.append('latitude', document.getElementById('latitude').value);
                        fd.append('longitude', document.getElementById('longitude').value);
                        fd.append('location_name', document.getElementById('location_name').value);
                        const res = await fetch('{{ route('attendance.check-out') }}', { method: 'POST', body: fd });
                        if (res.redirected) { window.location = res.url; }
                        else { const t = await res.text(); console.log(t); window.location.reload(); }
                    } catch(e) { alert('Gagal submit: '+e.message); }
                    clearInterval(scanTimer); video.pause();
                    streamRef.getTracks().forEach(t=>t.stop());
                }
            }, 500);
        } catch(e){ alert('Tidak bisa mengakses kamera: '+e.message); }
    });
</script>
@endpush

