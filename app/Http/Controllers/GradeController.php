<?php

namespace App\Http\Controllers;

use App\Grade;
use App\Subject;
use App\Teacher;
use App\ClassFormat;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $classes = Grade::withCount('students')->latest()->paginate(10);

        return view('backend.classes.index', compact('classes'));
    }
    public function adminindex()
    {
        $classes = Grade::withCount('students')->latest()->paginate(10);

        return view('classes.index', compact('classes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $teachers = Teacher::where('is_class_teacher', 1)
            ->whereDoesntHave('classes')
            ->latest()
            ->get();
        $classFormats = ClassFormat::active()->ordered()->get();
        $existingClasses = Grade::pluck('class_name')->toArray();
        
        return view('backend.classes.create', compact('teachers', 'classFormats', 'existingClasses'));
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
            'selected_classes'   => 'required|array|min:1',
            'selected_classes.*' => 'required|string|max:255|unique:grades,class_name',
        ], [
            'selected_classes.required' => 'Please select at least one class to create.',
            'selected_classes.min'      => 'Please select at least one class to create.',
            'selected_classes.*.unique' => 'The class ":input" already exists.',
        ]);

        $classFormats = ClassFormat::active()->get()->keyBy('display_name');
        $createdCount = 0;

        foreach ($request->selected_classes as $className) {
            $format = $classFormats->get($className);
            $numericValue = $format ? $format->numeric_value : 0;
            
            Grade::create([
                'class_name'        => $className,
                'class_numeric'     => $numericValue,
                'teacher_id'        => null,
                'class_description' => 'This is the ' . $className . ' classroom for the current academic year.'
            ]);
            $createdCount++;
        }

        return redirect()->route('classes.index')->with('success', $createdCount . ' class(es) created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Grade  $grade
     * @return \Illuminate\Http\Response
     */
    public function show(Grade $grade)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Grade  $grade
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $class = Grade::findOrFail($id);
        
        $teachers = Teacher::where('is_class_teacher', 1)
            ->where(function($query) use ($class) {
                $query->whereDoesntHave('classes')
                      ->orWhere('id', $class->teacher_id);
            })
            ->latest()
            ->get();
        
        $classFormats = ClassFormat::active()->ordered()->get();

        return view('backend.classes.edit', compact('class', 'teachers', 'classFormats'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Grade  $grade
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'class_name'        => 'required|string|max:255|unique:grades,class_name,'.$id,
            'class_numeric'     => 'required|numeric',
            'teacher_id'        => 'nullable|numeric',
        ]);

        $class = Grade::findOrFail($id);

        $class->update([
            'class_name'        => $request->class_name,
            'class_numeric'     => $request->class_numeric,
            'teacher_id'        => $request->teacher_id ?: null,
        ]);

        return redirect()->route('classes.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Grade  $grade
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $class = Grade::findOrFail($id);
        
        $class->subjects()->detach();
        $class->delete();

        return back();
    }

    /*
     * Assign Subjects to Grade 
     * 
     * @return \Illuminate\Http\Response
     */
    public function assignSubject($classid)
    {
        $subjects   = Subject::latest()->get();
        $assigned   = Grade::with(['subjects','students'])->findOrFail($classid);

        return view('backend.classes.assign-subject', compact('classid','subjects','assigned'));
    }

    /*
     * Add Assigned Subjects to Grade 
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeAssignedSubject(Request $request, $id)
    {
        $class = Grade::findOrFail($id);

        $class->subjects()->sync($request->selectedsubjects);

        return redirect()->route('classes.index');
    }
}
