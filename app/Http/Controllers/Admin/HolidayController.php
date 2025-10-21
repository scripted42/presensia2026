<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\HolidaySchedule;
use Carbon\Carbon;

class HolidayController extends Controller
{
    /**
     * Display a listing of holidays.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $year = $request->get('year', now()->year);
        
        $holidays = HolidaySchedule::where('school_id', $user->school_id)
            ->whereYear('date', $year)
            ->orderBy('date')
            ->get();
            
        return view('admin.holidays.index', compact('holidays', 'year'));
    }

    /**
     * Show the form for creating a new holiday.
     */
    public function create()
    {
        return view('admin.holidays.create');
    }

    /**
     * Store a newly created holiday.
     */
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'holiday_name' => 'required|string|max:255',
            'is_national_holiday' => 'boolean',
            'is_active' => 'boolean'
        ]);

        $user = Auth::user();
        
        HolidaySchedule::create([
            'school_id' => $user->school_id,
            'date' => $request->date,
            'day_name' => Carbon::parse($request->date)->format('l'),
            'holiday_name' => $request->holiday_name,
            'is_weekend' => false,
            'is_national_holiday' => $request->boolean('is_national_holiday'),
            'is_active' => $request->boolean('is_active', true)
        ]);

        return redirect()->route('admin.holidays.index')
            ->with('success', 'Hari libur berhasil ditambahkan!');
    }

    /**
     * Show the form for editing a holiday.
     */
    public function edit(HolidaySchedule $holiday)
    {
        return view('admin.holidays.edit', compact('holiday'));
    }

    /**
     * Update the specified holiday.
     */
    public function update(Request $request, HolidaySchedule $holiday)
    {
        $request->validate([
            'date' => 'required|date',
            'holiday_name' => 'required|string|max:255',
            'is_national_holiday' => 'boolean',
            'is_active' => 'boolean'
        ]);

        $holiday->update([
            'date' => $request->date,
            'day_name' => Carbon::parse($request->date)->format('l'),
            'holiday_name' => $request->holiday_name,
            'is_national_holiday' => $request->boolean('is_national_holiday'),
            'is_active' => $request->boolean('is_active')
        ]);

        return redirect()->route('admin.holidays.index')
            ->with('success', 'Hari libur berhasil diperbarui!');
    }

    /**
     * Remove the specified holiday.
     */
    public function destroy(HolidaySchedule $holiday)
    {
        $holiday->delete();
        
        return redirect()->route('admin.holidays.index')
            ->with('success', 'Hari libur berhasil dihapus!');
    }

    /**
     * Toggle holiday status.
     */
    public function toggle(HolidaySchedule $holiday)
    {
        $holiday->update(['is_active' => !$holiday->is_active]);
        
        $status = $holiday->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()
            ->with('success', "Hari libur berhasil {$status}!");
    }
}