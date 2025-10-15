<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\SuperAdmin;
use App\Models\School;
use App\Models\TenantSetting;
use Illuminate\Support\Str;

class SuperAdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $schools = School::with(['superAdmin', 'tenantSettings', 'users'])->get();
        $superAdmins = SuperAdmin::with('schools')->get();
        
        return view('super-admin.dashboard', compact('schools', 'superAdmins'));
    }

    /**
     * Show change password form for Super Admin
     */
    public function showChangePassword()
    {
        return view('super-admin.change-password');
    }

    /**
     * Handle change password for Super Admin
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'nullable|string|min:8|confirmed',
            'new_email' => 'nullable|email|unique:users,email',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.']);
        }

        // Update password jika diisi
        if ($request->filled('new_password')) {
            $user->update(['password' => Hash::make($request->new_password)]);
        }

        // Update email login super admin jika diisi
        if ($request->filled('new_email')) {
            $oldEmail = $user->email;
            $user->update(['email' => $request->new_email]);
            // Persist ke .env agar middleware super.admin konsisten
            $this->setEnvValue('APP_SUPER_ADMIN_EMAIL', $request->new_email);
        }

        return redirect()->route('super-admin.index')->with('success', 'Akun super admin berhasil diperbarui.');
    }

    private function setEnvValue(string $key, string $value): void
    {
        $envPath = base_path('.env');
        if (!file_exists($envPath) || !is_writable($envPath)) {
            return; // diam apabila tidak bisa menulis
        }
        $content = file_get_contents($envPath);
        $pattern = "/^{$key}=.*$/m";
        $line = $key.'='.str_replace(['\n',"\r\n"], '', $value);
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, $line, $content);
        } else {
            $content .= "\n{$line}\n";
        }
        file_put_contents($envPath, $content);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('super-admin.schools.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'school_name' => 'required|string|max:255',
            'school_address' => 'nullable|string',
            'school_phone' => 'nullable|string',
            'school_email' => 'nullable|email',
            'school_website' => 'nullable|url',
            'school_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240', // 10MB max
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:8',
            'app_name' => 'required|string|max:255',
            'primary_color' => 'required|string',
            'secondary_color' => 'required|string',
        ]);

        // Handle logo upload
        $logoPath = null;
        if ($request->hasFile('school_logo')) {
            $logoPath = $request->file('school_logo')->store('schools/logos', 'public');
        }

        // Create school
        $school = School::create([
            'name' => $request->school_name,
            'address' => $request->school_address,
            'phone' => $request->school_phone,
            'email' => $request->school_email,
            'website' => $request->school_website,
            'logo' => $logoPath,
            'is_active' => true,
        ]);

        // Create admin user for the school
        $admin = \App\Models\User::create([
            'school_id' => $school->id,
            'name' => $request->admin_name,
            'email' => $request->admin_email,
            'password' => Hash::make($request->admin_password),
            'phone' => $request->school_phone,
            'address' => $request->school_address,
            'user_type' => 'employee',
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        // Create tenant settings
        TenantSetting::create([
            'school_id' => $school->id,
            'app_name' => $request->app_name,
            'primary_color' => $request->primary_color,
            'secondary_color' => $request->secondary_color,
            'accent_color' => $request->accent_color ?? '#F59E0B',
            'branding' => TenantSetting::getDefaultBranding(),
            'features' => TenantSetting::getDefaultFeatures(),
            'is_active' => true,
        ]);

        // Create attendance settings
        \App\Models\AttendanceSetting::create([
            'school_id' => $school->id,
            'check_in_time' => '07:00',
            'check_out_time' => '15:00',
            'location_latitude' => -6.2088,
            'location_longitude' => 106.8456,
            'location_name' => $request->school_name,
            'radius_meters' => 100,
            'qr_code_duration' => 10,
            'require_photo' => true,
            'require_location' => true,
            'is_active' => true,
        ]);

        return redirect()->route('super-admin.schools.show', $school)
            ->with('success', "Sekolah {$school->name} berhasil dibuat!")
            ->with('admin_info', [
                'email' => $admin->email,
                'password' => $request->admin_password,
                'name' => $admin->name
            ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(School $school)
    {
        $school->load(['superAdmin', 'tenantSettings', 'users', 'classes']);
        
        return view('super-admin.schools.show', compact('school'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(School $school)
    {
        $school->load('tenantSettings');
        
        return view('super-admin.schools.edit', compact('school'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, School $school)
    {
        $request->validate([
            'school_name' => 'required|string|max:255',
            'school_address' => 'nullable|string',
            'school_phone' => 'nullable|string',
            'school_email' => 'nullable|email',
            'school_website' => 'nullable|url',
            'school_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240', // 10MB max
            'app_name' => 'required|string|max:255',
            'primary_color' => 'required|string',
            'secondary_color' => 'required|string',
            'accent_color' => 'required|string',
        ]);

        // Handle logo upload/removal
        $logoPath = $school->logo; // Keep current logo by default
        
        // If remove_logo is set, set logo to null
        if ($request->has('remove_logo')) {
            // Delete old logo file if exists
            if ($school->logo && \Storage::disk('public')->exists($school->logo)) {
                \Storage::disk('public')->delete($school->logo);
            }
            $logoPath = null;
        }
        
        // If new logo is uploaded
        if ($request->hasFile('school_logo')) {
            // Delete old logo file if exists
            if ($school->logo && \Storage::disk('public')->exists($school->logo)) {
                \Storage::disk('public')->delete($school->logo);
            }
            // Store new logo
            $logoPath = $request->file('school_logo')->store('schools/logos', 'public');
        }

        // Update school
        $school->update([
            'name' => $request->school_name,
            'address' => $request->school_address,
            'phone' => $request->school_phone,
            'email' => $request->school_email,
            'website' => $request->school_website,
            'logo' => $logoPath,
        ]);

        // Update tenant settings
        $tenantSettings = $school->tenantSettings;
        if ($tenantSettings) {
            $tenantSettings->update([
                'app_name' => $request->app_name,
                'primary_color' => $request->primary_color,
                'secondary_color' => $request->secondary_color,
                'accent_color' => $request->accent_color,
            ]);
        }

        return redirect()->route('super-admin.index')
            ->with('success', "Sekolah {$school->name} berhasil diperbarui.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(School $school)
    {
        $schoolName = $school->name;
        $userCount = $school->users->count();
        
        // Delete related data first (cascade delete)
        $school->users()->delete();
        $school->classes()->delete();
        $school->tenantSettings()->delete();
        $school->attendanceSettings()->delete();
        
        // Delete the school
        $school->delete();

        return redirect()->route('super-admin.index')
            ->with('success', "Sekolah {$schoolName} dan {$userCount} user terkait berhasil dihapus.");
    }

    /**
     * Toggle school active status
     */
    public function toggleStatus(School $school)
    {
        $school->update(['is_active' => !$school->is_active]);
        
        $status = $school->is_active ? 'diaktifkan' : 'dinonaktifkan';
        
        return redirect()->route('super-admin.index')
            ->with('success', "Sekolah {$school->name} berhasil {$status}.");
    }

    /**
     * Show tenant settings for a school
     */
    public function tenantSettings(School $school)
    {
        $school->load('tenantSettings');
        
        return view('super-admin.schools.tenant-settings', compact('school'));
    }

    /**
     * Update tenant settings for a school
     */
    public function updateTenantSettings(Request $request, School $school)
    {
        $request->validate([
            'app_name' => 'required|string|max:255',
            'primary_color' => 'required|string',
            'secondary_color' => 'required|string',
            'accent_color' => 'required|string',
            'features' => 'array',
            'branding' => 'array',
        ]);

        $tenantSettings = $school->tenantSettings;
        if ($tenantSettings) {
            $tenantSettings->update([
                'app_name' => $request->app_name,
                'primary_color' => $request->primary_color,
                'secondary_color' => $request->secondary_color,
                'accent_color' => $request->accent_color,
                'features' => $request->features ?? TenantSetting::getDefaultFeatures(),
                'branding' => $request->branding ?? TenantSetting::getDefaultBranding(),
            ]);
        }

        return redirect()->route('super-admin.tenant-settings', $school)
            ->with('success', 'Pengaturan tenant berhasil diperbarui.');
    }
}