<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','nis','nisn','nik','place_of_birth','date_of_birth','religion','gender',
        'address_line','rt','rw','village','district','city','province','postal_code','phone',
        'father_name','mother_name','guardian_name','father_job','mother_job','guardian_job','father_phone','mother_phone','guardian_phone',
        'admission_year','previous_school','transportation'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}


