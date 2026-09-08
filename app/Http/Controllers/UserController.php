<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SuperAdmin;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\ClassStudent;
use App\Models\EmployeeProfile;
use App\Models\StudentProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

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
        $schoolId = auth()->user()->school_id;
        
        $superAdminEmails = SuperAdmin::pluck('email');

        $query = User::with(['school', 'roles', 'studentClasses', 'employeeProfile'])
            ->where('school_id', $schoolId)
            ->where('user_type', $type)
            ->whereDoesntHave('roles', function($q){
                $q->where('name', 'super-admin');
            })
            ->whereNotIn('email', $superAdminEmails)
            ->where('email', 'not like', 'superadmin@%');

        // Apply centralized filter scope
        $query->filter($request->all());

        $users = $query->orderBy('name', 'asc')
            ->paginate($perPage)
            ->appends($request->query());

        // Data for dynamic dropdown filters
        $classes = collect();
        $levels = collect();
        $roles = collect();
        $employmentStatuses = ['PNS', 'PPPK', 'GTT / PTT', 'Honorer', 'Tetap Yayasan', 'Kontrak'];

        if ($type === 'student') {
            $classes = SchoolClass::where('school_id', $schoolId)
                ->where('is_active', true)
                ->orderBy('level')
                ->orderBy('name')
                ->get();

            $levels = SchoolClass::where('school_id', $schoolId)
                ->where('is_active', true)
                ->whereNotNull('level')
                ->where('level', '!=', '')
                ->distinct()
                ->pluck('level')
                ->sort()
                ->values();
        } else {
            $roles = \Spatie\Permission\Models\Role::whereNotIn('name', ['super-admin', 'student'])->get();
        }

        // Build active filters for badges/chips
        $activeFilters = [];
        if ($q = trim($request->get('q', ''))) {
            $activeFilters[] = [
                'key' => 'q',
                'label' => 'Cari',
                'value' => $q,
                'removeUrl' => request()->fullUrlWithQuery(['q' => null]),
            ];
        }
        if ($classId = $request->get('class_id')) {
            $className = $classId === 'none' ? 'Tanpa Kelas' : ($classes->firstWhere('id', $classId)?->name ?? $classId);
            $activeFilters[] = [
                'key' => 'class_id',
                'label' => 'Kelas',
                'value' => $className,
                'removeUrl' => request()->fullUrlWithQuery(['class_id' => null]),
            ];
        }
        if ($level = $request->get('level')) {
            $activeFilters[] = [
                'key' => 'level',
                'label' => 'Tingkat',
                'value' => 'Tingkat ' . $level,
                'removeUrl' => request()->fullUrlWithQuery(['level' => null]),
            ];
        }
        if ($gender = $request->get('gender')) {
            $activeFilters[] = [
                'key' => 'gender',
                'label' => 'Gender',
                'value' => $gender === 'L' ? 'Laki-laki' : 'Perempuan',
                'removeUrl' => request()->fullUrlWithQuery(['gender' => null]),
            ];
        }
        if ($request->filled('is_active')) {
            $activeFilters[] = [
                'key' => 'is_active',
                'label' => 'Status',
                'value' => $request->get('is_active') == '1' ? 'Aktif' : 'Tidak Aktif',
                'removeUrl' => request()->fullUrlWithQuery(['is_active' => null]),
            ];
        }
        if ($role = $request->get('role')) {
            $roleObj = $roles->firstWhere('name', $role);
            $activeFilters[] = [
                'key' => 'role',
                'label' => 'Role',
                'value' => $roleObj?->display_name ?? $roleObj?->name ?? $role,
                'removeUrl' => request()->fullUrlWithQuery(['role' => null]),
            ];
        }
        if ($empStatus = $request->get('employment_status')) {
            $activeFilters[] = [
                'key' => 'employment_status',
                'label' => 'Status Pegawai',
                'value' => $empStatus,
                'removeUrl' => request()->fullUrlWithQuery(['employment_status' => null]),
            ];
        }

        // Summary Cards Metrics
        $schoolId = Auth::user()->school_id;
        $summaryCards = [];

        if ($type === 'student') {
            $totalStudents = User::where('school_id', $schoolId)->where('user_type', 'student')->count();
            $maleStudents = User::where('school_id', $schoolId)->where('user_type', 'student')->where('gender', 'L')->count();
            $femaleStudents = User::where('school_id', $schoolId)->where('user_type', 'student')->where('gender', 'P')->count();
            $hasClasses = User::where('school_id', $schoolId)->where('user_type', 'student')->whereHas('studentClasses')->count();
            $noClasses = $totalStudents - $hasClasses;
            $activeStudents = User::where('school_id', $schoolId)->where('user_type', 'student')->where('is_active', true)->count();
            $inactiveStudents = $totalStudents - $activeStudents;

            $summaryCards = [
                [
                    'title' => 'Total Siswa',
                    'value' => number_format($totalStudents),
                    'subtext' => 'Terdaftar di sekolah',
                    'icon' => 'fas fa-user-graduate',
                    'color' => 'blue',
                ],
                [
                    'title' => 'Jenis Kelamin',
                    'value' => "{$maleStudents} L / {$femaleStudents} P",
                    'subtext' => '<span class="text-blue-600 font-medium">L: ' . $maleStudents . '</span> · <span class="text-pink-600 font-medium">P: ' . $femaleStudents . '</span>',
                    'icon' => 'fas fa-venus-mars',
                    'color' => 'purple',
                ],
                [
                    'title' => 'Penempatan Kelas',
                    'value' => number_format($hasClasses) . ' Siswa',
                    'subtext' => $noClasses > 0 ? '<span class="text-amber-600 font-medium">' . $noClasses . ' belum ada kelas</span>' : '<span class="text-emerald-600 font-medium">Semua memiliki kelas</span>',
                    'icon' => 'fas fa-chalkboard-teacher',
                    'color' => 'emerald',
                ],
                [
                    'title' => 'Status Akun',
                    'value' => number_format($activeStudents) . ' Aktif',
                    'subtext' => $inactiveStudents > 0 ? '<span class="text-rose-600 font-medium">' . $inactiveStudents . ' nonaktif</span>' : '<span class="text-emerald-600 font-medium">100% aktif</span>',
                    'icon' => 'fas fa-user-check',
                    'color' => $inactiveStudents > 0 ? 'amber' : 'green',
                ],
            ];
        } else {
            $totalEmployees = User::where('school_id', $schoolId)->where('user_type', 'employee')->count();
            $maleEmployees = User::where('school_id', $schoolId)->where('user_type', 'employee')->where('gender', 'L')->count();
            $femaleEmployees = User::where('school_id', $schoolId)->where('user_type', 'employee')->where('gender', 'P')->count();
            $activeEmployees = User::where('school_id', $schoolId)->where('user_type', 'employee')->where('is_active', true)->count();
            $inactiveEmployees = $totalEmployees - $activeEmployees;
            $pnsCount = User::where('school_id', $schoolId)->where('user_type', 'employee')
                ->whereHas('employeeProfile', function($q) {
                    $q->whereIn('employment_status', ['PNS', 'PPPK', 'PNS Depag', 'PNS Diperbantukan']);
                })->count();
            $honorerCount = $totalEmployees - $pnsCount;

            $summaryCards = [
                [
                    'title' => 'Total Pegawai',
                    'value' => number_format($totalEmployees),
                    'subtext' => 'Pendidik & Tenaga Kependidikan',
                    'icon' => 'fas fa-users-cog',
                    'color' => 'blue',
                ],
                [
                    'title' => 'Jenis Kelamin',
                    'value' => "{$maleEmployees} L / {$femaleEmployees} P",
                    'subtext' => '<span class="text-blue-600 font-medium">L: ' . $maleEmployees . '</span> · <span class="text-pink-600 font-medium">P: ' . $femaleEmployees . '</span>',
                    'icon' => 'fas fa-venus-mars',
                    'color' => 'purple',
                ],
                [
                    'title' => 'Status Kepegawaian',
                    'value' => $pnsCount . ' ASN / ' . $honorerCount . ' Non-ASN',
                    'subtext' => 'PNS/PPPK & Honorer/GTT',
                    'icon' => 'fas fa-id-badge',
                    'color' => 'amber',
                ],
                [
                    'title' => 'Status Akun',
                    'value' => number_format($activeEmployees) . ' Aktif',
                    'subtext' => $inactiveEmployees > 0 ? '<span class="text-rose-600 font-medium">' . $inactiveEmployees . ' nonaktif</span>' : '<span class="text-emerald-600 font-medium">100% aktif</span>',
                    'icon' => 'fas fa-user-check',
                    'color' => 'emerald',
                ],
            ];
        }
        
        return view('users.index', compact('users', 'type', 'perPageParam', 'classes', 'levels', 'roles', 'employmentStatuses', 'activeFilters', 'summaryCards'));
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
            $created = 0; $duplicates = 0; $updatedClasses = 0; $errors = 0; $errorDetails = [];
            foreach ($rows as $row) {
                try {
                    // Determine final email and password
                    $defaultEmail = $type === 'student' 
                        ? (strtolower(trim($row['nis'])) . '.' . auth()->user()->school_id . '@siswa.local') 
                        : (strtolower(trim($row['nik'])) . '.' . auth()->user()->school_id . '@pegawai.local');
                    $finalEmail = !empty($row['email']) ? $row['email'] : $defaultEmail;
                    $finalPassword = !empty($row['password']) ? $row['password'] : ($type === 'student' ? $row['nis'] : $row['nik']);

                    // recheck duplicate by email and nik/nis
                    $existsQuery = User::where('school_id', auth()->user()->school_id)
                        ->where(function($q) use ($row, $type, $finalEmail){
                            $q->where('email', $finalEmail);
                            if ($type === 'employee' && !empty($row['nik'])) $q->orWhere('nik', $row['nik']);
                            if ($type === 'student' && !empty($row['nis'])) $q->orWhere('nis', $row['nis']);
                        });
                    $existingUser = $existsQuery->first();
                    if ($existingUser) { 
                        $duplicates++; 
                        // Jika siswa sudah ada dan di file terdapat kolom kelas, hubungkan/update kelasnya
                        if ($type === 'student' && !empty($row['class'])) {
                            $this->assignStudentToClass($existingUser, $row['class']);
                            $updatedClasses++;
                        }
                        continue; 
                    }

                    $user = User::create([
                        'school_id' => auth()->user()->school_id,
                        'name' => $row['name'],
                        'email' => $finalEmail,
                        'password' => Hash::make($finalPassword ?: 'password'),
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

                    // Hubungkan siswa ke kelas jika kolom kelas diisi
                    if ($type === 'student' && !empty($row['class'])) {
                        $this->assignStudentToClass($user, $row['class']);
                    }

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
                    $errorDetails[] = "Error pada {$row['name']}: " . $e->getMessage();
                }
            }

            $messageParts = [];
            if ($created > 0) $messageParts[] = "{$created} akun baru dibuat";
            if ($updatedClasses > 0) $messageParts[] = "{$updatedClasses} siswa berhasil dihubungkan ke kelas";
            $skippedDuplicates = $duplicates - $updatedClasses;
            if ($skippedDuplicates > 0) $messageParts[] = "{$skippedDuplicates} data duplikat dilewati";
            if ($errors > 0) $messageParts[] = "{$errors} gagal";

            $message = "Import selesai: " . (empty($messageParts) ? "Tidak ada perubahan." : implode(', ', $messageParts) . ".");
            if (!empty($errorDetails)) {
                $message .= " Detail error: " . implode('; ', $errorDetails);
            }
            
            return redirect()->route('users.index', ['type' => $type])
                ->with('success', $message);
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

        // Role, email, password, birth_place, phone, address, birth_date, gender opsional
        $headerMap = [
            'nama' => 'name',
            'nama siswa' => 'name',
            'nama_siswa' => 'name',
            'nama lengkap' => 'name',
            'nama_lengkap' => 'name',
            'jenis kelamin' => 'gender',
            'jenis_kelamin' => 'gender',
            'jk' => 'gender',
            'tanggal lahir' => 'birth_date',
            'tanggal_lahir' => 'birth_date',
            'tgl lahir' => 'birth_date',
            'tgl_lahir' => 'birth_date',
            'tempat lahir' => 'birth_place',
            'tempat_lahir' => 'birth_place',
            'alamat' => 'address',
            'kelas' => 'class',
            'kelas_siswa' => 'class',
            'rombel' => 'class',
            'rombongan_belajar' => 'class',
            'tingkat' => 'class',
            'telepon' => 'phone',
            'no hp' => 'phone',
            'no_hp' => 'phone',
            'nomor hp' => 'phone',
            'nomor_hp' => 'phone',
            'hp' => 'phone',
            'peran' => 'role',
        ];
        $header = array_map(fn($h) => $headerMap[$h] ?? $h, $header);

        // Hanya kolom utama yang benar-benar wajib di header CSV
        $required = $type === 'employee' ? ['name', 'nik'] : ['name', 'nis'];
        $missing = array_diff($required, $header);
        if (!empty($missing)) {
            return redirect()->back()->withErrors(['file' => 'Header CSV tidak sesuai template. Kolom wajib yang hilang: '.implode(', ', $missing)]);
        }

        $rows = []; $preview = []; $line = 1; $fileDuplicates = 0; $dbDuplicates = 0; $valid = 0; $invalid = 0; $updates = 0; $seenKeys = [];
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
            if (count($data) !== count($header)) {
                continue;
            }
            $row = array_combine($header, $data);
            // normalize
            $row['name'] = trim($row['name'] ?? '');
            $originalEmail = trim($row['email'] ?? '');
            $row['email'] = strtolower($originalEmail);
            $row['password'] = trim($row['password'] ?? '');

            // normalisasi gender (L/P, Laki-Laki, Perempuan, dll)
            $rawGender = strtoupper(trim($row['gender'] ?? ''));
            if (in_array($rawGender, ['L', 'LAKI-LAKI', 'LAKI - LAKI', 'PRIA', 'M', 'MALE'])) {
                $row['gender'] = 'L';
            } elseif (in_array($rawGender, ['P', 'PEREMPUAN', 'WANITA', 'F', 'FEMALE'])) {
                $row['gender'] = 'P';
            } else {
                $row['gender'] = $rawGender;
            }

            $rawBirthDate = trim($row['birth_date'] ?? '');
            $row['birth_date'] = $rawBirthDate !== '' ? $parseDate($rawBirthDate) : null;
            // map role
            $rawRole = strtolower(trim($row['role'] ?? ''));
            if (isset($roleAliases[$rawRole])) { $rawRole = $roleAliases[$rawRole]; }
            $row['role'] = $rawRole !== '' ? $rawRole : ($type === 'student' ? 'student' : null);
            // optional birth_place, phone, address passthrough
            $row['birth_place'] = trim($row['birth_place'] ?? '');
            $row['phone'] = trim($row['phone'] ?? '');
            $row['address'] = trim($row['address'] ?? '');
            // optional extended fields
            if ($type === 'employee') {
                $row['nuptk'] = trim($row['nuptk'] ?? '');
                $row['nik'] = trim($row['nik'] ?? '');
                if ($row['password'] === '') {
                    $row['password'] = $row['nik']; // Default password = NIK
                }
                if ($row['email'] === '') {
                    $row['email'] = strtolower($row['nik']) . '.' . auth()->user()->school_id . '@pegawai.local';
                }
                $key = ($row['nik'] !== '' ? $row['nik'] : $row['email']);
            } else {
                $row['kk_number'] = trim($row['kk_number'] ?? '');
                $row['kip_number'] = trim($row['kip_number'] ?? '');
                $row['nis'] = trim($row['nis'] ?? '');
                $row['nisn'] = trim($row['nisn'] ?? '');
                $row['class'] = trim($row['class'] ?? '');
                if ($row['password'] === '') {
                    $row['password'] = $row['nis']; // Default password = NIS
                }
                if ($row['email'] === '') {
                    $row['email'] = strtolower($row['nis']) . '.' . auth()->user()->school_id . '@siswa.local';
                }
                $key = ($row['nis'] !== '' ? $row['nis'] : $row['email']);
            }

            $issues = [];
            if ($row['name'] === '') $issues[] = 'Nama kosong';
            if ($originalEmail !== '' && !filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
                $issues[] = 'Email tidak valid';
            }
            if ($row['password'] === '' || strlen($row['password']) < 4) {
                $issues[] = 'Password minimal 4 karakter (atau pastikan NIS/NIK terisi)';
            }
            if ($row['gender'] !== '' && !in_array($row['gender'], ['L','P'])) $issues[] = 'Gender harus L/P';
            // Validasi format tanggal lahir hanya jika diisi (opsional jika kosong)
            if ($rawBirthDate !== '' && ($row['birth_date'] ?? null) === null) {
                if (preg_match('/[A-Za-z]/', $rawBirthDate)) {
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

            $hasFatalError = !empty($issues);

            // DB duplicate check
            $exists = User::where('school_id', auth()->user()->school_id)
                ->where(function($q) use ($row, $type){
                    if ($type === 'employee' && !empty($row['nik'])) $q->where('nik', $row['nik']);
                    if ($type === 'student' && !empty($row['nis'])) $q->where('nis', $row['nis']);
                    $q->orWhere('email', $row['email']);
                })->exists();

            if ($exists) { 
                $dbDuplicates++; 
            }

            if (!$hasFatalError) {
                if ($exists && $type === 'student' && !empty($row['class'])) {
                    $updates++;
                    $rows[] = $row;
                    $status = 'update';
                    $issues[] = 'Siswa sudah ada di database (akan dihubungkan ke kelas ' . $row['class'] . ')';
                } elseif ($exists) {
                    $invalid++;
                    $status = 'invalid';
                    $issues[] = 'Sudah ada di database';
                } else {
                    $valid++;
                    $rows[] = $row;
                    $status = 'valid';
                }
            } else {
                if ($exists) {
                    $issues[] = 'Sudah ada di database';
                }
                $invalid++;
                $status = 'invalid';
            }

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
                'update' => $updates,
                'invalid' => $invalid,
                'fileDuplicates' => $fileDuplicates,
                'dbDuplicates' => $dbDuplicates,
                'total' => $valid + $updates + $invalid,
            ],
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
            $sample  = ['Budi Santoso','','','3173xxxxxxxxxxxx','08123456789','Jl. Merdeka 1, Jakarta','Jakarta','31-08-1990','L','teacher','123456789012'];
        } else {
            $headers = ['name','email','password','nis','nisn','class','gender','phone','address','birth_place','birth_date','role','kk_number','kip_number'];
            $sample  = ['Siti Aminah','','','12001','3200xxxxxxxxxx','7A','P','08129876543','Jl. Kenanga 2, Surabaya','Surabaya','15-07-2011','student','3173xxxxxxxxxxxx','KIP123456789'];
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
     * Hubungkan siswa ke kelas (buat kelas otomatis jika belum ada di sekolah)
     */
    private function assignStudentToClass(User $student, string $className): void
    {
        $className = trim($className);
        if (empty($className)) return;

        // Ekstrak tingkat kelas (contoh: "7A" -> "7", "VII-B" -> "7", "8" -> "8", "Kelas 9" -> "9")
        $level = preg_replace('/[^0-9]/', '', $className);
        if (empty($level)) {
            if (stripos($className, 'vii') !== false) $level = '7';
            elseif (stripos($className, 'viii') !== false) $level = '8';
            elseif (stripos($className, 'ix') !== false) $level = '9';
            else $level = '7';
        }

        $schoolClass = SchoolClass::firstOrCreate(
            [
                'school_id' => $student->school_id,
                'name' => $className,
            ],
            [
                'level' => $level,
                'year' => (int) date('Y'),
                'is_active' => true,
            ]
        );

        ClassStudent::updateOrCreate(
            [
                'class_id' => $schoolClass->id,
                'student_id' => $student->id,
            ],
            [
                'status' => 'active',
                'enrolled_at' => now(),
            ]
        );
    }

    /**
     * Export users to Excel (.xlsx)
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'employee');
        $superAdminEmails = SuperAdmin::pluck('email');

        $query = User::with(['school', 'roles', 'employeeProfile', 'studentProfile', 'studentClasses'])
            ->where('school_id', auth()->user()->school_id)
            ->where('user_type', $type)
            ->whereDoesntHave('roles', function($q) {
                $q->where('name', 'super-admin');
            })
            ->whereNotIn('email', $superAdminEmails)
            ->where('email', 'not like', 'superadmin@%');

        // Apply centralized filter scope
        $query->filter($request->all());

        $users = $query->orderBy('name', 'asc')->get();

        if ($users->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data ' . ($type === 'employee' ? 'pegawai' : 'siswa') . ' untuk di-export.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        if ($type === 'employee') {
            $sheet->setTitle('Data Pegawai');
            $headers = [
                'A1' => 'No',
                'B1' => 'NIK/NIP',
                'C1' => 'NUPTK',
                'D1' => 'Nama Lengkap',
                'E1' => 'Email',
                'F1' => 'Role / Jabatan',
                'G1' => 'Jenis Kelamin',
                'H1' => 'No. Telepon',
                'I1' => 'Tempat Lahir',
                'J1' => 'Tanggal Lahir',
                'K1' => 'Alamat',
                'L1' => 'Jenis PTK',
                'M1' => 'Status Kepegawaian',
                'N1' => 'Status Akun',
            ];
            $lastCol = 'N';
        } else {
            $sheet->setTitle('Data Siswa');
            $headers = [
                'A1' => 'No',
                'B1' => 'NIS',
                'C1' => 'NISN',
                'D1' => 'Nama Lengkap',
                'E1' => 'Kelas',
                'F1' => 'Jenis Kelamin',
                'G1' => 'Email',
                'H1' => 'No. Telepon',
                'I1' => 'Tempat Lahir',
                'J1' => 'Tanggal Lahir',
                'K1' => 'Alamat',
                'L1' => 'Nama Ayah',
                'M1' => 'Nama Ibu',
                'N1' => 'No. KK',
                'O1' => 'No. KIP',
                'P1' => 'Status Akun',
            ];
            $lastCol = 'P';
        }

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        // Header Styling
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2563EB'], // Blue 600
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(28);

        $rowNum = 2;
        foreach ($users as $index => $u) {
            $gender = $u->gender === 'L' ? 'Laki-laki' : ($u->gender === 'P' ? 'Perempuan' : ($u->gender ?? '-'));
            $statusAkun = $u->is_active ? 'Aktif' : 'Tidak Aktif';

            if ($type === 'employee') {
                $nik = $u->nik ?? $u->employeeProfile?->nip ?? '-';
                $nuptk = $u->employeeProfile?->nuptk ?? '-';
                $role = $u->roles->first()?->display_name ?? $u->roles->first()?->name ?? 'No Role';
                $phone = $u->phone ?? $u->employeeProfile?->phone ?? '-';
                $pob = $u->employeeProfile?->place_of_birth ?? '-';
                $dob = $u->birth_date ? $u->birth_date->format('d/m/Y') : ($u->employeeProfile?->date_of_birth ? $u->employeeProfile->date_of_birth->format('d/m/Y') : '-');
                $address = $u->address ?? $u->employeeProfile?->address_line ?? '-';
                $ptkType = $u->employeeProfile?->ptk_type ?? '-';
                $empStatus = $u->employeeProfile?->employment_status ?? '-';

                $sheet->setCellValue('A' . $rowNum, $index + 1);
                $sheet->setCellValueExplicit('B' . $rowNum, $nik, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('C' . $rowNum, $nuptk, DataType::TYPE_STRING);
                $sheet->setCellValue('D' . $rowNum, $u->name);
                $sheet->setCellValue('E' . $rowNum, $u->email);
                $sheet->setCellValue('F' . $rowNum, $role);
                $sheet->setCellValue('G' . $rowNum, $gender);
                $sheet->setCellValueExplicit('H' . $rowNum, $phone, DataType::TYPE_STRING);
                $sheet->setCellValue('I' . $rowNum, $pob);
                $sheet->setCellValue('J' . $rowNum, $dob);
                $sheet->setCellValue('K' . $rowNum, $address);
                $sheet->setCellValue('L' . $rowNum, $ptkType);
                $sheet->setCellValue('M' . $rowNum, $empStatus);
                $sheet->setCellValue('N' . $rowNum, $statusAkun);
            } else {
                $nis = $u->nis ?? $u->studentProfile?->nis ?? '-';
                $nisn = $u->nisn ?? $u->studentProfile?->nisn ?? '-';
                $className = $u->studentClasses->first()?->name ?? '-';
                $phone = $u->phone ?? $u->studentProfile?->phone ?? '-';
                $pob = $u->studentProfile?->place_of_birth ?? '-';
                $dob = $u->birth_date ? $u->birth_date->format('d/m/Y') : ($u->studentProfile?->date_of_birth ? $u->studentProfile->date_of_birth->format('d/m/Y') : '-');
                $address = $u->address ?? $u->studentProfile?->address_line ?? '-';
                $father = $u->studentProfile?->father_name ?? '-';
                $mother = $u->studentProfile?->mother_name ?? '-';
                $kk = $u->studentProfile?->kk_number ?? '-';
                $kip = $u->studentProfile?->kip_number ?? '-';

                $sheet->setCellValue('A' . $rowNum, $index + 1);
                $sheet->setCellValueExplicit('B' . $rowNum, $nis, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('C' . $rowNum, $nisn, DataType::TYPE_STRING);
                $sheet->setCellValue('D' . $rowNum, $u->name);
                $sheet->setCellValue('E' . $rowNum, $className);
                $sheet->setCellValue('F' . $rowNum, $gender);
                $sheet->setCellValue('G' . $rowNum, $u->email);
                $sheet->setCellValueExplicit('H' . $rowNum, $phone, DataType::TYPE_STRING);
                $sheet->setCellValue('I' . $rowNum, $pob);
                $sheet->setCellValue('J' . $rowNum, $dob);
                $sheet->setCellValue('K' . $rowNum, $address);
                $sheet->setCellValue('L' . $rowNum, $father);
                $sheet->setCellValue('M' . $rowNum, $mother);
                $sheet->setCellValueExplicit('N' . $rowNum, $kk, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('O' . $rowNum, $kip, DataType::TYPE_STRING);
                $sheet->setCellValue('P' . $rowNum, $statusAkun);
            }

            // Zebra stripe
            if ($rowNum % 2 === 0) {
                $sheet->getStyle("A{$rowNum}:{$lastCol}{$rowNum}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F8FAFC');
            }

            $sheet->getRowDimension($rowNum)->setRowHeight(22);
            $rowNum++;
        }

        $lastRow = $rowNum - 1;

        // Border styling
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'E2E8F0'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];
        $sheet->getStyle("A1:{$lastCol}{$lastRow}")->applyFromArray($borderStyle);

        // Alignment
        $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        if ($type === 'employee') {
            $sheet->getStyle("B2:C{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G2:G{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("J2:J{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("N2:N{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        } else {
            $sheet->getStyle("B2:C{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E2:F{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("J2:J{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("P2:P{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // Auto column width
        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = ($type === 'employee' ? 'data_pegawai_' : 'data_siswa_') . date('Y-m-d_H-i') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
