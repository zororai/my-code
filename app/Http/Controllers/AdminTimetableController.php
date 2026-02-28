<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Timetable;
use App\TimetableSetting;
use App\Grade;
use App\Subject;
use App\Teacher;
use App\Services\TimetableConflictChecker;
use App\Services\TimetableGenerationResult;
use Carbon\Carbon;

class AdminTimetableController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $classes = Grade::with(['subjects', 'teacher'])->orderBy('class_numeric')->get();
        return view('backend.admin.timetable.index', compact('classes'));
    }

    /**
     * Display master timetable with all classes on one sheet
     */
    public function master(Request $request)
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        
        // Get current term info
        $currentTerm = \App\ResultsStatus::orderBy('year', 'desc')
            ->orderBy('result_period', 'desc')
            ->first();
        
        $academicYear = $request->get('year', $currentTerm ? $currentTerm->year : date('Y'));
        $term = $request->get('term', 1);
        if ($currentTerm && $currentTerm->result_period) {
            preg_match('/\d+/', $currentTerm->result_period, $matches);
            $term = $request->get('term', $matches[0] ?? 1);
        }
        
        // Get all classes with timetables
        $classes = Grade::with(['subjects', 'teacher'])
            ->orderBy('class_numeric')
            ->get();
        
        // Build timetable data for each class
        $classTimetables = [];
        $allTimeSlots = [];
        
        foreach ($classes as $class) {
            $timetable = Timetable::where('class_id', $class->id)
                ->where('academic_year', $academicYear)
                ->where('term', $term)
                ->with(['subject', 'teacher'])
                ->orderBy('slot_order')
                ->get();
            
            if ($timetable->count() > 0) {
                $classTimetables[$class->id] = [
                    'class' => $class,
                    'slots' => $timetable->groupBy('day'),
                ];
                
                // Collect all unique time slots
                foreach ($timetable as $slot) {
                    $timeKey = $slot->start_time . '-' . $slot->end_time;
                    if (!isset($allTimeSlots[$timeKey])) {
                        $allTimeSlots[$timeKey] = [
                            'start_time' => $slot->start_time,
                            'end_time' => $slot->end_time,
                            'slot_type' => $slot->slot_type,
                            'slot_order' => $slot->slot_order,
                        ];
                    }
                }
            }
        }
        
        // Sort time slots by start time
        uasort($allTimeSlots, function($a, $b) {
            return strcmp($a['start_time'], $b['start_time']);
        });
        
        return view('backend.admin.timetable.master', compact(
            'classes', 
            'classTimetables', 
            'days', 
            'allTimeSlots',
            'academicYear',
            'term',
            'currentTerm'
        ));
    }

    public function create()
    {
        $classes = Grade::orderBy('class_numeric')->get();
        $subjects = Subject::orderBy('name')->get();
        $teachers = Teacher::with('user')->get();
        
        // Get current term from ResultsStatus
        $currentTerm = \App\ResultsStatus::orderBy('year', 'desc')
            ->orderBy('result_period', 'desc')
            ->first();
        
        return view('backend.admin.timetable.create', compact('classes', 'subjects', 'teachers', 'currentTerm'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'class_ids' => 'required|array|min:1',
            'class_ids.*' => 'exists:grades,id',
            'start_time' => 'required|date_format:H:i',
            'break_start' => 'required|date_format:H:i',
            'break_end' => 'required|date_format:H:i',
            'lunch_start' => 'required|date_format:H:i',
            'lunch_end' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'subject_duration' => 'required|integer|min:20|max:120',
            'academic_year' => 'required|string|max:10',
            'term' => 'required|integer|min:1|max:3',
            // Clubs
            'include_clubs' => 'nullable',
            'clubs_days' => 'nullable|array',
            'clubs_days.*' => 'nullable|string',
            'clubs_position' => 'nullable|string',
            'clubs_start' => 'nullable|date_format:H:i',
            'clubs_end' => 'nullable|date_format:H:i',
            'clubs_periods' => 'nullable|integer|min:1|max:4',
            // Special slots
            'include_before_break' => 'nullable',
            'before_break_name' => 'nullable|string|max:50',
            'before_break_days' => 'nullable|array',
            'before_break_periods' => 'nullable|integer|min:1|max:3',
            'include_after_break' => 'nullable',
            'after_break_name' => 'nullable|string|max:50',
            'after_break_days' => 'nullable|array',
            'after_break_periods' => 'nullable|integer|min:1|max:3',
            'include_after_lunch' => 'nullable',
            'after_lunch_name' => 'nullable|string|max:50',
            'after_lunch_days' => 'nullable|array',
            'after_lunch_periods' => 'nullable|integer|min:1|max:3',
            // Practicals
            'include_practicals' => 'nullable',
            'practical_subjects' => 'nullable|array',
            'practical_subjects.*' => 'nullable|exists:subjects,id',
            'practical_periods_day1' => 'nullable|integer|min:1|max:6',
            'practical_periods_day2' => 'nullable|integer|min:1|max:6',
        ]);

        $classIds = $validated['class_ids'];
        $generatedCount = 0;
        $conflictChecker = new TimetableConflictChecker();
        $allResults = [];
        $allWarnings = [];
        
        // Prepare special slots config (base config without practicals)
        $baseSpecialSlots = [
            'clubs' => $request->include_clubs ? [
                'days' => $request->clubs_days ?? ['Wednesday'],
                'position' => $request->clubs_position ?? 'after_lunch',
                'start' => $request->clubs_start ?? '14:00',
                'end' => $request->clubs_end ?? '15:00',
                'periods' => intval($request->clubs_periods ?? 2),
            ] : null,
            'before_break' => $request->include_before_break ? [
                'name' => $request->before_break_name ?? 'Assembly',
                'days' => $request->before_break_days ?? [],
                'periods' => intval($request->before_break_periods ?? 1),
            ] : null,
            'after_break' => $request->include_after_break ? [
                'name' => $request->after_break_name ?? 'Sports',
                'days' => $request->after_break_days ?? [],
                'periods' => intval($request->after_break_periods ?? 1),
            ] : null,
            'after_lunch' => $request->include_after_lunch ? [
                'name' => $request->after_lunch_name ?? 'Reading',
                'days' => $request->after_lunch_days ?? [],
                'periods' => intval($request->after_lunch_periods ?? 1),
            ] : null,
        ];

        foreach ($classIds as $index => $classId) {
            $class = Grade::with('subjects')->find($classId);
            
            // Pre-validate before generation
            $warnings = $conflictChecker->preValidate(
                (object)$validated,
                $class->subjects
            );
            $allWarnings = array_merge($allWarnings, $warnings);
            
            // Clone base special slots for this class
            $specialSlots = $baseSpecialSlots;
            
            // Add practicals with dynamic day assignment
            if ($request->include_practicals) {
                $practicalDays = $this->assignPracticalDays($index);
                
                $specialSlots['practicals'] = [
                    'subjects' => $request->practical_subjects ?? [],
                    'day_periods1' => $practicalDays['day_periods1'],
                    'day_periods2' => $practicalDays['day_periods2'],
                    'periods_day1' => intval($request->practical_periods_day1 ?? 2),
                    'periods_day2' => intval($request->practical_periods_day2 ?? 4),
                ];
            } else {
                $specialSlots['practicals'] = null;
            }
            
            // Prepare settings data for this class
            $settingsData = $validated;
            $settingsData['class_id'] = $classId;
            unset($settingsData['class_ids']);
            
            // Store practical config in JSON
            if ($request->include_practicals) {
                $settingsData['practical_config'] = [
                    'periods_day1' => intval($request->practical_periods_day1 ?? 2),
                    'periods_day2' => intval($request->practical_periods_day2 ?? 4),
                ];
            }
            
            // Remove special slot fields from settings
            unset($settingsData['include_clubs'], $settingsData['clubs_days'], $settingsData['clubs_position'], 
                  $settingsData['clubs_start'], $settingsData['clubs_end'], $settingsData['clubs_periods'],
                  $settingsData['include_before_break'], $settingsData['before_break_name'], 
                  $settingsData['before_break_days'], $settingsData['before_break_periods'],
                  $settingsData['include_after_break'], $settingsData['after_break_name'], 
                  $settingsData['after_break_days'], $settingsData['after_break_periods'],
                  $settingsData['include_after_lunch'], $settingsData['after_lunch_name'], 
                  $settingsData['after_lunch_days'], $settingsData['after_lunch_periods'],
                  $settingsData['include_practicals'], $settingsData['practical_subjects'],
                  $settingsData['practical_periods_day1'], $settingsData['practical_periods_day2']);

            // Save or update timetable settings
            $settings = TimetableSetting::updateOrCreate(
                [
                    'class_id' => $classId,
                    'academic_year' => $validated['academic_year'],
                    'term' => $validated['term']
                ],
                $settingsData
            );

            // Generate timetable with special slots
            $result = $this->generateTimetable($settings, $specialSlots);
            $allResults[] = $result;
            $generatedCount++;
        }

        // Check for conflicts after all timetables are generated
        $conflicts = $conflictChecker->findAllConflicts($validated['academic_year'], $validated['term']);
        
        // Collect all failures
        $allFailures = [];
        foreach ($allResults as $result) {
            $allFailures = array_merge($allFailures, $result->failedLessons);
        }
        
        // Flash warnings, conflicts, and failures to session
        if (!empty($allWarnings)) {
            session()->flash('timetable_warnings', $allWarnings);
        }
        if (!empty($conflicts)) {
            session()->flash('timetable_conflicts', $conflicts);
        }
        if (!empty($allFailures)) {
            session()->flash('timetable_failures', $allFailures);
        }

        // If only one class, redirect to show that class's timetable
        if (count($classIds) === 1) {
            return redirect()->route('admin.timetable.show', $classIds[0])
                ->with('success', 'Timetable generated successfully!');
        }

        // If multiple classes, redirect to index
        return redirect()->route('admin.timetable.index')
            ->with('success', "Timetables generated successfully for {$generatedCount} classes!");
    }

    public function show($classId)
    {
        $class = Grade::with(['subjects', 'teacher'])->findOrFail($classId);
        $settings = TimetableSetting::where('class_id', $classId)
            ->orderBy('academic_year', 'desc')
            ->orderBy('term', 'desc')
            ->first();
        
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $timetable = [];
        
        if ($settings) {
            foreach ($days as $day) {
                $timetable[$day] = Timetable::where('class_id', $classId)
                    ->where('academic_year', $settings->academic_year)
                    ->where('term', $settings->term)
                    ->where('day', $day)
                    ->with(['subject.teacher.user', 'teacher.user'])
                    ->orderBy('slot_order')
                    ->get();
            }
        }

        return view('backend.admin.timetable.show', compact('class', 'settings', 'timetable', 'days'));
    }

    public function edit($classId)
    {
        $class = Grade::with('subjects.teacher.user')->findOrFail($classId);
        $settings = TimetableSetting::where('class_id', $classId)
            ->orderBy('academic_year', 'desc')
            ->orderBy('term', 'desc')
            ->first();
        $classSubjects = $class->subjects()->with('teacher.user')->orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();
        $teachers = Teacher::with(['user', 'subjects'])->get();
        
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $timetable = [];
        
        if ($settings) {
            foreach ($days as $day) {
                $timetable[$day] = Timetable::where('class_id', $classId)
                    ->where('academic_year', $settings->academic_year)
                    ->where('term', $settings->term)
                    ->where('day', $day)
                    ->orderBy('slot_order')
                    ->get();
            }
        }

        return view('backend.admin.timetable.edit', compact('class', 'settings', 'timetable', 'days', 'subjects', 'teachers', 'classSubjects'));
    }

    public function update(Request $request, $classId)
    {
        $validated = $request->validate([
            'slots' => 'required|array',
            'slots.*.id' => 'required|exists:timetables,id',
            'slots.*.subject_id' => 'nullable|exists:subjects,id',
            'slots.*.teacher_id' => 'nullable|exists:teachers,id',
        ]);

        $conflicts = [];
        $class = Grade::with('subjects')->findOrFail($classId);
        $classSubjectIds = $class->subjects->pluck('id')->toArray();

        foreach ($validated['slots'] as $slotData) {
            $slot = Timetable::find($slotData['id']);
            
            // Check if subject is assigned to this class
            if ($slotData['subject_id'] && !in_array($slotData['subject_id'], $classSubjectIds)) {
                $subject = Subject::find($slotData['subject_id']);
                $conflicts[] = "Subject '{$subject->name}' is not assigned to this class";
                continue;
            }
            
            // Check if teacher is assigned to teach this subject
            if ($slotData['teacher_id'] && $slotData['subject_id']) {
                $subject = Subject::find($slotData['subject_id']);
                if ($subject->teacher_id != $slotData['teacher_id']) {
                    $teacher = Teacher::with('user')->find($slotData['teacher_id']);
                    $assignedTeacher = $subject->teacher ? $subject->teacher->user->name : 'None';
                    $conflicts[] = "{$teacher->user->name} does not teach {$subject->name} (assigned: {$assignedTeacher})";
                    continue;
                }
            }
            
            // Check for teacher time conflicts
            if ($slotData['teacher_id']) {
                $hasConflict = Timetable::checkTeacherConflict(
                    $slotData['teacher_id'],
                    $slot->day,
                    $slot->start_time,
                    $slot->end_time,
                    $slot->id
                );
                
                if ($hasConflict) {
                    $teacher = Teacher::with('user')->find($slotData['teacher_id']);
                    $conflicts[] = "{$teacher->user->name} has a time conflict on {$slot->day} at {$slot->start_time}";
                    continue;
                }
            }

            $slot->update([
                'subject_id' => $slotData['subject_id'],
                'teacher_id' => $slotData['teacher_id'],
            ]);
        }

        if (!empty($conflicts)) {
            return redirect()->back()->with('warning', 'Some slots had conflicts: ' . implode(', ', $conflicts));
        }

        return redirect()->route('admin.timetable.show', $classId)
            ->with('success', 'Timetable updated successfully!');
    }

    public function destroy($classId)
    {
        Timetable::where('class_id', $classId)->delete();
        TimetableSetting::where('class_id', $classId)->delete();

        return redirect()->route('admin.timetable.index')
            ->with('success', 'Timetable deleted successfully!');
    }

    private function generateTimetable(TimetableSetting $settings, $specialSlots = [])
    {
        $result = new TimetableGenerationResult();
        
        // Delete existing timetable for this class, academic year, and term
        Timetable::where('class_id', $settings->class_id)
            ->where('academic_year', $settings->academic_year)
            ->where('term', $settings->term)
            ->delete();

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $class = Grade::with('subjects.teacher')->find($settings->class_id);
        $subjects = $class->subjects;
        
        // Get practical subject IDs to exclude from regular timetable
        $practicalSubjectIds = [];
        if (!empty($specialSlots['practicals']['subjects'])) {
            $practicalSubjectIds = $specialSlots['practicals']['subjects'];
        }
        
        // Build lesson pools by type (quad, triple, double, single)
        // Each entry represents ONE lesson block (not individual periods)
        // EXCLUDE practical subjects - they will be in the Clubs/Activities section
        $lessonPool = [];
        
        // Track which subjects have multi-period lessons configured
        // These subjects CAN have lessons back-to-back on the same day
        $subjectsWithMultiPeriod = [];
        foreach ($subjects as $subject) {
            $hasMulti = ($subject->double_lessons_per_week ?? 0) > 0 
                     || ($subject->triple_lessons_per_week ?? 0) > 0 
                     || ($subject->quad_lessons_per_week ?? 0) > 0;
            $subjectsWithMultiPeriod[$subject->id] = $hasMulti;
        }
        
        foreach ($subjects as $subject) {
            // Skip practical subjects - they will be scheduled in Clubs/Activities section
            if (in_array($subject->id, $practicalSubjectIds)) {
                continue;
            }
            
            $validTeacherId = null;
            if ($subject->teacher_id && $subject->teacher) {
                $validTeacherId = $subject->teacher_id;
            }
            
            $subjectInfo = [
                'id' => $subject->id,
                'name' => $subject->name,
                'teacher_id' => $validTeacherId,
                'has_multi_period' => $subjectsWithMultiPeriod[$subject->id],
            ];
            
            // Add quad lessons (4 consecutive periods)
            $quadCount = $subject->quad_lessons_per_week ?? 0;
            for ($i = 0; $i < $quadCount; $i++) {
                $lessonPool[] = array_merge($subjectInfo, ['duration_periods' => 4, 'type' => 'quad']);
            }
            
            // Add triple lessons (3 consecutive periods)
            $tripleCount = $subject->triple_lessons_per_week ?? 0;
            for ($i = 0; $i < $tripleCount; $i++) {
                $lessonPool[] = array_merge($subjectInfo, ['duration_periods' => 3, 'type' => 'triple']);
            }
            
            // Add double lessons (2 consecutive periods)
            $doubleCount = $subject->double_lessons_per_week ?? 0;
            for ($i = 0; $i < $doubleCount; $i++) {
                $lessonPool[] = array_merge($subjectInfo, ['duration_periods' => 2, 'type' => 'double']);
            }
            
            // Add single lessons (1 period)
            $singleCount = $subject->single_lessons_per_week ?? 0;
            for ($i = 0; $i < $singleCount; $i++) {
                $lessonPool[] = array_merge($subjectInfo, ['duration_periods' => 1, 'type' => 'single']);
            }
        }
        
        // Sort pool: longer lessons first (harder to fit), then shuffle within each type
        usort($lessonPool, function($a, $b) {
            return $b['duration_periods'] - $a['duration_periods'];
        });
        
        // Track which lessons have been placed and on which days
        $placedLessons = []; // Array of placed lesson info
        
        // STRICT CONSTRAINT: Track subjects used per day
        // A subject may appear ONLY ONCE per day (as a single consecutive block)
        $usedSubjectsByDay = [
            'Monday'    => [],
            'Tuesday'   => [],
            'Wednesday' => [],
            'Thursday'  => [],
            'Friday'    => [],
        ];
        
        // First pass: Generate the time structure for each day
        $dayStructure = [];
        foreach ($days as $day) {
            $dayStructure[$day] = $this->generateDayStructure($settings, $day, $specialSlots);
        }
        
        // Track how many lessons of each type have been placed for each subject
        $subjectLessonsPlaced = [];
        foreach ($subjects as $subject) {
            $subjectLessonsPlaced[$subject->id] = [
                'quad' => 0,
                'triple' => 0,
                'double' => 0,
                'single' => 0,
            ];
        }
        
        // Distribute lessons across days, spacing out multi-period lessons
        foreach ($lessonPool as $lessonIndex => $lesson) {
            $subjectId = $lesson['id'];
            $durationPeriods = $lesson['duration_periods'];
            $teacherId = $lesson['teacher_id'];
            $lessonType = $lesson['type'];
            $hasMultiPeriod = $lesson['has_multi_period'] ?? false;
            
            // For multi-period lessons, try to space them across different days
            $preferredDays = $this->getPreferredDays($days, $subjectId, $usedSubjectsByDay, $durationPeriods);
            
            // STRICT CONSTRAINT: Maximum 1 lesson block per subject per day
            // This applies to ALL lesson types (single, double, triple, quad)
            // A double lesson counts as 1 block, not 2 separate lessons
            $maxBlocksPerDay = 1;
            
            $placed = false;
            foreach ($preferredDays as $day) {
                // HARD CONSTRAINT: Check if subject already has a block on this day
                if (in_array($subjectId, $usedSubjectsByDay[$day])) {
                    continue; // Skip this day - subject already scheduled
                }
                
                // Find consecutive available slots for this lesson
                $availableSlot = $this->findConsecutiveSlots(
                    $dayStructure[$day], 
                    $durationPeriods, 
                    $settings->subject_duration,
                    $teacherId,
                    $day,
                    $settings,
                    $subjectId,
                    $lessonType,
                    $hasMultiPeriod
                );
                
                if ($availableSlot !== null) {
                    // Mark slots as used - $availableSlot is now an array of actual indices
                    foreach ($availableSlot as $i => $slotIndex) {
                        $dayStructure[$day][$slotIndex]['subject_id'] = $subjectId;
                        $dayStructure[$day][$slotIndex]['teacher_id'] = $teacherId;
                        $dayStructure[$day][$slotIndex]['lesson_type'] = $lessonType;
                        $dayStructure[$day][$slotIndex]['is_continuation'] = ($i > 0);
                    }
                    
                    // STRICT CONSTRAINT: Mark subject as used for this day
                    // This ensures the subject cannot appear again on this day
                    // (whether consecutive or non-consecutive)
                    $usedSubjectsByDay[$day][] = $subjectId;
                    
                    // Track lessons placed by type
                    $subjectLessonsPlaced[$subjectId][$lessonType]++;
                    
                    $result->addPlacedLesson($lesson['name'], $day, $dayStructure[$day][$availableSlot[0]]['start_time']);
                    
                    $placed = true;
                    break;
                }
            }
            
            // If couldn't place, track as failure
            if (!$placed) {
                $reason = 'No available slots found';
                if (empty($preferredDays)) {
                    $reason = 'Subject already scheduled on all available days';
                } elseif (!$teacherId) {
                    $reason = 'No teacher assigned to subject';
                }
                $result->addFailure($lesson['name'], $reason);
            }
        }
        
        // Now create timetable records from the day structure
        // IMPORTANT: Store every slot as a separate record to ensure all days align
        foreach ($days as $day) {
            $slots = $dayStructure[$day];
            
            foreach ($slots as $slotOrder => $slot) {
                // Create the timetable record for every slot
                Timetable::create([
                    'class_id' => $settings->class_id,
                    'subject_id' => $slot['subject_id'] ?? null,
                    'teacher_id' => $slot['teacher_id'] ?? null,
                    'day' => $day,
                    'start_time' => $slot['start_time'],
                    'end_time' => $slot['end_time'],
                    'slot_type' => $slot['slot_type'],
                    'slot_name' => $slot['slot_name'] ?? null,
                    'slot_order' => $slotOrder,
                    'academic_year' => $settings->academic_year,
                    'term' => $settings->term,
                ]);
            }
        }
        
        return $result;
    }
    
    /**
     * Generate the basic time structure for a day (breaks, lunch, subject slots)
     * Break and Lunch are FIXED at their configured times and never move
     */
    private function generateDayStructure(TimetableSetting $settings, $day, $specialSlots = [])
    {
        $structure = [];
        
        // Parse all times as strings to avoid Carbon date issues
        $startTime = $settings->start_time;
        $endTime = $settings->end_time;
        $breakStart = $settings->break_start;
        $breakEnd = $settings->break_end;
        $lunchStart = $settings->lunch_start;
        $lunchEnd = $settings->lunch_end;
        $periodDuration = $settings->subject_duration;
        
        // Check for clubs on this day
        $clubsOnThisDay = false;
        $clubsStart = null;
        $clubsEnd = null;
        $clubsPeriods = 1;
        if (!empty($specialSlots['clubs']) && in_array($day, $specialSlots['clubs']['days'] ?? [])) {
            $clubsOnThisDay = true;
            $clubsStart = $specialSlots['clubs']['start'];
            $clubsEnd = $specialSlots['clubs']['end'];
            $clubsPeriods = $specialSlots['clubs']['periods'] ?? 2;
        }
        
        // Check for practicals on this day (STANDALONE - separate from clubs)
        // Practicals start after lunch automatically
        $practicalsOnThisDay = false;
        $practicalSubjects = [];
        $practicalPeriods = 0;
        $practicalsStart = null;
        $practicalsEnd = null;
        if (!empty($specialSlots['practicals'])) {
            $dayPeriods1 = $specialSlots['practicals']['day_periods1'] ?? null;
            $dayPeriods2 = $specialSlots['practicals']['day_periods2'] ?? null;
            $periodsDay1 = $specialSlots['practicals']['periods_day1'] ?? 2;
            $periodsDay2 = $specialSlots['practicals']['periods_day2'] ?? 4;
            
            if ($day === $dayPeriods1) {
                $practicalsOnThisDay = true;
                $practicalPeriods = $periodsDay1;
                $practicalSubjects = $specialSlots['practicals']['subjects'] ?? [];
                // Calculate start/end times automatically - start after lunch
                $practicalsStart = $lunchEnd;
                // Calculate end time: lunch_end + (periods × duration)
                $lunchEndParts = explode(':', $lunchEnd);
                $lunchEndMinutes = intval($lunchEndParts[0]) * 60 + intval($lunchEndParts[1]);
                $practicalsEndMinutes = $lunchEndMinutes + ($practicalPeriods * $periodDuration);
                $practicalsEnd = str_pad(floor($practicalsEndMinutes / 60), 2, '0', STR_PAD_LEFT) . ':' . str_pad($practicalsEndMinutes % 60, 2, '0', STR_PAD_LEFT);
            } elseif ($day === $dayPeriods2) {
                $practicalsOnThisDay = true;
                $practicalPeriods = $periodsDay2;
                $practicalSubjects = $specialSlots['practicals']['subjects'] ?? [];
                // Calculate start/end times automatically - start after lunch
                $practicalsStart = $lunchEnd;
                // Calculate end time: lunch_end + (periods × duration)
                $lunchEndParts = explode(':', $lunchEnd);
                $lunchEndMinutes = intval($lunchEndParts[0]) * 60 + intval($lunchEndParts[1]);
                $practicalsEndMinutes = $lunchEndMinutes + ($practicalPeriods * $periodDuration);
                $practicalsEnd = str_pad(floor($practicalsEndMinutes / 60), 2, '0', STR_PAD_LEFT) . ':' . str_pad($practicalsEndMinutes % 60, 2, '0', STR_PAD_LEFT);
            }
        }
        
        // Check for special slots on this day
        $beforeBreakName = null;
        $beforeBreakPeriods = 1;
        $afterBreakName = null;
        $afterBreakPeriods = 1;
        $afterLunchName = null;
        $afterLunchPeriods = 1;
        
        if (!empty($specialSlots['before_break']) && in_array($day, $specialSlots['before_break']['days'] ?? [])) {
            $beforeBreakName = $specialSlots['before_break']['name'];
            $beforeBreakPeriods = $specialSlots['before_break']['periods'] ?? 1;
        }
        if (!empty($specialSlots['after_break']) && in_array($day, $specialSlots['after_break']['days'] ?? [])) {
            $afterBreakName = $specialSlots['after_break']['name'];
            $afterBreakPeriods = $specialSlots['after_break']['periods'] ?? 1;
        }
        if (!empty($specialSlots['after_lunch']) && in_array($day, $specialSlots['after_lunch']['days'] ?? [])) {
            $afterLunchName = $specialSlots['after_lunch']['name'];
            $afterLunchPeriods = $specialSlots['after_lunch']['periods'] ?? 1;
        }
        
        // Convert times to minutes from midnight for easier comparison
        $toMinutes = function($time) {
            $parts = explode(':', $time);
            return intval($parts[0]) * 60 + intval($parts[1]);
        };
        
        $fromMinutes = function($minutes) {
            $h = str_pad(floor($minutes / 60), 2, '0', STR_PAD_LEFT);
            $m = str_pad($minutes % 60, 2, '0', STR_PAD_LEFT);
            return "{$h}:{$m}:00";
        };
        
        $startMins = $toMinutes($startTime);
        $endMins = $toMinutes($endTime);
        $breakStartMins = $toMinutes($breakStart);
        $breakEndMins = $toMinutes($breakEnd);
        $lunchStartMins = $toMinutes($lunchStart);
        $lunchEndMins = $toMinutes($lunchEnd);
        
        $currentMins = $startMins;
        
        // Clubs timing
        $clubsStartMins = $clubsOnThisDay ? $toMinutes($clubsStart) : null;
        $clubsEndMins = $clubsOnThisDay ? $toMinutes($clubsEnd) : null;
        
        // Practicals timing (STANDALONE)
        $practicalsStartMins = $practicalsOnThisDay ? $toMinutes($practicalsStart) : null;
        $practicalsEndMins = $practicalsOnThisDay ? $toMinutes($practicalsEnd) : null;
        
        // Track if we've added special slots
        $addedBeforeBreak = false;
        $addedAfterBreak = false;
        $addedAfterLunch = false;
        $addedClubs = false;
        $addedPracticals = false;
        
        while ($currentMins < $endMins) {
            // Check if we're at PRACTICALS time (STANDALONE - separate from clubs)
            if ($practicalsOnThisDay && !$addedPracticals && $currentMins >= $practicalsStartMins && $currentMins < $practicalsEndMins) {
                // Get ALL practical subjects with their teachers
                $practicalSubjectsData = Subject::with('teacher')->whereIn('id', $practicalSubjects)->get();
                
                // Add the specified number of practical periods (2 or 4)
                // Each practical subject gets its own slot at the same time (they share the time slot)
                $practicalSlotStart = $practicalsStartMins;
                for ($p = 0; $p < $practicalPeriods; $p++) {
                    $practicalSlotEnd = $practicalSlotStart + $periodDuration;
                    if ($practicalSlotEnd > $practicalsEndMins) {
                        $practicalSlotEnd = $practicalsEndMins;
                    }
                    
                    // Create a slot for EACH practical subject (all teachers share the same time)
                    foreach ($practicalSubjectsData as $practicalSubject) {
                        $structure[] = [
                            'start_time' => $fromMinutes($practicalSlotStart),
                            'end_time' => $fromMinutes($practicalSlotEnd),
                            'slot_type' => 'practical',
                            'slot_name' => 'Practicals',
                            'subject_id' => $practicalSubject->id,
                            'teacher_id' => $practicalSubject->teacher ? $practicalSubject->teacher_id : null,
                        ];
                    }
                    
                    $practicalSlotStart = $practicalSlotEnd;
                    if ($practicalSlotStart >= $practicalsEndMins) break;
                }
                
                $currentMins = $practicalsEndMins;
                $addedPracticals = true;
                continue;
            }
            
            // Check if we're at clubs time (if clubs is on this day)
            if ($clubsOnThisDay && !$addedClubs && $currentMins >= $clubsStartMins && $currentMins < $clubsEndMins) {
                // Regular clubs slots (practicals are now completely standalone)
                $clubSlotStart = $clubsStartMins;
                for ($p = 0; $p < $clubsPeriods; $p++) {
                    $clubSlotEnd = $clubSlotStart + $periodDuration;
                    if ($clubSlotEnd > $clubsEndMins) {
                        $clubSlotEnd = $clubsEndMins;
                    }
                    $structure[] = [
                        'start_time' => $fromMinutes($clubSlotStart),
                        'end_time' => $fromMinutes($clubSlotEnd),
                        'slot_type' => 'clubs',
                        'slot_name' => 'Clubs',
                        'subject_id' => null,
                        'teacher_id' => null,
                    ];
                    $clubSlotStart = $clubSlotEnd;
                    if ($clubSlotStart >= $clubsEndMins) break;
                }
                $currentMins = $clubsEndMins;
                $addedClubs = true;
                continue;
            }
            
            // Check if we need to add "before break" slot
            if ($beforeBreakName && !$addedBeforeBreak && $currentMins + $periodDuration >= $breakStartMins && $currentMins < $breakStartMins) {
                // Create multiple periods for before break slot
                $beforeBreakStart = $breakStartMins - ($periodDuration * $beforeBreakPeriods);
                if ($beforeBreakStart < $currentMins) $beforeBreakStart = $currentMins;
                
                for ($p = 0; $p < $beforeBreakPeriods; $p++) {
                    $slotEnd = $beforeBreakStart + $periodDuration;
                    if ($slotEnd > $breakStartMins) $slotEnd = $breakStartMins;
                    
                    $structure[] = [
                        'start_time' => $fromMinutes($beforeBreakStart),
                        'end_time' => $fromMinutes($slotEnd),
                        'slot_type' => 'special',
                        'slot_name' => $beforeBreakName,
                        'subject_id' => null,
                        'teacher_id' => null,
                    ];
                    $beforeBreakStart = $slotEnd;
                    if ($beforeBreakStart >= $breakStartMins) break;
                }
                $currentMins = $breakStartMins;
                $addedBeforeBreak = true;
                continue;
            }
            
            // Check if we're at break time
            if ($currentMins >= $breakStartMins && $currentMins < $breakEndMins) {
                $structure[] = [
                    'start_time' => $fromMinutes($breakStartMins),
                    'end_time' => $fromMinutes($breakEndMins),
                    'slot_type' => 'break',
                    'subject_id' => null,
                    'teacher_id' => null,
                ];
                $currentMins = $breakEndMins;
                
                // Add "after break" slot immediately after break
                if ($afterBreakName && !$addedAfterBreak) {
                    for ($p = 0; $p < $afterBreakPeriods; $p++) {
                        $afterBreakEnd = $currentMins + $periodDuration;
                        $structure[] = [
                            'start_time' => $fromMinutes($currentMins),
                            'end_time' => $fromMinutes($afterBreakEnd),
                            'slot_type' => 'special',
                            'slot_name' => $afterBreakName,
                            'subject_id' => null,
                            'teacher_id' => null,
                        ];
                        $currentMins = $afterBreakEnd;
                    }
                    $addedAfterBreak = true;
                }
                continue;
            }
            
            // Check if we're at lunch time
            if ($currentMins >= $lunchStartMins && $currentMins < $lunchEndMins) {
                $structure[] = [
                    'start_time' => $fromMinutes($lunchStartMins),
                    'end_time' => $fromMinutes($lunchEndMins),
                    'slot_type' => 'lunch',
                    'subject_id' => null,
                    'teacher_id' => null,
                ];
                $currentMins = $lunchEndMins;
                
                // Add "after lunch" slot immediately after lunch
                if ($afterLunchName && !$addedAfterLunch) {
                    for ($p = 0; $p < $afterLunchPeriods; $p++) {
                        $afterLunchEnd = $currentMins + $periodDuration;
                        $structure[] = [
                            'start_time' => $fromMinutes($currentMins),
                            'end_time' => $fromMinutes($afterLunchEnd),
                            'slot_type' => 'special',
                            'slot_name' => $afterLunchName,
                            'subject_id' => null,
                            'teacher_id' => null,
                        ];
                        $currentMins = $afterLunchEnd;
                    }
                    $addedAfterLunch = true;
                }
                continue;
            }
            
            // Calculate slot end time
            $slotEndMins = $currentMins + $periodDuration;
            
            // Check if slot would overlap with clubs
            if ($clubsOnThisDay && !$addedClubs && $currentMins < $clubsStartMins && $slotEndMins > $clubsStartMins) {
                if ($clubsStartMins - $currentMins >= 10) {
                    $structure[] = [
                        'start_time' => $fromMinutes($currentMins),
                        'end_time' => $fromMinutes($clubsStartMins),
                        'slot_type' => 'subject',
                        'subject_id' => null,
                        'teacher_id' => null,
                        'is_gap' => true,
                    ];
                }
                $currentMins = $clubsStartMins;
                continue;
            }
            
            // Check if slot would overlap with break
            if ($currentMins < $breakStartMins && $slotEndMins > $breakStartMins) {
                if ($breakStartMins - $currentMins >= 10) {
                    $structure[] = [
                        'start_time' => $fromMinutes($currentMins),
                        'end_time' => $fromMinutes($breakStartMins),
                        'slot_type' => 'subject',
                        'subject_id' => null,
                        'teacher_id' => null,
                        'is_gap' => true,
                    ];
                }
                $currentMins = $breakStartMins;
                continue;
            }
            
            // Check if slot would overlap with lunch
            if ($currentMins < $lunchStartMins && $slotEndMins > $lunchStartMins) {
                if ($lunchStartMins - $currentMins >= 10) {
                    $structure[] = [
                        'start_time' => $fromMinutes($currentMins),
                        'end_time' => $fromMinutes($lunchStartMins),
                        'slot_type' => 'subject',
                        'subject_id' => null,
                        'teacher_id' => null,
                        'is_gap' => true,
                    ];
                }
                $currentMins = $lunchStartMins;
                continue;
            }
            
            // Don't exceed end time
            if ($slotEndMins > $endMins) {
                if ($endMins - $currentMins >= 10) {
                    $structure[] = [
                        'start_time' => $fromMinutes($currentMins),
                        'end_time' => $fromMinutes($endMins),
                        'slot_type' => 'subject',
                        'subject_id' => null,
                        'teacher_id' => null,
                        'is_gap' => true,
                    ];
                }
                break;
            }
            
            // Create a normal subject slot
            $structure[] = [
                'start_time' => $fromMinutes($currentMins),
                'end_time' => $fromMinutes($slotEndMins),
                'slot_type' => 'subject',
                'subject_id' => null,
                'teacher_id' => null,
            ];
            
            $currentMins = $slotEndMins;
        }
        
        return $structure;
    }
    
    /**
     * Get preferred days for placing a lesson, prioritizing days where the subject hasn't been placed yet
     * STRICT CONSTRAINT: Only return days where the subject has NOT been scheduled
     */
    private function getPreferredDays($days, $subjectId, $usedSubjectsByDay, $durationPeriods)
    {
        // STRICT CONSTRAINT: Only consider days where this subject hasn't been placed
        $availableDays = [];
        
        foreach ($days as $day) {
            // Check if subject is already used on this day
            if (!in_array($subjectId, $usedSubjectsByDay[$day])) {
                $availableDays[] = $day;
            }
        }
        
        // Shuffle available days for variety in distribution
        shuffle($availableDays);
        
        return $availableDays;
    }
    
    /**
     * Find consecutive available slots that can fit a multi-period lesson
     * STRICT CONSTRAINT: Subject can only appear once per day as a consecutive block
     * Non-consecutive repetitions are strictly forbidden
     */
    private function findConsecutiveSlots($dayStructure, $periodsNeeded, $periodDuration, $teacherId, $day, $settings, $subjectId = null, $lessonType = 'single', $hasMultiPeriod = false, $forcePlace = false)
    {
        $subjectSlots = [];
        
        // Get indices of subject slots only (excluding gap slots which stay as free periods)
        foreach ($dayStructure as $index => $slot) {
            if ($slot['slot_type'] === 'subject' && $slot['subject_id'] === null) {
                // Skip gap slots - they should remain as free periods
                if (isset($slot['is_gap']) && $slot['is_gap']) {
                    continue;
                }
                $subjectSlots[] = $index;
            }
        }
        
        // STRICT CONSTRAINT: Check if this subject is ANYWHERE on this day already
        // This applies to ALL lesson types (single, double, triple, quad)
        // A subject may appear only ONCE per day as a single consecutive block
        if ($subjectId !== null) {
            foreach ($dayStructure as $slot) {
                if (isset($slot['subject_id']) && $slot['subject_id'] === $subjectId) {
                    // Subject already has a lesson block today - FORBIDDEN
                    // Return null to force trying another day
                    return null;
                }
            }
        }
        
        // Find consecutive available slots
        for ($i = 0; $i <= count($subjectSlots) - $periodsNeeded; $i++) {
            $consecutive = true;
            $startIndex = $subjectSlots[$i];
            
            // Check if the next N slots are consecutive and available
            for ($j = 0; $j < $periodsNeeded; $j++) {
                if ($i + $j >= count($subjectSlots)) {
                    $consecutive = false;
                    break;
                }
                
                $currentSlotIndex = $subjectSlots[$i + $j];
                
                // Check if this slot is already used
                if ($dayStructure[$currentSlotIndex]['subject_id'] !== null) {
                    $consecutive = false;
                    break;
                }
                
                // Check if slots are actually consecutive
                // Double lessons CAN span across break/lunch but NOT across other subjects
                if ($j > 0) {
                    $prevSlotIndex = $subjectSlots[$i + $j - 1];
                    $currSlotIndex = $subjectSlots[$i + $j];
                    
                    // If not directly adjacent, check what's in between
                    if ($currSlotIndex != $prevSlotIndex + 1) {
                        // Check if the gap contains ONLY break/lunch (which is allowed)
                        $gapIsOnlyBreakLunch = true;
                        for ($k = $prevSlotIndex + 1; $k < $currSlotIndex; $k++) {
                            if (!in_array($dayStructure[$k]['slot_type'], ['break', 'lunch'])) {
                                // There's something other than break/lunch in the gap
                                $gapIsOnlyBreakLunch = false;
                                break;
                            }
                        }
                        if (!$gapIsOnlyBreakLunch) {
                            // Gap contains subject slots - not truly consecutive
                            $consecutive = false;
                            break;
                        }
                        // If gap is only break/lunch, that's OK - continue checking
                    }
                }
            }
            
            if (!$consecutive) {
                continue;
            }
            
            // Check for teacher conflict across the entire time span
            if ($teacherId) {
                $startTime = $dayStructure[$startIndex]['start_time'];
                $endSlotIndex = $subjectSlots[$i + $periodsNeeded - 1];
                $endTime = Carbon::parse($dayStructure[$startIndex]['start_time'])
                    ->addMinutes($periodDuration * $periodsNeeded)
                    ->format('H:i:s');
                
                // Check against existing timetable records (other classes)
                $teacherHasConflict = Timetable::where('teacher_id', $teacherId)
                    ->where('day', $day)
                    ->where('academic_year', $settings->academic_year)
                    ->where('term', $settings->term)
                    ->where(function($query) use ($startTime, $endTime) {
                        $query->where('start_time', '<', $endTime)
                              ->where('end_time', '>', $startTime);
                    })
                    ->exists();
                
                if ($teacherHasConflict) {
                    continue;
                }
                
                // ALSO check against current day structure (e.g., practicals already in structure)
                // This prevents teachers from being assigned to regular slots when they have practicals
                foreach ($dayStructure as $existingSlot) {
                    if ($existingSlot['teacher_id'] == $teacherId && 
                        $existingSlot['start_time'] < $endTime && 
                        $existingSlot['end_time'] > $startTime) {
                        $teacherHasConflict = true;
                        break;
                    }
                }
                
                if ($teacherHasConflict) {
                    continue;
                }
            }
            
            // Return array of actual slot indices for multi-period lessons
            $slotIndices = [];
            for ($j = 0; $j < $periodsNeeded; $j++) {
                $slotIndices[] = $subjectSlots[$i + $j];
            }
            return $slotIndices;
        }
        
        return null;
    }

    public function checkConflicts(Request $request)
    {
        $teacherId = $request->teacher_id;
        $day = $request->day;
        $startTime = $request->start_time;
        $endTime = $request->end_time;
        $excludeId = $request->exclude_id;

        $hasConflict = Timetable::checkTeacherConflict($teacherId, $day, $startTime, $endTime, $excludeId);

        return response()->json(['has_conflict' => $hasConflict]);
    }

    /**
     * Clear timetables for a specific term and academic year
     */
    public function clear(Request $request)
    {
        $validated = $request->validate([
            'academic_year' => 'required|integer',
            'term' => 'required|integer|between:1,3',
            'class_id' => 'nullable|exists:grades,id',
        ]);

        $query = Timetable::where('academic_year', $validated['academic_year'])
            ->where('term', $validated['term']);

        // If a specific class is selected, only delete for that class
        if (!empty($validated['class_id'])) {
            $query->where('class_id', $validated['class_id']);
            $className = Grade::find($validated['class_id'])->class_name;
            $deletedCount = $query->delete();
            
            // Also delete the timetable settings for this class
            TimetableSetting::where('class_id', $validated['class_id'])
                ->where('academic_year', $validated['academic_year'])
                ->where('term', $validated['term'])
                ->delete();
            
            return redirect()->route('admin.timetable.index')
                ->with('success', "Cleared {$deletedCount} timetable records for {$className} (Term {$validated['term']}, {$validated['academic_year']})");
        }

        // Delete all timetables for the term and year
        $deletedCount = $query->delete();
        
        // Also delete the timetable settings
        TimetableSetting::where('academic_year', $validated['academic_year'])
            ->where('term', $validated['term'])
            ->delete();

        return redirect()->route('admin.timetable.index')
            ->with('success', "Cleared {$deletedCount} timetable records for all classes (Term {$validated['term']}, {$validated['academic_year']})");
    }

    /**
     * Generate practical day combinations dynamically
     * Days are spread apart (not consecutive) to allow flexibility
     */
    private function generatePracticalDayCombinations(): array
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $combinations = [];
        
        for ($i = 0; $i < count($days); $i++) {
            for ($j = $i + 2; $j < count($days); $j++) {
                $combinations[] = [
                    'day_periods1' => $days[$i],
                    'day_periods2' => $days[$j]
                ];
            }
        }
        
        return $combinations;
    }

    /**
     * Assign practical days to a class based on index
     * Cycles through available combinations to distribute across classes
     */
    private function assignPracticalDays(int $classIndex): array
    {
        $combinations = $this->generatePracticalDayCombinations();
        $index = $classIndex % count($combinations);
        return $combinations[$index];
    }
}
