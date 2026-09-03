<!-- Student Dashboard -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8 items-stretch">
    <!-- Kolom Kiri (8 Kolom Desktop): Kartu Pelajar, Stat Kehadiran, Presensi Mandiri, Pengajuan Izin -->
    <div class="lg:col-span-8 flex flex-col gap-6">
        <!-- Banner Pelajar / Info Kelas -->
        <div class="rounded-2xl bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 p-5 md:p-6 text-white shadow-md flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-white text-xl font-bold flex-shrink-0">
                    <i data-lucide="graduation-cap" class="h-8 w-8"></i>
                </div>
                <div>
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-white/20 text-white text-xs font-semibold mb-1">
                        <span>{{ $studentClass->name ?? 'Siswa Aktif' }}</span>
                    </div>
                    <h2 class="text-xl font-bold text-white">{{ $user->name }}</h2>
                    <p class="text-xs text-blue-100 mt-0.5">NISN / ID: {{ $user->identifier ?? ($user->id) }} · {{ $school->name ?? 'Presensia' }}</p>
                </div>
            </div>
            <div class="flex sm:flex-col items-start sm:items-end justify-between border-t sm:border-t-0 border-white/10 pt-3 sm:pt-0">
                <span class="text-xs text-blue-100">Bulan Berjalan</span>
                <span class="text-sm font-bold text-white">{{ $monthSummary['month_name'] ?? '' }}</span>
            </div>
        </div>

        <!-- Statistics Cards Siswa -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5 desktop-stats-grid">
            @php
                $status = $todayDetail['status'] ?? 'none';
                $stConfig = [
                    'none' => ['badge' => 'badge-ghost', 'text' => 'Belum Absen', 'color' => 'text-gray-500', 'bg' => 'bg-gray-50'],
                    'completed' => ['badge' => 'badge-success', 'text' => 'Sudah Hadir', 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50'],
                ];
                $currentSt = $stConfig[$status] ?? $stConfig['none'];
            @endphp

            <!-- Card 1: Status Hari Ini -->
            <div class="bg-white rounded-2xl border border-gray-100 p-3.5 sm:p-4 shadow-sm flex flex-col justify-between card-hover">
                <div class="flex items-center justify-between">
                    <div class="p-2 {{ $currentSt['bg'] }} {{ $currentSt['color'] }} rounded-xl">
                        <i data-lucide="calendar-check" class="h-4 w-4"></i>
                    </div>
                    <span class="badge {{ $currentSt['badge'] }} badge-sm font-semibold">{{ $currentSt['text'] }}</span>
                </div>
                <div class="mt-2.5">
                    <div class="text-xs font-semibold text-gray-500">Hari Ini</div>
                    <div class="text-sm font-bold text-gray-900 mt-0.5">
                        {{ $todayDetail['check_in'] ? substr($todayDetail['check_in'], 0, 5) . ' WIB' : 'Belum Scan' }}
                    </div>
                </div>
            </div>

            <!-- Card 2: Total Hadir -->
            <div class="bg-white rounded-2xl border border-gray-100 p-3.5 sm:p-4 shadow-sm flex flex-col justify-between card-hover">
                <div class="flex items-center justify-between">
                    <div class="p-2 bg-emerald-50 text-emerald-600 rounded-xl">
                        <i data-lucide="check-circle-2" class="h-4 w-4"></i>
                    </div>
                    <div class="text-xl font-bold text-emerald-600">{{ $monthSummary['present'] ?? 0 }} <span class="text-xs font-normal text-gray-400">hari</span></div>
                </div>
                <div class="mt-2.5">
                    <div class="text-xs font-semibold text-gray-500">Hadir</div>
                    <div class="text-[11px] text-gray-400 mt-0.5">Tepat Waktu: {{ $monthSummary['ontime'] ?? 0 }}</div>
                </div>
            </div>

            <!-- Card 3: Izin & Sakit -->
            <div class="bg-white rounded-2xl border border-gray-100 p-3.5 sm:p-4 shadow-sm flex flex-col justify-between card-hover">
                <div class="flex items-center justify-between">
                    <div class="p-2 bg-amber-50 text-amber-600 rounded-xl">
                        <i data-lucide="file-text" class="h-4 w-4"></i>
                    </div>
                    <div class="text-xl font-bold text-amber-600">{{ ($monthSummary['permit'] ?? 0) + ($monthSummary['sick'] ?? 0) }} <span class="text-xs font-normal text-gray-400">hari</span></div>
                </div>
                <div class="mt-2.5">
                    <div class="text-xs font-semibold text-gray-500">Izin & Sakit</div>
                    <div class="text-[11px] text-gray-400 mt-0.5">Sakit: {{ $monthSummary['sick'] ?? 0 }} | Izin: {{ $monthSummary['permit'] ?? 0 }}</div>
                </div>
            </div>

            <!-- Card 4: Alpha / Tanpa Keterangan -->
            <div class="bg-white rounded-2xl border border-gray-100 p-3.5 sm:p-4 shadow-sm flex flex-col justify-between card-hover">
                <div class="flex items-center justify-between">
                    <div class="p-2 bg-rose-50 text-rose-600 rounded-xl">
                        <i data-lucide="alert-circle" class="h-4 w-4"></i>
                    </div>
                    <div class="text-xl font-bold {{ ($monthSummary['alpha'] ?? 0) > 0 ? 'text-rose-600' : 'text-gray-400' }}">{{ $monthSummary['alpha'] ?? 0 }} <span class="text-xs font-normal text-gray-400">hari</span></div>
                </div>
                <div class="mt-2.5">
                    <div class="text-xs font-semibold text-gray-500">Alpha</div>
                    <div class="text-[11px] text-gray-400 mt-0.5">Tanpa Keterangan</div>
                </div>
            </div>
        </div>

        <!-- Card: Status Presensi Gerbang Hari Ini -->
        <div class="card bg-white border border-gray-100 shadow-sm rounded-2xl p-5 md:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-100">
                <div>
                    <h3 class="font-bold text-gray-900 text-base">Presensi Siswa Hari Ini</h3>
                    <p class="text-xs text-gray-400 mt-0.5">{{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('l, d F Y') }}</p>
                </div>
                <div>
                    @if($todayDetail['check_in'])
                        <span class="badge badge-success gap-1.5 py-3 px-3 text-xs font-semibold">
                            <i data-lucide="check-circle" class="h-4 w-4"></i>
                            Sudah Terdata Masuk
                        </span>
                    @else
                        <span class="badge badge-ghost gap-1.5 py-3 px-3 text-xs font-semibold text-gray-600">
                            <i data-lucide="clock" class="h-4 w-4"></i>
                            Menunggu Scan Masuk
                        </span>
                    @endif
                </div>
            </div>

            <div class="pt-4 flex flex-col sm:flex-row items-center gap-4 bg-blue-50/50 rounded-xl p-4 border border-blue-100/60 mt-2">
                <div class="w-12 h-12 rounded-xl bg-blue-600 text-white flex items-center justify-center text-xl font-bold flex-shrink-0">
                    <i data-lucide="qr-code" class="h-6 w-6"></i>
                </div>
                <div class="flex-1 text-center sm:text-left">
                    <div class="text-xs font-bold text-gray-900">Cara Melakukan Presensi Siswa:</div>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Tunjukkan kartu pelajar Anda kepada petugas gerbang sekolah atau wali kelas saat memasuki lingkungan sekolah untuk scan otomatis.
                    </p>
                </div>
            </div>
        </div>

        <!-- Card: Permohonan Izin / Sakit Siswa -->
        <div class="card bg-white border border-gray-100 shadow-sm rounded-2xl p-5 md:p-6">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                <div>
                    <h3 class="font-bold text-gray-900 text-base">Surat Izin & Sakit Saya</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Ajukan surat izin atau surat dokter jika berhalangan hadir</p>
                </div>
                <a href="{{ route('leave-requests.create') }}" class="btn btn-primary btn-sm rounded-xl font-semibold gap-1.5">
                    <i data-lucide="plus" class="h-4 w-4"></i>
                    <span>Ajukan Izin</span>
                </a>
            </div>
            <div class="divide-y divide-gray-100 pt-1">
                @forelse($personalLeaves as $leave)
                @php
                    $leaveBadge = match($leave->status) {
                        'approved' => 'badge-success',
                        'rejected' => 'badge-error',
                        default => 'badge-warning',
                    };
                    $leaveStatusText = match($leave->status) {
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default => 'Menunggu',
                    };
                @endphp
                <div class="flex items-center justify-between py-3 hover:bg-gray-50/50 px-1 rounded-xl transition-colors">
                    <div>
                        <div class="text-xs font-bold text-gray-900">
                            {{ ucfirst($leave->type) }} · {{ \Carbon\Carbon::parse($leave->start_date)->format('d M') }} - {{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}
                        </div>
                        <div class="text-[11px] text-gray-400 mt-0.5 truncate max-w-sm">{{ $leave->reason ?? 'Tidak ada keterangan' }}</div>
                    </div>
                    <span class="badge {{ $leaveBadge }} badge-sm font-semibold text-[10px]">{{ $leaveStatusText }}</span>
                </div>
                @empty
                <div class="py-8 text-center text-xs text-gray-400">Belum ada pengajuan izin/sakit yang diajukan.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Kolom Kanan (4 Kolom Desktop): Riwayat Presensi Siswa -->
    <div class="lg:col-span-4 flex flex-col h-full">
        <div class="flex items-center justify-between mb-3 px-1 flex-shrink-0">
            <h3 class="text-sm font-semibold text-gray-500">Riwayat Presensi Saya</h3>
            <span class="text-[11px] text-gray-400">14 hari terakhir</span>
        </div>

        <div class="card bg-white border border-gray-100 shadow-sm rounded-2xl flex-1 flex flex-col min-h-0">
            <div class="card-body p-4 md:p-5 flex-1 flex flex-col min-h-0">
                <div class="flex-1 overflow-y-auto divide-y divide-gray-100 -mx-1 px-1 pr-1.5 custom-feed-scrollbar min-h-0">
                    @forelse($personalHistory as $att)
                    @php
                        $attBadge = match($att->status) {
                            'ontime' => ['badge' => 'badge-success', 'label' => 'Hadir'],
                            'late' => ['badge' => 'badge-warning', 'label' => 'Terlambat'],
                            'sick' => ['badge' => 'badge-info', 'label' => 'Sakit'],
                            'permit' => ['badge' => 'badge-warning', 'label' => 'Izin'],
                            'alpha' => ['badge' => 'badge-error', 'label' => 'Alpha'],
                            default => ['badge' => 'badge-ghost', 'label' => ucfirst($att->status ?? '-')],
                        };
                    @endphp
                    <div class="py-3 hover:bg-gray-50/50 rounded-xl px-1.5 transition-colors">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-bold text-gray-900">
                                {{ \Carbon\Carbon::parse($att->date)->translatedFormat('l, d M Y') }}
                            </span>
                            <span class="badge {{ $attBadge['badge'] }} badge-xs font-semibold text-[9px]">
                                {{ $attBadge['label'] }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2 text-[11px] text-gray-500 mt-1">
                            <i data-lucide="clock" class="h-3 w-3 text-blue-500"></i>
                            <span>Masuk: {{ $att->check_in ? $att->check_in->format('H:i') . ' WIB' : '—' }}</span>
                        </div>
                    </div>
                    @empty
                    <div class="py-12 text-center text-xs text-gray-400 flex items-center justify-center">
                        Belum ada riwayat absensi.
                    </div>
                    @endforelse
                </div>

                <div class="pt-3 border-t border-gray-100 mt-2 text-center flex-shrink-0">
                    <a href="{{ route('attendance.reports') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 transition-colors inline-flex items-center gap-1">
                        <span>Lihat Rekap Kehadiran Lengkap</span>
                        <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
