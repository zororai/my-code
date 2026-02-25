<?php

namespace App\Http\Controllers;

use App\SchemeOfWork;
use App\SchemeTopic;
use App\SyllabusTopic;
use App\RemedialLesson;
use App\TopicPerformanceSnapshot;
use App\Grade;
use App\Subject;
use App\Assessment;
use App\Services\TopicPerformanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SchemeController extends Controller
{
    protected $performanceService;

    public function __construct(TopicPerformanceService $performanceService)
    {
        $this->performanceService = $performanceService;
    }

    /**
     * Display schemes dashboard for teacher
     */
    public function index()
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return redirect()->route('home')->with('error', 'Teacher profile not found.');
        }

        // Get teacher's classes
        $classTeacherClasses = $teacher->classes()->get();
        $teacherSubjectIds = $teacher->subjects->pluck('id')->toArray();
        $subjectClasses = Grade::whereHas('subjects', function($query) use ($teacherSubjectIds) {
            $query->whereIn('subjects.id', $teacherSubjectIds);
        })->get();
        $classes = $classTeacherClasses->merge($subjectClasses)->unique('id');

        // Get schemes for this teacher
        $schemes = SchemeOfWork::where('teacher_id', $teacher->id)
            ->with(['subject', 'class', 'schemeTopics.syllabusTopic'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Calculate assessment counts for each scheme
        foreach ($schemes as $scheme) {
            $syllabusTopicIds = $scheme->schemeTopics->pluck('syllabus_topic_id')->filter()->toArray();
            $scheme->assessments_count = Assessment::where('class_id', $scheme->class_id)
                ->where('subject_id', $scheme->subject_id)
                ->whereIn('syllabus_topic_id', $syllabusTopicIds)
                ->count();
            
            // Calculate average performance from linked assessments
            $avgPerformance = Assessment::where('class_id', $scheme->class_id)
                ->where('subject_id', $scheme->subject_id)
                ->whereIn('syllabus_topic_id', $syllabusTopicIds)
                ->whereHas('marks')
                ->with('marks')
                ->get()
                ->flatMap(function($assessment) {
                    return $assessment->marks->map(function($mark) use ($assessment) {
                        $papers = $assessment->papers ?? [];
                        $totalPossible = collect($papers)->sum('total_marks');
                        if ($totalPossible > 0) {
                            $studentTotal = 0;
                            foreach ($papers as $index => $paper) {
                                $paperMark = $mark->marks[$index] ?? 0;
                                $studentTotal += $paperMark;
                            }
                            return ($studentTotal / $totalPossible) * 100;
                        }
                        return null;
                    });
                })
                ->filter()
                ->avg();
            
            $scheme->linked_performance = $avgPerformance ? round($avgPerformance, 1) : null;
        }

        // Get current term/year (you might want to get this from settings)
        $currentTerm = 'Term 1';
        $currentYear = date('Y');

        // Get weak topics summary
        $weakTopics = $this->performanceService->getWeakTopicsForTeacher(
            $teacher->id,
            $currentTerm,
            $currentYear
        );

        return view('backend.teacher.schemes.index', compact(
            'schemes',
            'classes',
            'teacher',
            'weakTopics',
            'currentTerm',
            'currentYear'
        ));
    }

    /**
     * Show form to create a new scheme
     */
    public function create(Request $request)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return redirect()->route('home')->with('error', 'Teacher profile not found.');
        }

        $classId = $request->get('class_id');
        $subjectId = $request->get('subject_id');

        // Get teacher's classes
        $classTeacherClasses = $teacher->classes()->get();
        $teacherSubjectIds = $teacher->subjects->pluck('id')->toArray();
        $subjectClasses = Grade::whereHas('subjects', function($query) use ($teacherSubjectIds) {
            $query->whereIn('subjects.id', $teacherSubjectIds);
        })->get();
        $classes = $classTeacherClasses->merge($subjectClasses)->unique('id');

        // Get subjects taught by teacher
        $subjects = $teacher->subjects;

        // Get syllabus topics if subject is selected
        $syllabusTopics = [];
        $historicalPerformance = [];

        if ($subjectId) {
            $syllabusTopics = SyllabusTopic::where('subject_id', $subjectId)
                ->active()
                ->ordered()
                ->get();

            // Get historical performance for each topic
            foreach ($syllabusTopics as $topic) {
                $historicalPerformance[$topic->id] = [
                    'history' => $this->performanceService->getTopicHistoricalPerformance($topic->id, $classId),
                    'suggested_periods' => $this->performanceService->getSuggestedPeriods($topic->id, $classId)
                ];
            }
        }

        $terms = ['Term 1', 'Term 2', 'Term 3'];
        $academicYears = [date('Y'), date('Y') + 1];

        return view('backend.teacher.schemes.create', compact(
            'classes',
            'subjects',
            'syllabusTopics',
            'historicalPerformance',
            'terms',
            'academicYears',
            'classId',
            'subjectId',
            'teacher'
        ));
    }

    /**
     * Generate printable version of scheme
     */
    public function print($id)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return redirect()->route('home')->with('error', 'Teacher profile not found.');
        }

        $scheme = SchemeOfWork::with([
            'subject',
            'class',
            'teacher.user',
            'schemeTopics.syllabusTopic'
        ])->findOrFail($id);

        // Authorization check
        if ($scheme->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized access to this scheme.');
        }

        // Get school information
        $schoolName = \App\SchoolSetting::get('school_name', 'School Name');
        $schoolAddress = \App\SchoolSetting::get('school_address', '');
        $schoolLogo = \App\SchoolSetting::get('school_logo', '');

        return view('backend.teacher.schemes.print', compact(
            'scheme',
            'schoolName',
            'schoolAddress',
            'schoolLogo'
        ));
    }

    /**
     * Auto-generate scheme based on weak assessment performance
     */
    public function autoGenerateFromAssessments(Request $request)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Teacher profile not found.'], 403);
        }

        $classId = $request->get('class_id');
        $subjectId = $request->get('subject_id');
        $term = $request->get('term', 'Term 1');

        if (!$classId || !$subjectId) {
            return response()->json(['success' => false, 'message' => 'Class and subject are required.'], 400);
        }

        // Get all syllabus topics for this subject
        $syllabusTopics = SyllabusTopic::where('subject_id', $subjectId)
            ->active()
            ->ordered()
            ->get();

        // Get assessments for this class/subject
        $assessments = Assessment::where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->whereNotNull('syllabus_topic_id')
            ->with(['marks', 'syllabusTopic'])
            ->get();

        // Calculate average performance per topic
        $topicPerformance = [];
        foreach ($assessments as $assessment) {
            if (!$assessment->syllabus_topic_id) continue;

            $marks = $assessment->marks;
            if ($marks->isEmpty()) continue;

            $papers = $assessment->papers ?? [];
            $totalPossible = collect($papers)->sum('total_marks');

            if ($totalPossible > 0) {
                $averagePercentage = $marks->map(function($mark) use ($papers) {
                    $studentTotal = 0;
                    foreach ($papers as $index => $paper) {
                        $studentTotal += $mark->mark ?? 0;
                    }
                    return ($studentTotal / collect($papers)->sum('total_marks')) * 100;
                })->avg();

                if (!isset($topicPerformance[$assessment->syllabus_topic_id])) {
                    $topicPerformance[$assessment->syllabus_topic_id] = [];
                }
                $topicPerformance[$assessment->syllabus_topic_id][] = $averagePercentage;
            }
        }

        // Calculate average and identify weak topics (below 60%)
        $weakTopics = [];
        foreach ($topicPerformance as $topicId => $performances) {
            $avgPerformance = collect($performances)->avg();
            if ($avgPerformance < 60) {
                $topic = $syllabusTopics->firstWhere('id', $topicId);
                if ($topic) {
                    $weakTopics[] = [
                        'id' => $topic->id,
                        'name' => $topic->name,
                        'average_score' => round($avgPerformance, 1),
                        'suggested_periods' => $topic->suggested_periods,
                        'difficulty_level' => $topic->difficulty_level,
                        'reason' => 'Low performance: ' . round($avgPerformance, 1) . '%'
                    ];
                }
            }
        }

        // Sort by performance (weakest first)
        usort($weakTopics, function($a, $b) {
            return $a['average_score'] <=> $b['average_score'];
        });

        return response()->json([
            'success' => true,
            'weak_topics' => $weakTopics,
            'total_assessed' => count($topicPerformance),
            'message' => count($weakTopics) > 0 
                ? count($weakTopics) . ' weak topics identified based on assessment data.'
                : 'No weak topics found. All topics performing above 60%.'
        ]);
    }

    /**
     * Get student assessment data for quick marks entry
     */
    public function getStudentAssessments(Request $request)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Teacher profile not found.'], 403);
        }

        $classId = $request->get('class_id');
        $subjectId = $request->get('subject_id');

        if (!$classId || !$subjectId) {
            return response()->json(['success' => false, 'message' => 'Class and subject are required.'], 400);
        }

        // Get students in the class
        $class = Grade::with('students')->find($classId);
        if (!$class) {
            return response()->json(['success' => false, 'message' => 'Class not found.'], 404);
        }

        // Get recent assessments for this class/subject
        $assessments = Assessment::where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->with(['syllabusTopic', 'marks'])
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();

        $students = $class->students->map(function($student) use ($assessments) {
            $studentMarks = [];
            foreach ($assessments as $assessment) {
                $mark = $assessment->marks->firstWhere('student_id', $student->id);
                $studentMarks[] = [
                    'assessment_id' => $assessment->id,
                    'assessment_name' => $assessment->topic,
                    'mark' => $mark ? $mark->mark : null,
                    'total_marks' => $mark ? $mark->total_marks : null,
                    'percentage' => $mark && $mark->total_marks > 0 
                        ? round(($mark->mark / $mark->total_marks) * 100, 1) 
                        : null
                ];
            }

            return [
                'id' => $student->id,
                'name' => $student->user->name ?? 'Unknown',
                'admission_number' => $student->admission_number,
                'marks' => $studentMarks
            ];
        });

        return response()->json([
            'success' => true,
            'students' => $students,
            'assessments' => $assessments->map(function($assessment) {
                return [
                    'id' => $assessment->id,
                    'topic' => $assessment->topic,
                    'syllabus_topic_id' => $assessment->syllabus_topic_id,
                    'date' => $assessment->date ? $assessment->date->format('Y-m-d') : null,
                    'papers' => $assessment->papers
                ];
            })
        ]);
    }

    /**
     * Store a new scheme
     */
    public function store(Request $request)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return redirect()->route('home')->with('error', 'Teacher profile not found.');
        }

        $validated = $request->validate([
            'class_id' => 'required|exists:grades,id',
            'subject_id' => 'required|exists:subjects,id',
            'term' => 'required|string',
            'academic_year' => 'required|string',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'expected_performance' => 'nullable|numeric|min:0|max:100',
            'topics' => 'required|array|min:1',
            'topics.*.syllabus_topic_id' => 'required|exists:syllabus_topics,id',
            'topics.*.week_number' => 'nullable|integer|min:1',
            'topics.*.planned_periods' => 'required|integer|min:1',
            'topics.*.expected_performance' => 'nullable|numeric|min:0|max:100',
            'topics.*.teaching_methods' => 'nullable|string',
            'topics.*.resources' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            // Create the scheme
            $scheme = SchemeOfWork::create([
                'teacher_id' => $teacher->id,
                'class_id' => $request->class_id,
                'subject_id' => $request->subject_id,
                'term' => $request->term,
                'academic_year' => $request->academic_year,
                'title' => $request->title,
                'description' => $request->description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'expected_performance' => $request->expected_performance,
                'status' => 'draft'
            ]);

            // Add scheme topics
            $totalPlannedPeriods = 0;
            $orderIndex = 0;

            foreach ($request->topics as $topicData) {
                SchemeTopic::create([
                    'scheme_id' => $scheme->id,
                    'syllabus_topic_id' => $topicData['syllabus_topic_id'],
                    'week_number' => $topicData['week_number'] ?? null,
                    'planned_periods' => $topicData['planned_periods'],
                    'expected_performance' => $topicData['expected_performance'] ?? null,
                    'teaching_methods' => $topicData['teaching_methods'] ?? null,
                    'resources' => $topicData['resources'] ?? null,
                    'order_index' => $orderIndex++
                ]);

                $totalPlannedPeriods += $topicData['planned_periods'];
            }

            $scheme->total_planned_periods = $totalPlannedPeriods;
            $scheme->save();

            DB::commit();

            return redirect()->route('teacher.schemes.show', $scheme->id)
                ->with('success', 'Scheme of work created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create scheme: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create scheme. Please try again.');
        }
    }

    /**
     * Display a scheme with evaluation data
     */
    public function show($id)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return redirect()->route('home')->with('error', 'Teacher profile not found.');
        }

        $scheme = SchemeOfWork::where('id', $id)
            ->where('teacher_id', $teacher->id)
            ->with(['subject', 'class', 'schemeTopics.syllabusTopic', 'schemeTopics.remedialLessons'])
            ->firstOrFail();

        // Recalculate performance data
        $this->performanceService->recalculateSchemePerformance($scheme);
        $scheme->refresh();

        // Get topic heatmap data
        $heatmapData = $this->performanceService->getClassTopicHeatmap(
            $scheme->class_id,
            $scheme->subject_id,
            $scheme->term,
            $scheme->academic_year
        );

        // Get remedial lessons for this scheme
        $remedialLessons = RemedialLesson::whereIn('scheme_topic_id', $scheme->schemeTopics->pluck('id'))
            ->with(['syllabusTopic', 'students'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('backend.teacher.schemes.show', compact(
            'scheme',
            'heatmapData',
            'remedialLessons',
            'teacher'
        ));
    }

    /**
     * Show form to edit a scheme
     */
    public function edit($id)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return redirect()->route('home')->with('error', 'Teacher profile not found.');
        }

        $scheme = SchemeOfWork::where('id', $id)
            ->where('teacher_id', $teacher->id)
            ->with(['subject', 'class', 'schemeTopics.syllabusTopic'])
            ->firstOrFail();

        // Get teacher's classes
        $classTeacherClasses = $teacher->classes()->get();
        $teacherSubjectIds = $teacher->subjects->pluck('id')->toArray();
        $subjectClasses = Grade::whereHas('subjects', function($query) use ($teacherSubjectIds) {
            $query->whereIn('subjects.id', $teacherSubjectIds);
        })->get();
        $classes = $classTeacherClasses->merge($subjectClasses)->unique('id');

        $subjects = $teacher->subjects;

        $syllabusTopics = SyllabusTopic::where('subject_id', $scheme->subject_id)
            ->active()
            ->ordered()
            ->get();

        // Get historical performance for each topic
        $historicalPerformance = [];
        foreach ($syllabusTopics as $topic) {
            $historicalPerformance[$topic->id] = [
                'history' => $this->performanceService->getTopicHistoricalPerformance($topic->id, $scheme->class_id),
                'suggested_periods' => $this->performanceService->getSuggestedPeriods($topic->id, $scheme->class_id)
            ];
        }

        $terms = ['Term 1', 'Term 2', 'Term 3'];
        $academicYears = [date('Y'), date('Y') + 1];

        return view('backend.teacher.schemes.edit', compact(
            'scheme',
            'classes',
            'subjects',
            'syllabusTopics',
            'historicalPerformance',
            'terms',
            'academicYears',
            'teacher'
        ));
    }

    /**
     * Update a scheme
     */
    public function update(Request $request, $id)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return redirect()->route('home')->with('error', 'Teacher profile not found.');
        }

        $scheme = SchemeOfWork::where('id', $id)
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'expected_performance' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|in:draft,active,completed,archived',
            'topics' => 'required|array|min:1',
            'topics.*.id' => 'nullable|exists:scheme_topics,id',
            'topics.*.syllabus_topic_id' => 'required|exists:syllabus_topics,id',
            'topics.*.week_number' => 'nullable|integer|min:1',
            'topics.*.planned_periods' => 'required|integer|min:1',
            'topics.*.actual_periods' => 'nullable|integer|min:0',
            'topics.*.expected_performance' => 'nullable|numeric|min:0|max:100',
            'topics.*.teaching_methods' => 'nullable|string',
            'topics.*.resources' => 'nullable|string',
            'topics.*.remarks' => 'nullable|string',
            'topics.*.status' => 'nullable|in:pending,in_progress,completed,needs_remedial'
        ]);

        DB::beginTransaction();
        try {
            $scheme->update([
                'title' => $request->title,
                'description' => $request->description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'expected_performance' => $request->expected_performance,
                'status' => $request->status
            ]);

            // Get existing topic IDs
            $existingTopicIds = $scheme->schemeTopics->pluck('id')->toArray();
            $updatedTopicIds = [];

            $totalPlannedPeriods = 0;
            $totalActualPeriods = 0;
            $orderIndex = 0;

            foreach ($request->topics as $topicData) {
                if (!empty($topicData['id'])) {
                    // Update existing topic
                    $schemeTopic = SchemeTopic::find($topicData['id']);
                    if ($schemeTopic && $schemeTopic->scheme_id == $scheme->id) {
                        $schemeTopic->update([
                            'week_number' => $topicData['week_number'] ?? null,
                            'planned_periods' => $topicData['planned_periods'],
                            'actual_periods' => $topicData['actual_periods'] ?? 0,
                            'expected_performance' => $topicData['expected_performance'] ?? null,
                            'teaching_methods' => $topicData['teaching_methods'] ?? null,
                            'resources' => $topicData['resources'] ?? null,
                            'remarks' => $topicData['remarks'] ?? null,
                            'status' => $topicData['status'] ?? 'pending',
                            'order_index' => $orderIndex++
                        ]);
                        $updatedTopicIds[] = $schemeTopic->id;
                    }
                } else {
                    // Create new topic
                    $schemeTopic = SchemeTopic::create([
                        'scheme_id' => $scheme->id,
                        'syllabus_topic_id' => $topicData['syllabus_topic_id'],
                        'week_number' => $topicData['week_number'] ?? null,
                        'planned_periods' => $topicData['planned_periods'],
                        'actual_periods' => $topicData['actual_periods'] ?? 0,
                        'expected_performance' => $topicData['expected_performance'] ?? null,
                        'teaching_methods' => $topicData['teaching_methods'] ?? null,
                        'resources' => $topicData['resources'] ?? null,
                        'remarks' => $topicData['remarks'] ?? null,
                        'status' => $topicData['status'] ?? 'pending',
                        'order_index' => $orderIndex++
                    ]);
                    $updatedTopicIds[] = $schemeTopic->id;
                }

                $totalPlannedPeriods += $topicData['planned_periods'];
                $totalActualPeriods += $topicData['actual_periods'] ?? 0;
            }

            // Delete removed topics
            $topicsToDelete = array_diff($existingTopicIds, $updatedTopicIds);
            SchemeTopic::whereIn('id', $topicsToDelete)->delete();

            // Update scheme totals
            $scheme->total_planned_periods = $totalPlannedPeriods;
            $scheme->total_actual_periods = $totalActualPeriods;
            $scheme->save();

            // Recalculate performance
            $this->performanceService->recalculateSchemePerformance($scheme);

            DB::commit();

            return redirect()->route('teacher.schemes.show', $scheme->id)
                ->with('success', 'Scheme updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update scheme: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update scheme. Please try again.');
        }
    }

    /**
     * Delete a scheme
     */
    public function destroy($id)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Teacher profile not found.'], 403);
        }

        $scheme = SchemeOfWork::where('id', $id)
            ->where('teacher_id', $teacher->id)
            ->first();

        if (!$scheme) {
            return response()->json(['success' => false, 'message' => 'Scheme not found.'], 404);
        }

        $scheme->delete();

        return response()->json(['success' => true, 'message' => 'Scheme deleted successfully!']);
    }

    /**
     * Update scheme topic status (AJAX)
     */
    public function updateTopicStatus(Request $request, $topicId)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Teacher profile not found.'], 403);
        }

        $schemeTopic = SchemeTopic::with('scheme')->find($topicId);

        if (!$schemeTopic || $schemeTopic->scheme->teacher_id != $teacher->id) {
            return response()->json(['success' => false, 'message' => 'Topic not found.'], 404);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed,needs_remedial',
            'actual_periods' => 'nullable|integer|min:0',
            'remarks' => 'nullable|string'
        ]);

        $schemeTopic->update($validated);

        // Recalculate performance
        $this->performanceService->updateSchemeTopicPerformance($schemeTopic);

        // Check for remedial trigger
        if ($schemeTopic->remedial_required) {
            $this->performanceService->checkAndTriggerRemedial($schemeTopic);
        }

        // Update scheme totals
        $schemeTopic->scheme->recalculateTotals();

        return response()->json([
            'success' => true,
            'message' => 'Topic status updated!',
            'topic' => $schemeTopic->fresh()
        ]);
    }

    /**
     * Get syllabus topics for a subject (AJAX)
     */
    public function getSyllabusTopics(Request $request)
    {
        $subjectId = $request->get('subject_id');
        $classId = $request->get('class_id');

        $topics = SyllabusTopic::where('subject_id', $subjectId)
            ->active()
            ->ordered()
            ->get();

        $topicsWithPerformance = $topics->map(function ($topic) use ($classId) {
            return [
                'id' => $topic->id,
                'name' => $topic->name,
                'description' => $topic->description,
                'suggested_periods' => $this->performanceService->getSuggestedPeriods($topic->id, $classId),
                'difficulty_level' => $topic->difficulty_level,
                'term' => $topic->term,
                'historical_performance' => $this->performanceService->getTopicHistoricalPerformance($topic->id, $classId)
            ];
        });

        return response()->json([
            'success' => true,
            'topics' => $topicsWithPerformance
        ]);
    }

    /**
     * Create a remedial lesson for a topic
     */
    public function createRemedial(Request $request, $topicId)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return redirect()->route('home')->with('error', 'Teacher profile not found.');
        }

        $schemeTopic = SchemeTopic::with('scheme', 'syllabusTopic')->find($topicId);

        if (!$schemeTopic || $schemeTopic->scheme->teacher_id != $teacher->id) {
            return redirect()->back()->with('error', 'Topic not found.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'objectives' => 'nullable|string',
            'scheduled_date' => 'required|date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'duration_minutes' => 'required|integer|min:15',
            'resources' => 'nullable|string',
            'student_ids' => 'nullable|array',
            'student_ids.*' => 'exists:students,id'
        ]);

        $scheme = $schemeTopic->scheme;

        $remedialLesson = RemedialLesson::create([
            'syllabus_topic_id' => $schemeTopic->syllabus_topic_id,
            'scheme_topic_id' => $schemeTopic->id,
            'class_id' => $scheme->class_id,
            'subject_id' => $scheme->subject_id,
            'teacher_id' => $teacher->id,
            'title' => $request->title,
            'description' => $request->description,
            'objectives' => $request->objectives,
            'scheduled_date' => $request->scheduled_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'duration_minutes' => $request->duration_minutes,
            'resources' => $request->resources,
            'status' => 'scheduled',
            'trigger_type' => 'manual',
            'pre_remedial_avg' => $schemeTopic->actual_performance
        ]);

        // Attach students if provided
        if ($request->has('student_ids') && is_array($request->student_ids)) {
            $remedialLesson->students()->attach($request->student_ids);
        }

        return redirect()->route('teacher.schemes.show', $scheme->id)
            ->with('success', 'Remedial lesson created successfully!');
    }

    /**
     * Mark remedial lesson as completed
     */
    public function completeRemedial(Request $request, $remedialId)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Teacher profile not found.'], 403);
        }

        $remedial = RemedialLesson::where('id', $remedialId)
            ->where('teacher_id', $teacher->id)
            ->first();

        if (!$remedial) {
            return response()->json(['success' => false, 'message' => 'Remedial lesson not found.'], 404);
        }

        $validated = $request->validate([
            'notes' => 'nullable|string',
            'student_attendance' => 'nullable|array',
            'student_attendance.*.student_id' => 'exists:students,id',
            'student_attendance.*.attended' => 'boolean',
            'student_attendance.*.post_score' => 'nullable|numeric|min:0|max:100'
        ]);

        // Update student attendance and scores
        if ($request->has('student_attendance')) {
            foreach ($request->student_attendance as $attendance) {
                $remedial->students()->updateExistingPivot($attendance['student_id'], [
                    'attended' => $attendance['attended'] ?? false,
                    'post_score' => $attendance['post_score'] ?? null
                ]);
            }
        }

        $remedial->notes = $request->notes;
        $remedial->markCompleted();

        return response()->json([
            'success' => true,
            'message' => 'Remedial lesson marked as completed!',
            'improvement' => $remedial->improvement
        ]);
    }

    /**
     * Get scheme evaluation report data
     */
    public function evaluationReport($id)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return redirect()->route('home')->with('error', 'Teacher profile not found.');
        }

        $scheme = SchemeOfWork::where('id', $id)
            ->where('teacher_id', $teacher->id)
            ->with(['subject', 'class', 'schemeTopics.syllabusTopic', 'schemeTopics.remedialLessons.students'])
            ->firstOrFail();

        // Recalculate all performance data
        $this->performanceService->recalculateSchemePerformance($scheme);
        $scheme->refresh();

        // Prepare report data
        $reportData = [
            'scheme' => $scheme,
            'summary' => [
                'total_topics' => $scheme->topics_count,
                'completed_topics' => $scheme->completed_topics_count,
                'weak_topics' => $scheme->weak_topics_count,
                'needs_remedial' => $scheme->needs_remedial_count,
                'progress_percentage' => $scheme->progress_percentage,
                'performance_gap' => $scheme->performance_gap
            ],
            'topics' => $scheme->schemeTopics->map(function ($topic) {
                return [
                    'name' => $topic->syllabusTopic->name ?? 'Unknown',
                    'planned_periods' => $topic->planned_periods,
                    'actual_periods' => $topic->actual_periods,
                    'expected_performance' => $topic->expected_performance,
                    'actual_performance' => $topic->actual_performance,
                    'performance_gap' => $topic->performance_gap,
                    'mastery_level' => $topic->mastery_level,
                    'status' => $topic->status,
                    'remedial_required' => $topic->remedial_required,
                    'remedials_count' => $topic->remedialLessons->count()
                ];
            }),
            'heatmap' => $this->performanceService->getClassTopicHeatmap(
                $scheme->class_id,
                $scheme->subject_id,
                $scheme->term,
                $scheme->academic_year
            )
        ];

        return view('backend.teacher.schemes.evaluation-report', compact('reportData', 'teacher'));
    }
}
