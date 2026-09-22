<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('users:sync-passwords {--type=all : student, employee, or all}', function () {
    $type = $this->option('type') ?: 'all';
    $this->info("Menyinkronkan password default (Siswa -> NIS, Pegawai -> NIK)...");

    $studentCount = 0;
    if (in_array($type, ['all', 'student'])) {
        $students = \App\Models\User::role('student')->whereNotNull('nis')->where('nis', '!=', '')->get();
        foreach ($students as $student) {
            $student->password = \Illuminate\Support\Facades\Hash::make((string) $student->nis);
            $student->save();
            $studentCount++;
        }
        $this->info("Berhasil mereset/sinkron password untuk {$studentCount} siswa ke NIS masing-masing.");
    }

    $employeeCount = 0;
    if (in_array($type, ['all', 'employee'])) {
        $employees = \App\Models\User::where('user_type', 'employee')->whereNotNull('nik')->where('nik', '!=', '')->get();
        foreach ($employees as $emp) {
            $emp->password = \Illuminate\Support\Facades\Hash::make((string) $emp->nik);
            $emp->save();
            $employeeCount++;
        }
        $this->info("Berhasil mereset/sinkron password untuk {$employeeCount} pegawai ke NIK masing-masing.");
    }

    $this->info("Sinkronisasi password selesai.");
})->purpose('Sinkronkan password siswa ke NIS dan pegawai ke NIK');

Artisan::command('attendance:sync-status {--date= : Tanggal spesifik YYYY-MM-DD atau all}', function () {
    $date = $this->option('date');
    $query = \App\Models\Attendance::with('user')->whereNotNull('check_in')->whereIn('status', ['ontime', 'late']);
    
    if ($date && $date !== 'all') {
        $query->whereDate('date', $date);
        $this->info("Menyinkronkan status absensi untuk tanggal {$date}...");
    } elseif ($date !== 'all') {
        $today = \Carbon\Carbon::today('Asia/Jakarta')->format('Y-m-d');
        $query->whereDate('date', $today);
        $this->info("Menyinkronkan status absensi hari ini ({$today})...");
    } else {
        $this->info("Menyinkronkan semua status absensi...");
    }

    $attendances = $query->get();
    $updated = 0;
    foreach ($attendances as $att) {
        if (!$att->user) continue;
        $correctStatus = \App\Models\Attendance::determineStatusFor($att->user, $att->check_in);
        if ($att->status !== $correctStatus) {
            $this->line("Update [{$att->user->name}] tgl {$att->date->format('Y-m-d')} jam {$att->check_in->format('H:i')}: {$att->status} -> {$correctStatus}");
            $att->status = $correctStatus;
            $att->saveQuietly();
            $updated++;
        }
    }

    $this->info("Selesai. Total data disinkronkan: {$updated} dari {$attendances->count()} data absensi.");
})->purpose('Sinkronkan ulang status absensi (ontime/late) berdasarkan jam masuk dan aturan batas waktu');

