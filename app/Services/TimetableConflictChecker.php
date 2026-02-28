<?php

namespace App\Services;

use App\Timetable;
use App\TimetableSetting;
use App\Subject;
use Illuminate\Support\Collection;

class TimetableConflictChecker
{
    /**
     * Check if a teacher is available at a specific time
     */
    public function isTeacherAvailable(
        int $teacherId,
        string $day,
        string $startTime,
        string $endTime,
        int $excludeClassId,
        string $academicYear,
        int $term
    ): bool {
        $conflict = Timetable::where('teacher_id', $teacherId)
            ->where('day', $day)
            ->where('academic_year', $academicYear)
            ->where('term', $term)
            ->where('class_id', '!=', $excludeClassId)
            ->where('slot_type', 'subject')
            ->where(function($query) use ($startTime, $endTime) {
                $query->where('start_time', '<', $endTime)
                      ->where('end_time', '>', $startTime);
            })
            ->exists();

        return !$conflict;
    }

    /**
     * Find all teacher conflicts in the timetable
     */
    public function findAllConflicts(string $academicYear, int $term): array
    {
        $conflicts = [];
        
        $timetables = Timetable::where('academic_year', $academicYear)
            ->where('term', $term)
            ->where('slot_type', 'subject')
            ->whereNotNull('teacher_id')
            ->with(['teacher.user', 'grade'])
            ->orderBy('teacher_id')
            ->orderBy('day')
            ->orderBy('start_time')
            ->get();

        $groupedByTeacherDay = $timetables->groupBy(function($item) {
            return $item->teacher_id . '_' . $item->day;
        });

        foreach ($groupedByTeacherDay as $group) {
            $slots = $group->sortBy('start_time')->values();
            
            for ($i = 0; $i < $slots->count(); $i++) {
                for ($j = $i + 1; $j < $slots->count(); $j++) {
                    $slot1 = $slots[$i];
                    $slot2 = $slots[$j];
                    
                    if ($slot1->start_time < $slot2->end_time && $slot1->end_time > $slot2->start_time) {
                        $teacherName = $slot1->teacher && $slot1->teacher->user 
                            ? $slot1->teacher->user->name 
                            : 'Unknown Teacher';
                        
                        $conflictKey = $slot1->teacher_id . '_' . $slot1->day . '_' . $slot1->start_time;
                        
                        if (!isset($conflicts[$conflictKey])) {
                            $conflicts[$conflictKey] = [
                                'teacher' => $teacherName,
                                'teacher_id' => $slot1->teacher_id,
                                'day' => $slot1->day,
                                'time' => $slot1->start_time . ' - ' . $slot1->end_time,
                                'classes' => []
                            ];
                        }
                        
                        if (!in_array($slot1->grade->class_name, $conflicts[$conflictKey]['classes'])) {
                            $conflicts[$conflictKey]['classes'][] = $slot1->grade->class_name;
                        }
                        if (!in_array($slot2->grade->class_name, $conflicts[$conflictKey]['classes'])) {
                            $conflicts[$conflictKey]['classes'][] = $slot2->grade->class_name;
                        }
                    }
                }
            }
        }

        return array_values($conflicts);
    }

    /**
     * Pre-validate timetable settings before generation
     */
    public function preValidate(array $settings, Collection $subjects): array
    {
        $warnings = [];
        
        $totalPeriodsNeeded = 0;
        foreach ($subjects as $subject) {
            $totalPeriodsNeeded += ($subject->single_lessons_per_week ?? 0) * 1;
            $totalPeriodsNeeded += ($subject->double_lessons_per_week ?? 0) * 2;
            $totalPeriodsNeeded += ($subject->triple_lessons_per_week ?? 0) * 3;
            $totalPeriodsNeeded += ($subject->quad_lessons_per_week ?? 0) * 4;
        }
        
        $dayStartMinutes = $this->timeToMinutes($settings['start_time']);
        $dayEndMinutes = $this->timeToMinutes($settings['end_time']);
        $breakDuration = $this->timeToMinutes($settings['break_end']) - $this->timeToMinutes($settings['break_start']);
        $lunchDuration = $this->timeToMinutes($settings['lunch_end']) - $this->timeToMinutes($settings['lunch_start']);
        
        $availableMinutesPerDay = ($dayEndMinutes - $dayStartMinutes) - $breakDuration - $lunchDuration;
        $periodsPerDay = floor($availableMinutesPerDay / $settings['subject_duration']);
        $totalAvailableSlots = $periodsPerDay * 5;
        
        if ($totalPeriodsNeeded > $totalAvailableSlots) {
            $warnings[] = [
                'type' => 'capacity',
                'message' => "Total periods needed ({$totalPeriodsNeeded}) exceeds available slots ({$totalAvailableSlots})",
                'suggestion' => 'Reduce the number of lessons per week, increase subject duration, or extend the school day'
            ];
        }
        
        foreach ($subjects as $subject) {
            if (!$subject->teacher_id) {
                $warnings[] = [
                    'type' => 'teacher',
                    'message' => "Subject '{$subject->name}' has no teacher assigned",
                    'suggestion' => 'Assign a teacher to this subject before generating the timetable'
                ];
            }
        }
        
        return $warnings;
    }

    /**
     * Convert time string to minutes from midnight
     */
    private function timeToMinutes(string $time): int
    {
        $parts = explode(':', $time);
        return (int)$parts[0] * 60 + (int)$parts[1];
    }
}
