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
                                
                                <!-- Simple instruction -->
                                <div class="absolute top-2 right-2 bg-blue-500 text-white px-3 py-1 rounded-lg text-sm font-medium">
                                    <i class="fas fa-camera mr-1"></i>Arahkan QR ke area ini
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Camera Controls -->
                    <div class="flex space-x-3 mb-4">
                        <button id="startCamera" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-qrcode mr-2"></i>Mulai Scan QR Code
                        </button>
                        <button id="stopCamera" class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition-colors" style="display: none;">
                            <i class="fas fa-stop mr-2"></i>Stop Scan
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
                                <button type="button" onclick="testQRDetection()" 
                                        class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs hover:bg-green-200">
                                    Test QR
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
    <!-- HTML5 QR Code Scanner Library - Fresh Install -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        let capturedStudents = [];
        let html5QrcodeScanner = null;
        let cameraActive = false;

        // Simple HTML5 QR Code Scanner Implementation
        document.getElementById('startCamera').addEventListener('click', function() {
            try {
                console.log('🎥 Starting HTML5 QR Code Scanner...');
                
                const camera = document.getElementById('camera');
                const guide = document.getElementById('qr-guide');
                
                if (!camera) {
                    throw new Error('Camera element not found');
                }
                
                // Clear previous content
                camera.innerHTML = '<div id="html5-qrcode-reader"></div>';
                if (guide) {
                    guide.style.display = 'block';
                }
                
                // Initialize scanner with simple config
                html5QrcodeScanner = new Html5QrcodeScanner(
                    "html5-qrcode-reader",
                    {
                        fps: 10,
                        qrbox: { width: 250, height: 250 },
                        rememberLastUsedCamera: true,
                        supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA]
                    },
                    false
                );
                
                // Start scanning
                html5QrcodeScanner.render(onScanSuccess, onScanFailure);
                
                cameraActive = true;
                document.getElementById('startCamera').style.display = 'none';
                document.getElementById('stopCamera').style.display = 'inline-block';
                
                showNotification('Scanner aktif. Arahkan QR Code ke area panduan.', 'success');
                console.log('✅ HTML5 QR Code Scanner started successfully');
                
            } catch (err) {
                console.error('❌ Scanner error:', err);
                showNotification('Gagal mengakses kamera: ' + err.message, 'error');
            }
        });

        // Stop scanner
        document.getElementById('stopCamera').addEventListener('click', function() {
            try {
                if (html5QrcodeScanner) {
                    html5QrcodeScanner.clear();
                    html5QrcodeScanner = null;
                }
                
                cameraActive = false;
                document.getElementById('startCamera').style.display = 'inline-block';
                document.getElementById('stopCamera').style.display = 'none';
                
                const camera = document.getElementById('camera');
                const guide = document.getElementById('qr-guide');
                if (guide) guide.style.display = 'none';
                if (camera) {
                    camera.innerHTML = `
                        <div class="text-center">
                            <i class="fas fa-camera text-4xl text-gray-400 mb-2"></i>
                            <p class="text-gray-600">Kamera akan aktif saat tombol start ditekan</p>
                        </div>
                    `;
                }
                
                showNotification('Scanner dihentikan', 'info');
                
            } catch (err) {
                console.error('❌ Stop scanner error:', err);
                showNotification('Gagal menghentikan scanner', 'error');
            }
        });

        // QR Code scan success callback
        function onScanSuccess(decodedText, decodedResult) {
            console.log('QR Code detected:', decodedText);
            addCapturedPhoto(decodedText, true);
        }

        // QR Code scan failure callback
        function onScanFailure(error) {
            // Silent failure - don't show errors for every scan attempt
        }

        // Start HTML5 QR Code Scanner
        document.getElementById('startCamera').addEventListener('click', async function() {
            try {
                console.log('🎥 Starting HTML5 QR Code Scanner...');
                
                // Wait for DOM to be ready
                await new Promise(resolve => setTimeout(resolve, 100));
                
                const camera = document.getElementById('camera');
                const guide = document.getElementById('qr-guide');
                
                console.log('Camera element:', camera);
                console.log('Guide element:', guide);
                
                if (!camera) {
                    console.log('Camera element not found, creating fallback...');
                    // Try to find camera container and create camera element
                    const cameraContainer = document.getElementById('camera-container');
                    if (cameraContainer) {
                        const newCamera = document.createElement('div');
                        newCamera.id = 'camera';
                        newCamera.className = 'w-full h-96 bg-gray-100 rounded-lg flex items-center justify-center relative overflow-hidden';
                        newCamera.innerHTML = `
                            <div class="text-center">
                                <i class="fas fa-camera text-4xl text-gray-400 mb-2"></i>
                                <p class="text-gray-600">Kamera akan aktif saat tombol start ditekan</p>
                            </div>
                        `;
                        cameraContainer.appendChild(newCamera);
                        console.log('✅ Camera element created');
                        // Update camera reference
                        camera = newCamera;
                    } else {
                        throw new Error('Camera element not found and cannot create fallback');
                    }
                }
                
                // Guide element is optional, create if not found
                let guideElement = guide;
                if (!guideElement) {
                    console.log('Guide element not found, creating fallback...');
                    // Create guide element if not found
                    const cameraContainer = document.getElementById('camera-container');
                    if (cameraContainer) {
                        const newGuide = document.createElement('div');
                        newGuide.id = 'qr-guide';
                        newGuide.className = 'absolute inset-0 pointer-events-none';
                        newGuide.style.display = 'none';
                        newGuide.innerHTML = `
                            <div class="absolute top-8 left-8 w-8 h-8 border-l-4 border-t-4 border-blue-500"></div>
                            <div class="absolute top-8 right-8 w-8 h-8 border-r-4 border-t-4 border-blue-500"></div>
                            <div class="absolute bottom-8 left-8 w-8 h-8 border-l-4 border-b-4 border-blue-500"></div>
                            <div class="absolute bottom-8 right-8 w-8 h-8 border-r-4 border-b-4 border-blue-500"></div>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="w-48 h-48 border-2 border-dashed border-blue-300 rounded-lg flex items-center justify-center">
                                    <span class="text-blue-500 text-sm font-medium">Arahkan QR Code ke area ini</span>
                                </div>
                            </div>
                            <div class="absolute top-2 right-2 bg-blue-500 text-white px-3 py-1 rounded-lg text-sm font-medium">
                                <i class="fas fa-camera mr-1"></i>Arahkan QR ke area ini
                            </div>
                        `;
                        cameraContainer.appendChild(newGuide);
                        guideElement = newGuide;
                        console.log('✅ Guide element created');
                    }
                }
                
                // Clean up any existing scanner
                if (html5QrcodeScanner) {
                    try {
                        html5QrcodeScanner.clear();
                        html5QrcodeScanner = null;
                        console.log('✅ Previous scanner cleared');
                    } catch (e) {
                        console.log('Previous scanner cleanup failed:', e);
                    }
                }
                
                // Clear previous content and add scanner container
                camera.innerHTML = '<div id="html5-qrcode-reader"></div>';
                if (guideElement) {
                    guideElement.style.display = 'block';
                }
                
                // Wait for DOM update
                await new Promise(resolve => setTimeout(resolve, 200));

                // Pre-request camera permission with back camera only
                try {
                    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                        const stream = await navigator.mediaDevices.getUserMedia({ 
                            video: { 
                                facingMode: { exact: "environment" },
                                width: { ideal: 1280 },
                                height: { ideal: 720 }
                            } 
                        });
                        // Stop the test stream immediately
                        stream.getTracks().forEach(track => track.stop());
                        console.log('✅ Back camera permission pre-granted');
                    } else {
                        console.log('getUserMedia not supported');
                    }
                } catch (e) {
                    console.log('Back camera permission will be requested by scanner:', e.message);
                }

                // Get available cameras and select camera 2 (back camera)
                let cameras = [];
                try {
                    cameras = await Html5Qrcode.getCameras();
                    console.log('Available cameras:', cameras);
                    console.log('Camera count:', cameras.length);
                    cameras.forEach((camera, index) => {
                        console.log(`Camera ${index}:`, camera.id, camera.label);
                    });
                } catch (e) {
                    console.log('Failed to get cameras:', e.message);
                    // Continue without specific camera selection
                }
                
                if (cameras.length === 0) {
                    console.log('No cameras found, will use default camera');
                }
                
                // Use camera 2 (back camera) - try camera 2 first, then fallback to others
                let cameraId = null;
                if (cameras.length >= 3) {
                    cameraId = cameras[2].id; // Camera 2
                    console.log('Using camera 2:', cameraId);
                } else if (cameras.length >= 2) {
                    cameraId = cameras[1].id; // Camera 1
                    console.log('Using camera 1:', cameraId);
                } else if (cameras.length >= 1) {
                    cameraId = cameras[0].id; // Camera 0
                    console.log('Using camera 0:', cameraId);
                } else {
                    console.log('No specific camera selected, will use default');
                }
                
                // Test camera access with fallback
                let finalCameraId = cameraId;
                try {
                    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                        const testStream = await navigator.mediaDevices.getUserMedia({ 
                            video: { deviceId: { exact: cameraId } } 
                        });
                        testStream.getTracks().forEach(track => track.stop());
                        console.log('✅ Camera access confirmed:', cameraId);
                    } else {
                        console.log('getUserMedia not supported, skipping camera test');
                    }
                } catch (e) {
                    console.log('Camera access failed, trying fallback cameras:', e.message);
                    
                    // Try camera 1 if camera 2 failed
                    if (cameras.length >= 2) {
                        try {
                            const fallbackId = cameras[1].id;
                            const fallbackStream = await navigator.mediaDevices.getUserMedia({ 
                                video: { deviceId: { exact: fallbackId } } 
                            });
                            fallbackStream.getTracks().forEach(track => track.stop());
                            finalCameraId = fallbackId;
                            console.log('✅ Fallback to camera 1:', fallbackId);
                        } catch (e2) {
                            console.log('Camera 1 also failed, trying camera 0:', e2.message);
                            finalCameraId = cameras[0].id;
                        }
                    }
                    
                    // Final fallback to environment camera
                    if (!finalCameraId) {
                        try {
                            const envStream = await navigator.mediaDevices.getUserMedia({ 
                                video: { facingMode: "environment" } 
                            });
                            envStream.getTracks().forEach(track => track.stop());
                            console.log('✅ Using environment camera');
                        } catch (e3) {
                            console.log('All camera access failed:', e3.message);
                        }
                    }
                }
                
                // Initialize HTML5 QR Code Scanner with camera 0
                try {
                    // Check if scanner container exists
                    const scannerContainer = document.getElementById('html5-qrcode-reader');
                    if (!scannerContainer) {
                        throw new Error('Scanner container not found');
                    }
                    
                    html5QrcodeScanner = new Html5QrcodeScanner(
                        "html5-qrcode-reader",
                        {
                            fps: 10,
                            qrbox: { width: 250, height: 250 },
                            rememberLastUsedCamera: false,
                            supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA],
                            showTorchButtonIfSupported: true,
                            useBarCodeDetectorIfSupported: true
                        },
                        false
                    );
                    console.log('✅ HTML5 QR Code Scanner initialized');
                } catch (e) {
                    console.error('❌ Failed to initialize scanner:', e);
                    throw new Error('Failed to initialize scanner: ' + e.message);
                }

                // Start scanning with specific camera
                try {
                    if (html5QrcodeScanner) {
                        if (finalCameraId) {
                            html5QrcodeScanner.render(onScanSuccess, onScanFailure, {
                                cameraIdOrConfig: finalCameraId
                            });
                            console.log('✅ Scanner started with camera:', finalCameraId);
                        } else {
                            html5QrcodeScanner.render(onScanSuccess, onScanFailure);
                            console.log('✅ Scanner started without specific camera');
                        }
                    } else {
                        throw new Error('Scanner not initialized');
                    }
                } catch (e) {
                    console.log('Scanner failed, trying fallback:', e.message);
                    try {
                        // Fallback: start without specific camera
                        if (html5QrcodeScanner) {
                            html5QrcodeScanner.render(onScanSuccess, onScanFailure);
                            console.log('✅ Scanner started without specific camera');
                        } else {
                            throw new Error('Scanner not initialized for fallback');
                        }
                    } catch (e2) {
                        console.error('❌ All scanner attempts failed:', e2);
                        throw new Error('Failed to start scanner: ' + e2.message);
                    }
                }
                
                // Hide camera selection dialog completely
                setTimeout(() => {
                    try {
                        const cameraSelect = document.querySelector('#html5-qrcode-reader select');
                        if (cameraSelect && cameraSelect.style) {
                            cameraSelect.style.display = 'none';
                        }
                        
                        // Hide all text elements safely
                        const textElements = document.querySelectorAll('#html5-qrcode-reader *');
                        textElements.forEach(el => {
                            if (el && el.style && el.tagName !== 'VIDEO' && el.tagName !== 'CANVAS') {
                                el.style.display = 'none';
                            }
                        });
                    } catch (e) {
                        console.log('Error hiding camera dialog:', e);
                    }
                }, 100);
                
                cameraActive = true;
                document.getElementById('startCamera').style.display = 'none';
                document.getElementById('stopCamera').style.display = 'inline-block';
                
                showNotification('Scanner aktif. Arahkan QR Code ke area panduan.', 'success');
                console.log('✅ HTML5 QR Code Scanner started successfully');
                
            } catch (err) {
                console.error('❌ Scanner error:', err);
                showNotification('Gagal mengakses kamera: ' + err.message, 'error');
            }
        });

        // Stop HTML5 QR Code Scanner
        document.getElementById('stopCamera').addEventListener('click', function() {
            try {
                console.log('🛑 Stopping HTML5 QR Code Scanner...');
                
                if (html5QrcodeScanner) {
                    try {
                        html5QrcodeScanner.clear();
                        console.log('✅ Scanner cleared successfully');
                    } catch (e) {
                        console.log('Scanner clear failed:', e);
                    }
                    html5QrcodeScanner = null;
                }
                
                // Stop observer
                if (scannerObserver) {
                    try {
                        scannerObserver.disconnect();
                        console.log('✅ Observer disconnected');
                    } catch (e) {
                        console.log('Observer disconnect failed:', e);
                    }
                    scannerObserver = null;
                }
                
                cameraActive = false;
                
                // Reset UI elements safely
                const startBtn = document.getElementById('startCamera');
                const stopBtn = document.getElementById('stopCamera');
                const camera = document.getElementById('camera');
                const guide = document.getElementById('qr-guide');
                
                if (startBtn) startBtn.style.display = 'inline-block';
                if (stopBtn) stopBtn.style.display = 'none';
                if (guide) guide.style.display = 'none';
                
                if (camera) {
                    camera.innerHTML = `
                        <div class="text-center">
                            <i class="fas fa-camera text-4xl text-gray-400 mb-2"></i>
                            <p class="text-gray-600">Kamera akan aktif saat tombol start ditekan</p>
                        </div>
                    `;
                }
                    
                showNotification('Scanner dihentikan', 'info');
                console.log('✅ Scanner stopped successfully');
            } catch (err) {
                console.error('❌ Stop scanner error:', err);
            }
        });

        // HTML5 QR Code Scanner callbacks
        function onScanSuccess(decodedText, decodedResult) {
            console.log('✅ QR Code detected:', decodedText);
            addCapturedPhoto(decodedText, true); // true indicates it's a decoded QR text
            showNotification('QR Code berhasil dideteksi: ' + decodedText, 'success');
        }

        function onScanFailure(error) {
            // Handle scan failure silently - this is called frequently during scanning
            // Only log significant errors
            if (error && !error.includes('NotFoundException')) {
                console.log('QR Code scan attempt:', error);
            }
        }

        
        // REMOVED COMPLEX QR DETECTION - Using simple photo capture instead
        
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
        
        // SIMPLE PHOTO CAPTURE - Just take photo, no QR detection

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
        function addCapturedPhoto(data, isQRText = false) {
            const currentTime = new Date().toLocaleTimeString('id-ID', { 
                hour12: false, 
                timeZone: 'Asia/Jakarta' 
            });
            
            if (isQRText) {
                // QR text detected by scanner
                const qrRecord = {
                    qrText: data,
                    captureTime: currentTime,
                    timestamp: Date.now(),
                    id: 'qr_' + Date.now(),
                    isQRText: true
                };
                capturedStudents.push(qrRecord);
                showNotification('QR Code berhasil dideteksi: ' + data, 'success');
            } else {
                // Photo captured (fallback method)
                const photoRecord = {
                    imageData: data,
                    captureTime: currentTime,
                    timestamp: Date.now(),
                    id: 'photo_' + Date.now(),
                    isQRText: false
                };
                capturedStudents.push(photoRecord);
                showNotification('Foto berhasil diambil! QR akan diproses saat synchronize.', 'success');
            }
            
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
                } else if (record.isQRText) {
                    // QR Text detected by scanner
                    html += `
                        <div class="px-3 py-2 border-b hover:bg-gray-50">
                            <div class="grid grid-cols-12 gap-2 items-center text-sm">
                                <div class="col-span-1 text-gray-600">${index + 1}</div>
                                <div class="col-span-3">
                                    <div class="w-12 h-12 bg-blue-100 rounded border flex items-center justify-center">
                                        <i class="fas fa-qrcode text-blue-600"></i>
                                    </div>
                                </div>
                                <div class="col-span-5 text-gray-700">
                                    <span class="text-blue-600">QR Terdeteksi</span>
                                    <br><small class="text-gray-500">${record.qrText}</small>
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
                    input.value = record.qrText;
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