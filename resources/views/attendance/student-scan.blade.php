@extends('layouts.app')

@section('title', 'Scan Absensi Siswa - Presensia')

@section('content')
    <div class="max-w-6xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Scan Absensi Siswa</h1>
                        <p class="text-gray-600 mt-1">{{ now()->setTimezone('Asia/Jakarta')->format('d F Y') }} - <span id="currentTime">{{ now()->setTimezone('Asia/Jakarta')->format('H:i:s') }}</span></p>
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

        <!-- Scanner Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- QR Scanner -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Scanner QR Code</h2>
                    
                    <!-- Camera Preview -->
                    <div id="scanner-container" class="mb-4">
                        <div id="scanner" class="w-full h-64 bg-gray-100 rounded-lg flex items-center justify-center">
                            <div class="text-center">
                                <i class="fas fa-qrcode text-4xl text-gray-400 mb-2"></i>
                                <p class="text-gray-600">Kamera akan aktif saat tombol scan ditekan</p>
                            </div>
                        </div>
                    </div>

                    <!-- Scanner Controls -->
                    <div class="flex space-x-3 mb-4">
                        <button id="startScanner" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-play mr-2"></i>Mulai Scan
                        </button>
                        <button id="stopScanner" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors" style="display: none;">
                            <i class="fas fa-stop mr-2"></i>Stop Scan
                        </button>
                    </div>

                    <!-- Manual Input -->
                    <div class="mb-4">
                        <label for="manual_qr" class="block text-sm font-medium text-gray-700">Input Manual QR Code</label>
                        <div class="flex space-x-2">
                            <input type="text" id="manual_qr" placeholder="Masukkan QR Code siswa"
                                   class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <button id="addManual" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <!-- Test QR Buttons -->
                        <div class="mt-2 flex space-x-2">
                            <button id="testQR1" class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600">
                                Test QR 1
                            </button>
                            <button id="testQR2" class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600">
                                Test QR 2
                            </button>
                            <button id="testQR3" class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600">
                                Test QR 3
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Scanned Students -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Siswa yang Sudah Diabsensi</h2>
                    
                    <!-- Scanned List -->
                    <div class="mb-4">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="text-sm font-medium text-gray-700">Record Absensi</h3>
                            <span id="scanCount" class="text-sm text-gray-500">0 siswa</span>
                        </div>
                        <div id="scannedList" class="max-h-64 overflow-y-auto border rounded-lg">
                            <div class="text-gray-500 text-center py-4">Belum ada siswa yang diabsensi</div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex space-x-2">
                        <button id="syncButton" type="button" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors" style="display: none;">
                            <i class="fas fa-sync mr-2"></i>Synchronize (0)
                        </button>
                        <button id="clearButton" type="button" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors" style="display: none;">
                            <i class="fas fa-trash mr-2"></i>Clear All
                        </button>
                    </div>

                    <!-- Submit Form -->
                    <form id="scanForm" method="POST" action="{{ route('attendance.student-scan') }}" style="display: none;">
                        @csrf
                        <div id="hiddenInputs"></div>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <!-- ZXing via Nimiq QrScanner (v1.2.0 - kompatibel WORKER_PATH). Jika gagal dimuat, fallback ke jsQR di bawah -->
    <script src="https://unpkg.com/qr-scanner@1.2.0/qr-scanner.min.js"></script>
    <!-- Fallback decoder: jsQR -->
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
    <script>
        let scannedStudents = [];
        let qrScanner = null;
        let scannerActive = false;
        let lastDecodeTs = 0;

        // Start scanner: gunakan ZXing melalui QrScanner jika tersedia; jika tidak, fallback ke jsQR
        document.getElementById('startScanner').addEventListener('click', async function() {
            // Cek HTTPS untuk akses kamera
            if (location.protocol !== 'https:' && location.hostname !== 'localhost') {
                alert('Akses kamera memerlukan HTTPS. Silakan akses melalui https:// atau gunakan localhost.');
                return;
            }
            
            try {
                const scanner = document.getElementById('scanner');
                
                // Clear previous content
                scanner.innerHTML = '';
                
                // Buat elemen video dengan optimasi performa
                const video = document.createElement('video');
                video.style.width = '100%';
                video.style.height = '100%';
                video.style.objectFit = 'cover';
                video.setAttribute('playsinline', 'true');
                video.setAttribute('autoplay', 'true');
                video.setAttribute('muted', 'true');
                video.setAttribute('webkit-playsinline', 'true');
                scanner.appendChild(video);

                if (window.QrScanner) {
                    console.log('QrScanner available, initializing...');
                    
                    try {
                        // Set path untuk worker lokal (same-origin, hindari CORS)
                        QrScanner.WORKER_PATH = '/qr-scanner-worker.min.js';

                        // Inisialisasi QrScanner dengan konfigurasi yang lebih sederhana
                        qrScanner = new QrScanner(video, result => {
                            console.log('QrScanner callback triggered:', result);
                            const data = typeof result === 'string' ? result : (result && result.data ? result.data : '');
                            if (!data) {
                                console.log('No data in result:', result);
                                return;
                            }
                            const n = Date.now();
                            if (n - lastDecodeTs > 150) {
                                lastDecodeTs = n;
                                console.log('QR detected (QrScanner):', data);
                                addScannedStudent(data);
                            }
                        }, {
                            preferredCamera: 'environment',
                            highlightScanRegion: true, // Aktifkan untuk debugging
                            highlightCodeOutline: true,
                            returnDetailedScanResult: true,
                            maxScansPerSecond: 15 // Turunkan untuk stabilitas
                        });

                        console.log('Starting QrScanner...');
                        await qrScanner.start();
                        console.log('QrScanner started successfully');
                    } catch (qrError) {
                        console.error('QrScanner failed, falling back to jsQR:', qrError);
                        // Fallback ke jsQR jika QrScanner gagal
                        window.QrScanner = null;
                    }
                }
                
                // Jika QrScanner gagal atau tidak tersedia, gunakan jsQR
                if (!window.QrScanner || !qrScanner) {
                    console.log('Using jsQR fallback...');
                    // Fallback ke jsQR manual loop dengan kompatibilitas browser lama
                    let stream;
                    try {
                        // Coba getUserMedia modern dulu dengan resolusi tinggi untuk jarak jauh
                        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                            const constraints = {
                                video: {
                                    facingMode: { ideal: 'environment' },
                                    width: { ideal: 2560, min: 1280 }, // Resolusi tinggi untuk jarak jauh
                                    height: { ideal: 1440, min: 720 },
                                    frameRate: { ideal: 30, min: 15 }, // Frame rate tinggi
                                    focusMode: { ideal: 'continuous' }, // Auto-focus kontinyu
                                    exposureMode: { ideal: 'continuous' }, // Auto-exposure kontinyu
                                    whiteBalanceMode: { ideal: 'continuous' }, // Auto white balance
                                    torch: false // Matikan flash untuk performa
                                },
                                audio: false
                            };
                            stream = await navigator.mediaDevices.getUserMedia(constraints);
                        } else if (navigator.getUserMedia) {
                            // Fallback untuk browser lama
                            stream = await new Promise((resolve, reject) => {
                                navigator.getUserMedia({ video: true, audio: false }, resolve, reject);
                            });
                        } else if (navigator.webkitGetUserMedia) {
                            // Fallback untuk WebKit lama
                            stream = await new Promise((resolve, reject) => {
                                navigator.webkitGetUserMedia({ video: true, audio: false }, resolve, reject);
                            });
                        } else {
                            throw new Error('getUserMedia tidak didukung di browser ini');
                        }
                        
                        video.srcObject = stream;
                        await video.play();
                    } catch (err) {
                        console.error('Error accessing camera:', err);
                        throw new Error('Tidak dapat mengakses kamera. Pastikan menggunakan HTTPS dan izinkan akses kamera.');
                    }

                    // Canvas offscreen untuk performa tinggi
                    const off = document.createElement('canvas');
                    const ctx = off.getContext('2d');
                    off.style.display = 'none'; // Sembunyikan canvas

                    // ROI dinamis: scan area yang lebih besar untuk jarak jauh
                    function getROI(vw, vh) {
                        // Untuk jarak jauh, scan area lebih besar (90% vs 70%)
                        const rw = Math.floor(vw * 0.9);
                        const rh = Math.floor(vh * 0.9);
                        const rx = Math.floor((vw - rw) / 2);
                        const ry = Math.floor((vh - rh) / 2);
                        return { rx, ry, rw, rh };
                    }

                    let lastScanTs = 0;
                    let scanCount = 0;
                    console.log('Starting jsQR scanning loop...');
                    
                    const loop = () => {
                        if (!scannerActive) {
                            console.log('Scanner not active, stopping loop');
                            return;
                        }
                        if (video.readyState === video.HAVE_ENOUGH_DATA) {
                            const now = performance.now();
                            // Throttle lebih agresif: 50ms (~20fps) untuk performa tinggi
                            if (now - lastScanTs > 50) {
                                lastScanTs = now;
                                const vw = video.videoWidth, vh = video.videoHeight;
                                if (vw && vh) {
                                    // Set canvas size sesuai video
                                    off.width = vw; off.height = vh;
                                    ctx.drawImage(video, 0, 0, vw, vh);
                                    
                                    // Scan area yang lebih besar
                                    const { rx, ry, rw, rh } = getROI(vw, vh);
                                    const img = ctx.getImageData(rx, ry, rw, rh);
                                    
                                    // Multi-pass scanning dengan parameter berbeda
                                    const scanParams = [
                                        { inversionAttempts: 'dontInvert' },
                                        { inversionAttempts: 'onlyInvert' },
                                        { inversionAttempts: 'attemptBoth' }
                                    ];
                                    
                                    for (const params of scanParams) {
                                        try {
                                            const code = window.jsQR ? jsQR(img.data, rw, rh, params) : null;
                                            if (code && code.data) {
                                                const n = Date.now();
                                                if (n - lastDecodeTs > 100) { // Lebih cepat dari 250ms
                                                    lastDecodeTs = n;
                                                    console.log(`QR detected (jsQR) - pass ${scanCount}:`, code.data);
                                                    addScannedStudent(code.data);
                                                    return; // Keluar dari loop jika berhasil
                                                }
                                            }
                                        } catch (decodeError) {
                                            console.log('jsQR decode error:', decodeError);
                                        }
                                    }
                                    
                                    // Test dengan QR code sederhana untuk debugging
                                    if (scanCount === 50) {
                                        console.log('Testing with simple QR data...');
                                        addScannedStudent('SISWA001|Test QR');
                                    }
                                    scanCount++;
                                    if (scanCount % 100 === 0) {
                                        console.log(`jsQR scan attempts: ${scanCount}, video size: ${vw}x${vh}`);
                                    }
                                }
                            }
                        }
                        requestAnimationFrame(loop);
                    };
                    scannerActive = true; 
                    console.log('Starting jsQR loop...');
                    requestAnimationFrame(loop);
                }

                scannerActive = true;
                document.getElementById('startScanner').style.display = 'none';
                document.getElementById('stopScanner').style.display = 'inline-block';
                
                console.log('QrScanner started successfully');
                console.log('Scanner active:', scannerActive);
                console.log('QR Scanner instance:', qrScanner);
                showNotification('Scanner berhasil dimulai. Arahkan kamera ke QR Code siswa.', 'success');
                
            } catch (err) {
                console.error('Error starting scanner:', err);
                
                let errorMsg = 'Tidak dapat mengakses kamera: ' + err.message;
                if (err.message.includes('getUserMedia')) {
                    errorMsg = 'Browser tidak mendukung akses kamera. Pastikan:\n' +
                              '1. Menggunakan HTTPS (bukan HTTP)\n' +
                              '2. Browser terbaru (Chrome, Firefox, Safari)\n' +
                              '3. Izin kamera sudah diberikan\n' +
                              '4. Tidak ada aplikasi lain yang menggunakan kamera';
                }
                
                alert(errorMsg);
                
                // Show manual input as fallback
                document.getElementById('manualQrInput').style.display = 'block';
                showNotification('Scanner gagal. Gunakan input manual di bawah.', 'warning');
            }
        });

        // Stop scanner: hentikan QrScanner dan stream video
        document.getElementById('stopScanner').addEventListener('click', function() {
            if (qrScanner) {
                try { qrScanner.stop(); } catch (e) {}
                try { qrScanner.destroy(); } catch (e) {}
                qrScanner = null;
            } else {
                const scanner = document.getElementById('scanner');
                const video = scanner.querySelector('video');
                if (video && video.srcObject) {
                    try { video.srcObject.getTracks().forEach(t => t.stop()); } catch (e) {}
                    video.srcObject = null;
                }
            }
            scannerActive = false;
            
            document.getElementById('startScanner').style.display = 'inline-block';
            document.getElementById('stopScanner').style.display = 'none';
            
            // Reset scanner display
            const scanner = document.getElementById('scanner');
            scanner.innerHTML = `
                <div class="text-center">
                    <i class="fas fa-qrcode text-4xl text-gray-400 mb-2"></i>
                    <p class="text-gray-600">Kamera akan aktif saat tombol scan ditekan</p>
                </div>
            `;
            
            console.log('QrScanner stopped');
            showNotification('Scanner dihentikan', 'info');
        });

        // Manual input
        document.getElementById('addManual').addEventListener('click', function() {
            const manualQr = document.getElementById('manual_qr').value.trim();
            if (manualQr) {
                console.log('Manual QR input:', manualQr);
                addScannedStudent(manualQr);
                document.getElementById('manual_qr').value = '';
            }
        });
        
        // Test QR buttons
        document.getElementById('testQR1').addEventListener('click', function() {
            console.log('Test QR 1 clicked');
            addScannedStudent('SISWA001|Siswa Test 1');
        });
        
        document.getElementById('testQR2').addEventListener('click', function() {
            console.log('Test QR 2 clicked');
            addScannedStudent('SISWA002|Siswa Test 2');
        });
        
        document.getElementById('testQR3').addEventListener('click', function() {
            console.log('Test QR 3 clicked');
            addScannedStudent('SISWA003|Siswa Test 3');
        });
        
        // Test parsing function
        console.log('Testing parseQRCode function:');
        console.log('Test 1 - SISWA001|Siswa 1:', parseQRCode('SISWA001|Siswa 1'));
        console.log('Test 2 - SISWA002_Siswa 2:', parseQRCode('SISWA002_Siswa 2'));
        console.log('Test 3 - SISWA003:', parseQRCode('SISWA003'));
        
        // Test manual QR untuk debugging
        console.log('Testing manual QR input...');
        setTimeout(() => {
            console.log('Auto-testing with SISWA999|Test Student');
            addScannedStudent('SISWA999|Test Student');
        }, 2000);
        
        // Cek ketersediaan kamera (opsional)
        if (window.QrScanner && typeof window.QrScanner.hasCamera === 'function') {
            window.QrScanner.hasCamera().then(hasCamera => {
                console.log('Device has camera:', hasCamera);
            }).catch(err => console.log('Error checking camera:', err));
        }

        // Parse QR code to extract NIS and name
        function parseQRCode(qrCode) {
            console.log('Parsing QR Code:', qrCode);
            const raw = String(qrCode ?? '').trim();
            try {
                // Format QR: "NIS|Nama" atau "NIS_Nama"
                if (raw.includes('|')) {
                    const [nis, name = ''] = raw.split('|');
                    const result = { nis: (nis || '').trim(), name: (name || '').trim() };
                    console.log('Parsed with | separator:', result);
                    return result;
                }
                if (raw.includes('_')) {
                    const [nis, name = ''] = raw.split('_');
                    const result = { nis: (nis || '').trim(), name: (name || '').trim() };
                    console.log('Parsed with _ separator:', result);
                    return result;
                }

                // Hanya coba JSON bila string terlihat seperti JSON
                if (/^[\[{]/.test(raw)) {
                    const parsed = JSON.parse(raw);
                    const result = {
                        nis: (parsed?.nis ?? parsed?.NIS ?? '').toString(),
                        name: (parsed?.name ?? parsed?.nama ?? '').toString()
                    };
                    console.log('Parsed as JSON:', result);
                    if (result.nis) return result;
                }
            } catch (e) {
                console.warn('QR parse error, fallback to plain NIS:', e);
            }

            // Fallback: anggap seluruh string adalah NIS
            const result = { nis: raw, name: raw };
            console.log('Fallback parsing:', result);
            return result;
        }

        // Add scanned student with duplicate check and time tracking
        function addScannedStudent(qrCode) {
            console.log('Processing QR Code:', qrCode);
            const parsed = parseQRCode(qrCode);
            console.log('Parsed QR Code:', parsed);
            const currentTime = new Date().toLocaleTimeString('id-ID', { 
                hour12: false, 
                timeZone: 'Asia/Jakarta' 
            });
            
            // Check for duplicates by NIS
            const existingIndex = scannedStudents.findIndex(student => student.nis === parsed.nis);
            if (existingIndex !== -1) {
                showNotification(`Siswa ${parsed.name} (${parsed.nis}) sudah diabsensi pada ${scannedStudents[existingIndex].scanTime}`, 'warning');
                return;
            }
            
            // Hindari request network saat antrian panjang: gunakan nama dari QR jika ada,
            // jika tidak ada, tampilkan NIS; sinkronisasi nama bisa dilakukan kemudian.
            if (parsed.name === parsed.nis) {
                const studentRecord = {
                    qrCode: qrCode,
                    nis: parsed.nis,
                    name: parsed.nis,
                    scanTime: currentTime,
                    timestamp: Date.now()
                };
                scannedStudents.push(studentRecord);
                updateScannedList();
                showNotification(`${parsed.nis} berhasil ditambahkan`, 'success');
            } else {
                // Add to scanned list with timestamp
                const studentRecord = {
                    qrCode: qrCode,
                    nis: parsed.nis,
                    name: parsed.name,
                    scanTime: currentTime,
                    timestamp: Date.now()
                };
                
                scannedStudents.push(studentRecord);
                updateScannedList();
                showNotification(`${parsed.name} (${parsed.nis}) berhasil ditambahkan`, 'success');
            }
        }
        
        // Fetch student name from server
        async function fetchStudentName(nis) {
            try {
                const response = await fetch(`/api/student/${encodeURIComponent(nis)}`);
                if (response.ok) {
                    const data = await response.json();
                    return data?.name ?? null;
                }
            } catch (error) {
                console.error('Error fetching student name:', error);
            }
            return null;
        }

        // Show notification
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 px-4 py-2 rounded-lg text-white z-50 ${
                type === 'success' ? 'bg-green-500' : 
                type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500'
            }`;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 2000);
        }

        // Update scanned list with table format
        function updateScannedList() {
            const scannedList = document.getElementById('scannedList');
            const hiddenInputs = document.getElementById('hiddenInputs');
            const scanForm = document.getElementById('scanForm');
            const scanCount = document.getElementById('scanCount');
            const syncButton = document.getElementById('syncButton');
            const clearButton = document.getElementById('clearButton');
            
            // Update count
            scanCount.textContent = `${scannedStudents.length} siswa`;
            
            if (scannedStudents.length === 0) {
                scannedList.innerHTML = '<div class="text-gray-500 text-center py-4">Belum ada siswa yang diabsensi</div>';
                scanForm.style.display = 'none';
                syncButton.style.display = 'none';
                clearButton.style.display = 'none';
                return;
            }
            
            // Show action buttons
            syncButton.style.display = 'inline-block';
            clearButton.style.display = 'inline-block';
            syncButton.innerHTML = `<i class="fas fa-sync mr-2"></i>Synchronize (${scannedStudents.length})`;
            
            let html = '';
            hiddenInputs.innerHTML = '';
            
            // Sort by scan time (earliest first)
            const sortedStudents = [...scannedStudents].sort((a, b) => a.timestamp - b.timestamp);
            
            // Table header
            html += `
                <div class="bg-gray-50 px-3 py-2 border-b">
                    <div class="grid grid-cols-12 gap-2 text-xs font-semibold text-gray-600">
                        <div class="col-span-1">No.</div>
                        <div class="col-span-3">NIS</div>
                        <div class="col-span-5">Nama</div>
                        <div class="col-span-2">Waktu</div>
                        <div class="col-span-1">Aksi</div>
                    </div>
                </div>
            `;
            
            sortedStudents.forEach((student, index) => {
                html += `
                    <div class="px-3 py-2 border-b hover:bg-gray-50">
                        <div class="grid grid-cols-12 gap-2 items-center text-sm">
                            <div class="col-span-1 text-gray-600">${index + 1}</div>
                            <div class="col-span-3 font-medium text-gray-900">${student.nis}</div>
                            <div class="col-span-5 text-gray-700">${student.name}</div>
                            <div class="col-span-2 text-gray-600">${student.scanTime}</div>
                            <div class="col-span-1">
                                <button type="button" onclick="removeStudentByNis('${'${student.nis}'.replace(/'/g, "\\'")}')" class="text-red-600 hover:text-red-800 p-1">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'student_qr_codes[]';
                input.value = student.qrCode;
                hiddenInputs.appendChild(input);
            });
            
            scannedList.innerHTML = html;
            scanForm.style.display = 'block';
        }

        // Remove student by NIS agar tidak bergantung pada urutan sort
        function removeStudentByNis(nis) {
            scannedStudents = scannedStudents.filter(s => s.nis !== nis);
            updateScannedList();
        }

        // Synchronize button
        document.getElementById('syncButton').addEventListener('click', function() {
            if (scannedStudents.length === 0) {
                showNotification('Tidak ada data untuk disinkronkan', 'warning');
                return;
            }
            
            if (confirm(`Apakah Anda yakin ingin menyinkronkan ${scannedStudents.length} siswa ke sistem?`)) {
                document.getElementById('scanForm').submit();
            }
        });

        // Clear all button
        document.getElementById('clearButton').addEventListener('click', function() {
            if (scannedStudents.length === 0) {
                showNotification('Tidak ada data untuk dihapus', 'warning');
                return;
            }
            
            if (confirm(`Apakah Anda yakin ingin menghapus semua ${scannedStudents.length} record?`)) {
                scannedStudents = [];
                updateScannedList();
                showNotification('Semua record telah dihapus', 'info');
            }
        });

        // Real-time clock update
        function updateClock() {
            const now = new Date();
            const jakartaTime = new Date(now.toLocaleString("en-US", {timeZone: "Asia/Jakarta"}));
            const timeString = jakartaTime.toLocaleTimeString('en-GB', { hour12: false });
            const timeElement = document.getElementById('currentTime');
            if (timeElement) {
                timeElement.textContent = timeString;
            }
        }
        
        // Update clock every second
        setInterval(updateClock, 1000);

        // Clean up on page unload
        window.addEventListener('beforeunload', function() {
            if (qrScanner) {
                try { qrScanner.destroy(); } catch (e) {}
            } else {
                const scanner = document.getElementById('scanner');
                if (!scanner) return;
                const video = scanner.querySelector('video');
                if (video && video.srcObject) {
                    try { video.srcObject.getTracks().forEach(t => t.stop()); } catch (e) {}
                }
            }
        });
    </script>
@endpush

