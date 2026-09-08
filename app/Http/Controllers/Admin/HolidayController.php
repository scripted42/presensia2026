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
        
        $params = $request->all();
        if (!$request->has('year')) {
            $params['year'] = (string) now()->year;
        }

        $query = HolidaySchedule::where('school_id', $user->school_id)
            ->filter($params)
            ->orderBy('date', 'desc');

        $perPage = (int) $request->get('per_page', 25);
        $holidays = $query->paginate($perPage)->appends($request->query());

        // Build active filters
        $activeFilters = [];

        if ($request->filled('q')) {
            $activeFilters[] = [
                'label' => 'Cari',
                'value' => $request->q,
                'remove_url' => request()->fullUrlWithQuery(['q' => null, 'page' => null]),
            ];
        }

        if ($request->filled('year') && $request->year !== 'all') {
            $activeFilters[] = [
                'label' => 'Tahun',
                'value' => $request->year,
                'remove_url' => request()->fullUrlWithQuery(['year' => 'all', 'page' => null]),
            ];
        }

        if ($request->filled('month') && $request->month !== 'all') {
            $monthNames = [
                '1' => 'Januari', '2' => 'Februari', '3' => 'Maret', '4' => 'April',
                '5' => 'Mei', '6' => 'Juni', '7' => 'Juli', '8' => 'Agustus',
                '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
            ];
            $activeFilters[] = [
                'label' => 'Bulan',
                'value' => $monthNames[$request->month] ?? $request->month,
                'remove_url' => request()->fullUrlWithQuery(['month' => null, 'page' => null]),
            ];
        }

        if ($request->filled('type')) {
            $typeNames = ['national' => 'Nasional', 'school' => 'Sekolah'];
            $activeFilters[] = [
                'label' => 'Jenis',
                'value' => $typeNames[$request->type] ?? $request->type,
                'remove_url' => request()->fullUrlWithQuery(['type' => null, 'page' => null]),
            ];
        }

        if ($request->filled('is_active')) {
            $activeFilters[] = [
                'label' => 'Status',
                'value' => $request->is_active == '1' ? 'Aktif' : 'Nonaktif',
                'remove_url' => request()->fullUrlWithQuery(['is_active' => null, 'page' => null]),
            ];
        }

        $year = $request->get('year', (string) now()->year);

        // Compute summary metrics
        $baseHolidayQuery = HolidaySchedule::where('school_id', $user->school_id);
        if ($year !== 'all') {
            $baseHolidayQuery->whereYear('date', $year);
        }

        $totalHolidays = (clone $baseHolidayQuery)->count();
        $nationalHolidays = (clone $baseHolidayQuery)->where('is_national_holiday', true)->count();
        $schoolHolidays = (clone $baseHolidayQuery)->where('is_national_holiday', false)->count();
        $activeHolidays = (clone $baseHolidayQuery)->where('is_active', true)->count();

        $yearLabel = $year === 'all' ? 'Semua tahun' : "Tahun $year";

        $summaryCards = [
            [
                'title' => 'Total Hari Libur',
                'value' => number_format($totalHolidays) . ' Hari',
                'subtext' => $yearLabel,
                'icon' => 'fas fa-calendar-alt',
                'color' => 'blue',
            ],
            [
                'title' => 'Libur Nasional',
                'value' => number_format($nationalHolidays) . ' Hari',
                'subtext' => 'Hari libur resmi nasional',
                'icon' => 'fas fa-flag',
                'color' => 'rose',
            ],
            [
                'title' => 'Libur Sekolah',
                'value' => number_format($schoolHolidays) . ' Hari',
                'subtext' => 'Kebijakan internal sekolah',
                'icon' => 'fas fa-school',
                'color' => 'purple',
            ],
            [
                'title' => 'Status Aktif',
                'value' => number_format($activeHolidays) . ' Hari',
                'subtext' => $totalHolidays > 0 ? round(($activeHolidays / $totalHolidays) * 100) . '% aktif berlaku' : 'Berlaku dalam sistem',
                'icon' => 'fas fa-check-circle',
                'color' => 'emerald',
            ],
        ];

        return view('admin.holidays.index', compact('holidays', 'year', 'activeFilters', 'summaryCards'));
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
    
    /**
     * Show import national holidays form.
     */
    public function showImport()
    {
        $years = \App\Services\NationalHolidayService::getAvailableYears();
        return view('admin.holidays.import', compact('years'));
    }
    
    /**
     * Import national holidays.
     */
    public function import(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:2030',
            'confirm' => 'required|accepted'
        ]);
        
        $user = Auth::user();
        $year = $request->year;
        
        try {
            $result = \App\Services\NationalHolidayService::importNationalHolidays($year, $user->school_id);
            
            $message = "Import berhasil! ";
            $message .= "Ditambahkan: {$result['imported']} hari libur, ";
            $message .= "Dilewati: {$result['skipped']} hari libur (sudah ada)";
            
            return redirect()->route('admin.holidays.index', ['year' => $year])
                ->with('success', $message);
                
        } catch (\Exception $e) {
            \Log::error('Failed to import national holidays: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Gagal mengimport hari libur nasional. Silakan coba lagi.');
        }
    }
    
    /**
     * Get national holidays preview via AJAX.
     */
    public function preview(Request $request)
    {
        $year = $request->get('year', now()->year);
        
        try {
            $holidays = \App\Services\NationalHolidayService::getNationalHolidays($year);
            
            return response()->json([
                'success' => true,
                'holidays' => $holidays,
                'count' => count($holidays)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data hari libur nasional'
            ], 500);
        }
    }
}