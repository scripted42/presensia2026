<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($school) ? $school->name : 'Presensia' }} - Arsip Laporan Absensi</title>
    
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: #F8FAFC;
        }
        .folder-card {
            transition: all 0.2s ease-in-out;
        }
        .folder-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04);
        }
        .file-row {
            transition: background-color 0.15s ease-in-out;
        }
        .file-row:hover {
            background-color: #F1F5F9;
        }
    </style>
</head>
<body class="min-h-screen text-gray-800 flex flex-col justify-between">

    <!-- Top Navigation Bar -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-30 shadow-xs">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center text-white shadow-md">
                    <i class="fas fa-file-excel text-lg"></i>
                </div>
                <div>
                    <h1 class="text-base sm:text-lg font-bold text-gray-900 leading-tight">
                        {{ isset($school) ? $school->name : 'Presensia' }}
                    </h1>
                    <p class="text-xs text-gray-500 font-medium flex items-center gap-1.5">
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Portal Unduh Rekap Absensi Siswa
                    </p>
                </div>
            </div>

            <!-- Tanggal Jam Saat Ini -->
            <div class="hidden sm:flex flex-col items-end text-xs text-gray-500">
                <span class="font-medium text-gray-700">Waktu Server</span>
                <span id="liveClock">{{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB</span>
            </div>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full flex-grow">

        @if($needsAuth)
            <!-- Tampilan Jika Membutuhkan Kunci Akses (Klien belum memasukkan token) -->
            <div class="max-w-md mx-auto my-12 bg-white rounded-2xl shadow-xl border border-gray-100 p-8 text-center">
                <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-900 mb-2">Akses Arsip Absensi</h2>
                <p class="text-sm text-gray-500 mb-6">
                    Halaman ini dilindungi kunci akses keamanan sekolah. Masukkan token akses untuk melihat dan mengunduh laporan.
                </p>

                @if($errorMessage)
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-600 rounded-xl text-xs font-medium text-left flex items-start gap-2">
                        <i class="fas fa-exclamation-circle mt-0.5"></i>
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
                        <i class="fas fa-unlock-alt"></i>
                        Buka Arsip Laporan
                    </button>
                </form>
            </div>
        @else

            <!-- Breadcrumbs Bar -->
            <div class="flex items-center justify-between flex-wrap gap-3 mb-6 bg-white p-3 rounded-xl border border-gray-200 shadow-2xs">
                <nav class="flex items-center text-sm font-medium text-gray-600">
                    <a href="{{ route('archive.index', ['key' => $key]) }}" class="hover:text-blue-600 flex items-center gap-1.5 text-gray-800">
                        <i class="fas fa-folder text-amber-500 text-base"></i>
                        <span>Arsip Utama</span>
                    </a>
                    @if($isFolder)
                        <span class="mx-2 text-gray-400">/</span>
                        <span class="text-blue-700 font-bold flex items-center gap-1.5">
                            <i class="fas fa-folder-open text-amber-500"></i>
                            {{ $month_label }}
                        </span>
                    @endif
                </nav>

                @if($isFolder)
                    <a href="{{ route('archive.index', ['key' => $key]) }}" 
                       class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">
                        <i class="fas fa-arrow-left"></i>
                        Kembali ke Daftar Bulan
                    </a>
                @endif
            </div>

            @if(!$isFolder)
                <!-- ================= ROOT VIEW (DAFTAR FOLDER BULAN) ================= -->

                <!-- Quick Hero Card: Rekap Hari Ini -->
                <div class="bg-linear-to-r from-blue-700 via-indigo-700 to-blue-800 rounded-2xl shadow-lg text-white p-5 sm:p-7 mb-8 flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="space-y-1 text-center md:text-left">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/30 text-blue-100 text-xs font-semibold backdrop-blur-xs">
                            <i class="fas fa-bolt text-yellow-300"></i>
                            Akses Cepat Hari Ini (Real-Time)
                        </div>
                        <h2 class="text-xl sm:text-2xl font-black tracking-tight">
                            Rekap Absensi: {{ $today['date_formatted'] }}
                        </h2>
                        <p class="text-xs sm:text-sm text-blue-100/90 max-w-xl">
                            Unduh seluruh daftar kehadiran siswa hari ini (termasuk status ontime, terlambat, izin, sakit, dan alpha) dalam satu klik langsung.
                        </p>
                    </div>
                    <div class="shrink-0 w-full md:w-auto">
                        <a href="{{ route('archive.download.daily', ['year' => $today['year'], 'month' => $today['month'], 'day' => $today['day'], 'key' => $key]) }}"
                           class="w-full md:w-auto inline-flex items-center justify-center gap-2.5 px-6 py-3.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-xl shadow-md hover:shadow-lg transition transform hover:-translate-y-0.5 text-sm">
                            <i class="fas fa-file-excel text-lg"></i>
                            <span>Unduh Rekap Hari Ini (.xlsx)</span>
                        </a>
                    </div>
                </div>

                <!-- Section Header -->
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-folder-tree text-amber-500"></i>
                            Daftar Folder Arsip Bulanan
                        </h3>
                        <p class="text-xs text-gray-500">Pilih salah satu folder bulan untuk melihat dan mengunduh rekap harian atau bulanan.</p>
                    </div>
                </div>

                <!-- Grid Folder Kartu -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($months as $m)
                        <a href="{{ route('archive.folder', ['year' => $m['year'], 'month' => $m['month'], 'key' => $key]) }}" 
                           class="folder-card bg-white p-5 rounded-2xl border {{ $m['is_current'] ? 'border-blue-400 ring-2 ring-blue-100' : 'border-gray-200' }} shadow-xs block">
                            <div class="flex items-start justify-between mb-3">
                                <div class="w-12 h-12 rounded-xl {{ $m['is_current'] ? 'bg-blue-50 text-blue-600' : 'bg-amber-50 text-amber-600' }} flex items-center justify-center text-2xl">
                                    <i class="fas fa-folder"></i>
                                </div>
                                @if($m['is_current'])
                                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">
                                        Bulan Berjalan
                                    </span>
                                @endif
                            </div>
                            <h4 class="font-bold text-gray-900 text-base mb-1 group-hover:text-blue-600 transition">
                                {{ $m['label'] }}
                            </h4>
                            <p class="text-xs text-gray-500 flex items-center gap-1.5">
                                <i class="fas fa-file text-gray-400"></i>
                                <span>{{ $m['days_count'] }} Hari Absensi</span>
                            </p>
                            <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs font-semibold text-blue-600">
                                <span>Buka Folder</span>
                                <i class="fas fa-chevron-right text-[10px]"></i>
                            </div>
                        </a>
                    @endforeach
                </div>

            @else
                <!-- ================= FOLDER VIEW (ISI BULAN TERPILIH) ================= -->

                <!-- Card 1: Rekap Keseluruhan 1 Bulan Penuh -->
                <div class="bg-white rounded-2xl border-2 border-emerald-500 shadow-sm p-5 sm:p-6 mb-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-3xl shrink-0">
                            <i class="fas fa-file-excel"></i>
                        </div>
                        <div>
                            <div class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md bg-emerald-100 text-emerald-800 text-[11px] font-bold uppercase tracking-wider mb-1">
                                Rekap Keseluruhan
                            </div>
                            <h3 class="text-lg sm:text-xl font-bold text-gray-900">
                                {{ $monthly_filename }}
                            </h3>
                            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                                Berisi rekapitulasi lengkap seluruh siswa selama bulan {{ $month_label }} (No, Tanggal, NIS, Nama, Kelas, Jam Masuk, Status, Keterangan).
                            </p>
                        </div>
                    </div>
                    <div class="w-full md:w-auto shrink-0">
                        <a href="{{ route('archive.download.monthly', ['year' => $year, 'month' => $month, 'key' => $key]) }}"
                           class="w-full md:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-md transition text-sm">
                            <i class="fas fa-download"></i>
                            <span>Unduh Rekap Bulanan (.xlsx)</span>
                        </a>
                    </div>
                </div>

                <!-- Section: Rekap Harian -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-200 bg-gray-50/70 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h3 class="font-bold text-gray-900 text-sm sm:text-base flex items-center gap-2">
                                <i class="fas fa-calendar-days text-blue-600"></i>
                                Berkas Rekap Harian (Bulan {{ $month_label }})
                            </h3>
                            <p class="text-xs text-gray-500">Klik tombol unduh pada tanggal yang diinginkan.</p>
                        </div>
                        <span class="text-xs font-semibold text-gray-500 bg-white px-3 py-1 rounded-lg border border-gray-200 self-start sm:self-auto">
                            Total: {{ count($days) }} Berkas Harian
                        </span>
                    </div>

                    <!-- File Listing Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-100/60 text-gray-500 uppercase text-[10px] tracking-wider font-semibold border-b border-gray-200">
                                <tr>
                                    <th class="py-3 px-4 w-12 text-center">Tipe</th>
                                    <th class="py-3 px-4">Nama File Rekap</th>
                                    <th class="py-3 px-4">Hari & Tanggal</th>
                                    <th class="py-3 px-4 text-center">Status</th>
                                    <th class="py-3 px-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($days as $d)
                                    <tr class="file-row {{ $d['is_today'] ? 'bg-amber-50/40' : '' }}">
                                        <td class="py-3 px-4 text-center">
                                            <i class="fas fa-file-excel text-emerald-600 text-lg"></i>
                                        </td>
                                        <td class="py-3 px-4 font-semibold text-gray-900">
                                            <a href="{{ route('archive.download.daily', ['year' => $year, 'month' => $month, 'day' => $d['day'], 'key' => $key]) }}"
                                               class="hover:text-blue-600 flex items-center gap-2">
                                                <span>{{ $d['filename'] }}</span>
                                                @if($d['is_today'])
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[9px] font-extrabold bg-red-100 text-red-600 animate-pulse">
                                                        LIVE
                                                    </span>
                                                @endif
                                            </a>
                                        </td>
                                        <td class="py-3 px-4 text-xs text-gray-600 whitespace-nowrap">
                                            {{ $d['date_formatted'] }}
                                        </td>
                                        <td class="py-3 px-4 text-center whitespace-nowrap">
                                            @if($d['is_today'])
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    Hari Ini
                                                </span>
                                            @elseif($d['is_weekend'])
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-gray-100 text-gray-600">
                                                    Weekend
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-blue-50 text-blue-700">
                                                    Tersedia
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-right whitespace-nowrap">
                                            <a href="{{ route('archive.download.daily', ['year' => $year, 'month' => $month, 'day' => $d['day'], 'key' => $key]) }}"
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 {{ $d['is_today'] ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : 'bg-gray-100 hover:bg-gray-200 text-gray-700' }} text-xs font-semibold rounded-lg transition">
                                                <i class="fas fa-download text-[11px]"></i>
                                                <span>Unduh</span>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-gray-400 text-xs">
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
    <footer class="bg-white border-t border-gray-200 mt-12 py-5 text-center text-xs text-gray-400">
        <div class="max-w-6xl mx-auto px-4">
            <p>&copy; {{ date('Y') }} Presensia — Sistem Presensi Digital Terintegrasi.</p>
        </div>
    </footer>

    <script>
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
    </script>
</body>
</html>

