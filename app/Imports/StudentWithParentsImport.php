<?php

namespace App\Imports;

use App\User;
use App\Student;
use App\Parents;
use App\Grade;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class StudentWithParentsImport implements ToCollection, WithHeadingRow, WithValidation
{
    protected $results = [];
    protected $errors = [];
    protected $controller;
    
    public function __construct($controller = null)
    {
        $this->controller = $controller;
    }
    
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            
            try {
                // Validate required fields
                if (empty($row['student_name'])) {
                    $this->errors[] = "Row {$rowNumber}: Student name is required";
                    continue;
                }
                
                if (empty($row['student_email'])) {
                    $this->errors[] = "Row {$rowNumber}: Student email is required";
                    continue;
                }
                
                if (empty($row['class_name'])) {
                    $this->errors[] = "Row {$rowNumber}: Class name is required";
                    continue;
                }
                
                // Validate dateofbirth before creating user
                if (!empty($row['dateofbirth'])) {
                    try {
                        \Carbon\Carbon::parse(trim($row['dateofbirth']))->format('Y-m-d');
                    } catch (\Exception $e) {
                        $this->errors[] = "Row {$rowNumber}: Invalid date of birth format '{$row['dateofbirth']}'";
                        continue;
                    }
                }
                
                // Check if email already exists
                if (User::where('email', $row['student_email'])->exists()) {
                    $this->errors[] = "Row {$rowNumber}: Email '{$row['student_email']}' already exists";
                    continue;
                }
                
                // Find class
                $class = Grade::where('class_name', $row['class_name'])->first();
                if (!$class) {
                    $this->errors[] = "Row {$rowNumber}: Class '{$row['class_name']}' not found";
                    continue;
                }
                
                // Generate roll number
                $rollNumber = $this->generateRollNumber();
                
                // Create student user
                $studentUser = User::create([
                    'name'      => $row['student_name'],
                    'email'     => $row['student_email'],
                    'password'  => Hash::make('12345678'),
                    'profile_picture' => 'avatar.png',
                    'must_change_password' => true
                ]);
                
                // Create student record
                $student = $studentUser->student()->create([
                    'parent_id'             => null,
                    'class_id'              => $class->id,
                    'roll_number'           => $rollNumber,
                    'gender'                => $row['student_gender'] ?? 'male',
                    'phone'                 => $row['student_phone'] ?? '',
                    'dateofbirth'           => !empty($row['dateofbirth'])
                        ? \Carbon\Carbon::parse(trim($row['dateofbirth']))->format('Y-m-d')
                        : now()->subYears(10)->format('Y-m-d'),
                    'current_address'       => 'To be updated',
                    'permanent_address'     => 'To be updated',
                    'student_type'          => $row['student_type'] ?? 'day',
                    'curriculum_type'       => $row['curriculum_type'] ?? 'zimsec',
                    'scholarship_percentage' => $row['scholarship_percentage'] ?? 0,
                    'is_new_student'        => true,
                    'chair'                 => $row['chair'] ?? null,
                    'desk'                  => $row['desk'] ?? null,
                ]);
                
                $studentUser->assignRole('Student');
                
                // Process parents (up to 2)
                $parentIds = [];
                for ($i = 1; $i <= 2; $i++) {
                    $parentNameKey = "parent_{$i}_name";
                    $parentPhoneKey = "parent_{$i}_phone";
                    
                    if (!empty($row[$parentNameKey]) && !empty($row[$parentPhoneKey])) {
                        $parentId = $this->createParent($row[$parentNameKey], $row[$parentPhoneKey], $row['student_name']);
                        if ($parentId) {
                            $parentIds[] = $parentId;
                        }
                    }
                }
                
                // Attach parents to student
                if (!empty($parentIds)) {
                    $student->update(['parent_id' => $parentIds[0]]);
                    $student->parents()->attach($parentIds);
                }
                
                $this->results[] = [
                    'row' => $rowNumber,
                    'student' => $row['student_name'],
                    'roll_number' => $rollNumber,
                    'email' => $row['student_email'],
                    'class' => $class->class_name,
                    'parents_count' => count($parentIds),
                    'status' => 'success'
                ];
                
            } catch (\Exception $e) {
                $this->errors[] = "Row {$rowNumber}: " . $e->getMessage();
            }
        }
    }
    
    protected function createParent($name, $phone, $studentName)
    {
        try {
            // Check if parent with this phone already exists
            $existingParent = Parents::where('phone', $phone)->first();
            if ($existingParent) {
                return $existingParent->id;
            }
            
            // Generate unique registration token
            $registrationToken = Str::random(60);
            
            // Create temporary email
            $tempEmail = 'pending_' . time() . '_' . Str::random(8) . '@temp.parent';
            
            // Create parent user
            $parentUser = User::create([
                'name'      => $name,
                'email'     => $tempEmail,
                'password'  => Hash::make(Str::random(16)),
                'profile_picture' => 'avatar.png'
            ]);
            
            // Create parent record
            $parent = $parentUser->parent()->create([
                'gender'                    => 'male',
                'phone'                     => $phone,
                'current_address'           => 'Pending',
                'permanent_address'         => 'Pending',
                'registration_token'        => $registrationToken,
                'token_expires_at'          => now()->addDays(7),
                'registration_completed'    => false
            ]);
            
            $parentUser->assignRole('Parent');
            
            return $parent->id;
            
        } catch (\Exception $e) {
            \Log::error("Failed to create parent: " . $e->getMessage());
            return null;
        }
    }
    
    protected function generateRollNumber()
    {
        $lastStudent = Student::orderBy('id', 'desc')->first();
        
        if (!$lastStudent || empty($lastStudent->roll_number)) {
            $newNumber = 1;
        } else {
            $newNumber = (int) substr($lastStudent->roll_number, 3) + 1;
        }
        
        $maxAttempts = 1000;
        $attempts = 0;
        
        while ($attempts < $maxAttempts) {
            $rollNumber = 'RSH' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
            $email = strtolower($rollNumber) . '@roshs.co.zw';
            
            if (!User::where('email', $email)->exists()) {
                return $rollNumber;
            }
            
            $newNumber++;
            $attempts++;
        }
        
        return 'RSH' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
    
    public function rules(): array
    {
        return [
            'student_name' => 'required|string',
            'student_email' => 'required|email',
            'class_name' => 'required|string',
        ];
    }
    
    public function customValidationMessages()
    {
        return [
            'student_name.required' => 'Student name is required',
            'student_email.required' => 'Student email is required',
            'student_email.email' => 'Student email must be valid',
            'class_name.required' => 'Class name is required',
        ];
    }
    
    public function getResults()
    {
        return $this->results;
    }
    
    public function getErrors()
    {
        return $this->errors;
    }
    
    public function hasErrors()
    {
        return count($this->errors) > 0;
    }
    
    public function getSuccessCount()
    {
        return count($this->results);
    }
}
