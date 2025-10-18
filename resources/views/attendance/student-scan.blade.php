@extends('layouts.app')

@section('title', 'Scan Siswa - Presensia')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h1 class="text-2xl font-bold text-gray-900 mb-6">Scan QR Code Siswa</h1>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Camera Capture -->
                    <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                            <h2 class="text-lg font-medium text-gray-900 mb-4">Capture QR Code</h2>
                            
                            <!-- Camera Preview -->
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

                    <!-- Manual Input -->
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
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Siswa yang Sudah Diabsensi</h3>
                            <div class="flex justify-between items-center mb-4">
                                <span class="text-sm text-gray-600">Record Absensi</span>
                                <span class="text-sm font-medium text-gray-900" id="studentCount">0 siswa</span>
                            </div>
                            
                            <div id="capturedList" class="space-y-2 min-h-32">
                                <div class="text-center text-gray-500 py-8">
                                    <i class="fas fa-users text-2xl mb-2"></i>
                                    <p>Belum ada siswa yang diabsensi</p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                            <div class="mt-6 flex space-x-3">
                                <button id="syncButton" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors flex items-center">
                                    <i class="fas fa-sync-alt mr-2"></i>Synchronize
                        </button>
                                <button id="clearAllButton" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors flex items-center">
                            <i class="fas fa-trash mr-2"></i>Clear All
                        </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Synchronize Form -->
<form id="syncForm" method="POST" action="{{ route('attendance.scan-student') }}" style="display: none;">
    @csrf
    <div id="hiddenInputs"></div>
</form>
@endsection

@push('scripts')
    <!-- HTML5 QR Code Scanner Library -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    
    <!-- Simple CSS for camera UI -->
    <style>
        /* Camera container styling */
        #html5-qrcode-reader {
            width: 100% !important;
            height: 100% !important;
        }
        
        /* Video element styling */
        #html5-qrcode-reader video {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            border-radius: 4px !important;
        }
        
        
        /* Hide camera selection popup completely */
        #html5-qrcode-reader div[style*="position: absolute"],
        #html5-qrcode-reader div[style*="background-color: rgba"],
        #html5-qrcode-reader div[style*="z-index"] {
            display: none !important;
        }
        
        /* Hide camera selection dropdown */
        #html5-qrcode-reader select {
            display: none !important;
        }
        
        /* Hide camera selection buttons */
        #html5-qrcode-reader button[data-camera-id] {
            display: none !important;
        }
        
        /* Hide all camera selection UI elements */
        #html5-qrcode-reader div:not(:has(video)) {
            display: none !important;
        }
        
        /* Simple permission message */
        .camera-permission-message {
            background: #f8f9fa;
            color: #495057;
            padding: 20px;
            border-radius: 4px;
            text-align: center;
            border: 1px solid #dee2e6;
        }
        
        .camera-permission-message h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
            font-weight: 500;
        }
        
        .camera-permission-message p {
            margin: 0;
            font-size: 14px;
        }
    </style>
    <script>
        let capturedStudents = [];
        let html5QrcodeScanner = null;
        let cameraActive = false;

        // Start camera with back camera forced
        document.getElementById('startCamera').addEventListener('click', async function() {
            try {
                console.log('🎥 Starting scanner with back camera...');
                
                const camera = document.getElementById('camera');
                const guide = document.getElementById('qr-guide');
                
                if (!camera) {
                    throw new Error('Camera element not found');
                }
                
                // Clear previous content
                camera.innerHTML = `
                    <div id="html5-qrcode-reader">
                        <div class="camera-permission-message">
                            <h3>Mengaktifkan Kamera Belakang</h3>
                            <p>Mohon izinkan akses kamera</p>
                        </div>
                    </div>
                `;
                if (guide) {
                    guide.style.display = 'block';
                }
                
                // Initialize scanner with back camera forced
                html5QrcodeScanner = new Html5QrcodeScanner(
                    "html5-qrcode-reader",
                    {
                        fps: 10,
                        qrbox: { width: 400, height: 400 },
                        rememberLastUsedCamera: false,
                        supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA],
                        showTorchButtonIfSupported: true,
                        useBarCodeDetectorIfSupported: true
                    },
                    false
                );
                
                // Start scanning with back camera
                html5QrcodeScanner.render(onScanSuccess, onScanFailure, {
                    facingMode: "environment" // Force back camera
                });
                
                // Hide camera selection popup after a short delay
                setTimeout(() => {
                    const popups = document.querySelectorAll('#html5-qrcode-reader div[style*="position: absolute"]');
                    popups.forEach(popup => {
                        popup.style.display = 'none';
                    });
                    
                    const selects = document.querySelectorAll('#html5-qrcode-reader select');
                    selects.forEach(select => {
                        select.style.display = 'none';
                    });
                    
                    const buttons = document.querySelectorAll('#html5-qrcode-reader button[data-camera-id]');
                    buttons.forEach(button => {
                        button.style.display = 'none';
                    });
                }, 100);
                
                cameraActive = true;
                document.getElementById('startCamera').style.display = 'none';
                document.getElementById('stopCamera').style.display = 'inline-block';
                
                showNotification('Scanner aktif dengan kamera belakang. Arahkan QR Code ke area panduan.', 'success');
                console.log('✅ HTML5 QR Code Scanner started with back camera');
                
            } catch (err) {
                console.error('❌ Scanner error:', err);
                showNotification('Gagal mengakses kamera belakang: ' + err.message, 'error');
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

        // Add captured photo/QR to list
        function addCapturedPhoto(data, isQRText = false) {
            const currentTime = new Date().toLocaleTimeString('id-ID', { 
                hour12: false, 
                timeZone: 'Asia/Jakarta' 
            });
            
            const record = {
                id: Date.now(),
                qrCode: data,
                timestamp: currentTime,
                isQRText: isQRText,
                isManual: false
            };
            
            capturedStudents.push(record);
            updateCapturedList();
            showNotification('Siswa berhasil ditambahkan: ' + data, 'success');
        }

        // Update captured list display
        function updateCapturedList() {
            const listContainer = document.getElementById('capturedList');
            const studentCount = document.getElementById('studentCount');
            
            studentCount.textContent = `${capturedStudents.length} siswa`;
            
            if (capturedStudents.length === 0) {
                listContainer.innerHTML = `
                    <div class="text-center text-gray-500 py-8">
                        <i class="fas fa-users text-2xl mb-2"></i>
                        <p>Belum ada siswa yang diabsensi</p>
                    </div>
                `;
                return;
            }
            
            let html = '';
            capturedStudents.forEach((record, index) => {
                if (record.isQRText) {
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
                                <div class="col-span-6">
                                    <div class="font-medium text-gray-900">${record.qrCode}</div>
                                    <div class="text-xs text-gray-500">${record.timestamp}</div>
                                </div>
                                <div class="col-span-2 text-right">
                                    <button onclick="removeStudent(${record.id})" class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                    </div>
                </div>
            `;
                } else {
                    // Manual input
                html += `
                    <div class="px-3 py-2 border-b hover:bg-gray-50">
                        <div class="grid grid-cols-12 gap-2 items-center text-sm">
                            <div class="col-span-1 text-gray-600">${index + 1}</div>
                                <div class="col-span-3">
                                    <div class="w-12 h-12 bg-green-100 rounded border flex items-center justify-center">
                                        <i class="fas fa-keyboard text-green-600"></i>
                                    </div>
                                </div>
                                <div class="col-span-6">
                                    <div class="font-medium text-gray-900">${record.qrCode}</div>
                                    <div class="text-xs text-gray-500">${record.timestamp}</div>
                                </div>
                                <div class="col-span-2 text-right">
                                    <button onclick="removeStudent(${record.id})" class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                }
            });
            
            listContainer.innerHTML = html;
        }

        // Remove student from list
        function removeStudent(id) {
            capturedStudents = capturedStudents.filter(record => record.id !== id);
            updateCapturedList();
            showNotification('Siswa dihapus dari list', 'info');
        }

        // Manual input
        document.getElementById('addManual').addEventListener('click', function() {
            const manualQr = document.getElementById('manual_qr').value.trim();
            if (manualQr) {
                addCapturedPhoto(manualQr, true);
                document.getElementById('manual_qr').value = '';
            } else {
                showNotification('Masukkan QR Code terlebih dahulu', 'warning');
            }
        });

        // Quick add student
        function quickAddStudent(qrCode) {
            addCapturedPhoto(qrCode, true);
        }

        // Test QR detection
        function testQRDetection() {
            const testQR = 'SISWA999|Test Student';
            addCapturedPhoto(testQR, true);
        }

        // Synchronize button
        document.getElementById('syncButton').addEventListener('click', function() {
            if (capturedStudents.length === 0) {
                showNotification('Tidak ada siswa untuk disinkronkan', 'warning');
                return;
            }
            
            // Prepare hidden inputs
            const hiddenInputs = document.getElementById('hiddenInputs');
            hiddenInputs.innerHTML = '';
            
            capturedStudents.forEach(record => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'qr_codes[]';
                input.value = record.qrCode;
                hiddenInputs.appendChild(input);
            });
            
            // Submit form
            document.getElementById('syncForm').submit();
        });

        // Clear all button
        document.getElementById('clearAllButton').addEventListener('click', function() {
            if (capturedStudents.length === 0) {
                showNotification('Tidak ada data untuk dihapus', 'info');
                return;
            }
            
            if (confirm(`Apakah Anda yakin ingin menghapus semua ${capturedStudents.length} record?`)) {
                capturedStudents = [];
                updateCapturedList();
                showNotification('Semua record telah dihapus', 'info');
            }
        });

        // Notification function
        function showNotification(message, type = 'info') {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
                type === 'success' ? 'bg-green-500 text-white' :
                type === 'error' ? 'bg-red-500 text-white' :
                type === 'warning' ? 'bg-yellow-500 text-white' :
                'bg-blue-500 text-white'
            }`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            // Remove after 3 seconds
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 3000);
        }
    </script>
@endpush
