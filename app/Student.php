<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Student extends Model
{
    use Auditable;
    protected $fillable = [
        'user_id',
        'parent_id',
        'class_id',
        'roll_number',
        'gender',
        'phone',
        'dateofbirth',
        'current_address',
        'permanent_address',
        'is_transferred',
        'student_type',
        'curriculum_type',
        'scholarship_percentage',
        'is_new_student',
        'chair',
        'desk',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(Parents::class);
    }

    // Many-to-many relationship with parents
    public function parents()
    {
        return $this->belongsToMany(Parents::class, 'student_parent', 'student_id', 'parent_id')->withTimestamps();
    }

    public function class()
    {
        return $this->belongsTo(Grade::class, 'class_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function payments()
    {
        return $this->hasMany(StudentPayment::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'student_subject', 'student_id', 'subject_id');
    }

    public function uniformCollections()
    {
        return $this->hasMany(UniformCollection::class);
    }

    public function pendingUniformCollections()
    {
        return $this->hasMany(UniformCollection::class)->where('status', 'pending');
    }

    public function purchases()
    {
        return $this->hasMany(ProductSaleItem::class);
    }
}
