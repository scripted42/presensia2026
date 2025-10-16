<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\School;
use App\Models\TenantSetting;

class TenantController extends Controller
{
    /**
     * Display tenant settings for current school
     */
    public function index()
    {
        $user = Auth::user();
        $school = $user->school;
        $tenantSettings = $school->tenantSettings;
        
        if (!$tenantSettings) {
            // Create default tenant settings if not exists
            $tenantSettings = TenantSetting::create([
                'school_id' => $school->id,
                'app_name' => $school->name,
                'branding' => TenantSetting::getDefaultBranding(),
                'features' => TenantSetting::getDefaultFeatures(),
                'is_active' => true,
            ]);
        }
        
        return view('tenant.settings', compact('school', 'tenantSettings'));
    }

    /**
     * Update tenant settings
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $school = $user->school;
        
        $request->validate([
            'app_name' => 'required|string|max:255',
            'primary_color' => 'required|string',
            'secondary_color' => 'required|string',
            'accent_color' => 'required|string',
            'app_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'app_favicon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:512',
        ]);

        $tenantSettings = $school->tenantSettings;
        
        $data = [
            'app_name' => $request->app_name,
            'primary_color' => $request->primary_color,
            'secondary_color' => $request->secondary_color,
            'accent_color' => $request->accent_color,
        ];

        // Handle logo upload
        if ($request->hasFile('app_logo')) {
            $logoPath = $request->file('app_logo')->store('tenant/logos', 'public');
            $data['app_logo'] = $logoPath;
        }

        // Handle favicon upload
        if ($request->hasFile('app_favicon')) {
            $faviconPath = $request->file('app_favicon')->store('tenant/favicons', 'public');
            $data['app_favicon'] = $faviconPath;
        }

        if ($tenantSettings) {
            $tenantSettings->update($data);
        } else {
            $data['school_id'] = $school->id;
            $data['branding'] = TenantSetting::getDefaultBranding();
            $data['features'] = TenantSetting::getDefaultFeatures();
            $data['is_active'] = true;
            TenantSetting::create($data);
        }

        return redirect()->route('tenant.settings')
            ->with('success', 'Pengaturan aplikasi berhasil diperbarui.');
    }

    /**
     * Update banner and layout settings
     */
    public function updateBanner(Request $request)
    {
        $user = Auth::user();
        $school = $user->school;
        
        $request->validate([
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            'banner_text' => 'nullable|string|max:255',
            'school_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            'school_photo_opacity' => 'nullable|integer|min:0|max:100',
            'topbar_announcement' => 'nullable|string|max:500',
            'show_announcement' => 'nullable|boolean',
        ]);

        $tenantSettings = $school->tenantSettings;
        
        $data = [
            'banner_text' => $request->banner_text,
            'school_photo_opacity' => $request->school_photo_opacity ?? 10,
            'topbar_announcement' => $request->topbar_announcement,
            'show_announcement' => $request->has('show_announcement'),
        ];

        // Handle banner image upload
        if ($request->hasFile('banner_image')) {
            $bannerPath = $request->file('banner_image')->store('tenant/banners', 'public');
            $data['banner_image'] = $bannerPath;
        }

        // Handle school photo upload
        if ($request->hasFile('school_photo')) {
            $schoolPhotoPath = $request->file('school_photo')->store('tenant/school-photos', 'public');
            $data['school_photo'] = $schoolPhotoPath;
        }

        if ($tenantSettings) {
            $tenantSettings->update($data);
        } else {
            $data['school_id'] = $school->id;
            $data['app_name'] = $school->name;
            $data['primary_color'] = '#3B82F6';
            $data['secondary_color'] = '#1E40AF';
            $data['accent_color'] = '#F59E0B';
            $data['branding'] = TenantSetting::getDefaultBranding();
            $data['features'] = TenantSetting::getDefaultFeatures();
            $data['is_active'] = true;
            TenantSetting::create($data);
        }

        return redirect()->route('tenant.settings')
            ->with('success', 'Pengaturan banner dan layout berhasil diperbarui.');
    }

    /**
     * Remove banner or school photo
     */
    public function removeImage(Request $request, $type)
    {
        $user = Auth::user();
        $school = $user->school;
        $tenantSettings = $school->tenantSettings;
        
        if (!$tenantSettings) {
            return response()->json([
                'success' => false,
                'message' => 'Pengaturan tenant tidak ditemukan'
            ], 404);
        }

        try {
            if ($type === 'banner') {
                // Delete banner file if exists
                if ($tenantSettings->banner_image && \Storage::disk('public')->exists($tenantSettings->banner_image)) {
                    \Storage::disk('public')->delete($tenantSettings->banner_image);
                }
                $tenantSettings->update(['banner_image' => null]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Banner berhasil dihapus'
                ]);
                
            } elseif ($type === 'school_photo') {
                // Delete school photo file if exists
                if ($tenantSettings->school_photo && \Storage::disk('public')->exists($tenantSettings->school_photo)) {
                    \Storage::disk('public')->delete($tenantSettings->school_photo);
                }
                $tenantSettings->update(['school_photo' => null]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Foto sekolah berhasil dihapus'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Tipe gambar tidak valid'
            ], 400);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update branding settings
     */
    public function updateBranding(Request $request)
    {
        $user = Auth::user();
        $school = $user->school;
        $tenantSettings = $school->tenantSettings;
        
        $request->validate([
            'show_logo' => 'boolean',
            'show_school_name' => 'boolean',
            'footer_text' => 'nullable|string|max:255',
            'custom_css' => 'nullable|string',
        ]);

        $branding = $tenantSettings->branding ?? TenantSetting::getDefaultBranding();
        $branding = array_merge($branding, $request->only([
            'show_logo',
            'show_school_name',
            'footer_text',
            'custom_css',
        ]));

        $tenantSettings->update(['branding' => $branding]);

        return redirect()->route('tenant.settings')
            ->with('success', 'Pengaturan branding berhasil diperbarui.');
    }

    /**
     * Update feature settings
     */
    public function updateFeatures(Request $request)
    {
        $user = Auth::user();
        $school = $user->school;
        $tenantSettings = $school->tenantSettings;
        
        $request->validate([
            'features' => 'array',
            'features.*' => 'boolean',
        ]);

        $tenantSettings->update(['features' => $request->features]);

        return redirect()->route('tenant.settings')
            ->with('success', 'Pengaturan fitur berhasil diperbarui.');
    }

    /**
     * Get current tenant settings for API
     */
    public function getSettings()
    {
        $user = Auth::user();
        $school = $user->school;
        $tenantSettings = $school->tenantSettings;
        
        if (!$tenantSettings) {
            return response()->json([
                'app_name' => $school->name,
                'primary_color' => '#3B82F6',
                'secondary_color' => '#1E40AF',
                'accent_color' => '#F59E0B',
                'branding' => TenantSetting::getDefaultBranding(),
                'features' => TenantSetting::getDefaultFeatures(),
            ]);
        }

        return response()->json([
            'app_name' => $tenantSettings->app_name,
            'app_logo' => $tenantSettings->app_logo,
            'app_favicon' => $tenantSettings->app_favicon,
            'primary_color' => $tenantSettings->primary_color,
            'secondary_color' => $tenantSettings->secondary_color,
            'accent_color' => $tenantSettings->accent_color,
            'branding' => $tenantSettings->branding,
            'features' => $tenantSettings->features,
        ]);
    }
}