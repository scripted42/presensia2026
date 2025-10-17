<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Email super admin dapat dikonfigurasi via env APP_SUPER_ADMIN_EMAIL
        $superAdminEmail = config('app.super_admin_email', env('APP_SUPER_ADMIN_EMAIL', 'superadmin@presensia.com'));

        if (!$user || strtolower($user->email) !== strtolower((string) $superAdminEmail)) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}





