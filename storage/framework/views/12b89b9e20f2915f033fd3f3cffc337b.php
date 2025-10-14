<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan Absensi Siswa - Presensia</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/qr-scanner@1.4.2/qr-scanner.legacy.min.js"></script>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <i class="fas fa-graduation-cap text-blue-600 text-2xl mr-3"></i>
                        <span class="text-xl font-bold text-gray-900">Presensia</span>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="<?php echo e(route('dashboard')); ?>" class="text-sm text-gray-700 hover:text-gray-900">
                        <i class="fas fa-home mr-1"></i>Dashboard
                    </a>
                    <a href="<?php echo e(route('attendance.index')); ?>" class="text-sm text-gray-700 hover:text-gray-900">
                        <i class="fas fa-calendar-check mr-1"></i>Absensi
                    </a>
                    <span class="text-sm text-gray-700">Selamat datang, <strong><?php echo e(auth()->user()->name); ?></strong></span>
                    <form method="POST" action="<?php echo e(route('logout')); ?>" class="inline">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="text-sm text-gray-700 hover:text-gray-900">
                            <i class="fas fa-sign-out-alt mr-1"></i>Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-6xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Scan Absensi Siswa</h1>
                        <p class="text-gray-600 mt-1"><?php echo e(now()->setTimezone('Asia/Jakarta')->format('d F Y')); ?> - <span id="currentTime"><?php echo e(now()->setTimezone('Asia/Jakarta')->format('H:i:s')); ?></span></p>
                    </div>
                    <a href="<?php echo e(route('attendance.index')); ?>" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                    </a>
                </div>
            </div>
        </div>

        <!-- Success Message -->
        <?php if(session('success')): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <i class="fas fa-check-circle mr-2"></i><?php echo e(session('success')); ?>

        </div>
        <?php endif; ?>

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
                    <form id="scanForm" method="POST" action="<?php echo e(route('attendance.student-scan')); ?>" style="display: none;">
                        <?php echo csrf_field(); ?>
                        <div id="hiddenInputs"></div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <script>
        let scannedStudents = [];
        let qrScanner = null;
        let scannerActive = false;

        // Start scanner using Nimiq QR Scanner
        document.getElementById('startScanner').addEventListener('click', async function() {
            try {
                const scanner = document.getElementById('scanner');
                
                // Clear previous content
                scanner.innerHTML = '';
                
                // Create video element for Nimiq QR Scanner
                const video = document.createElement('video');
                video.style.width = '100%';
                video.style.height = '100%';
                video.style.objectFit = 'cover';
                video.setAttribute('playsinline', 'true');
                scanner.appendChild(video);
                
                // Initialize Nimiq QR Scanner (worker path handled automatically)
                qrScanner = new QrScanner(video, result => {
                    console.log('=== SCANNER CALLBACK TRIGGERED ===');
                    console.log('QR Code detected (Nimiq):', result.data);
                    console.log('QR Code type:', typeof result.data);
                    console.log('QR Code length:', result.data.length);
                    console.log('Full result object:', result);
                    addScannedStudent(result.data);
                }, {
                    highlightScanRegion: true,
                    highlightCodeOutline: true,
                    preferredCamera: 'environment',
                    returnDetailedScanResult: true,
                    onDecodeError: (error) => {
                        console.log('QR Scanner decode error:', error);
                    }
                });
                
                // Start scanning
                console.log('Starting QR Scanner...');
                await qrScanner.start();
                
                scannerActive = true;
                document.getElementById('startScanner').style.display = 'none';
                document.getElementById('stopScanner').style.display = 'inline-block';
                
                console.log('Nimiq QR Scanner started successfully');
                console.log('Scanner active:', scannerActive);
                console.log('QR Scanner instance:', qrScanner);
                showNotification('Scanner berhasil dimulai. Arahkan kamera ke QR Code siswa.', 'success');
                
            } catch (err) {
                console.error('Error starting scanner:', err);
                alert('Tidak dapat mengakses kamera: ' + err.message);
                
                // Show manual input as fallback
                document.getElementById('manualQrInput').style.display = 'block';
                showNotification('Scanner gagal. Gunakan input manual di bawah.', 'warning');
            }
        });

        // Stop scanner
        document.getElementById('stopScanner').addEventListener('click', function() {
            if (scannerActive && qrScanner) {
                qrScanner.stop();
                qrScanner.destroy();
                qrScanner = null;
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
                
                console.log('Nimiq QR Scanner stopped');
                showNotification('Scanner dihentikan', 'info');
            }
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
        
        // Test parsing function
        console.log('Testing parseQRCode function:');
        console.log('Test 1 - SISWA001|Siswa 1:', parseQRCode('SISWA001|Siswa 1'));
        console.log('Test 2 - SISWA002_Siswa 2:', parseQRCode('SISWA002_Siswa 2'));
        console.log('Test 3 - SISWA003:', parseQRCode('SISWA003'));
        
        // Test scanner availability
        console.log('Testing scanner availability:');
        QrScanner.hasCamera().then(hasCamera => {
            console.log('Device has camera:', hasCamera);
        }).catch(err => {
            console.log('Error checking camera:', err);
        });

        // Parse QR code to extract NIS and name
        function parseQRCode(qrCode) {
            console.log('Parsing QR Code:', qrCode);
            try {
                // Format QR: "NIS|Nama" atau "NIS_Nama" atau JSON
                if (qrCode.includes('|')) {
                    const [nis, name] = qrCode.split('|');
                    const result = { nis: nis.trim(), name: name.trim() };
                    console.log('Parsed with | separator:', result);
                    return result;
                } else if (qrCode.includes('_')) {
                    const [nis, name] = qrCode.split('_');
                    const result = { nis: nis.trim(), name: name.trim() };
                    console.log('Parsed with _ separator:', result);
                    return result;
                } else {
                    // Try JSON format
                    const parsed = JSON.parse(qrCode);
                    const result = { nis: parsed.nis || parsed.NIS, name: parsed.name || parsed.nama };
                    console.log('Parsed as JSON:', result);
                    return result;
                }
            } catch (e) {
                // Fallback: treat entire QR as NIS, but don't show "Unknown"
                const result = { nis: qrCode, name: qrCode };
                console.log('Fallback parsing:', result);
                return result;
            }
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
            
            // Fetch student name from server if not available in QR
            if (parsed.name === parsed.nis) {
                fetchStudentName(parsed.nis).then(studentName => {
                    const studentRecord = {
                        qrCode: qrCode,
                        nis: parsed.nis,
                        name: studentName || parsed.nis,
                        scanTime: currentTime,
                        timestamp: Date.now()
                    };
                    
                    scannedStudents.push(studentRecord);
                    updateScannedList();
                    showNotification(`${studentName || parsed.nis} (${parsed.nis}) berhasil ditambahkan`, 'success');
                });
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
                const response = await fetch(`/api/student/${nis}`);
                if (response.ok) {
                    const data = await response.json();
                    return data.name;
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
                                <button type="button" onclick="removeStudent(${index})" class="text-red-600 hover:text-red-800 p-1">
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

        // Remove student
        function removeStudent(index) {
            scannedStudents.splice(index, 1);
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
                qrScanner.destroy();
            }
        });
    </script>
</body>
</html>

<?php /**PATH C:\Users\FHL\.cursor\presensia-v2\starter-kit\resources\views/attendance/student-scan.blade.php ENDPATH**/ ?>