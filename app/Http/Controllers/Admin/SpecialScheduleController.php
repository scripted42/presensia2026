<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SpecialSchedule;

class SpecialScheduleController extends Controller
{
    /**
     * Display a listing of special schedules.
     */
    public function index()
    {
        $user = Auth::user();
        
        $specialSchedules = SpecialSchedule::where('school_id', $user->school_id)
            ->orderBy('day_of_week')
            ->orderBy('max_check_in_time')
            ->get();
            
        return view('admin.special-schedules.index', compact('specialSchedules'));
    }

    /**
     * Show the form for creating a new special schedule.
     */
    public function create()
    {
        return view('admin.special-schedules.create');
    }

    /**
     * Store a newly created special schedule.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'max_check_in_time' => 'required|date_format:H:i',
            'affected_roles' => 'nullable|array',
            'affected_roles.*' => 'string|in:teacher,student,employee,admin,headmaster,tu,bk,kesiswaan',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'boolean'
        ]);

        $user = Auth::user();
        
        SpecialSchedule::create([
            'school_id' => $user->school_id,
            'name' => $request->name,
            'description' => $request->description,
            'day_of_week' => $request->day_of_week,
            'max_check_in_time' => $request->max_check_in_time,
            'affected_roles' => $request->affected_roles,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => $request->boolean('is_active', true)
        ]);

        return redirect()->route('admin.special-schedules.index')
            ->with('success', 'Jadwal khusus berhasil ditambahkan!');
    }

    /**
     * Show the form for editing a special schedule.
     */
    public function edit(SpecialSchedule $specialSchedule)
    {
        return view('admin.special-schedules.edit', compact('specialSchedule'));
    }

    /**
     * Update the specified special schedule.
     */
    public function update(Request $request, SpecialSchedule $specialSchedule)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'max_check_in_time' => 'required|date_format:H:i',
            'affected_roles' => 'nullable|array',
            'affected_roles.*' => 'string|in:teacher,student,employee,admin,headmaster,tu,bk,kesiswaan',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'boolean'
        ]);

        $specialSchedule->update([
            'name' => $request->name,
            'description' => $request->description,
            'day_of_week' => $request->day_of_week,
            'max_check_in_time' => $request->max_check_in_time,
            'affected_roles' => $request->affected_roles,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => $request->boolean('is_active')
        ]);

        return redirect()->route('admin.special-schedules.index')
            ->with('success', 'Jadwal khusus berhasil diperbarui!');
    }

    /**
     * Remove the specified special schedule.
     */
    public function destroy(SpecialSchedule $specialSchedule)
    {
        $specialSchedule->delete();
        
        return redirect()->route('admin.special-schedules.index')
            ->with('success', 'Jadwal khusus berhasil dihapus!');
    }

    /**
     * Toggle special schedule status.
     */
    public function toggle(SpecialSchedule $specialSchedule)
    {
        $specialSchedule->update(['is_active' => !$specialSchedule->is_active]);
        
        $status = $specialSchedule->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()
            ->with('success', "Jadwal khusus berhasil {$status}!");
    }
}