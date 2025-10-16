<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditTrail;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuditTrailController extends Controller
{
    /**
     * Display audit trails for Super Admin
     */
    public function index(Request $request)
    {
        $query = AuditTrail::with(['school', 'user']);

        // Filter by school if specified
        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        // Filter by user if specified
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by action if specified
        if ($request->filled('action')) {
            $query->where('action', $request->action);
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

        $auditTrails = $query->orderBy('created_at', 'desc')->paginate(50);
        $schools = School::all();
        $users = User::all();

        return view('super-admin.audit-trails.index', compact('auditTrails', 'schools', 'users'));
    }

    /**
     * Display audit trails for specific school
     */
    public function school(Request $request, $schoolId)
    {
        $school = School::findOrFail($schoolId);
        
        $query = AuditTrail::with(['user'])
            ->where('school_id', $schoolId);

        // Filter by user if specified
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by action if specified
        if ($request->filled('action')) {
            $query->where('action', $request->action);
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

        $auditTrails = $query->orderBy('created_at', 'desc')->paginate(50);
        $users = User::where('school_id', $schoolId)->get();

        return view('super-admin.audit-trails.school', compact('auditTrails', 'school', 'users'));
    }

    /**
     * Show specific audit trail
     */
    public function show(AuditTrail $auditTrail)
    {
        $auditTrail->load(['school', 'user']);
        return view('super-admin.audit-trails.show', compact('auditTrail'));
    }

    /**
     * Export audit trails
     */
    public function export(Request $request)
    {
        $query = AuditTrail::with(['school', 'user']);

        // Apply same filters as index
        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
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

        $auditTrails = $query->orderBy('created_at', 'desc')->get();

        $filename = 'audit_trails_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($auditTrails) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID', 'School', 'User', 'Action', 'Resource Type', 'Resource ID',
                'IP Address', 'MAC Address', 'User Agent', 'Status', 'Description', 'Created At'
            ]);

            foreach ($auditTrails as $trail) {
                fputcsv($file, [
                    $trail->id,
                    $trail->school ? $trail->school->name : 'N/A',
                    $trail->user ? $trail->user->name : 'N/A',
                    $trail->formatted_action,
                    $trail->resource_type,
                    $trail->resource_id,
                    $trail->ip_address,
                    $trail->mac_address ?? 'N/A',
                    $trail->user_agent,
                    $trail->formatted_status,
                    $trail->description,
                    $trail->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get audit trail statistics
     */
    public function statistics(Request $request)
    {
        $query = AuditTrail::query();

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
            'total_actions' => $query->count(),
            'login_attempts' => $query->clone()->where('action', 'login')->count(),
            'failed_logins' => $query->clone()->where('action', 'login')->where('status', 'failed')->count(),
            'unique_ips' => $query->clone()->distinct('ip_address')->count('ip_address'),
            'actions_by_type' => $query->clone()->groupBy('action')->selectRaw('action, count(*) as count')->pluck('count', 'action'),
            'actions_by_school' => $query->clone()->join('schools', 'audit_trails.school_id', '=', 'schools.id')
                ->groupBy('schools.name')
                ->selectRaw('schools.name, count(*) as count')
                ->pluck('count', 'schools.name'),
            'recent_activity' => $query->clone()->with(['school', 'user'])->orderBy('created_at', 'desc')->limit(10)->get()
        ];

        return response()->json($stats);
    }
}
