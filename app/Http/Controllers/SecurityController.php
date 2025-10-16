<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SecurityLog;
use App\Models\BannedIp;
use App\Models\School;
use Illuminate\Support\Facades\Auth;

class SecurityController extends Controller
{
    /**
     * Display security logs dashboard
     */
    public function index(Request $request)
    {
        $query = SecurityLog::with(['school']);

        // Filter by school if specified
        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        // Filter by attack type if specified
        if ($request->filled('attack_type')) {
            $query->where('attack_type', $request->attack_type);
        }

        // Filter by severity if specified
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        // Filter by IP address if specified
        if ($request->filled('ip_address')) {
            $query->where('ip_address', 'like', '%' . $request->ip_address . '%');
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('created_at', '<=', $request->end_date);
        }

        $securityLogs = $query->orderBy('created_at', 'desc')->paginate(50);
        $schools = School::all();

        return view('super-admin.security.index', compact('securityLogs', 'schools'));
    }

    /**
     * Show specific security log
     */
    public function show(SecurityLog $securityLog)
    {
        $securityLog->load(['school']);
        return view('super-admin.security.show', compact('securityLog'));
    }

    /**
     * Display banned IPs
     */
    public function bannedIps(Request $request)
    {
        $query = BannedIp::with(['school', 'bannedBy']);

        // Filter by school if specified
        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        // Filter by ban type if specified
        if ($request->filled('ban_type')) {
            $query->where('ban_type', $request->ban_type);
        }

        // Filter by active status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $bannedIps = $query->orderBy('created_at', 'desc')->paginate(50);
        $schools = School::all();

        return view('super-admin.security.banned-ips', compact('bannedIps', 'schools'));
    }

    /**
     * Ban IP address
     */
    public function banIp(Request $request)
    {
        $request->validate([
            'ip_address' => 'nullable|ip',
            'mac_address' => 'nullable|string',
            'username' => 'nullable|string',
            'ban_type' => 'required|in:temporary,permanent,ip_range,mac,username',
            'reason' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_hours' => 'required_if:ban_type,temporary|integer|min:1|max:8760', // Max 1 year
            'school_id' => 'nullable|exists:schools,id'
        ]);

        $expiresAt = null;
        if ($request->ban_type === 'temporary') {
            $expiresAt = now()->addHours($request->duration_hours);
        }

        BannedIp::create([
            'school_id' => $request->school_id,
            'ip_address' => $request->ip_address,
            'mac_address' => $request->mac_address,
            'username' => $request->username,
            'ban_type' => $request->ban_type,
            'reason' => $request->reason,
            'description' => $request->description,
            'banned_at' => now(),
            'expires_at' => $expiresAt,
            'is_active' => true,
            'banned_by' => Auth::id()
        ]);

        return redirect()->back()->with('success', 'Ban berhasil ditambahkan.');
    }

    /**
     * Unban IP address
     */
    public function unbanIp(BannedIp $bannedIp)
    {
        $bannedIp->update(['is_active' => false]);
        
        return redirect()->back()->with('success', 'IP address has been unbanned successfully.');
    }

    /**
     * Block security log
     */
    public function blockSecurityLog(SecurityLog $securityLog)
    {
        $securityLog->update([
            'is_blocked' => true,
            'block_reason' => 'Manually blocked by admin'
        ]);

        // Auto-ban the IP if it's a critical attack
        if ($securityLog->severity === 'critical') {
            BannedIp::create([
                'school_id' => $securityLog->school_id,
                'ip_address' => $securityLog->ip_address,
                'mac_address' => $securityLog->mac_address,
                'ban_type' => 'temporary',
                'reason' => 'Auto-ban due to ' . $securityLog->attack_type,
                'description' => $securityLog->description,
                'banned_at' => now(),
                'expires_at' => now()->addHours(24),
                'is_active' => true,
                'banned_by' => Auth::id()
            ]);
        }

        return redirect()->back()->with('success', 'Security log has been blocked successfully.');
    }

    /**
     * Export security logs
     */
    public function exportSecurityLogs(Request $request)
    {
        $query = SecurityLog::with(['school']);

        // Apply same filters as index
        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }
        if ($request->filled('attack_type')) {
            $query->where('attack_type', $request->attack_type);
        }
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }
        if ($request->filled('ip_address')) {
            $query->where('ip_address', 'like', '%' . $request->ip_address . '%');
        }
        if ($request->filled('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('created_at', '<=', $request->end_date);
        }

        $securityLogs = $query->orderBy('created_at', 'desc')->get();

        $filename = 'security_logs_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($securityLogs) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID', 'School', 'IP Address', 'MAC Address', 'Attack Type', 'Severity',
                'Description', 'Attempt Count', 'First Attempt', 'Last Attempt', 'Blocked', 'Created At'
            ]);

            foreach ($securityLogs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->school ? $log->school->name : 'N/A',
                    $log->ip_address,
                    $log->mac_address ?? 'N/A',
                    $log->formatted_attack_type,
                    $log->formatted_severity,
                    $log->description,
                    $log->attempt_count,
                    $log->first_attempt ? $log->first_attempt->format('Y-m-d H:i:s') : 'N/A',
                    $log->last_attempt ? $log->last_attempt->format('Y-m-d H:i:s') : 'N/A',
                    $log->is_blocked ? 'Yes' : 'No',
                    $log->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get security statistics
     */
    public function statistics(Request $request)
    {
        $query = SecurityLog::query();

        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        if ($request->filled('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('created_at', '<=', $request->end_date);
        }

        $stats = [
            'total_attacks' => $query->count(),
            'critical_attacks' => $query->clone()->where('severity', 'critical')->count(),
            'high_attacks' => $query->clone()->where('severity', 'high')->count(),
            'blocked_attacks' => $query->clone()->where('is_blocked', true)->count(),
            'unique_ips' => $query->clone()->distinct('ip_address')->count('ip_address'),
            'attacks_by_type' => $query->clone()->groupBy('attack_type')->selectRaw('attack_type, count(*) as count')->pluck('count', 'attack_type'),
            'attacks_by_severity' => $query->clone()->groupBy('severity')->selectRaw('severity, count(*) as count')->pluck('count', 'severity'),
            'recent_attacks' => $query->clone()->with(['school'])->orderBy('created_at', 'desc')->limit(10)->get()
        ];

        return response()->json($stats);
    }

    /**
     * Get real-time security alerts
     */
    public function alerts()
    {
        $alerts = [
            'critical_attacks' => SecurityLog::where('severity', 'critical')
                ->where('created_at', '>=', now()->subHours(1))
                ->count(),
            'repeated_attacks' => SecurityLog::where('attempt_count', '>', 5)
                ->where('created_at', '>=', now()->subHours(24))
                ->count(),
            'new_banned_ips' => BannedIp::where('created_at', '>=', now()->subHours(24))
                ->count(),
            'active_bans' => BannedIp::active()->count()
        ];

        return response()->json($alerts);
    }
}
