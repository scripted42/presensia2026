<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\LeaveRequest;
use App\Models\Attendance;
use App\Models\User;

class MobileLeaveController extends Controller
{
    /**
     * Get list of user's leave requests and approvals (if teacher/admin).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $statusFilter = $request->input('status'); // 'all', 'pending', 'approved', 'rejected'
        $typeFilter = $request->input('type');     // 'sick', 'leave', 'duty'

        // 1. Personal leave requests query
        $myQuery = LeaveRequest::with(['approver:id,name'])
            ->where('user_id', $user->id);

        if (!empty($statusFilter) && $statusFilter !== 'all') {
            $myQuery->where('status', $statusFilter);
        }
        if (!empty($typeFilter) && $typeFilter !== 'all') {
            $myQuery->where('type', $typeFilter);
        }

        $myLeaves = $myQuery->orderBy('created_at', 'desc')->get();

        // Calculate personal statistics
        $allUserLeaves = LeaveRequest::where('user_id', $user->id)->get();
        $stats = [
            'total' => $allUserLeaves->count(),
            'pending' => $allUserLeaves->where('status', 'pending')->count(),
            'approved' => $allUserLeaves->where('status', 'approved')->count(),
            'rejected' => $allUserLeaves->where('status', 'rejected')->count(),
        ];

        // Format personal leave items
        $formattedMyLeaves = $myLeaves->map(function ($item) {
            return $this->formatLeaveItem($item);
        });

        // 2. Check if user can approve requests
        $canApprove = $user->hasRole(['admin', 'headmaster', 'teacher']);
        $approvalsList = [];

        if ($canApprove) {
            $approvalQuery = LeaveRequest::with([
                'user' => function ($q) {
                    $q->select('id', 'name', 'nis', 'user_type', 'school_id')
                      ->with(['studentClasses:id,name']);
                }
            ])
            ->where('school_id', $user->school_id)
            ->where('status', 'pending')
            ->where('user_id', '!=', $user->id);

            // If user is teacher (not admin/headmaster), only show student leave requests
            if (!$user->hasRole(['admin', 'headmaster'])) {
                $approvalQuery->whereHas('user', function ($q) {
                    $q->where('user_type', 'student');
                });
            }

            $approvals = $approvalQuery->orderBy('created_at', 'desc')->get();
            $approvalsList = $approvals->map(function ($item) {
                return $this->formatLeaveItem($item);
            });
        }

        return response()->json([
            'success' => true,
            'data' => [
                'statistics' => $stats,
                'my_leaves' => $formattedMyLeaves,
                'can_approve' => $canApprove,
                'pending_approvals_count' => count($approvalsList),
                'approvals' => $approvalsList,
            ],
        ]);
    }

    /**
     * Submit new leave request.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'type' => 'required|in:sick,leave,duty',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
            'evidence' => 'nullable|file|mimes:jpg,jpeg,png,pdf,webp|max:5120',
        ]);

        $evidencePaths = [];

        // Handle file upload
        if ($request->hasFile('evidence')) {
            $path = $request->file('evidence')->store('leave-evidence', 'public');
            $evidencePaths[] = $path;
        }

        $leaveRequest = LeaveRequest::create([
            'user_id' => $user->id,
            'school_id' => $user->school_id,
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'evidence' => $evidencePaths,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permohonan izin berhasil diajukan dan sedang menunggu persetujuan.',
            'data' => $this->formatLeaveItem($leaveRequest),
        ], 201);
    }

    /**
     * Get detail of a leave request.
     */
    public function show($id)
    {
        $user = Auth::user();
        $leaveRequest = LeaveRequest::with(['user', 'approver'])->findOrFail($id);

        // Security check
        if ($leaveRequest->user_id !== $user->id && $leaveRequest->school_id !== $user->school_id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke pengajuan ini.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatLeaveItem($leaveRequest),
        ]);
    }

    /**
     * Cancel/delete a pending leave request.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $leaveRequest = LeaveRequest::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($leaveRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan yang sudah diproses tidak dapat dibatalkan.',
            ], 400);
        }

        // Delete evidence if any
        if (is_array($leaveRequest->evidence)) {
            foreach ($leaveRequest->evidence as $path) {
                Storage::disk('public')->delete($path);
            }
        }

        $leaveRequest->delete();

        return response()->json([
            'success' => true,
            'message' => 'Permohonan izin berhasil dibatalkan.',
        ]);
    }

    /**
     * Approve a leave request (Guru/Admin) and synchronize with Attendance table.
     */
    public function approve(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user->hasRole(['admin', 'headmaster', 'teacher'])) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki wewenang untuk menyetujui permohonan izin.',
            ], 403);
        }

        $leaveRequest = LeaveRequest::with('user')->findOrFail($id);

        if ($leaveRequest->school_id !== $user->school_id) {
            return response()->json([
                'success' => false,
                'message' => 'Permohonan berasal dari sekolah yang berbeda.',
            ], 403);
        }

        if ($leaveRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Permohonan ini sudah diproses sebelumnya.',
            ], 400);
        }

        // Update leave request status
        $leaveRequest->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => Carbon::now('Asia/Jakarta'),
        ]);

        // Synchronize with Attendance table for the date range
        try {
            $period = CarbonPeriod::create($leaveRequest->start_date, $leaveRequest->end_date);
            $statusMapping = [
                'sick' => 'sick',
                'leave' => 'leave',
                'duty' => 'duty',
            ];
            $attendanceStatus = $statusMapping[$leaveRequest->type] ?? 'permit';

            foreach ($period as $date) {
                // Skip Sunday (0) and Saturday (6) if weekend
                if ($date->isSunday() || $date->isSaturday()) {
                    continue;
                }

                $dateStr = $date->toDateString();
                Attendance::updateOrCreate(
                    [
                        'user_id' => $leaveRequest->user_id,
                        'date' => $dateStr,
                    ],
                    [
                        'status' => $attendanceStatus,
                        'notes' => 'Izin disetujui: ' . $leaveRequest->reason,
                        'approved_by' => $user->id,
                        'approved_at' => Carbon::now('Asia/Jakarta'),
                    ]
                );
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error sync leave request to attendance: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Permohonan izin berhasil disetujui dan telah disinkronkan ke rekap presensi.',
            'data' => $this->formatLeaveItem($leaveRequest),
        ]);
    }

    /**
     * Reject a leave request (Guru/Admin).
     */
    public function reject(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user->hasRole(['admin', 'headmaster', 'teacher'])) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki wewenang untuk menolak permohonan izin.',
            ], 403);
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $leaveRequest = LeaveRequest::findOrFail($id);

        if ($leaveRequest->school_id !== $user->school_id) {
            return response()->json([
                'success' => false,
                'message' => 'Permohonan berasal dari sekolah yang berbeda.',
            ], 403);
        }

        $leaveRequest->update([
            'status' => 'rejected',
            'approved_by' => $user->id,
            'approved_at' => Carbon::now('Asia/Jakarta'),
            'rejection_reason' => $request->rejection_reason,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permohonan izin telah ditolak.',
            'data' => $this->formatLeaveItem($leaveRequest),
        ]);
    }

    /**
     * Helper to format a single leave request object with clean metadata and URLs.
     */
    private function formatLeaveItem($item)
    {
        $startDate = $item->start_date ? Carbon::parse($item->start_date)->toDateString() : null;
        $endDate = $item->end_date ? Carbon::parse($item->end_date)->toDateString() : null;

        $days = 1;
        if ($startDate && $endDate) {
            $days = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
        }

        // Evidence URLs
        $evidenceUrls = [];
        if (is_array($item->evidence)) {
            foreach ($item->evidence as $path) {
                if ($path) {
                    $evidenceUrls[] = asset('storage/' . $path);
                }
            }
        }

        // Type label
        $typeLabels = [
            'sick' => 'Sakit',
            'leave' => 'Cuti / Izin',
            'duty' => 'Dinas Luar',
        ];

        // Status label
        $statusLabels = [
            'pending' => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
        ];

        $userName = $item->user->name ?? null;
        $userNis = $item->user->nis ?? null;
        $userType = $item->user->user_type ?? null;
        $className = null;
        if (!empty($item->user->studentClasses) && $item->user->studentClasses->isNotEmpty()) {
            $className = $item->user->studentClasses->first()->name;
        }

        return [
            'id' => $item->id,
            'user_id' => $item->user_id,
            'user_name' => $userName,
            'user_nis' => $userNis,
            'user_type' => $userType,
            'class_name' => $className,
            'type' => $item->type,
            'type_label' => $typeLabels[$item->type] ?? ucfirst($item->type),
            'status' => $item->status,
            'status_label' => $statusLabels[$item->status] ?? ucfirst($item->status),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'duration_days' => $days,
            'reason' => $item->reason,
            'rejection_reason' => $item->rejection_reason,
            'evidence_urls' => $evidenceUrls,
            'approved_by_name' => $item->approver->name ?? null,
            'approved_at' => $item->approved_at ? Carbon::parse($item->approved_at)->format('d M Y H:i') : null,
            'created_at' => $item->created_at ? Carbon::parse($item->created_at)->format('d M Y H:i') : null,
        ];
    }
}
