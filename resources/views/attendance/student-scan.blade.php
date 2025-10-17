@extends('layouts.app')

@section('title', 'Scan Absensi Siswa - Presensia')

@section('content')
    <div class="max-w-6xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Capture QR Code Siswa</h1>
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
            <!-- Camera Capture -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Capture QR Code</h2>
                    
                    <!-- Camera Preview dengan Area Panduan -->
                    <div id="camera-container" class="mb-4 relative">
                        <div id="camera" class="w-full h-96 bg-gray-100 rounded-lg flex items-center justify-center relative overflow-hidden">
                            <div class="text-center">
                                <i class="fas fa-camera text-4xl text-gray-400 mb-2"></i>
                                <p class="text-gray-600">Kamera akan aktif saat tombol start ditekan</p>
                            </div>
                            
                            <!-- Overlay Panduan QR -->
                            <div id="qr-guide" class="absolute inset-0 pointer-events-none" style="display: none;">
                                <!-- Sudut kiri atas -->
                                <div class="absolute top-8 left-8 w-8 h-8 border-l-4 border-t-4 border-blue-500"></div>
                                <!-- Sudut kanan atas -->
                                <div class="absolute top-8 right-8 w-8 h-8 border-r-4 border-t-4 border-blue-500"></div>
                                <!-- Sudut kiri bawah -->
                                <div class="absolute bottom-8 left-8 w-8 h-8 border-l-4 border-b-4 border-blue-500"></div>
                                <!-- Sudut kanan bawah -->
                                <div class="absolute bottom-8 right-8 w-8 h-8 border-r-4 border-b-4 border-blue-500"></div>
                                
                                <!-- Area tengah untuk panduan -->
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="w-48 h-48 border-2 border-dashed border-blue-300 rounded-lg flex items-center justify-center">
                                        <span class="text-blue-500 text-sm font-medium">Arahkan QR Code ke area ini</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Camera Controls -->
                    <div class="flex space-x-3 mb-4">
                        <button id="startCamera" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-camera mr-2"></i>Mulai Kamera
                        </button>
                        <button id="stopCamera" class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition-colors" style="display: none;">
                            <i class="fas fa-stop mr-2"></i>Stop Kamera
                        </button>
                        <button id="capturePhoto" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition-colors" style="display: none;">
                            <i class="fas fa-camera mr-2"></i>Capture QR
                        </button>
                    </div>

                    <!-- Enhanced Manual Input -->
                    <div class="mb-4">
                        <label for="manual_qr" class="block text-sm font-medium text-gray-700">Input Manual QR Code</label>
                        
                        <!-- Quick Add Buttons -->
                        <div class="mb-3">
                            <p class="text-xs text-gray-600 mb-2">Quick Add (untuk testing):</p>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" onclick="quickAddStudent('SISWA001|John Doe')" 
                                        class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs hover:bg-blue-200">
                                    SISWA001
                                </button>
                                <button type="button" onclick="quickAddStudent('SISWA002|Jane Smith')" 
                                        class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs hover:bg-blue-200">
                                    SISWA002
                                </button>
                                <button type="button" onclick="quickAddStudent('SISWA003|Bob Wilson')" 
                                        class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs hover:bg-blue-200">
                                    SISWA003
                                </button>
                            </div>
                        </div>
                        
                        <div class="flex space-x-2">
                            <input type="text" id="manual_qr" placeholder="Masukkan QR Code siswa (NIS|Nama)"
                                   class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <button id="addManual" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        
                        <!-- Help Text -->
                        <div class="mt-1 text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Format: NIS|Nama (contoh: SISWA001|John Doe)
                        </div>
                    </div>
                </div>
            </div>

            <!-- Captured Students -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Siswa yang Sudah Diabsensi</h2>
                    
                    <!-- Captured List -->
                    <div class="mb-4">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="text-sm font-medium text-gray-700">Record Absensi</h3>
                            <span id="captureCount" class="text-sm text-gray-500">0 siswa</span>
                        </div>
                        <div id="capturedList" class="max-h-64 overflow-y-auto border rounded-lg">
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
                    <form id="captureForm" method="POST" action="{{ route('attendance.student-scan') }}" style="display: none;">
                        @csrf
                        <div id="hiddenInputs"></div>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        let capturedStudents = [];
        let cameraStream = null;
        let cameraActive = false;
        let video = null;

        // Start camera
        document.getElementById('startCamera').addEventListener('click', async function() {
            // Cek HTTPS untuk akses kamera
            if (location.protocol !== 'https:' && location.hostname !== 'localhost') {
                alert('Akses kamera memerlukan HTTPS. Silakan akses melalui https:// atau gunakan localhost.');
                return;
            }
            
            try {
                const camera = document.getElementById('camera');
                const guide = document.getElementById('qr-guide');
                
                // Clear previous content
                camera.innerHTML = '';
                guide.style.display = 'block';
                
                // Buat elemen video
                video = document.createElement('video');
                video.style.width = '100%';
                video.style.height = '100%';
                video.style.objectFit = 'cover';
                video.setAttribute('playsinline', 'true');
                video.setAttribute('autoplay', 'true');
                video.setAttribute('muted', 'true');
                camera.appendChild(video);

                // Enhanced camera constraints for better QR scanning
                const constraints = {
                    video: {
                        facingMode: { ideal: 'environment' },
                        width: { ideal: 2560, min: 1920 },
                        height: { ideal: 1440, min: 1080 },
                        frameRate: { ideal: 30, min: 24 },
                        focusMode: 'continuous',
                        exposureMode: 'continuous',
                        whiteBalanceMode: 'continuous'
                    },
                    audio: false
                };
                
                cameraStream = await navigator.mediaDevices.getUserMedia(constraints);
                video.srcObject = cameraStream;
                await video.play();
                
                cameraActive = true;
                document.getElementById('startCamera').style.display = 'none';
                document.getElementById('stopCamera').style.display = 'inline-block';
                document.getElementById('capturePhoto').style.display = 'inline-block';
                
                showNotification('Kamera aktif. Arahkan QR Code ke area panduan dan klik Capture.', 'success');
                
            } catch (err) {
                console.error('Error starting camera:', err);
                alert('Tidak dapat mengakses kamera: ' + err.message);
                showNotification('Kamera gagal. Gunakan input manual di bawah.', 'warning');
            }
        });

        // Stop camera
        document.getElementById('stopCamera').addEventListener('click', function() {
            if (cameraStream) {
                try {
                    cameraStream.getTracks().forEach(track => track.stop());
                } catch (e) {}
                cameraStream = null;
            }
            
            cameraActive = false;
            document.getElementById('startCamera').style.display = 'inline-block';
            document.getElementById('stopCamera').style.display = 'none';
            document.getElementById('capturePhoto').style.display = 'none';
            
            // Reset camera display
            const camera = document.getElementById('camera');
            const guide = document.getElementById('qr-guide');
            guide.style.display = 'none';
            camera.innerHTML = `
                <div class="text-center">
                    <i class="fas fa-camera text-4xl text-gray-400 mb-2"></i>
                    <p class="text-gray-600">Kamera akan aktif saat tombol start ditekan</p>
                </div>
            `;
            
            showNotification('Kamera dihentikan', 'info');
        });

        // Enhanced photo capture with quality optimization
        document.getElementById('capturePhoto').addEventListener('click', function() {
            if (!cameraActive || !video) {
                showNotification('Kamera belum aktif', 'warning');
                return;
            }
            
            try {
                // Show loading state
                const captureBtn = document.getElementById('capturePhoto');
                const originalText = captureBtn.innerHTML;
                captureBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mengambil Foto...';
                captureBtn.disabled = true;
                
                // Wait for video to be ready
                setTimeout(() => {
                    // Buat canvas dengan resolusi tinggi
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    
                    // Set canvas size dengan resolusi tinggi
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    
                    // Draw video frame ke canvas dengan kualitas tinggi
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                    
                    // Apply image enhancement
                    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                    const enhancedData = enhanceImage(imageData);
                    ctx.putImageData(enhancedData, 0, 0);
                    
                    // Convert ke base64 dengan kualitas tinggi
                    const base64Image = canvas.toDataURL('image/jpeg', 0.95);
                    
                    // Tambah ke list
                    addCapturedPhoto(base64Image);
                    showNotification('Foto berhasil diambil dengan kualitas tinggi!', 'success');
                    
                    // Reset button
                    captureBtn.innerHTML = originalText;
                    captureBtn.disabled = false;
                    
                }, 500); // Wait 500ms for camera stabilization
                
            } catch (err) {
                console.error('Error capturing photo:', err);
                showNotification('Gagal capture foto', 'error');
                
                // Reset button
                const captureBtn = document.getElementById('capturePhoto');
                captureBtn.innerHTML = '<i class="fas fa-camera mr-2"></i>Ambil Foto QR';
                captureBtn.disabled = false;
            }
        });
        
        // Image enhancement function
        function enhanceImage(imageData) {
            const data = imageData.data;
            const width = imageData.width;
            const height = imageData.height;
            
            // Apply contrast enhancement
            for (let i = 0; i < data.length; i += 4) {
                // Red channel
                data[i] = Math.min(255, Math.max(0, (data[i] - 128) * 1.2 + 128));
                // Green channel  
                data[i + 1] = Math.min(255, Math.max(0, (data[i + 1] - 128) * 1.2 + 128));
                // Blue channel
                data[i + 2] = Math.min(255, Math.max(0, (data[i + 2] - 128) * 1.2 + 128));
            }
            
            return imageData;
        }
        
        // Quick add student function
        function quickAddStudent(qrCode) {
            console.log('Quick adding student:', qrCode);
            
            const currentTime = new Date().toLocaleTimeString('id-ID', { 
                hour12: false, 
                timeZone: 'Asia/Jakarta' 
            });
            
            const manualRecord = {
                qrCode: qrCode,
                captureTime: currentTime,
                timestamp: Date.now(),
                id: 'manual_' + Date.now(),
                isManual: true
            };
            
            capturedStudents.push(manualRecord);
            updateCapturedList();
            showNotification('Siswa berhasil ditambahkan: ' + qrCode, 'success');
        }

        // Manual input - untuk QR code manual
        document.getElementById('addManual').addEventListener('click', function() {
            const manualQr = document.getElementById('manual_qr').value.trim();
            if (manualQr) {
                console.log('Manual QR input:', manualQr);
                // Simpan sebagai QR code manual (bukan foto)
                const currentTime = new Date().toLocaleTimeString('id-ID', { 
                    hour12: false, 
                    timeZone: 'Asia/Jakarta' 
                });
                
                const manualRecord = {
                    qrCode: manualQr,
                    captureTime: currentTime,
                    timestamp: Date.now(),
                    id: 'manual_' + Date.now(),
                    isManual: true
                };
                
                capturedStudents.push(manualRecord);
                updateCapturedList();
                document.getElementById('manual_qr').value = '';
                showNotification('QR Code manual ditambahkan', 'success');
            }
        });

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

        // Add captured photo - tanpa decode QR
        function addCapturedPhoto(imageData) {
            const currentTime = new Date().toLocaleTimeString('id-ID', { 
                hour12: false, 
                timeZone: 'Asia/Jakarta' 
            });
            
            // Add to captured list - hanya simpan foto
            const photoRecord = {
                imageData: imageData,
                captureTime: currentTime,
                timestamp: Date.now(),
                id: 'photo_' + Date.now() // Unique ID untuk foto
            };
            
            capturedStudents.push(photoRecord);
            updateCapturedList();
        }

        // Show notification
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 px-4 py-2 rounded-lg text-white z-50 ${
                type === 'success' ? 'bg-green-500' : 
                type === 'warning' ? 'bg-yellow-500' : 
                type === 'error' ? 'bg-red-500' : 'bg-blue-500'
            }`;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Update captured list
        function updateCapturedList() {
            const capturedList = document.getElementById('capturedList');
            const hiddenInputs = document.getElementById('hiddenInputs');
            const captureForm = document.getElementById('captureForm');
            const captureCount = document.getElementById('captureCount');
            const syncButton = document.getElementById('syncButton');
            const clearButton = document.getElementById('clearButton');
            
            // Update count
            captureCount.textContent = `${capturedStudents.length} siswa`;
            
            if (capturedStudents.length === 0) {
                capturedList.innerHTML = '<div class="text-gray-500 text-center py-4">Belum ada siswa yang diabsensi</div>';
                captureForm.style.display = 'none';
                syncButton.style.display = 'none';
                clearButton.style.display = 'none';
                return;
            }
            
            // Show action buttons
            syncButton.style.display = 'inline-block';
            clearButton.style.display = 'inline-block';
            syncButton.innerHTML = `<i class="fas fa-sync mr-2"></i>Synchronize (${capturedStudents.length})`;
            
            let html = '';
            hiddenInputs.innerHTML = '';
            
            // Sort by capture time (earliest first)
            const sortedStudents = [...capturedStudents].sort((a, b) => a.timestamp - b.timestamp);
            
            // Table header
            html += `
                <div class="bg-gray-50 px-3 py-2 border-b">
                    <div class="grid grid-cols-12 gap-2 text-xs font-semibold text-gray-600">
                        <div class="col-span-1">No.</div>
                        <div class="col-span-3">Preview</div>
                        <div class="col-span-5">Status</div>
                        <div class="col-span-2">Waktu</div>
                        <div class="col-span-1">Aksi</div>
                    </div>
                </div>
            `;
            
            sortedStudents.forEach((record, index) => {
                if (record.isManual) {
                    // Manual QR Code
                html += `
                    <div class="px-3 py-2 border-b hover:bg-gray-50">
                        <div class="grid grid-cols-12 gap-2 items-center text-sm">
                            <div class="col-span-1 text-gray-600">${index + 1}</div>
                                <div class="col-span-3">
                                    <div class="w-12 h-12 bg-green-100 rounded border flex items-center justify-center">
                                        <i class="fas fa-keyboard text-green-600"></i>
                                    </div>
                                </div>
                                <div class="col-span-5 text-gray-700">
                                    <span class="text-green-600">QR Manual</span>
                                    <br><small class="text-gray-500">${record.qrCode}</small>
                                </div>
                                <div class="col-span-2 text-gray-600">${record.captureTime}</div>
                            <div class="col-span-1">
                                    <button type="button" onclick="removePhotoById('${record.id}')" class="text-red-600 hover:text-red-800 p-1">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                
                const input = document.createElement('input');
                input.type = 'hidden';
                    input.name = 'qr_codes[]';
                    input.value = record.qrCode;
                    hiddenInputs.appendChild(input);
                } else {
                    // Foto QR Code
                    html += `
                        <div class="px-3 py-2 border-b hover:bg-gray-50">
                            <div class="grid grid-cols-12 gap-2 items-center text-sm">
                                <div class="col-span-1 text-gray-600">${index + 1}</div>
                                <div class="col-span-3">
                                    <img src="${record.imageData}" class="w-12 h-12 object-cover rounded border" alt="QR Photo">
                                </div>
                                <div class="col-span-5 text-gray-700">
                                    <span class="text-blue-600">Foto QR Code</span>
                                    <br><small class="text-gray-500">Akan diproses saat sync</small>
                                </div>
                                <div class="col-span-2 text-gray-600">${record.captureTime}</div>
                                <div class="col-span-1">
                                    <button type="button" onclick="removePhotoById('${record.id}')" class="text-red-600 hover:text-red-800 p-1">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'qr_photos[]';
                    input.value = record.imageData;
                hiddenInputs.appendChild(input);
                }
            });
            
            capturedList.innerHTML = html;
            captureForm.style.display = 'block';
        }

        // Remove photo by ID
        function removePhotoById(photoId) {
            capturedStudents = capturedStudents.filter(p => p.id !== photoId);
            updateCapturedList();
        }

        // Synchronize button
        document.getElementById('syncButton').addEventListener('click', function() {
            if (capturedStudents.length === 0) {
                showNotification('Tidak ada data untuk disinkronkan', 'warning');
                return;
            }
            
            if (confirm(`Apakah Anda yakin ingin menyinkronkan ${capturedStudents.length} siswa ke sistem?`)) {
                document.getElementById('captureForm').submit();
            }
        });

        // Clear all button
        document.getElementById('clearButton').addEventListener('click', function() {
            if (capturedStudents.length === 0) {
                showNotification('Tidak ada data untuk dihapus', 'warning');
                return;
            }
            
            if (confirm(`Apakah Anda yakin ingin menghapus semua ${capturedStudents.length} record?`)) {
                capturedStudents = [];
                updateCapturedList();
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
            if (cameraStream) {
                try {
                    cameraStream.getTracks().forEach(track => track.stop());
                } catch (e) {}
            }
        });
    </script>
@endpush