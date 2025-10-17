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
        
        // Debug: Log request data
        \Log::info('=== UPDATEBANNER DEBUG START ===');
        \Log::info('Request method: ' . $request->method());
        \Log::info('Request URL: ' . $request->fullUrl());
        \Log::info('Has banner_image: ' . ($request->hasFile('banner_image') ? 'true' : 'false'));
        \Log::info('Has school_photo: ' . ($request->hasFile('school_photo') ? 'true' : 'false'));
        \Log::info('All files:', $request->allFiles());
        \Log::info('All input:', $request->all());
        \Log::info('Content type: ' . $request->header('Content-Type'));
        \Log::info('Content length: ' . $request->header('Content-Length'));
        
        $request->validate([
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            'banner_text' => 'nullable|string|max:255',
            'school_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            'school_photo_opacity' => 'nullable|integer|min:0|max:100',
            'school_photo_position_x' => 'nullable|string|in:left,center,right',
            'school_photo_position_y' => 'nullable|string|in:top,center,bottom',
            'school_photo_scale' => 'nullable|integer|min:50|max:200',
            'topbar_announcement' => 'nullable|string|max:500',
            'show_announcement' => 'nullable|boolean',
        ]);

        $tenantSettings = $school->tenantSettings;
        
        $data = [
            'banner_text' => $request->banner_text,
            'school_photo_opacity' => $request->school_photo_opacity ?? 10,
            'school_photo_position_x' => $request->school_photo_position_x ?? 'center',
            'school_photo_position_y' => $request->school_photo_position_y ?? 'center',
            'school_photo_scale' => $request->school_photo_scale ?? 100,
            'topbar_announcement' => $request->topbar_announcement,
            'show_announcement' => $request->has('show_announcement'),
        ];

        // Handle banner image upload
        if ($request->hasFile('banner_image')) {
            try {
                $bannerFile = $request->file('banner_image');
                \Log::info('Banner file details:', [
                    'original_name' => $bannerFile->getClientOriginalName(),
                    'size' => $bannerFile->getSize(),
                    'mime_type' => $bannerFile->getMimeType(),
                    'is_valid' => $bannerFile->isValid()
                ]);
                
                $bannerPath = $bannerFile->store('tenant/banners', 'public');
                $data['banner_image'] = $bannerPath;
                \Log::info('Banner uploaded successfully:', ['path' => $bannerPath]);
                
                // Verify file exists
                if (\Storage::disk('public')->exists($bannerPath)) {
                    \Log::info('Banner file verified to exist in storage');
                } else {
                    \Log::error('Banner file does not exist in storage after upload');
                }
            } catch (\Exception $e) {
                \Log::error('Banner upload failed:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                return redirect()->back()->with('error', 'Gagal mengupload banner: ' . $e->getMessage());
            }
        }

        // Handle school photo upload
        if ($request->hasFile('school_photo')) {
            try {
                $schoolPhotoFile = $request->file('school_photo');
                \Log::info('School photo file details:', [
                    'original_name' => $schoolPhotoFile->getClientOriginalName(),
                    'size' => $schoolPhotoFile->getSize(),
                    'mime_type' => $schoolPhotoFile->getMimeType(),
                    'is_valid' => $schoolPhotoFile->isValid()
                ]);
                
                $schoolPhotoPath = $schoolPhotoFile->store('tenant/school-photos', 'public');
                $data['school_photo'] = $schoolPhotoPath;
                \Log::info('School photo uploaded successfully:', ['path' => $schoolPhotoPath]);
                
                // Verify file exists
                if (\Storage::disk('public')->exists($schoolPhotoPath)) {
                    \Log::info('School photo file verified to exist in storage');
                } else {
                    \Log::error('School photo file does not exist in storage after upload');
                }
            } catch (\Exception $e) {
                \Log::error('School photo upload failed:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                return redirect()->back()->with('error', 'Gagal mengupload foto sekolah: ' . $e->getMessage());
            }
        }

        \Log::info('Data to be saved:', $data);
        
        if ($tenantSettings) {
            \Log::info('Updating existing tenant settings with ID: ' . $tenantSettings->id);
            $tenantSettings->update($data);
            \Log::info('Tenant settings updated successfully');
        } else {
            \Log::info('Creating new tenant settings');
            $data['school_id'] = $school->id;
            $data['app_name'] = $school->name;
            $data['primary_color'] = '#3B82F6';
            $data['secondary_color'] = '#1E40AF';
            $data['accent_color'] = '#F59E0B';
            $data['branding'] = TenantSetting::getDefaultBranding();
            $data['features'] = TenantSetting::getDefaultFeatures();
            $data['is_active'] = true;
            $tenantSettings = TenantSetting::create($data);
            \Log::info('New tenant settings created with ID: ' . $tenantSettings->id);
        }

        // Verify data was saved
        $updatedSettings = $school->tenantSettings()->latest()->first();
        \Log::info('Final tenant settings data:', [
            'banner_image' => $updatedSettings->banner_image,
            'school_photo' => $updatedSettings->school_photo,
            'school_photo_opacity' => $updatedSettings->school_photo_opacity,
            'school_photo_position_x' => $updatedSettings->school_photo_position_x,
            'school_photo_position_y' => $updatedSettings->school_photo_position_y,
            'school_photo_scale' => $updatedSettings->school_photo_scale
        ]);

        $message = 'Pengaturan banner dan layout berhasil diperbarui.';
        
        // Add specific messages for uploaded files
        if ($request->hasFile('banner_image')) {
            $message .= ' Banner berhasil diupload.';
        }
        if ($request->hasFile('school_photo')) {
            $message .= ' Foto sekolah berhasil diupload.';
        }
        
        \Log::info('=== UPDATEBANNER DEBUG END ===');
        
        return redirect()->route('tenant.settings')
            ->with('success', $message);
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