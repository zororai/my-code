<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SyllabusTopic extends Model
{
    protected $fillable = [
        'subject_id',
        'syllabus_category',
        'name',
        'description',
        'learning_objectives',
        'suggested_periods',
        'order_index',
        'term',
        'difficulty_level',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'suggested_periods' => 'integer',
        'order_index' => 'integer'
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function assessments()
    {
        return $this->hasMany(Assessment::class, 'syllabus_topic_id');
    }

    public function schemeTopics()
    {
        return $this->hasMany(SchemeTopic::class, 'syllabus_topic_id');
    }

    public function performanceSnapshots()
    {
        return $this->hasMany(TopicPerformanceSnapshot::class, 'syllabus_topic_id');
    }

    public function remedialLessons()
    {
        return $this->hasMany(RemedialLesson::class, 'syllabus_topic_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByTerm($query, $term)
    {
        return $query->where('term', $term);
    }

    public function scopeBySubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order_index')->orderBy('name');
    }
}
