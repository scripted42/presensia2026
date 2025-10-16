<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','nuptk','nip','nik','place_of_birth','date_of_birth','religion',
        'address_line','rt','rw','village','district','city','province','postal_code','phone',
        'last_education','major','university','graduation_year','ptk_type','employment_status','rank','salary_source','sk_cpns','tmt_cpns'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'tmt_cpns' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}




