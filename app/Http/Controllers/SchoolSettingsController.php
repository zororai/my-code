<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SchoolSetting;
use App\ClassFormat;
use App\Grade;

class SchoolSettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function classFormats()
    {
        $classFormats = ClassFormat::ordered()->get();
        $existingClasses = Grade::orderBy('class_numeric')->get();
        $formatTemplates = \App\ClassFormatTemplate::active()->get();
        
        return view('backend.admin.settings.class-formats', compact('classFormats', 'existingClasses', 'formatTemplates'));
    }

    public function storeClassFormat(Request $request)
    {
        $validated = $request->validate([
            'format_name' => 'required|string|max:100',
            'numeric_value' => 'required|integer|min:0',
            'display_name' => 'required|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? ClassFormat::max('sort_order') + 1;
        
        ClassFormat::create($validated);

        return redirect()->route('admin.settings.class-formats')
            ->with('success', 'Class format added successfully!');
    }

    public function updateClassFormat(Request $request, $id)
    {
        $classFormat = ClassFormat::findOrFail($id);

        $validated = $request->validate([
            'format_name' => 'required|string|max:100',
            'numeric_value' => 'required|integer|min:0',
            'display_name' => 'required|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        
        $classFormat->update($validated);

        return redirect()->route('admin.settings.class-formats')
            ->with('success', 'Class format updated successfully!');
    }

    public function deleteClassFormat($id)
    {
        $classFormat = ClassFormat::findOrFail($id);
        $classFormat->delete();

        return redirect()->route('admin.settings.class-formats')
            ->with('success', 'Class format deleted successfully!');
    }

    public function bulkStoreClassFormats(Request $request)
    {
        $validated = $request->validate([
            'grades' => 'required|array|min:1',
            'grades.*.numeric_value' => 'required|integer|min:0',
            'grades.*.class_names' => 'required|array|min:1',
            'grades.*.class_names.*' => 'required|string',
        ]);

        $createdCount = 0;
        $sortOrder = ClassFormat::max('sort_order') ?? 0;

        foreach ($validated['grades'] as $gradeLevel => $gradeData) {
            $numericValue = $gradeData['numeric_value'];
            $classNames = $gradeData['class_names'];
            
            foreach ($classNames as $className) {
                $sortOrder++;
                $formatName = $gradeLevel . ' ' . $className;
                
                // Check if already exists
                if (!ClassFormat::where('format_name', $formatName)->exists()) {
                    ClassFormat::create([
                        'format_name' => $formatName,
                        'numeric_value' => $numericValue,
                        'display_name' => $formatName,
                        'sort_order' => $sortOrder,
                        'is_active' => true,
                    ]);
                    $createdCount++;
                }
            }
        }

        return redirect()->route('admin.settings.class-formats')
            ->with('success', "Successfully created {$createdCount} class format(s)!");
    }

    public function upgradeDirection()
    {
        $upgradeDirection = SchoolSetting::get('upgrade_direction', 'ascending');
        $classes = Grade::orderBy('class_numeric')->get();
        
        return view('backend.admin.settings.upgrade-direction', compact('upgradeDirection', 'classes'));
    }

    public function updateUpgradeDirection(Request $request)
    {
        $validated = $request->validate([
            'upgrade_direction' => 'required|in:ascending,descending',
        ]);

        SchoolSetting::set(
            'upgrade_direction', 
            $validated['upgrade_direction'],
            'select',
            'Direction of class upgrade (ascending: 1->2->3 or descending: 3->2->1)'
        );

        return redirect()->route('admin.settings.upgrade-direction')
            ->with('success', 'Upgrade direction updated successfully!');
    }

    public function getUpgradePreview(Request $request)
    {
        $direction = SchoolSetting::get('upgrade_direction', 'ascending');
        $classes = Grade::orderBy('class_numeric', $direction === 'ascending' ? 'asc' : 'desc')->get();
        
        $upgradeMap = [];
        
        foreach ($classes as $index => $class) {
            if ($direction === 'ascending') {
                $nextClass = Grade::where('class_numeric', $class->class_numeric + 1)->first();
            } else {
                $nextClass = Grade::where('class_numeric', $class->class_numeric - 1)->first();
            }
            
            $upgradeMap[] = [
                'current' => $class->class_name,
                'current_numeric' => $class->class_numeric,
                'next' => $nextClass ? $nextClass->class_name : 'Graduated/Final',
                'next_numeric' => $nextClass ? $nextClass->class_numeric : null,
            ];
        }

        return response()->json([
            'success' => true,
            'direction' => $direction,
            'upgrade_map' => $upgradeMap,
        ]);
    }

    public function receiptSettings()
    {
        $settings = [
            'receipt_school_short_name' => SchoolSetting::get('receipt_school_short_name', 'ROSHS'),
            'receipt_school_full_name' => SchoolSetting::get('receipt_school_full_name', 'Rose Of Sharon High School'),
            'receipt_footer_message' => SchoolSetting::get('receipt_footer_message', 'Thank You!'),
            'receipt_footer_note' => SchoolSetting::get('receipt_footer_note', 'This is a computer-generated receipt.'),
        ];
        
        return view('backend.admin.settings.receipt-settings', compact('settings'));
    }

    public function updateReceiptSettings(Request $request)
    {
        $validated = $request->validate([
            'receipt_school_short_name' => 'required|string|max:50',
            'receipt_school_full_name' => 'required|string|max:255',
            'receipt_footer_message' => 'required|string|max:100',
            'receipt_footer_note' => 'required|string|max:255',
        ]);

        foreach ($validated as $key => $value) {
            SchoolSetting::set($key, $value, 'text');
        }

        return redirect()->route('admin.settings.receipt')
            ->with('success', 'Receipt settings updated successfully!');
    }

    /**
     * Display theme settings page
     */
    public function themeSettings()
    {
        $settings = [
            'theme_primary_color' => SchoolSetting::get('theme_primary_color', '#2563eb'),
            'theme_primary_hover' => SchoolSetting::get('theme_primary_hover', '#1d4ed8'),
            'theme_primary_dark' => SchoolSetting::get('theme_primary_dark', '#1e40af'),
            'theme_sidebar_color' => SchoolSetting::get('theme_sidebar_color', '#2563eb'),
            'theme_navbar_color' => SchoolSetting::get('theme_navbar_color', '#2563eb'),
        ];
        
        return view('backend.admin.settings.theme-settings', compact('settings'));
    }

    /**
     * Update theme settings
     */
    public function updateThemeSettings(Request $request)
    {
        $validated = $request->validate([
            'theme_primary_color' => 'required|string|max:20',
            'theme_primary_hover' => 'required|string|max:20',
            'theme_primary_dark' => 'required|string|max:20',
            'theme_sidebar_color' => 'required|string|max:20',
            'theme_navbar_color' => 'required|string|max:20',
        ]);

        foreach ($validated as $key => $value) {
            SchoolSetting::set($key, $value, 'color');
        }

        return redirect()->route('admin.settings.theme')
            ->with('success', 'Theme settings updated successfully!');
    }
}
