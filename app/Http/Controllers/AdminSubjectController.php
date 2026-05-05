<?php

namespace App\Http\Controllers;

use App\Subject;
use App\Teacher;
use App\Grade;
use App\OnboardSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Imports\TeacherSubjectAssignmentImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;

class AdminSubjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $classes = Grade::with(['subjects' => function($query) {
            $query->with('teacher')->withCount('readings');
        }])->orderBy('class_name')->get();

        $totalSubjects = Subject::count();

        $allSubjects = Subject::with('grades')
            ->orderBy('name')
            ->get();

        return view('backend.subjectsadmin.index', compact('classes', 'totalSubjects', 'allSubjects'));
    }
    
    public function showByClass($classId)
    {
        $class = Grade::findOrFail($classId);
        $subjects = $class->subjects()
            ->with('teacher')
            ->withCount('readings')
            ->latest()
            ->paginate(10);
        
        return view('backend.subjectsadmin.show_class', compact('class', 'subjects'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $classes = Grade::orderBy('class_name')->get();
        $onboardSubjects = OnboardSubject::orderBy('name')->get();

        return view('backend.subjectsadmin.create', compact('classes', 'onboardSubjects'));
    }

    /**
     * Generate subject code from subject name and class name
     * Format: F{number}{stream_initial} {subject_first_letter}{subject_last_letter}
     * Example: "English" + "Form 2 Red" = "F2R EH"
     */
    private function generateSubjectCode($subjectName, $className)
    {
        $subjectName = strtoupper(trim($subjectName));
        $className = trim($className);
        
        // Parse class name format: "Form {number} {stream}"
        if (preg_match('/Form\s+(\d+)\s+(\w+)/i', $className, $matches)) {
            $formNumber = $matches[1];
            $stream = $matches[2];
            $streamInitial = strtoupper(substr($stream, 0, 1));
            
            // Get first and last letter of subject
            $firstLetterSubject = substr($subjectName, 0, 1);
            $lastLetterSubject = substr($subjectName, -1);
            
            // Format: F{number}{stream_initial} {subject_first}{subject_last}
            return "F{$formNumber}{$streamInitial} {$firstLetterSubject}{$lastLetterSubject}";
        }
        
        // Fallback to old format if pattern doesn't match
        $cleanClass = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $className));
        $firstLetterSubject = substr($subjectName, 0, 1);
        $lastLetterSubject = substr($subjectName, -1);
        $firstCharClass = substr($cleanClass, 0, 1);
        $lastCharClass = substr($cleanClass, -1);
        
        return $firstLetterSubject . $lastLetterSubject . $firstCharClass . $lastCharClass;
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
            'class_id' => 'required|exists:grades,id',
            'subjects' => 'required|array|min:1',
            'subjects.*.name' => 'required|string|max:255',
            'subjects.*.subject_code' => 'required|string|max:20',
            'subjects.*.single_lessons_per_week' => 'nullable|numeric|min:0|max:20',
            'subjects.*.double_lessons_per_week' => 'nullable|numeric|min:0|max:10',
            'subjects.*.triple_lessons_per_week' => 'nullable|numeric|min:0|max:5',
            'subjects.*.quad_lessons_per_week'   => 'nullable|numeric|min:0|max:5',
        ]);

        // Convert empty strings to 0 for lesson fields
        $subjects = $request->subjects;
        foreach ($subjects as $key => $subject) {
            $subjects[$key]['single_lessons_per_week'] = intval($subject['single_lessons_per_week'] ?? 0);
            $subjects[$key]['double_lessons_per_week'] = intval($subject['double_lessons_per_week'] ?? 0);
            $subjects[$key]['triple_lessons_per_week'] = intval($subject['triple_lessons_per_week'] ?? 0);
            $subjects[$key]['quad_lessons_per_week'] = intval($subject['quad_lessons_per_week'] ?? 0);
        }
        $request->merge(['subjects' => $subjects]);

        $class = Grade::findOrFail($request->class_id);
        $createdCount = 0;

        foreach ($request->subjects as $subjectData) {
            // Calculate total periods per week
            $totalPeriods = ($subjectData['single_lessons_per_week'] ?? 0) * 1 
                          + ($subjectData['double_lessons_per_week'] ?? 0) * 2 
                          + ($subjectData['triple_lessons_per_week'] ?? 0) * 3 
                          + ($subjectData['quad_lessons_per_week'] ?? 0) * 4;

            // Create new subject (allows same name for different classes with unique codes)
            $subject = Subject::create([
                'name'          => $subjectData['name'],
                'slug'          => Str::slug($subjectData['name']) . '-' . Str::slug($class->class_name),
                'subject_code'  => $subjectData['subject_code'],
                'single_lessons_per_week' => $subjectData['single_lessons_per_week'] ?? 0,
                'double_lessons_per_week' => $subjectData['double_lessons_per_week'] ?? 0,
                'triple_lessons_per_week' => $subjectData['triple_lessons_per_week'] ?? 0,
                'quad_lessons_per_week'   => $subjectData['quad_lessons_per_week'] ?? 0,
                'periods_per_week' => $totalPeriods,
            ]);

            // Attach subject to the selected class
            $subject->grades()->attach($class->id);
            $createdCount++;
        }

        return redirect()->route('admin.subjects.index')->with('success', $createdCount . ' subject(s) created and assigned to ' . $class->class_name . '.');
    }

    /**
     * Show the assign subject to teacher form.
     */
    public function assignForm()
    {
        $teachers = Teacher::with('user')->latest()->get();
        $subjects = Subject::whereNull('teacher_id')->latest()->get();
        $assignedSubjects = Subject::with('teacher.user')->whereNotNull('teacher_id')->latest()->get();

        return view('backend.subjectsadmin.assign', compact('teachers', 'subjects', 'assignedSubjects'));
    }

    /**
     * Assign subjects to a teacher.
     */
    public function assign(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'subject_ids' => 'required|array|min:1',
            'subject_ids.*' => 'exists:subjects,id',
        ]);

        Subject::whereIn('id', $request->subject_ids)->update(['teacher_id' => $request->teacher_id]);

        $count = count($request->subject_ids);
        return redirect()->route('admin.subjects.assign')->with('success', $count . ' subject(s) assigned to teacher successfully.');
    }

    /**
     * Remove teacher assignment from a subject.
     */
    public function unassign($id)
    {
        $subject = Subject::findOrFail($id);
        $subject->update(['teacher_id' => null]);

        return redirect()->route('admin.subjects.assign')->with('success', 'Teacher unassigned from subject.');
    }

    /**
     * Remove teacher assignments from multiple subjects.
     */
    public function bulkUnassign(Request $request)
    {
        $request->validate([
            'subject_ids' => 'required|array|min:1',
            'subject_ids.*' => 'exists:subjects,id',
        ]);

        Subject::whereIn('id', $request->subject_ids)->update(['teacher_id' => null]);

        $count = count($request->subject_ids);
        return redirect()->route('admin.subjects.assign')->with('success', $count . ' teacher assignment(s) removed successfully.');
    }

    /**
     * Update only the weekly lesson configuration for a subject.
     */
    public function updateLessonConfig(Request $request, Subject $subject)
    {
        $request->validate([
            'single_lessons_per_week' => 'nullable|integer|min:0|max:20',
            'double_lessons_per_week' => 'nullable|integer|min:0|max:10',
            'triple_lessons_per_week' => 'nullable|integer|min:0|max:5',
            'quad_lessons_per_week'   => 'nullable|integer|min:0|max:5',
        ]);

        $single = intval($request->single_lessons_per_week);
        $double = intval($request->double_lessons_per_week);
        $triple = intval($request->triple_lessons_per_week);
        $quad   = intval($request->quad_lessons_per_week);

        $subject->update([
            'single_lessons_per_week' => $single,
            'double_lessons_per_week' => $double,
            'triple_lessons_per_week' => $triple,
            'quad_lessons_per_week'   => $quad,
            'periods_per_week'        => ($single * 1) + ($double * 2) + ($triple * 3) + ($quad * 4),
        ]);

        return redirect()->route('admin.subjects.index', ['#lesson-config'])
            ->with('success', '"' . $subject->name . '" lesson configuration updated successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Subject  $subject
     * @return \Illuminate\Http\Response
     */
    public function edit(Subject $subject)
    {
        $teachers = Teacher::latest()->get();

        return view('backend.subjectsadmin.edit', compact('subject','teachers'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Subject  $subject
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Subject $subject)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'subject_code'  => 'required|string|max:20',
            'teacher_id'    => 'nullable|numeric',
            'single_lessons_per_week' => 'nullable|numeric|min:0|max:20',
            'double_lessons_per_week' => 'nullable|numeric|min:0|max:10',
            'triple_lessons_per_week' => 'nullable|numeric|min:0|max:5',
            'quad_lessons_per_week'   => 'nullable|numeric|min:0|max:5',
        ]);

        // Calculate total periods per week
        $totalPeriods = (intval($request->single_lessons_per_week) * 1) 
                      + (intval($request->double_lessons_per_week) * 2) 
                      + (intval($request->triple_lessons_per_week) * 3) 
                      + (intval($request->quad_lessons_per_week) * 4);

        $subject->update([
            'name'          => $request->name,
            'slug'          => Str::slug($request->name),
            'subject_code'  => $request->subject_code,
            'teacher_id'    => $request->teacher_id,
            'single_lessons_per_week' => intval($request->single_lessons_per_week),
            'double_lessons_per_week' => intval($request->double_lessons_per_week),
            'triple_lessons_per_week' => intval($request->triple_lessons_per_week),
            'quad_lessons_per_week'   => intval($request->quad_lessons_per_week),
            'periods_per_week' => $totalPeriods
        ]);

        return redirect()->route('admin.subjects.index')->with('success', 'Subject updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Subject  $subject
     * @return \Illuminate\Http\Response
     */
    public function destroy(Subject $subject)
    {
        $subject->delete();

        return back()->with('success', 'Subject deleted successfully.');
    }

    /**
     * Import teacher-subject assignments from CSV/Excel file.
     */
    public function importAssignments(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:csv,xlsx,xls|max:2048',
        ]);

        try {
            $import = new TeacherSubjectAssignmentImport();
            Excel::import($import, $request->file('import_file'));

            $successCount = $import->getSuccessCount();
            $errors = $import->getErrors();

            if ($import->hasErrors()) {
                $errorMessage = "Import completed with {$successCount} successful assignment(s) and " . count($errors) . " error(s):<br>";
                foreach (array_slice($errors, 0, 10) as $error) {
                    $errorMessage .= "• " . $error . "<br>";
                }
                if (count($errors) > 10) {
                    $errorMessage .= "• ... and " . (count($errors) - 10) . " more error(s)";
                }
                
                return redirect()->route('admin.subjects.assign')
                    ->with('warning', $errorMessage)
                    ->with('import_success_count', $successCount);
            }

            return redirect()->route('admin.subjects.assign')
                ->with('success', "Successfully imported {$successCount} teacher-subject assignment(s).");

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errorMessage = "Validation errors found:<br>";
            foreach (array_slice($failures, 0, 10) as $failure) {
                $errorMessage .= "• Row {$failure->row()}: " . implode(', ', $failure->errors()) . "<br>";
            }
            
            return redirect()->route('admin.subjects.assign')
                ->with('error', $errorMessage);
                
        } catch (\Exception $e) {
            return redirect()->route('admin.subjects.assign')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Download CSV template for bulk teacher-subject assignment.
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="teacher_subject_assignment_template.csv"',
        ];

        $columns = ['teacher_email', 'teacher_name', 'subject_code', 'subject_name'];
        
        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($file, $columns);
            
            // Add sample data rows
            fputcsv($file, ['john.doe@school.com', 'John Doe', 'F1R EH', 'English']);
            fputcsv($file, ['john.doe@school.com', 'John Doe', 'F1R MS', 'Mathematics']);
            fputcsv($file, ['jane.smith@school.com', 'Jane Smith', 'F2B SH', 'Shona']);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
