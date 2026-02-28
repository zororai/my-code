<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TimetableSetting extends Model
{
    protected $fillable = [
        'class_id',
        'start_time',
        'break_start',
        'break_end',
        'lunch_start',
        'lunch_end',
        'end_time',
        'subject_duration',
        'academic_year',
        'term',
        'practical_config'
    ];

    protected $casts = [
        'subject_duration' => 'integer',
        'practical_config' => 'array',
    ];

    public function grade()
    {
        return $this->belongsTo(Grade::class, 'class_id');
    }

    public function getPracticalPeriodsDay1(): int
    {
        return $this->practical_config['periods_day1'] ?? 2;
    }

    public function getPracticalPeriodsDay2(): int
    {
        return $this->practical_config['periods_day2'] ?? 4;
    }

    public function getTotalPracticalPeriods(): int
    {
        return $this->getPracticalPeriodsDay1() + $this->getPracticalPeriodsDay2();
    }
}
