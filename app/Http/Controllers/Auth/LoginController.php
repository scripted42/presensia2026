<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        // Ensure captcha shows after threshold even on fresh GET
        $ipAddress = (string) $request->ip();
        $emailLower = strtolower((string) ($request->old('email') ?? $request->session()->get('login_email_lower', '')));
        if ($emailLower !== '') {
            $attemptKey = 'login:attempts:' . sha1($ipAddress . '|' . $emailLower);
            $attempts = (int) Cache::get($attemptKey, 0);
            if ($attempts >= 3) {
                if (!$request->session()->has('captcha_question')) {
                    $this->generateCaptchaQuestion($request);
                } else {
                    $request->session()->put('captcha_required', true);
                }
            }
        }

        return view('auth.login');
    }

    /**
     * Handle a login request.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'captcha' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->except('password'));
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        $ipAddress = (string) $request->ip();
        $emailLower = strtolower((string) $request->input('email'));
        $attemptKey = 'login:attempts:' . sha1($ipAddress . '|' . $emailLower);
        $banKey = 'login:ban:' . sha1($ipAddress . '|' . $emailLower);
        $attempts = (int) Cache::get($attemptKey, 0);

        // Check temporary ban (30 minutes)
        if (Cache::has($banKey)) {
            return redirect()->back()
                ->withErrors(['email' => 'Terlalu banyak percobaan login. Coba lagi dalam beberapa saat.'])
                ->withInput($request->except('password'))
                ->with('captcha_required', true)
                ->with('captcha_question', session('captcha_question'));
        }

        // Enforce CAPTCHA after 3 failed attempts
        if ($attempts >= 3) {
            $expected = session('captcha_answer');
            $captchaInput = trim((string) $request->input('captcha'));
            if ($expected === null || $captchaInput === '' || (string) $expected !== $captchaInput) {
                // ensure captcha question exists
                $this->generateCaptchaQuestion($request);
                return redirect()->back()
                    ->withErrors(['captcha' => 'Captcha salah atau belum diisi.'])
                    ->withInput($request->except('password'))
                    ->with('captcha_required', true)
                    ->with('captcha_question', session('captcha_question'));
            }
        }

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // Reset attempts and captcha on success
            Cache::forget($attemptKey);
            Cache::forget($banKey);
            $request->session()->forget(['captcha_required', 'captcha_question', 'captcha_answer']);
            
            // Check if user is active
            if (!Auth::user()->is_active) {
                Auth::logout();
                return redirect()->back()
                    ->withErrors(['email' => 'Akun Anda tidak aktif. Silakan hubungi administrator.'])
                    ->withInput($request->except('password'));
            }

            // Redirect super admin ke dashboard SaaS
            $superEmail = config('app.super_admin_email', env('APP_SUPER_ADMIN_EMAIL', 'superadmin@presensia.com'));
            if (strtolower(Auth::user()->email) === strtolower((string) $superEmail)) {
                return redirect()->intended(route('super-admin.index'));
            }

            // Redirect to loading screen first, then dashboard
            return redirect()->route('loading');
        }

        // On failed login: increment attempts
        $attempts++;
        // attempts window: 30 minutes
        Cache::put($attemptKey, $attempts, now()->addMinutes(30));

        // Prepare captcha after threshold
        if ($attempts >= 3) {
            $this->generateCaptchaQuestion($request);
        }

        // Temporary ban after 10 failed attempts within window
        if ($attempts >= 10) {
            Cache::put($banKey, true, now()->addMinutes(30));
        }

        // Remember last email to evaluate attempts on GET
        $request->session()->put('login_email_lower', $emailLower);

        return redirect()->back()
            ->withErrors(['email' => 'Email atau password salah.'])
            ->withInput($request->except('password'))
            ->with('captcha_required', $attempts >= 3)
            ->with('captcha_question', session('captcha_question'));
    }

    /**
     * Show loading screen after successful login.
     */
    public function showLoading()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        return view('auth.loading');
    }

    /**
     * Handle a logout request.
     */
    public function logout(Request $request)
    {
        // Debug: Log before logout
        \Log::info('Logout attempt for user: ' . auth()->user()->email ?? 'No user');
        
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        // Debug: Log after logout
        \Log::info('Logout completed, redirecting to login');
        
        return redirect()->route('login');
    }

    private function generateCaptchaQuestion(Request $request): void
    {
        // Simple math captcha (1-9) + (1-9)
        $a = random_int(1, 9);
        $b = random_int(1, 9);
        $request->session()->put('captcha_question', "Berapa hasil $a + $b ?");
        $request->session()->put('captcha_answer', (string) ($a + $b));
        $request->session()->put('captcha_required', true);
    }
}
