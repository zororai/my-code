<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Teacher extends Model
{
    use Auditable;
    protected $fillable = [
        'user_id',
        'gender',
        'phone',
        'dateofbirth',
        'current_address',
        'permanent_address',
        'status',
        'is_class_teacher',
        'is_hod',
        'is_sport_director',
        'qr_code',
        'qr_code_token',
        'device_registration_status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    public function classes()
    {
        return $this->hasMany(Grade::class);
    }

    public function students() 
    {
        return $this->classes()->withCount('students');
    }

    public function attendances()
    {
        return $this->hasMany(TeacherAttendance::class);
    }

    public function devices()
    {
        return $this->hasMany(TeacherDevice::class);
    }

    public function activeDevice()
    {
        return $this->devices()->where('status', 'active')->first();
    }

    public function hasRegisteredDevice()
    {
        return $this->devices()->where('status', 'active')->exists();
    }

    public function requiresDeviceRegistration()
    {
        return $this->device_registration_status === 'pending';
    }

    public function isDeviceRegistrationRequired()
    {
        return $this->device_registration_status !== 'not_required';
    }

    /**
     * Get today's attendance entry
     */
    public function todayAttendance()
    {
        return $this->attendances()->whereDate('date', today())->first();
    }

    /**
     * Check if teacher is currently checked in today
     */
    public function isCheckedInToday()
    {
        $attendance = $this->todayAttendance();
        return $attendance && $attendance->isCheckedIn();
    }
}
