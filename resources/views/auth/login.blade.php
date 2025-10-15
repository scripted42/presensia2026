<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Presensia</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 flex items-center justify-center relative overflow-hidden">
    <!-- Background ornaments -->
    <div class="absolute -top-24 -left-24 w-80 h-80 bg-blue-200/40 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-28 -right-28 w-96 h-96 bg-purple-200/40 rounded-full blur-3xl"></div>

    <div class="w-full mx-4 max-w-5xl">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
            <!-- Illustration / Brand -->
            <div class="hidden md:block">
                <div class="bg-white/50 border border-white/60 rounded-2xl shadow-xl p-8 backdrop-blur-sm">
                    <img src="{{ asset('assets/images/banner/siswa.png') }}" alt="Ilustrasi" class="w-full h-auto" />
                    <div class="mt-6">
                        <img src="{{ asset('assets/images/logo/presensia-logo.png') }}" alt="Presensia" class="h-10 w-auto" />
                        <p class="text-gray-600 mt-2">Sistem Manajemen Absensi Sekolah yang ringan, cepat, dan aman.</p>
                    </div>
                </div>
            </div>

            <!-- Login Card -->
            <div class="bg-white/80 border border-white/60 rounded-2xl shadow-2xl p-8 backdrop-blur-sm">
                <!-- Logo/Header (mobile) -->
                <div class="text-center mb-6 md:hidden">
                    <img src="{{ asset('assets/images/logo/presensia-logo.png') }}" alt="Presensia" class="mx-auto h-20 w-auto mb-2" />
                    <p class="text-gray-600 text-sm">Sistem Manajemen Absensi Sekolah</p>
                </div>

                <!-- Login Form -->
                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf
                
                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><i class="fas fa-envelope"></i></span>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                               class="w-full pl-10 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 @error('email') border-red-500 @enderror"
                               placeholder="nama@sekolah.sch.id" required autofocus>
                    </div>
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><i class="fas fa-lock"></i></span>
                        <input type="password" id="password" name="password"
                               class="w-full pl-10 pr-10 py-3 border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 @error('password') border-red-500 @enderror"
                               placeholder="••••••••" required>
                        <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"><i class="fas fa-eye"></i></button>
                    </div>
                    @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center">
                        <input type="checkbox" 
                               id="remember" 
                               name="remember" 
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="remember" class="ml-2 block text-sm text-gray-700">
                            Ingat saya
                        </label>
                    </div>
                    <a href="#" class="text-blue-600 hover:text-blue-700">Lupa password?</a>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-3 px-4 rounded-xl hover:shadow-lg focus:ring-4 focus:ring-blue-200 transition duration-200 font-medium">
                    <i class="fas fa-sign-in-alt mr-2"></i>Masuk
                </button>
                </form>

            <!-- Demo Credentials -->
            <div class="mt-8 p-4 bg-gray-50/70 border border-gray-100 rounded-xl">
                <h3 class="text-sm font-medium text-gray-700 mb-2">Demo Credentials:</h3>
                <div class="text-xs text-gray-600 space-y-1">
                    <p><strong>Admin:</strong> admin@presensia.com / password</p>
                    <p><strong>Guru:</strong> guru@presensia.com / password</p>
                    <p><strong>TU:</strong> tu@presensia.com / password</p>
                    <p><strong>BK:</strong> bk@presensia.com / password</p>
                    <p><strong>Kesiswaan:</strong> kesiswaan@presensia.com / password</p>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-8 text-sm text-gray-500">
                <p>&copy; 2024 Presensia. All rights reserved.</p>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Auto-focus on email field
        document.getElementById('email').focus();
        
        // Show/hide password
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
        }
    </script>
</body>
</html>


