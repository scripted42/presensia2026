@extends('layouts.app')

@section('title', 'Edit User - Presensia')

@section('content')
    <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit User</h1>
                <p class="text-xs text-gray-500 mt-1">Perbarui data pengguna</p>
            </div>
            <a href="{{ route('users.index', ['type' => $user->user_type]) }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors text-sm font-medium">Kembali</a>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg">
        <form method="POST" action="{{ route('users.update', $user) }}" class="p-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nama Lengkap *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password (kosongkan jika tidak berubah)</label>
                    <input type="password" name="password" id="password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">No. Telepon</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="gender" class="block text-sm font-medium text-gray-700">Jenis Kelamin</label>
                    <select name="gender" id="gender" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Pilih Jenis Kelamin</option>
                        <option value="L" {{ old('gender', $user->gender) === 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('gender', $user->gender) === 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
                <div>
                    <label for="birth_date" class="block text-sm font-medium text-gray-700">Tanggal Lahir</label>
                    <input type="date" name="birth_date" id="birth_date" value="{{ old('birth_date', optional($user->birth_date)->format('Y-m-d')) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                @if($user->user_type === 'employee')
                    <div>
                        <label for="nik" class="block text-sm font-medium text-gray-700">NIK/NIP</label>
                        <input type="text" name="nik" id="nik" value="{{ old('nik', $user->nik) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="nuptk" class="block text-sm font-medium text-gray-700">NUPTK</label>
                        <input type="text" name="nuptk" id="nuptk" value="{{ old('nuptk', $user->employeeProfile->nuptk ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="ptk_type" class="block text-sm font-medium text-gray-700">Jenis PTK</label>
                        <input type="text" name="ptk_type" id="ptk_type" value="{{ old('ptk_type', $user->employeeProfile->ptk_type ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="employment_status" class="block text-sm font-medium text-gray-700">Status Kepegawaian</label>
                        <input type="text" name="employment_status" id="employment_status" value="{{ old('employment_status', $user->employeeProfile->employment_status ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="md:col-span-2">
                        <label for="address_line" class="block text-sm font-medium text-gray-700">Alamat (Dapodik)</label>
                        <input type="text" name="address_line" id="address_line" value="{{ old('address_line', $user->employeeProfile->address_line ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="npwp" class="block text-sm font-medium text-gray-700">NPWP</label>
                        <input type="text" name="npwp" id="npwp" value="{{ old('npwp', $user->employeeProfile->npwp ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="bank_name" class="block text-sm font-medium text-gray-700">Bank</label>
                        <input type="text" name="bank_name" id="bank_name" value="{{ old('bank_name', $user->employeeProfile->bank_name ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="bank_account" class="block text-sm font-medium text-gray-700">No. Rekening</label>
                        <input type="text" name="bank_account" id="bank_account" value="{{ old('bank_account', $user->employeeProfile->bank_account ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="certification_number" class="block text-sm font-medium text-gray-700">No. Sertifikasi</label>
                        <input type="text" name="certification_number" id="certification_number" value="{{ old('certification_number', $user->employeeProfile->certification_number ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="certification_year" class="block text-sm font-medium text-gray-700">Tahun Sertifikasi</label>
                        <input type="text" name="certification_year" id="certification_year" value="{{ old('certification_year', $user->employeeProfile->certification_year ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="main_subject" class="block text-sm font-medium text-gray-700">Mapel Utama</label>
                        <input type="text" name="main_subject" id="main_subject" value="{{ old('main_subject', $user->employeeProfile->main_subject ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="teaching_hours_per_week" class="block text-sm font-medium text-gray-700">Jam Mengajar/Minggu</label>
                        <input type="number" name="teaching_hours_per_week" id="teaching_hours_per_week" value="{{ old('teaching_hours_per_week', $user->employeeProfile->teaching_hours_per_week ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                @else
                    <div>
                        <label for="nis" class="block text-sm font-medium text-gray-700">NIS</label>
                        <input type="text" name="nis" id="nis" value="{{ old('nis', $user->nis) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="nisn" class="block text-sm font-medium text-gray-700">NISN</label>
                        <input type="text" name="nisn" id="nisn" value="{{ old('nisn', $user->nisn) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="father_name" class="block text-sm font-medium text-gray-700">Nama Ayah</label>
                        <input type="text" name="father_name" id="father_name" value="{{ old('father_name', $user->studentProfile->father_name ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="mother_name" class="block text-sm font-medium text-gray-700">Nama Ibu</label>
                        <input type="text" name="mother_name" id="mother_name" value="{{ old('mother_name', $user->studentProfile->mother_name ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="md:col-span-2">
                        <label for="address_line" class="block text-sm font-medium text-gray-700">Alamat (Dapodik)</label>
                        <input type="text" name="address_line" id="address_line" value="{{ old('address_line', $user->studentProfile->address_line ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="birth_certificate_number" class="block text-sm font-medium text-gray-700">No. Akta Lahir</label>
                        <input type="text" name="birth_certificate_number" id="birth_certificate_number" value="{{ old('birth_certificate_number', $user->studentProfile->birth_certificate_number ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="kk_number" class="block text-sm font-medium text-gray-700">No. KK</label>
                        <input type="text" name="kk_number" id="kk_number" value="{{ old('kk_number', $user->studentProfile->kk_number ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="kip_number" class="block text-sm font-medium text-gray-700">No. KIP</label>
                        <input type="text" name="kip_number" id="kip_number" value="{{ old('kip_number', $user->studentProfile->kip_number ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="citizenship" class="block text-sm font-medium text-gray-700">Kewarganegaraan</label>
                        <input type="text" name="citizenship" id="citizenship" value="{{ old('citizenship', $user->studentProfile->citizenship ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="residence_type" class="block text-sm font-medium text-gray-700">Jenis Tinggal</label>
                        <input type="text" name="residence_type" id="residence_type" value="{{ old('residence_type', $user->studentProfile->residence_type ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="sibling_count" class="block text-sm font-medium text-gray-700">Jumlah Saudara</label>
                        <input type="number" name="sibling_count" id="sibling_count" value="{{ old('sibling_count', $user->studentProfile->sibling_count ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="order_in_family" class="block text-sm font-medium text-gray-700">Anak ke-</label>
                        <input type="number" name="order_in_family" id="order_in_family" value="{{ old('order_in_family', $user->studentProfile->order_in_family ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="special_needs" class="block text-sm font-medium text-gray-700">Kebutuhan Khusus</label>
                        <input type="text" name="special_needs" id="special_needs" value="{{ old('special_needs', $user->studentProfile->special_needs ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="blood_type" class="block text-sm font-medium text-gray-700">Golongan Darah</label>
                        <input type="text" name="blood_type" id="blood_type" value="{{ old('blood_type', $user->studentProfile->blood_type ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                @endif
                <div class="md:col-span-2">
                    <label for="address" class="block text-sm font-medium text-gray-700">Alamat</label>
                    <textarea name="address" id="address" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('address', $user->address) }}</textarea>
                </div>
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700">Role *</label>
                    <select name="role" id="role" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="admin" {{ $user->hasRole('admin') ? 'selected' : '' }}>Admin</option>
                        <option value="teacher" {{ $user->hasRole('teacher') ? 'selected' : '' }}>Guru</option>
                        <option value="headmaster" {{ $user->hasRole('headmaster') ? 'selected' : '' }}>Kepala Sekolah</option>
                        <option value="tu" {{ $user->hasRole('tu') ? 'selected' : '' }}>Tata Usaha</option>
                        <option value="bk" {{ $user->hasRole('bk') ? 'selected' : '' }}>BK</option>
                        <option value="kesiswaan" {{ $user->hasRole('kesiswaan') ? 'selected' : '' }}>Kesiswaan</option>
                        <option value="student" {{ $user->hasRole('student') ? 'selected' : '' }}>Siswa</option>
                    </select>
                </div>
                <div>
                    <label for="is_active" class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="is_active" id="is_active" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="1" {{ $user->is_active ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ !$user->is_active ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('users.index', ['type' => $user->user_type]) }}" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition-colors">Batal</a>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">Simpan Perubahan</button>
            </div>
        </form>
    </div>
@endsection


