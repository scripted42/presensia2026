<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = LeaveRequest::with(['user', 'approver']);

        // Role-based filtering with tenant isolation
        if ($user->hasRole('admin')) {
            // Admin can see all leave requests in their school only
            $query->whereHas('user', function($userQuery) use ($user) {
                $userQuery->where('school_id', $user->school_id);
            })->orderBy('created_at', 'desc');
        } elseif ($user->hasRole('headmaster')) {
            // Headmaster can see all leave requests in their school
            $query->whereHas('user', function($userQuery) use ($user) {
                $userQuery->where('school_id', $user->school_id);
            })->orderBy('created_at', 'desc');
        } elseif ($user->hasRole('teacher')) {
            // Teacher can see their own and students in the same school
            $query->where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('user', function($userQuery) use ($user) {
                      $userQuery->where('user_type', 'student')
                                ->where('school_id', $user->school_id);
                  });
            })->orderBy('created_at', 'desc');
        } else {
            // Other roles can only see their own requests
            $query->where('user_id', $user->id)->orderBy('created_at', 'desc');
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('type') && $request->type !== '') {
            $query->where('type', $request->type);
        }

        $leaveRequests = $query->paginate(10);

        return view('leave-requests.index', compact('leaveRequests'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $targetUserId = $request->get('user_id', $user->id);
        
        // Handle special case for student selection
        if ($targetUserId === 'student') {
            // Show student selection page for teachers/admins
            if (!$user->hasRole(['teacher', 'admin'])) {
                abort(403, 'Anda tidak memiliki izin untuk membuat permohonan izin untuk siswa.');
            }
            
            $students = User::where('user_type', 'student')
                ->where('school_id', $user->school_id)
                ->orderBy('name')
                ->get();
                
            return view('leave-requests.select-student', compact('students'));
        }
        
        $targetUser = User::where('school_id', $user->school_id)->findOrFail($targetUserId);

        // Check if user can create leave request for target user
        if (!$this->canCreateForUser($user, $targetUser)) {
            abort(403, 'Anda tidak memiliki izin untuk membuat permohonan izin untuk pengguna ini.');
        }

        return view('leave-requests.create', compact('targetUser'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $targetUserId = $request->user_id ?? $user->id;
        $targetUser = User::where('school_id', $user->school_id)->findOrFail($targetUserId);

        // Check if user can create leave request for target user
        if (!$this->canCreateForUser($user, $targetUser)) {
            abort(403, 'Anda tidak memiliki izin untuk membuat permohonan izin untuk pengguna ini.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:sick,leave,duty',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
            'evidence' => 'nullable|array|max:5',
            'evidence.*' => 'file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $evidencePaths = [];
        if ($request->hasFile('evidence')) {
            foreach ($request->file('evidence') as $file) {
                $path = $file->store('leave-evidence', 'public');
                $evidencePaths[] = $path;
            }
        }

        $leaveRequest = LeaveRequest::create([
            'user_id' => $targetUserId,
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'evidence' => $evidencePaths,
            'status' => 'pending',
        ]);

        return redirect()->route('leave-requests.index')
            ->with('success', 'Permohonan izin berhasil diajukan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(LeaveRequest $leaveRequest)
    {
        $user = Auth::user();
        
        // Check if user can view this leave request
        if (!$this->canViewLeaveRequest($user, $leaveRequest)) {
            abort(403, 'Anda tidak memiliki izin untuk melihat permohonan izin ini.');
        }

        return view('leave-requests.show', compact('leaveRequest'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LeaveRequest $leaveRequest)
    {
        $user = Auth::user();
        
        // Only allow editing if it's pending and user owns it
        if ($leaveRequest->user_id !== $user->id || $leaveRequest->status !== 'pending') {
            abort(403, 'Anda tidak dapat mengedit permohonan izin ini.');
        }

        return view('leave-requests.edit', compact('leaveRequest'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        $user = Auth::user();
        
        // Only allow updating if it's pending and user owns it
        if ($leaveRequest->user_id !== $user->id || $leaveRequest->status !== 'pending') {
            abort(403, 'Anda tidak dapat mengedit permohonan izin ini.');
        }

        $request->validate([
            'type' => 'required|in:sick,leave,duty',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
            'evidence' => 'nullable|array|max:5',
            'evidence.*' => 'file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $evidencePaths = $leaveRequest->evidence ?? [];
        
        if ($request->hasFile('evidence')) {
            // Delete old evidence files
            foreach ($evidencePaths as $path) {
                Storage::disk('public')->delete($path);
            }
            
            // Upload new evidence files
            $evidencePaths = [];
            foreach ($request->file('evidence') as $file) {
                $path = $file->store('leave-evidence', 'public');
                $evidencePaths[] = $path;
            }
        }

        $leaveRequest->update([
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'evidence' => $evidencePaths,
        ]);

        return redirect()->route('leave-requests.index')
            ->with('success', 'Permohonan izin berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeaveRequest $leaveRequest)
    {
        $user = Auth::user();
        
        // Only allow deleting if it's pending and user owns it
        if ($leaveRequest->user_id !== $user->id || $leaveRequest->status !== 'pending') {
            abort(403, 'Anda tidak dapat menghapus permohonan izin ini.');
        }

        // Delete evidence files
        if ($leaveRequest->evidence) {
            foreach ($leaveRequest->evidence as $path) {
                Storage::disk('public')->delete($path);
            }
        }

        $leaveRequest->delete();

        return redirect()->route('leave-requests.index')
            ->with('success', 'Permohonan izin berhasil dihapus.');
    }

    /**
     * Approve a leave request.
     */
    public function approve(LeaveRequest $leaveRequest)
    {
        $user = Auth::user();
        
        // Only admin and headmaster can approve
        if (!$user->hasRole(['admin', 'headmaster'])) {
            abort(403, 'Anda tidak memiliki izin untuk menyetujui permohonan izin.');
        }

        // Tenant isolation: approver must be in the same school as requester
        if ($leaveRequest->user->school_id !== $user->school_id) {
            abort(403, 'Anda tidak dapat menyetujui permohonan dari sekolah lain.');
        }

        $leaveRequest->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return redirect()->route('leave-requests.index')
            ->with('success', 'Permohonan izin berhasil disetujui.');
    }

    /**
     * Reject a leave request.
     */
    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        $user = Auth::user();
        
        // Only admin and headmaster can reject
        if (!$user->hasRole(['admin', 'headmaster'])) {
            abort(403, 'Anda tidak memiliki izin untuk menolak permohonan izin.');
        }

        // Tenant isolation: approver must be in the same school as requester
        if ($leaveRequest->user->school_id !== $user->school_id) {
            abort(403, 'Anda tidak dapat menolak permohonan dari sekolah lain.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $leaveRequest->update([
            'status' => 'rejected',
            'approved_by' => $user->id,
            'approved_at' => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        return redirect()->route('leave-requests.index')
            ->with('success', 'Permohonan izin berhasil ditolak.');
    }

    /**
     * Check if user can create leave request for target user.
     */
    private function canCreateForUser($user, $targetUser)
    {
        // User can always create for themselves
        if ($user->id === $targetUser->id) {
            return true;
        }

        // Teacher can create for students
        if ($user->hasRole('teacher') && $targetUser->user_type === 'student') {
            return true;
        }

        // Admin can create for anyone in their school only
        if ($user->hasRole('admin')) {
            return $targetUser->school_id === $user->school_id;
        }

        return false;
    }

    /**
     * Check if user can view leave request.
     */
    private function canViewLeaveRequest($user, $leaveRequest)
    {
        // User can view their own requests
        if ($user->id === $leaveRequest->user_id) {
            return true;
        }

        // Admin can view all in their school only
        if ($user->hasRole('admin')) {
            return $leaveRequest->user->school_id === $user->school_id;
        }

        // Headmaster can view all requests in their school
        if ($user->hasRole('headmaster') && $leaveRequest->user->school_id === $user->school_id) {
            return true;
        }

        // Teacher can view their students' requests (same school only)
        if ($user->hasRole('teacher') && $leaveRequest->user->user_type === 'student' && $leaveRequest->user->school_id === $user->school_id) {
            return true;
        }

        return false;
    }
}