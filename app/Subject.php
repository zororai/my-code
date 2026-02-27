<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'subject_code',
        'teacher_id',
        'single_lessons_per_week',
        'double_lessons_per_week',
        'triple_lessons_per_week',
        'quad_lessons_per_week',
        'periods_per_week',
        'is_practical'
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function grades()
    {
        return $this->belongsToMany(Grade::class);
    }

    // A subject has many results
    public function results()
    {
        return $this->hasMany(Result::class, 'subject_id');
    }

    // A subject has many reading materials
    public function readings()
    {
        return $this->hasMany(Reading::class, 'subject_id');
    }
}
