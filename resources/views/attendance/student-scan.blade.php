@extends('layouts.app')

@section('title', 'Scan Siswa - Presensia')

@section('content')
<!-- Device Detection Notice for Web -->
<div id="web-notice" class="hidden">
    <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
        <div class="max-w-sm w-full bg-white rounded-lg shadow-md p-6 text-center">
            <div class="mb-4">
                <i class="fas fa-mobile-alt text-4xl text-blue-600 mb-3"></i>
                <h1 class="text-xl font-bold text-gray-900 mb-2">Scan Siswa</h1>
                <p class="text-gray-600 text-sm">Fitur scan QR Code siswa hanya dapat diakses melalui Mobile Platform</p>
            </div>
            
            <a href="{{ route('attendance.index') }}" class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition-colors inline-block text-sm">
                <i class="fas fa-arrow-left mr-2"></i>Kembali ke Dashboard
            </a>
        </div>
    </div>
</div>

<!-- Original Content (Hidden on Web) -->
<div id="mobile-content">
<!-- Mobile Full Screen Layout -->
<div class="mobile-scanner-container">
    <!-- Camera Section - Full Screen on Mobile -->
    <div class="camera-section">
        <div class="camera-header">
            <div class="camera-header-left">
            <h1 class="text-xl font-bold text-white">Scan QR Code Siswa</h1>
                <p class="text-sm text-white/80">Arahkan kamera ke QR Code siswa</p>
                    </div>
            <div class="camera-controls">
                <button id="startCamera" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center">
                    <i class="fas fa-qrcode mr-2"></i>Mulai Scan
                </button>
                <button id="stopCamera" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors flex items-center" style="display: none;">
                    <i class="fas fa-stop mr-2"></i>Stop
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
            <div class="students-header-left">
                <h2 class="text-lg font-semibold text-gray-900">Daftar Absensi Siswa</h2>
                <div class="swipe-indicator">
                    <i class="fas fa-chevron-up text-gray-400"></i>
                    <span class="text-sm text-gray-500">Swipe ke atas untuk melihat daftar</span>
                </div>
            </div>
            <div class="students-header-right">
                <div class="record-count" id="recordCount">0 Record</div>
                <button id="syncButton" class="sync-button">
                    <i class="fas fa-sync-alt"></i>
                    Sync
                </button>
            </div>
        </div>
        
        <div class="students-content">
            <!-- Manual Input Section -->
            <div class="manual-input-section">
                <div class="manual-input-header">
                    <h3 class="text-sm font-medium text-gray-700">Input Manual</h3>
                    <p class="text-xs text-gray-500">Masukkan QR Code secara manual</p>
                    </div>


                <!-- Manual Input Form -->
                <div class="manual-input-form">
                    <div class="input-group">
                                    <input type="text" id="manual_qr" placeholder="Masukkan QR Code siswa (NIS|Nama)"
                               class="manual-input">
                        <button id="addManual" class="add-btn">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                                
                                <!-- Help Text -->
                    <div class="help-text">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Format: NIS|Nama (contoh: SISWA001|John Doe)
                    </div>
                </div>
            </div>

            <!-- Captured Students List -->
            <div class="captured-students-section">
                <div class="captured-header">
                    <h3 class="text-lg font-semibold text-gray-900">Siswa yang Sudah Diabsensi</h3>
                    <div class="student-count">
                        <span class="text-sm text-gray-600">Total:</span>
                        <span class="text-sm font-semibold text-gray-900" id="studentCount">0 siswa</span>
                        </div>
                            </div>
                            
                <div id="capturedList" class="captured-list">
                    <div class="empty-state">
                        <i class="fas fa-users text-3xl text-gray-300 mb-3"></i>
                        <p class="text-gray-500">Belum ada siswa yang diabsensi</p>
                        <p class="text-xs text-gray-400 mt-1">Gunakan kamera atau input manual</p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                <div class="action-buttons">
                    <button id="syncButtonBottom" class="action-btn sync-btn">
                                    <i class="fas fa-sync-alt mr-2"></i>Synchronize
                        </button>
                    <button id="clearAllButton" class="action-btn clear-btn">
                            <i class="fas fa-trash mr-2"></i>Clear All
                        </button>
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
</div>
@endsection

@push('scripts')
    <!-- HTML5 QR Code Scanner Library -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    
    <!-- Handle session notifications -->
    @if(session('sync_result'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const syncResult = @json(session('sync_result'));
                
                // Show enhanced notification with detailed counts
                showSyncNotification(
                    syncResult.success_count,
                    syncResult.duplicate_count,
                    syncResult.error_count,
                    syncResult.total_count
                );
                
                // Clear the session data after displaying
                fetch('{{ route("attendance.clear-sync-result") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                });
            });
        </script>
    @endif
    
    <!-- Mobile Full Screen Layout CSS -->
    <style>
        /* Mobile Full Screen Layout */
        .mobile-scanner-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
            touch-action: pan-y;
        }
        
        /* Prevent page refresh on swipe */
        body {
            overscroll-behavior: none;
            touch-action: pan-y;
        }
        
        .camera-section {
            flex: 1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            flex-direction: column;
            position: relative;
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            min-height: 100vh;
        }
        
        .camera-section.swiped-up {
            transform: translateY(-60%);
        }
        
        .camera-header {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            z-index: 10;
            background: linear-gradient(180deg, rgba(0, 0, 0, 0.6) 0%, rgba(0, 0, 0, 0.2) 50%, transparent 100%);
            padding: 20px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            backdrop-filter: blur(10px);
        }
        
        .camera-header-left {
            flex: 1;
        }
        
        .camera-header-left h1 {
            margin-bottom: 4px;
        }
        
        .camera-header-left p {
            font-size: 14px;
            opacity: 0.8;
        }
        
        .camera-header h1 {
            color: white;
            font-size: 20px;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .camera-controls {
            display: flex;
            gap: 12px;
        }
        
        .camera-controls button {
            padding: 12px 20px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 14px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }
        
        .camera-controls button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3);
        }
        
        .camera-preview {
            flex: 1;
            position: relative;
            overflow: hidden;
            height: 100vh;
            width: 100%;
        }
        
        .students-section {
            background: white;
            border-top-left-radius: 24px;
            border-top-right-radius: 24px;
            padding: 24px;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 0 -8px 32px rgba(0, 0, 0, 0.15);
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            transform: translateY(100%);
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            backdrop-filter: blur(10px);
        }
        
        .students-section.show {
            transform: translateY(0);
        }
        
        .students-content {
            flex: 1;
            overflow-y: auto;
            min-height: 0;
            display: flex;
            flex-direction: column;
        }
        
        .students-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 20px;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .students-header-left {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .students-header-left h2 {
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }
        
        .students-header-right {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 12px;
        }
        
        .sync-button {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
        
        .sync-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
        }
        
        .record-count {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
        }
        
        .swipe-indicator {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            opacity: 0.7;
        }
        
        .swipe-indicator i {
            animation: bounce 2s infinite;
            color: #64748b;
        }
        
        .swipe-indicator span {
            font-size: 12px;
            color: #64748b;
            font-weight: 500;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-8px);
            }
            60% {
                transform: translateY(-4px);
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
                height: 100vh;
                transform: none;
                border-radius: 0;
                box-shadow: none;
                padding: 32px;
                overflow-y: auto;
                position: relative;
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
            position: relative !important;
        }
        
        #html5-qrcode-reader video {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
        }
        
        /* Ensure camera container takes full space */
        #camera {
            width: 100% !important;
            height: 100% !important;
            min-height: 100vh !important;
        }
        
        /* Use default QR code scanning area styling - let library handle it */
        
        /* Camera permission overlay */
        .camera-permission-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        
        .permission-content {
            background: white;
            padding: 32px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            max-width: 300px;
            width: 90%;
        }
        
        .permission-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
        }
        
        .permission-buttons button {
            transition: all 0.2s ease;
        }
        
        .permission-buttons button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
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
        
        /* Hide camera selection dropdown completely */
        #html5-qrcode-reader select {
            display: none !important;
        }
        
        #html5-qrcode-reader button[data-camera-id] {
            display: none !important;
        }
        
        /* Hide camera selection UI elements */
        #html5-qrcode-reader div[style*="position: absolute"]:not(:has(video)) {
            display: none !important;
        }
        
        #html5-qrcode-reader div[style*="background-color: rgba"]:not(:has(video)) {
            display: none !important;
        }
        
        #html5-qrcode-reader div[style*="z-index"]:not(:has(video)) {
            display: none !important;
        }
        
        /* Hide any camera selection text or dropdown */
        #html5-qrcode-reader div:not(:has(video)):not(.camera-permission-message) {
            display: none !important;
        }
        
        /* Manual Input Section */
        .manual-input-section {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
            border: 1px solid #e2e8f0;
        }
        
        .manual-input-header {
            margin-bottom: 16px;
        }
        
        
        .manual-input-form {
            margin-top: 16px;
        }
        
        .input-group {
            display: flex;
            gap: 8px;
            margin-bottom: 8px;
        }
        
        .manual-input {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s ease;
        }
        
        .manual-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .add-btn {
            background: #10b981;
            color: white;
            border: none;
            padding: 12px 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 48px;
        }
        
        .add-btn:hover {
            background: #059669;
            transform: translateY(-1px);
        }
        
        .help-text {
            font-size: 12px;
            color: #6b7280;
            display: flex;
            align-items: center;
        }
        
        /* Captured Students Section */
        .captured-students-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #e2e8f0;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 0;
        }
        
        .captured-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .student-count {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .captured-list {
            flex: 1;
            overflow-y: auto;
            min-height: 200px;
        }
        
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 40px 20px;
        }
        
        .student-item {
            display: flex;
            align-items: center;
            padding: 12px;
            border-bottom: 1px solid #f1f5f9;
            transition: background-color 0.2s ease;
        }
        
        .student-item:hover {
            background-color: #f8fafc;
        }
        
        .student-item:last-child {
            border-bottom: none;
        }
        
        .student-avatar {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
        }
        
        .student-info {
            flex: 1;
        }
        
        .student-name {
            font-weight: 500;
            color: #1f2937;
            margin-bottom: 2px;
        }
        
        .student-time {
            font-size: 12px;
            color: #6b7280;
        }
        
        .student-actions {
            display: flex;
            gap: 8px;
        }
        
        .remove-btn {
            background: #fef2f2;
            color: #dc2626;
            border: none;
            padding: 6px 8px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .remove-btn:hover {
            background: #fee2e2;
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 12px;
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid #f1f5f9;
        }
        
        .action-btn {
            flex: 1;
            padding: 12px 16px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .sync-btn {
            background: #10b981;
            color: white;
            border: none;
        }
        
        .sync-btn:hover {
            background: #059669;
            transform: translateY(-1px);
        }
        
        .clear-btn {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        
        .clear-btn:hover {
            background: #fee2e2;
            transform: translateY(-1px);
        }
        
        /* Mobile responsive */
        @media (max-width: 768px) {
            #html5-qrcode-reader {
                height: 70vh !important;
            }
            
            #html5-qrcode-reader video {
                height: 70vh !important;
            }
            
            .manual-input-section {
                padding: 16px;
            }
            
            .captured-students-section {
                padding: 16px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
    <script>
        let capturedStudents = [];
        let html5QrcodeScanner = null;
        let cameraActive = false;
        let html5Qrcode = null;

        // Function to start camera with permission
        function startCameraWithPermission() {
            try {
                console.log('🎥 Starting camera with permission...');
                
                // Hide permission overlay
                const permissionOverlay = document.getElementById('camera-permission-overlay');
                if (permissionOverlay) {
                    permissionOverlay.style.display = 'none';
                }
                
                // Use Html5Qrcode directly (simpler approach)
                html5Qrcode = new Html5Qrcode("html5-qrcode-reader");
                const config = {
                    fps: 10,
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: 1.0
                };
                
                console.log('🎥 Requesting camera access...');
                
                html5Qrcode.start(
                    { facingMode: "environment" },
                    config,
                    onScanSuccess,
                    onScanFailure
                ).then(() => {
                    console.log('✅ Camera started successfully');
                    cameraActive = true;
                    document.getElementById('startCamera').style.display = 'none';
                    document.getElementById('stopCamera').style.display = 'inline-block';
                    showNotification('Scanner aktif dengan kamera belakang. Arahkan QR Code ke area panduan.', 'success');
                }).catch((error) => {
                    console.error('❌ Camera start failed:', error);
                    showNotification('Gagal mengakses kamera: ' + error.message, 'error');
                    
                    // Show fallback message
                    const camera = document.getElementById('camera');
                    if (camera) {
                        camera.innerHTML = `
                            <div class="text-center p-8">
                                <i class="fas fa-camera-slash text-4xl text-red-400 mb-4"></i>
                                <h3 class="text-lg font-semibold text-gray-800 mb-2">Kamera Tidak Dapat Diakses</h3>
                                <p class="text-gray-600 mb-4">Error: ${error.message}</p>
                                <button onclick="location.reload()" class="bg-blue-500 text-white px-4 py-2 rounded-lg">
                                    Coba Lagi
                                </button>
                            </div>
                        `;
                    }
                });
                
            } catch (err) {
                console.error('❌ Camera permission error:', err);
                showNotification('Gagal mengakses kamera belakang: ' + err.message, 'error');
                
                // Show fallback message for mobile
                const camera = document.getElementById('camera');
                if (camera) {
                    camera.innerHTML = `
                        <div class="text-center p-8">
                            <i class="fas fa-camera-slash text-4xl text-red-400 mb-4"></i>
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">Kamera Tidak Dapat Diakses</h3>
                            <p class="text-gray-600 mb-4">Pastikan browser memiliki izin akses kamera</p>
                            <button onclick="location.reload()" class="bg-blue-500 text-white px-4 py-2 rounded-lg">
                                Coba Lagi
                            </button>
                        </div>
                    `;
                }
            }
        }

        // Start camera with original template
        document.getElementById('startCamera').addEventListener('click', function() {
            try {
                console.log('🎥 Starting scanner...');
                
                // Check if Html5QrcodeScanner is available
                if (typeof Html5QrcodeScanner === 'undefined') {
                    throw new Error('Html5QrcodeScanner library not loaded');
                }
                
                console.log('✅ Html5QrcodeScanner library loaded');
                
                // Check if camera is supported
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    throw new Error('Camera tidak didukung di browser ini');
                }
                
                console.log('✅ Camera API supported');
                
                const camera = document.getElementById('camera');
                const guide = document.getElementById('qr-guide');
                
                if (!camera) {
                    throw new Error('Camera element not found');
                }
                
                // Clear previous content and show permission request
                camera.innerHTML = `
                    <div id="html5-qrcode-reader"></div>
                    <div id="camera-permission-overlay" class="camera-permission-overlay">
                        <div class="permission-content">
                            <i class="fas fa-camera text-4xl text-blue-500 mb-4"></i>
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">Izin Akses Kamera</h3>
                            <p class="text-gray-600 mb-4">Browser memerlukan izin untuk mengakses kamera belakang</p>
                            <div class="permission-buttons">
                                <button id="requestPermission" class="bg-blue-500 text-white px-6 py-2 rounded-lg mr-2">
                                    Berikan Izin
                                </button>
                                <button id="cancelPermission" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg">
                                    Batal
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                
                if (guide) {
                    guide.style.display = 'block';
                }
                
                // Add event listeners for permission buttons
                document.getElementById('requestPermission').addEventListener('click', function() {
                    console.log('🎥 User requested camera permission');
                    startCameraWithPermission();
                });
                
                document.getElementById('cancelPermission').addEventListener('click', function() {
                    console.log('❌ User cancelled camera permission');
                    // Reset UI
                    document.getElementById('startCamera').style.display = 'inline-block';
                    document.getElementById('stopCamera').style.display = 'none';
                    camera.innerHTML = `
                        <div class="text-center">
                            <i class="fas fa-camera text-4xl text-gray-400 mb-2"></i>
                            <p class="text-gray-600">Kamera akan aktif saat tombol start ditekan</p>
                        </div>
                    `;
                });
                
                // Initialize scanner with mobile-optimized config
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
                        rememberLastUsedCamera: false,
                        supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA],
                        showTorchButtonIfSupported: true,
                        useBarCodeDetectorIfSupported: true,
                        verbose: false,
                        // Force back camera for mobile
                        videoConstraints: {
                            facingMode: "environment"
                        }
                    },
                    false
                );
                
                // Render scanner with proper callback handling
                try {
                html5QrcodeScanner.render(onScanSuccess, onScanFailure);
                
                    // Use setTimeout to check if scanner is working
                    setTimeout(() => {
                        const videoElement = document.querySelector('#html5-qrcode-reader video');
                        if (videoElement && videoElement.videoWidth > 0) {
                            console.log('✅ Scanner rendered successfully');
                cameraActive = true;
                document.getElementById('startCamera').style.display = 'none';
                document.getElementById('stopCamera').style.display = 'inline-block';
                showNotification('Scanner aktif dengan kamera belakang. Arahkan QR Code ke area panduan.', 'success');
                        } else {
                            throw new Error('Camera tidak dapat diakses atau tidak ada video stream');
                        }
                    }, 2000);
                    
                } catch (renderError) {
                    console.error('❌ Scanner render failed:', renderError);
                    console.log('🔄 Trying fallback with Html5Qrcode...');
                    
                    // Fallback: Try with Html5Qrcode directly
                    try {
                        const html5Qrcode = new Html5Qrcode("html5-qrcode-reader");
                        const config = {
                            fps: 10,
                            qrbox: { width: 250, height: 250 },
                            aspectRatio: 1.0
                        };
                        
                        html5Qrcode.start(
                            { facingMode: "environment" },
                            config,
                            onScanSuccess,
                            onScanFailure
                        ).then(() => {
                            console.log('✅ Fallback scanner started successfully');
                            cameraActive = true;
                            document.getElementById('startCamera').style.display = 'none';
                            document.getElementById('stopCamera').style.display = 'inline-block';
                            showNotification('Scanner aktif dengan kamera belakang (fallback mode).', 'success');
                        }).catch((fallbackError) => {
                            console.error('❌ Fallback scanner also failed:', fallbackError);
                            showNotification('Gagal memulai scanner: ' + fallbackError.message, 'error');
                            // Reset UI
                            document.getElementById('startCamera').style.display = 'inline-block';
                            document.getElementById('stopCamera').style.display = 'none';
                        });
                        
                    } catch (fallbackError) {
                        console.error('❌ Fallback scanner failed:', fallbackError);
                        showNotification('Gagal memulai scanner: ' + fallbackError.message, 'error');
                        // Reset UI
                        document.getElementById('startCamera').style.display = 'inline-block';
                        document.getElementById('stopCamera').style.display = 'none';
                    }
                }
                
            } catch (err) {
                console.error('❌ Scanner error:', err);
                showNotification('Gagal mengakses kamera belakang: ' + err.message, 'error');
                
                // Show fallback message for mobile
                const camera = document.getElementById('camera');
                if (camera) {
                    camera.innerHTML = `
                        <div class="text-center p-8">
                            <i class="fas fa-camera-slash text-4xl text-red-400 mb-4"></i>
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">Kamera Tidak Dapat Diakses</h3>
                            <p class="text-gray-600 mb-4">Pastikan browser memiliki izin akses kamera</p>
                            <button onclick="location.reload()" class="bg-blue-500 text-white px-4 py-2 rounded-lg">
                                Coba Lagi
                            </button>
                        </div>
                    `;
                }
            }
        });


        // Stop scanner
        document.getElementById('stopCamera').addEventListener('click', function() {
            try {
                // Stop Html5Qrcode if active
                if (html5Qrcode) {
                    html5Qrcode.stop().then(() => {
                        console.log('✅ Camera stopped successfully');
                        html5Qrcode = null;
                    }).catch((err) => {
                        console.log('⚠️ Camera stop error:', err);
                        html5Qrcode = null;
                    });
                }
                
                // Clear scanner if exists
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
            
            // Check if student already exists
            const existingStudent = capturedStudents.find(student => student.qrCode === decodedText);
            if (existingStudent) {
                console.log('⚠️ Siswa sudah ada dalam daftar:', decodedText);
                showNotification('Siswa sudah ada dalam daftar', 'warning');
                return;
            }
            
            addCapturedPhoto(decodedText, true);
        }
        
        // Smooth swipe gesture handling
        let startY = 0;
        let currentY = 0;
        let isDragging = false;
        let isStudentsSectionVisible = false;
        
        const studentsSection = document.querySelector('.students-section');
        const cameraSection = document.querySelector('.camera-section');
        
        // Touch events for swipe on camera section
        cameraSection.addEventListener('touchstart', function(e) {
            startY = e.touches[0].clientY;
            isDragging = true;
        });
        
        cameraSection.addEventListener('touchmove', function(e) {
            if (!isDragging) return;
            
            currentY = e.touches[0].clientY;
            const deltaY = startY - currentY;
            
            if (deltaY > 30) {
                // Swipe up - show students section with smooth animation
                if (!isStudentsSectionVisible) {
                    e.preventDefault(); // Prevent default browser behavior
                    showStudentsSectionSmooth();
                }
            }
        });
        
        cameraSection.addEventListener('touchend', function(e) {
            isDragging = false;
        });
        
        // Touch events for swipe on students section
        studentsSection.addEventListener('touchstart', function(e) {
            startY = e.touches[0].clientY;
            isDragging = true;
        });
        
        studentsSection.addEventListener('touchmove', function(e) {
            if (!isDragging) return;
            
            currentY = e.touches[0].clientY;
            const deltaY = startY - currentY;
            
            if (deltaY < -30) {
                // Swipe down - hide students section with smooth animation
                if (isStudentsSectionVisible) {
                    e.preventDefault(); // Prevent default browser behavior
                    hideStudentsSectionSmooth();
                }
            }
        });
        
        studentsSection.addEventListener('touchend', function(e) {
            isDragging = false;
        });
        
        // Mouse events for desktop
        cameraSection.addEventListener('mousedown', function(e) {
            startY = e.clientY;
            isDragging = true;
        });
        
        cameraSection.addEventListener('mousemove', function(e) {
            if (!isDragging) return;
            
            currentY = e.clientY;
            const deltaY = startY - currentY;
            
            if (deltaY > 30) {
                if (!isStudentsSectionVisible) {
                    showStudentsSectionSmooth();
                }
            }
        });
        
        cameraSection.addEventListener('mouseup', function(e) {
            isDragging = false;
        });
        
        studentsSection.addEventListener('mousedown', function(e) {
            startY = e.clientY;
            isDragging = true;
        });
        
        studentsSection.addEventListener('mousemove', function(e) {
            if (!isDragging) return;
            
            currentY = e.clientY;
            const deltaY = startY - currentY;
            
            if (deltaY < -30) {
                if (isStudentsSectionVisible) {
                    hideStudentsSectionSmooth();
                }
            }
        });
        
        studentsSection.addEventListener('mouseup', function(e) {
            isDragging = false;
        });
        
        // Smooth show students section
        function showStudentsSectionSmooth() {
            studentsSection.classList.add('show');
            cameraSection.classList.add('swiped-up');
            isStudentsSectionVisible = true;
        }
        
        // Smooth hide students section
        function hideStudentsSectionSmooth() {
            studentsSection.classList.remove('show');
            cameraSection.classList.remove('swiped-up');
            isStudentsSectionVisible = false;
        }
        
        // Auto show students section when students are added
        function showStudentsSection() {
            showStudentsSectionSmooth();
        }
        
        // Update record count
        function updateRecordCount() {
            const recordCount = document.getElementById('recordCount');
            if (recordCount) {
                recordCount.textContent = `${capturedStudents.length} Record`;
            }
        }
        
        // Sync button event listener - REMOVED (duplicate with working one below)

        // QR Code scan failure callback
        function onScanFailure(error) {
            // Silent failure - don't show errors for every scan attempt
        }


        // Add captured photo/QR to list
        function addCapturedPhoto(data, isQRText = false) {
            // Check if student already exists in capturedStudents
            const existingStudent = capturedStudents.find(student => student.qrCode === data);
            if (existingStudent) {
                console.log('⚠️ Siswa sudah ada dalam daftar:', data);
                showNotification('Siswa sudah ada dalam daftar', 'warning');
                return;
            }
            
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
            
            // Format notification message
            let notificationMessage = 'Siswa berhasil ditambahkan: ' + data;
            if (data.includes('|')) {
                const parts = data.split('|');
                if (parts.length === 2) {
                    notificationMessage = `Siswa berhasil ditambahkan: ${parts[0]} - ${parts[1]}`;
                }
            }
            showNotification(notificationMessage, 'success');
            
            // Don't auto show students section - let user swipe manually
        }

        // Update captured list display
        function updateCapturedList() {
            const listContainer = document.getElementById('capturedList');
            const studentCount = document.getElementById('studentCount');
            
            studentCount.textContent = `${capturedStudents.length} siswa`;
            
            // Update record count
            updateRecordCount();
            
            if (capturedStudents.length === 0) {
                listContainer.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-users text-3xl text-gray-300 mb-3"></i>
                        <p class="text-gray-500">Belum ada siswa yang diabsensi</p>
                        <p class="text-xs text-gray-400 mt-1">Gunakan kamera atau input manual</p>
                    </div>
                `;
                return;
            }
            
            let html = '';
            capturedStudents.forEach((record, index) => {
                const isQR = record.isQRText;
                const avatarClass = isQR ? 'bg-blue-100 text-blue-600' : 'bg-green-100 text-green-600';
                const iconClass = isQR ? 'fas fa-qrcode' : 'fas fa-keyboard';
                
                // Format QR code data: "101112|Sahara" -> "101112 - Sahara"
                let displayName = record.qrCode;
                if (record.qrCode.includes('|')) {
                    const parts = record.qrCode.split('|');
                    if (parts.length === 2) {
                        displayName = `${parts[0]} - ${parts[1]}`;
                    }
                }
                
            html += `
                    <div class="student-item">
                        <div class="student-avatar ${avatarClass}">
                            <i class="${iconClass}"></i>
                                    </div>
                        <div class="student-info">
                            <div class="student-name">${displayName}</div>
                            <div class="student-time">${record.timestamp}</div>
                                </div>
                        <div class="student-actions">
                            <button onclick="removeStudent(${record.id})" class="remove-btn">
                                        <i class="fas fa-trash"></i>
                                    </button>
                    </div>
                </div>
            `;
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
                // Check if student already exists
                const existingStudent = capturedStudents.find(student => student.qrCode === manualQr);
                if (existingStudent) {
                    showNotification('Siswa sudah ada dalam daftar', 'warning');
                    document.getElementById('manual_qr').value = '';
                    return;
                }
                
                addCapturedPhoto(manualQr, true);
                document.getElementById('manual_qr').value = '';
            } else {
                showNotification('Masukkan QR Code terlebih dahulu', 'warning');
            }
        });


        // Synchronize button (top)
        document.getElementById('syncButton').addEventListener('click', function() {
            if (capturedStudents.length === 0) {
                showNotification('Tidak ada siswa untuk disinkronkan', 'warning');
                return;
            }
            
            // Show loading state
            const syncButton = this;
            const originalText = syncButton.innerHTML;
            syncButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyinkronkan...';
            syncButton.disabled = true;
            
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

        // Synchronize button (bottom)
        document.getElementById('syncButtonBottom').addEventListener('click', function() {
            if (capturedStudents.length === 0) {
                showNotification('Tidak ada siswa untuk disinkronkan', 'warning');
                return;
            }
            
            // Show loading state
            const syncButton = this;
            const originalText = syncButton.innerHTML;
            syncButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyinkronkan...';
            syncButton.disabled = true;
            
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

        // Enhanced notification function for sync results
        function showSyncNotification(successCount, duplicateCount, errorCount, totalCount) {
            let message = '';
            let type = 'info';
            
            if (successCount > 0 && errorCount === 0) {
                message = `✅ Berhasil: ${successCount} siswa`;
                if (duplicateCount > 0) {
                    message += ` | ⚠️ Duplikat: ${duplicateCount} siswa`;
                }
                type = 'success';
            } else if (successCount > 0 && errorCount > 0) {
                message = `✅ Berhasil: ${successCount} siswa | ❌ Gagal: ${errorCount} siswa`;
                if (duplicateCount > 0) {
                    message += ` | ⚠️ Duplikat: ${duplicateCount} siswa`;
                }
                type = 'warning';
            } else if (errorCount > 0) {
                message = `❌ Gagal: ${errorCount} siswa`;
                type = 'error';
            } else {
                message = `📊 Total diproses: ${totalCount} siswa`;
            }
            
            showNotification(message, type);
        }

        // Device Detection
        function detectDevice() {
            const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || 
                             (navigator.maxTouchPoints && navigator.maxTouchPoints > 2) ||
                             window.innerWidth <= 768;
            
            const webNotice = document.getElementById('web-notice');
            const mobileContent = document.getElementById('mobile-content');
            
            if (isMobile) {
                // Show mobile content, hide notice
                webNotice.classList.add('hidden');
                mobileContent.classList.remove('hidden');
            } else {
                // Show notice, hide mobile content
                webNotice.classList.remove('hidden');
                mobileContent.classList.add('hidden');
            }
        }
        
        // Run detection on page load
        detectDevice();
        
        // Re-run on window resize
        window.addEventListener('resize', detectDevice);
    </script>
@endpush
