<!-- Employee (Teacher, TU, Staff) Dashboard -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8 items-stretch">
    <!-- Kolom Kiri (8 Kolom Desktop): Stat Cards Personal, Presensi Mandiri, Tugas Operasional, Pengajuan Izin -->
    <div class="lg:col-span-8 flex flex-col gap-6">
        <!-- Statistics Cards Personal -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5 desktop-stats-grid">
            @php
                $status = $todayDetail['status'] ?? 'none';
                $stConfig = [
                    'none' => ['badge' => 'badge-ghost', 'text' => 'Belum Absen', 'color' => 'text-gray-500', 'bg' => 'bg-gray-50'],
                    'in_only' => ['badge' => 'badge-warning', 'text' => 'Sudah Masuk', 'color' => 'text-amber-600', 'bg' => 'bg-amber-50'],
                    'completed' => ['badge' => 'badge-success', 'text' => 'Absensi Selesai', 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50'],
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
                    <div class="text-xs font-semibold text-gray-500">Status Hari Ini</div>
                    <div class="text-sm font-bold text-gray-900 mt-0.5">
                        @if($status === 'completed')
                            {{ substr($todayDetail['check_in'] ?? '', 0, 5) }} - {{ substr($todayDetail['check_out'] ?? '', 0, 5) }}
                        @elseif($status === 'in_only')
                            Masuk: {{ substr($todayDetail['check_in'] ?? '', 0, 5) }}
                        @else
                            Belum Check In
                        @endif
                    </div>
                </div>
            </div>

            <!-- Card 2: Kehadiran Bulan Ini -->
            <div class="bg-white rounded-2xl border border-gray-100 p-3.5 sm:p-4 shadow-sm flex flex-col justify-between card-hover">
                <div class="flex items-center justify-between">
                    <div class="p-2 bg-blue-50 text-blue-600 rounded-xl">
                        <i data-lucide="check-circle" class="h-4 w-4"></i>
                    </div>
                    <div class="text-xl font-bold text-gray-900">{{ $monthSummary['present'] ?? 0 }} <span class="text-xs font-normal text-gray-400">hari</span></div>
                </div>
                <div class="mt-2.5">
                    <div class="text-xs font-semibold text-gray-500">Hadir Bulan Ini</div>
                    <div class="text-[11px] text-gray-400 mt-0.5">Tepat Waktu: {{ $monthSummary['ontime'] ?? 0 }} hari</div>
                </div>
            </div>

            <!-- Card 3: Terlambat Bulan Ini -->
            <div class="bg-white rounded-2xl border border-gray-100 p-3.5 sm:p-4 shadow-sm flex flex-col justify-between card-hover">
                <div class="flex items-center justify-between">
                    <div class="p-2 bg-amber-50 text-amber-600 rounded-xl">
                        <i data-lucide="clock" class="h-4 w-4"></i>
                    </div>
                    <div class="text-xl font-bold text-gray-900">{{ $monthSummary['late'] ?? 0 }} <span class="text-xs font-normal text-gray-400">kali</span></div>
                </div>
                <div class="mt-2.5">
                    <div class="text-xs font-semibold text-gray-500">Terlambat</div>
                    <div class="text-[11px] text-gray-400 mt-0.5">Bulan {{ $monthSummary['month_name'] ?? '' }}</div>
                </div>
            </div>

            <!-- Card 4: Izin & Cuti -->
            <div class="bg-white rounded-2xl border border-gray-100 p-3.5 sm:p-4 shadow-sm flex flex-col justify-between card-hover">
                <div class="flex items-center justify-between">
                    <div class="p-2 bg-purple-50 text-purple-600 rounded-xl">
                        <i data-lucide="file-text" class="h-4 w-4"></i>
                    </div>
                    <div class="text-xl font-bold text-gray-900">{{ ($monthSummary['permit'] ?? 0) + ($monthSummary['sick'] ?? 0) }} <span class="text-xs font-normal text-gray-400">hari</span></div>
                </div>
                <div class="mt-2.5">
                    <div class="text-xs font-semibold text-gray-500">Izin & Sakit</div>
                    <div class="text-[11px] text-gray-400 mt-0.5">Izin: {{ $monthSummary['permit'] ?? 0 }} | Sakit: {{ $monthSummary['sick'] ?? 0 }}</div>
                </div>
            </div>
        </div>

        <!-- Card: Presensi Mandiri Hari Ini (Action Box) -->
        <div class="card bg-white border border-gray-100 shadow-sm rounded-2xl p-5 md:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-100">
                <div>
                    <h3 class="font-bold text-gray-900 text-base">Presensi Mandiri Hari Ini</h3>
                    <p class="text-xs text-gray-400 mt-0.5">{{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('l, d F Y') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    @if($status === 'none')
                        <a href="{{ route('attendance.check-in') }}" class="btn btn-primary btn-sm rounded-xl font-semibold gap-1.5 shadow-sm">
                            <i data-lucide="log-in" class="h-4 w-4"></i>
                            <span>Absen Masuk</span>
                        </a>
                    @elseif($status === 'in_only')
                        <a href="{{ route('attendance.check-out') }}" class="btn btn-warning btn-sm rounded-xl font-semibold gap-1.5 shadow-sm text-white">
                            <i data-lucide="log-out" class="h-4 w-4"></i>
                            <span>Absen Keluar</span>
                        </a>
                    @else
                        <span class="badge badge-success gap-1 py-3 px-3 text-xs font-semibold">
                            <i data-lucide="check-circle" class="h-3.5 w-3.5"></i>
                            Presensi Selesai
                        </span>
                    @endif
                </div>
            </div>

            <!-- Jam Check-In & Check-Out Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4">
                <div class="flex items-center gap-3.5 p-3.5 rounded-xl bg-gray-50/80 border border-gray-100">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                        <i data-lucide="sunrise" class="h-5 w-5"></i>
                    </div>
                    <div>
                        <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Jam Masuk</div>
                        <div class="text-base font-bold text-gray-900">
                            {{ $todayDetail['check_in'] ? substr($todayDetail['check_in'], 0, 5) . ' WIB' : '—' }}
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3.5 p-3.5 rounded-xl bg-gray-50/80 border border-gray-100">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                        <i data-lucide="sunset" class="h-5 w-5"></i>
                    </div>
                    <div>
                        <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Jam Keluar</div>
                        <div class="text-base font-bold text-gray-900">
                            {{ $todayDetail['check_out'] ? substr($todayDetail['check_out'], 0, 5) . ' WIB' : '—' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Operasional Khusus: Guru (Kelas Saya) ATAU TU (Administrasi Izin) -->
        @if($user->hasRole('teacher'))
        <div class="card bg-white border border-gray-100 shadow-sm rounded-2xl p-5 md:p-6">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                <div>
                    <h3 class="font-bold text-gray-900 text-base">Kelas yang Saya Ampu</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Kelola dan scan kehadiran siswa di kelas</p>
                </div>
                <a href="{{ route('attendance.student-scan') }}" class="btn btn-outline btn-primary btn-sm rounded-xl font-semibold gap-1.5">
                    <i data-lucide="scan-line" class="h-4 w-4"></i>
                    <span>Scan Siswa</span>
                </a>
            </div>
            <div class="divide-y divide-gray-100 pt-1">
                @forelse($taughtClasses as $cls)
                <div class="flex items-center justify-between py-3 hover:bg-gray-50/50 px-1 rounded-xl transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center font-bold text-xs">
                            <i data-lucide="book-open" class="h-4 w-4"></i>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-gray-900">{{ $cls->name }}</div>
                            <div class="text-xs text-gray-400">{{ $cls->students_count ?? 0 }} Siswa terdaftar</div>
                        </div>
                    </div>
                    <a href="{{ route('attendance.student-scan') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800">
                        Buka Presensi
                    </a>
                </div>
                @empty
                <div class="py-8 text-center text-xs text-gray-400">Belum ada kelas yang ditetapkan untuk Anda.</div>
                @endforelse
            </div>
        </div>
        @elseif($user->hasRole('tu'))
        <div class="card bg-white border border-gray-100 shadow-sm rounded-2xl p-5 md:p-6">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                <div>
                    <h3 class="font-bold text-gray-900 text-base">Tugas Administrasi Tata Usaha</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Kelola verifikasi izin dan akses master data sekolah</p>
                </div>
                <a href="{{ route('leave-requests.index') }}" class="badge badge-warning gap-1 py-3 px-3 text-xs font-bold text-gray-800">
                    <i data-lucide="inbox" class="h-3.5 w-3.5"></i>
                    {{ $pendingLeaveCount ?? 0 }} Izin Pending
                </a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 pt-4">
                <a href="{{ route('leave-requests.index') }}" class="p-3.5 rounded-xl border border-gray-100 bg-gray-50/60 hover:bg-gray-100/80 transition-colors flex items-center gap-3 group">
                    <div class="p-2.5 bg-yellow-100 text-yellow-800 rounded-xl group-hover:scale-105 transition-transform">
                        <i data-lucide="file-check" class="h-5 w-5"></i>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-gray-900">Verifikasi Izin</div>
                        <div class="text-[11px] text-gray-400">Proses permohonan</div>
                    </div>
                </a>

                <a href="{{ route('users.index', ['type' => 'students']) }}" class="p-3.5 rounded-xl border border-gray-100 bg-gray-50/60 hover:bg-gray-100/80 transition-colors flex items-center gap-3 group">
                    <div class="p-2.5 bg-green-100 text-green-800 rounded-xl group-hover:scale-105 transition-transform">
                        <i data-lucide="graduation-cap" class="h-5 w-5"></i>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-gray-900">Data Siswa</div>
                        <div class="text-[11px] text-gray-400">Kelola master siswa</div>
                    </div>
                </a>

                <a href="{{ route('users.index', ['type' => 'employees']) }}" class="p-3.5 rounded-xl border border-gray-100 bg-gray-50/60 hover:bg-gray-100/80 transition-colors flex items-center gap-3 group">
                    <div class="p-2.5 bg-blue-100 text-blue-800 rounded-xl group-hover:scale-105 transition-transform">
                        <i data-lucide="users" class="h-5 w-5"></i>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-gray-900">Data Pegawai</div>
                        <div class="text-[11px] text-gray-400">Kelola master staf/guru</div>
                    </div>
                </a>
            </div>
        </div>
        @endif

        <!-- Card: Pengajuan Izin Saya -->
        <div class="card bg-white border border-gray-100 shadow-sm rounded-2xl p-5 md:p-6">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                <div>
                    <h3 class="font-bold text-gray-900 text-base">Pengajuan Izin Saya</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Status permohonan izin/cuti yang Anda ajukan</p>
                </div>
                <a href="{{ route('leave-requests.create') }}" class="btn btn-outline btn-sm rounded-xl font-semibold gap-1">
                    <i data-lucide="plus" class="h-3.5 w-3.5"></i>
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
                <div class="py-8 text-center text-xs text-gray-400">Belum ada pengajuan izin/cuti terbaru.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Kolom Kanan (4 Kolom Desktop): Riwayat Absensi Saya (Privat 100% data sendiri) -->
    <div class="lg:col-span-4 flex flex-col h-full">
        <div class="flex items-center justify-between mb-3 px-1 flex-shrink-0">
            <h3 class="text-sm font-semibold text-gray-500">Riwayat Absensi Saya</h3>
            <span class="text-[11px] text-gray-400">14 hari terakhir</span>
        </div>

        <div class="card bg-white border border-gray-100 shadow-sm rounded-2xl flex-1 flex flex-col min-h-0">
            <div class="card-body p-4 md:p-5 flex-1 flex flex-col min-h-0">
                <div class="flex-1 overflow-y-auto divide-y divide-gray-100 -mx-1 px-1 pr-1.5 custom-feed-scrollbar min-h-0">
                    @forelse($personalHistory as $att)
                    @php
                        $attBadge = match($att->status) {
                            'ontime' => ['badge' => 'badge-success', 'label' => 'Tepat Waktu'],
                            'late' => ['badge' => 'badge-warning', 'label' => 'Terlambat'],
                            'sick' => ['badge' => 'badge-info', 'label' => 'Sakit'],
                            'permit' => ['badge' => 'badge-warning', 'label' => 'Izin'],
                            'duty' => ['badge' => 'badge-neutral', 'label' => 'Dinas'],
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
                        <div class="flex items-center gap-3 text-[11px] text-gray-500 mt-1">
                            <span class="flex items-center gap-1">
                                <i data-lucide="log-in" class="h-3 w-3 text-blue-500"></i>
                                <span>{{ $att->check_in ? $att->check_in->format('H:i') : '—' }}</span>
                            </span>
                            <span class="text-gray-300">•</span>
                            <span class="flex items-center gap-1">
                                <i data-lucide="log-out" class="h-3 w-3 text-amber-500"></i>
                                <span>{{ $att->check_out ? $att->check_out->format('H:i') : '—' }}</span>
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="py-12 text-center text-xs text-gray-400 flex items-center justify-center">
                        Belum ada riwayat absensi.
                    </div>
                    @endforelse
                </div>

                <div class="pt-3 border-t border-gray-100 mt-2 text-center flex-shrink-0">
                    <a href="{{ route('attendance.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 transition-colors inline-flex items-center gap-1">
                        <span>Lihat Rekap Absensi Lengkap</span>
                        <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
