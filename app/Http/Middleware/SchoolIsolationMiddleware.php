<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SchoolIsolationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        // Skip isolation for super admin (no school_id)
        if (!$user->school_id) {
            return $next($request);
        }
        
        // For regular users, ensure they can only access their school's data
        $schoolId = $user->school_id;
        
        // Add school_id to request for automatic filtering
        $request->merge(['school_id' => $schoolId]);
        
        return $next($request);
    }
}