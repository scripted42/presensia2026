@extends('layouts.app')

@section('title', 'Dashboard - Presensia')

@push('styles')
<style>
    /* Mobile responsive grid */
    @media (max-width: 768px) {
        .mobile\:grid-cols-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    
    /* Simple tooltip styles */
    .group:hover .opacity-0 {
        opacity: 1;
    }
</style>
@endpush

@section('content')
        
        <!-- Welcome Section -->
        <div class="bg-white overflow-hidden shadow rounded-lg mb-6 relative">
            @if($school->tenantSettings && $school->tenantSettings->banner_image)
                @php
                    $bannerScale = $school->tenantSettings->banner_scale ?? 100;
                @endphp
                <div class="banner-custom-image" style="background-image: url('{{ asset('storage/' . $school->tenantSettings->banner_image) }}'); background-size: {{ $bannerScale }}%; background-position: top; background-repeat: no-repeat;"></div>
            @endif
            <!-- School Photo Background -->
            @if($school->tenantSettings && $school->tenantSettings->school_photo)
                @php
                    $positionX = $school->tenantSettings->school_photo_position_x ?? 'center';
                    $positionY = $school->tenantSettings->school_photo_position_y ?? 'center';
                    $scale = $school->tenantSettings->school_photo_scale ?? 100;
                    $opacity = $school->tenantSettings->school_photo_opacity ?? 10;
                    
                    $backgroundPosition = $positionX . ' ' . $positionY;
                @endphp
                <div class="absolute inset-0 school-photo-bg" 
                     style="background-image: url('{{ asset('storage/' . $school->tenantSettings->school_photo) }}'); 
                            background-size: auto {{ $scale }}%;
                            background-position: {{ $backgroundPosition }};
                            background-repeat: no-repeat;
                            opacity: {{ $opacity / 100 }};"></div>
            @endif
            <div class="px-4 py-5 sm:p-6 welcome-hero-wrap relative z-10">
                <div class="flex items-start">
                    @if($school->logo)
                        <img src="{{ asset('storage/' . $school->logo) }}" alt="{{ $school->name }}" class="h-20 w-auto mr-4 flex-shrink-0">
                    @endif
                    <div class="flex-1 min-w-0">
                        <h1 class="text-2xl font-bold text-gray-900 mb-2">Dashboard</h1>
                        @if($school->tenantSettings && $school->tenantSettings->banner_text)
                            <p class="text-gray-600 leading-relaxed">{{ $school->tenantSettings->banner_text }}</p>
                        @else
                            <p class="text-gray-600 leading-relaxed">Selamat datang di sistem manajemen absensi sekolah, Presensia!</p>
                        @endif
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap gap-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        <i class="fas fa-building mr-2"></i>{{ $school->name }}
                    </span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        <i class="fas fa-user-tag mr-2"></i>{{ $user->roles->first()->display_name ?? $user->roles->first()->name ?? 'No Role' }}
                    </span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                        <i class="fas fa-user mr-2"></i>{{ $user->user_type === 'employee' ? 'Pegawai' : 'Siswa' }}
                    </span>
                </div>
                <!-- Decorative image on the right - REMOVED -->
            </div>
        </div>

        <!-- Main Dashboard Content (Role-Based Partials) -->
        @if($isManagerial)
            @include('dashboard.partials.admin')
        @elseif($isStudent)
            @include('dashboard.partials.student')
        @else
            @include('dashboard.partials.employee')
        @endif

        <!-- Charts after Ringkasan Performa (role-based) -->
        

        

        <!-- Quick Actions (Khusus Mobile/PWA, disembunyikan di Desktop) -->
        <div class="block lg:hidden mt-8">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-5">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Menu Aplikasi</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 quick-actions">
                        @if($user->hasRole('admin'))
                            <!-- Admin Menu -->
                            <!-- Kategori 1: Manajemen Data / User (Biru) -->
                            <a href="{{ route('users.index') }}" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md flex flex-col items-center text-center transition-all group">
                                <div class="p-3 bg-blue-50 text-blue-600 border border-blue-100/60 rounded-2xl mb-2 group-hover:bg-blue-600 group-hover:text-white transition-all">
                                    <i data-lucide="users" class="h-6 w-6"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-700">Manajemen User</span>
                            </a>
                            <a href="{{ route('users.import') }}" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md flex flex-col items-center text-center transition-all group">
                                <div class="p-3 bg-blue-50 text-blue-600 border border-blue-100/60 rounded-2xl mb-2 group-hover:bg-blue-600 group-hover:text-white transition-all">
                                    <i data-lucide="upload" class="h-6 w-6"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-700">Import Data</span>
                            </a>
                            <!-- Kategori 2: Laporan & Pengaturan (Ungu) -->
                            <a href="{{ route('settings.attendance') }}" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md flex flex-col items-center text-center transition-all group">
                                <div class="p-3 bg-purple-50 text-purple-600 border border-purple-100/60 rounded-2xl mb-2 group-hover:bg-purple-600 group-hover:text-white transition-all">
                                    <i data-lucide="settings" class="h-6 w-6"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-700">Pengaturan</span>
                            </a>
                            <a href="{{ route('attendance.reports') }}" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md flex flex-col items-center text-center transition-all group">
                                <div class="p-3 bg-purple-50 text-purple-600 border border-purple-100/60 rounded-2xl mb-2 group-hover:bg-purple-600 group-hover:text-white transition-all">
                                    <i data-lucide="bar-chart-2" class="h-6 w-6"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-700">Laporan</span>
                            </a>
                            <!-- Kategori 3: Izin & Cuti (Amber/Orange) -->
                            <a href="{{ route('leave-requests.create') }}" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md flex flex-col items-center text-center transition-all group">
                                <div class="p-3 bg-amber-50 text-amber-600 border border-amber-100/60 rounded-2xl mb-2 group-hover:bg-amber-600 group-hover:text-white transition-all">
                                    <i data-lucide="calendar-days" class="h-6 w-6"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-700">Izin Pribadi</span>
                            </a>
                            <a href="{{ route('leave-requests.create', ['user_id' => 'student']) }}" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md flex flex-col items-center text-center transition-all group">
                                <div class="p-3 bg-amber-50 text-amber-600 border border-amber-100/60 rounded-2xl mb-2 group-hover:bg-amber-600 group-hover:text-white transition-all">
                                    <i data-lucide="user-x" class="h-6 w-6"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-700">Izin Siswa</span>
                            </a>
                        @elseif($user->hasRole('headmaster'))
                            <!-- Headmaster Menu -->
                            <a href="{{ route('attendance.reports') }}" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md flex flex-col items-center text-center transition-all">
                                <div class="p-3 bg-blue-50 text-blue-600 rounded-2xl mb-2">
                                    <i data-lucide="bar-chart-2" class="h-6 w-6"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-700">Laporan Absensi</span>
                            </a>
                            <a href="{{ route('leave-requests.index') }}" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md flex flex-col items-center text-center transition-all">
                                <div class="p-3 bg-yellow-50 text-yellow-600 rounded-2xl mb-2">
                                    <i data-lucide="file-check" class="h-6 w-6"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-700">Persetujuan Izin</span>
                            </a>
                            <a href="{{ route('attendance.index') }}" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md flex flex-col items-center text-center transition-all">
                                <div class="p-3 bg-green-50 text-green-600 rounded-2xl mb-2">
                                    <i data-lucide="calendar-check" class="h-6 w-6"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-700">Status Absensi</span>
                            </a>
                            <a href="{{ route('attendance.export') }}" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md flex flex-col items-center text-center transition-all">
                                <div class="p-3 bg-purple-50 text-purple-600 rounded-2xl mb-2">
                                    <i data-lucide="download" class="h-6 w-6"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-700">Export Laporan</span>
                            </a>
                        @elseif($user->hasRole(['teacher', 'tu', 'bk', 'kesiswaan', 'admin']))
                            <!-- Employee Menu -->
                            <a href="{{ route('attendance.check-in') }}" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md flex flex-col items-center text-center transition-all">
                                <div class="p-3 bg-blue-50 text-blue-600 rounded-2xl mb-2">
                                    <i data-lucide="log-in" class="h-6 w-6"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-700">Absensi Masuk</span>
                            </a>
                            <a href="{{ route('attendance.check-out') }}" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md flex flex-col items-center text-center transition-all">
                                <div class="p-3 bg-green-50 text-green-600 rounded-2xl mb-2">
                                    <i data-lucide="log-out" class="h-6 w-6"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-700">Absensi Keluar</span>
                            </a>
                            @if($user->hasRole(['teacher','admin']))
                                <a href="{{ route('attendance.student-scan') }}" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md flex flex-col items-center text-center transition-all">
                                    <div class="p-3 bg-purple-50 text-purple-600 rounded-2xl mb-2">
                                        <i data-lucide="qr-code" class="h-6 w-6"></i>
                                    </div>
                                    <span class="text-xs font-bold text-gray-700">Scan Siswa</span>
                                </a>
                            @endif
                            <a href="{{ route('attendance.reports') }}" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md flex flex-col items-center text-center transition-all">
                                <div class="p-3 bg-orange-50 text-orange-600 rounded-2xl mb-2">
                                    <i data-lucide="bar-chart-2" class="h-6 w-6"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-700">Laporan</span>
                            </a>
                            <a href="{{ route('leave-requests.create') }}" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md flex flex-col items-center text-center transition-all">
                                <div class="p-3 bg-orange-50 text-orange-600 rounded-2xl mb-2">
                                    <i data-lucide="calendar-days" class="h-6 w-6"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-700">Izin Pribadi</span>
                            </a>
                            @if($user->hasRole(['teacher', 'admin']))
                                <a href="{{ route('leave-requests.create', ['user_id' => 'student']) }}" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md flex flex-col items-center text-center transition-all">
                                    <div class="p-3 bg-yellow-50 text-yellow-600 rounded-2xl mb-2">
                                        <i data-lucide="user-x" class="h-6 w-6"></i>
                                    </div>
                                    <span class="text-xs font-bold text-gray-700">Izin Siswa</span>
                                </a>
                            @endif
                        @elseif($user->hasRole('student'))
                            <!-- Student Menu -->
                            <a href="{{ route('attendance.reports') }}" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md flex flex-col items-center text-center transition-all">
                                <div class="p-3 bg-green-50 text-green-600 rounded-2xl mb-2">
                                    <i data-lucide="calendar" class="h-6 w-6"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-700">Riwayat Absensi</span>
                            </a>
                            <a href="{{ route('leave-requests.index') }}" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md flex flex-col items-center text-center transition-all">
                                <div class="p-3 bg-yellow-50 text-yellow-600 rounded-2xl mb-2">
                                    <i data-lucide="file-text" class="h-6 w-6"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-700">Izin Saya</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
@endsection

@push('scripts')
<style>
/* Welcome image: REMOVED - using custom banner image instead */

/* Custom banner image: full height background */
.banner-custom-image{
    position:absolute; right:0; top:-10px; height:calc(100% + 20px); width:50%; border-radius:0 12px 12px 0;
    opacity:0; 
    z-index: 1; pointer-events: none;
    transform: translateY(20px) scale(0.95);
    transition: opacity 0.8s ease, transform 0.8s ease;
}
.banner-custom-image.loaded{ 
    opacity:1; 
    transform: translateY(0) scale(1);
}

/* Hide banner image on mobile devices only */
@media (max-width: 767px) {
    .banner-custom-image {
        display: none !important;
    }
}

/* Hide banner image on mobile devices */
@media (max-width: 767px) {
    .banner-custom-image {
        display: none !important;
    }
}

@media (min-width:768px){ .banner-custom-image{ width:45%; } }
@media (min-width:1024px){ .banner-custom-image{ width:40%; } }

/* School photo background animation - lazy loading */
.school-photo-bg{
    opacity: 0;
    transform: scale(0.9) translateY(20px);
    transition: opacity 2s cubic-bezier(0.4, 0, 0.2, 1), transform 2s cubic-bezier(0.4, 0, 0.2, 1), filter 2s cubic-bezier(0.4, 0, 0.2, 1);
    filter: blur(3px);
}
.school-photo-bg.loaded{
    opacity: 1;
    transform: scale(1) translateY(0);
    filter: blur(0);
}

.welcome-hero-wrap{ position: relative; z-index: 2; }
/* Mobile adjustments: keep text readable */
@media (max-width: 639px){
  .welcome-hero-image{ height:120px; right:8px; top:-6px; }
}

/* Mini stepper for Absensi Hari Ini */
.att-stepper{ display:flex; align-items:center; gap:10px; }
.att-step{ display:flex; align-items:center; gap:6px; }
.att-step .dot{ width:10px; height:10px; border-radius:9999px; background:#d1d5db; }
.att-step.done .dot{ background:#10b981; }
.att-step .label{ font-size:.75rem; color:#6b7280; }
.att-step .time{ font-size:.75rem; color:#111827; font-weight:600; }
.att-connector{ flex:1; height:2px; background:#e5e7eb; }
.att-connector.half{ background:linear-gradient(90deg,#10b981 0%,#e5e7eb 50%); }
.att-connector.done{ background:#10b981; }

/* Custom sleek scrollbar for attendance feed */
.custom-feed-scrollbar::-webkit-scrollbar {
    width: 5px;
}
.custom-feed-scrollbar::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 9999px;
}
.custom-feed-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 9999px;
}
.custom-feed-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
<script>
    // add loaded class to custom banner image
    window.requestAnimationFrame(()=>{
        const bannerImg=document.querySelector('.banner-custom-image');
        console.log('Banner image element:', bannerImg);
        if(bannerImg){
            console.log('Adding loaded class to banner image');
            bannerImg.classList.add('loaded');
        } else {
            console.log('Banner image element not found!');
        }
        
        // add loaded class to school photo background with delay for lazy effect
        const schoolPhoto=document.querySelector('.school-photo-bg');
        if(schoolPhoto){
            console.log('Adding loaded class to school photo with delay');
            setTimeout(() => {
                schoolPhoto.classList.add('loaded');
            }, 300); // 300ms delay for smoother transition
        }
    });

    // Date Bottom Sheet helpers
    function openDateBottomSheet() {
        const backdrop = document.getElementById('date-sheet-backdrop');
        const sheet = document.getElementById('date-sheet');
        if (backdrop && sheet) {
            backdrop.classList.remove('hidden');
            setTimeout(() => {
                backdrop.classList.add('opacity-100');
                sheet.classList.remove('translate-y-full');
            }, 50);
        }
    }

    function closeDateBottomSheet() {
        const backdrop = document.getElementById('date-sheet-backdrop');
        const sheet = document.getElementById('date-sheet');
        if (backdrop && sheet) {
            backdrop.classList.remove('opacity-100');
            sheet.classList.add('translate-y-full');
            setTimeout(() => {
                backdrop.classList.add('hidden');
            }, 300);
        }
    }

    // Zona 2 Tab and Modal Logic
    @if(isset($metrics))
    const zona2Counts = {
        all: {{ $trueTotalIssuesCount ?? $totalIssuesCount ?? 0 }},
        incomplete: {{ $trueIncompleteCount ?? $incompleteCount ?? 0 }},
        non_user: {{ $trueNonUserCount ?? $nonUserCount ?? 0 }},
        leak: {{ $trueLeakCount ?? $leakCount ?? 0 }},
    };
    const zona2Data = {
        all: @json($allProblems ?? []),
        incomplete: @json($incompleteList ?? []),
        non_user: @json($nonUserList ?? []),
        leak: @json($leakList ?? []),
        startDate: @json($startDate ?? ''),
        endDate: @json($endDate ?? ''),
    };
    let currentZona2Tab = 'all';

    function switchZona2Tab(tab) {
        currentZona2Tab = tab;
        // daisyUI tab styling
        document.querySelectorAll('#zona2-tabs .tab').forEach(btn => {
            btn.classList.remove('tab-active', 'font-bold');
        });
        const activeBtn = document.getElementById('tab-btn-' + tab);
        if (activeBtn) {
            activeBtn.classList.add('tab-active', 'font-bold');
        }

        // Tab panels display
        document.querySelectorAll('.zona2-panel').forEach(p => p.classList.add('hidden'));
        const activePanel = document.getElementById('zona2-panel-' + tab);
        if (activePanel) activePanel.classList.remove('hidden');

        // Export button update
        const exportBtn = document.getElementById('zona2-export-btn');
        if (exportBtn) {
            const typeMap = { all: 'all', incomplete: 'incomplete', non_user: 'non_users', leak: 'leak' };
            const exportType = typeMap[tab] || 'all';
            exportBtn.href = `{{ route('dashboard.export') }}?type=${exportType}&start=${zona2Data.startDate}&end=${zona2Data.endDate}`;
        }

        // View All text update
        const count = zona2Counts[tab] !== undefined ? zona2Counts[tab] : (zona2Data[tab] || []).length;
        const viewAllText = document.getElementById('zona2-view-all-text');
        if (viewAllText) {
            viewAllText.innerText = `Lihat semua ${count} item`;
        }
    }

    function openZona2Modal() {
        const modal = document.getElementById('zona2-modal');
        const modalTitle = document.getElementById('zona2-modal-title');
        const modalBody = document.getElementById('zona2-modal-body');
        if (!modal || !modalTitle || !modalBody) return;

        const titles = {
            all: 'Daftar Semua Masalah (' + (zona2Data.all || []).length + ')',
            incomplete: 'Daftar Profil Kosong (' + (zona2Data.incomplete || []).length + ')',
            non_user: 'Daftar Tidak Aktif (' + (zona2Data.non_user || []).length + ')',
            leak: 'Daftar Leak Absensi (' + (zona2Data.leak || []).length + ')'
        };
        modalTitle.innerText = titles[currentZona2Tab] || 'Daftar Masalah';

        const items = zona2Data[currentZona2Tab] || [];
        if (items.length === 0) {
            modalBody.innerHTML = '<div class="py-8 text-center text-sm text-gray-400">Tidak ada item dalam kategori ini.</div>';
        } else {
            modalBody.innerHTML = items.map(item => `
                <div class="flex items-center justify-between py-3">
                    <div>
                        <p class="text-sm font-medium text-gray-900">${item.name}</p>
                        <p class="text-xs text-gray-500 mt-0.5">${item.subtitle}</p>
                    </div>
                    <div>
                        <span class="badge ${item.badge_class} badge-sm text-xs font-medium">
                            ${item.badge_text}
                        </span>
                    </div>
                </div>
            `).join('');
        }
        modal.classList.remove('hidden');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function closeZona2Modal() {
        const modal = document.getElementById('zona2-modal');
        if (modal) modal.classList.add('hidden');
    }
    @endif

    @if($isManagerial)
    // Live update for Attendance Feed, Late Today, and On Leave (no page refresh) - Managerial only
    (function initLiveAttendanceFeed() {
        let isPolling = false;
        let lastFeedJson = '';
        let lastLateJson = '';
        let lastLeaveJson = '';

        function pollFeed() {
            if (isPolling) return;
            isPolling = true;

            fetch('{{ route("dashboard.live-feed") }}', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => {
                if (!res.ok) throw new Error('Live feed response error');
                return res.json();
            })
            .then(data => {
                if (data && data.status === 'success') {
                    // 1. Update Attendance Feed
                    const feedContainer = document.getElementById('attendance-feed-list');
                    const newFeedJson = JSON.stringify(data.feed || []);
                    if (feedContainer && newFeedJson !== lastFeedJson) {
                        lastFeedJson = newFeedJson;
                        if (!data.feed || data.feed.length === 0) {
                            feedContainer.innerHTML = '<div class="py-12 text-center text-xs text-gray-400 flex items-center justify-center">Belum ada data absensi terbaru hari ini</div>';
                        } else {
                            feedContainer.innerHTML = data.feed.map(feed => {
                                const avatarHtml = feed.avatar 
                                    ? `<div class="avatar flex-shrink-0"><div class="w-8 h-8 rounded-full"><img src="${feed.avatar}" alt="${feed.name}" /></div></div>`
                                    : `<div class="avatar placeholder flex-shrink-0"><div class="bg-blue-50 text-blue-600 rounded-full w-8 h-8 text-xs font-bold flex items-center justify-center border border-blue-100"><span>${feed.initials || 'U'}</span></div></div>`;

                                const locationHtml = feed.location 
                                    ? `<span>•</span><span class="truncate max-w-[110px] text-gray-400" title="${feed.location}">${feed.location}</span>` 
                                    : '';

                                return `
                                <div class="flex items-center justify-between py-3 hover:bg-gray-50/50 rounded-xl px-1.5 transition-colors">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        ${avatarHtml}
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-900 leading-tight truncate">${feed.name}</p>
                                            <div class="flex items-center gap-1.5 text-xs text-gray-500 mt-0.5 flex-wrap">
                                                <span class="font-medium text-gray-600">${feed.event_type}</span>
                                                <span>•</span>
                                                <span class="flex items-center gap-0.5 text-gray-400">
                                                    <i data-lucide="clock" class="h-3 w-3"></i>
                                                    ${feed.time_str}
                                                </span>
                                                ${locationHtml}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right flex-shrink-0 pl-2">
                                        <span class="text-xs text-gray-400 whitespace-nowrap">${feed.relative_time}</span>
                                    </div>
                                </div>`;
                            }).join('');
                            if (window.lucide) lucide.createIcons({ root: feedContainer });
                        }
                    }

                    // 2. Update Late Today
                    const lateContainer = document.getElementById('late-today-list');
                    const lateBadge = document.getElementById('late-today-badge');
                    const newLateJson = JSON.stringify(data.late_today || []);
                    if (lateBadge) {
                        lateBadge.innerText = data.late_count || 0;
                        if ((data.late_count || 0) > 0) {
                            lateBadge.classList.remove('hidden');
                        } else {
                            lateBadge.classList.add('hidden');
                        }
                    }
                    if (lateContainer && newLateJson !== lastLateJson) {
                        lastLateJson = newLateJson;
                        if (!data.late_today || data.late_today.length === 0) {
                            lateContainer.innerHTML = '<div class="py-12 text-center text-xs text-emerald-600 font-medium flex-1 flex flex-col items-center justify-center gap-1.5"><i data-lucide="check-circle" class="h-5 w-5 text-emerald-500"></i><span>Tidak ada yang terlambat hari ini</span></div>';
                        } else {
                            lateContainer.innerHTML = data.late_today.map(late => {
                                const avatarHtml = late.avatar 
                                    ? `<div class="avatar flex-shrink-0"><div class="w-8 h-8 rounded-full"><img src="${late.avatar}" alt="${late.name}" /></div></div>`
                                    : `<div class="avatar placeholder flex-shrink-0"><div class="bg-amber-50 text-amber-600 rounded-full w-8 h-8 text-xs font-bold flex items-center justify-center border border-amber-100"><span>${late.initials}</span></div></div>`;

                                return `
                                <div class="flex items-center justify-between py-2.5 hover:bg-gray-50/50 rounded-xl px-1.5 transition-colors">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        ${avatarHtml}
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-900 leading-tight truncate">${late.name}</p>
                                            <p class="text-xs text-gray-500 mt-0.5 truncate">${late.subtitle}</p>
                                            <div class="flex items-center gap-1 text-xs text-gray-400 mt-0.5">
                                                <i data-lucide="clock" class="h-3 w-3"></i>
                                                <span>Masuk pukul ${late.check_in_time}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right flex-shrink-0 pl-2">
                                        <span class="badge badge-warning badge-sm text-xs font-medium inline-flex items-center gap-0.5 whitespace-nowrap">
                                            <i data-lucide="hourglass" class="h-2.5 w-2.5"></i>
                                            ${late.late_duration}
                                        </span>
                                    </div>
                                </div>`;
                            }).join('');
                        }
                        if (window.lucide) lucide.createIcons({ root: lateContainer });
                    }

                    // 3. Update On Leave Today
                    const leaveContainer = document.getElementById('on-leave-list');
                    const leaveBadge = document.getElementById('on-leave-badge');
                    const newLeaveJson = JSON.stringify(data.on_leave_today || []);
                    if (leaveBadge) {
                        leaveBadge.innerText = data.on_leave_count || 0;
                        if ((data.on_leave_count || 0) > 0) {
                            leaveBadge.classList.remove('hidden');
                        } else {
                            leaveBadge.classList.add('hidden');
                        }
                    }
                    if (leaveContainer && newLeaveJson !== lastLeaveJson) {
                        lastLeaveJson = newLeaveJson;
                        if (!data.on_leave_today || data.on_leave_today.length === 0) {
                            leaveContainer.innerHTML = '<div class="py-12 text-center text-xs text-gray-400 flex-1 flex flex-col items-center justify-center gap-1.5"><i data-lucide="user-check" class="h-5 w-5 text-gray-300"></i><span>Tidak ada yang sedang izin/cuti hari ini</span></div>';
                        } else {
                            leaveContainer.innerHTML = data.on_leave_today.map(leave => {
                                const avatarHtml = leave.avatar 
                                    ? `<div class="avatar flex-shrink-0"><div class="w-8 h-8 rounded-full"><img src="${leave.avatar}" alt="${leave.name}" /></div></div>`
                                    : `<div class="avatar placeholder flex-shrink-0"><div class="bg-purple-50 text-purple-600 rounded-full w-8 h-8 text-xs font-bold flex items-center justify-center border border-purple-100"><span>${leave.initials}</span></div></div>`;

                                return `
                                <div class="flex items-center justify-between py-2.5 hover:bg-gray-50/50 rounded-xl px-1.5 transition-colors">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        ${avatarHtml}
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-900 leading-tight truncate">${leave.name}</p>
                                            <p class="text-xs text-gray-500 mt-0.5 truncate">${leave.subtitle}</p>
                                            <div class="flex items-center gap-1 text-xs text-gray-400 mt-0.5">
                                                <i data-lucide="calendar" class="h-3 w-3"></i>
                                                <span>${leave.date_range}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right flex-shrink-0 pl-2">
                                        <span class="badge ${leave.badge_class} badge-sm text-xs font-medium whitespace-nowrap">
                                            ${leave.type_label}
                                        </span>
                                    </div>
                                </div>`;
                            }).join('');
                        }
                        if (window.lucide) lucide.createIcons({ root: leaveContainer });
                    }
                }
            })
            .catch(err => {
                console.debug('Polling live attendance feed:', err);
            })
            .finally(() => {
                isPolling = false;
            });
        }

        // Poll every 4 seconds without page reload
        setInterval(pollFeed, 4000);
    })();
    @endif
</script>
@endpush
