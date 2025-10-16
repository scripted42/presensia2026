<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\BannedIp;
use App\Models\SecurityLog;
use App\Models\AuditTrail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SecurityMonitoringMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();
        $macAddress = $this->getMacAddress($request);
        $schoolId = $this->getSchoolId($request);

        // Check if IP is banned
        if (BannedIp::isIpBanned($ipAddress, $schoolId)) {
            $this->logSecurityEvent($request, 'blocked_ip', 'high', 'Access blocked - IP address is banned');
            return response()->json(['error' => 'Access denied'], 403);
        }

        // Check if MAC address is banned
        if ($macAddress && BannedIp::isMacBanned($macAddress, $schoolId)) {
            $this->logSecurityEvent($request, 'blocked_mac', 'high', 'Access blocked - MAC address is banned');
            return response()->json(['error' => 'Access denied'], 403);
        }

        // Detect potential attacks
        $this->detectAttacks($request);

        // Log audit trail
        $this->logAuditTrail($request);

        $response = $next($request);

        // Log response if it's an error
        if ($response->getStatusCode() >= 400) {
            $this->logSecurityEvent($request, 'http_error', 'medium', 'HTTP Error: ' . $response->getStatusCode());
        }

        return $response;
    }

    /**
     * Detect potential security attacks
     */
    private function detectAttacks(Request $request)
    {
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();
        $schoolId = $this->getSchoolId($request);

        // Detect brute force attacks
        $this->detectBruteForce($request, $ipAddress, $schoolId);

        // Detect DDoS attacks
        $this->detectDDoS($request, $ipAddress, $schoolId);

        // Detect SQL injection attempts
        $this->detectSQLInjection($request, $ipAddress, $schoolId);

        // Detect XSS attempts
        $this->detectXSS($request, $ipAddress, $schoolId);

        // Detect directory traversal
        $this->detectDirectoryTraversal($request, $ipAddress, $schoolId);

        // Detect suspicious user agents
        $this->detectSuspiciousUserAgent($request, $userAgent, $ipAddress, $schoolId);
    }

    /**
     * Detect brute force attacks
     */
    private function detectBruteForce(Request $request, $ipAddress, $schoolId)
    {
        $loginAttempts = SecurityLog::byIp($ipAddress)
            ->byAttackType('brute_force')
            ->recent(1) // Last 1 hour
            ->count();

        if ($loginAttempts >= 5) {
            $this->logSecurityEvent($request, 'brute_force', 'high', 'Multiple failed login attempts detected');
        }
    }

    /**
     * Detect DDoS attacks
     */
    private function detectDDoS(Request $request, $ipAddress, $schoolId)
    {
        $requestCount = SecurityLog::byIp($ipAddress)
            ->recent(1) // Last 1 hour
            ->count();

        if ($requestCount >= 100) {
            $this->logSecurityEvent($request, 'ddos', 'critical', 'High request volume detected - potential DDoS attack');
        }
    }

    /**
     * Detect SQL injection attempts
     */
    private function detectSQLInjection(Request $request, $ipAddress, $schoolId)
    {
        $sqlPatterns = [
            'union select', 'drop table', 'delete from', 'insert into',
            'update set', 'or 1=1', 'and 1=1', 'exec(', 'execute(',
            'script>', 'javascript:', 'onload=', 'onerror='
        ];

        $allInput = $request->all();
        $inputString = json_encode($allInput);

        foreach ($sqlPatterns as $pattern) {
            if (stripos($inputString, $pattern) !== false) {
                $this->logSecurityEvent($request, 'sql_injection', 'critical', 'SQL injection attempt detected: ' . $pattern);
                break;
            }
        }
    }

    /**
     * Detect XSS attempts
     */
    private function detectXSS(Request $request, $ipAddress, $schoolId)
    {
        $xssPatterns = [
            '<script', 'javascript:', 'onload=', 'onerror=', 'onclick=',
            'onmouseover=', 'onfocus=', 'onblur=', 'onchange=',
            'document.cookie', 'document.write', 'window.location'
        ];

        $allInput = $request->all();
        $inputString = json_encode($allInput);

        foreach ($xssPatterns as $pattern) {
            if (stripos($inputString, $pattern) !== false) {
                $this->logSecurityEvent($request, 'xss', 'high', 'XSS attempt detected: ' . $pattern);
                break;
            }
        }
    }

    /**
     * Detect directory traversal attempts
     */
    private function detectDirectoryTraversal(Request $request, $ipAddress, $schoolId)
    {
        $traversalPatterns = [
            '../', '..\\', '..%2f', '..%5c', '%2e%2e%2f',
            '%2e%2e%5c', '..%252f', '..%255c'
        ];

        $url = $request->fullUrl();
        $allInput = $request->all();
        $inputString = json_encode($allInput);

        foreach ($traversalPatterns as $pattern) {
            if (stripos($url, $pattern) !== false || stripos($inputString, $pattern) !== false) {
                $this->logSecurityEvent($request, 'directory_traversal', 'high', 'Directory traversal attempt detected: ' . $pattern);
                break;
            }
        }
    }

    /**
     * Detect suspicious user agents
     */
    private function detectSuspiciousUserAgent(Request $request, $userAgent, $ipAddress, $schoolId)
    {
        $suspiciousPatterns = [
            'sqlmap', 'nikto', 'nmap', 'masscan', 'zap',
            'burp', 'w3af', 'acunetix', 'nessus', 'openvas',
            'curl', 'wget', 'python-requests', 'bot', 'crawler'
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (stripos($userAgent, $pattern) !== false) {
                $this->logSecurityEvent($request, 'suspicious_activity', 'medium', 'Suspicious user agent detected: ' . $pattern);
                break;
            }
        }
    }

    /**
     * Log security event
     */
    private function logSecurityEvent(Request $request, $attackType, $severity, $description)
    {
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();
        $macAddress = $this->getMacAddress($request);
        $schoolId = $this->getSchoolId($request);

        // Check if this is a repeated attack
        $existingLog = SecurityLog::byIp($ipAddress)
            ->byAttackType($attackType)
            ->recent(24) // Last 24 hours
            ->first();

        if ($existingLog) {
            // Update existing log
            $existingLog->update([
                'attempt_count' => $existingLog->attempt_count + 1,
                'last_attempt' => now(),
                'description' => $description
            ]);
        } else {
            // Create new log
            SecurityLog::create([
                'school_id' => $schoolId,
                'ip_address' => $ipAddress,
                'mac_address' => $macAddress,
                'user_agent' => $userAgent,
                'attack_type' => $attackType,
                'severity' => $severity,
                'description' => $description,
                'request_data' => $this->sanitizeRequestData($request),
                'attempt_count' => 1,
                'first_attempt' => now(),
                'last_attempt' => now()
            ]);
        }

        // Auto-ban for critical attacks
        if ($severity === 'critical' && $attackType !== 'ddos') {
            $this->autoBanIp($ipAddress, $macAddress, $schoolId, $attackType, $description);
        }
    }

    /**
     * Log audit trail
     */
    private function logAuditTrail(Request $request)
    {
        $user = Auth::user();
        $schoolId = $this->getSchoolId($request);
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();
        $macAddress = $this->getMacAddress($request);

        // Determine action based on route
        $action = $this->getActionFromRoute($request);
        $resourceType = $this->getResourceTypeFromRoute($request);

        AuditTrail::create([
            'school_id' => $schoolId,
            'user_id' => $user ? $user->id : null,
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $this->getResourceIdFromRoute($request),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'mac_address' => $macAddress,
            'description' => $this->getActionDescription($request, $action),
            'status' => 'success'
        ]);
    }

    /**
     * Auto-ban IP for critical attacks
     */
    private function autoBanIp($ipAddress, $macAddress, $schoolId, $attackType, $description)
    {
        BannedIp::create([
            'school_id' => $schoolId,
            'ip_address' => $ipAddress,
            'mac_address' => $macAddress,
            'ban_type' => 'temporary',
            'reason' => 'Auto-ban due to ' . $attackType,
            'description' => $description,
            'banned_at' => now(),
            'expires_at' => now()->addHours(24), // 24 hour ban
            'is_active' => true,
            'banned_by' => null // System ban
        ]);
    }

    /**
     * Get MAC address from request
     */
    private function getMacAddress(Request $request)
    {
        // This is a simplified implementation
        // In a real application, you might need to use ARP tables or other methods
        return $request->header('X-MAC-Address') ?: null;
    }

    /**
     * Get school ID from request
     */
    private function getSchoolId(Request $request)
    {
        $user = Auth::user();
        if ($user && $user->school_id) {
            return $user->school_id;
        }

        // Try to get from subdomain or other methods
        $host = $request->getHost();
        $subdomain = explode('.', $host)[0];
        
        if ($subdomain !== 'www' && $subdomain !== 'localhost') {
            $school = \App\Models\School::where('subdomain', $subdomain)->first();
            return $school ? $school->id : null;
        }

        return null;
    }

    /**
     * Get action from route
     */
    private function getActionFromRoute(Request $request)
    {
        $routeName = $request->route() ? $request->route()->getName() : '';
        
        if (str_contains($routeName, 'login')) return 'login';
        if (str_contains($routeName, 'logout')) return 'logout';
        if (str_contains($routeName, 'create')) return 'create';
        if (str_contains($routeName, 'edit')) return 'update';
        if (str_contains($routeName, 'destroy')) return 'delete';
        if (str_contains($routeName, 'show')) return 'view';
        if (str_contains($routeName, 'export')) return 'export';
        if (str_contains($routeName, 'import')) return 'import';
        
        return 'access';
    }

    /**
     * Get resource type from route
     */
    private function getResourceTypeFromRoute(Request $request)
    {
        $routeName = $request->route() ? $request->route()->getName() : '';
        
        if (str_contains($routeName, 'user')) return 'user';
        if (str_contains($routeName, 'school')) return 'school';
        if (str_contains($routeName, 'attendance')) return 'attendance';
        if (str_contains($routeName, 'report')) return 'report';
        if (str_contains($routeName, 'role')) return 'role';
        if (str_contains($routeName, 'permission')) return 'permission';
        
        return 'system';
    }

    /**
     * Get resource ID from route
     */
    private function getResourceIdFromRoute(Request $request)
    {
        $route = $request->route();
        if ($route && $route->parameters()) {
            $params = $route->parameters();
            return reset($params); // Get first parameter
        }
        return null;
    }

    /**
     * Get action description
     */
    private function getActionDescription(Request $request, $action)
    {
        $routeName = $request->route() ? $request->route()->getName() : '';
        $method = $request->method();
        
        return "{$method} request to {$routeName}";
    }

    /**
     * Sanitize request data for logging
     */
    private function sanitizeRequestData(Request $request)
    {
        $data = $request->all();
        
        // Remove sensitive data
        $sensitiveFields = ['password', 'password_confirmation', 'token', 'secret'];
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[REDACTED]';
            }
        }
        
        return $data;
    }
}
