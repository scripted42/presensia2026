<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\School;
use App\Models\SchoolClass;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $school = School::first();

        // Create admin user
        $admin = User::create([
            'school_id' => $school->id,
            'name' => 'Administrator',
            'email' => 'admin@presensia.com',
            'password' => Hash::make('password'),
            'phone' => '081234567890',
            'address' => 'Jl. Admin No. 1',
            'birth_date' => '1990-01-01',
            'gender' => 'L',
            'qr_code' => Str::random(32),
            'nik' => '1234567890123456',
            'user_type' => 'employee',
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        // Create teacher user
        $teacher = User::create([
            'school_id' => $school->id,
            'name' => 'Guru Matematika',
            'email' => 'guru@presensia.com',
            'password' => Hash::make('password'),
            'phone' => '081234567891',
            'address' => 'Jl. Guru No. 1',
            'birth_date' => '1985-05-15',
            'gender' => 'P',
            'qr_code' => Str::random(32),
            'nik' => '1234567890123457',
            'user_type' => 'employee',
            'is_active' => true,
        ]);
        $teacher->assignRole('teacher');

        // Create TU user
        $tu = User::create([
            'school_id' => $school->id,
            'name' => 'Tata Usaha',
            'email' => 'tu@presensia.com',
            'password' => Hash::make('password'),
            'phone' => '081234567892',
            'address' => 'Jl. TU No. 1',
            'birth_date' => '1988-03-20',
            'gender' => 'L',
            'qr_code' => Str::random(32),
            'nik' => '1234567890123458',
            'user_type' => 'employee',
            'is_active' => true,
        ]);
        $tu->assignRole('tu');

        // Create BK user
        $bk = User::create([
            'school_id' => $school->id,
            'name' => 'Bimbingan Konseling',
            'email' => 'bk@presensia.com',
            'password' => Hash::make('password'),
            'phone' => '081234567893',
            'address' => 'Jl. BK No. 1',
            'birth_date' => '1987-07-10',
            'gender' => 'P',
            'qr_code' => Str::random(32),
            'nik' => '1234567890123459',
            'user_type' => 'employee',
            'is_active' => true,
        ]);
        $bk->assignRole('bk');

        // Create Kesiswaan user
        $kesiswaan = User::create([
            'school_id' => $school->id,
            'name' => 'Kesiswaan',
            'email' => 'kesiswaan@presensia.com',
            'password' => Hash::make('password'),
            'phone' => '081234567894',
            'address' => 'Jl. Kesiswaan No. 1',
            'birth_date' => '1986-11-25',
            'gender' => 'L',
            'qr_code' => Str::random(32),
            'nik' => '1234567890123460',
            'user_type' => 'employee',
            'is_active' => true,
        ]);
        $kesiswaan->assignRole('kesiswaan');

        // Create sample students
        for ($i = 1; $i <= 10; $i++) {
            $student = User::create([
                'school_id' => $school->id,
                'name' => "Siswa $i",
                'email' => "siswa$i@presensia.com",
                'password' => Hash::make('password'),
                'phone' => '08123456789' . (5 + $i),
                'address' => "Jl. Siswa No. $i",
                'birth_date' => '2005-01-01',
                'gender' => $i % 2 == 0 ? 'P' : 'L',
                'qr_code' => Str::random(32),
                'nis' => 'SISWA' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'nisn' => '123456789' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'user_type' => 'student',
                'is_active' => true,
            ]);
            $student->assignRole('student');
        }

        // Create sample classes
        $class1 = SchoolClass::create([
            'school_id' => $school->id,
            'name' => 'X IPA 1',
            'level' => 'X',
            'major' => 'IPA',
            'year' => 2024,
            'teacher_id' => $teacher->id,
            'description' => 'Kelas X IPA 1',
            'is_active' => true,
        ]);

        $class2 = SchoolClass::create([
            'school_id' => $school->id,
            'name' => 'X IPS 1',
            'level' => 'X',
            'major' => 'IPS',
            'year' => 2024,
            'teacher_id' => $teacher->id,
            'description' => 'Kelas X IPS 1',
            'is_active' => true,
        ]);

        // Assign students to classes
        $students = User::where('user_type', 'student')->get();
        foreach ($students->take(5) as $index => $student) {
            $class1->students()->attach($student->id, [
                'status' => 'active',
                'enrolled_at' => now(),
            ]);
        }

        foreach ($students->skip(5)->take(5) as $index => $student) {
            $class2->students()->attach($student->id, [
                'status' => 'active',
                'enrolled_at' => now(),
            ]);
        }
    }
}
