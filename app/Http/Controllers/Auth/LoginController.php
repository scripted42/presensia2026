<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

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
        $identityLower = strtolower(trim((string) ($request->old('login') ?? $request->old('email') ?? $request->session()->get('login_identity_lower', ''))));
        if ($identityLower !== '') {
            $attemptKey = 'login:attempts:' . sha1($ipAddress . '|' . $identityLower);
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
        $loginInput = trim((string) ($request->input('login') ?? $request->input('email', '')));
        $request->merge(['login' => $loginInput]);

        $validator = Validator::make($request->all(), [
            'login' => 'required|string',
            'password' => 'required|string|min:6',
            'captcha' => 'nullable|string'
        ], [
            'login.required' => 'NIS, NIK, atau Email wajib diisi.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->except('password'));
        }

        $remember = $request->boolean('remember');

        $ipAddress = (string) $request->ip();
        $identityLower = strtolower($loginInput);
        $attemptKey = 'login:attempts:' . sha1($ipAddress . '|' . $identityLower);
        $banKey = 'login:ban:' . sha1($ipAddress . '|' . $identityLower);
        $attempts = (int) Cache::get($attemptKey, 0);

        // Check temporary ban (30 minutes)
        if (Cache::has($banKey)) {
            return redirect()->back()
                ->withErrors(['login' => 'Terlalu banyak percobaan login. Coba lagi dalam beberapa saat.'])
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

        // Find user by NIS, NIK, or Email
        $user = User::where(function ($query) use ($loginInput) {
            $query->where('nis', $loginInput)
                  ->orWhere('nik', $loginInput)
                  ->orWhere('email', $loginInput);
        })->first();

        if ($user && Hash::check($request->password, $user->password)) {
            // Check if user is active
            if (!$user->is_active) {
                return redirect()->back()
                    ->withErrors(['login' => 'Akun Anda tidak aktif. Silakan hubungi administrator.'])
                    ->withInput($request->except('password'));
            }

            Auth::login($user, $remember);
            $request->session()->regenerate();

            // Reset attempts and captcha on success
            Cache::forget($attemptKey);
            Cache::forget($banKey);
            $request->session()->forget(['captcha_required', 'captcha_question', 'captcha_answer', 'login_identity_lower']);

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

        // Remember last identity to evaluate attempts on GET
        $request->session()->put('login_identity_lower', $identityLower);

        return redirect()->back()
            ->withErrors(['login' => 'NIS, NIK, atau password salah.'])
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
