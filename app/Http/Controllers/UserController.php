<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\EmployeeProfile;
use App\Models\StudentProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $type = $request->get('type', 'employee'); // Default to employee
        $perPageParam = $request->get('per_page', '10');
        $perPage = (string) $perPageParam === 'all' ? 1000000 : max(1, (int) $perPageParam);
        
        $query = User::with(['school', 'roles'])
            ->where('school_id', auth()->user()->school_id)
            ->where('user_type', $type);
            
        $users = $query->paginate($perPage)->appends([
            'type' => $type,
            'per_page' => $perPageParam,
        ]);
        
        return view('users.index', compact('users', 'type', 'perPageParam'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $type = $request->get('type', 'employee'); // Default to employee
        $schools = School::all();
        $classes = SchoolClass::where('school_id', auth()->user()->school_id)->get();
        
        return view('users.create', compact('schools', 'classes', 'type'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'user_type' => 'required|in:employee,student',
            'role' => 'required|string',
        ]);

        $user = User::create([
            'school_id' => auth()->user()->school_id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
            'birth_date' => $request->birth_date,
            'gender' => $request->gender,
            'qr_code' => Str::random(32),
            'nik' => $request->nik,
            'nis' => $request->nis,
            'nisn' => $request->nisn,
            'user_type' => $request->user_type,
            'is_active' => true,
        ]);

        $user->assignRole($request->role);

        return redirect()->route('users.index', ['type' => $user->user_type])
            ->with('success', 'User berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load(['school', 'roles', 'attendances' => function($query) {
            $query->latest()->limit(10);
        }]);
        
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $schools = School::all();
        $classes = SchoolClass::where('school_id', auth()->user()->school_id)->get();
        $user->load(['employeeProfile', 'studentProfile']);
        
        return view('users.edit', compact('user', 'schools', 'classes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|string',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'birth_date' => $request->birth_date,
            'gender' => $request->gender,
            'nik' => $request->nik,
            'nis' => $request->nis,
            'nisn' => $request->nisn,
            // user_type tidak diubah pada update dari halaman ini
            'is_active' => (bool) ((int) $request->input('is_active', $user->is_active ? 1 : 0)),
        ]);

        if ($request->password) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        $user->syncRoles([$request->role]);

        // Upsert Dapodik profiles
        if ($user->isEmployee()) {
            EmployeeProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nuptk' => $request->nuptk,
                    'nip' => $request->nip ?? $request->nik,
                    'nik' => $request->nik,
                    'place_of_birth' => $request->place_of_birth,
                    'date_of_birth' => $request->birth_date,
                    'religion' => $request->religion,
                    'address_line' => $request->address_line ?? $request->address,
                    'rt' => $request->rt,
                    'rw' => $request->rw,
                    'village' => $request->village,
                    'district' => $request->district,
                    'city' => $request->city,
                    'province' => $request->province,
                    'postal_code' => $request->postal_code,
                    'phone' => $request->phone,
                    'npwp' => $request->npwp,
                    'bank_name' => $request->bank_name,
                    'bank_account' => $request->bank_account,
                    'mother_maiden_name' => $request->mother_maiden_name,
                    'marital_status' => $request->marital_status,
                    'children_count' => $request->children_count,
                    'last_education' => $request->last_education,
                    'major' => $request->major,
                    'university' => $request->university,
                    'graduation_year' => $request->graduation_year,
                    'ptk_type' => $request->ptk_type,
                    'employment_status' => $request->employment_status,
                    'rank' => $request->rank,
                    'salary_source' => $request->salary_source,
                    'certification_number' => $request->certification_number,
                    'certification_year' => $request->certification_year,
                    'main_subject' => $request->main_subject,
                    'teaching_hours_per_week' => $request->teaching_hours_per_week,
                    'sk_cpns' => $request->sk_cpns,
                    'tmt_cpns' => $request->tmt_cpns,
                    'sk_appointment' => $request->sk_appointment,
                    'tmt_appointment' => $request->tmt_appointment,
                    'bpjs_number' => $request->bpjs_number,
                ]
            );
        } else {
            StudentProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nis' => $request->nis ?? $user->nis,
                    'nisn' => $request->nisn ?? $user->nisn,
                    'nik' => $request->nik,
                    'birth_certificate_number' => $request->birth_certificate_number,
                    'kk_number' => $request->kk_number,
                    'kks_number' => $request->kks_number,
                    'kip_number' => $request->kip_number,
                    'pkh_number' => $request->pkh_number,
                    'place_of_birth' => $request->place_of_birth,
                    'date_of_birth' => $request->birth_date,
                    'religion' => $request->religion,
                    'citizenship' => $request->citizenship,
                    'gender' => $request->gender,
                    'address_line' => $request->address_line ?? $request->address,
                    'rt' => $request->rt,
                    'rw' => $request->rw,
                    'village' => $request->village,
                    'district' => $request->district,
                    'city' => $request->city,
                    'province' => $request->province,
                    'postal_code' => $request->postal_code,
                    'phone' => $request->phone,
                    'father_name' => $request->father_name,
                    'mother_name' => $request->mother_name,
                    'guardian_name' => $request->guardian_name,
                    'father_job' => $request->father_job,
                    'mother_job' => $request->mother_job,
                    'guardian_job' => $request->guardian_job,
                    'father_phone' => $request->father_phone,
                    'mother_phone' => $request->mother_phone,
                    'guardian_phone' => $request->guardian_phone,
                    'admission_year' => $request->admission_year,
                    'previous_school' => $request->previous_school,
                    'transportation' => $request->transportation,
                    'residence_type' => $request->residence_type,
                    'sibling_count' => $request->sibling_count,
                    'order_in_family' => $request->order_in_family,
                    'special_needs' => $request->special_needs,
                    'blood_type' => $request->blood_type,
                ]
            );
        }

        return redirect()->route('users.index', ['type' => $user->user_type])
            ->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();
        
        return redirect()->route('users.index')
            ->with('success', 'User berhasil dihapus.');
    }

    /**
     * Show import form.
     */
    public function showImportForm(Request $request)
    {
        $type = $request->get('type', 'employee');
        return view('users.import', compact('type'));
    }

    /**
     * Import users from Excel/CSV.
     */
    public function import(Request $request)
    {
        $type = $request->get('type', 'employee');

        // Commit phase
        if ($request->boolean('confirm')) {
            $rows = session()->pull('import_rows');
            if (!$rows) {
                return redirect()->back()->withErrors(['file' => 'Sesi import habis atau data tidak ditemukan.']);
            }
            $created = 0; $duplicates = 0; $errors = 0;
            foreach ($rows as $row) {
                try {
                    // recheck duplicate by email and nik/nis
                    $existsQuery = User::where('school_id', auth()->user()->school_id)
                        ->where(function($q) use ($row, $type){
                            $q->where('email', $row['email']);
                            if ($type === 'employee' && !empty($row['nik'])) $q->orWhere('nik', $row['nik']);
                            if ($type === 'student' && !empty($row['nis'])) $q->orWhere('nis', $row['nis']);
                        });
                    if ($existsQuery->exists()) { $duplicates++; continue; }

                    $user = User::create([
                        'school_id' => auth()->user()->school_id,
                        'name' => $row['name'],
                        'email' => $row['email'],
                        'password' => Hash::make($row['password'] ?: 'password'),
                        'phone' => $row['phone'] ?? null,
                        'address' => $row['address'] ?? null,
                        'birth_date' => $row['birth_date'] ?? null,
                        'gender' => $row['gender'] ?? null,
                        'nik' => $type === 'employee' ? ($row['nik'] ?? null) : null,
                        'nis' => $type === 'student' ? ($row['nis'] ?? null) : null,
                        'nisn' => $type === 'student' ? ($row['nisn'] ?? null) : null,
                        'user_type' => $type,
                        'is_active' => true,
                    ]);
                    $role = $row['role'] ?? ($type === 'student' ? 'student' : 'teacher');
                    $user->assignRole($role);

                    // Create minimal profile with birth_place + birth_date mapping
                    if ($type === 'employee') {
                        EmployeeProfile::updateOrCreate(
                            ['user_id' => $user->id],
                            [
                                'nuptk' => $row['nuptk'] ?? null,
                                'place_of_birth' => $row['birth_place'] ?? null,
                                'date_of_birth' => $row['birth_date'] ?? null,
                                'nik' => $row['nik'] ?? null,
                            ]
                        );
                    } else {
                        StudentProfile::updateOrCreate(
                            ['user_id' => $user->id],
                            [
                                'place_of_birth' => $row['birth_place'] ?? null,
                                'date_of_birth' => $row['birth_date'] ?? null,
                                'nis' => $row['nis'] ?? null,
                                'nisn' => $row['nisn'] ?? null,
                            ]
                        );
                    }
                    $created++;
                } catch (\Throwable $e) {
                    $errors++;
                }
            }

            return redirect()->route('users.index', ['type' => $type])
                ->with('success', "Import selesai: {$created} berhasil, {$duplicates} duplikasi, {$errors} gagal.");
        }

        // Preview phase
        $request->validate([
            'file' => 'required|file|mimes:csv,txt', // fokus CSV untuk kesederhanaan
        ]);

        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');
        if ($handle === false) {
            return redirect()->back()->withErrors(['file' => 'Tidak bisa membuka file.']);
        }

        $header = fgetcsv($handle);
        if (!$header) { return redirect()->back()->withErrors(['file' => 'Header CSV tidak ditemukan.']); }
        $header = array_map(fn($h) => strtolower(trim($h)), $header);

        // Role dibuat opsional; birth_place opsional untuk menghindari kekeliruan umum
        $requiredEmployee = ['name','email','password','nik','phone','address','birth_date','gender'];
        // pastikan tidak meminta 'nik' untuk siswa
        $requiredStudent  = ['name','email','password','nis','nisn','phone','address','birth_date','gender'];
        $required = $type === 'employee' ? $requiredEmployee : $requiredStudent;
        $missing = array_diff($required, $header);
        if (!empty($missing)) {
            return redirect()->back()->withErrors(['file' => 'Header CSV tidak sesuai template. Kolom hilang: '.implode(', ', $missing)]);
        }

        $rows = []; $preview = []; $line = 1; $fileDuplicates = 0; $dbDuplicates = 0; $valid = 0; $invalid = 0; $seenKeys = [];
        // util untuk parse tanggal multi-format
        $parseDate = function($value) {
            $v = trim($value ?? '');
            if ($v === '') return null;
            // coba Y-m-d, d-m-Y, d/m/Y
            foreach (["Y-m-d","d-m-Y","d/m/Y","d.m.Y"] as $fmt) {
                $dt = \DateTime::createFromFormat($fmt, $v);
                if ($dt && $dt->format($fmt) === $v) {
                    return $dt->format('Y-m-d');
                }
            }
            return null; // tidak valid
        };

        $allowedRoles = ['admin','teacher','tu','bk','kesiswaan','student'];
        $roleAliases = [
            'guru' => 'teacher',
            'siswa' => 'student',
            'tata usaha' => 'tu',
        ];

        while (($data = fgetcsv($handle)) !== false) {
            $line++;
            $row = array_combine($header, $data);
            // normalize
            $row['name'] = trim($row['name'] ?? '');
            $row['email'] = strtolower(trim($row['email'] ?? ''));
            $row['password'] = trim($row['password'] ?? '');
            $row['gender'] = strtoupper(trim($row['gender'] ?? ''));
            $rawBirthDate = trim($row['birth_date'] ?? '');
            $row['birth_date'] = $parseDate($row['birth_date'] ?? null);
            // map role
            $rawRole = strtolower(trim($row['role'] ?? ''));
            if (isset($roleAliases[$rawRole])) { $rawRole = $roleAliases[$rawRole]; }
            $row['role'] = $rawRole !== '' ? $rawRole : null;
            // optional birth_place passthrough
            $row['birth_place'] = trim($row['birth_place'] ?? '');
            // optional extended fields
            if ($type === 'employee') {
                $row['nuptk'] = trim($row['nuptk'] ?? '');
            } else {
                $row['kk_number'] = trim($row['kk_number'] ?? '');
                $row['kip_number'] = trim($row['kip_number'] ?? '');
            }
            if ($type === 'employee') { $key = $row['email'].'|'.($row['nik'] ?? ''); }
            else { $key = $row['email'].'|'.($row['nis'] ?? ''); }

            $issues = [];
            if ($row['name'] === '') $issues[] = 'Nama kosong';
            if (!filter_var($row['email'], FILTER_VALIDATE_EMAIL)) $issues[] = 'Email tidak valid';
            if ($row['password'] === '' || strlen($row['password']) < 6) $issues[] = 'Password minimal 6 karakter';
            if (!in_array($row['gender'], ['L','P'])) $issues[] = 'Gender harus L/P';
            if (($row['birth_date'] ?? null) === null) {
                if ($rawBirthDate !== '' && preg_match('/[A-Za-z]/', $rawBirthDate)) {
                    $issues[] = 'Kolom birth_date berisi teks (mungkin tempat lahir?). Tambahkan kolom birth_place atau pindahkan tanggal lahir.';
                } else {
                    $issues[] = 'Tanggal lahir tidak valid (format: dd-mm-yyyy atau yyyy-mm-dd)';
                }
            }
            if ($row['role'] && !in_array($row['role'], $allowedRoles)) $issues[] = 'Role tidak valid (gunakan: admin, teacher, tu, bk, kesiswaan, student)';
            if ($type === 'employee' && empty($row['nik'])) $issues[] = 'NIK wajib';
            if ($type === 'student' && empty($row['nis'])) $issues[] = 'NIS wajib';

            if (isset($seenKeys[$key])) { $issues[] = 'Duplikasi di file'; $fileDuplicates++; }
            $seenKeys[$key] = true;

            // DB duplicate check
            $exists = User::where('school_id', auth()->user()->school_id)
                ->where(function($q) use ($row, $type){
                    $q->where('email', $row['email']);
                    if ($type === 'employee' && !empty($row['nik'])) $q->orWhere('nik', $row['nik']);
                    if ($type === 'student' && !empty($row['nis'])) $q->orWhere('nis', $row['nis']);
                })->exists();
            if ($exists) { $issues[] = 'Sudah ada di database'; $dbDuplicates++; }

            if (empty($issues)) { $valid++; $rows[] = $row; $status = 'valid'; }
            else { $invalid++; $status = 'invalid'; }

            $preview[] = [
                'line' => $line,
                'data' => $row,
                'issues' => $issues,
                'status' => $status,
            ];
        }
        fclose($handle);

        session(['import_rows' => $rows]);

        return view('users.import_preview', [
            'type' => $type,
            'preview' => $preview,
            'summary' => [
                'valid' => $valid,
                'invalid' => $invalid,
                'fileDuplicates' => $fileDuplicates,
                'dbDuplicates' => $dbDuplicates,
                'total' => $valid + $invalid,
            ]
        ]);
    }

    /**
     * Download CSV template per type
     */
    public function downloadTemplate(Request $request)
    {
        $type = $request->get('type', 'employee');
        if ($type === 'employee') {
            $headers = ['name','email','password','nik','phone','address','birth_place','birth_date','gender','role','nuptk'];
            $sample  = ['Budi Santoso','budi@example.com','Password123','3173xxxxxxxxxxxx','08123456789','Jl. Merdeka 1, Jakarta','Jakarta','31-08-1990','L','teacher','123456789012'];
        } else {
            $headers = ['name','email','password','nis','nisn','phone','address','birth_place','birth_date','gender','role','kk_number','kip_number'];
            $sample  = ['Siti Aminah','siti@example.com','Password123','12001','3200xxxxxxxxxx','08129876543','Jl. Kenanga 2, Bandung','Bandung','15-07-2008','P','student','3173xxxxxxxxxxxx','KIP123456789'];
        }

        // Gunakan fputcsv agar kolom yang mengandung koma otomatis di-quote
        $fp = fopen('php://temp', 'r+');
        fputcsv($fp, $headers);
        fputcsv($fp, $sample);
        rewind($fp);
        $csv = stream_get_contents($fp);
        fclose($fp);

        $filename = $type.'-template.csv';
        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"'
        ]);
    }

    /**
     * Export users to Excel/CSV.
     */
    public function export(Request $request)
    {
        // TODO: Implement Excel/CSV export logic
        return redirect()->route('users.index')
            ->with('success', 'Export berhasil. (Fitur dalam pengembangan)');
    }
}
