<?php

namespace App\Services;

class TimetableGenerationResult
{
    public array $placedLessons = [];
    public array $failedLessons = [];
    public array $conflicts = [];
    public array $warnings = [];

    /**
     * Add a failed lesson placement
     */
    public function addFailure(string $subject, string $reason, string $day = ''): void
    {
        $suggestion = $this->getSuggestionForReason($reason);
        
        $this->failedLessons[] = [
            'subject' => $subject,
            'reason' => $reason,
            'day' => $day,
            'suggestion' => $suggestion
        ];
    }

    /**
     * Add a conflict
     */
    public function addConflict(string $teacher, string $time, string $day, array $classes): void
    {
        $this->conflicts[] = [
            'teacher' => $teacher,
            'time' => $time,
            'day' => $day,
            'classes' => $classes
        ];
    }

    /**
     * Add a placed lesson
     */
    public function addPlacedLesson(string $subject, string $day, string $time): void
    {
        $this->placedLessons[] = [
            'subject' => $subject,
            'day' => $day,
            'time' => $time
        ];
    }

    /**
     * Add a warning
     */
    public function addWarning(string $type, string $message, string $suggestion = ''): void
    {
        $this->warnings[] = [
            'type' => $type,
            'message' => $message,
            'suggestion' => $suggestion
        ];
    }

    /**
     * Check if there are any issues
     */
    public function hasIssues(): bool
    {
        return !empty($this->failedLessons) || !empty($this->conflicts) || !empty($this->warnings);
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'placed' => count($this->placedLessons),
            'failed' => $this->failedLessons,
            'conflicts' => $this->conflicts,
            'warnings' => $this->warnings
        ];
    }

    /**
     * Get suggestion based on failure reason
     */
    private function getSuggestionForReason(string $reason): string
    {
        $reason = strtolower($reason);
        
        if (strpos($reason, 'teacher') !== false) {
            return 'Assign a different teacher or adjust their schedule';
        }
        
        if (strpos($reason, 'slot') !== false || strpos($reason, 'space') !== false) {
            return 'Reduce the number of lessons per week or extend the school day';
        }
        
        if (strpos($reason, 'consecutive') !== false) {
            return 'Use shorter multi-period lessons or adjust the timetable structure';
        }
        
        if (strpos($reason, 'day') !== false || strpos($reason, 'already') !== false) {
            return 'Subject already scheduled on this day - try regenerating the timetable';
        }
        
        return 'Review timetable settings and subject configurations';
    }
}
