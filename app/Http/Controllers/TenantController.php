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