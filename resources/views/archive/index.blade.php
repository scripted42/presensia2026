<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($school) ? $school->name : 'Presensia' }} - Arsip Laporan Absensi</title>
    
    <!-- Google Fonts: Baloo 2, Inter, Manrope (Identik dengan Desain Presensia) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;600;700;800&family=Inter:wght@300;400;500;600;700;800&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    
    <!-- Lucide Icons (SVG) -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Chart.js (Untuk Bar Chart Presensi Bulanan) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --font-heading: 'Baloo 2', system-ui, -apple-system, sans-serif;
            --font-body: 'Inter', system-ui, -apple-system, sans-serif;
            --font-numeric: 'Manrope', system-ui, -apple-system, sans-serif;
        }

        body {
            font-family: var(--font-body);
            background-color: #F8FAFC;
            color: #1E293B;
            -webkit-font-smoothing: antialiased;
        }

        .font-heading {
            font-family: var(--font-heading);
        }

        .font-numeric {
            font-family: var(--font-numeric);
        }

        .hero-banner {
            background: linear-gradient(135deg, #1E3A8A 0%, #2563EB 55%, #4338CA 100%) !important;
            color: #FFFFFF !important;
        }

        .folder-card {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .folder-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px -6px rgba(37, 99, 235, 0.12), 0 4px 6px -2px rgba(0, 0, 0, 0.04);
        }

        .file-row {
            transition: background-color 0.15s ease-in-out;
        }

        .file-row:hover {
            background-color: #F1F5F9;
        }

        /* Styling Icon Lucide */
        .lucide {
            display: inline-block;
            vertical-align: middle;
            stroke-width: 2.2px;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-between">

    <!-- Top Navigation Bar -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-30 shadow-2xs">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex items-center justify-between">
            <div class="flex items-center space-x-3.5">
                <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center text-white shadow-md shadow-blue-500/20 shrink-0">
                    <i data-lucide="file-spreadsheet" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <h1 class="font-heading text-lg sm:text-xl font-bold text-gray-900 leading-tight">
                        {{ isset($school) ? $school->name : 'Presensia' }}
                    </h1>
                    <p class="text-xs text-gray-500 font-medium flex items-center gap-1.5">
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Portal Unduh Rekap Absensi Siswa
                    </p>
                </div>
            </div>

            <!-- Tanggal Jam Server & Status Akses -->
            <div class="hidden sm:flex items-center gap-4 text-xs">
                <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 font-semibold border border-emerald-200/60">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                    <span>Akses Aman</span>
                </div>
                <div class="flex flex-col items-end text-gray-500">
                    <span class="font-medium text-gray-700 flex items-center gap-1">
                        <i data-lucide="clock" class="w-3 h-3 text-gray-400"></i> Waktu Server
                    </span>
                    <span id="liveClock" class="font-numeric font-semibold text-gray-800">
                        {{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB
                    </span>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full flex-grow">

        @if($needsAuth)
            <!-- ================= TAMPILAN PROTEKSI KUNCI AKSES ================= -->
            <div class="max-w-md mx-auto my-12 bg-white rounded-3xl shadow-xl border border-gray-100 p-8 text-center">
                <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="lock" class="w-8 h-8 text-blue-600"></i>
                </div>
                <h2 class="font-heading text-2xl font-bold text-gray-900 mb-2">Akses Arsip Absensi</h2>
                <p class="text-sm text-gray-500 mb-6">
                    Halaman ini dilindungi kunci akses keamanan sekolah. Masukkan token akses untuk melihat dan mengunduh laporan.
                </p>

                @if($errorMessage)
                    <div class="mb-4 p-3.5 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-xs font-semibold text-left flex items-start gap-2">
                        <i data-lucide="alert-circle" class="w-4 h-4 text-rose-500 shrink-0 mt-0.5"></i>
                        <span>{{ $errorMessage }}</span>
                    </div>
                @endif

                <form method="GET" action="{{ route('archive.index') }}" class="space-y-4">
                    <div>
                        <input type="text" name="key" value="{{ request('key') }}" placeholder="Masukkan Kunci Akses..." required autofocus
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 text-center font-mono text-sm tracking-wide focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-hidden">
                    </div>
                    <button type="submit" 
                        class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-sm shadow-md hover:shadow-lg transition flex items-center justify-center gap-2">
                        <i data-lucide="key-round" class="w-4 h-4"></i>
                        <span>Buka Arsip Laporan</span>
                    </button>
                </form>
            </div>
        @else

            <!-- Breadcrumbs Navigation -->
            <div class="flex items-center justify-between flex-wrap gap-3 mb-6 bg-white p-3.5 rounded-2xl border border-gray-200 shadow-2xs">
                <nav class="flex items-center text-sm font-semibold text-gray-600">
                    <a href="{{ route('archive.index', ['key' => $key]) }}" class="hover:text-blue-600 flex items-center gap-1.5 text-gray-800">
                        <i data-lucide="folder" class="w-4 h-4 text-amber-500"></i>
                        <span>Arsip Utama</span>
                    </a>
                    @if($isFolder)
                        <i data-lucide="chevron-right" class="w-4 h-4 mx-1.5 text-gray-400"></i>
                        <span class="text-blue-600 font-bold flex items-center gap-1.5">
                            <i data-lucide="folder-open" class="w-4 h-4 text-blue-500"></i>
                            {{ $month_label }}
                        </span>
                    @endif
                </nav>

                @if($isFolder)
                    <a href="{{ route('archive.index', ['key' => $key]) }}" 
                       class="inline-flex items-center gap-2 text-xs font-bold px-3.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl transition">
                        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                        <span>Kembali ke Daftar Bulan</span>
                    </a>
                @endif
            </div>

            @if(!$isFolder)
                <!-- ================= ROOT VIEW (DAFTAR FOLDER + GRAFIK) ================= -->

                <!-- Quick Hero Card: Rekap Hari Ini -->
                <div class="hero-banner rounded-3xl shadow-xl p-6 sm:p-8 mb-8 flex flex-col md:flex-row items-center justify-between gap-6 relative overflow-hidden">
                    <div class="space-y-2 text-center md:text-left z-10">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 text-white text-xs font-bold tracking-wide backdrop-blur-xs">
                            <i data-lucide="zap" class="w-3.5 h-3.5 text-amber-300"></i>
                            <span>Akses Cepat Hari Ini (Real-Time)</span>
                        </div>
                        <h2 class="font-heading text-2xl sm:text-3xl font-extrabold text-white tracking-tight leading-snug">
                            Rekap Absensi: {{ $today['date_formatted'] }}
                        </h2>
                        <p class="text-xs sm:text-sm text-blue-100 max-w-xl font-normal leading-relaxed">
                            Unduh seluruh daftar kehadiran siswa hari ini (termasuk status ontime, terlambat, izin, sakit, dan alpha) dalam satu klik langsung format Excel.
                        </p>
                    </div>
                    <div class="shrink-0 w-full md:w-auto z-10">
                        <a href="{{ route('archive.download.daily', ['year' => $today['year'], 'month' => $today['month'], 'day' => $today['day'], 'key' => $key]) }}"
                           class="w-full md:w-auto inline-flex items-center justify-center gap-2.5 px-6 py-4 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-2xl shadow-lg hover:shadow-xl transition transform hover:-translate-y-0.5 text-sm">
                            <i data-lucide="download" class="w-4 h-4"></i>
                            <span>Unduh Rekap Hari Ini (.xlsx)</span>
                        </a>
                    </div>
                </div>

                <!-- ================= BAR CHART PRESENSI BULANAN ================= -->
                <div class="bg-white rounded-3xl border border-gray-200/90 shadow-sm p-6 mb-8">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-gray-100">
                        <div>
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                                    <i data-lucide="bar-chart-3" class="w-4 h-4 text-blue-600"></i>
                                </div>
                                <h3 class="font-heading text-lg sm:text-xl font-bold text-gray-900">
                                    Grafik Kehadiran Siswa per Bulan
                                </h3>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                Tren statistik absensi 6 bulan terakhir (Tepat Waktu, Terlambat, Izin/Sakit, dan Alpha).
                            </p>
                        </div>

                        <!-- KPI Indicator Badges -->
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 text-xs font-semibold border border-emerald-200/50">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                Tepat Waktu: <strong class="font-numeric">{{ number_format($chartData['grand_ontime'] ?? 0) }}</strong>
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 text-amber-700 text-xs font-semibold border border-amber-200/50">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                Terlambat: <strong class="font-numeric">{{ number_format($chartData['grand_late'] ?? 0) }}</strong>
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 text-blue-700 text-xs font-semibold border border-blue-200/50">
                                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                Izin/Sakit: <strong class="font-numeric">{{ number_format($chartData['grand_leave'] ?? 0) }}</strong>
                            </span>
                        </div>
                    </div>

                    <!-- Canvas Chart.js -->
                    <div class="relative w-full" style="min-height: 260px; max-height: 320px;">
                        <canvas id="monthlyAttendanceChart"></canvas>
                    </div>
                </div>

                <!-- Section Header: Folder Bulanan -->
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-heading text-xl font-bold text-gray-900 flex items-center gap-2">
                            <i data-lucide="folder-tree" class="w-5 h-5 text-amber-500"></i>
                            <span>Daftar Folder Arsip Bulanan</span>
                        </h3>
                        <p class="text-xs text-gray-500">Pilih salah satu folder bulan untuk melihat dan mengunduh rekap harian atau bulanan.</p>
                    </div>
                </div>

                <!-- Grid Folder Kartu -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($months as $m)
                        <a href="{{ route('archive.folder', ['year' => $m['year'], 'month' => $m['month'], 'key' => $key]) }}" 
                           class="folder-card bg-white p-5 rounded-3xl border {{ $m['is_current'] ? 'border-blue-400 ring-2 ring-blue-100 shadow-sm' : 'border-gray-200 shadow-2xs' }} block group">
                            <div class="flex items-start justify-between mb-3.5">
                                <div class="w-12 h-12 rounded-2xl {{ $m['is_current'] ? 'bg-blue-50 text-blue-600' : 'bg-amber-50 text-amber-500' }} flex items-center justify-center">
                                    <i data-lucide="{{ $m['is_current'] ? 'folder-open' : 'folder' }}" class="w-6 h-6"></i>
                                </div>
                                @if($m['is_current'])
                                    <span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full bg-blue-100 text-blue-700">
                                        Bulan Berjalan
                                    </span>
                                @endif
                            </div>
                            <h4 class="font-heading font-bold text-gray-900 text-lg mb-1 group-hover:text-blue-600 transition">
                                {{ $m['label'] }}
                            </h4>
                            <p class="text-xs text-gray-500 flex items-center gap-1.5 font-numeric">
                                <i data-lucide="calendar" class="w-3.5 h-3.5 text-gray-400"></i>
                                <span>{{ $m['days_count'] }} Hari Absensi</span>
                            </p>
                            <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs font-bold text-blue-600">
                                <span>Buka Folder</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5 transition group-hover:translate-x-1"></i>
                            </div>
                        </a>
                    @endforeach
                </div>

            @else
                <!-- ================= FOLDER VIEW (ISI BULAN TERPILIH) ================= -->

                <!-- Card 1: Rekap Keseluruhan 1 Bulan Penuh -->
                <div class="bg-white rounded-3xl border-2 border-emerald-500 shadow-sm p-6 mb-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                            <i data-lucide="file-spreadsheet" class="w-7 h-7 text-emerald-600"></i>
                        </div>
                        <div>
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-emerald-100 text-emerald-800 text-[11px] font-bold uppercase tracking-wider mb-1">
                                <i data-lucide="sparkles" class="w-3 h-3"></i>
                                Rekap Keseluruhan 1 Bulan
                            </div>
                            <h3 class="font-heading text-xl sm:text-2xl font-bold text-gray-900">
                                {{ $monthly_filename }}
                            </h3>
                            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                                Berisi rekapitulasi lengkap seluruh siswa selama bulan {{ $month_label }} (No, Tanggal, NIS, Nama, Kelas, Jam Masuk, Status, Keterangan).
                            </p>
                        </div>
                    </div>
                    <div class="w-full md:w-auto shrink-0">
                        <a href="{{ route('archive.download.monthly', ['year' => $year, 'month' => $month, 'key' => $key]) }}"
                           class="w-full md:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-2xl shadow-md transition text-sm">
                            <i data-lucide="download" class="w-4 h-4"></i>
                            <span>Unduh Rekap Bulanan (.xlsx)</span>
                        </a>
                    </div>
                </div>

                <!-- Section: Rekap Harian -->
                <div class="bg-white rounded-3xl border border-gray-200 shadow-2xs overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/70 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h3 class="font-heading font-bold text-gray-900 text-base sm:text-lg flex items-center gap-2">
                                <i data-lucide="calendar-days" class="w-4 h-4 text-blue-600"></i>
                                <span>Berkas Rekap Harian (Bulan {{ $month_label }})</span>
                            </h3>
                            <p class="text-xs text-gray-500">Klik tombol unduh pada tanggal yang diinginkan.</p>
                        </div>
                        <span class="text-xs font-numeric font-bold text-gray-600 bg-white px-3 py-1 rounded-xl border border-gray-200 self-start sm:self-auto">
                            Total: {{ count($days) }} Berkas Harian
                        </span>
                    </div>

                    <!-- File Listing Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-100/70 text-gray-500 uppercase text-[10px] tracking-wider font-bold border-b border-gray-200">
                                <tr>
                                    <th class="py-3.5 px-5 w-14 text-center">Tipe</th>
                                    <th class="py-3.5 px-4">Nama File Rekap</th>
                                    <th class="py-3.5 px-4">Hari & Tanggal</th>
                                    <th class="py-3.5 px-4 text-center">Status</th>
                                    <th class="py-3.5 px-5 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($days as $d)
                                    <tr class="file-row {{ $d['is_today'] ? 'bg-amber-50/40' : '' }}">
                                        <td class="py-3.5 px-5 text-center">
                                            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto">
                                                <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-600"></i>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4 font-semibold text-gray-900">
                                            <a href="{{ route('archive.download.daily', ['year' => $year, 'month' => $month, 'day' => $d['day'], 'key' => $key]) }}"
                                               class="hover:text-blue-600 flex items-center gap-2">
                                                <span>{{ $d['filename'] }}</span>
                                                @if($d['is_today'])
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-red-100 text-red-600 animate-pulse font-numeric">
                                                        LIVE
                                                    </span>
                                                @endif
                                            </a>
                                        </td>
                                        <td class="py-3.5 px-4 text-xs text-gray-600 whitespace-nowrap">
                                            {{ $d['date_formatted'] }}
                                        </td>
                                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                            @if($d['is_today'])
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    Hari Ini
                                                </span>
                                            @elseif($d['is_weekend'])
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-[11px] font-medium bg-gray-100 text-gray-600">
                                                    <i data-lucide="sun" class="w-3 h-3 text-amber-500"></i>
                                                    Weekend
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-[11px] font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                                    <i data-lucide="check-circle-2" class="w-3 h-3 text-blue-500"></i>
                                                    Tersedia
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                            <a href="{{ route('archive.download.daily', ['year' => $year, 'month' => $month, 'day' => $d['day'], 'key' => $key]) }}"
                                               class="inline-flex items-center gap-1.5 px-3.5 py-2 {{ $d['is_today'] ? 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm' : 'bg-gray-100 hover:bg-gray-200 text-gray-700' }} text-xs font-bold rounded-xl transition">
                                                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                                <span>Unduh</span>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-12 text-center text-gray-400 text-xs">
                                            Belum ada berkas pada bulan ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        @endif

    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-12 py-6 text-center text-xs text-gray-400">
        <div class="max-w-6xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-2">
            <p>&copy; {{ date('Y') }} Presensia — Sistem Presensi Digital Terintegrasi.</p>
            <p class="text-[11px] text-gray-400">SMP Negeri 14 Surabaya</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Inisialisasi Semua Icon Lucide SVG
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            // Inisialisasi Bar Chart jika canvas ada
            const chartCanvas = document.getElementById('monthlyAttendanceChart');
            @if(!$isFolder && isset($chartData))
            if (chartCanvas) {
                const chartLabels = {!! json_encode($chartData['labels'] ?? []) !!};
                const ontimeData = {!! json_encode($chartData['ontime'] ?? []) !!};
                const lateData = {!! json_encode($chartData['late'] ?? []) !!};
                const leaveData = {!! json_encode($chartData['leave'] ?? []) !!};
                const alphaData = {!! json_encode($chartData['alpha'] ?? []) !!};

                new Chart(chartCanvas, {
                    type: 'bar',
                    data: {
                        labels: chartLabels,
                        datasets: [
                            {
                                label: 'Tepat Waktu',
                                data: ontimeData,
                                backgroundColor: '#10B981',
                                borderRadius: 6,
                                borderSkipped: false,
                            },
                            {
                                label: 'Terlambat',
                                data: lateData,
                                backgroundColor: '#F59E0B',
                                borderRadius: 6,
                                borderSkipped: false,
                            },
                            {
                                label: 'Izin / Sakit',
                                data: leaveData,
                                backgroundColor: '#3B82F6',
                                borderRadius: 6,
                                borderSkipped: false,
                            },
                            {
                                label: 'Alpha',
                                data: alphaData,
                                backgroundColor: '#EF4444',
                                borderRadius: 6,
                                borderSkipped: false,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                align: 'end',
                                labels: {
                                    usePointStyle: true,
                                    boxWidth: 8,
                                    boxHeight: 8,
                                    font: {
                                        family: "'Inter', sans-serif",
                                        size: 11,
                                        weight: '600'
                                    },
                                    padding: 15
                                }
                            },
                            tooltip: {
                                backgroundColor: '#1E293B',
                                titleFont: { family: "'Baloo 2', sans-serif", size: 13, weight: 'bold' },
                                bodyFont: { family: "'Inter', sans-serif", size: 12 },
                                padding: 12,
                                cornerRadius: 10,
                                displayColors: true,
                                callbacks: {
                                    label: function (context) {
                                        return ' ' + context.dataset.label + ': ' + context.parsed.y + ' siswa';
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                stacked: false,
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        family: "'Inter', sans-serif",
                                        size: 11,
                                        weight: '600'
                                    },
                                    color: '#64748B'
                                }
                            },
                            y: {
                                stacked: false,
                                beginAtZero: true,
                                grid: {
                                    color: '#F1F5F9'
                                },
                                ticks: {
                                    precision: 0,
                                    font: {
                                        family: "'Manrope', sans-serif",
                                        size: 11
                                    },
                                    color: '#94A3B8'
                                }
                            }
                        }
                    }
                });
            }
            @endif

            // Update live clock
            function updateClock() {
                const el = document.getElementById('liveClock');
                if (el) {
                    const now = new Date();
                    const options = { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
                    el.innerText = now.toLocaleDateString('id-ID', options) + ' WIB';
                }
            }
            setInterval(updateClock, 1000);
        });
    </script>
</body>
</html>
