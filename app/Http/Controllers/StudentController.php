<?php

namespace App\Http\Controllers;

use App\User;
use App\Grade;
use App\Parents;
use App\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade as PDF;
use App\Helpers\SmsHelper;
use App\SchoolSetting;
use App\Imports\StudentWithParentsImport;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{
    /**
     * Generate next roll number
     *
     * @return string
     */
    private function generateRollNumber()
    {
        // Get the highest roll number from students table
        $lastStudent = Student::orderBy('id', 'desc')->first();
        
        if (!$lastStudent || empty($lastStudent->roll_number)) {
            $newNumber = 1;
        } else {
            // Extract the numeric part from the last roll number
            $newNumber = (int) substr($lastStudent->roll_number, 3) + 1;
        }

        // Keep incrementing until we find a roll number whose email is not taken
        $maxAttempts = 1000;
        $attempts = 0;
        
        while ($attempts < $maxAttempts) {
            $rollNumber = 'RSH' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
            $email = strtolower($rollNumber) . '@roshs.co.zw';
            
            // Check if this email already exists in users table
            $emailExists = \App\User::where('email', $email)->exists();
            
            if (!$emailExists) {
                return $rollNumber;
            }
            
            $newNumber++;
            $attempts++;
        }

        // Fallback: use timestamp-based roll number
        return 'RSH' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $classes = Grade::withCount('students')->orderBy('class_numeric')->get();
        $students = Student::with(['class', 'user', 'parents'])->latest()->get();

        return view('backend.students.index', compact('classes', 'students'));
    }

    public function downloadIdCard($id)
    {
        $student = Student::with(['user', 'parent', 'class'])->findOrFail($id);
        $pdf = PDF::loadView('student.id_card', compact('student'));

        return $pdf->download('student_id_card_' . $student->roll_number . '.pdf');
    }

    public function showid($id)
    {
        $student = Student::with(['user', 'parent', 'class', 'attendances', 'subjects'])->findOrFail($id);

        return view('student.id_card', compact('student'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $classes = Grade::latest()->get();
        $parents = Parents::with('user')->latest()->get();
        $nextRollNumber = $this->generateRollNumber();

        // Store the roll number in session for this form
        session(['student_roll_number' => $nextRollNumber]);

        return view('backend.students.create', compact('classes','parents','nextRollNumber'));
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
            'name'              => 'required|string|max:255',
            'email'             => 'required|string|email|max:255|unique:users',
            'password'          => 'required|string|min:8',
            'parent_id'         => 'required|array|min:1',
            'parent_id.*'       => 'required|numeric|exists:parents,id',
            'class_id'          => 'required|numeric',
            'gender'            => 'required|string',
            'phone'             => 'required|string|max:255',
            'dateofbirth'       => 'required|date',
            'current_address'   => 'required|string|max:255',
            'permanent_address' => 'required|string|max:255'
        ]);

        // Get the roll number from session (generated when form was opened)
        $rollNumber = session('student_roll_number', $this->generateRollNumber());

        // Clear the session roll number
        session()->forget('student_roll_number');

        $user = User::create([
            'name'              => $request->name,
            'email'             => $request->email,
            'password'          => Hash::make($request->password)
        ]);

        if ($request->hasFile('profile_picture')) {
            $profile = Str::slug($user->name).'-'.$user->id.'.'.$request->profile_picture->getClientOriginalExtension();
            $request->profile_picture->move(public_path('images/profile'), $profile);
        } else {
            $profile = 'avatar.png';
        }
        $user->update([
            'profile_picture' => $profile
        ]);

        $student = $user->student()->create([
            'parent_id'         => $request->parent_id[0], // Keep first parent for backward compatibility
            'class_id'          => $request->class_id,
            'roll_number'       => $rollNumber,
            'gender'            => $request->gender,
            'phone'             => $request->phone,
            'dateofbirth'       => $request->dateofbirth,
            'current_address'   => $request->current_address,
            'permanent_address' => $request->permanent_address
        ]);

        // Attach all parents to the student
        $student->parents()->attach($request->parent_id);

        $user->assignRole('Student');

        return redirect()->route('student.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Student  $student
     * @return \Illuminate\Http\Response
     */
    public function show(Student $student)
    {
        $class = Grade::with('subjects')->where('id', $student->class_id)->first();
        
        // Get student payment history
        $payments = \App\StudentPayment::where('student_id', $student->id)
            ->with('resultsStatus')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('backend.students.show', compact('class', 'student', 'payments'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Student  $student
     * @return \Illuminate\Http\Response
     */
    public function edit(Student $student)
    {
        $classes = Grade::latest()->get();
        $parents = Parents::with('user')->latest()->get();

        return view('backend.students.edit', compact('classes','parents','student'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Student  $student
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Student $student)
    {
        $request->validate([
            'name'              => 'required|string|max:255',
            'email'             => 'required|string|email|max:255|unique:users,email,'.$student->user_id,
            'class_id'          => 'required|numeric',
            'roll_number'       => [
                'required',
                'string',
                'max:255',
                Rule::unique('students')->ignore($student->id)->where(function ($query) use ($request) {
                    return $query->where('class_id', $request->class_id);
                })
            ],
            'gender'            => 'required|string',
            'phone'             => 'nullable|string|max:255',
            'dateofbirth'       => 'required|date',
            'current_address'   => 'required|string|max:255',
            'permanent_address' => 'required|string|max:255',
            'student_type'      => 'nullable|in:day,boarding',
            'curriculum_type'   => 'nullable|in:zimsec,cambridge',
            'is_new_student'    => 'nullable|boolean',
            'scholarship_percentage' => 'nullable|numeric|min:0|max:100',
            'new_parent_name'   => 'nullable|string|max:255',
            'new_parent_email'  => 'nullable|email|max:255',
            'new_parent_phone'  => 'nullable|string|max:255',
            'new_parent_gender' => 'nullable|in:male,female',
        ]);

        if ($request->hasFile('profile_picture')) {
            $profile = Str::slug($student->user->name).'-'.$student->user->id.'.'.$request->profile_picture->getClientOriginalExtension();
            $request->profile_picture->move(public_path('images/profile'), $profile);
        } else {
            $profile = $student->user->profile_picture;
        }

        $student->user->update([
            'name'              => $request->name,
            'email'             => $request->email,
            'profile_picture'   => $profile
        ]);

        $student->update([
            'class_id'          => $request->class_id,
            'roll_number'       => $request->roll_number,
            'gender'            => $request->gender,
            'phone'             => $request->phone,
            'dateofbirth'       => $request->dateofbirth,
            'current_address'   => $request->current_address,
            'permanent_address' => $request->permanent_address,
            'student_type'      => $request->student_type ?? $student->student_type,
            'curriculum_type'   => $request->curriculum_type ?? $student->curriculum_type,
            'is_new_student'    => $request->is_new_student ?? $student->is_new_student,
            'scholarship_percentage' => $request->scholarship_percentage ?? $student->scholarship_percentage,
        ]);

        // Handle parent assignment
        $parentIds = $request->existing_parent_ids ?? [];

        // Create new parent if fields are filled
        $newParentPhone = null;
        $newParentRecord = null;
        if ($request->filled('new_parent_name') && $request->filled('new_parent_phone')) {
            // Generate unique registration token
            $registrationToken = Str::random(60);
            
            // Create a temporary email for parent (will be updated when they register)
            $tempEmail = 'pending_' . time() . '_' . $student->id . '@temp.parent';
            
            // Check if user with this email already exists
            $existingUser = User::where('email', $tempEmail)->first();
            if (!$existingUser) {
                // Create user for the parent
                $parentUser = User::create([
                    'name'     => $request->new_parent_name,
                    'email'    => $tempEmail,
                    'password' => Hash::make(Str::random(16)),
                    'profile_picture' => 'avatar.png'
                ]);
                $parentUser->assignRole('Parent');

                // Create the parent record with registration token
                $newParent = $parentUser->parent()->create([
                    'gender'                 => $request->new_parent_gender ?? 'male',
                    'phone'                  => $request->new_parent_phone,
                    'current_address'        => $student->current_address,
                    'permanent_address'      => $student->permanent_address,
                    'registration_token'     => $registrationToken,
                    'token_expires_at'       => now()->addDays(7),
                    'registration_completed' => false,
                ]);

                $parentIds[] = $newParent->id;
                $newParentPhone = $request->new_parent_phone;
                $newParentRecord = $newParent;
            }
        }

        // Sync parents if we have any
        if (!empty($parentIds)) {
            $student->parents()->sync($parentIds);
            // Update parent_id for backward compatibility
            $student->update(['parent_id' => $parentIds[0]]);
        }

        // Send SMS to new parent with registration link
        $smsMessage = '';
        if ($newParentPhone && $newParentRecord && $newParentRecord->registration_token) {
            $registrationUrl = url('/parent/register/' . $newParentRecord->registration_token);
            $messageTemplate = SchoolSetting::get(
                'sms_student_parent_registration_template',
                'RSH School: {student_name} registered. Complete parent registration: {url}'
            );
            $message = str_replace(
                ['{student_name}', '{url}'],
                [$student->user->name, $registrationUrl],
                $messageTemplate
            );
            
            $smsResult = SmsHelper::sendSms($newParentPhone, $message);
            
            if ($smsResult['success']) {
                $smsMessage = ' SMS sent to parent for registration.';
                \Log::info('SMS sent to new parent from student edit', [
                    'phone' => $newParentPhone,
                    'student_id' => $student->id,
                    'parent_id' => $newParentRecord->id,
                ]);
            } else {
                $smsMessage = ' Failed to send SMS to parent.';
                \Log::warning('Failed to send SMS to new parent from student edit', [
                    'phone' => $newParentPhone,
                    'student_id' => $student->id,
                    'error' => $smsResult['message'] ?? 'Unknown error',
                ]);
            }
        }

        return redirect()->route('student.index')->with('success', 'Student updated successfully.' . $smsMessage);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Student  $student
     * @return \Illuminate\Http\Response
     */
    public function destroy(Student $student)
    {
        $user = User::findOrFail($student->user_id);
        $user->student()->delete();
        $user->removeRole('Student');

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

    /**
     * Show the form for creating a student with parents (stepper form)
     *
     * @return \Illuminate\Http\Response
     */
    public function createWithParents()
    {
        $classes = Grade::latest()->get();
        $nextRollNumber = $this->generateRollNumber();

        // Store the roll number in session for this form
        session(['student_roll_number' => $nextRollNumber]);

        return view('backend.students.create_with_parents', compact('classes', 'nextRollNumber'));
    }

    /**
     * Generate a new roll number via AJAX
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateRollNumberAjax()
    {
        $newRollNumber = $this->generateRollNumber();
        
        // Update the session with the new roll number
        session(['student_roll_number' => $newRollNumber]);
        
        return response()->json(['roll_number' => $newRollNumber]);
    }

    /**
     * Store a newly created student with multiple parents
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeWithParents(Request $request)
    {
        // Debug: Log all incoming request data
        \Log::info('=== STORE WITH PARENTS DEBUG ===');
        \Log::info('Request data:', $request->all());
        \Log::info('Parents array:', $request->input('parents', []));

        try {
            // Validate student data
            $request->validate([
                'student_name'              => 'required|string|max:255',
                'student_email'             => 'required|string|email|max:255|unique:users,email',
                'student_phone'             => 'nullable|string|max:255',
                'student_gender'            => 'required|string',
                'dateofbirth'               => 'required|date',
                'class_id'                  => 'required|numeric',
                'chair'                     => 'nullable|string|max:255',
                'desk'                      => 'nullable|string|max:255',
                'curriculum_type'           => 'nullable|in:zimsec,cambridge',
                'scholarship_percentage'    => 'nullable|numeric|min:0|max:100',
            ]);

        \Log::info('Validation passed successfully');

        // Create student user with default password
        \Log::info('Creating student user...');
        $studentUser = User::create([
            'name'      => $request->student_name,
            'email'     => $request->student_email,
            'password'  => Hash::make('12345678') // Default password
        ]);

        // Handle student profile picture
        if ($request->hasFile('student_profile_picture')) {
            $profile = Str::slug($studentUser->name).'-'.$studentUser->id.'.'.$request->student_profile_picture->getClientOriginalExtension();
            $request->student_profile_picture->move(public_path('images/profile'), $profile);
        } else {
            $profile = 'avatar.png';
        }
        $studentUser->update([
            'profile_picture' => $profile
        ]);

        \Log::info('Student user created:', ['id' => $studentUser->id, 'name' => $studentUser->name]);

        // Create parents and collect their IDs (only if parents data is provided)
        $parentIds = [];
        $parentPhones = [];

        if ($request->has('parents') && is_array($request->parents) && count($request->parents) > 0) {
            // Filter out empty parent entries
            $validParents = array_filter($request->parents, function($parent) {
                return !empty($parent['name']) && !empty($parent['phone']);
            });

            foreach ($validParents as $index => $parentData) {
                // Generate unique registration token
                $registrationToken = \Illuminate\Support\Str::random(60);

                // Create a temporary email for parent (will be updated when they register)
                $tempEmail = 'pending_' . time() . '_' . $index . '@temp.parent';

                // Create parent user with temporary data
                $parentUser = User::create([
                    'name'      => $parentData['name'],
                    'email'     => $tempEmail,
                    'password'  => Hash::make(\Illuminate\Support\Str::random(16)) // Temporary password
                ]);

                // Set default profile picture
                $parentUser->update([
                    'profile_picture' => 'avatar.png'
                ]);

                // Create parent record with registration token
                $parent = $parentUser->parent()->create([
                    'gender'                    => 'male', // Temporary default, will be updated by parent during registration
                    'phone'                     => $parentData['phone'],
                    'current_address'           => 'Pending', // Will be filled by parent
                    'permanent_address'         => 'Pending', // Will be filled by parent
                    'registration_token'        => $registrationToken,
                    'token_expires_at'          => now()->addDays(7), // Token valid for 7 days
                    'registration_completed'    => false
                ]);

                $parentUser->assignRole('Parent');
                $parentIds[] = $parent->id;
                $parentPhones[] = $parentData['phone'];
            }
        }

        // Get the roll number from session (generated when form was opened)
        $rollNumber = session('student_roll_number', $this->generateRollNumber());

        // Clear the session roll number
        session()->forget('student_roll_number');

        // Create student record with dummy values for fields that will be updated on password change
        $student = $studentUser->student()->create([
            'parent_id'             => !empty($parentIds) ? $parentIds[0] : null, // First parent for backward compatibility, null if no parents
            'class_id'              => $request->class_id,
            'roll_number'           => $rollNumber,
            'gender'                => $request->student_gender,
            'phone'                 => $request->student_phone ?? '',
            'dateofbirth'           => $request->dateofbirth,
            'current_address'       => 'To be updated', // Dummy value - student will update on first login
            'permanent_address'     => 'To be updated', // Dummy value - student will update on first login
            'student_type'          => $request->student_type ?? 'day', // Day or Boarding student
            'curriculum_type'       => $request->curriculum_type ?? 'zimsec', // ZIMSEC or Cambridge curriculum
            'scholarship_percentage' => $request->scholarship_percentage ?? 0, // Scholarship discount percentage
            'is_new_student'        => $request->is_new_student ?? true, // New student flag for fee structure
            'chair'                 => $request->chair ?? null,
            'desk'                  => $request->desk ?? null,
        ]);

        // Attach all parents to the student (only if parents exist)
        if (!empty($parentIds)) {
            $student->parents()->attach($parentIds);
        }

        \Log::info('Student record created:', ['student_id' => $student->id, 'roll_number' => $rollNumber]);
        \Log::info('Parents attached:', ['parent_ids' => $parentIds]);

        $studentUser->assignRole('Student');

        // Send SMS to all parents with registration link and student roll number (only if parents were created)
        $smsSentCount = 0;
        $smsFailedCount = 0;
        
        if (!empty($parentPhones)) {
            foreach ($parentPhones as $index => $phone) {
                $parent = \App\Parents::where('phone', $phone)->latest()->first();

                if ($parent && $parent->registration_token) {
                    $registrationUrl = url('/parent/register/' . $parent->registration_token);

                    // Get message template from settings
                    $messageTemplate = SchoolSetting::get(
                        'sms_student_parent_registration_template',
                        'RSH School: {student_name} registered. Complete parent registration: {url}'
                    );
                    $message = str_replace(
                        ['{student_name}', '{url}'],
                        [$request->student_name, $registrationUrl],
                        $messageTemplate
                    );

                    // Send SMS directly using SmsHelper (same as test page)
                    $smsResult = SmsHelper::sendSms($phone, $message);
                    
                    if ($smsResult['success']) {
                        $smsSentCount++;
                        \Log::info('SMS sent successfully to parent', [
                            'phone' => $phone,
                            'parent_id' => $parent->id,
                            'student_id' => $student->id,
                            'response' => $smsResult['response'] ?? null
                        ]);
                    } else {
                        $smsFailedCount++;
                        \Log::warning('Failed to send SMS to parent', [
                            'phone' => $phone,
                            'parent_id' => $parent->id,
                            'student_id' => $student->id,
                            'error' => $smsResult['message'] ?? 'Unknown error'
                        ]);
                    }
                }
            }
        }

        \Log::info('Process completed successfully');
        \Log::info('=== END DEBUG ===');

        // Prepare success message based on whether parents were added
        $successMessage = 'Student created successfully!';
        if ($smsSentCount > 0) {
            $successMessage .= " SMS sent to {$smsSentCount} parent(s) for registration completion.";
        } elseif (empty($parentIds)) {
            $successMessage .= ' No parent information was provided.';
        }

        // Redirect based on user role
        if (auth()->user()->hasRole('Admin')) {
            return redirect()->route('student.index')->with('success', $successMessage);
        } else {
            // Class teacher redirect - show students of the class where student was added
            return redirect('/teacher/student-record/' . $request->class_id)->with('success', $successMessage);
        }

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed:', ['errors' => $e->errors()]);
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Error in storeWithParents:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create student: ' . $e->getMessage());
        }
    }

    /**
     * Show password change form
     */
    public function showChangePasswordForm()
    {
        return view('student.change_password');
    }

    /**
     * Update student password and phone number
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();

        // Check if current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->with('error', 'Current password is incorrect.');
        }

        // Check if new password is same as default
        if ($request->new_password === '12345678') {
            return redirect()->back()->with('error', 'New password cannot be the default password (12345678).');
        }

        // Update phone number on student record
        if ($user->student) {
            $user->student->phone = $request->phone;
            $user->student->save();
        }

        // Update password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->route('home')->with('success', 'Profile updated successfully!');
    }

    /**
     * Resend SMS to pending parents
     */
    public function resendParentSms(Student $student)
    {
        try {
            $sentCount = 0;
            $failedCount = 0;
            $skippedCount = 0;
            
            // Get all pending parents for this student
            $pendingParents = $student->parents()->where('registration_completed', false)->get();
            
            if ($pendingParents->count() == 0) {
                return redirect()->back()->with('warning', 'No pending parent registrations found for this student.');
            }
            
            foreach ($pendingParents as $parent) {
                if ($parent->registration_token && $parent->token_expires_at && $parent->token_expires_at->isFuture()) {
                    $registrationUrl = url('/parent/register/' . $parent->registration_token);
                    
                    // Get message template from settings
                    $messageTemplate = SchoolSetting::get(
                        'sms_student_parent_registration_template',
                        'RSH School: {student_name} registered. Complete parent registration: {url}'
                    );
                    $message = str_replace(
                        ['{student_name}', '{url}'],
                        [$student->user->name, $registrationUrl],
                        $messageTemplate
                    );
                    
                    // Send SMS directly using SmsHelper (same as test page)
                    $result = SmsHelper::sendSms($parent->phone, $message);
                    
                    if ($result['success']) {
                        $sentCount++;
                        \Log::info('SMS sent successfully to parent', [
                            'phone' => $parent->phone,
                            'student_id' => $student->id,
                            'parent_id' => $parent->id,
                            'response' => $result['response'] ?? null
                        ]);
                    } else {
                        $failedCount++;
                        \Log::warning('Failed to send SMS to parent', [
                            'phone' => $parent->phone,
                            'student_id' => $student->id,
                            'parent_id' => $parent->id,
                            'error' => $result['message'] ?? 'Unknown error'
                        ]);
                    }
                } else {
                    $skippedCount++;
                    \Log::warning('Cannot resend SMS - token expired or missing', [
                        'parent_id' => $parent->id,
                        'token_expires_at' => $parent->token_expires_at,
                        'has_token' => !empty($parent->registration_token)
                    ]);
                }
            }
            
            if ($sentCount > 0 && $failedCount == 0 && $skippedCount == 0) {
                return redirect()->back()->with('success', "SMS sent successfully to {$sentCount} parent(s)!");
            } elseif ($sentCount > 0 && ($failedCount > 0 || $skippedCount > 0)) {
                return redirect()->back()->with('warning', "SMS sent to {$sentCount} parent(s), failed: {$failedCount}, skipped: {$skippedCount}.");
            } elseif ($failedCount > 0) {
                return redirect()->back()->with('error', "Failed to send SMS to {$failedCount} parent(s). Check logs for details.");
            } else {
                return redirect()->back()->with('error', "No SMS sent - all parent tokens are expired or missing.");
            }
            
        } catch (\Exception $e) {
            \Log::error('Error in resendParentSms:', [
                'message' => $e->getMessage(),
                'student_id' => $student->id
            ]);
            
            return redirect()->back()->with('error', 'Failed to send SMS: ' . $e->getMessage());
        }
    }

    /**
     * Force student to change password on next login
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function forcePasswordReset($id)
    {
        $student = Student::with('user')->findOrFail($id);
        
        if (!$student->user) {
            return back()->with('error', 'Student user account not found.');
        }

        // Set must_change_password flag
        $student->user->update([
            'must_change_password' => true
        ]);

        return back()->with('success', 'Password reset required for ' . $student->user->name . '. They will be prompted to change their password on next login.');
    }

    /**
     * Update student chair and desk assignment
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateChairDesk(Request $request)
    {
        $request->validate([
            'chair' => 'nullable|string|max:255',
            'desk' => 'nullable|string|max:255',
        ]);

        $user = auth()->user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student record not found.'
            ], 404);
        }

        $student->update([
            'chair' => $request->chair,
            'desk' => $request->desk,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Chair and desk information updated successfully.',
            'chair' => $student->chair,
            'desk' => $student->desk,
        ]);
    }

    /**
     * Display seat assignment management page
     */
    public function seatAssignmentIndex()
    {
        $classes = Grade::all();
        $students = Student::with(['user', 'class'])->orderBy('class_id')->get();
        
        return view('backend.students.seat-assignment', compact('classes', 'students'));
    }

    /**
     * Update student seat assignment
     */
    public function updateSeatAssignment(Request $request, Student $student)
    {
        $request->validate([
            'chair' => 'nullable|string|max:255',
            'desk' => 'nullable|string|max:255',
        ]);

        $student->update([
            'chair' => $request->chair,
            'desk' => $request->desk,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Seat assignment updated successfully.',
            'chair' => $student->chair,
            'desk' => $student->desk,
        ]);
    }

    /**
     * Bulk update all new students to existing students
     */
    public function bulkUpdateToExisting()
    {
        $updatedCount = Student::where('is_new_student', true)->update(['is_new_student' => false]);

        return redirect()->route('student.index')->with('success', "{$updatedCount} student(s) updated from 'New Student' to 'Existing Student' successfully.");
    }

    /**
     * Bulk delete multiple students
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'required|numeric|exists:students,id'
        ]);

        $deletedCount = 0;
        
        foreach ($request->student_ids as $studentId) {
            $student = Student::find($studentId);
            
            if ($student) {
                $user = User::find($student->user_id);
                
                // Delete student record
                $student->delete();
                
                // Remove role and delete user
                if ($user) {
                    $user->removeRole('Student');
                    
                    // Delete profile picture if not default
                    if ($user->profile_picture != 'avatar.png') {
                        $image_path = public_path('images/profile/' . $user->profile_picture);
                        if (is_file($image_path) && file_exists($image_path)) {
                            unlink($image_path);
                        }
                    }
                    
                    $user->delete();
                }
                
                $deletedCount++;
            }
        }

        return redirect()->route('student.index')->with('success', "{$deletedCount} student(s) deleted successfully.");
    }

    /**
     * Import students with parents from CSV/Excel file
     */
    public function importStudentsWithParents(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:csv,xlsx,xls|max:2048',
        ]);

        try {
            $import = new StudentWithParentsImport($this);
            Excel::import($import, $request->file('import_file'));

            $successCount = $import->getSuccessCount();
            $errors = $import->getErrors();

            if ($import->hasErrors()) {
                $errorMessage = "Import completed with {$successCount} successful student(s) and " . count($errors) . " error(s):<br>";
                foreach (array_slice($errors, 0, 10) as $error) {
                    $errorMessage .= "• " . $error . "<br>";
                }
                if (count($errors) > 10) {
                    $errorMessage .= "• ... and " . (count($errors) - 10) . " more error(s)";
                }
                
                return redirect()->route('shared.student.create-with-parents')
                    ->with('warning', $errorMessage)
                    ->with('import_success_count', $successCount);
            }

            return redirect()->route('shared.student.create-with-parents')
                ->with('success', "Successfully imported {$successCount} student(s) with parents.");

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errorMessage = "Validation errors found:<br>";
            foreach (array_slice($failures, 0, 10) as $failure) {
                $errorMessage .= "• Row {$failure->row()}: " . implode(', ', $failure->errors()) . "<br>";
            }
            
            return redirect()->route('shared.student.create-with-parents')
                ->with('error', $errorMessage);
                
        } catch (\Exception $e) {
            return redirect()->route('shared.student.create-with-parents')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Download CSV template for bulk student import
     */
    public function downloadStudentTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="student_import_template.csv"',
        ];

        $columns = [
            'student_name',
            'student_email',
            'student_phone',
            'student_gender',
            'dateofbirth',
            'class_name',
            'student_type',
            'curriculum_type',
            'scholarship_percentage',
            'chair',
            'desk',
            'parent_1_name',
            'parent_1_phone',
            'parent_2_name',
            'parent_2_phone'
        ];
        
        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($file, $columns);
            
            // Add sample data rows
            fputcsv($file, [
                'John Doe',
                'john.doe@example.com',
                '+27123456789',
                'male',
                '2010-05-15',
                'Form 1 Red',
                'day',
                'zimsec',
                '0',
                'A1',
                'D1',
                'Jane Doe',
                '+27123456780',
                'Robert Doe',
                '+27123456781'
            ]);
            
            fputcsv($file, [
                'Mary Smith',
                'mary.smith@example.com',
                '+27987654321',
                'female',
                '2011-08-20',
                'Form 1 Blue',
                'boarding',
                'cambridge',
                '10',
                'B2',
                'D2',
                'Sarah Smith',
                '+27987654320',
                '',
                ''
            ]);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
