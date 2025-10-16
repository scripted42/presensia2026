<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'app_name',
        'app_logo',
        'app_favicon',
        'primary_color',
        'secondary_color',
        'accent_color',
        'branding',
        'features',
        'custom_fields',
        'is_active',
    ];

    protected $casts = [
        'branding' => 'array',
        'features' => 'array',
        'custom_fields' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the school that owns the tenant setting
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get default features configuration
     */
    public static function getDefaultFeatures()
    {
        return [
            'attendance' => true,
            'leave_management' => true,
            'reports' => true,
            'qr_codes' => true,
            'bulk_import' => true,
            'rbac' => true,
            'notifications' => true,
            'api_access' => false,
            'custom_branding' => false,
        ];
    }

    /**
     * Get default branding configuration
     */
    public static function getDefaultBranding()
    {
        return [
            'show_logo' => true,
            'show_school_name' => true,
            'custom_css' => null,
            'footer_text' => 'Powered by Presensia',
            'login_background' => null,
        ];
    }
}


