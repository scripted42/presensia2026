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

