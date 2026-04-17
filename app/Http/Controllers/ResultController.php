<?php
namespace App\Http\Controllers;

use App\Result;
use App\Grade;
use App\Student;
use App\Parents;
use App\Subject;
use App\ResultsStatus;
use App\Teacher;
use App\StudentPayment;
use App\Assessment;
use App\AssessmentMark;
use App\PaymentVerification;
use App\ResultViewExemption;
use App\Http\Controllers\GroceryController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function index()
    {
        // Get the logged-in teacher
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            return view('backend.results.index', ['classes' => collect()]);
        }

        // Get all subjects taught by this teacher
        $teacherSubjects = $teacher->subjects;

        // Get all unique class IDs from the subjects this teacher teaches
        $classIds = [];
        foreach ($teacherSubjects as $subject) {
            $subjectClassIds = $subject->grades()->pluck('grades.id')->toArray();
            $classIds = array_merge($classIds, $subjectClassIds);
        }

        // Remove duplicates
        $classIds = array_unique($classIds);

        // Get all classes where teacher teaches subjects, with student count
        $classes = Grade::whereIn('id', $classIds)
            ->withCount('students')
            ->get();

        return view('backend.results.index', compact('classes'));
    }

    public function parentindex()
    {
        // Get the logged-in teacher
        $teacher = Auth::user()->teacher;

        // Get classes assigned to this teacher
        $classes = $teacher ? $teacher->classes : [];

        return view('backend.results.index', compact('classes'));
    }
    public function resultsactive()
    {
        // Get the logged-in teacher
        $classes = Grade::withCount('students')->latest()->paginate(10);

        return view('backend.activeresults.index', compact('classes'));
    }


    public function recordindex()
    {
        // Get the logged-in teacher
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            return view('backend.results.resultsrecord', ['classes' => collect(), 'years' => collect()]);
        }

        // Get all subjects taught by this teacher
        $teacherSubjects = $teacher->subjects;

        // Get all unique class IDs from the subjects this teacher teaches
        $classIds = [];
        foreach ($teacherSubjects as $subject) {
            $subjectClassIds = $subject->grades()->pluck('grades.id')->toArray();
            $classIds = array_merge($classIds, $subjectClassIds);
        }

        // Remove duplicates
        $classIds = array_unique($classIds);

        // Get all classes where teacher teaches subjects
        $classes = Grade::whereIn('id', $classIds)->get();

        $years = ResultsStatus::select('year')->distinct()->pluck('year');

        return view('backend.results.resultsrecord', compact('classes','years'));
    }

    public function Classnames($class_id)
    {
        // Get the class with its students and teacher
        $classes = Grade::with(['students.user', 'teacher'])->findOrFail($class_id);

        // Get results for students in this class
        $studentIds = $classes->students->pluck('id');
        $results = Result::with(['subject', 'student.user'])->whereIn('student_id', $studentIds)->get();

        // Check if current user is the class teacher
        $user = Auth::user();
        $isClassTeacher = false;

        if ($user && $user->hasRole('Teacher') && $user->teacher) {
            $isClassTeacher = $classes->teacher_id === $user->teacher->id;
        }

        // If class teacher, get comprehensive results data
        $classResults = null;
        $subjects = collect();
        $years = collect();

        if ($isClassTeacher) {
            // Get all unique subjects and years from results
            $subjects = $results->pluck('subject')->unique('id')->filter();
            $years = ResultsStatus::select('year', 'result_period')
                ->distinct()
                ->orderBy('year', 'desc')
                ->get();

            // Group results by student for easier display
            $classResults = $results->groupBy('student_id');
        }

        return view('backend.results.classname', compact('results', 'classes', 'isClassTeacher', 'classResults', 'subjects', 'years'));
    }

    public function adminclassnames($class_id)
    {
        // Get the class with its students
        $classes = Grade::with('students')->findOrFail($class_id);

        // Get results for students in this class
        $studentIds = $classes->students->pluck('id');
        $results = Result::whereIn('student_id', $studentIds)->get();

        return view('results.classname', compact('results', 'classes'));
    }



    ////test



    public function listResults($class_id)
    {
        // Get the class with its students
        $classes = Grade::with('students')->findOrFail($class_id);

        // Get student IDs from this class
        $studentIds =$classes->students->pluck('id');

        // Get the most recent ResultsStatus record by academic year and period
        $lastRecord = ResultsStatus::orderBy('year', 'desc')
            ->orderByRaw("FIELD(result_period, 'third', 'second', 'first')")
            ->first();

        // Initialize results query
        $resultsQuery = Result::whereIn('student_id', $studentIds);

        $exists = false;
        $results = collect();

        if ($lastRecord) {
            // Check if any results exist for the current period
            $exists = Result::whereIn('student_id', $studentIds)
                ->where('year', $lastRecord->year)
                ->where('result_period', $lastRecord->result_period)
                ->exists();

            // Fetch only results for the current period
            $results = Result::whereIn('student_id', $studentIds)
                ->where('year', $lastRecord->year)
                ->where('result_period', $lastRecord->result_period)
                ->get();
        }

        return view('backend.results.results', compact('results', 'classes', 'lastRecord', 'exists'));
    }



    public function activelistResults($class_id)
    {
        // Get the class with its students
        $classes = Grade::with('students')->findOrFail($class_id);

        // Get student IDs from this class
        $studentIds =$classes->students->pluck('id');

        // Get the most recent ResultsStatus record by academic year and period
        $lastRecord = ResultsStatus::orderBy('year', 'desc')
            ->orderByRaw("FIELD(result_period, 'third', 'second', 'first')")
            ->first();

        // Retrieve results for the specified students and check if they match the last record
        $resultsQuery = Result::whereIn('student_id', $studentIds);
        $Pending='Paid';
        $exists = false;
        if ($lastRecord) {
            $exists = $resultsQuery
                ->where('year', $lastRecord->year)
                ->where('result_period', $lastRecord->result_period)
                ->where('status', $Pending)
                ->exists();
        }

        // Fetch results after filtering
        $results = $resultsQuery->get();

        return view('backend.activeresults.results', compact('results', 'classes', 'lastRecord', 'exists'));
    }



    public function createByTeacher($classid)
    {
        $class = Grade::with(['students', 'subjects', 'teacher'])->findOrFail($classid);
        return view('backend.results.create', compact('class'));
    }


    public function edit($id)
    {
        $result = Result::findOrFail($id);

        return view('backend.results.edit', compact('result'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'score' => 'required|numeric',
            'comment' => 'nullable|string',
            'mark_grade' => 'nullable|string',
        ]);

        $result = Result::findOrFail($id);
        $result->update($request->all());

        return redirect()->route('results.index')->with('status', 'Result updated successfully!');
    }

    public function destroy($id)
    {
        // Find the result by ID or fail if it doesn't exist
        $result = Result::findOrFail($id);

        // Store the student ID for redirection after deletion
        $studentId = $result->student_id;

        // Delete the result
        $result->delete();

        // Fetch the updated results for the student
        $studentResults = Result::with('subject')->where('student_id', $studentId)->get();

        // Return the view with the updated student results
        return view('backend.results.viewstudentresults', compact('studentResults'));
    }
    public function showstudentresults($id)
    {
        // Retrieve the student by ID

        $studentResults = Result::with('subject')->where('student_id', $id)->get();
        return view('backend.results.viewstudentresults', compact('studentResults'));

    }

    public function viewupdateresults($id)
    {
        // Retrieve the student by ID

        $studentResults = Result::with('subject')->where('student_id', $id)->get();
        return view('backend.activeresults.viewstudentresults', compact('studentResults'));

    }

    public function changestatus($id)
    {
        // Retrieve the student results by ID and update the status
        $newStatus = 'Paid';
        Result::where('student_id', $id)->update(['status' => $newStatus]);

        // Fetch updated results for the student
        $studentResults = Result::where('student_id', $id)->get();
        $class_id = optional($studentResults->first())->class_id;

        //// zoro

        // Get the class with its students
        $classes = Grade::with('students')->findOrFail($class_id);

        // Get student IDs from this class
        $studentIds =$classes->students->pluck('id');

        // Get the most recent ResultsStatus record by academic year and period
        $lastRecord = ResultsStatus::orderBy('year', 'desc')
            ->orderByRaw("FIELD(result_period, 'third', 'second', 'first')")
            ->first();

        // Retrieve results for the specified students and check if they match the last record
        $resultsQuery = Result::whereIn('student_id',$studentIds);
        $Pending='Paid';
        $exists = false;

        if ($lastRecord) {
            $exists = $resultsQuery
                ->where('year', $lastRecord->year)
                ->where('result_period', $lastRecord->result_period)
                ->where('status', $Pending)
                ->exists();
        }

        // Fetch results after filtering
        $results = $resultsQuery->get();

        return view('backend.activeresults.results', compact('results', 'classes', 'lastRecord', 'exists'));
    }

    public function Showssubject(Student $student)
    {
        // Get the authenticated teacher
        $teacher = Teacher::findOrFail(auth()->user()->teacher->id);

        // Get the class with its subjects
        $class = Grade::with('subjects')->findOrFail($student->class_id);

        // Get the IDs of subjects that this teacher teaches
        $teacherSubjectIds = $teacher->subjects->pluck('id')->toArray();

        // Filter the class subjects to only include those the teacher teaches
        $class->subjects = $class->subjects->filter(function($subject) use ($teacherSubjectIds) {
            return in_array($subject->id, $teacherSubjectIds);
        });

        // Get the current period (most recent by academic year and period)
        $lastRecord = ResultsStatus::orderBy('year', 'desc')
            ->orderByRaw("FIELD(result_period, 'third', 'second', 'first')")
            ->first();

        // Fetch existing results for this student in the current period
        $existingResults = collect();
        if ($lastRecord) {
            $existingResults = Result::where('student_id', $student->id)
                ->where('year', $lastRecord->year)
                ->where('result_period', $lastRecord->result_period)
                ->get()
                ->keyBy('subject_id'); // Key by subject_id for easy lookup
        }

        return view('backend.results.studentsubject', compact('class', 'student', 'existingResults', 'lastRecord'));
    }
    public function Stuntentname($id)
    {
        $class = $id;
        $years = ResultsStatus::select('year')->distinct()->pluck('year');
        return view('backend.results.Allresults', compact('class','years'));
    }
    public function adminStuntentname($id)
    {
        $class = $id;
        $years = ResultsStatus::select('year')->distinct()->pluck('year');
        return view('results.Allresults', compact('class','years'));
    }

    public function store(Request $request)
    {
        $classid = $request->class_id;
        $teacher = Teacher::findOrFail(auth()->user()->teacher->id);
        $class = Grade::find($classid);



        $request->validate([
            'class_id' => 'required|numeric',
            'teacher_id' => 'required|numeric',
            'student_id' => 'required|numeric',
            'results' => 'required|array',
            'result_period'=> 'required',
        ]);

        // Get the explicit student ID from the form
        $targetStudentId = $request->student_id;

        // Get the teacher's subject IDs
        $teacherSubjectIds = $teacher->subjects->pluck('id')->toArray();

        // Get the class to verify subjects belong to this class
        $class = Grade::with('subjects')->findOrFail($request->class_id);
        $classSubjectIds = $class->subjects->pluck('id')->toArray();

        // Loop through results - only process for the target student
        foreach ($request->results as $studentId => $subjects) {
            // IMPORTANT: Only process results for the explicitly specified student
            if ($studentId != $targetStudentId) {
                continue;
            }

            foreach ($subjects as $subjectId => $data) {
                // Only allow results for subjects the teacher teaches AND that belong to this class
                if (!in_array($subjectId, $teacherSubjectIds) || !in_array($subjectId, $classSubjectIds)) {
                    continue;
                }

                Result::updateOrCreate(
                    [
                        'class_id' => $request->class_id,
                        'teacher_id' => $request->teacher_id,
                        'student_id' => $targetStudentId,
                        'subject_id' => $subjectId,
                        'year' => date('Y'),
                        'result_period' => $request->result_period,
                    ],
                    [
                        'marks' => $data['marks'],
                        'comment' => $data['comment'] ?? null,
                        'mark_grade' => $data['mark_grade'] ?? null,
                        'status' => 'Pending',
                    ]
                );
            }
        }

        return redirect()->route('results.results', $classid)
            ->with('status', 'Results entered successfully!');
    }

    public function viewstatus($classid)
    {
        $results = $classid;
        return view ('backend.results.status', compact('results'));
    }

    public function classname($classid)
    {
        $classes = $classid;
        return view ('backend.results.classname', compact('classes'));
    }







public function deleteResult($id)
{
    $result = Result::findOrFail($id);
    $result->delete();

    return redirect()->back()->with('status', 'Result deleted successfully!');
}

public function showResult(Request $request)
{
    // Validate the incoming request
    $request->validate([
        'year' => 'required|integer',
        'result_period' => 'required|string',
        'class_id' => 'required|integer',
    ]);

    // Note: class_id in the form actually contains the student_id
    $studentId = $request->class_id;

    $results = Result::with('subject')
        ->where('student_id', $studentId)
        ->where('year', $request->year)
        ->where('result_period', $request->result_period)
        ->get();

    return view('backend.results.studentview', compact('results'));
}

public function adminshowResult(Request $request)
{
    // Validate the incoming request
    $request->validate([
        'year' => 'required|integer',
        'result_period' => 'required|string',
        'class_id' => 'required|integer',
    ]);

    // Note: class_id in the form actually contains the student_id
    $studentId = $request->class_id;

    $results = Result::with('subject')
        ->where('student_id', $studentId)
        ->where('year', $request->year)
        ->where('result_period', $request->result_period)
        ->get();

    return view('results.studentview', compact('results'));
}




    public function show($studentId)
    {
        $results = Result::where('student_id', $studentId)->with(['subject', 'class'])->get();
        return view('backend.results.show', compact('results'));
    }


    public function studentshow()
    {
        $studentId = Student::where('user_id', Auth::user()->id)->value('id');

        // Check for outstanding fees
        $allTerms = ResultsStatus::all();
        $totalFees = 0;
        foreach ($allTerms as $term) {
            $totalFees += floatval($term->total_fees);
        }
        $totalPaid = floatval(StudentPayment::where('student_id', $studentId)->sum('amount_paid'));
        $outstandingFees = $totalFees - $totalPaid;

        // Check for grocery arrears (only if grocery blocking is enabled and applies to this student's type)
        $groceryBlockEnabled = GroceryController::isGroceryBlockEnabled();
        $student = Student::find($studentId);
        $isGroceryExempt = $student ? $student->grocery_exempt : false;
        $appliesToType = $groceryBlockEnabled && GroceryController::isStudentTypeBlocked($student);
        $groceryArrears = ($appliesToType && !$isGroceryExempt) ? GroceryController::calculateGroceryBalance($studentId) : 0;

        // If student has outstanding fees OR grocery arrears (when enabled and not exempt), block results view
        if ($outstandingFees > 0 || ($groceryBlockEnabled && !$isGroceryExempt && $groceryArrears > 0)) {
            $messages = [];
            if ($outstandingFees > 0) {
                $messages[] = 'Outstanding school fees: $' . number_format($outstandingFees, 2);
            }
            if ($groceryBlockEnabled && $groceryArrears > 0) {
                $messages[] = 'Outstanding groceries: $' . number_format($groceryArrears, 2);
            }

            return view('reports.blocked', [
                'message' => 'You cannot view your results due to outstanding balance.',
                'outstanding' => $outstandingFees,
                'grocery_arrears' => $groceryBlockEnabled ? $groceryArrears : 0,
                'details' => $messages
            ]);
        }

        $lastRecord = ResultsStatus::orderBy('year', 'desc')
            ->orderByRaw("FIELD(result_period, 'third', 'second', 'first')")
            ->first();
        $year = $lastRecord->year;
        $period = $lastRecord->result_period;
        
        // Check if student has exemption for this term/year
        $hasExemption = ResultViewExemption::isExempted($studentId, $year, $period);
        
        $results = Result::where('student_id', $studentId)
        ->where('result_period', $period)
        ->where('year', $year)
        ->where(function($query) use ($hasExemption) {
            // If student has exemption, show all approved results regardless of payment status
            if ($hasExemption) {
                $query->where('approved', true);
            } else {
                // No exemption: require both 'Paid' status AND approved
                $query->where('status', 'Paid')->where('approved', true);
            }
        })
        ->with(['subject', 'teacher', 'class'])
        ->get();

        return view('reports.index', compact('results'));
    }


    public function viewstudentshow()
    {
        $parentId = Parents::where('user_id', Auth::user()->id)->value('id');

        // Check if parent record exists
        if (!$parentId) {
            return view('reports.index', [
                'students' => collect(),
                'results' => collect(),
                'error' => 'Parent record not found. Please contact administrator.'
            ]);
        }

        // Get ALL students for this parent
        $students = Student::where('parent_id', $parentId)->with('user', 'class')->get();

        if ($students->isEmpty()) {
            return view('reports.index', [
                'students' => collect(),
                'results' => collect(),
                'error' => 'No students linked to this parent account.'
            ]);
        }

        $studentIds = $students->pluck('id');

        // Check for outstanding fees first
        $allTerms = ResultsStatus::all();
        $totalFees = 0;
        if ($allTerms->isNotEmpty()) {
            foreach ($allTerms as $term) {
                $totalFees += floatval($term->total_fees ?? 0);
            }
        }
        $totalPaid = floatval(StudentPayment::whereIn('student_id', $studentIds)->sum('amount_paid'));
        $outstandingFees = $totalFees - $totalPaid;

        // Check for grocery arrears for all children (only if grocery blocking is enabled and not exempt)
        $groceryBlockEnabled = GroceryController::isGroceryBlockEnabled();
        $totalGroceryArrears = 0;
        if ($groceryBlockEnabled) {
            foreach ($students as $student) {
                // only count arrears for children whose student type is subject to blocking and who are not exempt
                if (!GroceryController::isStudentTypeBlocked($student)) continue;
                if (!$student->grocery_exempt) {
                    $totalGroceryArrears += GroceryController::calculateGroceryBalance($student->id);
                }
            }
        }

        // If there are outstanding fees OR grocery arrears (when enabled), block results
        if ($outstandingFees > 0 || ($groceryBlockEnabled && $totalGroceryArrears > 0)) {
            // Check for verified payment for this parent (only for fees)
            $hasVerifiedPayment = PaymentVerification::where('parent_id', $parentId)
                ->where('status', 'verified')
                ->exists();

            // If no verified payment OR has grocery arrears (when enabled), block results
            if (!$hasVerifiedPayment || ($groceryBlockEnabled && $totalGroceryArrears > 0)) {
                // Check if there's a pending verification
                $pendingVerification = PaymentVerification::where('parent_id', $parentId)
                    ->where('status', 'pending')
                    ->exists();

                $messages = [];
                if ($outstandingFees > 0 && !$hasVerifiedPayment) {
                    $messages[] = 'Outstanding school fees: $' . number_format($outstandingFees, 2);
                }
                if ($groceryBlockEnabled && $totalGroceryArrears > 0) {
                    $messages[] = 'Outstanding groceries: $' . number_format($totalGroceryArrears, 2);
                }

                $message = 'You cannot view your child\'s results due to outstanding balance.';
                if ($pendingVerification && $outstandingFees > 0) {
                    $message = 'Your payment verification is pending approval. Please wait for admin confirmation.';
                }

                return view('reports.blocked', [
                    'message' => $message,
                    'outstanding' => $outstandingFees,
                    'grocery_arrears' => $groceryBlockEnabled ? $totalGroceryArrears : 0,
                    'details' => $messages,
                    'show_verification_link' => $outstandingFees > 0 && !$hasVerifiedPayment,
                    'pending' => $pendingVerification
                ]);
            }
        }

        // Get exempted student/year/term combinations
        $exemptions = ResultViewExemption::whereIn('student_id', $studentIds)->get();
        $exemptionMap = [];
        foreach ($exemptions as $exemption) {
            $key = $exemption->student_id . '_' . $exemption->year . '_' . $exemption->term;
            $exemptionMap[$key] = true;
        }

        // Get ALL results for all students (all terms, all years) - approved OR exempted
        $results = Result::whereIn('student_id', $studentIds)
            ->where(function($query) use ($exemptionMap, $studentIds) {
                $query->where('approved', true);
                // Also include unapproved results for exempted students
                if (!empty($exemptionMap)) {
                    $query->orWhere(function($q) use ($exemptionMap) {
                        foreach ($exemptionMap as $key => $val) {
                            list($studentId, $year, $term) = explode('_', $key);
                            $q->orWhere(function($subQ) use ($studentId, $year, $term) {
                                $subQ->where('student_id', $studentId)
                                     ->where('year', $year)
                                     ->where('result_period', $term);
                            });
                        }
                    });
                }
            })
            ->with(['subject', 'teacher', 'class', 'student.user'])
            ->orderBy('year', 'desc')
            ->orderBy('result_period', 'desc')
            ->orderBy('student_id')
            ->get();

        return view('reports.index', compact('results', 'students'));
    }

    /**
     * Admin view results - Display class cards with view results button
     */
    public function adminViewResults()
    {
        $classes = Grade::withCount('students')->orderBy('class_name')->get();
        $years = ResultsStatus::select('year')->distinct()->orderBy('year', 'desc')->pluck('year');
        $terms = ResultsStatus::all();

        return view('backend.results.admin-view-results', compact('classes', 'years', 'terms'));
    }

    /**
     * Get results for admin by class, year and term
     */
    public function getAdminResults(Request $request)
    {
        $classId = $request->class_id;
        $year = $request->year;
        $term = $request->term;

        $class = Grade::with('students.user')->findOrFail($classId);
        $studentIds = $class->students->pluck('id');

        $results = Result::with(['student.user', 'subject'])
            ->whereIn('student_id', $studentIds)
            ->where('year', $year)
            ->where('result_period', $term)
            ->get()
            ->groupBy('student_id');

        return response()->json([
            'class' => $class,
            'results' => $results,
            'students' => $class->students
        ]);
    }

    /**
     * Parent view student assessments
     */
    public function parentAssessments()
    {
        $parentId = Parents::where('user_id', Auth::user()->id)->value('id');

        if (!$parentId) {
            return view('backend.parent.assessments', [
                'students' => collect(),
                'assessments' => collect(),
                'error' => 'Parent record not found. Please contact administrator.'
            ]);
        }

        // Get ALL students for this parent
        $students = Student::where('parent_id', $parentId)->with('user', 'class')->get();

        if ($students->isEmpty()) {
            return view('backend.parent.assessments', [
                'students' => collect(),
                'assessments' => collect(),
                'error' => 'No students linked to this parent account.'
            ]);
        }

        $studentIds = $students->pluck('id');

        // Get assessment marks for all students
        $assessmentMarks = AssessmentMark::whereIn('student_id', $studentIds)
            ->with(['assessment.subject', 'assessment.class', 'assessment.teacher.user', 'student.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Group by assessment for better display
        $groupedAssessments = $assessmentMarks->groupBy('assessment_id');

        return view('backend.parent.assessments', compact('students', 'assessmentMarks', 'groupedAssessments'));
    }

    /**
     * Clean/Delete results records by class, term, and year
     */
    public function cleanResults(Request $request)
    {
        try {
            $cleanAll = $request->clean_all;
            $classId = $request->class_id;
            $year = $request->year;
            $term = $request->term;

            $query = Result::query();

            if ($cleanAll) {
                // Delete all results
                $deletedCount = $query->delete();
            } else {
                // Apply filters
                if ($classId) {
                    $query->where('class_id', $classId);
                }
                if ($year) {
                    $query->where('year', $year);
                }
                if ($term) {
                    $query->where('result_period', $term);
                }

                $deletedCount = $query->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'Results cleaned successfully.',
                'deleted_count' => $deletedCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clean results: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin view pending results for approval
     */
    public function pendingApproval()
    {
        $classes = Grade::withCount('students')->orderBy('class_name')->get();
        $years = ResultsStatus::select('year')->distinct()->orderBy('year', 'desc')->pluck('year');

        // Get count of pending results per class/term/year
        $pendingCounts = Result::where('approved', false)
            ->selectRaw('class_id, year, result_period, COUNT(*) as count')
            ->groupBy('class_id', 'year', 'result_period')
            ->get();

        return view('backend.results.pending-approval', compact('classes', 'years', 'pendingCounts'));
    }

    /**
     * Get pending results for a specific class/term/year
     */
    public function getPendingResults(Request $request)
    {
        $classId = $request->class_id;
        $year = $request->year;
        $term = $request->term;

        $class = Grade::with('students.user')->findOrFail($classId);
        $studentIds = $class->students->pluck('id');

        $results = Result::with(['student.user', 'subject', 'teacher.user'])
            ->whereIn('student_id', $studentIds)
            ->where('year', $year)
            ->where('result_period', $term)
            ->where('approved', false)
            ->get()
            ->groupBy('student_id');

        $pendingCount = Result::whereIn('student_id', $studentIds)
            ->where('year', $year)
            ->where('result_period', $term)
            ->where('approved', false)
            ->count();

        return response()->json([
            'class' => $class,
            'results' => $results,
            'students' => $class->students,
            'pending_count' => $pendingCount
        ]);
    }

    /**
     * Approve results for a class/term/year
     */
    public function approveResults(Request $request)
    {
        $classId = $request->class_id;
        $year = $request->year;
        $term = $request->term;
        $studentIds = $request->student_ids; // Optional: specific students

        $class = Grade::findOrFail($classId);
        $allStudentIds = $class->students->pluck('id')->toArray();

        // If specific students provided, use those, otherwise approve all
        $targetStudentIds = $studentIds ? array_intersect($studentIds, $allStudentIds) : $allStudentIds;

        $updated = Result::whereIn('student_id', $targetStudentIds)
            ->where('year', $year)
            ->where('result_period', $term)
            ->where('approved', false)
            ->update([
                'approved' => true,
                'approved_by' => Auth::id(),
                'approved_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => "Successfully approved {$updated} results.",
            'approved_count' => $updated
        ]);
    }

    /**
     * Approve ALL pending results across all classes
     */
    public function approveAllResults()
    {
        $updated = Result::where('approved', false)
            ->update([
                'approved' => true,
                'approved_by' => Auth::id(),
                'approved_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => "Successfully approved {$updated} results across all classes.",
            'approved_count' => $updated
        ]);
    }

    /**
     * Reject/Unapprove results (set back to pending)
     */
    public function rejectResults(Request $request)
    {
        $classId = $request->class_id;
        $year = $request->year;
        $term = $request->term;

        $class = Grade::findOrFail($classId);
        $studentIds = $class->students->pluck('id');

        $updated = Result::whereIn('student_id', $studentIds)
            ->where('year', $year)
            ->where('result_period', $term)
            ->where('approved', true)
            ->update([
                'approved' => false,
                'approved_by' => null,
                'approved_at' => null
            ]);

        return response()->json([
            'success' => true,
            'message' => "Successfully rejected {$updated} results. They are now pending approval.",
            'rejected_count' => $updated
        ]);
    }

    /**
     * Admin view pending assessment marks for approval
     */
    public function pendingAssessmentMarks()
    {
        $classes = Grade::withCount('students')->orderBy('class_name')->get();

        // Get count of pending assessment marks per class
        $pendingCounts = AssessmentMark::where('approved', false)
            ->join('assessments', 'assessment_marks.assessment_id', '=', 'assessments.id')
            ->selectRaw('assessments.class_id, COUNT(DISTINCT assessment_marks.assessment_id) as assessment_count, COUNT(*) as marks_count')
            ->groupBy('assessments.class_id')
            ->get()
            ->keyBy('class_id');

        $totalPending = AssessmentMark::where('approved', false)->count();

        return view('backend.results.pending-assessment-marks', compact('classes', 'pendingCounts', 'totalPending'));
    }

    /**
     * Get pending assessment marks for a specific class
     */
    public function getPendingAssessmentMarks(Request $request)
    {
        $classId = $request->class_id;

        $class = Grade::with('students.user')->findOrFail($classId);

        // Get assessments with pending marks for this class
        $assessments = Assessment::where('class_id', $classId)
            ->whereHas('marks', function($query) {
                $query->where('approved', false);
            })
            ->with(['subject', 'teacher.user'])
            ->withCount(['marks as pending_count' => function($query) {
                $query->where('approved', false);
            }])
            ->orderBy('date', 'desc')
            ->get();

        return response()->json([
            'class' => $class,
            'assessments' => $assessments
        ]);
    }

    /**
     * Get student marks for a specific assessment
     */
    public function getAssessmentMarksForApproval(Request $request)
    {
        $assessmentId = $request->assessment_id;

        $marks = AssessmentMark::where('assessment_id', $assessmentId)
            ->where('approved', false)
            ->with('student.user')
            ->get()
            ->groupBy('student_id');

        $assessment = Assessment::with(['subject', 'teacher.user'])->findOrFail($assessmentId);

        return response()->json([
            'assessment' => $assessment,
            'marks' => $marks
        ]);
    }

    /**
     * Approve assessment marks
     */
    public function approveAssessmentMarks(Request $request)
    {
        $assessmentId = $request->assessment_id;
        $classId = $request->class_id;

        $query = AssessmentMark::where('approved', false);

        if ($assessmentId) {
            $query->where('assessment_id', $assessmentId);
        } elseif ($classId) {
            $query->whereHas('assessment', function($q) use ($classId) {
                $q->where('class_id', $classId);
            });
        }

        $updated = $query->update([
            'approved' => true,
            'approved_by' => Auth::id(),
            'approved_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => "Successfully approved {$updated} assessment marks.",
            'approved_count' => $updated
        ]);
    }

    /**
     * Approve ALL pending assessment marks
     */
    public function approveAllAssessmentMarks()
    {
        $updated = AssessmentMark::where('approved', false)
            ->update([
                'approved' => true,
                'approved_by' => Auth::id(),
                'approved_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => "Successfully approved {$updated} assessment marks across all classes.",
            'approved_count' => $updated
        ]);
    }

    /**
     * Exempt a student to view results for a specific term/year
     */
    public function exemptStudentResults(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'year' => 'required|string',
            'term' => 'required|string',
            'reason' => 'nullable|string|max:500',
        ]);

        $exemption = ResultViewExemption::updateOrCreate(
            [
                'student_id' => $request->student_id,
                'year' => $request->year,
                'term' => $request->term,
            ],
            [
                'exempted_by' => Auth::id(),
                'reason' => $request->reason,
            ]
        );

        $student = Student::with('user')->find($request->student_id);

        return response()->json([
            'success' => true,
            'message' => "{$student->user->name} can now view results for {$request->term} term {$request->year}.",
            'exemption' => $exemption
        ]);
    }

    /**
     * Remove exemption for a student
     */
    public function removeExemption(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'year' => 'required|string',
            'term' => 'required|string',
        ]);

        $deleted = ResultViewExemption::where('student_id', $request->student_id)
            ->where('year', $request->year)
            ->where('term', $request->term)
            ->delete();

        return response()->json([
            'success' => $deleted > 0,
            'message' => $deleted > 0 ? 'Exemption removed successfully.' : 'No exemption found to remove.'
        ]);
    }

    /**
     * Get exempted students for a class/term/year
     */
    public function getExemptedStudents(Request $request)
    {
        $classId = $request->class_id;
        $year = $request->year;
        $term = $request->term;

        $class = Grade::findOrFail($classId);
        $studentIds = $class->students->pluck('id');

        $exemptions = ResultViewExemption::whereIn('student_id', $studentIds)
            ->where('year', $year)
            ->where('term', $term)
            ->with(['student.user', 'exemptedByUser'])
            ->get();

        return response()->json([
            'success' => true,
            'exemptions' => $exemptions
        ]);
    }
}



