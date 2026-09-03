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

            <!-- daisyUI Stats Component -->
            <div class="stats stats-vertical sm:stats-horizontal shadow-sm bg-white border border-gray-100 rounded-2xl w-full">
                <div class="stat py-4 px-6">
                    <div class="stat-title text-gray-500 font-medium text-xs">Penggunaan</div>
                    <div class="stat-value text-2xl font-bold {{ $getDaisyColor($metrics['usage']['percentage'] ?? 0) }}">
                        {{ $formatPercent($metrics['usage']['percentage'] ?? 0) }}
                    </div>
                </div>
                <div class="stat py-4 px-6">
                    <div class="stat-title text-gray-500 font-medium text-xs">KPI Absensi</div>
                    <div class="stat-value text-2xl font-bold {{ $getDaisyColor($metrics['kpi']['score'] ?? 0) }}">
                        {{ $formatPercent($metrics['kpi']['score'] ?? 0) }}
                    </div>
                </div>
                <div class="stat py-4 px-6">
                    <div class="stat-title text-gray-500 font-medium text-xs">Data Pegawai</div>
                    <div class="stat-value text-2xl font-bold {{ $getDaisyColor($metrics['completeness']['employees']['percentage'] ?? 0) }}">
                        {{ $formatPercent($metrics['completeness']['employees']['percentage'] ?? 0) }}
                    </div>
                </div>
                <div class="stat py-4 px-6">
                    <div class="stat-title text-gray-500 font-medium text-xs">Data Siswa</div>
                    <div class="stat-value text-2xl font-bold {{ $getDaisyColor($metrics['completeness']['students']['percentage'] ?? 0) }}">
                        {{ $formatPercent($metrics['completeness']['students']['percentage'] ?? 0) }}
                    </div>
                </div>
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
