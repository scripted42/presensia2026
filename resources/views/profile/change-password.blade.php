@extends('layouts.app')

@section('title', 'Ganti Password - Presensia')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header Page -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Ganti Password</h1>
            <p class="text-sm text-gray-600 mt-1">Perbarui password akun Anda untuk menjaga keamanan data.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Dashboard
        </a>
    </div>

    <!-- Alert Success -->
    @if(session('success'))
        <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center shadow-sm">
            <i class="fas fa-check-circle text-emerald-500 text-lg mr-3"></i>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Alert Error -->
    @if($errors->any())
        <div class="mb-6 bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl shadow-sm">
            <div class="flex items-center mb-1">
                <i class="fas fa-exclamation-circle text-rose-500 text-lg mr-2"></i>
                <span class="text-sm font-semibold">Terdapat kesalahan pada formulir:</span>
            </div>
            <ul class="list-disc list-inside text-sm space-y-1 ml-6 text-rose-700">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-150 overflow-hidden">
        <!-- Account Info Summary -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-blue-100 p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center space-x-4">
                <div class="h-12 w-12 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-lg shadow-sm">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900">{{ $user->name }}</h2>
                    <div class="flex flex-wrap items-center gap-2 text-xs text-gray-600 mt-1">
                        @if($user->user_type === 'student')
                            <span class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full font-medium">Siswa</span>
                            <span>NIS: <strong>{{ $user->nis ?? '-' }}</strong></span>
                        @else
                            <span class="bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full font-medium">Pegawai</span>
                            <span>NIK: <strong>{{ $user->nik ?? '-' }}</strong></span>
                        @endif
                        <span class="text-gray-300">•</span>
                        <span>Role: <strong>{{ $user->roles->first()->display_name ?? $user->roles->first()->name ?? 'User' }}</strong></span>
                    </div>
                </div>
            </div>
            <div class="text-xs text-gray-500 bg-white/70 backdrop-blur px-3 py-1.5 rounded-lg border border-blue-200/50 self-start sm:self-center">
                <i class="fas fa-shield-alt text-blue-600 mr-1"></i> Proteksi Akun
            </div>
        </div>

        <!-- Form Ubah Password -->
        <form method="POST" action="{{ route('profile.password.update') }}" class="p-6 sm:p-8 space-y-6">
            @csrf
            @method('PUT')

            <!-- Password Saat Ini -->
            <div>
                <label for="current_password" class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-1.5">
                    Password Saat Ini <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input 
                        type="password" 
                        id="current_password" 
                        name="current_password" 
                        class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition @error('current_password') border-red-500 @enderror"
                        placeholder="Masukkan password yang digunakan saat ini"
                        required
                    >
                    <button type="button" onclick="togglePasswordVisibility('current_password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                @error('current_password')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500 mt-1">Jika belum pernah mengubah password, password saat ini adalah NIS atau NIK Anda.</p>
            </div>

            <hr class="border-gray-100">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Password Baru -->
                <div>
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-1.5">
                        Password Baru <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition @error('password') border-red-500 @enderror"
                            placeholder="Minimal 6 karakter"
                            required
                        >
                        <button type="button" onclick="togglePasswordVisibility('password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Konfirmasi Password Baru -->
                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-1.5">
                        Konfirmasi Password Baru <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="password_confirmation" 
                            name="password_confirmation" 
                            class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition"
                            placeholder="Ulangi password baru"
                            required
                        >
                        <button type="button" onclick="togglePasswordVisibility('password_confirmation', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="pt-4 flex items-center justify-end space-x-3 border-t border-gray-100">
                <a href="{{ route('dashboard') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-sm transition flex items-center">
                    <i class="fas fa-key mr-2"></i> Simpan Password Baru
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function togglePasswordVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
@endsection
