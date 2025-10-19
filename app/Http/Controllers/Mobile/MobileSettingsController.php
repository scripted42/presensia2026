<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AttendanceSetting;

class MobileSettingsController extends Controller
{
    /**
     * Get attendance settings
     */
    public function attendanceSettings(Request $request)
    {
        $user = Auth::user();

        $settings = AttendanceSetting::where('school_id', $user->school_id)
            ->where('is_active', true)
            ->first();

        if (!$settings) {
            return response()->json([
                'success' => false,
                'message' => 'Pengaturan absensi tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'check_in_time' => $settings->check_in_time,
                'check_out_time' => $settings->check_out_time,
                'location_latitude' => $settings->location_latitude,
                'location_longitude' => $settings->location_longitude,
                'location_name' => $settings->location_name,
                'radius_meters' => $settings->radius_meters,
                'require_photo' => $settings->require_photo,
                'require_location' => $settings->require_location,
                'qr_code_duration' => $settings->qr_code_duration,
            ]
        ]);
    }
}
