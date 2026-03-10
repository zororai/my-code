<?php

namespace App\Http\Controllers;

use App\User;
use App\Teacher;
use App\Grade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Helpers\SmsHelper;
use App\SchoolSetting;
use Illuminate\Support\Facades\Storage;

class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $teachers = Teacher::with('user')->latest()->paginate(10);

        return view('backend.teachers.index', compact('teachers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.teachers.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'gender' => 'required|string',
            'phone'  => 'required|string|max:255|unique:teachers,phone',
            'email'  => 'nullable|email|max:255|unique:users,email'
        ]);

        // Default password for first login
        $password = '12345678';
        
        // Use provided email or generate placeholder from phone number
        $hasRealEmail = !empty($request->email);
        if ($hasRealEmail) {
            $userEmail = $request->email;
        } else {
            $cleanPhone = preg_replace('/[^0-9]/', '', $request->phone);
            $userEmail = 'teacher_' . $cleanPhone . '@placeholder.co.zw';
        }

        // Check if user with this email already exists (from failed previous attempt)
        $existingUser = User::where('email', $userEmail)->first();
        
        if ($existingUser) {
            // Check if this user already has a teacher profile
            if ($existingUser->teacher) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['phone' => 'A teacher with this phone number already exists.']);
            }
            
            // Orphaned user record - delete it and proceed
            $existingUser->delete();
        }

        $user = User::create([
            'name'      => $request->name,
            'email'     => $userEmail,
            'password'  => Hash::make($password),
            'profile_picture' => 'avatar.png'
        ]);

        $teacher = $user->teacher()->create([
            'gender'            => $request->gender,
            'phone'             => $request->phone,
            'dateofbirth'       => null,
            'current_address'   => null,
            'permanent_address' => null,
            'is_class_teacher'  => $request->has('is_class_teacher'),
            'is_hod'            => $request->has('is_hod'),
            'is_sport_director' => $request->has('is_sport_director'),
            'session'           => $request->session ?? 'dual',
        ]);

        $user->assignRole('Teacher');

        // Generate QR code for teacher attendance
        $this->generateTeacherQrCode($teacher);

        // Send email notification with credentials (only if real email provided)
        if ($hasRealEmail) {
            $this->sendCredentialsEmail($user, $password);
        }

        // Send SMS notification with credentials (phone number is username for login)
        $this->sendCredentialsSms($request->phone, $user->name, $request->phone, $password);

        $successMessage = 'Teacher created successfully! Login credentials have been sent via SMS.';
        if ($hasRealEmail) {
            $successMessage = 'Teacher created successfully! Login credentials have been sent via email and SMS.';
        }

        return redirect()->route('teacher.index')
            ->with('success', $successMessage);
    }

    /**
     * Send credentials email to new teacher.
     */
    private function sendCredentialsEmail($user, $password)
    {
        try {
            Mail::send('emails.teacher-credentials', [
                'name' => $user->name,
                'email' => $user->email,
                'password' => $password
            ], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Your Teacher Account Credentials - ' . config('app.name'));
            });
        } catch (\Exception $e) {
            \Log::error('Failed to send teacher credentials email: ' . $e->getMessage());
        }
    }

    /**
     * Send credentials SMS to new teacher.
     */
    private function sendCredentialsSms($phone, $name, $email, $password)
    {
        try {
            // Get configurable country code from settings
            $countryCode = SchoolSetting::get('sms_country_code', '+263');
            
            // Format phone number (ensure it has country code)
            $phone = preg_replace('/\s+/', '', $phone);
            if (!preg_match('/^\+/', $phone)) {
                $phone = $countryCode . ltrim($phone, '0');
            }
            
            // Get message template from settings and replace placeholders
            $messageTemplate = SchoolSetting::get(
                'sms_teacher_credentials_template',
                'RSH School: Teacher account created. Login: {phone}, Password: {password}. Complete profile on first login.'
            );
            
            $message = str_replace(
                ['{name}', '{phone}', '{password}'],
                [$name, $email, $password],
                $messageTemplate
            );
            
            // Send SMS using SmsHelper
            $result = \App\Helpers\SmsHelper::sendSms($phone, $message);
            
            if ($result['success']) {
                \Log::info('Teacher credentials SMS sent successfully', [
                    'phone' => $phone,
                    'teacher_name' => $name
                ]);
            } else {
                \Log::warning('Failed to send teacher credentials SMS', [
                    'phone' => $phone,
                    'error' => $result['message'] ?? 'Unknown error'
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send teacher credentials SMS: ' . $e->getMessage());
        }
    }

    /**
     * Generate QR code for teacher attendance.
     */
    private function generateTeacherQrCode($teacher)
    {
        try {
            // Generate unique QR token
            $qrToken = Str::uuid()->toString();

            // Use external API to generate QR code
            $qrApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($qrToken);
            $qrImage = file_get_contents($qrApiUrl);

            if ($qrImage === false) {
                throw new \Exception('Failed to fetch QR code from external API');
            }

            $fileName = 'teacher_qr_' . $teacher->id . '.png';
            $filePath = 'qrcodes/teachers/' . $fileName;

            Storage::disk('public')->put($filePath, $qrImage);

            $teacher->update([
                'qr_code_token' => $qrToken,
                'qr_code' => $filePath,
            ]);

            \Log::info('QR code generated for teacher', [
                'teacher_id' => $teacher->id,
                'qr_code_path' => $filePath
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to generate QR code for teacher: ' . $e->getMessage());
        }
    }

    /**
     * Show password change form for teacher.
     */
    public function showChangePasswordForm()
    {
        return view('backend.teacher.change-password');
    }

    /**
     * Update teacher profile and password.
     */
    public function updatePassword(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'email'             => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'dateofbirth'       => 'required|date',
            'current_address'   => 'required|string|max:500',
            'permanent_address' => 'required|string|max:500',
            'current_password'  => 'required',
            'password'          => 'required|string|min:8|confirmed|different:current_password',
            'profile_picture'   => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'password.different' => 'New password must be different from the default password.',
            'profile_picture.image' => 'The file must be an image.',
            'profile_picture.mimes' => 'The image must be a JPEG, PNG, JPG, or GIF.',
            'profile_picture.max' => 'The image must not exceed 2MB.',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        // Handle profile picture upload
        if ($request->hasFile('profile_picture') && $request->file('profile_picture')->isValid()) {
            $file = $request->file('profile_picture');
            $profile = Str::slug($user->name).'-'.$user->id.'.'.$file->getClientOriginalExtension();
            
            // Ensure directory exists
            $uploadPath = public_path('images/profile');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            
            $file->move($uploadPath, $profile);
            $user->update(['profile_picture' => $profile]);
        }

        // Update email, password, and mark profile as completed
        $user->update([
            'email'                => $request->email,
            'password'             => Hash::make($request->password),
            'must_change_password' => false
        ]);

        // Update teacher profile details
        $user->teacher()->update([
            'dateofbirth'       => $request->dateofbirth,
            'current_address'   => $request->current_address,
            'permanent_address' => $request->permanent_address
        ]);

        return redirect()->route('home')->with('success', 'Profile completed successfully!');
    }

    /**
     * Show teacher's own attendance history.
     */
    public function myAttendance(Request $request)
    {
        $teacher = auth()->user()->teacher;
        
        if (!$teacher) {
            return redirect()->route('home')->with('error', 'Teacher profile not found.');
        }

        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $attendance = \App\TeacherAttendance::where('teacher_id', $teacher->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date', 'desc')
            ->get();

        // Calculate statistics
        $totalDays = $attendance->count();
        $presentDays = $attendance->whereNotNull('check_in_time')->count();
        $lateDays = $attendance->filter(function($record) {
            return $record->check_in_time && $record->check_in_time > '08:00:00';
        })->count();

        return view('backend.teacher.my-attendance', compact(
            'teacher', 
            'attendance', 
            'month', 
            'year', 
            'totalDays', 
            'presentDays', 
            'lateDays'
        ));
    }

    /**
     * Teacher self-checkout with reason if early checkout.
     */
    public function selfCheckout(Request $request)
    {
        $teacher = auth()->user()->teacher;
        
        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher profile not found.'
            ], 404);
        }

        $today = today();
        $now = now();
        
        // Find today's attendance record
        $attendance = \App\TeacherAttendance::where('teacher_id', $teacher->id)
            ->whereDate('date', $today)
            ->first();

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'No check-in record found for today. Please check in first.'
            ], 400);
        }

        if ($attendance->check_out_time) {
            return response()->json([
                'success' => false,
                'message' => 'You have already checked out today at ' . \Carbon\Carbon::parse($attendance->check_out_time)->format('H:i')
            ], 400);
        }

        // Define expected checkout time based on teacher session
        $expectedCheckoutTime = '16:30'; // Default 4:30 PM
        if ($teacher->session === 'morning') {
            $expectedCheckoutTime = '13:00'; // 1:00 PM for morning session
        } elseif ($teacher->session === 'afternoon') {
            $expectedCheckoutTime = '17:00'; // 5:00 PM for afternoon session
        }

        $currentTime = $now->format('H:i');
        $isEarlyCheckout = $currentTime < $expectedCheckoutTime;

        // Require reason if early checkout
        if ($isEarlyCheckout && empty($request->checkout_reason)) {
            return response()->json([
                'success' => false,
                'requires_reason' => true,
                'message' => 'You are checking out early (before ' . $expectedCheckoutTime . '). Please provide a reason.',
                'expected_time' => $expectedCheckoutTime,
                'current_time' => $currentTime
            ], 400);
        }

        // Update attendance with checkout
        $attendance->update([
            'check_out_time' => $now,
            'checkout_reason' => $request->checkout_reason,
            'expected_checkout_time' => $expectedCheckoutTime,
            'status' => 'OUT',
        ]);

        $checkIn = \Carbon\Carbon::parse($attendance->check_in_time);
        $duration = $now->diffForHumans($checkIn, true);

        return response()->json([
            'success' => true,
            'message' => 'Successfully checked out! Duration: ' . $duration,
            'check_out_time' => $now->format('H:i'),
            'duration' => $duration,
            'was_early' => $isEarlyCheckout
        ]);
    }

    /**
     * Show students in the class where the logged-in teacher is the class teacher.
     */
    public function myClassStudents()
    {
        $teacher = auth()->user()->teacher;
        
        if (!$teacher || !$teacher->is_class_teacher) {
            return redirect()->route('home')->with('error', 'You are not assigned as a class teacher.');
        }

        $class = Grade::where('teacher_id', $teacher->id)->with('students.user')->first();
        
        if (!$class) {
            return redirect()->route('home')->with('error', 'No class assigned to you.');
        }

        $students = $class->students()->with('user')->orderBy('id')->get();

        return view('backend.teacher.my-class-students', compact('class', 'students'));
    }

    /**
     * Show attendance register for the class teacher's class.
     */
    public function classAttendance(Request $request)
    {
        $teacher = auth()->user()->teacher;
        
        if (!$teacher || !$teacher->is_class_teacher) {
            return redirect()->route('home')->with('error', 'You are not assigned as a class teacher.');
        }

        $class = Grade::where('teacher_id', $teacher->id)->with('students.user')->first();
        
        if (!$class) {
            return redirect()->route('home')->with('error', 'No class assigned to you.');
        }

        $date = $request->get('date', now()->format('Y-m-d'));
        $students = $class->students()->with('user')->orderBy('id')->get();
        
        // Get existing attendance for this date
        $attendances = \App\Attendance::where('class_id', $class->id)
            ->whereDate('attendence_date', $date)
            ->get()
            ->keyBy('student_id');

        return view('backend.teacher.class-attendance', compact('class', 'students', 'date', 'attendances'));
    }

    /**
     * Store attendance for the class teacher's class.
     */
    public function storeClassAttendance(Request $request)
    {
        $teacher = auth()->user()->teacher;
        
        if (!$teacher || !$teacher->is_class_teacher) {
            return redirect()->route('home')->with('error', 'You are not assigned as a class teacher.');
        }

        $class = Grade::where('teacher_id', $teacher->id)->first();
        
        if (!$class) {
            return redirect()->route('home')->with('error', 'No class assigned to you.');
        }

        $request->validate([
            'date' => 'required|date',
            'attendance' => 'required|array',
        ]);

        foreach ($request->attendance as $studentId => $status) {
            // Convert status string to boolean
            $statusBoolean = ($status === 'present') ? true : false;
            
            \App\Attendance::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'class_id' => $class->id,
                    'attendence_date' => $request->date,
                ],
                [
                    'attendence_status' => $statusBoolean,
                    'teacher_id' => $teacher->id,
                ]
            );
        }

        return redirect()->route('teacher.class-attendance', ['date' => $request->date])
            ->with('success', 'Attendance saved successfully!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Teacher  $teacher
     * @return \Illuminate\Http\Response
     */
    public function show(Teacher $teacher)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Teacher  $teacher
     * @return \Illuminate\Http\Response
     */
    public function edit(Teacher $teacher)
    {
        $teacher = Teacher::with('user')->findOrFail($teacher->id);

        return view('backend.teachers.edit', compact('teacher'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Teacher  $teacher
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Teacher $teacher)
    {
        $request->validate([
            'name'              => 'required|string|max:255',
            'email'             => 'required|string|email|max:255|unique:users,email,'.$teacher->user_id,
            'gender'            => 'required|string',
            'phone'             => 'required|string|max:255',
            'dateofbirth'       => 'nullable|date',
            'current_address'   => 'nullable|string|max:255',
            'permanent_address' => 'nullable|string|max:255'
        ]);

        $user = User::findOrFail($teacher->user_id);

        if ($request->hasFile('profile_picture')) {
            $profile = Str::slug($user->name).'-'.$user->id.'.'.$request->profile_picture->getClientOriginalExtension();
            $request->profile_picture->move(public_path('images/profile'), $profile);
        } else {
            $profile = $user->profile_picture;
        }

        $user->update([
            'name'              => $request->name,
            'email'             => $request->email,
            'profile_picture'   => $profile
        ]);

        $user->teacher()->update([
            'gender'            => $request->gender,
            'phone'             => $request->phone,
            'dateofbirth'       => $request->dateofbirth,
            'current_address'   => $request->current_address,
            'permanent_address' => $request->permanent_address,
            'is_class_teacher'  => $request->has('is_class_teacher'),
            'is_hod'            => $request->has('is_hod'),
            'is_sport_director' => $request->has('is_sport_director'),
        ]);

        return redirect()->route('teacher.index');
    }

    /**
     * Display classes taught by the logged-in teacher.
     *
     * @return \Illuminate\Http\Response
     */
    public function studentRecord()
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return redirect()->route('home')->with('error', 'Teacher profile not found.');
        }

        // Get classes where teacher is class teacher
        $classTeacherClasses = $teacher->classes()->withCount('students')->get();
        
        // Get classes through subjects the teacher teaches
        $teacherSubjectIds = $teacher->subjects->pluck('id')->toArray();
        $subjectClasses = Grade::whereHas('subjects', function($query) use ($teacherSubjectIds) {
            $query->whereIn('subjects.id', $teacherSubjectIds);
        })->withCount('students')->get();
        
        // Merge and remove duplicates
        $classes = $classTeacherClasses->merge($subjectClasses)->unique('id');

        return view('backend.teacher.student-record', compact('classes'));
    }

    /**
     * Display students in a specific class.
     *
     * @param  int  $class_id
     * @return \Illuminate\Http\Response
     */
    public function classStudents($class_id)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return redirect()->route('home')->with('error', 'Teacher profile not found.');
        }

        // Get classes where teacher is class teacher
        $classTeacherClassIds = $teacher->classes()->pluck('id')->toArray();
        
        // Get classes through subjects the teacher teaches
        $teacherSubjectIds = $teacher->subjects->pluck('id')->toArray();
        $subjectClassIds = Grade::whereHas('subjects', function($query) use ($teacherSubjectIds) {
            $query->whereIn('subjects.id', $teacherSubjectIds);
        })->pluck('id')->toArray();
        
        // Merge all class IDs
        $allowedClassIds = array_unique(array_merge($classTeacherClassIds, $subjectClassIds));
        
        // Verify the class belongs to this teacher (either as class teacher or subject teacher)
        if (!in_array($class_id, $allowedClassIds)) {
            return redirect()->route('teacher.studentrecord')->with('error', 'You do not have access to this class.');
        }
        
        $class = Grade::withCount('students')->findOrFail($class_id);

        // Get students in the class with their parent relationships and subjects
        $students = $class->students()->with(['user', 'parents.user', 'class.subjects'])->get();

        return view('backend.teacher.class-students', compact('class', 'students'));
    }

    /**
     * Mark a student as transferred.
     *
     * @param  int  $student_id
     * @return \Illuminate\Http\Response
     */
    public function transferStudent($student_id)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return redirect()->route('home')->with('error', 'Teacher profile not found.');
        }

        $student = \App\Student::findOrFail($student_id);

        // Verify the student belongs to a class taught by this teacher
        $class = $teacher->classes()->where('id', $student->class_id)->first();

        if (!$class) {
            return back()->with('error', 'You do not have permission to transfer this student.');
        }

        // Mark student as transferred
        $student->is_transferred = true;
        $student->save();

        // Disable the user account
        $student->user->update(['is_active' => false]);

        return back()->with('success', 'Student has been marked as transferred.');
    }

    /**
     * Display student assessment page for teachers.
     *
     * @return \Illuminate\Http\Response
     */
    public function assessment()
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return redirect()->route('home')->with('error', 'Teacher profile not found.');
        }

        // Get classes where teacher is class teacher
        $classTeacherClasses = $teacher->classes()->withCount('students')->get();
        
        // Get classes through subjects the teacher teaches
        $teacherSubjectIds = $teacher->subjects->pluck('id')->toArray();
        $subjectClasses = Grade::whereHas('subjects', function($query) use ($teacherSubjectIds) {
            $query->whereIn('subjects.id', $teacherSubjectIds);
        })->withCount('students')->get();
        
        // Merge and remove duplicates
        $classes = $classTeacherClasses->merge($subjectClasses)->unique('id');
        $classIds = $classes->pluck('id')->toArray();

        // Get subjects taught by this teacher
        $subjects = $teacher->subjects()->get();

        // Assessment types to track
        $assessmentTypes = ['Quiz', 'Test', 'In Class Test', 'Monthly Test', 'Assignment', 'Exercise', 'Project', 'Fort Night', 'Exam', 'Vacation Exam', 'National Exam'];

        // Build performance data per subject
        $subjectPerformance = [];
        foreach ($subjects as $subject) {
            $typeStats = [];
            foreach ($assessmentTypes as $type) {
                $assessments = \App\Assessment::where('subject_id', $subject->id)
                    ->where('teacher_id', $teacher->id)
                    ->where('assessment_type', $type)
                    ->whereIn('class_id', $classIds)
                    ->get();

                $taken = $assessments->count();
                $totalMarks = 0;
                $obtainedMarks = 0;

                foreach ($assessments as $assessment) {
                    $marks = \App\AssessmentMark::where('assessment_id', $assessment->id)->get();
                    foreach ($marks as $mark) {
                        if ($mark->mark !== null && $mark->total_marks > 0) {
                            $totalMarks += $mark->total_marks;
                            $obtainedMarks += $mark->mark;
                        }
                    }
                }

                $performance = $totalMarks > 0 ? round(($obtainedMarks / $totalMarks) * 100, 1) : 0;

                $typeStats[] = [
                    'type' => $type,
                    'taken' => $taken,
                    'performance' => $performance
                ];
            }

            $subjectPerformance[] = [
                'subject' => $subject,
                'stats' => $typeStats
            ];
        }

        // Get recent assessments for this teacher (last 10)
        $recentAssessments = \App\Assessment::where('teacher_id', $teacher->id)
            ->with(['subject', 'class'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('backend.teacher.assessment', compact('classes', 'teacher', 'recentAssessments', 'subjectPerformance', 'assessmentTypes'));
    }

    /**
     * Display assessment list for a specific class.
     *
     * @param  int  $class_id
     * @return \Illuminate\Http\Response
     */
    public function assessmentList($class_id)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return redirect()->route('home')->with('error', 'Teacher profile not found.');
        }

        // Get allowed class IDs (class teacher + subject classes)
        $classTeacherClassIds = $teacher->classes()->pluck('id')->toArray();
        $teacherSubjectIds = $teacher->subjects->pluck('id')->toArray();
        $subjectClassIds = Grade::whereHas('subjects', function($query) use ($teacherSubjectIds) {
            $query->whereIn('subjects.id', $teacherSubjectIds);
        })->pluck('id')->toArray();
        $allowedClassIds = array_unique(array_merge($classTeacherClassIds, $subjectClassIds));
        
        if (!in_array($class_id, $allowedClassIds)) {
            return redirect()->route('teacher.assessment')->with('error', 'You do not have access to this class.');
        }
        
        // Verify the class belongs to this teacher and load subjects
        $class = Grade::with('subjects')->findOrFail($class_id);

        // Filter subjects to only show those taught by this teacher
        $class->subjects = $class->subjects->filter(function($subject) use ($teacherSubjectIds) {
            return in_array($subject->id, $teacherSubjectIds);
        });

        // Get all assessments for this class and teacher
        $allAssessments = \App\Assessment::where('teacher_id', $teacher->id)
            ->where('class_id', $class_id)
            ->with(['subject', 'exercise'])
            ->orderBy('date', 'desc')
            ->get();

        // Separate into marked and unmarked assessments
        $assessments = $allAssessments->filter(function($assessment) {
            return \App\AssessmentMark::where('assessment_id', $assessment->id)->count() == 0;
        });

        $markedAssessments = $allAssessments->filter(function($assessment) {
            return \App\AssessmentMark::where('assessment_id', $assessment->id)->count() > 0;
        });

        // Get assessment comments for this class and teacher's subjects only
        $assessmentComments = \App\AssessmentComment::where('class_id', $class_id)
            ->whereIn('subject_id', $teacherSubjectIds)
            ->with('subject')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('backend.teacher.assessment-list', compact('class', 'assessments', 'markedAssessments', 'assessmentComments', 'teacher'));
    }

    /**
     * Show form to create a new assessment.
     *
     * @param  int  $class_id
     * @return \Illuminate\Http\Response
     */
    public function createAssessment($class_id)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return redirect()->route('home')->with('error', 'Teacher profile not found.');
        }

        // Get allowed class IDs (class teacher + subject classes)
        $classTeacherClassIds = $teacher->classes()->pluck('id')->toArray();
        $teacherSubjectIds = $teacher->subjects->pluck('id')->toArray();
        $subjectClassIds = Grade::whereHas('subjects', function($query) use ($teacherSubjectIds) {
            $query->whereIn('subjects.id', $teacherSubjectIds);
        })->pluck('id')->toArray();
        $allowedClassIds = array_unique(array_merge($classTeacherClassIds, $subjectClassIds));
        
        if (!in_array($class_id, $allowedClassIds)) {
            return redirect()->route('teacher.assessment')->with('error', 'You do not have access to this class.');
        }
        
        // Verify the class belongs to this teacher
        $class = Grade::findOrFail($class_id);

        // Get subjects for this class
        $subjects = $class->subjects;

        return view('backend.teacher.assessment-create', compact('class', 'subjects', 'teacher'));
    }

    /**
     * Store a new assessment.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeAssessment(Request $request)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return redirect()->route('home')->with('error', 'Teacher profile not found.');
        }

        // Validate the request with enhanced validation rules
        $validated = $request->validate([
            'class_id' => 'required|exists:grades,id',
            'subject_id' => 'required|exists:subjects,id',
            'syllabus_topic_id' => 'nullable|exists:syllabus_topics,id',
            'topic' => 'required|string|min:3|max:255',
            'assessment_type' => 'required|string|in:Quiz,Test,In Class Test,Monthly Test,Assignment,Exercise,Project,Fort Night,Exam,Vacation Exam,National Exam',
            'date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:date',
            'exam' => 'nullable|string|max:255',
            'papers' => 'required|array|min:1|max:20',
            'papers.*.name' => 'required|string|min:2|max:100',
            'papers.*.total_marks' => 'required|integer|min:1|max:1000',
            'papers.*.weight' => 'required|integer|min:1|max:100'
        ], [
            'class_id.required' => 'Class selection is required.',
            'class_id.exists' => 'The selected class does not exist.',
            'subject_id.required' => 'Subject selection is required.',
            'subject_id.exists' => 'The selected subject does not exist.',
            'topic.required' => 'Topic is required.',
            'topic.min' => 'Topic must be at least 3 characters long.',
            'topic.max' => 'Topic cannot exceed 255 characters.',
            'assessment_type.required' => 'Assessment type is required.',
            'assessment_type.in' => 'Assessment type must be one of: Quiz, Test, Assignment, Exam, or Project.',
            'date.required' => 'Assessment date is required.',
            'date.before_or_equal' => 'Assessment date cannot be in the future.',
            'due_date.required' => 'Due date is required.',
            'due_date.after_or_equal' => 'Due date must be on or after the assessment date.',
            'exam.max' => 'Exam name cannot exceed 255 characters.',
            'papers.required' => 'At least one paper is required.',
            'papers.min' => 'At least one paper is required.',
            'papers.max' => 'You cannot add more than 20 papers.',
            'papers.*.name.required' => 'Paper name is required for all papers.',
            'papers.*.name.min' => 'Paper name must be at least 2 characters long.',
            'papers.*.name.max' => 'Paper name cannot exceed 100 characters.',
            'papers.*.total_marks.required' => 'Total marks is required for all papers.',
            'papers.*.total_marks.integer' => 'Total marks must be a valid number.',
            'papers.*.total_marks.min' => 'Total marks must be at least 1.',
            'papers.*.total_marks.max' => 'Total marks cannot exceed 1000.',
            'papers.*.weight.required' => 'Weight percentage is required for all papers.',
            'papers.*.weight.integer' => 'Weight must be a valid number.',
            'papers.*.weight.min' => 'Weight must be at least 1%.',
            'papers.*.weight.max' => 'Weight cannot exceed 100%.'
        ]);

        // Verify the class belongs to this teacher (either as class teacher or teaches subjects in it)
        $classTeacherClassIds = $teacher->classes()->pluck('id')->toArray();
        $teacherSubjectIds = $teacher->subjects->pluck('id')->toArray();
        $subjectClassIds = Grade::whereHas('subjects', function($query) use ($teacherSubjectIds) {
            $query->whereIn('subjects.id', $teacherSubjectIds);
        })->pluck('id')->toArray();
        $allowedClassIds = array_unique(array_merge($classTeacherClassIds, $subjectClassIds));
        
        if (!in_array($request->class_id, $allowedClassIds)) {
            return redirect()->route('teacher.assessment')->with('error', 'You do not have access to this class.');
        }
        
        $class = Grade::findOrFail($request->class_id);

        // Validate that paper weights add up to 100%
        $totalWeight = array_sum(array_column($request->papers, 'weight'));
        if ($totalWeight != 100) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['papers' => "Paper weights must add up to 100%. Current total: {$totalWeight}%"]);
        }

        // Check for duplicate paper names
        $paperNames = array_column($request->papers, 'name');
        if (count($paperNames) !== count(array_unique($paperNames))) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['papers' => 'Paper names must be unique. Please ensure each paper has a different name.']);
        }

        // Create the assessment
        $assessment = \App\Assessment::create([
            'teacher_id' => $teacher->id,
            'class_id' => $request->class_id,
            'subject_id' => $request->subject_id,
            'syllabus_topic_id' => $request->syllabus_topic_id,
            'topic' => trim($request->topic),
            'assessment_type' => $request->assessment_type,
            'date' => $request->date,
            'due_date' => $request->due_date,
            'exam' => $request->exam ? trim($request->exam) : null,
            'papers' => $request->papers
        ]);

        return redirect()->route('teacher.assessment.list', $request->class_id)
            ->with('success', 'Assessment created successfully!');
    }

    /**
     * Store a new assessment comment.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeAssessmentComment(Request $request)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return redirect()->route('home')->with('error', 'Teacher profile not found.');
        }

        // Validate the request with enhanced validation rules
        $validated = $request->validate([
            'class_id' => 'required|exists:grades,id',
            'subject_id' => 'required|exists:subjects,id',
            'entries' => 'required|array|min:1|max:50',
            'entries.*.comment' => 'required|string|min:10|max:500',
            'entries.*.grade' => 'required|string|in:A,B,C,D,U'
        ], [
            'class_id.required' => 'Class selection is required.',
            'class_id.exists' => 'The selected class does not exist.',
            'subject_id.required' => 'Subject selection is required.',
            'subject_id.exists' => 'The selected subject does not exist.',
            'entries.required' => 'At least one assessment comment entry is required.',
            'entries.min' => 'At least one assessment comment entry is required.',
            'entries.max' => 'You cannot add more than 50 entries at once.',
            'entries.*.comment.required' => 'Comment field is required for all entries.',
            'entries.*.comment.min' => 'Each comment must be at least 10 characters long.',
            'entries.*.comment.max' => 'Each comment cannot exceed 500 characters.',
            'entries.*.grade.required' => 'Grade selection is required for all entries.',
            'entries.*.grade.in' => 'Grade must be one of: A, B, C, D, or U.'
        ]);

        // Verify the class belongs to this teacher (either as class teacher or teaches subjects in it)
        $classTeacherClassIds = $teacher->classes()->pluck('id')->toArray();
        $teacherSubjectIds = $teacher->subjects->pluck('id')->toArray();
        $subjectClassIds = Grade::whereHas('subjects', function($query) use ($teacherSubjectIds) {
            $query->whereIn('subjects.id', $teacherSubjectIds);
        })->pluck('id')->toArray();
        $allowedClassIds = array_unique(array_merge($classTeacherClassIds, $subjectClassIds));
        
        if (!in_array($request->class_id, $allowedClassIds)) {
            return redirect()->route('teacher.assessment')->with('error', 'You do not have access to this class.');
        }

        // Create multiple assessment comments for the selected subject
        $createdComments = [];
        foreach ($request->entries as $entry) {
            $comment = \App\AssessmentComment::create([
                'class_id' => $request->class_id,
                'subject_id' => $request->subject_id,
                'comment' => trim($entry['comment']),
                'grade' => strtoupper($entry['grade'])
            ]);
            $comment->load('subject');
            $createdComments[] = $comment;
        }

        $count = count($request->entries);
        $message = $count > 1 
            ? "Successfully added {$count} assessment comments!" 
            : 'Assessment comment added successfully!';

        // Return JSON response to keep modal open
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'comments' => $createdComments
            ]);
        }

        return redirect()->route('teacher.assessment.list', $request->class_id)
            ->with('success', $message);
    }

    /**
     * Delete an assessment comment.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteAssessmentComment($id)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return redirect()->route('home')->with('error', 'Teacher profile not found.');
        }

        $comment = \App\AssessmentComment::findOrFail($id);

        // Verify the class belongs to this teacher (either as class teacher or teaches subjects in it)
        $classTeacherClassIds = $teacher->classes()->pluck('id')->toArray();
        $teacherSubjectIds = $teacher->subjects->pluck('id')->toArray();
        $subjectClassIds = Grade::whereHas('subjects', function($query) use ($teacherSubjectIds) {
            $query->whereIn('subjects.id', $teacherSubjectIds);
        })->pluck('id')->toArray();
        $allowedClassIds = array_unique(array_merge($classTeacherClassIds, $subjectClassIds));
        
        if (!in_array($comment->class_id, $allowedClassIds)) {
            return redirect()->route('teacher.assessment')->with('error', 'You do not have access to this class.');
        }

        $comment->delete();

        return redirect()->route('teacher.assessment.list', $comment->class_id)
            ->with('success', 'Assessment comment deleted successfully!');
    }

    /**
     * Show form to add assessment marks for a class.
     *
     * @param  int  $class_id
     * @return \Illuminate\Http\Response
     */
    public function assessmentMarks($class_id)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return redirect()->route('home')->with('error', 'Teacher profile not found.');
        }

        // Get allowed class IDs (class teacher + subject classes)
        $classTeacherClassIds = $teacher->classes()->pluck('id')->toArray();
        $teacherSubjectIds = $teacher->subjects->pluck('id')->toArray();
        $subjectClassIds = Grade::whereHas('subjects', function($query) use ($teacherSubjectIds) {
            $query->whereIn('subjects.id', $teacherSubjectIds);
        })->pluck('id')->toArray();
        $allowedClassIds = array_unique(array_merge($classTeacherClassIds, $subjectClassIds));
        
        if (!in_array($class_id, $allowedClassIds)) {
            return redirect()->route('teacher.assessment')->with('error', 'You do not have access to this class.');
        }
        
        // Verify the class belongs to this teacher
        $class = Grade::findOrFail($class_id);

        // Get all assessments for this class
        $allAssessments = \App\Assessment::where('teacher_id', $teacher->id)
            ->where('class_id', $class_id)
            ->with('subject')
            ->orderBy('date', 'desc')
            ->get();

        // Get students count in the class
        $studentsCount = $class->students()->count();

        // Filter out assessments that already have marks entered
        $assessments = $allAssessments->filter(function($assessment) {
            // Count how many marks exist for this assessment
            $marksCount = \App\AssessmentMark::where('assessment_id', $assessment->id)->count();
            
            // Only show assessment if no marks have been entered yet
            return $marksCount == 0;
        });

        // Get students in the class
        $students = $class->students()->with('user')->get();

        // Get assessment comments for this class
        $assessmentComments = \App\AssessmentComment::where('class_id', $class_id)
            ->with('subject')
            ->get();

        return view('backend.teacher.assessment-marks', compact('class', 'assessments', 'students', 'teacher', 'assessmentComments'));
    }

    /**
     * Save an individual assessment mark (auto-save).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function saveAssessmentMark(Request $request)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Teacher profile not found.'], 403);
        }

        $validated = $request->validate([
            'assessment_id' => 'required|exists:assessments,id',
            'student_id' => 'required|exists:students,id',
            'paper_name' => 'required|string|max:100',
            'paper_index' => 'required|integer|min:0',
            'mark' => 'required|numeric|min:0|lte:total_marks',
            'total_marks' => 'required|numeric|min:1',
            'comment' => 'nullable|string|max:500'
        ], [
            'mark.lte' => 'Mark cannot exceed total marks.',
            'total_marks.min' => 'Total marks must be at least 1.'
        ]);

        // Verify the assessment belongs to this teacher
        $assessment = \App\Assessment::where('id', $request->assessment_id)
            ->where('teacher_id', $teacher->id)
            ->first();

        if (!$assessment) {
            return response()->json(['success' => false, 'message' => 'Assessment not found or access denied.'], 403);
        }

        // Create or update the assessment mark
        $assessmentMark = \App\AssessmentMark::updateOrCreate(
            [
                'assessment_id' => $request->assessment_id,
                'student_id' => $request->student_id,
                'paper_index' => $request->paper_index
            ],
            [
                'paper_name' => $request->paper_name,
                'mark' => $request->mark,
                'total_marks' => $request->total_marks,
                'comment' => $request->comment
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Mark saved successfully!',
            'data' => $assessmentMark
        ]);
    }

    /**
     * View assessment details.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function viewAssessment($id)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Teacher profile not found.'], 403);
        }

        $assessment = \App\Assessment::where('id', $id)
            ->where('teacher_id', $teacher->id)
            ->with('subject')
            ->first();

        if (!$assessment) {
            return response()->json(['success' => false, 'message' => 'Assessment not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'assessment' => $assessment
        ]);
    }

    /**
     * Show form to edit an assessment.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editAssessment($id)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return redirect()->route('home')->with('error', 'Teacher profile not found.');
        }

        $assessment = \App\Assessment::where('id', $id)
            ->where('teacher_id', $teacher->id)
            ->with('subject', 'class')
            ->first();

        if (!$assessment) {
            return redirect()->route('teacher.assessment')->with('error', 'Assessment not found.');
        }

        $class = $assessment->class;
        $subjects = $class->subjects;

        // Get syllabus topics for the current subject
        $syllabusTopics = \App\SyllabusTopic::where('subject_id', $assessment->subject_id)
            ->active()
            ->ordered()
            ->get();

        return view('backend.teacher.assessment-edit', compact('assessment', 'class', 'subjects', 'syllabusTopics', 'teacher'));
    }

    /**
     * Update an assessment.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateAssessment(Request $request, $id)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return redirect()->route('home')->with('error', 'Teacher profile not found.');
        }

        $assessment = \App\Assessment::where('id', $id)
            ->where('teacher_id', $teacher->id)
            ->first();

        if (!$assessment) {
            return redirect()->route('teacher.assessment')->with('error', 'Assessment not found.');
        }

        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'syllabus_topic_id' => 'nullable|exists:syllabus_topics,id',
            'topic' => 'required|string|min:3|max:255',
            'assessment_type' => 'required|string|in:Quiz,Test,In Class Test,Monthly Test,Assignment,Exercise,Project,Fort Night,Exam,Vacation Exam,National Exam',
            'date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:date',
            'exam' => 'nullable|string|max:255',
            'papers' => 'required|array|min:1|max:20',
            'papers.*.name' => 'required|string|min:2|max:100',
            'papers.*.total_marks' => 'required|integer|min:1|max:1000',
            'papers.*.weight' => 'required|integer|min:1|max:100'
        ]);

        // Validate paper weights
        $totalWeight = array_sum(array_column($request->papers, 'weight'));
        if ($totalWeight != 100) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['papers' => "Paper weights must add up to 100%. Current total: {$totalWeight}%"]);
        }

        $assessment->update([
            'subject_id' => $request->subject_id,
            'syllabus_topic_id' => $request->syllabus_topic_id,
            'topic' => trim($request->topic),
            'assessment_type' => $request->assessment_type,
            'date' => $request->date,
            'due_date' => $request->due_date,
            'exam' => $request->exam ? trim($request->exam) : null,
            'papers' => $request->papers
        ]);

        return redirect()->route('teacher.assessment.list', $assessment->class_id)
            ->with('success', 'Assessment updated successfully!');
    }

    /**
     * Delete an assessment.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteAssessment($id)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Teacher profile not found.'], 403);
        }

        $assessment = \App\Assessment::where('id', $id)
            ->where('teacher_id', $teacher->id)
            ->first();

        if (!$assessment) {
            return response()->json(['success' => false, 'message' => 'Assessment not found.'], 404);
        }

        // Delete linked online exercises
        \App\Exercise::where('assessment_id', $assessment->id)->delete();

        $assessment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Assessment and linked exercises deleted successfully!'
        ]);
    }

    /**
     * Display marking scheme page with assessment marks.
     *
     * @param  int  $class_id
     * @return \Illuminate\Http\Response
     */
    public function markingScheme($class_id)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return redirect()->route('home')->with('error', 'Teacher profile not found.');
        }

        // Get allowed class IDs (class teacher + subject classes)
        $classTeacherClassIds = $teacher->classes()->pluck('id')->toArray();
        $teacherSubjects = \App\Subject::where('teacher_id', $teacher->id)->with('grades')->get();
        $subjectClassIds = $teacherSubjects->flatMap(function($subject) {
            return $subject->grades->pluck('id');
        })->unique()->toArray();
        $allowedClassIds = array_unique(array_merge($classTeacherClassIds, $subjectClassIds));

        if (!in_array($class_id, $allowedClassIds)) {
            return redirect()->route('teacher.assessment')->with('error', 'You do not have access to this class.');
        }

        $class = \App\Grade::findOrFail($class_id);

        // Get assessments with marks for this class
        $assessments = \App\Assessment::where('teacher_id', $teacher->id)
            ->where('class_id', $class_id)
            ->with('subject')
            ->whereHas('marks')
            ->orderBy('date', 'desc')
            ->get();

        // Get students in the class
        $students = $class->students()->with('user')->get();

        return view('backend.teacher.marking-scheme', compact('class', 'assessments', 'students', 'teacher'));
    }

    /**
     * Export marking scheme to Excel.
     *
     * @param  int  $class_id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function exportMarkingScheme($class_id, Request $request)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return redirect()->route('home')->with('error', 'Teacher profile not found.');
        }

        $assessmentId = $request->query('assessment_id');

        if (!$assessmentId) {
            return redirect()->back()->with('error', 'Please select an assessment to export.');
        }

        // Get allowed class IDs (class teacher + subject classes)
        $classTeacherClassIds = $teacher->classes()->pluck('id')->toArray();
        $teacherSubjects = \App\Subject::where('teacher_id', $teacher->id)->with('grades')->get();
        $subjectClassIds = $teacherSubjects->flatMap(function($subject) {
            return $subject->grades->pluck('id');
        })->unique()->toArray();
        $allowedClassIds = array_unique(array_merge($classTeacherClassIds, $subjectClassIds));

        if (!in_array($class_id, $allowedClassIds)) {
            return redirect()->route('teacher.assessment')->with('error', 'You do not have access to this class.');
        }

        $class = \App\Grade::findOrFail($class_id);

        // Get the assessment
        $assessment = \App\Assessment::where('id', $assessmentId)
            ->where('teacher_id', $teacher->id)
            ->where('class_id', $class_id)
            ->with('subject')
            ->firstOrFail();

        // Get marks for this assessment
        $marks = \App\AssessmentMark::where('assessment_id', $assessmentId)
            ->with('student.user')
            ->get();

        // Group marks by student
        $studentMarks = [];
        foreach ($marks as $mark) {
            $studentId = $mark->student_id;
            if (!isset($studentMarks[$studentId])) {
                $studentMarks[$studentId] = [
                    'name' => $mark->student->user->name,
                    'papers' => []
                ];
            }
            $studentMarks[$studentId]['papers'][$mark->paper_index] = [
                'paper_name' => $mark->paper_name,
                'mark' => $mark->mark,
                'total_marks' => $mark->total_marks,
                'comment' => $mark->comment
            ];
        }

        // Create Excel export
        return \Excel::download(new \App\Exports\MarkingSchemeExport($assessment, $studentMarks), 
            $assessment->subject->name . '_' . $assessment->topic . '_Marks.xlsx');
    }

    /**
     * Get assessment marks via API.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getAssessmentMarks($id)
    {
        $marks = \App\AssessmentMark::where('assessment_id', $id)
            ->with('student.user')
            ->get();

        // Group marks by student
        $studentMarks = [];
        foreach ($marks as $mark) {
            $studentId = $mark->student_id;
            if (!isset($studentMarks[$studentId])) {
                $studentMarks[$studentId] = [
                    'student_name' => $mark->student->user->name,
                    'papers' => []
                ];
            }
            $studentMarks[$studentId]['papers'][$mark->paper_index] = [
                'paper_name' => $mark->paper_name,
                'mark' => $mark->mark,
                'total_marks' => $mark->total_marks,
                'comment' => $mark->comment
            ];
        }

        return response()->json([
            'success' => true,
            'marks' => array_values($studentMarks)
        ]);
    }

    /**
     * Display teacher sessions management page
     *
     * @return \Illuminate\Http\Response
     */
    public function sessions()
    {
        $teachers = Teacher::with('user')->get();
        return view('backend.teachers.sessions', compact('teachers'));
    }

    /**
     * Bulk update teacher sessions
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function updateSessions(Request $request)
    {
        $sessions = $request->input('sessions', []);
        
        foreach ($sessions as $teacherId => $session) {
            Teacher::where('id', $teacherId)->update(['session' => $session]);
        }
        
        $redirect = $request->input('redirect', 'teacher.sessions');
        
        return redirect()->route($redirect)
            ->with('success', 'Teacher sessions updated successfully!');
    }

    /**
     * Force teacher to change password on next login
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function forcePasswordReset($id)
    {
        $teacher = Teacher::with('user')->findOrFail($id);
        
        if (!$teacher->user) {
            return back()->with('error', 'Teacher user account not found.');
        }

        // Set default password
        $tempPassword = '12345678';
        
        // Update user's password and set must_change_password flag
        $teacher->user->update([
            'password' => Hash::make($tempPassword),
            'must_change_password' => true
        ]);

        // Send SMS with login credentials
        $smsSent = false;
        if ($teacher->phone) {
            $phone = $this->formatPhoneNumber($teacher->phone);
            $messageTemplate = SchoolSetting::get(
                'sms_teacher_password_reset_template',
                "ROSHS Password Reset\nEmail: {email}\nTemp Password: {password}\nPlease login and change your password immediately."
            );
            $message = str_replace(
                ['{name}', '{email}', '{password}'],
                [$teacher->user->name, $teacher->user->email, $tempPassword],
                $messageTemplate
            );
            
            $result = SmsHelper::sendSms($phone, $message);
            $smsSent = $result['success'];
            
            if (!$smsSent) {
                Log::warning('Failed to send password reset SMS to teacher', [
                    'teacher_id' => $teacher->id,
                    'phone' => $phone,
                    'error' => $result['message'] ?? 'Unknown error'
                ]);
            }
        }

        $successMsg = 'Password reset for ' . $teacher->user->name . '.';
        if ($smsSent) {
            $successMsg .= ' SMS sent with login credentials.';
        } else {
            $successMsg .= ' SMS could not be sent. Email: ' . $teacher->user->email . ', Temp Password: ' . $tempPassword;
        }

        return back()->with('success', $successMsg);
    }

    /**
     * Format phone number to international format
     */
    private function formatPhoneNumber($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (substr($phone, 0, 1) === '0') {
            $phone = '+263' . substr($phone, 1);
        } elseif (substr($phone, 0, 3) !== '263') {
            $phone = '+263' . $phone;
        } else {
            $phone = '+' . $phone;
        }
        
        return $phone;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Teacher  $teacher
     * @return \Illuminate\Http\Response
     */
    public function destroy(Teacher $teacher)
    {
        $user = User::findOrFail($teacher->user_id);

        $user->teacher()->delete();

        $user->removeRole('Teacher');

        if ($user->delete()) {
            if($user->profile_picture != 'avatar.png') {
                $image_path = public_path() . '/images/profile/' . $user->profile_picture;
                if (is_file($image_path) && file_exists($image_path)) {
                    unlink($image_path);
                }
            }
        }

        return back();
    }
}
