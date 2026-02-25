<?php

namespace App\Imports;

use App\Subject;
use App\Teacher;
use App\User;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Collection;

class TeacherSubjectAssignmentImport implements ToCollection, WithHeadingRow, WithValidation
{
    protected $results = [];
    protected $errors = [];
    
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            
            try {
                // Find teacher by email or name
                $teacher = null;
                
                if (!empty($row['teacher_email'])) {
                    $user = User::where('email', $row['teacher_email'])->first();
                    if ($user) {
                        $teacher = $user->teacher;
                    }
                } elseif (!empty($row['teacher_name'])) {
                    $user = User::where('name', $row['teacher_name'])->first();
                    if ($user) {
                        $teacher = $user->teacher;
                    }
                }
                
                if (!$teacher) {
                    $this->errors[] = "Row {$rowNumber}: Teacher not found (email: {$row['teacher_email']}, name: {$row['teacher_name']})";
                    continue;
                }
                
                // Find subject by code
                $subject = Subject::where('subject_code', $row['subject_code'])->first();
                
                if (!$subject) {
                    $this->errors[] = "Row {$rowNumber}: Subject with code '{$row['subject_code']}' not found";
                    continue;
                }
                
                // Check if subject is already assigned
                if ($subject->teacher_id && $subject->teacher_id != $teacher->id) {
                    $currentTeacher = $subject->teacher->user->name ?? 'Unknown';
                    $this->errors[] = "Row {$rowNumber}: Subject '{$subject->name}' ({$subject->subject_code}) is already assigned to {$currentTeacher}";
                    continue;
                }
                
                // Assign subject to teacher
                $subject->update(['teacher_id' => $teacher->id]);
                
                $this->results[] = [
                    'row' => $rowNumber,
                    'teacher' => $teacher->user->name,
                    'subject' => $subject->name,
                    'subject_code' => $subject->subject_code,
                    'status' => 'success'
                ];
                
            } catch (\Exception $e) {
                $this->errors[] = "Row {$rowNumber}: " . $e->getMessage();
            }
        }
    }
    
    public function rules(): array
    {
        return [
            'subject_code' => 'required|string',
        ];
    }
    
    public function customValidationMessages()
    {
        return [
            'subject_code.required' => 'Subject code is required',
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
