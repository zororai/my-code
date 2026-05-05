<?php

namespace App\Http\Controllers;

use App\Grade;
use App\Parents;
use App\Student;
use App\Teacher;
use App\Subject;
use App\Result;
use App\Assessment;
use App\AssessmentMark;
use App\Attendance;
use App\ResultsStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Str;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->hasRole('Admin')) {

            $parents = Parents::latest()->get();
            $teachers = Teacher::latest()->get();
            $students = Student::latest()->get();
            $subjects = Subject::latest()->get();
            $classes = Grade::latest()->get();

            // Get available terms for the switcher
            $availableTerms = ResultsStatus::select('id', 'year', 'result_period')
                ->orderBy('year', 'desc')
                ->orderByRaw("CASE result_period WHEN 'third' THEN 3 WHEN 'second' THEN 2 WHEN 'first' THEN 1 ELSE 0 END DESC")
                ->get()
                ->map(function($term) {
                    $periodLabels = ['first' => 'Term 1', 'second' => 'Term 2', 'third' => 'Term 3'];
                    return [
                        'id'     => $term->id,
                        'year'   => $term->year,
                        'period' => $term->result_period,
                        'label'  => ($periodLabels[$term->result_period] ?? ucfirst($term->result_period)) . ' ' . $term->year,
                    ];
                });

            // Determine current term — request params override automatic detection
            $latestTerm = ResultsStatus::orderBy('year', 'desc')
                ->orderByRaw("CASE result_period WHEN 'third' THEN 3 WHEN 'second' THEN 2 WHEN 'first' THEN 1 ELSE 0 END DESC")
                ->first();

            if ($request->filled('term_id')) {
                $currentTerm = ResultsStatus::find($request->term_id) ?? $latestTerm;
            } else {
                $currentTerm = $latestTerm;
            }

            $currentYear   = $currentTerm ? $currentTerm->year        : date('Y');
            $currentPeriod = $currentTerm ? $currentTerm->result_period : 'first';

            // Get pass/fail results by gender for current term (pass = marks >= 50)
            $resultsByGender = DB::table('results')
                ->join('students', 'results.student_id', '=', 'students.id')
                ->where('results.year', $currentYear)
                ->where('results.result_period', $currentPeriod)
                ->select(
                    'students.gender',
                    DB::raw('SUM(CASE WHEN results.marks >= 50 THEN 1 ELSE 0 END) as pass_count'),
                    DB::raw('SUM(CASE WHEN results.marks < 50 THEN 1 ELSE 0 END) as fail_count'),
                    DB::raw('COUNT(*) as total_count')
                )
                ->groupBy('students.gender')
                ->get();

            $malePass = 0;
            $maleFail = 0;
            $femalePass = 0;
            $femaleFail = 0;

            foreach ($resultsByGender as $row) {
                if (strtolower($row->gender) === 'male') {
                    $malePass = $row->pass_count;
                    $maleFail = $row->fail_count;
                } elseif (strtolower($row->gender) === 'female') {
                    $femalePass = $row->pass_count;
                    $femaleFail = $row->fail_count;
                }
            }

            $genderStats = [
                'malePass' => $malePass,
                'maleFail' => $maleFail,
                'femalePass' => $femalePass,
                'femaleFail' => $femaleFail,
            ];

            // Get classroom population data with gender breakdown
            $classroomPopulation = Grade::with(['students'])
                ->orderBy('class_numeric')
                ->get()
                ->map(function($class) {
                    $maleCount = $class->students->whereIn('gender', ['Male', 'male', 'M', 'm'])->count();
                    $femaleCount = $class->students->whereIn('gender', ['Female', 'female', 'F', 'f'])->count();
                    return [
                        'name' => $class->class_name,
                        'count' => $class->students->count(),
                        'male' => $maleCount,
                        'female' => $femaleCount
                    ];
                });

            // Get assessment statistics by type for admin (filtered by current term)
            $assessmentTypes = ['Quiz', 'Test', 'In Class Test', 'Monthly Test', 'Assignment', 'Exercise', 'Project', 'Fort Night', 'Exam', 'Vacation Exam', 'National Exam'];
            $assessmentStats = [];

            foreach ($assessmentTypes as $type) {
                $assessments = Assessment::where('assessment_type', $type)
                    ->where('academic_year', $currentYear)
                    ->where('term', $currentPeriod)
                    ->get();
                $totalGiven = $assessments->count();

                $totalMarks = 0;
                $obtainedMarks = 0;
                $marksCount = 0;

                foreach ($assessments as $assessment) {
                    $marks = AssessmentMark::where('assessment_id', $assessment->id)->get();
                    foreach ($marks as $mark) {
                        if ($mark->mark !== null && $mark->total_marks > 0) {
                            $totalMarks += $mark->total_marks;
                            $obtainedMarks += $mark->mark;
                            $marksCount++;
                        }
                    }
                }

                $performance = $totalMarks > 0 ? round(($obtainedMarks / $totalMarks) * 100, 1) : 0;

                $assessmentStats[] = [
                    'type' => $type,
                    'given' => $totalGiven,
                    'performance' => $performance
                ];
            }

            // Get assessment performance by type AND gender (filtered by current term)
            $assessmentStatsByGender = [];
            foreach ($assessmentTypes as $type) {
                $assessments = Assessment::where('assessment_type', $type)
                    ->where('academic_year', $currentYear)
                    ->where('term', $currentPeriod)
                    ->pluck('id');
                
                // Get marks with student gender information
                $marksByGender = DB::table('assessment_marks')
                    ->join('students', 'assessment_marks.student_id', '=', 'students.id')
                    ->whereIn('assessment_marks.assessment_id', $assessments)
                    ->whereNotNull('assessment_marks.mark')
                    ->where('assessment_marks.total_marks', '>', 0)
                    ->select(
                        'students.gender',
                        DB::raw('SUM(assessment_marks.mark) as obtained_marks'),
                        DB::raw('SUM(assessment_marks.total_marks) as total_marks'),
                        DB::raw('COUNT(*) as marks_count')
                    )
                    ->groupBy('students.gender')
                    ->get();

                $maleObtained = 0;
                $maleTotal = 0;
                $femaleObtained = 0;
                $femaleTotal = 0;

                foreach ($marksByGender as $row) {
                    if (in_array(strtolower($row->gender), ['male', 'm'])) {
                        $maleObtained += $row->obtained_marks;
                        $maleTotal += $row->total_marks;
                    } elseif (in_array(strtolower($row->gender), ['female', 'f'])) {
                        $femaleObtained += $row->obtained_marks;
                        $femaleTotal += $row->total_marks;
                    }
                }

                $malePerformance = $maleTotal > 0 ? round(($maleObtained / $maleTotal) * 100, 1) : 0;
                $femalePerformance = $femaleTotal > 0 ? round(($femaleObtained / $femaleTotal) * 100, 1) : 0;

                $assessmentStatsByGender[] = [
                    'type' => $type,
                    'male_performance' => $malePerformance,
                    'female_performance' => $femalePerformance,
                    'male_marks_count' => $maleTotal > 0 ? 1 : 0,
                    'female_marks_count' => $femaleTotal > 0 ? 1 : 0
                ];
            }

            // Get subject-wise assessment performance for admin (filtered by current term)
            $subjectPerformanceData = [];
            $subjectAssessmentMatrix = [];

            foreach ($subjects as $subject) {
                $subjectTotalMarks = 0;
                $subjectObtainedMarks = 0;
                $subjectAssessmentCount = 0;

                // Get all assessments for this subject (filtered by current term)
                $subjectAssessments = Assessment::where('subject_id', $subject->id)
                    ->where('academic_year', $currentYear)
                    ->where('term', $currentPeriod)
                    ->get();

                // Get unique class IDs for this subject
                $subjectClassIds = $subjectAssessments->pluck('class_id')->unique()->filter()->toArray();

                foreach ($subjectAssessments as $assessment) {
                    $marks = AssessmentMark::where('assessment_id', $assessment->id)->get();
                    foreach ($marks as $mark) {
                        if ($mark->mark !== null && $mark->total_marks > 0) {
                            $subjectTotalMarks += $mark->total_marks;
                            $subjectObtainedMarks += $mark->mark;
                            $subjectAssessmentCount++;
                        }
                    }
                }

                $subjectPerformance = $subjectTotalMarks > 0 ? round(($subjectObtainedMarks / $subjectTotalMarks) * 100, 1) : 0;

                $subjectPerformanceData[] = [
                    'subject' => $subject->name,
                    'subject_id' => $subject->id,
                    'assessments' => $subjectAssessments->count(),
                    'marks_count' => $subjectAssessmentCount,
                    'performance' => $subjectPerformance,
                    'class_ids' => $subjectClassIds
                ];

                // Build matrix: subject vs assessment types (filtered by current term)
                $typeStats = [];
                foreach ($assessmentTypes as $type) {
                    $typeAssessments = Assessment::where('subject_id', $subject->id)
                        ->where('assessment_type', $type)
                        ->where('academic_year', $currentYear)
                        ->where('term', $currentPeriod)
                        ->get();

                    $typeTotalMarks = 0;
                    $typeObtainedMarks = 0;
                    $typeCount = $typeAssessments->count();

                    foreach ($typeAssessments as $assessment) {
                        $marks = AssessmentMark::where('assessment_id', $assessment->id)->get();
                        foreach ($marks as $mark) {
                            if ($mark->mark !== null && $mark->total_marks > 0) {
                                $typeTotalMarks += $mark->total_marks;
                                $typeObtainedMarks += $mark->mark;
                            }
                        }
                    }

                    $typePerformance = $typeTotalMarks > 0 ? round(($typeObtainedMarks / $typeTotalMarks) * 100, 1) : 0;

                    $typeStats[$type] = [
                        'given' => $typeCount,
                        'performance' => $typePerformance
                    ];
                }

                $subjectAssessmentMatrix[] = [
                    'subject' => $subject->name,
                    'subject_id' => $subject->id,
                    'overall_performance' => $subjectPerformance,
                    'types' => $typeStats,
                    'class_ids' => $subjectClassIds
                ];
            }

            // Group subjects by class for the Subject Performance Cards (include all classes)
            $subjectsByClass = [];
            foreach ($classes as $class) {
                // Get all subjects assigned to this class
                $classAllSubjects = $class->subjects;
                $totalSubjectsInClass = $classAllSubjects->count();
                
                // Find subjects with assessments
                $classSubjectsWithAssessments = [];
                foreach ($subjectAssessmentMatrix as $subjectData) {
                    if (in_array($class->id, $subjectData['class_ids'])) {
                        $classSubjectsWithAssessments[] = $subjectData;
                    }
                }
                $subjectsWithAssessmentsCount = count($classSubjectsWithAssessments);
                
                // Calculate assessment coverage percentage
                $assessmentCoverage = $totalSubjectsInClass > 0 
                    ? round(($subjectsWithAssessmentsCount / $totalSubjectsInClass) * 100) 
                    : 0;
                
                $subjectsByClass[] = [
                    'class_id' => $class->id,
                    'class_name' => $class->class_name,
                    'subjects' => $classSubjectsWithAssessments,
                    'total_subjects' => $totalSubjectsInClass,
                    'subjects_with_assessments' => $subjectsWithAssessmentsCount,
                    'assessment_coverage' => $assessmentCoverage
                ];
            }

            return view('home', compact('parents','teachers','students','subjects','classes','genderStats','classroomPopulation','assessmentStats','assessmentStatsByGender','subjectPerformanceData','subjectAssessmentMatrix','subjectsByClass','assessmentTypes','availableTerms','currentTerm','currentYear','currentPeriod'));

        } elseif ($user->hasRole('Teacher')) {

            $teacher = Teacher::with(['user','subjects'])->withCount('subjects')->findOrFail($user->teacher->id);

            // Get classes either through the subjects the teacher teaches OR
            // where the teacher is assigned as the class teacher (grade.teacher_id)
            $teacherSubjectIds = $teacher->subjects->pluck('id')->toArray();

            $teacherClasses = Grade::where(function($q) use ($teacherSubjectIds, $teacher) {
                if (!empty($teacherSubjectIds)) {
                    $q->whereHas('subjects', function($query) use ($teacherSubjectIds) {
                        $query->whereIn('subjects.id', $teacherSubjectIds);
                    });
                }

                // include classes where teacher is explicitly assigned
                $q->orWhere('teacher_id', $teacher->id);
            })
            ->with(['students.user'])
            ->withCount('students')
            ->get();

            $teacherClassIds = $teacherClasses->pluck('id')->toArray();

            // Add classes and classes_count to teacher object for the view
            $teacher->setRelation('classes', $teacherClasses);
            $teacher->classes_count = $teacherClasses->count();
            $teacher->total_students_count = $teacherClasses->sum('students_count');

            $assessmentTypes = ['Quiz', 'Test', 'In Class Test', 'Monthly Test', 'Assignment', 'Exercise', 'Project', 'Fort Night', 'Exam', 'Vacation Exam', 'National Exam'];
            $teacherAssessmentStats = [];

            foreach ($assessmentTypes as $type) {
                // Query assessments by teacher_id only - the teacher created these assessments
                $assessments = Assessment::where('assessment_type', $type)
                    ->where('teacher_id', $teacher->id)
                    ->get();

                $totalGiven = $assessments->count();
                $allPercentages = [];

                foreach ($assessments as $assessment) {
                    $papers = $assessment->papers ?? [];
                    $marks = AssessmentMark::where('assessment_id', $assessment->id)->get();
                    
                    // Group marks by student to calculate per-student percentage
                    $studentMarks = $marks->groupBy('student_id');
                    
                    foreach ($studentMarks as $studentId => $studentPaperMarks) {
                        $studentWeightedTotal = 0;
                        $totalWeight = 0;
                        
                        foreach ($studentPaperMarks as $mark) {
                            if ($mark->mark !== null && $mark->total_marks > 0) {
                                // Get paper weight from assessment papers array
                                $paperWeight = 100; // default if no weight found
                                if (isset($papers[$mark->paper_index]['weight'])) {
                                    $paperWeight = (float) $papers[$mark->paper_index]['weight'];
                                }
                                
                                // Calculate percentage for this paper: (mark/total_marks) * 100
                                $paperPercentage = ($mark->mark / $mark->total_marks) * 100;
                                // Cap at 100% per paper
                                $paperPercentage = min($paperPercentage, 100);
                                
                                // Add weighted contribution
                                $studentWeightedTotal += $paperPercentage * ($paperWeight / 100);
                                $totalWeight += $paperWeight;
                            }
                        }
                        
                        // Calculate student's overall percentage for this assessment
                        if ($totalWeight > 0) {
                            // Normalize if weights don't add to 100
                            $studentPercentage = ($studentWeightedTotal / $totalWeight) * 100;
                            $allPercentages[] = min($studentPercentage, 100);
                        }
                    }
                }

                // Average percentage across all students
                $performance = count($allPercentages) > 0 ? round(array_sum($allPercentages) / count($allPercentages), 1) : 0;

                $teacherAssessmentStats[] = [
                    'type' => $type,
                    'given' => $totalGiven,
                    'performance' => $performance
                ];
            }

            // Get subject-wise assessment data based on actual assessments created by this teacher
            $subjectAssessmentData = [];
            
            // Get unique subject/class combinations from teacher's assessments
            $teacherAssessments = Assessment::where('teacher_id', $teacher->id)
                ->with(['subject', 'class'])
                ->get();
            
            $subjectClassCombos = $teacherAssessments->map(function($a) {
                return $a->subject_id . '-' . $a->class_id;
            })->unique();
            
            foreach ($subjectClassCombos as $combo) {
                list($subjectId, $classId) = explode('-', $combo);
                $subject = \App\Subject::find($subjectId);
                $class = \App\Grade::find($classId);
                
                if (!$subject || !$class) continue;
                
                $subjectStats = [];
                foreach ($assessmentTypes as $type) {
                    $assessments = Assessment::where('assessment_type', $type)
                        ->where('subject_id', $subjectId)
                        ->where('class_id', $classId)
                        ->where('teacher_id', $teacher->id)
                        ->get();

                    $given = $assessments->count();
                    $allPercentages = [];

                    foreach ($assessments as $assessment) {
                        $papers = $assessment->papers ?? [];
                        $marks = AssessmentMark::where('assessment_id', $assessment->id)->get();
                        
                        // Group marks by student to calculate per-student percentage
                        $studentMarks = $marks->groupBy('student_id');
                        
                        foreach ($studentMarks as $studentId => $studentPaperMarks) {
                            $studentWeightedTotal = 0;
                            $totalWeight = 0;
                            
                            foreach ($studentPaperMarks as $mark) {
                                if ($mark->mark !== null && $mark->total_marks > 0) {
                                    // Get paper weight from assessment papers array
                                    $paperWeight = 100; // default if no weight found
                                    if (isset($papers[$mark->paper_index]['weight'])) {
                                        $paperWeight = (float) $papers[$mark->paper_index]['weight'];
                                    }
                                    
                                    // Calculate percentage for this paper: (mark/total_marks) * 100
                                    $paperPercentage = ($mark->mark / $mark->total_marks) * 100;
                                    // Cap at 100% per paper
                                    $paperPercentage = min($paperPercentage, 100);
                                    
                                    // Add weighted contribution
                                    $studentWeightedTotal += $paperPercentage * ($paperWeight / 100);
                                    $totalWeight += $paperWeight;
                                }
                            }
                            
                            // Calculate student's overall percentage for this assessment
                            if ($totalWeight > 0) {
                                $studentPercentage = ($studentWeightedTotal / $totalWeight) * 100;
                                $allPercentages[] = min($studentPercentage, 100);
                            }
                        }
                    }

                    // Average percentage across all students
                    $performance = count($allPercentages) > 0 ? round(array_sum($allPercentages) / count($allPercentages), 1) : 0;

                    $subjectStats[] = [
                        'type' => $type,
                        'given' => $given,
                        'performance' => $performance > 0 ? $performance . '%' : '--%'
                    ];
                }

                $subjectAssessmentData[] = [
                    'subject' => $subject->name,
                    'class' => $class->class_name,
                    'stats' => $subjectStats
                ];
            }

            return view('home', compact('teacher', 'teacherAssessmentStats', 'subjectAssessmentData'));

        } elseif ($user->hasRole('Parent')) {

            $parents = Parents::with(['children.user', 'children.class'])->withCount('children')->findOrFail($user->parent->id);

            // Get children IDs
            $childrenIds = $parents->children->pluck('id')->toArray();

            // Get assessment marks for all children grouped by subject
            $assessmentData = [];
            $attendanceData = [];
            $recentAssessments = [];

            foreach ($parents->children as $child) {
                // Get assessment marks with subject info (only approved)
                $childAssessments = AssessmentMark::with(['assessment.subject', 'assessment.class'])
                    ->where('student_id', $child->id)
                    ->where('approved', true)
                    ->whereHas('assessment')
                    ->get();

                // Group by subject for chart
                $subjectMarks = [];
                foreach ($childAssessments as $mark) {
                    if ($mark->assessment && $mark->assessment->subject) {
                        $subjectName = $mark->assessment->subject->name;
                        if (!isset($subjectMarks[$subjectName])) {
                            $subjectMarks[$subjectName] = [
                                'total_marks' => 0,
                                'obtained_marks' => 0,
                                'count' => 0,
                                'assessments' => []
                            ];
                        }
                        $subjectMarks[$subjectName]['total_marks'] += $mark->total_marks ?? 0;
                        $subjectMarks[$subjectName]['obtained_marks'] += $mark->mark ?? 0;
                        $subjectMarks[$subjectName]['count']++;
                        $subjectMarks[$subjectName]['assessments'][] = [
                            'topic' => $mark->assessment->topic,
                            'type' => $mark->assessment->assessment_type,
                            'mark' => $mark->mark,
                            'total' => $mark->total_marks,
                            'comment' => $mark->comment,
                            'date' => $mark->assessment->date
                        ];
                    }
                }

                // Calculate percentages per subject
                $subjectPercentages = [];
                foreach ($subjectMarks as $subject => $data) {
                    $percentage = $data['total_marks'] > 0
                        ? round(($data['obtained_marks'] / $data['total_marks']) * 100, 1)
                        : 0;
                    $subjectPercentages[$subject] = $percentage;
                }

                $assessmentData[$child->id] = [
                    'student_name' => $child->user->name ?? 'Unknown',
                    'class' => $child->class->class_name ?? 'N/A',
                    'subject_marks' => $subjectMarks,
                    'subject_percentages' => $subjectPercentages
                ];

                // Get attendance data
                $totalAttendance = Attendance::where('student_id', $child->id)->count();
                $presentCount = Attendance::where('student_id', $child->id)->where('attendence_status', 'present')->count();
                $absentCount = Attendance::where('student_id', $child->id)->where('attendence_status', 'absent')->count();
                $lateCount = Attendance::where('student_id', $child->id)->where('attendence_status', 'late')->count();

                $attendanceData[$child->id] = [
                    'student_name' => $child->user->name ?? 'Unknown',
                    'total' => $totalAttendance,
                    'present' => $presentCount,
                    'absent' => $absentCount,
                    'late' => $lateCount,
                    'percentage' => $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100, 1) : 0
                ];

                // Get recent assessments (only approved)
                $recent = AssessmentMark::with(['assessment.subject'])
                    ->where('student_id', $child->id)
                    ->where('approved', true)
                    ->whereHas('assessment')
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get();

                $recentAssessments[$child->id] = $recent;

                // Get assessment type stats for each child
                $assessmentTypes = ['Quiz', 'Test', 'In Class Test', 'Monthly Test', 'Assignment', 'Exercise', 'Project', 'Fort Night', 'Exam', 'Vacation Exam', 'National Exam'];
                $childAssessmentStats = [];

                foreach ($assessmentTypes as $type) {
                    $typeMarks = AssessmentMark::where('student_id', $child->id)
                        ->where('approved', true)
                        ->whereHas('assessment', function($q) use ($type) {
                            $q->where('assessment_type', $type);
                        })
                        ->get();

                    $totalMarks = 0;
                    $obtainedMarks = 0;
                    $count = 0;

                    foreach ($typeMarks as $mark) {
                        if ($mark->mark !== null && $mark->total_marks > 0) {
                            $totalMarks += $mark->total_marks;
                            $obtainedMarks += $mark->mark;
                            $count++;
                        }
                    }

                    $performance = $totalMarks > 0 ? round(($obtainedMarks / $totalMarks) * 100, 1) : 0;

                    $childAssessmentStats[] = [
                        'type' => $type,
                        'given' => $count,
                        'performance' => $performance
                    ];
                }

                $assessmentData[$child->id]['assessment_stats'] = $childAssessmentStats;
            }

            return view('home', compact('parents', 'assessmentData', 'attendanceData', 'recentAssessments'));

        } elseif ($user->hasRole('Student')) {

            $student = Student::with(['user','parent','class','attendances'])->findOrFail($user->student->id);

            // Get student's assessment performance by type
            $assessmentTypes = ['Quiz', 'Test', 'In Class Test', 'Monthly Test', 'Assignment', 'Exercise', 'Project', 'Fort Night', 'Exam', 'Vacation Exam', 'National Exam'];
            $studentAssessmentStats = [];

            foreach ($assessmentTypes as $type) {
                $marks = AssessmentMark::where('student_id', $student->id)
                    ->where('approved', true)
                    ->whereHas('assessment', function($q) use ($type) {
                        $q->where('assessment_type', $type);
                    })
                    ->get();

                $totalMarks = 0;
                $obtainedMarks = 0;
                $count = 0;

                foreach ($marks as $mark) {
                    if ($mark->mark !== null && $mark->total_marks > 0) {
                        $totalMarks += $mark->total_marks;
                        $obtainedMarks += $mark->mark;
                        $count++;
                    }
                }

                $performance = $totalMarks > 0 ? round(($obtainedMarks / $totalMarks) * 100, 1) : 0;

                $studentAssessmentStats[] = [
                    'type' => $type,
                    'given' => $count,
                    'performance' => $performance
                ];
            }

            // Get subject-wise performance for this student
            $subjectPerformance = [];
            $studentMarks = AssessmentMark::with(['assessment.subject'])
                ->where('student_id', $student->id)
                ->where('approved', true)
                ->whereHas('assessment.subject')
                ->get();

            foreach ($studentMarks as $mark) {
                $subjectName = $mark->assessment->subject->name;
                if (!isset($subjectPerformance[$subjectName])) {
                    $subjectPerformance[$subjectName] = [
                        'total_marks' => 0,
                        'obtained_marks' => 0,
                        'count' => 0
                    ];
                }
                if ($mark->mark !== null && $mark->total_marks > 0) {
                    $subjectPerformance[$subjectName]['total_marks'] += $mark->total_marks;
                    $subjectPerformance[$subjectName]['obtained_marks'] += $mark->mark;
                    $subjectPerformance[$subjectName]['count']++;
                }
            }

            // Calculate percentages
            $subjectStats = [];
            foreach ($subjectPerformance as $subject => $data) {
                $percentage = $data['total_marks'] > 0
                    ? round(($data['obtained_marks'] / $data['total_marks']) * 100, 1)
                    : 0;
                $subjectStats[] = [
                    'subject' => $subject,
                    'assessments' => $data['count'],
                    'performance' => $percentage
                ];
            }

            return view('home', compact('student', 'studentAssessmentStats', 'subjectStats'));

        } else {
            // Show admin dashboard for other roles (Web manager, Librarian, Accountant, etc.)
            $parents = Parents::latest()->get();
            $teachers = Teacher::latest()->get();
            $students = Student::latest()->get();
            $subjects = Subject::latest()->get();
            $classes = Grade::latest()->get();

            // Available terms for switcher
            $availableTerms = ResultsStatus::select('id', 'year', 'result_period')
                ->orderBy('year', 'desc')
                ->orderByRaw("CASE result_period WHEN 'third' THEN 3 WHEN 'second' THEN 2 WHEN 'first' THEN 1 ELSE 0 END DESC")
                ->get()
                ->map(function($term) {
                    $periodLabels = ['first' => 'Term 1', 'second' => 'Term 2', 'third' => 'Term 3'];
                    return [
                        'id'     => $term->id,
                        'year'   => $term->year,
                        'period' => $term->result_period,
                        'label'  => ($periodLabels[$term->result_period] ?? ucfirst($term->result_period)) . ' ' . $term->year,
                    ];
                });

            // Determine current term — request params override automatic detection
            $latestTerm = ResultsStatus::orderBy('year', 'desc')
                ->orderByRaw("CASE result_period WHEN 'third' THEN 3 WHEN 'second' THEN 2 WHEN 'first' THEN 1 ELSE 0 END DESC")
                ->first();

            if ($request->filled('term_id')) {
                $currentTerm = ResultsStatus::find($request->term_id) ?? $latestTerm;
            } else {
                $currentTerm = $latestTerm;
            }

            $currentYear   = $currentTerm ? $currentTerm->year        : date('Y');
            $currentPeriod = $currentTerm ? $currentTerm->result_period : 'first';

            // Get pass/fail results by gender — filtered to current term
            $resultsByGender = DB::table('results')
                ->join('students', 'results.student_id', '=', 'students.id')
                ->where('results.year', $currentYear)
                ->where('results.result_period', $currentPeriod)
                ->select(
                    'students.gender',
                    DB::raw('SUM(CASE WHEN results.marks >= 50 THEN 1 ELSE 0 END) as pass_count'),
                    DB::raw('SUM(CASE WHEN results.marks < 50 THEN 1 ELSE 0 END) as fail_count'),
                    DB::raw('COUNT(*) as total_count')
                )
                ->groupBy('students.gender')
                ->get();

            $malePass = 0;
            $maleFail = 0;
            $femalePass = 0;
            $femaleFail = 0;

            foreach ($resultsByGender as $row) {
                if (strtolower($row->gender) === 'male') {
                    $malePass = $row->pass_count;
                    $maleFail = $row->fail_count;
                } elseif (strtolower($row->gender) === 'female') {
                    $femalePass = $row->pass_count;
                    $femaleFail = $row->fail_count;
                }
            }

            $genderStats = [
                'malePass' => $malePass,
                'maleFail' => $maleFail,
                'femalePass' => $femalePass,
                'femaleFail' => $femaleFail,
            ];

            // Get classroom population data
            $classroomPopulation = Grade::with(['students'])
                ->orderBy('class_numeric')
                ->get()
                ->map(function($class) {
                    $maleCount = $class->students->whereIn('gender', ['Male', 'male', 'M', 'm'])->count();
                    $femaleCount = $class->students->whereIn('gender', ['Female', 'female', 'F', 'f'])->count();
                    return [
                        'name' => $class->class_name,
                        'count' => $class->students->count(),
                        'male' => $maleCount,
                        'female' => $femaleCount
                    ];
                });

            // Get assessment statistics
            $assessmentTypes = ['Quiz', 'Test', 'In Class Test', 'Monthly Test', 'Assignment', 'Exercise', 'Project', 'Fort Night', 'Exam', 'Vacation Exam', 'National Exam'];
            $assessmentStats = [];

            foreach ($assessmentTypes as $type) {
                $assessments = Assessment::where('assessment_type', $type)
                    ->where('academic_year', $currentYear)
                    ->where('term', $currentPeriod)
                    ->get();
                $totalGiven = $assessments->count();

                $totalMarks = 0;
                $obtainedMarks = 0;
                $marksCount = 0;

                foreach ($assessments as $assessment) {
                    $marks = AssessmentMark::where('assessment_id', $assessment->id)->get();
                    foreach ($marks as $mark) {
                        if ($mark->mark !== null && $mark->total_marks > 0) {
                            $totalMarks += $mark->total_marks;
                            $obtainedMarks += $mark->mark;
                            $marksCount++;
                        }
                    }
                }

                $performance = $totalMarks > 0 ? round(($obtainedMarks / $totalMarks) * 100, 1) : 0;

                $assessmentStats[] = [
                    'type' => $type,
                    'given' => $totalGiven,
                    'performance' => $performance
                ];
            }

            // Get subject-wise performance
            $subjectPerformanceData = [];
            $subjectAssessmentMatrix = [];

            foreach ($subjects as $subject) {
                $subjectTotalMarks = 0;
                $subjectObtainedMarks = 0;
                $subjectAssessmentCount = 0;

                $subjectAssessments = Assessment::where('subject_id', $subject->id)
                    ->where('academic_year', $currentYear)
                    ->where('term', $currentPeriod)
                    ->get();
                $subjectClassIds = $subjectAssessments->pluck('class_id')->unique()->filter()->toArray();

                foreach ($subjectAssessments as $assessment) {
                    $marks = AssessmentMark::where('assessment_id', $assessment->id)->get();
                    foreach ($marks as $mark) {
                        if ($mark->mark !== null && $mark->total_marks > 0) {
                            $subjectTotalMarks += $mark->total_marks;
                            $subjectObtainedMarks += $mark->mark;
                            $subjectAssessmentCount++;
                        }
                    }
                }

                $subjectPerformance = $subjectTotalMarks > 0 ? round(($subjectObtainedMarks / $subjectTotalMarks) * 100, 1) : 0;

                $subjectPerformanceData[] = [
                    'subject' => $subject->name,
                    'subject_id' => $subject->id,
                    'assessments' => $subjectAssessments->count(),
                    'marks_count' => $subjectAssessmentCount,
                    'performance' => $subjectPerformance,
                    'class_ids' => $subjectClassIds
                ];

                $typeStats = [];
                foreach ($assessmentTypes as $type) {
                    $typeAssessments = Assessment::where('subject_id', $subject->id)
                        ->where('assessment_type', $type)
                        ->where('academic_year', $currentYear)
                        ->where('term', $currentPeriod)
                        ->get();

                    $typeTotalMarks = 0;
                    $typeObtainedMarks = 0;
                    $typeCount = $typeAssessments->count();

                    foreach ($typeAssessments as $assessment) {
                        $marks = AssessmentMark::where('assessment_id', $assessment->id)->get();
                        foreach ($marks as $mark) {
                            if ($mark->mark !== null && $mark->total_marks > 0) {
                                $typeTotalMarks += $mark->total_marks;
                                $typeObtainedMarks += $mark->mark;
                            }
                        }
                    }

                    $typePerformance = $typeTotalMarks > 0 ? round(($typeObtainedMarks / $typeTotalMarks) * 100, 1) : 0;

                    $typeStats[$type] = [
                        'given' => $typeCount,
                        'performance' => $typePerformance
                    ];
                }

                $subjectAssessmentMatrix[] = [
                    'subject' => $subject->name,
                    'subject_id' => $subject->id,
                    'overall_performance' => $subjectPerformance,
                    'types' => $typeStats,
                    'class_ids' => $subjectClassIds
                ];
            }

            return view('home', compact('parents','teachers','students','subjects','classes','genderStats','classroomPopulation','assessmentStats','subjectPerformanceData','subjectAssessmentMatrix','assessmentTypes','currentYear','currentPeriod','currentTerm','availableTerms'));
        }

    }

    /**
     * PROFILE
     */
    public function profile()
    {
        return view('profile.index');
    }

    public function profileEdit()
    {
        return view('profile.edit');
    }

    public function profileUpdate(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.auth()->id()
        ]);

        if ($request->hasFile('profile_picture')) {
            $profile = Str::slug(auth()->user()->name).'-'.auth()->id().'.'.$request->profile_picture->getClientOriginalExtension();
            $request->profile_picture->move(public_path('images/profile'), $profile);
        } else {
            $profile = 'avatar.png';
        }

        $user = auth()->user();

        $user->update([
            'name'              => $request->name,
            'email'             => $request->email,
            'profile_picture'   => $profile
        ]);

        return redirect()->route('profile');
    }

    /**
     * CHANGE PASSWORD
     */
    public function changePasswordForm()
    {
        return view('profile.changepassword');
    }

    public function changePassword(Request $request)
    {
        if (!(Hash::check($request->get('currentpassword'), Auth::user()->password))) {
            return back()->with([
                'msg_currentpassword' => 'Your current password does not matches with the password you provided! Please try again.'
            ]);
        }
        if(strcmp($request->get('currentpassword'), $request->get('newpassword')) == 0){
            return back()->with([
                'msg_currentpassword' => 'New Password cannot be same as your current password! Please choose a different password.'
            ]);
        }

        $this->validate($request, [
            'currentpassword' => 'required',
            'newpassword'     => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        $user->password = bcrypt($request->get('newpassword'));
        $user->save();

        Auth::logout();
        return redirect()->route('login');
    }

    /**
     * Get filtered gender stats for AJAX requests
     */
    public function getFilteredGenderStats(Request $request)
    {
        $classId = $request->get('class_id');
        $year = $request->get('year');
        $period = $request->get('period');

        $query = DB::table('results')
            ->join('students', 'results.student_id', '=', 'students.id');

        if ($classId && $classId !== 'all') {
            $query->where('students.class_id', $classId);
        }

        // Filter by term/year if provided
        if ($year && $year !== 'all') {
            $query->where('results.year', $year);
        }
        if ($period && $period !== 'all') {
            $query->where('results.result_period', $period);
        }

        $resultsByGender = $query->select(
                'students.gender',
                DB::raw('SUM(CASE WHEN results.marks >= 50 THEN 1 ELSE 0 END) as pass_count'),
                DB::raw('SUM(CASE WHEN results.marks < 50 THEN 1 ELSE 0 END) as fail_count'),
                DB::raw('COUNT(*) as total_count')
            )
            ->groupBy('students.gender')
            ->get();

        $malePass = 0;
        $maleFail = 0;
        $femalePass = 0;
        $femaleFail = 0;

        foreach ($resultsByGender as $row) {
            if (strtolower($row->gender) === 'male') {
                $malePass = $row->pass_count;
                $maleFail = $row->fail_count;
            } elseif (strtolower($row->gender) === 'female') {
                $femalePass = $row->pass_count;
                $femaleFail = $row->fail_count;
            }
        }

        $maleTotal = $malePass + $maleFail;
        $femaleTotal = $femalePass + $femaleFail;
        $malePassRate = $maleTotal > 0 ? round($malePass / $maleTotal * 100, 1) : 0;
        $femalePassRate = $femaleTotal > 0 ? round($femalePass / $femaleTotal * 100, 1) : 0;

        return response()->json([
            'success' => true,
            'stats' => [
                'malePass' => $malePass,
                'maleFail' => $maleFail,
                'femalePass' => $femalePass,
                'femaleFail' => $femaleFail,
                'maleTotal' => $maleTotal,
                'femaleTotal' => $femaleTotal,
                'malePassRate' => $malePassRate,
                'femalePassRate' => $femalePassRate
            ]
        ]);
    }

    /**
     * Get filtered assessment stats for AJAX requests
     */
    public function getFilteredAssessmentStats(Request $request)
    {
        $classId = $request->get('class_id');
        $subjectId = $request->get('subject_id');
        $year = $request->get('year');
        $term = $request->get('term');

        $assessmentTypes = ['Quiz', 'Test', 'In Class Test', 'Monthly Test', 'Assignment', 'Exercise', 'Project', 'Fort Night', 'Exam', 'Vacation Exam', 'National Exam'];
        $assessmentStats = [];

        foreach ($assessmentTypes as $type) {
            $query = Assessment::where('assessment_type', $type);

            if ($classId && $classId !== 'all') {
                $query->where('class_id', $classId);
            }

            if ($subjectId && $subjectId !== 'all') {
                $query->where('subject_id', $subjectId);
            }

            if ($year && $year !== 'all') {
                $query->where('academic_year', $year);
            }

            if ($term && $term !== 'all') {
                $query->where('term', $term);
            }

            $assessments = $query->get();
            $totalMarks = 0;
            $obtainedMarks = 0;
            $count = 0;

            foreach ($assessments as $assessment) {
                $marks = AssessmentMark::where('assessment_id', $assessment->id)->get();
                foreach ($marks as $mark) {
                    if ($mark->mark !== null && $mark->total_marks > 0) {
                        $totalMarks += $mark->total_marks;
                        $obtainedMarks += $mark->mark;
                        $count++;
                    }
                }
            }

            $performance = $totalMarks > 0 ? round(($obtainedMarks / $totalMarks) * 100, 1) : 0;

            $assessmentStats[] = [
                'type' => $type,
                'given' => $assessments->count(),
                'performance' => $performance
            ];
        }

        return response()->json([
            'success' => true,
            'stats' => $assessmentStats
        ]);
    }

    /**
     * Get filtered assessment stats by gender for AJAX requests
     */
    public function getFilteredAssessmentStatsByGender(Request $request)
    {
        $classId = $request->get('class_id');
        $subjectId = $request->get('subject_id');
        $year = $request->get('year');
        $term = $request->get('term');

        $assessmentTypes = ['Quiz', 'Test', 'In Class Test', 'Monthly Test', 'Assignment', 'Exercise', 'Project', 'Fort Night', 'Exam', 'Vacation Exam', 'National Exam'];
        $assessmentStatsByGender = [];

        foreach ($assessmentTypes as $type) {
            $query = Assessment::where('assessment_type', $type);

            if ($classId && $classId !== 'all') {
                $query->where('class_id', $classId);
            }

            if ($subjectId && $subjectId !== 'all') {
                $query->where('subject_id', $subjectId);
            }

            if ($year && $year !== 'all') {
                $query->where('academic_year', $year);
            }

            if ($term && $term !== 'all') {
                $query->where('term', $term);
            }

            $assessmentIds = $query->pluck('id');

            // Get marks with student gender information
            $marksByGender = DB::table('assessment_marks')
                ->join('students', 'assessment_marks.student_id', '=', 'students.id')
                ->whereIn('assessment_marks.assessment_id', $assessmentIds)
                ->whereNotNull('assessment_marks.mark')
                ->where('assessment_marks.total_marks', '>', 0)
                ->select(
                    'students.gender',
                    DB::raw('SUM(assessment_marks.mark) as obtained_marks'),
                    DB::raw('SUM(assessment_marks.total_marks) as total_marks'),
                    DB::raw('COUNT(*) as marks_count')
                )
                ->groupBy('students.gender')
                ->get();

            $maleObtained = 0;
            $maleTotal = 0;
            $femaleObtained = 0;
            $femaleTotal = 0;

            foreach ($marksByGender as $row) {
                if (in_array(strtolower($row->gender), ['male', 'm'])) {
                    $maleObtained += $row->obtained_marks;
                    $maleTotal += $row->total_marks;
                } elseif (in_array(strtolower($row->gender), ['female', 'f'])) {
                    $femaleObtained += $row->obtained_marks;
                    $femaleTotal += $row->total_marks;
                }
            }

            $malePerformance = $maleTotal > 0 ? round(($maleObtained / $maleTotal) * 100, 1) : 0;
            $femalePerformance = $femaleTotal > 0 ? round(($femaleObtained / $femaleTotal) * 100, 1) : 0;

            $assessmentStatsByGender[] = [
                'type' => $type,
                'male_performance' => $malePerformance,
                'female_performance' => $femalePerformance
            ];
        }

        return response()->json([
            'success' => true,
            'stats' => $assessmentStatsByGender
        ]);
    }

    /**
     * Show force password change form for users with must_change_password flag.
     */
    public function showForceChangePasswordForm()
    {
        return view('auth.force-change-password');
    }

    /**
     * Handle force password change.
     */
    public function forceChangePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.confirmed' => 'The password confirmation does not match.',
        ]);

        $user = Auth::user();

        // Ensure new password is different from default
        if (Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Please choose a different password from your current one.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        return redirect()->route('home')->with('success', 'Password changed successfully!');
    }

    /**
     * Show subject performance for a specific class.
     */
    public function classSubjects($id)
    {
        $class = Grade::with('subjects.teacher.user')->findOrFail($id);
        $classSubjects = $class->subjects()->with('teacher.user')->orderBy('name')->get();
        
        $currentTerm = ResultsStatus::orderBy('year', 'desc')
            ->orderByRaw("CASE result_period WHEN 'third' THEN 3 WHEN 'second' THEN 2 WHEN 'first' THEN 1 ELSE 0 END DESC")
            ->first();
        $currentYear = $currentTerm ? $currentTerm->year : date('Y');
        $currentPeriod = $currentTerm ? $currentTerm->result_period : 'first';
        
        $assessmentTypes = ['Quiz', 'Test', 'In Class Test', 'Monthly Test', 'Assignment', 'Exercise', 'Project', 'Fort Night', 'Exam', 'Vacation Exam', 'National Exam'];
        
        $subjectPerformanceData = [];
        $totalSubjects = $classSubjects->count();
        $subjectsWithAssessments = 0;
        
        foreach ($classSubjects as $subject) {
            $subjectAssessments = Assessment::where('subject_id', $subject->id)
                ->where('class_id', $class->id)
                ->where('academic_year', $currentYear)
                ->where('term', $currentPeriod)
                ->get();
            
            $hasAssessments = $subjectAssessments->count() > 0;
            if ($hasAssessments) {
                $subjectsWithAssessments++;
            }
            
            $subjectTotalMarks = 0;
            $subjectObtainedMarks = 0;
            
            foreach ($subjectAssessments as $assessment) {
                $marks = AssessmentMark::where('assessment_id', $assessment->id)->get();
                foreach ($marks as $mark) {
                    if ($mark->mark !== null && $mark->total_marks > 0) {
                        $subjectTotalMarks += $mark->total_marks;
                        $subjectObtainedMarks += $mark->mark;
                    }
                }
            }
            
            $subjectPerformance = $subjectTotalMarks > 0 ? round(($subjectObtainedMarks / $subjectTotalMarks) * 100, 1) : 0;
            
            $typeStats = [];
            foreach ($assessmentTypes as $type) {
                $typeAssessments = Assessment::where('subject_id', $subject->id)
                    ->where('class_id', $class->id)
                    ->where('assessment_type', $type)
                    ->where('academic_year', $currentYear)
                    ->where('term', $currentPeriod)
                    ->get();
                
                $typeTotalMarks = 0;
                $typeObtainedMarks = 0;
                
                foreach ($typeAssessments as $assessment) {
                    $marks = AssessmentMark::where('assessment_id', $assessment->id)->get();
                    foreach ($marks as $mark) {
                        if ($mark->mark !== null && $mark->total_marks > 0) {
                            $typeTotalMarks += $mark->total_marks;
                            $typeObtainedMarks += $mark->mark;
                        }
                    }
                }
                
                $typePerformance = $typeTotalMarks > 0 ? round(($typeObtainedMarks / $typeTotalMarks) * 100, 1) : 0;
                
                $typeStats[$type] = [
                    'given' => $typeAssessments->count(),
                    'performance' => $typePerformance
                ];
            }
            
            $subjectPerformanceData[] = [
                'subject' => $subject->name,
                'subject_id' => $subject->id,
                'assessments' => $subjectAssessments->count(),
                'overall_performance' => $subjectPerformance,
                'types' => $typeStats,
                'has_assessments' => $hasAssessments,
                'teacher_name' => $subject->teacher && $subject->teacher->user ? $subject->teacher->user->name : null
            ];
        }
        
        $assessmentCoverage = $totalSubjects > 0 ? round(($subjectsWithAssessments / $totalSubjects) * 100) : 0;
        
        return view('class_subjects', compact('class', 'subjectPerformanceData', 'assessmentTypes', 'currentYear', 'currentPeriod', 'totalSubjects', 'subjectsWithAssessments', 'assessmentCoverage'));
    }
}
