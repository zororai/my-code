<?php

namespace App\Http\Controllers;

use App\StudentAchievement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentAchievementController extends Controller
{
    public function index()
    {
        $achievements = StudentAchievement::orderBy('order')->get();
        return view('backend.website-settings.achievements.index', compact('achievements'));
    }

    public function create()
    {
        return view('backend.website-settings.achievements.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_name' => 'required|string|max:255',
            'achievement_title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'points' => 'nullable|string|max:50',
            'subjects' => 'nullable|array',
            'image' => 'required|image|max:5120',
        ]);

        $imagePath = $request->file('image')->store('achievements', 'public');

        $maxOrder = StudentAchievement::max('order') ?? 0;

        StudentAchievement::create([
            'student_name' => $request->student_name,
            'achievement_title' => $request->achievement_title ?? 'Congratulations',
            'description' => $request->description,
            'points' => $request->points,
            'subjects' => $request->subjects,
            'image_path' => $imagePath,
            'is_active' => $request->has('is_active'),
            'order' => $maxOrder + 1,
        ]);

        return redirect()->route('admin.website.homepage')->with('success', 'Slide added successfully!');
    }

    public function edit(StudentAchievement $achievement)
    {
        return view('backend.website-settings.achievements.edit', compact('achievement'));
    }

    public function update(Request $request, StudentAchievement $achievement)
    {
        $request->validate([
            'student_name' => 'required|string|max:255',
            'achievement_title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'points' => 'nullable|string|max:50',
            'subjects' => 'nullable|array',
            'image' => 'nullable|image|max:5120',
        ]);

        $data = [
            'student_name' => $request->student_name,
            'achievement_title' => $request->achievement_title ?? 'Congratulations',
            'description' => $request->description,
            'points' => $request->points,
            'subjects' => $request->subjects,
            'is_active' => $request->has('is_active'),
        ];

        if ($request->hasFile('image')) {
            if ($achievement->image_path) {
                Storage::disk('public')->delete($achievement->image_path);
            }
            $data['image_path'] = $request->file('image')->store('achievements', 'public');
        }

        $achievement->update($data);

        return redirect()->route('admin.website.homepage')->with('success', 'Slide updated successfully!');
    }

    public function destroy(StudentAchievement $achievement)
    {
        if ($achievement->image_path) {
            Storage::disk('public')->delete($achievement->image_path);
        }
        $achievement->delete();

        return redirect()->route('admin.website.homepage')->with('success', 'Slide deleted successfully!');
    }

    public function updateOrder(Request $request)
    {
        $order = $request->order;
        foreach ($order as $index => $id) {
            StudentAchievement::where('id', $id)->update(['order' => $index]);
        }
        return response()->json(['success' => true]);
    }

    public function toggleStatus(StudentAchievement $achievement)
    {
        $achievement->update(['is_active' => !$achievement->is_active]);
        $status = $achievement->is_active ? 'enabled' : 'disabled';
        return redirect()->route('admin.website.homepage')->with('success', "Slide {$status} successfully!");
    }
}
