<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Presensia</title>
    <!-- Resource Hints: speed up cold start/hard refresh -->
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
    <!-- Inter font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Inter', system-ui, -apple-system, sans-serif; 
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
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3b82f6',
                        accent: '#4f46e5',
                        background: '#f8fafc',
                        foreground: '#0f172a',
                        muted: '#64748b',
                        'muted-foreground': '#64748b',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-[#f8fafc]">
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
                style="background-image: url('{{ asset('assets/images/banner/background.jpg') }}');"
            ></div>
            <div class="absolute inset-0 bg-gradient-to-br from-slate-900/90 to-slate-800/90"></div>
            
            <!-- Content Container - Centered with Left Alignment -->
            <div class="relative z-10 flex flex-col justify-center items-start p-16 w-full max-w-xl mx-auto">
                <!-- Logo -->
                <img 
                    src="{{ asset('assets/images/logo/presensia-logo.png') }}" 
                    alt="Presensia Logo" 
                    class="h-12 w-auto mb-10 brightness-0 invert"
                />

                <!-- Text Content -->
                <div class="space-y-6">
                    <h1 class="text-4xl font-extrabold text-white leading-tight tracking-tight text-left">
                        Transform Your School Management
                    </h1>
                    <p class="text-lg text-slate-300 leading-relaxed text-left">
                        Smart attendance management made simple. Track, analyze, and optimize your school's performance with precision.
                    </p>
                </div>

                <!-- Security Badge -->
                <div class="flex items-center space-x-3 text-slate-400 text-sm mt-12 bg-white/5 px-4 py-2.5 rounded-full border border-white/10">
                    <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clipRule="evenodd" />
                    </svg>
                    <span>Your data is secure and encrypted</span>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="flex-1 flex items-center justify-center p-6 bg-gradient-to-br from-slate-50 to-slate-100">
            <div class="w-full max-w-md space-y-8 animate-entrance">
                <div class="bg-white rounded-2xl border border-slate-150 p-10 shadow-xl shadow-slate-200/50">
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
                        <h2 class="text-2xl font-bold text-slate-800">
                            Sign in to your account
                        </h2>
                        <p class="text-sm text-slate-500">
                            Enter your credentials to access your dashboard
                        </p>
                    </div>

                    <!-- Login Form -->
                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf
                        <div>
                            <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Email Address</label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                value="{{ old('email') }}"
                                class="w-full px-4 py-3 text-sm border border-slate-200 rounded-xl shadow-sm placeholder-slate-400 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all @error('email') border-red-500 @enderror"
                                placeholder="name@school.sch.id"
                                required 
                                autofocus
                            >
                            @error('email')
                                <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Password</label>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="w-full px-4 py-3 text-sm border border-slate-200 rounded-xl shadow-sm placeholder-slate-400 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all @error('password') border-red-500 @enderror"
                                placeholder="••••••••"
                                required
                            >
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
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500/20 border-slate-300 rounded"
                                >
                                <label for="remember" class="ml-2 block text-sm text-slate-700">
                                    Remember me
                                </label>
                            </div>
                            <a href="#" class="text-sm font-medium text-blue-600 hover:text-blue-700 transition-colors">
                                Forgot password?
                            </a>
                        </div>

                        <button 
                            type="submit" 
                            class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-md text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-4 focus:ring-blue-500/20 active:scale-[0.98] transition-all duration-150"
                        >
                            Sign in
                        </button>
                    </form>

                    <div class="text-center text-sm text-slate-500 mt-8">
                        Don't have an account? 
                        <a href="#" class="font-semibold text-blue-600 hover:text-blue-700 transition-colors">
                            Contact admin
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Simple Loading Animation
        document.addEventListener('DOMContentLoaded', function() {
            const bannerLoading = document.getElementById('bannerLoading');
            const bannerBackground = document.getElementById('bannerBackground');
            
            // Preload gambar background
            const img = new Image();
            
            // Event ketika gambar berhasil di-load
            img.onload = function() {
                // Fade in background
                bannerBackground.classList.add('loaded');
                
                // Hide loading quickly
                setTimeout(function() {
                    bannerLoading.classList.add('hidden');
                }, 200);
            };
            
            // Event jika gambar gagal load
            img.onerror = function() {
                // Show fallback gradient
                bannerLoading.classList.add('hidden');
                bannerBackground.style.background = 'linear-gradient(135deg, #f8fafc 0%, #0f2a5f 100%)';
            };
            
            // Start preload
            img.src = '{{ asset('assets/images/banner/background.jpg') }}';
            
            // Timeout fallback
            setTimeout(function() {
                if (!bannerBackground.classList.contains('loaded')) {
                    bannerLoading.classList.add('hidden');
                    bannerBackground.style.background = 'linear-gradient(135deg, #f8fafc 0%, #0f2a5f 100%)';
                }
            }, 5000); // 5 detik timeout
        });
        
        // Refresh CSRF token on page load
        document.addEventListener('DOMContentLoaded', function() {
            fetch('/login', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(response => {
                if (response.ok) {
                    console.log('CSRF token refreshed');
                }
            }).catch(error => {
                console.log('CSRF token refresh failed:', error);
            });
        });
    </script>
</body>
</html>