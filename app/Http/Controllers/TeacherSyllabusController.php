<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SyllabusTopic;
use App\Subject;
use Illuminate\Support\Facades\DB;
use App\Services\SyllabusPdfImportService;

class TeacherSyllabusController extends Controller
{
    public function index()
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return redirect()->route('home')->with('error', 'Teacher profile not found.');
        }

        // Get subjects taught by this teacher
        $subjectIds = $teacher->subjects->pluck('id');

        $topics = SyllabusTopic::whereIn('subject_id', $subjectIds)
            ->with('subject')
            ->orderBy('subject_id')
            ->orderBy('order_index')
            ->paginate(20);
        
        $subjects = $teacher->subjects;
        
        return view('backend.teacher.syllabus.index', compact('topics', 'subjects'));
    }

    public function create()
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return redirect()->route('home')->with('error', 'Teacher profile not found.');
        }

        $subjects = $teacher->subjects;
        $terms = ['Term 1', 'Term 2', 'Term 3'];
        $difficultyLevels = ['easy', 'medium', 'hard'];
        
        return view('backend.teacher.syllabus.create', compact('subjects', 'terms', 'difficultyLevels'));
    }

    public function store(Request $request)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return redirect()->route('home')->with('error', 'Teacher profile not found.');
        }

        // Verify teacher teaches this subject
        $subjectIds = $teacher->subjects->pluck('id');

        // Handle multiple topics submission
        if ($request->has('multiple') && $request->has('topics')) {
            $request->validate([
                'subject_id' => 'required|exists:subjects,id',
                'term' => 'required|in:Term 1,Term 2,Term 3',
                'topics' => 'required|array|min:1',
                'topics.*.name' => 'required|string|max:255',
                'topics.*.description' => 'nullable|string',
                'topics.*.learning_objectives' => 'nullable|string',
                'topics.*.difficulty_level' => 'required|in:easy,medium,hard',
                'topics.*.suggested_periods' => 'required|integer|min:1|max:20',
                'topics.*.order_index' => 'nullable|integer|min:0',
            ]);

            if (!$subjectIds->contains($request->subject_id)) {
                return back()->withErrors(['subject_id' => 'You can only create topics for subjects you teach.']);
            }

            $createdCount = 0;
            foreach ($request->topics as $topicData) {
                SyllabusTopic::create([
                    'subject_id' => $request->subject_id,
                    'term' => $request->term,
                    'name' => $topicData['name'],
                    'description' => $topicData['description'] ?? null,
                    'learning_objectives' => $topicData['learning_objectives'] ?? null,
                    'difficulty_level' => $topicData['difficulty_level'],
                    'suggested_periods' => $topicData['suggested_periods'],
                    'order_index' => $topicData['order_index'] ?? 0,
                    'is_active' => isset($topicData['is_active']) ? true : false,
                ]);
                $createdCount++;
            }

            return redirect()->route('teacher.syllabus.index')
                ->with('success', "{$createdCount} syllabus topic(s) created successfully!");
        }
        
        // Handle single topic submission (legacy)
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'learning_objectives' => 'nullable|string',
            'term' => 'required|in:Term 1,Term 2,Term 3',
            'difficulty_level' => 'required|in:easy,medium,hard',
            'suggested_periods' => 'required|integer|min:1|max:20',
            'order_index' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ]);

        if (!$subjectIds->contains($validated['subject_id'])) {
            return back()->withErrors(['subject_id' => 'You can only create topics for subjects you teach.']);
        }

        $validated['is_active'] = $request->has('is_active');
        
        SyllabusTopic::create($validated);

        return redirect()->route('teacher.syllabus.index')
            ->with('success', 'Syllabus topic created successfully!');
    }

    public function edit($id)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return redirect()->route('home')->with('error', 'Teacher profile not found.');
        }

        $topic = SyllabusTopic::findOrFail($id);
        
        // Verify teacher teaches this subject
        $subjectIds = $teacher->subjects->pluck('id');
        if (!$subjectIds->contains($topic->subject_id)) {
            return redirect()->route('teacher.syllabus.index')
                ->with('error', 'You can only edit topics for subjects you teach.');
        }

        $subjects = $teacher->subjects;
        $terms = ['Term 1', 'Term 2', 'Term 3'];
        $difficultyLevels = ['easy', 'medium', 'hard'];
        
        return view('backend.teacher.syllabus.edit', compact('topic', 'subjects', 'terms', 'difficultyLevels'));
    }

    public function update(Request $request, $id)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return redirect()->route('home')->with('error', 'Teacher profile not found.');
        }

        $topic = SyllabusTopic::findOrFail($id);
        
        // Verify teacher teaches this subject
        $subjectIds = $teacher->subjects->pluck('id');
        if (!$subjectIds->contains($topic->subject_id)) {
            return redirect()->route('teacher.syllabus.index')
                ->with('error', 'You can only edit topics for subjects you teach.');
        }
        
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'learning_objectives' => 'nullable|string',
            'term' => 'required|in:Term 1,Term 2,Term 3',
            'difficulty_level' => 'required|in:easy,medium,hard',
            'suggested_periods' => 'required|integer|min:1|max:20',
            'order_index' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ]);

        if (!$subjectIds->contains($validated['subject_id'])) {
            return back()->withErrors(['subject_id' => 'You can only update topics for subjects you teach.']);
        }

        $validated['is_active'] = $request->has('is_active');
        
        $topic->update($validated);

        return redirect()->route('teacher.syllabus.index')
            ->with('success', 'Syllabus topic updated successfully!');
    }

    public function destroy($id)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return redirect()->route('home')->with('error', 'Teacher profile not found.');
        }

        $topic = SyllabusTopic::findOrFail($id);
        
        // Verify teacher teaches this subject
        $subjectIds = $teacher->subjects->pluck('id');
        if (!$subjectIds->contains($topic->subject_id)) {
            return redirect()->route('teacher.syllabus.index')
                ->with('error', 'You can only delete topics for subjects you teach.');
        }

        $topic->delete();

        return redirect()->route('teacher.syllabus.index')
            ->with('success', 'Syllabus topic deleted successfully!');
    }

    /**
     * Import syllabus topics from PDF file
     */
    public function importFromPdf(Request $request)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return redirect()->route('home')->with('error', 'Teacher profile not found.');
        }

        // Validate request
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'pdf_file' => 'required|file|mimes:pdf',
        ]);

        // Verify teacher teaches this subject
        $subjectIds = $teacher->subjects->pluck('id');
        if (!$subjectIds->contains($request->subject_id)) {
            return back()->withErrors(['subject_id' => 'You can only import topics for subjects you teach.']);
        }

        try {
            // Use transaction for data integrity
            DB::beginTransaction();

            // Use service to parse PDF and extract topics
            $importService = new SyllabusPdfImportService();
            $topics = $importService->importFromPdf(
                $request->file('pdf_file')->getRealPath(),
                $request->subject_id
            );

            if (empty($topics)) {
                DB::rollBack();
                return redirect()->route('teacher.syllabus.create')
                    ->with('error', 'No topics found in PDF. Please check the PDF format.');
            }

            // Create syllabus topics
            $importedCount = 0;
            foreach ($topics as $topicData) {
                SyllabusTopic::create($topicData);
                $importedCount++;
            }

            DB::commit();

            return redirect()->route('teacher.syllabus.index')
                ->with('success', "{$importedCount} syllabus topics imported successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->route('teacher.syllabus.create')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete syllabus topics
     */
    public function bulkDelete(Request $request)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return redirect()->route('home')->with('error', 'Teacher profile not found.');
        }

        $request->validate([
            'topic_ids' => 'required|array|min:1',
            'topic_ids.*' => 'exists:syllabus_topics,id',
        ]);

        try {
            DB::beginTransaction();

            $subjectIds = $teacher->subjects->pluck('id');
            
            // Get topics and verify teacher owns them
            $topics = SyllabusTopic::whereIn('id', $request->topic_ids)->get();
            
            foreach ($topics as $topic) {
                if (!$subjectIds->contains($topic->subject_id)) {
                    DB::rollBack();
                    return back()->with('error', 'You can only delete topics for subjects you teach.');
                }
            }

            // Delete topics
            $count = SyllabusTopic::whereIn('id', $request->topic_ids)->delete();

            DB::commit();

            return redirect()->route('teacher.syllabus.index')
                ->with('success', "{$count} topic(s) deleted successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->route('teacher.syllabus.index')
                ->with('error', 'Bulk delete failed: ' . $e->getMessage());
        }
    }
}
