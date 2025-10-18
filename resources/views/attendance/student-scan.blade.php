@extends('layouts.app')

@section('title', 'Scan Siswa - Presensia')

@section('content')
<!-- Mobile Full Screen Layout -->
<div class="mobile-scanner-container">
    <!-- Camera Section - Full Screen on Mobile -->
    <div class="camera-section">
        <div class="camera-header">
            <h1 class="text-xl font-bold text-white">Scan QR Code Siswa</h1>
            <div class="camera-controls">
                <button id="startCamera" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-qrcode mr-2"></i>Mulai Scan QR Code
                </button>
                <button id="stopCamera" class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition-colors" style="display: none;">
                    <i class="fas fa-stop mr-2"></i>Stop Scan
                </button>
            </div>
        </div>

        <!-- Camera Preview -->
        <div id="camera-container" class="camera-preview">
            <div id="camera" class="w-full h-full bg-gray-100 flex items-center justify-center relative overflow-hidden">
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
    </div>
    
    <!-- Students List Section - Swipeable -->
    <div class="students-section">
        <div class="students-header">
            <h2 class="text-lg font-medium text-gray-900">Daftar Siswa yang Sudah Diabsensi</h2>
            <div class="swipe-indicator">
                <i class="fas fa-chevron-up text-gray-400"></i>
                <span class="text-sm text-gray-500">Swipe ke atas untuk melihat daftar</span>
            </div>
        </div>
        
        <div class="students-content">
            <!-- Manual Input -->
            <div class="mb-4">
                <label for="manual_qr" class="block text-sm font-medium text-gray-700">Input Manual QR Code</label>

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
    
    <!-- Mobile Full Screen Layout CSS -->
    <style>
        /* Mobile Full Screen Layout */
        .mobile-scanner-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
        }
        
        .camera-section {
            flex: 1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            flex-direction: column;
            position: relative;
        }
        
        .camera-header {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            z-index: 10;
            background: rgba(0, 0, 0, 0.3);
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .camera-header h1 {
            color: white;
            font-size: 18px;
            font-weight: 600;
        }
        
        .camera-controls {
            display: flex;
            gap: 10px;
        }
        
        .camera-preview {
            flex: 1;
            position: relative;
            overflow: hidden;
        }
        
        .students-section {
            background: white;
            border-top-left-radius: 20px;
            border-top-right-radius: 20px;
            padding: 20px;
            max-height: 50vh;
            overflow-y: auto;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);
            transform: translateY(100%);
            transition: transform 0.3s ease;
        }
        
        .students-section.show {
            transform: translateY(0);
        }
        
        .students-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .swipe-indicator {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
        }
        
        .swipe-indicator i {
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }
        
        /* Desktop responsive */
        @media (min-width: 769px) {
            .mobile-scanner-container {
                flex-direction: row;
                height: auto;
            }
            
            .camera-section {
                flex: 1;
                min-height: 500px;
            }
            
            .students-section {
                flex: 1;
                max-height: none;
                transform: none;
                border-radius: 0;
                box-shadow: none;
            }
        }
        
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
        
        
        /* Hide camera selection popup after permission granted */
        /* Maximize camera area for mobile */
        #html5-qrcode-reader {
            width: 100% !important;
            height: 100% !important;
            max-width: 100vw !important;
            max-height: 100vh !important;
        }
        
        #html5-qrcode-reader video {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
        }
        
        /* Custom UI for permission request */
        #html5-qrcode-reader div[style*="background-color: rgba"] {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            color: white !important;
            border-radius: 12px !important;
            padding: 20px !important;
            text-align: center !important;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3) !important;
        }
        
        #html5-qrcode-reader div[style*="background-color: rgba"] h3 {
            color: white !important;
            font-size: 18px !important;
            font-weight: 600 !important;
            margin-bottom: 10px !important;
        }
        
        #html5-qrcode-reader div[style*="background-color: rgba"] p {
            color: rgba(255,255,255,0.9) !important;
            font-size: 14px !important;
            margin-bottom: 15px !important;
        }
        
        /* Custom UI for camera selection */
        #html5-qrcode-reader select {
            background: white !important;
            border: 2px solid #667eea !important;
            border-radius: 8px !important;
            padding: 10px 15px !important;
            font-size: 14px !important;
            color: #333 !important;
            margin-bottom: 15px !important;
            width: 100% !important;
            max-width: 300px !important;
        }
        
        #html5-qrcode-reader button[data-camera-id] {
            background: #667eea !important;
            color: white !important;
            border: none !important;
            border-radius: 8px !important;
            padding: 12px 24px !important;
            font-size: 14px !important;
            font-weight: 600 !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
            width: 100% !important;
            max-width: 300px !important;
        }
        
        #html5-qrcode-reader button[data-camera-id]:hover {
            background: #5a6fd8 !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4) !important;
        }
        
        /* Mobile responsive */
        @media (max-width: 768px) {
            #html5-qrcode-reader {
                height: 70vh !important;
            }
            
            #html5-qrcode-reader video {
                height: 70vh !important;
            }
        }
    </style>
    <script>
        let capturedStudents = [];
        let html5QrcodeScanner = null;
        let cameraActive = false;

        // Start camera with original template
        document.getElementById('startCamera').addEventListener('click', function() {
            try {
                console.log('🎥 Starting scanner...');
                
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
                
                // Initialize scanner with original template
                html5QrcodeScanner = new Html5QrcodeScanner(
                    "html5-qrcode-reader",
                    {
                        fps: 10,
                        qrbox: function(viewfinderWidth, viewfinderHeight) {
                            // Maximize camera area for mobile
                            const minDimension = Math.min(viewfinderWidth, viewfinderHeight);
                            return {
                                width: Math.floor(minDimension * 0.8),
                                height: Math.floor(minDimension * 0.8)
                            };
                        },
                        rememberLastUsedCamera: true,
                        supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA],
                        showTorchButtonIfSupported: true,
                        useBarCodeDetectorIfSupported: true
                    },
                    false
                );
                
                // Render scanner
                html5QrcodeScanner.render(onScanSuccess, onScanFailure);
                
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
        
        // Swipe gesture handling
        let startY = 0;
        let currentY = 0;
        let isDragging = false;
        
        const studentsSection = document.querySelector('.students-section');
        
        // Touch events for swipe
        studentsSection.addEventListener('touchstart', function(e) {
            startY = e.touches[0].clientY;
            isDragging = true;
        });
        
        studentsSection.addEventListener('touchmove', function(e) {
            if (!isDragging) return;
            
            currentY = e.touches[0].clientY;
            const deltaY = startY - currentY;
            
            if (deltaY > 0) {
                // Swipe up - show students section
                studentsSection.classList.add('show');
            } else if (deltaY < -50) {
                // Swipe down - hide students section
                studentsSection.classList.remove('show');
            }
        });
        
        studentsSection.addEventListener('touchend', function(e) {
            isDragging = false;
        });
        
        // Mouse events for desktop
        studentsSection.addEventListener('mousedown', function(e) {
            startY = e.clientY;
            isDragging = true;
        });
        
        studentsSection.addEventListener('mousemove', function(e) {
            if (!isDragging) return;
            
            currentY = e.clientY;
            const deltaY = startY - currentY;
            
            if (deltaY > 0) {
                studentsSection.classList.add('show');
            } else if (deltaY < -50) {
                studentsSection.classList.remove('show');
            }
        });
        
        studentsSection.addEventListener('mouseup', function(e) {
            isDragging = false;
        });
        
        // Auto show students section when students are added
        function showStudentsSection() {
            studentsSection.classList.add('show');
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
            
            // Auto show students section when students are added
            showStudentsSection();
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
