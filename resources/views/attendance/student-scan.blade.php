@extends('layouts.app')

@section('title', 'Scan Siswa - Presensia')

@section('content')
{{-- DESKTOP NOTICE --}}
<div id="web-notice" class="hidden">
    <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
        <div class="max-w-sm w-full bg-white rounded-3xl shadow-lg p-8 text-center">
            <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-5">
                <i data-lucide="smartphone" class="h-8 w-8 text-blue-600"></i>
            </div>
            <p class="text-lg font-semibold text-gray-900 mb-2">Hanya untuk Perangkat Mobile</p>
            <p class="text-gray-500 text-sm mb-6">Fitur scan QR Code siswa hanya tersedia pada perangkat mobile dengan kamera.</p>
            <a href="{{ route('attendance.index') }}" class="flex items-center justify-center gap-2 w-full bg-blue-600 text-white py-3 px-4 rounded-2xl hover:bg-blue-700 transition-all font-semibold text-sm">
                <i data-lucide="arrow-left" class="h-4 w-4"></i> Kembali ke Absensi
            </a>
        </div>
    </div>
</div>

{{-- MOBILE CONTENT --}}
<div id="mobile-content" class="hidden">
<div class="scanner-page">

    {{-- CAMERA SECTION --}}
    <div class="camera-section" id="cameraSection">
        {{-- TOP BAR --}}
        <div class="cam-topbar">
            <a href="{{ route('dashboard') }}" class="cam-back-btn">
                <i data-lucide="chevron-left" class="h-5 w-5"></i>
            </a>
            <h1 class="cam-title text-base font-semibold">Scan QR Siswa</h1>
        </div>

        {{-- CAMERA VIEWPORT --}}
        <div class="cam-viewport">
            {{-- CAMERA CONTROLS OVERLAY (Flash & Switch Camera) --}}
            <div id="cameraControls" class="camera-controls-overlay hidden">
                <button id="flashBtn" class="cam-control-btn" title="Toggle Flash">
                    <i data-lucide="flashlight-off" class="h-5 w-5" id="flashIcon"></i>
                </button>
                <button id="switchCameraBtn" class="cam-control-btn" title="Ganti Kamera">
                    <i data-lucide="switch-camera" class="h-5 w-5"></i>
                </button>
            </div>

            <div class="cam-stream-container">
                {{-- HTML5 QR reader mounts here --}}
                <div id="html5-qrcode-reader" class="cam-qr-reader"></div>

                {{-- STATIC CAMERA PERMISSION OVERLAY --}}
                <div id="cameraPermissionOverlay" class="camera-permission-overlay hidden">
                    <div class="permission-content">
                        <div style="width:56px;height:56px;background:#eff6ff;border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                            <i data-lucide="camera" style="width:28px;height:28px;color:#3b82f6;"></i>
                        </div>
                        <h3 style="font-size:16px;font-weight:800;color:#1e293b;margin-bottom:8px;">Izin Akses Kamera</h3>
                        <p style="font-size:13px;color:#64748b;margin-bottom:0;">Diperlukan untuk scan QR Code siswa</p>
                        <div class="permission-buttons">
                            <button id="requestPermission" style="background:#3b82f6;color:white;">Berikan Izin</button>
                            <button id="cancelPermission" style="background:#f1f5f9;color:#475569;">Batal</button>
                        </div>
                    </div>
                </div>
                
                {{-- STATIC IDLE PLACEHOLDER --}}
                <div id="camIdlePlaceholder" class="cam-idle">
                    <div class="cam-idle-pulse">
                        <i data-lucide="scan-line" class="h-14 w-14 text-white/60"></i>
                    </div>
                    <p class="cam-idle-text" id="camInstructionText">Tekan tombol di bawah<br>untuk mulai scan</p>
                </div>
                
                {{-- STATIC QR SCAN OVERLAY --}}
                <div id="qr-guide" class="cam-scan-overlay hidden">
                    <div class="scan-frame">
                        <span class="corner tl"></span>
                        <span class="corner tr"></span>
                        <span class="corner bl"></span>
                        <span class="corner br"></span>
                        <div class="scan-laser"></div>
                    </div>
                    <p class="scan-hint-text" id="scanHintText">Arahkan kamera ke QR Code</p>
                </div>
            </div>
        </div>

        {{-- BOTTOM CONTROLS --}}
        <div class="cam-bottom-bar">
            {{-- Baris atas (sekunder): badge "0 Siswa" (kiri) + chip "Input Manual" (kanan) --}}
            <div class="cam-bottom-row-top">
                <div class="record-badge" id="recordCount">0 Siswa</div>
                <button class="manual-chip" onclick="openManualSheet()">
                    <i data-lucide="keyboard" class="h-3.5 w-3.5"></i>
                    <span>Input Manual</span>
                </button>
            </div>

            {{-- Baris bawah (primer): tombol Mulai Scan/Stop (dominan) + icon list (pojok kanan) --}}
            <div class="cam-bottom-row-bottom">
                <div id="scan-button-container" class="flex-grow">
                    <!-- Tombol di-render secara dinamis menggunakan Javascript -->
                </div>
                <button class="list-toggle-btn" onclick="showStudentPanel()">
                    <i data-lucide="list" class="h-5 w-5"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- STUDENT LIST PANEL --}}
    <div class="student-panel" id="studentPanel">
        <div class="panel-handle-row" onclick="hideStudentPanel()">
            <div class="panel-handle"></div>
        </div>
        <div class="panel-header">
            <h2 class="panel-title">Daftar Absensi</h2>
            <div class="panel-header-right">
                <span class="panel-badge" id="panelStudentCount">0 siswa</span>
                <button id="syncButton" class="sync-icon-btn" title="Sinkronkan data">
                    <i data-lucide="refresh-cw" class="h-4 w-4"></i>
                    <span class="sync-badge hidden" id="syncBadge">0</span>
                </button>
            </div>
        </div>
        <div id="capturedList" class="captured-list">
            <div class="empty-state">
                <div class="empty-icon-box">
                    <i data-lucide="users" class="h-8 w-8 text-gray-300"></i>
                </div>
                <p class="empty-title">Belum ada siswa yang diabsensi</p>
                <p class="empty-sub">Gunakan kamera atau input manual</p>
            </div>
        </div>
        <div class="panel-fab-area" id="panelFabArea" style="display:none;">
            <button id="syncButtonBottom" class="panel-sync-fab">
                <i data-lucide="upload-cloud" class="h-5 w-5 mr-2"></i>
                Simpan &amp; Sinkronkan
                <span class="fab-count-badge" id="fabCountBadge">0</span>
            </button>
        </div>
    </div>

</div>{{-- /scanner-page --}}

{{-- MANUAL BOTTOM SHEET --}}
<div id="manualSheetBackdrop" class="sheet-backdrop hidden" onclick="closeManualSheet()"></div>
<div id="manualSheet" class="manual-sheet translate-y-full">
    <div class="sheet-handle-row" onclick="closeManualSheet()">
        <div class="sheet-handle"></div>
    </div>
    <div class="sheet-body">
        <h3 class="sheet-title">Input Manual</h3>
        <p class="sheet-sub">Masukkan QR Code siswa secara manual</p>
        <div class="sheet-input-row">
            <input type="text" id="manual_qr" placeholder="Format: NIS|Nama (cth: 12345|Budi)"
                class="sheet-input" autocomplete="off">
        </div>
        <div class="sheet-help">
            <i data-lucide="info" class="h-3 w-3 flex-shrink-0 mr-1"></i>
            Format: NIS|Nama Siswa — contoh: SISWA001|Budi Santoso
        </div>
        <button id="addManual" class="sheet-add-btn" onclick="addManualStudent()">
            <i data-lucide="plus" class="h-4 w-4 mr-1.5"></i>
            Tambahkan ke Daftar
        </button>
    </div>
</div>

{{-- SYNC FORM --}}
<form id="syncForm" method="POST" action="{{ route('attendance.scan-student') }}" style="display:none;">
    @csrf
    <div id="hiddenInputs"></div>
</form>
</div>{{-- /mobile-content --}}
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
@if(session('sync_result'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const r = @json(session('sync_result'));
        showSyncNotification(r.success_count, r.duplicate_count, r.error_count, r.total_count);
        fetch('{{ route("attendance.clear-sync-result") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
        });
    });
</script>
@endif

<style>
body { overscroll-behavior: none; touch-action: pan-y; }
.scanner-page { position: fixed; inset: 0; display: flex; flex-direction: column; background: #000; z-index: 0; }

/* CAMERA SECTION */
.camera-section { position: relative; flex: 1; display: flex; flex-direction: column; overflow: hidden; background: #0a0a0a; }
.cam-topbar { position: absolute; top: 0; left: 0; right: 0; z-index: 30; display: flex; align-items: center; justify-content: space-between; padding: 20px 16px; background: linear-gradient(180deg, rgba(0,0,0,.80) 0%, transparent 100%); }
.cam-back-btn { width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,.15); border-radius: 50%; color: white; text-decoration: none; backdrop-filter: blur(6px); transition: background .2s; }
.cam-back-btn:hover { background: rgba(255,255,255,.28); }
.cam-title { font-size: 15px; font-weight: 800; color: white; letter-spacing: -.3px; }

/* CAMERA CONTROLS OVERLAY */
.camera-controls-overlay {
    position: absolute;
    top: 80px; /* Di bawah topbar navigation */
    right: 16px;
    z-index: 40;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.camera-controls-overlay.hidden {
    display: none !important;
}
.cam-control-btn {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.4);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 50%;
    color: white;
    cursor: pointer;
    backdrop-filter: blur(4px);
    transition: all 0.2s;
}
.cam-control-btn:hover {
    background: rgba(0, 0, 0, 0.6);
    transform: scale(1.05);
}

.cam-viewport { flex: 1; position: relative; overflow: hidden; }
.cam-stream-container { position: absolute; inset: 0; }
.cam-qr-reader { width: 100% !important; height: 100% !important; position: absolute !important; inset: 0 !important; }
.cam-qr-reader video { width: 100% !important; height: 100% !important; object-fit: cover !important; position: absolute !important; inset: 0 !important; }
.cam-qr-reader select { display: none !important; }
.cam-qr-reader button[data-camera-id] { display: none !important; }

/* IDLE */
.cam-idle { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #0f172a; z-index: 5; }
.cam-idle.hidden { display: none !important; }
.cam-idle-pulse { width: 110px; height: 110px; border-radius: 50%; border: 2px dashed rgba(255,255,255,.2); display: flex; align-items: center; justify-content: center; animation: idlePulse 2.5s ease-in-out infinite; background: rgba(255,255,255,.04); margin-bottom: 20px; }
@keyframes idlePulse { 0%,100%{transform:scale(1);opacity:.7;} 50%{transform:scale(1.08);opacity:1;} }
.cam-idle-text { color: rgba(255,255,255,.4); font-size: 13px; text-align: center; line-height: 1.6; }

/* SCAN OVERLAY */
.cam-scan-overlay { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; z-index: 10; pointer-events: none; }
.cam-scan-overlay.hidden { display: none !important; }
.scan-frame {
    position: relative;
    width: 260px;
    height: 260px;
    box-shadow: 0 0 0 9999px rgba(0,0,0,.5);
}
.corner {
    position: absolute;
    width: 28px;
    height: 28px;
    border-color: #3b82f6;
    border-style: solid;
    border-width: 0;
    filter: drop-shadow(0 0 4px rgba(59, 130, 246, 0.5));
}
.corner.tl { top: 0; left: 0; border-top-width: 4px; border-left-width: 4px; border-radius: 4px 0 0 0; }
.corner.tr { top: 0; right: 0; border-top-width: 4px; border-right-width: 4px; border-radius: 0 4px 0 0; }
.corner.bl { bottom: 0; left: 0; border-bottom-width: 4px; border-left-width: 4px; border-radius: 0 0 0 4px; }
.corner.br { bottom: 0; right: 0; border-bottom-width: 4px; border-right-width: 4px; border-radius: 0 0 4px 0; }
.scan-laser {
    position: absolute;
    left: 8px;
    right: 8px;
    height: 2px;
    background: linear-gradient(90deg, transparent, #4ade80 20%, #22c55e 50%, #4ade80 80%, transparent);
    box-shadow: 0 0 8px rgba(34, 197, 94, 0.8);
    animation: laserScan 2s ease-in-out infinite;
    top: 0;
}
@keyframes laserScan { 0%{top:4px;opacity:0;} 10%{opacity:1;} 50%{top:calc(100% - 6px);} 90%{opacity:1;} 100%{top:4px;opacity:0;} }
.scan-hint-text { position: absolute; bottom:-44px; left:50%; transform:translateX(-50%); color: rgba(255,255,255,.8); font-size:12px; font-weight:600; white-space:nowrap; background:rgba(0,0,0,.35); padding:5px 16px; border-radius:99px; }

/* BOTTOM CONTROLS BAR (Updated Layout) */
.cam-bottom-bar {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    z-index: 20;
    background: linear-gradient(0deg, rgba(0,0,0,.95) 0%, rgba(0,0,0,.6) 60%, transparent 100%);
    padding: 24px 20px calc(env(safe-area-inset-bottom, 0px) + 104px); /* Beri jarak longgar dari bottom nav */
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.cam-bottom-row-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
}
.cam-bottom-row-bottom {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
}

.record-badge { background: rgba(59,130,246,.9); color:white; font-size:11px; font-weight:800; padding:4px 12px; border-radius:99px; backdrop-filter:blur(4px); }
.manual-chip { display:inline-flex; align-items:center; gap:5px; background:rgba(255,255,255,.15); color:white; font-size:11px; font-weight:700; padding:6px 12px; border-radius:99px; border:1px solid rgba(255,255,255,.25); cursor:pointer; backdrop-filter:blur(4px); transition:background .2s; }
.manual-chip:hover { background:rgba(255,255,255,.28); }

.scan-fab-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: #3b82f6;
    color: white;
    font-size: 14px;
    font-weight: 800;
    padding: 14px 28px;
    border-radius: 99px;
    border: none;
    cursor: pointer;
    box-shadow: 0 6px 24px rgba(59,130,246,.55);
    transition: all .2s;
    width: 100%; /* Dominan/Full-width di container */
}
.scan-fab-btn:hover { transform:scale(1.02); }
.scan-fab-stop { background: rgba(239,68,68,.85); box-shadow: 0 6px 20px rgba(239,68,68,.45); }
.list-toggle-btn { width:46px; height:46px; display:flex; align-items:center; justify-content:center; background:rgba(255,255,255,.15); border-radius:50%; color:white; border:1px solid rgba(255,255,255,.2); cursor:pointer; backdrop-filter:blur(4px); flex-shrink: 0; }

/* STUDENT PANEL */
.student-panel { position:absolute; bottom:0; left:0; right:0; background:white; border-radius:24px 24px 0 0; box-shadow:0 -8px 32px rgba(0,0,0,.2); z-index:50; transform:translateY(100%); transition:transform .4s cubic-bezier(.25,.46,.45,.94); max-height:80vh; display:flex; flex-direction:column; overflow:hidden; }
.student-panel.show { transform:translateY(0); }
.panel-handle-row { display:flex; justify-content:center; padding:10px 0 6px; cursor:pointer; flex-shrink:0; }
.panel-handle { width:40px; height:4px; background:#e2e8f0; border-radius:99px; }
.panel-header { display:flex; align-items:center; justify-content:space-between; padding:0 20px 14px; border-bottom:1px solid #f1f5f9; flex-shrink:0; }
.panel-title { font-size:15px; font-weight:800; color:#1e293b; }
.panel-header-right { display:flex; align-items:center; gap:10px; }
.panel-badge { background:#eff6ff; color:#1d4ed8; font-size:11px; font-weight:800; padding:4px 12px; border-radius:99px; border:1px solid #dbeafe; }
.sync-icon-btn { position:relative; width:36px; height:36px; display:flex; align-items:center; justify-content:center; background:#f0f9ff; border-radius:50%; border:1px solid #bfdbfe; color:#2563eb; cursor:pointer; transition:all .2s; }
.sync-icon-btn:hover { background:#dbeafe; }
.sync-badge { position:absolute; top:-3px; right:-3px; width:16px; height:16px; background:#ef4444; color:white; font-size:9px; font-weight:900; border-radius:50%; display:flex; align-items:center; justify-content:center; }
.captured-list { flex:1; overflow-y:auto; padding:12px 16px; }
.empty-state { display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; padding:32px 16px; }
.empty-icon-box { width:64px; height:64px; background:#f8fafc; border-radius:20px; display:flex; align-items:center; justify-content:center; margin-bottom:12px; }
.empty-title { font-size:14px; font-weight:700; color:#94a3b8; }
.empty-sub { font-size:12px; color:#cbd5e1; margin-top:4px; }

/* Student card */
.student-card { display:flex; align-items:center; padding:10px 12px; background:#f8fafc; border-radius:14px; margin-bottom:8px; animation:slideDown .25s ease forwards; }
@keyframes slideDown { from{opacity:0;transform:translateY(-8px);} to{opacity:1;transform:translateY(0);} }
.student-card:last-child { margin-bottom:0; }
.student-avatar { width:42px; height:42px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:15px; font-weight:800; color:white; flex-shrink:0; margin-right:12px; }
.student-info { flex:1; min-width:0; }
.student-name { font-size:13px; font-weight:700; color:#1e293b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.student-nis { font-size:11px; color:#94a3b8; font-weight:500; margin-top:2px; }
.student-time { font-size:11px; color:#64748b; font-weight:600; background:#f1f5f9; padding:3px 8px; border-radius:8px; white-space:nowrap; flex-shrink:0; }
.student-del-btn { width:30px; height:30px; display:flex; align-items:center; justify-content:center; background:#fef2f2; border-radius:8px; border:none; color:#ef4444; cursor:pointer; margin-left:8px; transition:background .15s; flex-shrink:0; }
.student-del-btn:hover { background:#fee2e2; }

/* FAB area */
.panel-fab-area { padding:12px 16px calc(env(safe-area-inset-bottom,0px) + 12px); flex-shrink:0; border-top:1px solid #f1f5f9; }
.panel-sync-fab { width:100%; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg,#1d4ed8,#3b82f6); color:white; font-size:14px; font-weight:800; padding:14px; border-radius:16px; border:none; cursor:pointer; box-shadow:0 6px 20px rgba(29,78,216,.35); transition:all .2s; gap:4px; }
.panel-sync-fab:hover { transform:translateY(-1px); }
.fab-count-badge { background:rgba(255,255,255,.3); font-size:11px; font-weight:900; padding:1px 7px; border-radius:99px; margin-left:6px; }

/* MANUAL SHEET */
.sheet-backdrop { position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:60; backdrop-filter:blur(2px); }
.sheet-backdrop.hidden { display:none; }
.manual-sheet { position:fixed; bottom:0; left:0; right:0; background:white; border-radius:24px 24px 0 0; z-index:70; transition:transform .35s cubic-bezier(.25,.46,.45,.94); padding-bottom:env(safe-area-inset-bottom,0px); }
.manual-sheet.translate-y-full { transform:translateY(100%); }
.manual-sheet.open { transform:translateY(0); }
.sheet-handle-row { display:flex; justify-content:center; padding:12px 0 8px; cursor:pointer; }
.sheet-handle { width:40px; height:4px; background:#e2e8f0; border-radius:99px; }
.sheet-body { padding:0 24px 24px; }
.sheet-title { font-size:16px; font-weight:800; color:#1e293b; margin-bottom:4px; }
.sheet-sub { font-size:12px; color:#94a3b8; margin-bottom:16px; }
.sheet-input-row { margin-bottom:8px; }
.sheet-input { width:100%; padding:14px 16px; border:1.5px solid #e2e8f0; border-radius:14px; font-size:14px; font-weight:500; color:#1e293b; background:#f8fafc; transition:all .2s; box-sizing:border-box; }
.sheet-input::placeholder { color:#94a3b8; }
.sheet-input:focus { outline:none; border-color:#3b82f6; background:white; box-shadow:0 0 0 3px rgba(59,130,246,.1); }
.sheet-help { font-size:11px; color:#94a3b8; display:flex; align-items:center; margin-bottom:16px; font-weight:500; }
.sheet-add-btn { width:100%; display:flex; align-items:center; justify-content:center; background:#3b82f6; color:white; font-size:14px; font-weight:800; padding:14px; border-radius:14px; border:none; cursor:pointer; transition:all .2s; box-shadow:0 4px 14px rgba(59,130,246,.35); }
.sheet-add-btn:hover { background:#2563eb; }

/* Permission overlay */
.camera-permission-overlay { position:absolute; inset:0; background:rgba(0,0,0,.85); display:flex; align-items:center; justify-content:center; z-index:100; backdrop-filter:blur(8px); }
.camera-permission-overlay.hidden { display: none !important; }
.permission-content { background:white; padding:28px 24px; border-radius:24px; text-align:center; max-width:290px; width:90%; box-shadow:0 8px 40px rgba(0,0,0,.4); }
.permission-buttons { display:flex; gap:10px; justify-content:center; margin-top:16px; }
.permission-buttons button { border-radius:12px; font-weight:700; padding:10px 20px; border:none; cursor:pointer; transition:.2s; }
.permission-buttons button:hover { transform:translateY(-1px); }

/* Avatar colors */
.av-0{background:#3b82f6;} .av-1{background:#10b981;} .av-2{background:#8b5cf6;}
.av-3{background:#f59e0b;} .av-4{background:#ef4444;} .av-5{background:#06b6d4;}
.av-6{background:#ec4899;} .av-7{background:#84cc16;}

/* Toast */
.toast { position:fixed; top:16px; left:50%; transform:translateX(-50%); background:#1e293b; color:white; font-size:13px; font-weight:600; padding:10px 20px; border-radius:12px; z-index:999; white-space:nowrap; box-shadow:0 4px 20px rgba(0,0,0,.3); animation:toastIn .25s ease forwards; }
.toast.success{background:#059669;} .toast.error{background:#dc2626;} .toast.warning{background:#d97706;} .toast.info{background:#2563eb;}
@keyframes toastIn { from{opacity:0;transform:translateX(-50%) translateY(-8px);} to{opacity:1;transform:translateX(-50%) translateY(0);} }
</style>

<script>
let capturedStudents = [];
let html5Qrcode = null;
let cameraActive = false;
let flashActive = false;
let facingMode = 'environment';

/* DEVICE DETECTION */
function detectDevice() {
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)
        || (navigator.maxTouchPoints && navigator.maxTouchPoints > 2)
        || window.innerWidth <= 768;
    document.getElementById('web-notice').classList.toggle('hidden', isMobile);
    document.getElementById('mobile-content').classList.toggle('hidden', !isMobile);
    if (typeof lucide !== 'undefined') setTimeout(() => lucide.createIcons(), 80);
}
detectDevice();
window.addEventListener('resize', detectDevice);

/* CONDITIONAL SCAN BUTTON RENDERING */
function renderScanButton() {
    const container = document.getElementById('scan-button-container');
    if (cameraActive) {
        container.innerHTML = `
            <button id="stopCamera" class="scan-fab-btn scan-fab-stop" onclick="stopScanner()">
                <i data-lucide="square" class="h-5 w-5"></i>
                <span>Stop</span>
            </button>
        `;
    } else {
        container.innerHTML = `
            <button id="startCamera" class="scan-fab-btn" onclick="startScanner()">
                <i data-lucide="scan-line" class="h-5 w-5"></i>
                <span>Mulai Scan</span>
            </button>
        `;
    }
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

/* DYNAMIC INSTRUCTION UPDATING */
function updateInstruction() {
    const idleText = document.getElementById('camInstructionText');
    const hintText = document.getElementById('scanHintText');
    if (cameraActive) {
        if (hintText) hintText.textContent = "Arahkan kamera ke QR Code";
    } else {
        if (idleText) idleText.innerHTML = "Tekan tombol di bawah<br>untuk mulai scan";
    }
}

/* START SCANNER (Shows static overlay instead of replacing innerHTML) */
function startScanner() {
    const overlay = document.getElementById('cameraPermissionOverlay');
    overlay.classList.remove('hidden');
    if (typeof lucide !== 'undefined') lucide.createIcons();
    
    document.getElementById('requestPermission').onclick = () => {
        overlay.classList.add('hidden');
        startCameraStream();
    };
    document.getElementById('cancelPermission').onclick = () => {
        overlay.classList.add('hidden');
        resetCameraUI();
    };
}

function startCameraStream() {
    html5Qrcode = new Html5Qrcode("html5-qrcode-reader");
    const config = {
        fps: 10,
        qrbox: function(w, h) { const s = Math.min(w, h); return { width: Math.floor(s*.75), height: Math.floor(s*.75) }; },
        videoConstraints: { facingMode: facingMode }
    };
    html5Qrcode.start({ facingMode: facingMode }, config, onScanSuccess, onScanFailure)
        .then(() => {
            cameraActive = true;
            renderScanButton();
            updateInstruction();
            document.getElementById('camIdlePlaceholder').classList.add('hidden');
            document.getElementById('qr-guide').classList.remove('hidden');
            document.getElementById('cameraControls').classList.remove('hidden');
            showToast('Scanner aktif — arahkan QR ke bingkai', 'success');
        })
        .catch(err => { showToast('Gagal akses kamera: ' + err.message, 'error'); resetCameraUI(); });
}

function stopScanner() {
    if (html5Qrcode) { html5Qrcode.stop().then(() => { html5Qrcode = null; }).catch(() => { html5Qrcode = null; }); }
    cameraActive = false;
    renderScanButton();
    updateInstruction();
    document.getElementById('camIdlePlaceholder').classList.remove('hidden');
    document.getElementById('qr-guide').classList.add('hidden');
    document.getElementById('cameraControls').classList.add('hidden');
    resetCameraUI();
    showToast('Scanner dihentikan', 'info');
}

function resetCameraUI() {
    cameraActive = false;
    renderScanButton();
    updateInstruction();
    document.getElementById('cameraPermissionOverlay').classList.add('hidden');
    document.getElementById('camIdlePlaceholder').classList.remove('hidden');
    document.getElementById('qr-guide').classList.add('hidden');
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

/* INITIAL RUN */
renderScanButton();
updateInstruction();

/* FLASH */
document.getElementById('flashBtn').addEventListener('click', async () => {
    if (!html5Qrcode) return;
    try {
        flashActive = !flashActive;
        await html5Qrcode.applyVideoConstraints({ advanced: [{ torch: flashActive }] });
        document.getElementById('flashIcon').setAttribute('data-lucide', flashActive ? 'flashlight' : 'flashlight-off');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    } catch(e) { showToast('Flash tidak didukung di perangkat ini', 'warning'); }
});

/* SWITCH CAMERA */
document.getElementById('switchCameraBtn').addEventListener('click', () => {
    facingMode = facingMode === 'environment' ? 'user' : 'environment';
    stopScanner();
    setTimeout(() => startScanner(), 400);
});

/* SCAN CALLBACKS */
function onScanSuccess(decodedText) {
    if (capturedStudents.find(s => s.qrCode === decodedText)) { showToast('Siswa sudah ada dalam daftar', 'warning'); return; }
    addStudent(decodedText);
}
function onScanFailure() {}

/* ADD STUDENT */
function addStudent(data) {
    if (capturedStudents.find(s => s.qrCode === data)) { showToast('Siswa sudah ada dalam daftar', 'warning'); return; }
    const now = new Date().toLocaleTimeString('id-ID', { hour12: false, timeZone: 'Asia/Jakarta' });
    capturedStudents.push({ id: Date.now(), qrCode: data, timestamp: now });
    renderStudentList();
    const msg = data.includes('|') ? ('✓ ' + data.split('|')[1] + ' (' + data.split('|')[0] + ')') : ('Ditambahkan: ' + data);
    showToast(msg, 'success');
}

/* RENDER LIST */
const AVATAR_COLORS = ['av-0','av-1','av-2','av-3','av-4','av-5','av-6','av-7'];
function renderStudentList() {
    const list = document.getElementById('capturedList');
    const count = capturedStudents.length;
    document.getElementById('recordCount').textContent = count + ' Siswa';
    document.getElementById('panelStudentCount').textContent = count + ' siswa';
    document.getElementById('fabCountBadge').textContent = count;
    const sb = document.getElementById('syncBadge');
    count > 0 ? (sb.textContent = count, sb.classList.remove('hidden')) : sb.classList.add('hidden');
    document.getElementById('panelFabArea').style.display = count > 0 ? 'block' : 'none';
    if (count === 0) {
        list.innerHTML = `<div class="empty-state"><div class="empty-icon-box"><i data-lucide="users" style="width:32px;height:32px;color:#cbd5e1;"></i></div><p class="empty-title">Belum ada siswa yang diabsensi</p><p class="empty-sub">Gunakan kamera atau input manual</p></div>`;
        if (typeof lucide !== 'undefined') lucide.createIcons();
        return;
    }
    list.innerHTML = capturedStudents.map((rec, i) => {
        let name = rec.qrCode, nis = '';
        if (rec.qrCode.includes('|')) { [nis, name] = rec.qrCode.split('|'); }
        const init = (name.trim().split(' ').map(w=>w[0]).join('').toUpperCase().slice(0,2)) || '?';
        return `<div class="student-card" id="card-${rec.id}">
            <div class="student-avatar ${AVATAR_COLORS[i%8]}">${init}</div>
            <div class="student-info">
                <div class="student-name">${name}</div>
                ${nis ? `<div class="student-nis">NIS: ${nis}</div>` : ''}
            </div>
            <div class="student-time">${rec.timestamp}</div>
            <button class="student-del-btn" onclick="removeStudent(${rec.id})">
                <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
            </button>
        </div>`;
    }).join('');
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function removeStudent(id) {
    if (!confirm('Hapus siswa ini dari daftar absensi?')) return;
    capturedStudents = capturedStudents.filter(s => s.id !== id);
    renderStudentList();
    showToast('Siswa dihapus', 'info');
}

/* STUDENT PANEL */
function showStudentPanel() { document.getElementById('studentPanel').classList.add('show'); }
function hideStudentPanel() { document.getElementById('studentPanel').classList.remove('show'); }
window.showStudentsSectionSmooth = showStudentPanel;
window.hideStudentsSectionSmooth = hideStudentPanel;

/* MANUAL SHEET */
function openManualSheet() {
    document.getElementById('manualSheetBackdrop').classList.remove('hidden');
    setTimeout(() => document.getElementById('manualSheet').classList.add('open'), 20);
    setTimeout(() => document.getElementById('manual_qr').focus(), 380);
}
function closeManualSheet() {
    document.getElementById('manualSheet').classList.remove('open');
    document.getElementById('manualSheetBackdrop').classList.add('hidden');
}
window.openManualSheet = openManualSheet;
window.closeManualSheet = closeManualSheet;

function addManualStudent() {
    const val = document.getElementById('manual_qr').value.trim();
    if (!val) { showToast('Masukkan QR Code terlebih dahulu', 'warning'); return; }
    addStudent(val);
    document.getElementById('manual_qr').value = '';
    closeManualSheet();
}
document.getElementById('manual_qr').addEventListener('keydown', e => { if (e.key === 'Enter') addManualStudent(); });

/* SYNC */
function doSync() {
    if (capturedStudents.length === 0) { showToast('Tidak ada data untuk disinkronkan', 'warning'); return; }
    const hid = document.getElementById('hiddenInputs');
    hid.innerHTML = '';
    capturedStudents.forEach(rec => { const inp = document.createElement('input'); inp.type='hidden'; inp.name='qr_codes[]'; inp.value=rec.qrCode; hid.appendChild(inp); });
    document.getElementById('syncForm').submit();
}
document.getElementById('syncButton').addEventListener('click', doSync);
document.getElementById('syncButtonBottom').addEventListener('click', doSync);

/* TOAST */
function showToast(message, type='info') {
    const t = document.createElement('div');
    t.className = 'toast ' + type;
    t.textContent = message;
    document.body.appendChild(t);
    setTimeout(() => { t.style.opacity='0'; t.style.transition='opacity .3s'; setTimeout(()=>t.remove(),300); }, 3000);
}

/* SYNC RESULT FROM SESSION */
function showSyncNotification(suc, dup, err, tot) {
    let msg='', type='info';
    if (suc>0&&err===0) { msg=`Berhasil: ${suc} siswa`+(dup>0?` | Duplikat: ${dup}`:''); type='success'; }
    else if (suc>0) { msg=`${suc} berhasil | ${err} gagal`; type='warning'; }
    else if (err>0) { msg=`Gagal: ${err} siswa`; type='error'; }
    else { msg=`Diproses: ${tot} siswa`; }
    showToast(msg, type);
}

/* SWIPE DOWN TO CLOSE PANEL */
let panelStartY=0;
const panel = document.getElementById('studentPanel');
panel.addEventListener('touchstart', e => { panelStartY = e.touches[0].clientY; });
panel.addEventListener('touchend', e => { if (e.changedTouches[0].clientY - panelStartY > 60) hideStudentPanel(); });
</script>
@endpush
