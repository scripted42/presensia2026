<!-- Main Dashboard 2-Column Layout (Sejajar mulai dari Statistics Cards) -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8 items-stretch">
    <!-- Kolom Kiri (8 Kolom Desktop): Statistics Cards, Ringkasan Performa, Perlu Ditindaklanjuti, Terlambat & Izin -->
    <div class="lg:col-span-8 flex flex-col gap-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5 desktop-stats-grid">
            @if(isset($stats['total_employees']))
            <div class="bg-white rounded-2xl border border-gray-100 p-3.5 sm:p-4 shadow-sm flex flex-col justify-between card-hover">
                <div class="flex items-center justify-between">
                    <div class="p-2 bg-blue-50 text-blue-600 rounded-xl">
                        <i data-lucide="users" class="h-4 w-4"></i>
                    </div>
                    <div class="text-xl font-bold text-gray-900">{{ $stats['total_employees'] }}</div>
                </div>
                <div class="text-xs font-semibold text-gray-500 mt-2.5">Total Pegawai</div>
            </div>
            @endif

            @if(isset($stats['total_students']))
            <div class="bg-white rounded-2xl border border-gray-100 p-3.5 sm:p-4 shadow-sm flex flex-col justify-between card-hover">
                <div class="flex items-center justify-between">
                    <div class="p-2 bg-green-50 text-green-600 rounded-xl">
                        <i data-lucide="graduation-cap" class="h-4 w-4"></i>
                    </div>
                    <div class="text-xl font-bold text-gray-900">{{ $stats['total_students'] }}</div>
                </div>
                <div class="text-xs font-semibold text-gray-500 mt-2.5">Total Siswa</div>
            </div>
            @endif

            @if(isset($stats['today_attendance']))
            @php
                $isManager = $user->hasRole('admin') || $user->hasRole('headmaster');
                $detail = $stats['today_attendance_detail'] ?? null;
                $completed = ($detail && ($detail['status'] ?? '') === 'completed' && !$isManager);
                $status = $detail['status'] ?? 'none';
                $styles = [
                    'none' => ['wrap' => 'bg-white', 'badge' => 'bg-gray-100 text-gray-700', 'icon' => 'text-gray-500', 'bgIcon' => 'bg-gray-50'],
                    'in_only' => ['wrap' => 'bg-white', 'badge' => 'bg-yellow-100 text-yellow-800', 'icon' => 'text-yellow-600', 'bgIcon' => 'bg-yellow-50'],
                    'completed' => ['wrap' => 'bg-green-50', 'badge' => 'bg-green-100 text-green-800', 'icon' => 'text-green-600', 'bgIcon' => 'bg-green-100'],
                ];
                $st = $styles[$status] ?? $styles['none'];
            @endphp
            <div class="rounded-2xl border {{ $completed ? 'border-green-200' : 'border-gray-100' }} {{ $st['wrap'] }} p-3.5 sm:p-4 shadow-sm flex flex-col justify-between card-hover">
                <div class="flex items-center justify-between">
                    <div class="p-2 {{ $st['bgIcon'] }} {{ $st['icon'] }} rounded-xl">
                        <i data-lucide="calendar-check" class="h-4 w-4"></i>
                    </div>
                    @if($isManager)
                        <div class="text-right">
                            <div class="text-xl font-bold text-gray-900 leading-tight">{{ $stats['today_attendance_percent'] ?? 0 }}%</div>
                            <div class="text-[10px] font-medium text-gray-400 mt-0.5">{{ $stats['today_attendance'] ?? 0 }}/{{ $stats['today_total_users'] ?? 0 }}</div>
                        </div>
                    @endif
                </div>
                <div class="mt-2.5">
                    <div class="text-xs font-semibold text-gray-500">Absensi Hari Ini</div>
                    @if(!$isManager)
                        <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $st['badge'] }}">
                            @if($status==='none') Belum Absen @elseif($status==='in_only') Proses Absensi @else Absensi Selesai @endif
                        </span>
                    @endif
                </div>
            </div>
            @endif

            @if(isset($stats['my_students']))
            <div class="bg-white rounded-2xl border border-gray-100 p-3.5 sm:p-4 shadow-sm flex flex-col justify-between card-hover">
                <div class="flex items-center justify-between">
                    <div class="p-2 bg-teal-50 text-teal-600 rounded-xl">
                        <i data-lucide="graduation-cap" class="h-4 w-4"></i>
                    </div>
                    <div class="text-xl font-bold text-gray-900">{{ $stats['my_students'] }}</div>
                </div>
                <div class="text-xs font-semibold text-gray-500 mt-2.5">Siswa Saya</div>
            </div>
            @endif

            @if(isset($stats['pending_leaves']))
            <div class="bg-white rounded-2xl border border-gray-100 p-3.5 sm:p-4 shadow-sm flex flex-col justify-between card-hover">
                <div class="flex items-center justify-between">
                    <div class="p-2 bg-yellow-50 text-yellow-600 rounded-xl">
                        <i data-lucide="clock" class="h-4 w-4"></i>
                    </div>
                    <div class="text-right">
                        <div class="text-xl font-bold text-gray-900 leading-tight">{{ $stats['pending_leaves_percent'] ?? 0 }}%</div>
                        <div class="text-[10px] font-medium text-gray-400 mt-0.5">{{ $stats['pending_leaves'] ?? 0 }}/{{ $stats['pending_leaves_total'] ?? 0 }}</div>
                    </div>
                </div>
                <div class="text-xs font-semibold text-gray-500 mt-2.5">Izin Pending</div>
            </div>
            @endif

            @if(isset($stats['approved_leaves']))
            <div class="bg-white rounded-2xl border border-gray-100 p-3.5 sm:p-4 shadow-sm flex flex-col justify-between card-hover">
                <div class="flex items-center justify-between">
                    <div class="p-2 bg-green-50 text-green-600 rounded-xl">
                        <i data-lucide="check-circle" class="h-4 w-4"></i>
                    </div>
                    <div class="text-xl font-bold text-gray-900">{{ $stats['approved_leaves'] }}</div>
                </div>
                <div class="text-xs font-semibold text-gray-500 mt-2.5">Izin Disetujui</div>
            </div>
            @endif
        </div>

        <!-- Ringkasan Performa (daisyUI stats) -->
        @if(isset($metrics) && ($user->hasRole('admin') || $user->hasRole('headmaster') || $user->hasRole('bk') || $user->hasRole('kesiswaan')))
        @php
            $formatPercent = function($val) {
                $num = (float) ($val ?? 0);
                return (floor($num) == $num ? number_format($num, 0) : number_format($num, 1)) . '%';
            };
            $getDaisyColor = function($p) {
                $val = (float) $p;
                if ($val >= 80) return 'text-success';
                if ($val >= 50) return 'text-warning';
                return 'text-error';
            };
        @endphp

        <div class="mb-8">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-500">Ringkasan Performa</h3>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="openDateBottomSheet()" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition-colors" title="Filter Tanggal">
                        <i data-lucide="more-horizontal" class="h-5 w-5"></i>
                    </button>
                </div>
            </div>

            <!-- Date Bottom Sheet Modal (kept for functional date filtering) -->
            <div id="date-sheet-backdrop" onclick="closeDateBottomSheet()" class="fixed inset-0 bg-black bg-opacity-40 z-50 hidden transition-opacity duration-300 opacity-0"></div>
            <div id="date-sheet" class="fixed bottom-0 left-0 right-0 bg-white rounded-t-3xl z-50 transform translate-y-full transition-transform duration-300 shadow-2xl safe-bottom max-h-[85vh] overflow-y-auto">
                <div class="flex justify-center py-3" onclick="closeDateBottomSheet()">
                    <div class="w-12 h-1 bg-gray-200 rounded-full"></div>
                </div>
                <div class="px-6 pb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900">Pilih Rentang Tanggal</h3>
                        <button type="button" onclick="closeDateBottomSheet()" class="p-1 text-gray-400 hover:text-gray-600">
                            <i data-lucide="x" class="h-5 w-5"></i>
                        </button>
                    </div>
                    <form method="GET" action="{{ route('dashboard') }}" id="bottom-sheet-date-form">
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Tanggal Mulai</label>
                                <input type="date" name="start" value="{{ $startDate ?? '' }}" class="w-full border border-gray-100 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Tanggal Selesai</label>
                                <input type="date" name="end" value="{{ $endDate ?? '' }}" class="w-full border border-gray-100 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                            </div>
                        </div>
                        @if(isset($metrics['role_filter']))
                        <div class="mb-4">
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Filter Peran</label>
                            <select name="role" class="w-full border border-gray-100 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="all" {{ ($metrics['role_filter'] ?? 'all') === 'all' ? 'selected' : '' }}>Semua</option>
                                <option value="employee" {{ ($metrics['role_filter'] ?? 'all') === 'employee' ? 'selected' : '' }}>Pegawai</option>
                                <option value="student" {{ ($metrics['role_filter'] ?? 'all') === 'student' ? 'selected' : '' }}>Siswa</option>
                            </select>
                        </div>
                        @endif
                        <div class="flex gap-2 mb-6 overflow-x-auto pb-2">
                            <a href="{{ route('dashboard', ['start' => now()->timezone('Asia/Jakarta')->format('Y-m-d'), 'end' => now()->timezone('Asia/Jakarta')->format('Y-m-d')]) }}" class="px-3 py-1.5 bg-gray-50 hover:bg-gray-100 text-gray-600 rounded-lg text-xs font-semibold whitespace-nowrap border border-gray-100 transition-colors">Hari Ini</a>
                            <a href="{{ route('dashboard', ['start' => now()->timezone('Asia/Jakarta')->subDays(6)->format('Y-m-d'), 'end' => now()->timezone('Asia/Jakarta')->format('Y-m-d')]) }}" class="px-3 py-1.5 bg-gray-50 hover:bg-gray-100 text-gray-600 rounded-lg text-xs font-semibold whitespace-nowrap border border-gray-100 transition-colors">7 Hari Terakhir</a>
                            <a href="{{ route('dashboard', ['start' => now()->timezone('Asia/Jakarta')->startOfMonth()->format('Y-m-d'), 'end' => now()->timezone('Asia/Jakarta')->format('Y-m-d')]) }}" class="px-3 py-1.5 bg-gray-50 hover:bg-gray-100 text-gray-600 rounded-lg text-xs font-semibold whitespace-nowrap border border-gray-100 transition-colors">Bulan Ini</a>
                        </div>
                        <div class="flex gap-3">
                            <button type="button" onclick="closeDateBottomSheet()" class="flex-1 py-3 border border-gray-200 text-gray-600 rounded-xl text-sm font-semibold hover:bg-gray-50 transition-colors">Batal</button>
                            <button type="submit" class="flex-1 py-3 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition-colors shadow-lg shadow-blue-500/20">Terapkan</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Ringkasan Performa: 4 stat cards dengan tooltip hover -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">

                {{-- ========== KARTU 1: PENGGUNAAN ========== --}}
                @php
                    $usagePct      = $metrics['usage']['percentage'] ?? 0;
                    $activeUsers   = $metrics['usage']['active_users'] ?? 0;
                    $totalUsers    = $metrics['usage']['total_users'] ?? 0;
                    $activeEmp     = $metrics['usage']['breakdown']['employee']['active'] ?? 0;
                    $totalEmp      = $metrics['usage']['breakdown']['employee']['total'] ?? 0;
                    $activeStudent = $metrics['usage']['breakdown']['student']['active'] ?? 0;
                    $totalStudent  = $metrics['usage']['breakdown']['student']['total'] ?? 0;
                    $filterLabel   = ($metrics['role_filter'] ?? 'all') === 'employee' ? 'Pegawai' : (($metrics['role_filter'] ?? 'all') === 'student' ? 'Siswa' : 'Semua');
                @endphp
                <div class="perf-stat-card group relative bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 cursor-default">
                    <div class="flex items-start justify-between mb-2">
                        <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Penggunaan</span>
                        <div class="perf-tooltip-trigger flex-shrink-0 w-5 h-5 rounded-full bg-blue-50 flex items-center justify-center text-blue-400 hover:bg-blue-100 transition-colors">
                            <i data-lucide="info" class="h-3 w-3"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-extrabold {{ $getDaisyColor($usagePct) }} leading-none mb-1">
                        {{ $formatPercent($usagePct) }}
                    </div>
                    <div class="text-[11px] text-gray-400">{{ $activeUsers }} dari {{ $totalUsers }} pengguna absen</div>

                    <!-- Tooltip Popup -->
                    <div class="perf-tooltip-popup w-72 bg-gray-900 text-white rounded-xl shadow-2xl p-4 text-xs leading-relaxed">
                        <div class="font-bold text-blue-300 mb-2 flex items-center gap-1.5">
                            <i data-lucide="bar-chart-2" class="h-3.5 w-3.5"></i>
                            Tingkat Penggunaan Sistem
                        </div>
                        <div class="text-gray-300 mb-2">
                            Persentase pengguna yang <strong class="text-white">tercatat absen minimal sekali</strong> dalam periode yang dipilih, dibandingkan total pengguna aktif.
                        </div>
                        <div class="bg-gray-800 rounded-lg p-2.5 mb-2">
                            <div class="font-mono text-[10px] text-yellow-300">
                                {{ $activeUsers }} pengguna absen ÷ {{ $totalUsers }} total = <strong>{{ $formatPercent($usagePct) }}</strong>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-1.5 text-[10px]">
                            <div class="bg-gray-800 rounded-lg p-2">
                                <div class="text-gray-400 mb-0.5">Pegawai</div>
                                <div class="font-bold text-white">{{ $activeEmp }} / {{ $totalEmp }}</div>
                            </div>
                            <div class="bg-gray-800 rounded-lg p-2">
                                <div class="text-gray-400 mb-0.5">Siswa</div>
                                <div class="font-bold text-white">{{ $activeStudent }} / {{ $totalStudent }}</div>
                            </div>
                        </div>
                        <div class="mt-2 text-[10px] text-gray-400">Filter aktif: <span class="text-blue-300 font-semibold">{{ $filterLabel }}</span> · Hari libur dikecualikan</div>
                        <!-- Arrow -->
                        <div class="tooltip-arrow"></div>
                    </div>
                </div>

                {{-- ========== KARTU 2: KPI ABSENSI ========== --}}
                @php
                    $kpiScore        = $metrics['kpi']['score'] ?? 0;
                    $ontimeRate      = $metrics['kpi']['ontime_rate'] ?? 0;
                    $coverageRate    = $metrics['kpi']['coverage_rate'] ?? 0;
                    $completRate     = $metrics['kpi']['completeness_rate'] ?? 0;
                    $checkoutRate    = $metrics['kpi']['checkout_consistency'] ?? 0;
                    $workingDays     = $metrics['kpi']['working_days'] ?? 0;
                    $holidaysCount   = $metrics['kpi']['holidays_count'] ?? 0;
                @endphp
                <div class="perf-stat-card group relative bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 cursor-default">
                    <div class="flex items-start justify-between mb-2">
                        <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">KPI Absensi</span>
                        <div class="perf-tooltip-trigger flex-shrink-0 w-5 h-5 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-400 hover:bg-emerald-100 transition-colors">
                            <i data-lucide="info" class="h-3 w-3"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-extrabold {{ $getDaisyColor($kpiScore) }} leading-none mb-1">
                        {{ $formatPercent($kpiScore) }}
                    </div>
                    <div class="text-[11px] text-gray-400">Skor gabungan 4 indikator kunci</div>

                    <!-- Tooltip Popup -->
                    <div class="perf-tooltip-popup w-80 bg-gray-900 text-white rounded-xl shadow-2xl p-4 text-xs leading-relaxed">
                        <div class="font-bold text-emerald-300 mb-2 flex items-center gap-1.5">
                            <i data-lucide="activity" class="h-3.5 w-3.5"></i>
                            Indeks KPI Absensi
                        </div>
                        <div class="text-gray-300 mb-2.5">
                            Skor komposit berbobot dari <strong class="text-white">4 indikator kinerja</strong> kehadiran di sekolah:
                        </div>
                        <div class="space-y-1.5 mb-3">
                            <div class="flex items-center justify-between bg-gray-800 rounded-lg px-2.5 py-1.5">
                                <span class="text-gray-300">Tepat Waktu <span class="text-gray-500">(bobot 40%)</span></span>
                                <span class="font-bold text-white">{{ $formatPercent($ontimeRate) }}</span>
                            </div>
                            <div class="flex items-center justify-between bg-gray-800 rounded-lg px-2.5 py-1.5">
                                <span class="text-gray-300">Cakupan Absen <span class="text-gray-500">(bobot 30%)</span></span>
                                <span class="font-bold text-white">{{ $formatPercent($coverageRate) }}</span>
                            </div>
                            <div class="flex items-center justify-between bg-gray-800 rounded-lg px-2.5 py-1.5">
                                <span class="text-gray-300">Kelengkapan Data <span class="text-gray-500">(bobot 20%)</span></span>
                                <span class="font-bold text-white">{{ $formatPercent($completRate) }}</span>
                            </div>
                            <div class="flex items-center justify-between bg-gray-800 rounded-lg px-2.5 py-1.5">
                                <span class="text-gray-300">Check-Out Konsisten <span class="text-gray-500">(bobot 10%)</span></span>
                                <span class="font-bold text-white">{{ $formatPercent($checkoutRate) }}</span>
                            </div>
                        </div>
                        <div class="bg-gray-800 rounded-lg p-2 text-[10px] text-gray-400">
                            Periode: <span class="text-blue-300">{{ $workingDays }} hari kerja</span> · <span class="text-yellow-300">{{ $holidaysCount }} hari libur dikecualikan</span>
                        </div>
                        <div class="mt-1.5 text-[10px] text-gray-500">≥80% Baik · 50–79% Perlu Perhatian · &lt;50% Kritis</div>
                        <div class="tooltip-arrow"></div>
                    </div>
                </div>

                {{-- ========== KARTU 3: DATA PEGAWAI ========== --}}
                @php
                    $empPct      = $metrics['completeness']['employees']['percentage'] ?? 0;
                    $empComplete = $metrics['completeness']['employees']['complete'] ?? 0;
                    $empTotal    = $metrics['completeness']['employees']['total'] ?? 0;
                    $empMissing  = $empTotal - $empComplete;
                @endphp
                <div class="perf-stat-card group relative bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 cursor-default">
                    <div class="flex items-start justify-between mb-2">
                        <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Data Pegawai</span>
                        <div class="perf-tooltip-trigger flex-shrink-0 w-5 h-5 rounded-full bg-violet-50 flex items-center justify-center text-violet-400 hover:bg-violet-100 transition-colors">
                            <i data-lucide="info" class="h-3 w-3"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-extrabold {{ $getDaisyColor($empPct) }} leading-none mb-1">
                        {{ $formatPercent($empPct) }}
                    </div>
                    <div class="text-[11px] text-gray-400">{{ $empComplete }} dari {{ $empTotal }} profil lengkap</div>

                    <!-- Tooltip Popup -->
                    <div class="perf-tooltip-popup w-72 bg-gray-900 text-white rounded-xl shadow-2xl p-4 text-xs leading-relaxed">
                        <div class="font-bold text-violet-300 mb-2 flex items-center gap-1.5">
                            <i data-lucide="users" class="h-3.5 w-3.5"></i>
                            Kelengkapan Data Pegawai
                        </div>
                        <div class="text-gray-300 mb-2.5">
                            Proporsi <strong class="text-white">field profil yang telah diisi</strong> dari seluruh field wajib pegawai (NIK, NUPTK, nomor rekening, NPWP, dll.).
                        </div>
                        <div class="bg-gray-800 rounded-lg p-2.5 mb-2">
                            <div class="font-mono text-[10px] text-yellow-300">
                                Field terisi ÷ (Field wajib × {{ $empTotal }} pegawai) = <strong>{{ $formatPercent($empPct) }}</strong>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-1.5 text-[10px] mb-2">
                            <div class="bg-gray-800 rounded-lg p-2 text-center">
                                <div class="text-2xl font-bold text-emerald-400">{{ $empComplete }}</div>
                                <div class="text-gray-400 mt-0.5">Profil Lengkap</div>
                            </div>
                            <div class="bg-gray-800 rounded-lg p-2 text-center">
                                <div class="text-2xl font-bold text-red-400">{{ $empMissing }}</div>
                                <div class="text-gray-400 mt-0.5">Perlu Dilengkapi</div>
                            </div>
                        </div>
                        <div class="text-[10px] text-gray-500">Cek tab "Profil Tidak Lengkap" di Perlu Ditindaklanjuti ↓</div>
                        <div class="tooltip-arrow"></div>
                    </div>
                </div>

                {{-- ========== KARTU 4: DATA SISWA ========== --}}
                @php
                    $stuPct      = $metrics['completeness']['students']['percentage'] ?? 0;
                    $stuComplete = $metrics['completeness']['students']['complete'] ?? 0;
                    $stuTotal    = $metrics['completeness']['students']['total'] ?? 0;
                    $stuMissing  = $stuTotal - $stuComplete;
                @endphp
                <div class="perf-stat-card group relative bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 cursor-default">
                    <div class="flex items-start justify-between mb-2">
                        <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Data Siswa</span>
                        <div class="perf-tooltip-trigger flex-shrink-0 w-5 h-5 rounded-full bg-amber-50 flex items-center justify-center text-amber-400 hover:bg-amber-100 transition-colors">
                            <i data-lucide="info" class="h-3 w-3"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-extrabold {{ $getDaisyColor($stuPct) }} leading-none mb-1">
                        {{ $formatPercent($stuPct) }}
                    </div>
                    <div class="text-[11px] text-gray-400">{{ $stuComplete }} dari {{ $stuTotal }} profil lengkap</div>

                    <!-- Tooltip Popup -->
                    <div class="perf-tooltip-popup w-72 bg-gray-900 text-white rounded-xl shadow-2xl p-4 text-xs leading-relaxed">
                        <div class="font-bold text-amber-300 mb-2 flex items-center gap-1.5">
                            <i data-lucide="graduation-cap" class="h-3.5 w-3.5"></i>
                            Kelengkapan Data Siswa
                        </div>
                        <div class="text-gray-300 mb-2.5">
                            Proporsi <strong class="text-white">field profil yang telah diisi</strong> dari seluruh field wajib siswa (NIS, NISN, No. KK, No. Akte Lahir, golongan darah, dll.).
                        </div>
                        <div class="bg-gray-800 rounded-lg p-2.5 mb-2">
                            <div class="font-mono text-[10px] text-yellow-300">
                                Field terisi ÷ (Field wajib × {{ $stuTotal }} siswa) = <strong>{{ $formatPercent($stuPct) }}</strong>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-1.5 text-[10px] mb-2">
                            <div class="bg-gray-800 rounded-lg p-2 text-center">
                                <div class="text-2xl font-bold text-emerald-400">{{ $stuComplete }}</div>
                                <div class="text-gray-400 mt-0.5">Profil Lengkap</div>
                            </div>
                            <div class="bg-gray-800 rounded-lg p-2 text-center">
                                <div class="text-2xl font-bold text-red-400">{{ $stuMissing }}</div>
                                <div class="text-gray-400 mt-0.5">Perlu Dilengkapi</div>
                            </div>
                        </div>
                        <div class="text-[10px] text-gray-500">Cek tab "Profil Tidak Lengkap" di Perlu Ditindaklanjuti ↓</div>
                        <div class="tooltip-arrow"></div>
                    </div>
                </div>

            </div>
            <!-- Legenda warna -->
            <div class="flex items-center gap-4 mt-2.5 px-1">
                <span class="flex items-center gap-1 text-[10px] text-gray-400"><span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>≥80% Baik</span>
                <span class="flex items-center gap-1 text-[10px] text-gray-400"><span class="inline-block w-2 h-2 rounded-full bg-amber-400"></span>50–79% Perhatian</span>
                <span class="flex items-center gap-1 text-[10px] text-gray-400"><span class="inline-block w-2 h-2 rounded-full bg-red-500"></span>&lt;50% Kritis</span>
                <span class="ml-auto text-[10px] text-gray-400 italic">Arahkan kursor ke kartu untuk detail</span>
            </div>
        </div>
        @endif

        <!-- Section: Perlu Ditindaklanjuti (daisyUI tabs + badge) -->
        @if(isset($metrics) && ($user->hasRole('admin') || $user->hasRole('headmaster') || $user->hasRole('bk') || $user->hasRole('kesiswaan')))
        @php
            $incompleteList = collect($metrics['incomplete_profiles'] ?? [])->map(function($u) {
                return [
                    'category' => 'incomplete',
                    'name' => $u->name,
                    'user_type' => $u->user_type === 'employee' ? 'Pegawai' : 'Siswa',
                    'subtitle' => ($u->user_type === 'employee' ? 'Pegawai' : 'Siswa') . ' · profil kosong',
                    'badge_text' => ($u->missing_count ?? 0) . ' kosong',
                    'badge_class' => 'badge-error',
                ];
            });

            $daysDiff = 7;
            if (!empty($startDate) && !empty($endDate)) {
                $daysDiff = max(1, \Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate)) + 1);
            }

            $nonUserList = collect($metrics['non_users'] ?? [])->map(function($u) use ($daysDiff) {
                return [
                    'category' => 'non_user',
                    'name' => $u->name,
                    'user_type' => $u->user_type === 'employee' ? 'Pegawai' : 'Siswa',
                    'subtitle' => ($u->user_type === 'employee' ? 'Pegawai' : 'Siswa') . ' · tidak aktif',
                    'badge_text' => $daysDiff . ' hari',
                    'badge_class' => 'badge-warning',
                ];
            });

            $leakList = collect($metrics['leak']['samples'] ?? [])->map(function($item) {
                return [
                    'category' => 'leak',
                    'name' => $item->user->name ?? 'User',
                    'user_type' => ($item->user->user_type ?? '') === 'employee' ? 'Pegawai' : 'Siswa',
                    'subtitle' => (($item->user->user_type ?? '') === 'employee' ? 'Pegawai' : 'Siswa') . ' · leak absensi (' . (string)$item->date . ')',
                    'badge_text' => 'Tanpa Check Out',
                    'badge_class' => 'badge-error',
                ];
            });

            $allProblems = collect();
            $maxCount = max($incompleteList->count(), $nonUserList->count(), $leakList->count());
            for ($i = 0; $i < $maxCount; $i++) {
                if ($incompleteList->has($i)) $allProblems->push($incompleteList->get($i));
                if ($nonUserList->has($i)) $allProblems->push($nonUserList->get($i));
                if ($leakList->has($i)) $allProblems->push($leakList->get($i));
            }

            $totalIssuesCount = $allProblems->count();
            $incompleteCount = $incompleteList->count();
            $nonUserCount = $nonUserList->count();
            $leakCount = $leakList->count();
        @endphp

        <div>
            <h3 class="text-sm font-semibold text-gray-500 mb-3">Perlu Ditindaklanjuti</h3>
            
            <div class="rounded-2xl border border-gray-100 bg-white p-5 md:p-6 shadow-sm">
                <!-- Header Tabs and Single Export Button -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-100">
                    <!-- daisyUI tabs -->
                    <div role="tablist" class="tabs tabs-bordered overflow-x-auto" id="zona2-tabs">
                        <a role="tab" onclick="switchZona2Tab('all')" id="tab-btn-all" class="tab tab-active font-bold text-xs whitespace-nowrap">
                            Semua ({{ $totalIssuesCount }})
                        </a>
                        <a role="tab" onclick="switchZona2Tab('incomplete')" id="tab-btn-incomplete" class="tab text-xs whitespace-nowrap">
                            Profil kosong ({{ $incompleteCount }})
                        </a>
                        <a role="tab" onclick="switchZona2Tab('non_user')" id="tab-btn-non_user" class="tab text-xs whitespace-nowrap">
                            Tidak aktif ({{ $nonUserCount }})
                        </a>
                        <a role="tab" onclick="switchZona2Tab('leak')" id="tab-btn-leak" class="tab text-xs whitespace-nowrap">
                            Leak absensi ({{ $leakCount }})
                        </a>
                    </div>

                    <!-- Single Export Button (top right) -->
                    <div class="flex items-center self-end sm:self-auto">
                        <a id="zona2-export-btn" href="{{ route('dashboard.export', ['type' => 'all', 'start' => $startDate, 'end' => $endDate]) }}" class="flex items-center gap-1.5 px-3.5 py-1.5 bg-gray-50 hover:bg-gray-100 text-gray-700 text-xs font-semibold rounded-xl border border-gray-200 transition-colors">
                            <i data-lucide="download" class="h-3.5 w-3.5 text-gray-500"></i>
                            <span>Export</span>
                        </a>
                    </div>
                </div>

                <!-- Tab Content Panels (Flat rows with daisyUI badges) -->
                <!-- Tab 'all' -->
                <div id="zona2-panel-all" class="zona2-panel divide-y divide-gray-100">
                    @forelse($allProblems->take(5) as $row)
                    <div class="flex items-center justify-between py-3.5 hover:bg-gray-50/50 px-2 rounded-xl transition-colors">
                        <div>
                            <div class="font-bold text-gray-900 text-sm">{{ $row['name'] }}</div>
                            <div class="text-xs text-gray-400 mt-0.5">{{ $row['subtitle'] }}</div>
                        </div>
                        <div>
                            <span class="badge {{ $row['badge_class'] }} badge-sm font-semibold">
                                {{ $row['badge_text'] }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="py-8 text-center text-sm text-gray-400">Tidak ada masalah profil, keaktifan, atau kebocoran absensi.</div>
                    @endforelse
                </div>

                <!-- Tab 'incomplete' -->
                <div id="zona2-panel-incomplete" class="zona2-panel divide-y divide-gray-100 hidden">
                    @forelse($incompleteList->take(5) as $row)
                    <div class="flex items-center justify-between py-3.5 hover:bg-gray-50/50 px-2 rounded-xl transition-colors">
                        <div>
                            <div class="font-bold text-gray-900 text-sm">{{ $row['name'] }}</div>
                            <div class="text-xs text-gray-400 mt-0.5">{{ $row['subtitle'] }}</div>
                        </div>
                        <div>
                            <span class="badge {{ $row['badge_class'] }} badge-sm font-semibold">
                                {{ $row['badge_text'] }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="py-8 text-center text-sm text-emerald-600 font-medium">Semua data profil sudah lengkap 100%.</div>
                    @endforelse
                </div>

                <!-- Tab 'non_user' -->
                <div id="zona2-panel-non_user" class="zona2-panel divide-y divide-gray-100 hidden">
                    @forelse($nonUserList->take(5) as $row)
                    <div class="flex items-center justify-between py-3.5 hover:bg-gray-50/50 px-2 rounded-xl transition-colors">
                        <div>
                            <div class="font-bold text-gray-900 text-sm">{{ $row['name'] }}</div>
                            <div class="text-xs text-gray-400 mt-0.5">{{ $row['subtitle'] }}</div>
                        </div>
                        <div>
                            <span class="badge {{ $row['badge_class'] }} badge-sm font-semibold">
                                {{ $row['badge_text'] }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="py-8 text-center text-sm text-emerald-600 font-medium">Semua user aktif menggunakan absensi.</div>
                    @endforelse
                </div>

                <!-- Tab 'leak' -->
                <div id="zona2-panel-leak" class="zona2-panel divide-y divide-gray-100 hidden">
                    @forelse($leakList->take(5) as $row)
                    <div class="flex items-center justify-between py-3.5 hover:bg-gray-50/50 px-2 rounded-xl transition-colors">
                        <div>
                            <div class="font-bold text-gray-900 text-sm">{{ $row['name'] }}</div>
                            <div class="text-xs text-gray-400 mt-0.5">{{ $row['subtitle'] }}</div>
                        </div>
                        <div>
                            <span class="badge {{ $row['badge_class'] }} badge-sm font-semibold">
                                {{ $row['badge_text'] }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="py-8 text-center text-sm text-emerald-600 font-medium">Tidak ada kebocoran absensi (seluruh user telah check out).</div>
                    @endforelse
                </div>

                <!-- Footer 'Lihat semua N item' button -->
                <div class="pt-4 border-t border-gray-100 text-center">
                    <button type="button" onclick="openZona2Modal()" id="zona2-view-all-btn" class="text-xs font-bold text-blue-600 hover:text-blue-800 transition-colors inline-flex items-center gap-1">
                        <span id="zona2-view-all-text">Lihat semua {{ $totalIssuesCount }} item</span>
                        <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Detail 'Lihat Semua' -->
        <div id="zona2-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black bg-opacity-40 flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl relative max-h-[85vh] flex flex-col">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <h3 class="font-bold text-gray-900 text-base" id="zona2-modal-title">Daftar Lengkap (Semua)</h3>
                    <button type="button" onclick="closeZona2Modal()" class="text-gray-400 hover:text-gray-600 p-1">
                        <i data-lucide="x" class="h-5 w-5"></i>
                    </button>
                </div>
                <div class="py-3 flex-1 overflow-y-auto divide-y divide-gray-100" id="zona2-modal-body">
                    <!-- Populated dynamically by JS -->
                </div>
                <div class="pt-3 border-t border-gray-100 flex justify-end">
                    <button type="button" onclick="closeZona2Modal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition-colors">Tutup</button>
                </div>
            </div>
        </div>
        @endif

        <!-- Section: Terlambat Hari Ini & Sedang Izin/Cuti (2 Kolom Berdampingan) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Terlambat Hari Ini -->
            <div class="flex flex-col">
                <div class="flex items-center justify-between mb-3 px-1">
                    <h3 class="text-sm font-semibold text-gray-500">Terlambat Hari Ini</h3>
                    <span id="late-today-badge" class="badge badge-warning badge-sm font-semibold {{ (isset($lateToday) && $lateToday->isNotEmpty()) ? '' : 'hidden' }}">{{ isset($lateToday) ? $lateToday->count() : 0 }}</span>
                </div>

                <div class="card bg-white border border-gray-100 shadow-sm rounded-2xl h-full flex flex-col">
                    <div class="card-body p-4 md:p-5 flex-1 flex flex-col justify-between">
                        <div id="late-today-list" class="max-h-72 overflow-y-auto divide-y divide-gray-100 -mx-1 px-1 flex-1">
                            @if(isset($lateToday) && $lateToday->isNotEmpty())
                                @foreach($lateToday as $late)
                                <div class="flex items-center justify-between py-2.5 hover:bg-gray-50/50 rounded-xl px-1.5 transition-colors">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        @if(!empty($late['avatar']))
                                        <div class="avatar flex-shrink-0">
                                            <div class="w-8 h-8 rounded-full">
                                                <img src="{{ $late['avatar'] }}" alt="{{ $late['name'] }}" />
                                            </div>
                                        </div>
                                        @else
                                        <div class="avatar placeholder flex-shrink-0">
                                            <div class="bg-amber-50 text-amber-600 rounded-full w-8 h-8 text-[11px] font-bold flex items-center justify-center border border-amber-100">
                                                <span>{{ $late['initials'] }}</span>
                                            </div>
                                        </div>
                                        @endif

                                        <div class="min-w-0">
                                            <div class="text-xs font-bold text-gray-900 leading-tight truncate">{{ $late['name'] }}</div>
                                            <div class="text-[11px] text-gray-400 mt-0.5 truncate">{{ $late['subtitle'] }}</div>
                                            <div class="flex items-center gap-1 text-[10px] text-gray-400 mt-0.5">
                                                <i data-lucide="clock" class="h-3 w-3"></i>
                                                <span>Masuk pukul {{ $late['check_in_time'] }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-right flex-shrink-0 pl-2">
                                        <span class="badge badge-warning badge-sm font-semibold inline-flex items-center gap-0.5 text-[10px] whitespace-nowrap">
                                            <i data-lucide="hourglass" class="h-2.5 w-2.5"></i>
                                            {{ $late['late_duration'] }}
                                        </span>
                                    </div>
                                </div>
                                @endforeach
                            @else
                                <div class="py-12 text-center text-xs text-emerald-600 font-medium flex-1 flex flex-col items-center justify-center gap-1.5">
                                    <i data-lucide="check-circle" class="h-5 w-5 text-emerald-500"></i>
                                    <span>Tidak ada yang terlambat hari ini</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sedang Izin/Cuti -->
            <div class="flex flex-col">
                <div class="flex items-center justify-between mb-3 px-1">
                    <h3 class="text-sm font-semibold text-gray-500">Sedang Izin/Cuti</h3>
                    <span id="on-leave-badge" class="badge badge-neutral badge-sm font-semibold {{ (isset($onLeaveToday) && $onLeaveToday->isNotEmpty()) ? '' : 'hidden' }}">{{ isset($onLeaveToday) ? $onLeaveToday->count() : 0 }}</span>
                </div>

                <div class="card bg-white border border-gray-100 shadow-sm rounded-2xl h-full flex flex-col">
                    <div class="card-body p-4 md:p-5 flex-1 flex flex-col justify-between">
                        <div id="on-leave-list" class="max-h-72 overflow-y-auto divide-y divide-gray-100 -mx-1 px-1 flex-1">
                            @if(isset($onLeaveToday) && $onLeaveToday->isNotEmpty())
                                @foreach($onLeaveToday as $leave)
                                <div class="flex items-center justify-between py-2.5 hover:bg-gray-50/50 rounded-xl px-1.5 transition-colors">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        @if(!empty($leave['avatar']))
                                        <div class="avatar flex-shrink-0">
                                            <div class="w-8 h-8 rounded-full">
                                                <img src="{{ $leave['avatar'] }}" alt="{{ $leave['name'] }}" />
                                            </div>
                                        </div>
                                        @else
                                        <div class="avatar placeholder flex-shrink-0">
                                            <div class="bg-purple-50 text-purple-600 rounded-full w-8 h-8 text-[11px] font-bold flex items-center justify-center border border-purple-100">
                                                <span>{{ $leave['initials'] }}</span>
                                            </div>
                                        </div>
                                        @endif

                                        <div class="min-w-0">
                                            <div class="text-xs font-bold text-gray-900 leading-tight truncate">{{ $leave['name'] }}</div>
                                            <div class="text-[11px] text-gray-400 mt-0.5 truncate">{{ $leave['subtitle'] }}</div>
                                            <div class="flex items-center gap-1 text-[10px] text-gray-400 mt-0.5">
                                                <i data-lucide="calendar" class="h-3 w-3"></i>
                                                <span>{{ $leave['date_range'] }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-right flex-shrink-0 pl-2">
                                        <span class="badge {{ $leave['badge_class'] }} badge-sm font-semibold text-[10px] whitespace-nowrap">
                                            {{ $leave['type_label'] }}
                                        </span>
                                    </div>
                                </div>
                                @endforeach
                            @else
                                <div class="py-12 text-center text-xs text-gray-400 flex-1 flex flex-col items-center justify-center gap-1.5">
                                    <i data-lucide="user-check" class="h-5 w-5 text-gray-300"></i>
                                    <span>Tidak ada yang sedang izin/cuti hari ini</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: 4 Columns (Absensi Terbaru - Attendance Feed dengan Live Update) -->
    <div class="lg:col-span-4 flex flex-col h-full">
        <div class="flex items-center justify-between mb-3 px-1 flex-shrink-0">
            <h3 class="text-sm font-semibold text-gray-500">Absensi Terbaru</h3>
            <div class="flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-50 border border-emerald-200/60 shadow-xs">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-[11px] text-emerald-700 font-semibold tracking-tight">Live update</span>
            </div>
        </div>
        
        <div class="card bg-white border border-gray-100 shadow-sm rounded-2xl flex-1 flex flex-col min-h-0">
            <div class="card-body p-4 md:p-5 flex-1 flex flex-col min-h-0">
                <div id="attendance-feed-list" class="flex-1 overflow-y-auto divide-y divide-gray-100 -mx-1 px-1 pr-1.5 custom-feed-scrollbar min-h-0">
                    @if(isset($recentAttendanceFeed) && $recentAttendanceFeed->isNotEmpty())
                        @foreach($recentAttendanceFeed as $feed)
                        <div class="flex items-center justify-between py-3 hover:bg-gray-50/50 rounded-xl px-1.5 transition-colors">
                            <div class="flex items-center gap-2.5 min-w-0">
                                @if(!empty($feed['avatar']))
                                <div class="avatar flex-shrink-0">
                                    <div class="w-8 h-8 rounded-full">
                                        <img src="{{ $feed['avatar'] }}" alt="{{ $feed['name'] }}" />
                                    </div>
                                </div>
                                @else
                                <div class="avatar placeholder flex-shrink-0">
                                    <div class="bg-blue-50 text-blue-600 rounded-full w-8 h-8 text-xs font-bold flex items-center justify-center border border-blue-100">
                                        <span>{{ $feed['initials'] }}</span>
                                    </div>
                                </div>
                                @endif

                                <div class="min-w-0">
                                    <div class="text-xs font-bold text-gray-900 leading-tight truncate">{{ $feed['name'] }}</div>
                                    <div class="flex items-center gap-1.5 text-[11px] text-gray-400 mt-0.5 flex-wrap">
                                        <span class="font-medium text-gray-600">{{ $feed['event_type'] }}</span>
                                        <span>•</span>
                                        <span class="flex items-center gap-0.5">
                                            <i data-lucide="clock" class="h-3 w-3"></i>
                                            {{ $feed['time_str'] }}
                                        </span>
                                        @if(!empty($feed['location']))
                                        <span>•</span>
                                        <span class="truncate max-w-[120px]" title="{{ $feed['location'] }}">
                                            {{ $feed['location'] }}
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="text-right flex-shrink-0 pl-2">
                                <span class="text-[10px] font-medium text-gray-400 whitespace-nowrap">{{ $feed['relative_time'] }}</span>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="py-12 text-center text-xs text-gray-400 flex items-center justify-center">
                            Belum ada data absensi terbaru hari ini
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* ============================================================
   Ringkasan Performa – Stat Cards dengan Rich Tooltip Hover
   Position:fixed digunakan agar tidak ter-clip oleh overflow:hidden
   ============================================================ */
.perf-stat-card {
    overflow: visible;
    position: relative;
}

/* Tooltip versi fixed-position (dikendalikan via JS) */
.perf-tooltip-popup {
    position: fixed;
    z-index: 9999;
    opacity: 0;
    visibility: hidden;
    transform: translateY(6px);
    transition: opacity 0.18s ease, visibility 0.18s ease, transform 0.18s ease;
    pointer-events: none;
}
.perf-tooltip-popup.is-visible {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

/* Indikator teks warna sesuai kondisi */
.text-success  { color: #10b981; }
.text-warning  { color: #f59e0b; }
.text-error    { color: #ef4444; }

/* Arrow tooltip */
.perf-tooltip-popup .tooltip-arrow {
    position: absolute;
    top: -6px;
    left: 20px;
    width: 12px;
    height: 12px;
    background: #111827;
    transform: rotate(45deg);
    border-radius: 2px;
}
</style>
@endpush

@push('scripts')
<script>
/**
 * Tooltip dengan posisi fixed – tidak ter-clip oleh overflow:hidden parent.
 * Tooltip di-append ke body saat hover, lalu ditempatkan berdasarkan koordinat kartu.
 */
(function initPerfTooltips() {
    document.addEventListener('DOMContentLoaded', function () {
        const cards = document.querySelectorAll('.perf-stat-card');
        let activeTooltip = null;

        cards.forEach(card => {
            const tooltip = card.querySelector('.perf-tooltip-popup');
            if (!tooltip) return;

            // Pindahkan tooltip ke body agar tidak kena overflow clip
            document.body.appendChild(tooltip);

            card.addEventListener('mouseenter', function () {
                // Sembunyikan tooltip aktif sebelumnya
                if (activeTooltip && activeTooltip !== tooltip) {
                    activeTooltip.classList.remove('is-visible');
                }
                activeTooltip = tooltip;

                // Hitung posisi berdasarkan kartu
                const rect = card.getBoundingClientRect();
                const tooltipWidth = tooltip.offsetWidth || 288;
                const viewportWidth = window.innerWidth;
                const scrollY = window.scrollY || window.pageYOffset;

                // Posisi vertikal: tepat di bawah kartu + 8px gap
                tooltip.style.top = (rect.bottom + scrollY + 8) + 'px';

                // Posisi horizontal: align ke kiri kartu, tapi jangan keluar layar
                let leftPos = rect.left;
                if (leftPos + tooltipWidth > viewportWidth - 16) {
                    leftPos = rect.right - tooltipWidth;
                }
                if (leftPos < 16) leftPos = 16;
                tooltip.style.left = leftPos + 'px';

                // Sesuaikan posisi arrow
                const arrow = tooltip.querySelector('.tooltip-arrow');
                if (arrow) {
                    const arrowLeft = Math.max(12, rect.left + rect.width / 2 - leftPos - 6);
                    arrow.style.left = arrowLeft + 'px';
                    arrow.style.right = 'auto';
                }

                tooltip.classList.add('is-visible');
            });

            card.addEventListener('mouseleave', function () {
                tooltip.classList.remove('is-visible');
                if (activeTooltip === tooltip) activeTooltip = null;
            });
        });
    });
})();
</script>
@endpush

