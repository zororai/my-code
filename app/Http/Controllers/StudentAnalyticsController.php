<?php

namespace App\Http\Controllers;

use App\Student;
use App\Grade;
use App\Subject;
use App\Assessment;
use App\AssessmentMark;
use App\Result;
use App\ResultsStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentAnalyticsController extends Controller
{
    /**
     * Display the student analytics index page
     */
    public function index(Request $request)
    {
        // Get classes with student counts
        $classes = Grade::withCount('students')->orderBy('class_numeric')->get();
        $subjects = Subject::orderBy('name')->get();
        
        // Get available terms
        $availableTerms = ResultsStatus::select('id', 'year', 'result_period')
            ->orderBy('year', 'desc')
            ->orderByRaw("FIELD(result_period, 'third', 'second', 'first')")
            ->get()
            ->map(function($term) {
                $periodLabels = ['first' => 'Term 1', 'second' => 'Term 2', 'third' => 'Term 3'];
                return [
                    'id' => $term->id,
                    'year' => $term->year,
                    'period' => $term->result_period,
                    'label' => ($periodLabels[$term->result_period] ?? ucfirst($term->result_period)) . ' ' . $term->year
                ];
            });

        // Get current term (most recent by academic year and period)
        $currentTerm = ResultsStatus::orderBy('year', 'desc')
            ->orderByRaw("FIELD(result_period, 'third', 'second', 'first')")
            ->first();
        $currentYear = $currentTerm ? $currentTerm->year : date('Y');
        $currentPeriod = $currentTerm ? $currentTerm->result_period : 'first';

        // Get selected filters
        $selectedClassId = $request->get('class_id');
        $selectedYear = $request->get('year', $currentYear);
        $selectedTerm = $request->get('term', $currentPeriod);
        $selectedStudentId = $request->get('student_id');

        $students = collect();
        $analyticsData = null;
        $selectedClass = null;
        $selectedStudent = null;

        // If a class is selected, get students with user info
        if ($selectedClassId) {
            $selectedClass = Grade::find($selectedClassId);
            $students = Student::where('class_id', $selectedClassId)
                ->with('user')
                ->whereHas('user')
                ->orderBy('roll_number')
                ->get();
        }

        // If a student is selected, get analytics data
        if ($selectedStudentId) {
            $selectedStudent = Student::with(['user', 'class'])->find($selectedStudentId);
            $analyticsData = $this->getStudentAnalytics($selectedStudentId, $selectedYear, $selectedTerm);
        }

        return view('backend.students.analytics', compact(
            'classes',
            'subjects',
            'availableTerms',
            'students',
            'selectedClassId',
            'selectedClass',
            'selectedYear',
            'selectedTerm',
            'selectedStudentId',
            'selectedStudent',
            'analyticsData',
            'currentYear',
            'currentPeriod'
        ));
    }

    /**
     * Get analytics data for a specific student
     */
    private function getStudentAnalytics($studentId, $year, $term)
    {
        $student = Student::with(['user', 'class'])->find($studentId);
        if (!$student) {
            return null;
        }

        // Get subjects from assessments or results for this student in this term
        // First, get subjects where the student has assessments or results
        $subjectIdsFromAssessments = DB::table('assessment_marks')
            ->join('assessments', 'assessment_marks.assessment_id', '=', 'assessments.id')
            ->where('assessment_marks.student_id', $studentId)
            ->where('assessments.academic_year', $year)
            ->where('assessments.term', $term)
            ->distinct()
            ->pluck('assessments.subject_id')
            ->toArray();

        $subjectIdsFromResults = Result::where('student_id', $studentId)
            ->where('year', $year)
            ->where('result_period', $term)
            ->distinct()
            ->pluck('subject_id')
            ->toArray();

        $allSubjectIds = array_unique(array_merge($subjectIdsFromAssessments, $subjectIdsFromResults));

        // If no subjects found from assessments/results, try getting from class
        if (empty($allSubjectIds)) {
            $studentSubjects = Subject::whereHas('grades', function($q) use ($student) {
                $q->where('grades.id', $student->class_id);
            })->get();
        } else {
            $studentSubjects = Subject::whereIn('id', $allSubjectIds)->orderBy('name')->get();
        }

        $subjectAnalytics = [];

        foreach ($studentSubjects as $subject) {
            // Get assessments for this subject, student's class, year, and term
            $assessments = Assessment::where('subject_id', $subject->id)
                ->where('class_id', $student->class_id)
                ->where('academic_year', $year)
                ->where('term', $term)
                ->with(['marks' => function($q) use ($studentId) {
                    $q->where('student_id', $studentId);
                }])
                ->get();

            // Calculate assessment performance
            $totalAssessmentMarks = 0;
            $obtainedAssessmentMarks = 0;
            $assessmentCount = 0;
            $assessmentDetails = [];

            foreach ($assessments as $assessment) {
                $mark = $assessment->marks->first();
                if ($mark && $mark->mark !== null && $mark->total_marks > 0) {
                    $totalAssessmentMarks += $mark->total_marks;
                    $obtainedAssessmentMarks += $mark->mark;
                    $assessmentCount++;
                    
                    $assessmentDetails[] = [
                        'type' => $assessment->assessment_type,
                        'topic' => $assessment->topic,
                        'date' => $assessment->date,
                        'mark' => $mark->mark,
                        'total' => $mark->total_marks,
                        'percentage' => round(($mark->mark / $mark->total_marks) * 100, 1)
                    ];
                }
            }

            $assessmentPerformance = $totalAssessmentMarks > 0 
                ? round(($obtainedAssessmentMarks / $totalAssessmentMarks) * 100, 1) 
                : null;

            // Get term result for this subject
            $termResult = Result::where('student_id', $studentId)
                ->where('subject_id', $subject->id)
                ->where('year', $year)
                ->where('result_period', $term)
                ->first();

            $termPerformance = $termResult ? $termResult->marks : null;
            $termGrade = $termResult ? $termResult->mark_grade : null;

            // Calculate difference
            $difference = null;
            if ($assessmentPerformance !== null && $termPerformance !== null) {
                $difference = round($termPerformance - $assessmentPerformance, 1);
            }

            $subjectAnalytics[] = [
                'subject_id' => $subject->id,
                'subject_name' => $subject->name,
                'assessment_count' => $assessmentCount,
                'assessment_performance' => $assessmentPerformance,
                'term_performance' => $termPerformance,
                'term_grade' => $termGrade,
                'difference' => $difference,
                'assessment_details' => $assessmentDetails
            ];
        }

        // Calculate overall stats
        $totalAssessmentPerf = 0;
        $totalTermPerf = 0;
        $subjectsWithBoth = 0;

        foreach ($subjectAnalytics as $sa) {
            if ($sa['assessment_performance'] !== null && $sa['term_performance'] !== null) {
                $totalAssessmentPerf += $sa['assessment_performance'];
                $totalTermPerf += $sa['term_performance'];
                $subjectsWithBoth++;
            }
        }

        $overallAssessmentAvg = $subjectsWithBoth > 0 ? round($totalAssessmentPerf / $subjectsWithBoth, 1) : null;
        $overallTermAvg = $subjectsWithBoth > 0 ? round($totalTermPerf / $subjectsWithBoth, 1) : null;
        $overallDifference = ($overallAssessmentAvg !== null && $overallTermAvg !== null) 
            ? round($overallTermAvg - $overallAssessmentAvg, 1) 
            : null;

        return [
            'student' => $student,
            'year' => $year,
            'term' => $term,
            'subjects' => $subjectAnalytics,
            'overall' => [
                'assessment_avg' => $overallAssessmentAvg,
                'term_avg' => $overallTermAvg,
                'difference' => $overallDifference,
                'subjects_count' => count($subjectAnalytics),
                'subjects_with_data' => $subjectsWithBoth
            ]
        ];
    }

    /**
     * API endpoint for getting students by class
     */
    public function getStudentsByClass(Request $request)
    {
        $classId = $request->get('class_id');
        
        $students = Student::where('class_id', $classId)
            ->with('user')
            ->orderBy('roll_number')
            ->get()
            ->map(function($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->user ? $student->user->name : 'N/A',
                    'roll_number' => $student->roll_number
                ];
            });

        return response()->json(['students' => $students]);
    }

    /**
     * API endpoint for getting student analytics data
     */
    public function getStudentAnalyticsApi(Request $request)
    {
        $studentId = $request->get('student_id');
        $year = $request->get('year');
        $term = $request->get('term');

        if (!$studentId || !$year || !$term) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }

        $analytics = $this->getStudentAnalytics($studentId, $year, $term);

        if (!$analytics) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $analytics]);
    }

    /**
     * Display the parent student analytics page
     * Shows analytics for the parent's children
     */
    public function parentIndex(Request $request)
    {
        $user = auth()->user();
        $parent = $user->parent;

        if (!$parent) {
            return redirect()->route('home')->with('error', 'Parent profile not found.');
        }

        // Get parent's children
        $children = $parent->students()->with(['user', 'class'])->get();

        // Get current term (most recent by academic year and period)
        $currentTerm = ResultsStatus::orderBy('year', 'desc')
            ->orderByRaw("FIELD(result_period, 'third', 'second', 'first')")
            ->first();
        $currentYear = $currentTerm ? $currentTerm->year : date('Y');
        $currentPeriod = $currentTerm ? $currentTerm->result_period : 'first';

        // Get selected filters
        $selectedYear = $request->get('year', $currentYear);
        $selectedTerm = $request->get('term', $currentPeriod);
        $selectedStudentId = $request->get('student_id');

        $analyticsData = null;
        $selectedStudent = null;

        // If a student is selected, get analytics data
        if ($selectedStudentId) {
            // Verify the student belongs to this parent
            $isChildOfParent = $children->contains('id', $selectedStudentId);
            if ($isChildOfParent) {
                $selectedStudent = Student::with(['user', 'class'])->find($selectedStudentId);
                $analyticsData = $this->getStudentAnalytics($selectedStudentId, $selectedYear, $selectedTerm);
            }
        }

        return view('parent.student-analytics', compact(
            'children',
            'selectedYear',
            'selectedTerm',
            'selectedStudentId',
            'selectedStudent',
            'analyticsData',
            'currentYear',
            'currentPeriod'
        ));
    }

    /**
     * Display the teacher student analytics page
     * Shows analytics for students in teacher's classes
     */
    public function teacherIndex(Request $request)
    {
        $user = auth()->user();
        $teacher = $user->teacher;

        if (!$teacher) {
            return redirect()->route('home')->with('error', 'Teacher profile not found.');
        }

        // Get classes the teacher teaches (via subjects pivot table) or is class teacher of
        $teacherSubjects = Subject::where('teacher_id', $teacher->id)->with('grades')->get();
        $subjectClassIds = $teacherSubjects->flatMap(function($subject) {
            return $subject->grades->pluck('id');
        })->unique()->toArray();
        
        $teacherClasses = Grade::where(function($q) use ($subjectClassIds, $teacher) {
            $q->whereIn('id', $subjectClassIds)
              ->orWhere('teacher_id', $teacher->id);
        })->withCount('students')
          ->orderBy('class_numeric')
          ->get();

        // Get current term (most recent by academic year and period)
        $currentTerm = ResultsStatus::orderBy('year', 'desc')
            ->orderByRaw("FIELD(result_period, 'third', 'second', 'first')")
            ->first();
        $currentYear = $currentTerm ? $currentTerm->year : date('Y');
        $currentPeriod = $currentTerm ? $currentTerm->result_period : 'first';

        // Get selected filters
        $selectedClassId = $request->get('class_id');
        $selectedYear = $request->get('year', $currentYear);
        $selectedTerm = $request->get('term', $currentPeriod);
        $selectedStudentId = $request->get('student_id');

        $students = collect();
        $analyticsData = null;
        $selectedClass = null;
        $selectedStudent = null;

        // If a class is selected, get students
        if ($selectedClassId) {
            $selectedClass = Grade::find($selectedClassId);
            $students = Student::where('class_id', $selectedClassId)
                ->with('user')
                ->whereHas('user')
                ->orderBy('roll_number')
                ->get();
        }

        // If a student is selected, get analytics data
        if ($selectedStudentId) {
            $selectedStudent = Student::with(['user', 'class'])->find($selectedStudentId);
            $analyticsData = $this->getStudentAnalytics($selectedStudentId, $selectedYear, $selectedTerm);
        }

        return view('teacher.student-analytics', compact(
            'teacherClasses',
            'students',
            'selectedClassId',
            'selectedClass',
            'selectedYear',
            'selectedTerm',
            'selectedStudentId',
            'selectedStudent',
            'analyticsData',
            'currentYear',
            'currentPeriod'
        ));
    }
}
