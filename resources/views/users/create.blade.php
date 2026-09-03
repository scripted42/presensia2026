@extends('layouts.app')

@section('title', 'Tambah User - Presensia')

@section('content')
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">
                            @if($type === 'employee')
                                Tambah Pegawai Baru
                            @else
                                Tambah Siswa Baru
                            @endif
                        </h1>
                        <p class="text-xs text-gray-500 mt-1">
                            @if($type === 'employee')
                                Tambah data pegawai baru ke sistem
                            @else
                                Tambah data siswa baru ke sistem
                            @endif
                        </p>
                    </div>
                    <a href="{{ route('users.index', ['type' => $type]) }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors text-sm font-medium">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                    </a>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="bg-white shadow rounded-lg">
            <form method="POST" action="{{ route('users.store') }}" class="p-6">
                @csrf
                <input type="hidden" name="user_type" value="{{ $type }}">
                
                <!-- Basic Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nama Lengkap *</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password *</label>
                        <input type="password" name="password" id="password" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">No. Telepon</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <!-- User Type and Role -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="user_type" class="block text-sm font-medium text-gray-700">Tipe User *</label>
                        <select name="user_type" id="user_type" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Pilih Tipe User</option>
                            <option value="employee" {{ old('user_type') == 'employee' ? 'selected' : '' }}>Pegawai</option>
                            <option value="student" {{ old('user_type') == 'student' ? 'selected' : '' }}>Siswa</option>
                        </select>
                        @error('user_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700">Role *</label>
                        <select name="role" id="role" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Pilih Role</option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="teacher" {{ old('role') == 'teacher' ? 'selected' : '' }}>Guru</option>
                            <option value="headmaster" {{ old('role') == 'headmaster' ? 'selected' : '' }}>Kepala Sekolah</option>
                            <option value="tu" {{ old('role') == 'tu' ? 'selected' : '' }}>Tata Usaha</option>
                            <option value="bk" {{ old('role') == 'bk' ? 'selected' : '' }}>BK</option>
                            <option value="kesiswaan" {{ old('role') == 'kesiswaan' ? 'selected' : '' }}>Kesiswaan</option>
                            <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>Siswa</option>
                        </select>
                        @error('role')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="gender" class="block text-sm font-medium text-gray-700">Jenis Kelamin</label>
                        <select name="gender" id="gender"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="L" {{ old('gender') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ old('gender') == 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>

                    <div>
                        <label for="birth_date" class="block text-sm font-medium text-gray-700">Tanggal Lahir</label>
                        <input type="date" name="birth_date" id="birth_date" value="{{ old('birth_date') }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <!-- Employee/Student Specific Fields -->
                <div id="employee-fields" class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6" style="display: none;">
                    <div>
                        <label for="nik" class="block text-sm font-medium text-gray-700">NIK</label>
                        <input type="text" name="nik" id="nik" value="{{ old('nik') }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="nuptk" class="block text-sm font-medium text-gray-700">NUPTK</label>
                        <input type="text" name="nuptk" id="nuptk" value="{{ old('nuptk') }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="employee_id" class="block text-sm font-medium text-gray-700">ID Pegawai</label>
                        <input type="text" name="employee_id" id="employee_id" value="{{ old('employee_id') }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="position" class="block text-sm font-medium text-gray-700">Jabatan</label>
                        <input type="text" name="position" id="position" value="{{ old('position') }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="bank_name" class="block text-sm font-medium text-gray-700">Nama Bank</label>
                        <input type="text" name="bank_name" id="bank_name" value="{{ old('bank_name') }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="bank_account" class="block text-sm font-medium text-gray-700">No. Rekening</label>
                        <input type="text" name="bank_account" id="bank_account" value="{{ old('bank_account') }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="npwp" class="block text-sm font-medium text-gray-700">NPWP</label>
                        <input type="text" name="npwp" id="npwp" value="{{ old('npwp') }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="employment_status" class="block text-sm font-medium text-gray-700">Status Kepegawaian</label>
                        <select name="employment_status" id="employment_status"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Pilih Status</option>
                            <option value="PNS" {{ old('employment_status') == 'PNS' ? 'selected' : '' }}>PNS</option>
                            <option value="CPNS" {{ old('employment_status') == 'CPNS' ? 'selected' : '' }}>CPNS</option>
                            <option value="Guru Honor" {{ old('employment_status') == 'Guru Honor' ? 'selected' : '' }}>Guru Honor</option>
                            <option value="Tenaga Honor" {{ old('employment_status') == 'Tenaga Honor' ? 'selected' : '' }}>Tenaga Honor</option>
                        </select>
                    </div>
                    <div>
                        <label for="certification_number" class="block text-sm font-medium text-gray-700">No. Sertifikasi</label>
                        <input type="text" name="certification_number" id="certification_number" value="{{ old('certification_number') }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="certification_year" class="block text-sm font-medium text-gray-700">Tahun Sertifikasi</label>
                        <input type="number" name="certification_year" id="certification_year" value="{{ old('certification_year') }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="subject_taught" class="block text-sm font-medium text-gray-700">Mata Pelajaran</label>
                        <input type="text" name="subject_taught" id="subject_taught" value="{{ old('subject_taught') }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="teaching_hours" class="block text-sm font-medium text-gray-700">Jam Mengajar/Minggu</label>
                        <input type="number" name="teaching_hours" id="teaching_hours" value="{{ old('teaching_hours') }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <div id="student-fields" class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6" style="display: none;">
                    <div>
                        <label for="nis" class="block text-sm font-medium text-gray-700">NIS</label>
                        <input type="text" name="nis" id="nis" value="{{ old('nis') }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="nisn" class="block text-sm font-medium text-gray-700">NISN</label>
                        <input type="text" name="nisn" id="nisn" value="{{ old('nisn') }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="student_id" class="block text-sm font-medium text-gray-700">ID Siswa</label>
                        <input type="text" name="student_id" id="student_id" value="{{ old('student_id') }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="class_name" class="block text-sm font-medium text-gray-700">Kelas</label>
                        <input type="text" name="class_name" id="class_name" value="{{ old('class_name') }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="birth_certificate_number" class="block text-sm font-medium text-gray-700">No. Akta Lahir</label>
                        <input type="text" name="birth_certificate_number" id="birth_certificate_number" value="{{ old('birth_certificate_number') }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="kk_number" class="block text-sm font-medium text-gray-700">No. KK</label>
                        <input type="text" name="kk_number" id="kk_number" value="{{ old('kk_number') }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="kip_number" class="block text-sm font-medium text-gray-700">No. KIP</label>
                        <input type="text" name="kip_number" id="kip_number" value="{{ old('kip_number') }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="citizenship" class="block text-sm font-medium text-gray-700">Kewarganegaraan</label>
                        <select name="citizenship" id="citizenship"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Pilih Kewarganegaraan</option>
                            <option value="WNI" {{ old('citizenship') == 'WNI' ? 'selected' : '' }}>WNI</option>
                            <option value="WNA" {{ old('citizenship') == 'WNA' ? 'selected' : '' }}>WNA</option>
                        </select>
                    </div>
                    <div>
                        <label for="residence_type" class="block text-sm font-medium text-gray-700">Jenis Tinggal</label>
                        <select name="residence_type" id="residence_type"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Pilih Jenis Tinggal</option>
                            <option value="Bersama Orang Tua" {{ old('residence_type') == 'Bersama Orang Tua' ? 'selected' : '' }}>Bersama Orang Tua</option>
                            <option value="Wali" {{ old('residence_type') == 'Wali' ? 'selected' : '' }}>Wali</option>
                            <option value="Kost" {{ old('residence_type') == 'Kost' ? 'selected' : '' }}>Kost</option>
                            <option value="Asrama" {{ old('residence_type') == 'Asrama' ? 'selected' : '' }}>Asrama</option>
                        </select>
                    </div>
                    <div>
                        <label for="siblings_count" class="block text-sm font-medium text-gray-700">Jumlah Saudara</label>
                        <input type="number" name="siblings_count" id="siblings_count" value="{{ old('siblings_count') }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="child_order" class="block text-sm font-medium text-gray-700">Anak ke-</label>
                        <input type="number" name="child_order" id="child_order" value="{{ old('child_order') }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="special_needs" class="block text-sm font-medium text-gray-700">Kebutuhan Khusus</label>
                        <select name="special_needs" id="special_needs"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Pilih Kebutuhan Khusus</option>
                            <option value="Tidak" {{ old('special_needs') == 'Tidak' ? 'selected' : '' }}>Tidak</option>
                            <option value="Tuna Netra" {{ old('special_needs') == 'Tuna Netra' ? 'selected' : '' }}>Tuna Netra</option>
                            <option value="Tuna Rungu" {{ old('special_needs') == 'Tuna Rungu' ? 'selected' : '' }}>Tuna Rungu</option>
                            <option value="Tuna Grahita" {{ old('special_needs') == 'Tuna Grahita' ? 'selected' : '' }}>Tuna Grahita</option>
                            <option value="Tuna Daksa" {{ old('special_needs') == 'Tuna Daksa' ? 'selected' : '' }}>Tuna Daksa</option>
                            <option value="Tuna Laras" {{ old('special_needs') == 'Tuna Laras' ? 'selected' : '' }}>Tuna Laras</option>
                            <option value="Tuna Wicara" {{ old('special_needs') == 'Tuna Wicara' ? 'selected' : '' }}>Tuna Wicara</option>
                        </select>
                    </div>
                    <div>
                        <label for="blood_type" class="block text-sm font-medium text-gray-700">Golongan Darah</label>
                        <select name="blood_type" id="blood_type"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Pilih Golongan Darah</option>
                            <option value="A" {{ old('blood_type') == 'A' ? 'selected' : '' }}>A</option>
                            <option value="B" {{ old('blood_type') == 'B' ? 'selected' : '' }}>B</option>
                            <option value="AB" {{ old('blood_type') == 'AB' ? 'selected' : '' }}>AB</option>
                            <option value="O" {{ old('blood_type') == 'O' ? 'selected' : '' }}>O</option>
                        </select>
                    </div>
                </div>

                <!-- Address -->
                <div class="mb-6">
                    <label for="address" class="block text-sm font-medium text-gray-700">Alamat</label>
                    <textarea name="address" id="address" rows="3"
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('address') }}</textarea>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('users.index', ['type' => $type]) }}" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                        Batal
                    </a>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-save mr-2"></i>
                        @if($type === 'employee')
                            Simpan Pegawai
                        @else
                            Simpan Siswa
                        @endif
                    </button>
                </div>
            </form>
        </div>
@endsection

@push('scripts')
<script>
    // Set user type based on URL parameter
    document.addEventListener('DOMContentLoaded', function() {
        const userTypeSelect = document.getElementById('user_type');
        const employeeFields = document.getElementById('employee-fields');
        const studentFields = document.getElementById('student-fields');
        
        // Set default value based on type parameter
        @if($type === 'employee')
            userTypeSelect.value = 'employee';
            employeeFields.style.display = 'grid';
            studentFields.style.display = 'none';
        @else
            userTypeSelect.value = 'student';
            employeeFields.style.display = 'none';
            studentFields.style.display = 'grid';
        @endif
        
        // Show/hide fields based on user type
        userTypeSelect.addEventListener('change', function() {
            const userType = this.value;
            
            if (userType === 'employee') {
                employeeFields.style.display = 'grid';
                studentFields.style.display = 'none';
            } else if (userType === 'student') {
                employeeFields.style.display = 'none';
                studentFields.style.display = 'grid';
            } else {
                employeeFields.style.display = 'none';
                studentFields.style.display = 'none';
            }
        });
    });
</script>
@endpush
