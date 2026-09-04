<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Presensia</title>
    <!-- Resource Hints -->
    <link rel="dns-prefetch" href="//cdn.tailwindcss.com">
    <link rel="preconnect" href="https://cdn.tailwindcss.com" crossorigin>
    <link rel="dns-prefetch" href="//cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/fontawesome.css') }}">

    <!-- Google Fonts: Baloo 2 (heading), Manrope (body), Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;600;700;800&family=Inter:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }

        body { 
            font-family: 'Manrope', 'Inter', system-ui, -apple-system, sans-serif; 
        }
        
        /* Entrance animation for form */
        @keyframes formEntrance {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-entrance {
            animation: formEntrance 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .banner-container {
            position: relative;
            overflow: hidden;
        }
        
        .banner-loading {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: transparent;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding-top: 60px;
            z-index: 1;
            transition: opacity 0.3s ease-out;
        }
        
        .banner-loading.hidden {
            opacity: 0;
            pointer-events: none;
        }
        
        /* Simple Loading Dot */
        .loading-dot {
            width: 8px;
            height: 8px;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 50%;
            animation: bounce 1.4s ease-in-out infinite both;
        }
        
        .loading-dot:nth-child(1) { animation-delay: -0.32s; }
        .loading-dot:nth-child(2) { animation-delay: -0.16s; }
        .loading-dot:nth-child(3) { animation-delay: 0s; }
        
        @keyframes bounce {
            0%, 80%, 100% { 
                transform: scale(0);
            } 
            40% { 
                transform: scale(1);
            }
        }
        
        /* Background Image dengan Fade In */
        .banner-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
            filter: blur(1px);
        }
        
        .banner-background.loaded {
            opacity: 1;
        }

        @media (prefers-reduced-motion: reduce) {
            .animate-entrance {
                animation: none !important;
            }
        }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3b82f6',
                        accent: '#4f46e5',
                        teal: '#1E9C8D',
                        coral: '#FF6F59',
                        sun: '#FFC857',
                        background: '#f8fafc',
                        foreground: '#0f172a',
                        muted: '#64748b',
                        'muted-foreground': '#64748b',
                    },
                    fontFamily: {
                        display: ['"Baloo 2"', 'sans-serif'],
                        sans: ['Manrope', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-[#f8fafc]" x-data="loginTransition()">

    <!-- Reusable Auth Transition Component -->
    <x-auth-transition />

    <div class="min-h-screen flex">
        <!-- Left Side - Branding -->
        <div class="hidden lg:flex lg:w-1/2 banner-container">
            <!-- Simple Loading Animation -->
            <div class="banner-loading" id="bannerLoading">
                <div class="flex space-x-2">
                    <div class="loading-dot"></div>
                    <div class="loading-dot"></div>
                    <div class="loading-dot"></div>
                </div>
            </div>
            
            <!-- Background Image dengan Animation -->
            <div 
                class="banner-background"
                id="bannerBackground"
                style="background-image: url('{{ asset('assets/images/banner/school-banner.jpg') }}');"
            ></div>
            
            <!-- Content Overlay -->
            <div class="relative z-10 flex flex-col justify-between w-full h-full p-12 text-white bg-gradient-to-t from-slate-950/90 via-slate-900/40 to-slate-900/20">
                <div></div>
                
                <div class="space-y-4 max-w-md">
                    <h1 class="text-3xl font-extrabold tracking-tight leading-tight">
                        Sistem Absensi Digital Terpadu
                    </h1>
                    <p class="text-slate-300 text-sm leading-relaxed">
                        Kelola kehadiran pegawai dan siswa dengan mudah, cepat, dan akurat menggunakan teknologi GPS dan QR Code.
                    </p>
                </div>
                
                <div class="flex items-center justify-between text-xs text-slate-400 border-t border-white/10 pt-6">
                    <p>&copy; 2026 Presensia. All rights reserved.</p>
                    <div class="flex space-x-4">
                        <a href="#" class="hover:text-white transition-colors">Privacy Policy</a>
                        <a href="#" class="hover:text-white transition-colors">Terms of Service</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-12">
            <div class="w-full max-w-md animate-entrance">
                <div 
                    class="bg-white p-8 sm:p-10 rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 transition-all duration-300 ease-out"
                    :class="{ 'opacity-0 -translate-y-2 pointer-events-none': state === 'loading' || state === 'leaving' }"
                >
                    <!-- Mobile Logo -->
                    <div class="lg:hidden flex justify-center mb-6">
                        <img 
                            src="{{ asset('assets/images/logo/presensia-logo.png') }}" 
                            alt="Presensia Logo" 
                            class="h-8 w-auto"
                        />
                    </div>

                    <!-- Desktop Logo -->
                    <div class="hidden lg:block mb-6">
                        <img 
                            src="{{ asset('assets/images/logo/presensia-logo.png') }}" 
                            alt="Presensia Logo" 
                            class="h-8 w-auto"
                        />
                    </div>

                    <div class="space-y-2 mb-8">
                        <h2 class="font-display text-2xl font-bold text-slate-800">
                            Sign in to your account
                        </h2>
                        <p class="text-sm text-slate-500 font-sans">
                            Enter your credentials to access your dashboard
                        </p>
                    </div>

                    <!-- Inline Error Notification (AJAX or Session) -->
                    <div 
                        x-show="errorMessage" 
                        x-cloak 
                        class="p-3.5 mb-5 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs font-medium flex items-start gap-2.5 transition-all"
                    >
                        <svg class="w-4 h-4 text-red-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span x-text="errorMessage"></span>
                    </div>

                    @if(session('error'))
                    <div class="p-3.5 mb-5 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs font-medium flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-red-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                    @endif

                    <!-- Login Form -->
                    <form @submit.prevent="submitLogin" method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf
                        <div>
                            <label for="login" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">NIS / NIK</label>
                            <input 
                                type="text" 
                                id="login" 
                                name="login" 
                                x-model="login"
                                value="{{ old('login', old('email')) }}"
                                class="w-full px-4 py-3 text-sm border border-slate-200 rounded-xl shadow-sm placeholder-slate-400 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all @if($errors->has('login') || $errors->has('email')) border-red-500 @endif"
                                placeholder="NIS siswa, NIK pegawai, atau Email"
                                required 
                                autofocus
                            >
                            @if($errors->has('login'))
                                <p class="text-red-500 text-xs mt-1.5">{{ $errors->first('login') }}</p>
                            @elseif($errors->has('email'))
                                <p class="text-red-500 text-xs mt-1.5">{{ $errors->first('email') }}</p>
                            @endif
                        </div>

                        <div>
                            <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Password</label>
                            <div class="relative">
                                <input 
                                    type="password" 
                                    id="password" 
                                    name="password" 
                                    x-model="password"
                                    class="w-full pl-4 pr-11 py-3 text-sm border border-slate-200 rounded-xl shadow-sm placeholder-slate-400 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all @error('password') border-red-500 @enderror"
                                    placeholder="••••••••"
                                    required
                                >
                                <button 
                                    type="button" 
                                    id="togglePassword" 
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer transition-colors"
                                    title="Tampilkan / Sembunyikan Password"
                                    aria-label="Tampilkan atau sembunyikan password"
                                >
                                    <svg id="eyeIcon" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg id="eyeOffIcon" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>

                        @if(session('captcha_required'))
                        <div>
                            <label for="captcha" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">
                                {{ session('captcha_question', 'Captcha') }}
                            </label>
                            <input 
                                type="text" 
                                id="captcha" 
                                name="captcha" 
                                x-model="captcha"
                                class="w-full px-4 py-3 text-sm border border-slate-200 rounded-xl shadow-sm placeholder-slate-400 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all @error('captcha') border-red-500 @enderror"
                                placeholder="Jawaban"
                                required
                            >
                            @error('captcha')
                                <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>
                        @endif

                        <div class="flex items-center justify-between pt-1">
                            <div class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    id="remember" 
                                    name="remember" 
                                    x-model="remember"
                                    class="h-4 w-4 text-teal focus:ring-teal/20 border-slate-300 rounded"
                                >
                                <label for="remember" class="ml-2 block text-sm text-slate-700">
                                    Remember me
                                </label>
                            </div>
                            <a href="#" class="text-sm font-medium text-teal hover:text-teal/80 transition-colors">
                                Forgot password?
                            </a>
                        </div>

                        <button 
                            type="submit" 
                            :disabled="state !== 'idle'"
                            class="w-full flex items-center justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-md text-sm font-semibold text-white bg-gradient-to-r from-teal to-blue-600 hover:from-teal/90 hover:to-blue-700 focus:outline-none focus:ring-4 focus:ring-teal/20 active:scale-[0.98] disabled:opacity-75 disabled:cursor-not-allowed transition-all duration-150"
                        >
                            <span x-show="state === 'idle'">Sign in</span>
                            <span x-show="state !== 'idle'" x-cloak class="inline-flex items-center gap-2">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Memproses…
                            </span>
                        </button>
                    </form>

                    <div class="text-center text-sm text-slate-500 mt-8">
                        Don't have an account? 
                        <a href="#" class="font-semibold text-teal hover:underline transition-colors">
                            Contact admin
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Alpine.js Component for Login Transition State Machine
        function loginTransition() {
            return {
                login: '{{ old("login", old("email")) }}',
                password: '',
                remember: false,
                captcha: '',
                errorMessage: '',
                state: 'idle', // 'idle' | 'loading' | 'leaving' | 'done'
                progress: 0,
                statusText: 'Memeriksa NIS-mu…',
                textFading: false,
                redirectUrl: '',
                animTimeouts: [],

                clearAnimTimeouts() {
                    this.animTimeouts.forEach(t => clearTimeout(t));
                    this.animTimeouts = [];
                },

                async submitLogin() {
                    if (this.state !== 'idle') return;
                    this.errorMessage = '';
                    this.clearAnimTimeouts();

                    // Start transition sequence immediately
                    this.state = 'loading';
                    this.progress = 0.12;
                    this.statusText = 'Memeriksa NIS-mu…';

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                        || document.querySelector('input[name="_token"]')?.value;

                    // Sequence timeline (~0-800ms: Memeriksa NIS-mu…, ~800-1800ms: Menyiapkan jadwal kelas…, ~1800-2800ms: Hampir siap…)
                    this.animTimeouts.push(setTimeout(() => {
                        this.updateStatus('Menyiapkan jadwal kelas…', 0.55);
                    }, 850));

                    this.animTimeouts.push(setTimeout(() => {
                        this.updateStatus('Hampir siap…', 1.0);
                    }, 1850));

                    const animationReady = new Promise(resolve => {
                        this.animTimeouts.push(setTimeout(resolve, 2900));
                    });

                    try {
                        const response = await fetch('{{ route("login") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                login: this.login,
                                password: this.password,
                                remember: this.remember,
                                captcha: this.captcha
                            })
                        });

                        const data = await response.json().catch(() => ({}));

                        if (!response.ok || !data.success) {
                            // On failure: cancel animation, restore form with error alert
                            this.clearAnimTimeouts();
                            this.state = 'idle';
                            this.errorMessage = data.message || 'NIS, NIK, atau password salah.';
                            return;
                        }

                        this.redirectUrl = data.redirect || '{{ route("dashboard") }}';

                        // Wait until the sequence is complete (~2.8s)
                        await animationReady;

                        // Navigate directly to dashboard while the loading screen remains active
                        // (Prevents any momentary glimpse of the login form)
                        window.location.href = this.redirectUrl;

                    } catch (err) {
                        this.clearAnimTimeouts();
                        this.state = 'idle';
                        this.errorMessage = 'Terjadi gangguan jaringan. Silakan coba lagi.';
                    }
                },

                updateStatus(newText, newProgress) {
                    this.textFading = true;
                    setTimeout(() => {
                        this.statusText = newText;
                        this.progress = newProgress;
                        this.textFading = false;
                    }, 150);
                }
            };
        }

        // Simple Loading Animation for Background
        document.addEventListener('DOMContentLoaded', function() {
            const bannerLoading = document.getElementById('bannerLoading');
            const bannerBackground = document.getElementById('bannerBackground');
            
            // Preload gambar background
            const img = new Image();
            
            img.onload = function() {
                bannerBackground.classList.add('loaded');
                setTimeout(function() {
                    bannerLoading.classList.add('hidden');
                }, 200);
            };
            
            img.onerror = function() {
                bannerLoading.classList.add('hidden');
                bannerBackground.style.background = 'linear-gradient(135deg, #f8fafc 0%, #0f2a5f 100%)';
            };
            
            img.src = '{{ asset('assets/images/banner/school-banner.jpg') }}';
            
            setTimeout(function() {
                if (!bannerBackground.classList.contains('loaded')) {
                    bannerLoading.classList.add('hidden');
                    bannerBackground.style.background = 'linear-gradient(135deg, #f8fafc 0%, #0f2a5f 100%)';
                }
            }, 5000);

            // Toggle password visibility
            const togglePasswordBtn = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            const eyeOffIcon = document.getElementById('eyeOffIcon');

            if (togglePasswordBtn && passwordInput && eyeIcon && eyeOffIcon) {
                togglePasswordBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const isPassword = passwordInput.getAttribute('type') === 'password';
                    passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                    eyeIcon.classList.toggle('hidden', isPassword);
                    eyeOffIcon.classList.toggle('hidden', !isPassword);
                });
            }
        });
    </script>
</body>
</html>