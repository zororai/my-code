<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ResultsStatus;
use App\FeeType;
use App\TermFee;
use App\SchoolSetting;
use Carbon\Carbon;
use App\Grade;
use App\LevelFeeAdjustment;
use App\FeeLevelGroup;
use App\FeeStructure;
use App\Http\Controllers\GroceryStockController;

class ResultsStatusController extends Controller
{
    // Method to show the form for creating a new record
    public function create()
    {
        $feeTypes = FeeType::where('is_active', true)->get();
        $classes = Grade::orderBy('class_numeric', 'asc')->get();
        $upgradeDirection = SchoolSetting::get('upgrade_direction', 'ascending');
        $feeLevelGroups = FeeLevelGroup::where('is_active', true)->orderBy('display_order')->get();
        return view('results_status.create', compact('feeTypes', 'classes', 'upgradeDirection', 'feeLevelGroups'));
    }
    public function index()
    {
        $resultsStatuses = ResultsStatus::with(['termFees.feeType', 'feeStructures.feeType', 'feeStructures.feeLevelGroup'])->orderBy('year', 'desc')->orderBy('result_period', 'desc')->get();
        $feeLevelGroups = FeeLevelGroup::where('is_active', true)->orderBy('display_order')->get();
        return view('results_status.index', compact('resultsStatuses', 'feeLevelGroups'));
    }
    // Method to store a new record
    public function store(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'year' => 'required|integer',
            'result_period' => 'required|string',
            // Fee structures are optional - not all schools have both ZIMSEC and Cambridge
            'fee_structures' => 'nullable|array',
        ]);
    
        // Attempt to create a new record only if it doesn't already exist
        $existingRecord = ResultsStatus::where('year', $validatedData['year'])
                                        ->where('result_period', $validatedData['result_period'])
                                        ->exists();
    
        if ($existingRecord) {
            return redirect()->back()->withErrors(['duplicate' => 'A record with the same year and result period already exists.']);
        }
    
        // Create a new ResultsStatus record (fees will be calculated from fee_structures)
        $resultsStatus = ResultsStatus::create([
            'year' => $validatedData['year'],
            'result_period' => $validatedData['result_period'],
            'total_fees' => 0,
            'total_day_fees' => 0,
            'total_boarding_fees' => 0,
            'zimsec_day_fees' => 0,
            'zimsec_boarding_fees' => 0,
            'cambridge_day_fees' => 0,
            'cambridge_boarding_fees' => 0,
        ]);
        
        // Save level-based fee adjustments
        if ($request->has('level_adjustments')) {
            foreach ($request->level_adjustments as $level => $adjustments) {
                // ZIMSEC Day
                if (!empty($adjustments['zimsec_day']) && floatval($adjustments['zimsec_day']) > 0) {
                    LevelFeeAdjustment::create([
                        'results_status_id' => $resultsStatus->id,
                        'class_level' => $level,
                        'curriculum_type' => 'zimsec',
                        'student_type' => 'day',
                        'adjustment_amount' => floatval($adjustments['zimsec_day']),
                        'adjustment_type' => 'fixed'
                    ]);
                }
                // ZIMSEC Boarding
                if (!empty($adjustments['zimsec_boarding']) && floatval($adjustments['zimsec_boarding']) > 0) {
                    LevelFeeAdjustment::create([
                        'results_status_id' => $resultsStatus->id,
                        'class_level' => $level,
                        'curriculum_type' => 'zimsec',
                        'student_type' => 'boarding',
                        'adjustment_amount' => floatval($adjustments['zimsec_boarding']),
                        'adjustment_type' => 'fixed'
                    ]);
                }
                // Cambridge Day
                if (!empty($adjustments['cambridge_day']) && floatval($adjustments['cambridge_day']) > 0) {
                    LevelFeeAdjustment::create([
                        'results_status_id' => $resultsStatus->id,
                        'class_level' => $level,
                        'curriculum_type' => 'cambridge',
                        'student_type' => 'day',
                        'adjustment_amount' => floatval($adjustments['cambridge_day']),
                        'adjustment_type' => 'fixed'
                    ]);
                }
                // Cambridge Boarding
                if (!empty($adjustments['cambridge_boarding']) && floatval($adjustments['cambridge_boarding']) > 0) {
                    LevelFeeAdjustment::create([
                        'results_status_id' => $resultsStatus->id,
                        'class_level' => $level,
                        'curriculum_type' => 'cambridge',
                        'student_type' => 'boarding',
                        'adjustment_amount' => floatval($adjustments['cambridge_boarding']),
                        'adjustment_type' => 'fixed'
                    ]);
                }
            }
        }
        
        // Save fee structures by level groups
        if ($request->has('fee_structures')) {
            foreach ($request->fee_structures as $groupId => $groupFees) {
                foreach ($groupFees as $key => $fees) {
                    // Key format: {curriculum}_{studentType}_{isNew} e.g., "zimsec_day_existing" or "zimsec_day_new"
                    $parts = explode('_', $key);
                    if (count($parts) < 3) continue;
                    
                    $curriculumType = $parts[0];
                    $studentType = $parts[1];
                    $isNewStudent = ($parts[2] === 'new');
                    
                    if (is_array($fees)) {
                        foreach ($fees as $feeData) {
                            if (!empty($feeData['fee_type_id']) && isset($feeData['amount']) && floatval($feeData['amount']) > 0) {
                                FeeStructure::updateOrCreate(
                                    [
                                        'results_status_id' => $resultsStatus->id,
                                        'fee_level_group_id' => $groupId,
                                        'fee_type_id' => $feeData['fee_type_id'],
                                        'student_type' => $studentType,
                                        'curriculum_type' => $curriculumType,
                                        'is_for_new_student' => $isNewStudent,
                                    ],
                                    [
                                        'amount' => floatval($feeData['amount'])
                                    ]
                                );
                            }
                        }
                    }
                }
            }
        }
        
        // Save attendance settings
        $sessionMode = $request->session_mode ?? 'morning';
        SchoolSetting::set('attendance_session_mode', $sessionMode, 'text', 'Attendance session mode (morning, afternoon, or dual)');
        SchoolSetting::set('attendance_late_grace_minutes', $request->late_grace_minutes ?? 0, 'number', 'Grace period in minutes before marking as late');
        
        // Save session times based on mode
        if ($sessionMode === 'morning') {
            // Morning session only
            SchoolSetting::set('attendance_check_in_time', $request->check_in_time ?? '07:30', 'time', 'Morning session check-in time');
            SchoolSetting::set('attendance_check_out_time', $request->check_out_time ?? '12:30', 'time', 'Morning session check-out time');
            $checkIn = Carbon::parse($request->check_in_time ?? '07:30');
            $checkOut = Carbon::parse($request->check_out_time ?? '12:30');
            $workHours = $checkIn->diffInHours($checkOut);
            SchoolSetting::set('attendance_work_hours', $workHours, 'number', 'Morning session work hours');
        } elseif ($sessionMode === 'afternoon') {
            // Afternoon session only (use main check_in/check_out fields)
            SchoolSetting::set('attendance_check_in_time', $request->check_in_time ?? '12:30', 'time', 'Afternoon session check-in time');
            SchoolSetting::set('attendance_check_out_time', $request->check_out_time ?? '17:30', 'time', 'Afternoon session check-out time');
            $checkIn = Carbon::parse($request->check_in_time ?? '12:30');
            $checkOut = Carbon::parse($request->check_out_time ?? '17:30');
            $workHours = $checkIn->diffInHours($checkOut);
            SchoolSetting::set('attendance_work_hours', $workHours, 'number', 'Afternoon session work hours');
        } elseif ($sessionMode === 'dual') {
            // Dual session - morning and afternoon
            SchoolSetting::set('attendance_check_in_time', $request->check_in_time ?? '07:30', 'time', 'Morning session check-in time');
            SchoolSetting::set('attendance_check_out_time', $request->check_out_time ?? '12:30', 'time', 'Morning session check-out time');
            $checkIn = Carbon::parse($request->check_in_time ?? '07:30');
            $checkOut = Carbon::parse($request->check_out_time ?? '12:30');
            $morningHours = $checkIn->diffInHours($checkOut);
            SchoolSetting::set('attendance_work_hours', $morningHours, 'number', 'Morning session work hours');
            
            SchoolSetting::set('attendance_afternoon_check_in_time', $request->afternoon_check_in_time ?? '12:30', 'time', 'Afternoon session check-in time');
            SchoolSetting::set('attendance_afternoon_check_out_time', $request->afternoon_check_out_time ?? '17:30', 'time', 'Afternoon session check-out time');
            $afternoonIn = Carbon::parse($request->afternoon_check_in_time ?? '12:30');
            $afternoonOut = Carbon::parse($request->afternoon_check_out_time ?? '17:30');
            $afternoonHours = $afternoonIn->diffInHours($afternoonOut);
            SchoolSetting::set('attendance_afternoon_work_hours', $afternoonHours, 'number', 'Afternoon session work hours');
        }
        
        // Automatically carry forward grocery stock balances from previous term
        $itemsCarriedForward = GroceryStockController::autoCarryForwardForNewTerm(
            $validatedData['result_period'],
            $validatedData['year']
        );
        
        $stockMessage = $itemsCarriedForward > 0 
            ? " Grocery stock balances carried forward for {$itemsCarriedForward} items." 
            : "";
    
        return redirect()->route('results_status.index')->with('success', 'Term created successfully with fee structures and attendance settings!' . $stockMessage);
    }

    public function destroy($id)
    {
        // Find the record by ID
        $resultStatus = ResultsStatus::findOrFail($id);
        
        // Delete the record
        $resultStatus->delete();

        // Redirect back to the index with a success message
        return redirect()->route('results_status.index')->with('success', 'Record deleted successfully.');
    }
    

    // Method to show the edit form for a specific record
    public function edit($id)
    {
        $resultStatus = ResultsStatus::with(['feeStructures.feeType', 'feeStructures.feeLevelGroup', 'levelFeeAdjustments'])->findOrFail($id);
        $feeTypes = FeeType::where('is_active', true)->get();
        $classes = Grade::orderBy('class_numeric', 'asc')->get();
        $upgradeDirection = SchoolSetting::get('upgrade_direction', 'ascending');
        $feeLevelGroups = FeeLevelGroup::where('is_active', true)->orderBy('display_order')->get();
        
        // Group existing fee structures by level group for easier access in view
        $existingFeeStructures = [];
        foreach ($resultStatus->feeStructures as $fs) {
            $groupId = $fs->fee_level_group_id;
            $key = $fs->curriculum_type . '_' . $fs->student_type . '_' . ($fs->is_for_new_student ? 'new' : 'existing');
            if (!isset($existingFeeStructures[$groupId])) {
                $existingFeeStructures[$groupId] = [];
            }
            if (!isset($existingFeeStructures[$groupId][$key])) {
                $existingFeeStructures[$groupId][$key] = [];
            }
            $existingFeeStructures[$groupId][$key][] = [
                'fee_type_id' => $fs->fee_type_id,
                'amount' => $fs->amount
            ];
        }
        
        // Get attendance settings
        $attendanceSettings = [
            'session_mode' => SchoolSetting::get('attendance_session_mode', 'morning'),
            'check_in_time' => SchoolSetting::get('attendance_check_in_time', '07:30'),
            'check_out_time' => SchoolSetting::get('attendance_check_out_time', '12:30'),
            'late_grace_minutes' => SchoolSetting::get('attendance_late_grace_minutes', 0),
            'afternoon_check_in_time' => SchoolSetting::get('attendance_afternoon_check_in_time', '12:30'),
            'afternoon_check_out_time' => SchoolSetting::get('attendance_afternoon_check_out_time', '17:30'),
        ];
        
        return view('results_status.edit', compact('resultStatus', 'feeTypes', 'classes', 'upgradeDirection', 'feeLevelGroups', 'existingFeeStructures', 'attendanceSettings'));
    }

    // Method to update an existing record
    public function update(Request $request, $id)
    {
        $request->validate([
            'year' => 'required|integer',
            'result_period' => 'required|string',
            'fee_structures' => 'nullable|array',
        ]);

        $resultStatus = ResultsStatus::findOrFail($id);

        // Check for duplicate year+result_period combination (excluding current record)
        $existingRecord = ResultsStatus::where('year', $request->year)
            ->where('result_period', $request->result_period)
            ->where('id', '!=', $id)
            ->first();
            
        if ($existingRecord) {
            return redirect()->back()->withErrors(['year' => 'A record with this year and result period already exists.']);
        }

        // Update the results status basic info
        $resultStatus->update([
            'year' => $request->year,
            'result_period' => $request->result_period,
        ]);

        // Delete existing fee structures and level adjustments
        $resultStatus->feeStructures()->delete();
        $resultStatus->levelFeeAdjustments()->delete();
        
        // Save level-based fee adjustments
        if ($request->has('level_adjustments')) {
            foreach ($request->level_adjustments as $level => $adjustments) {
                // ZIMSEC Day
                if (!empty($adjustments['zimsec_day']) && floatval($adjustments['zimsec_day']) > 0) {
                    LevelFeeAdjustment::create([
                        'results_status_id' => $resultStatus->id,
                        'class_level' => $level,
                        'curriculum_type' => 'zimsec',
                        'student_type' => 'day',
                        'adjustment_amount' => floatval($adjustments['zimsec_day']),
                        'adjustment_type' => 'fixed'
                    ]);
                }
                // ZIMSEC Boarding
                if (!empty($adjustments['zimsec_boarding']) && floatval($adjustments['zimsec_boarding']) > 0) {
                    LevelFeeAdjustment::create([
                        'results_status_id' => $resultStatus->id,
                        'class_level' => $level,
                        'curriculum_type' => 'zimsec',
                        'student_type' => 'boarding',
                        'adjustment_amount' => floatval($adjustments['zimsec_boarding']),
                        'adjustment_type' => 'fixed'
                    ]);
                }
                // Cambridge Day
                if (!empty($adjustments['cambridge_day']) && floatval($adjustments['cambridge_day']) > 0) {
                    LevelFeeAdjustment::create([
                        'results_status_id' => $resultStatus->id,
                        'class_level' => $level,
                        'curriculum_type' => 'cambridge',
                        'student_type' => 'day',
                        'adjustment_amount' => floatval($adjustments['cambridge_day']),
                        'adjustment_type' => 'fixed'
                    ]);
                }
                // Cambridge Boarding
                if (!empty($adjustments['cambridge_boarding']) && floatval($adjustments['cambridge_boarding']) > 0) {
                    LevelFeeAdjustment::create([
                        'results_status_id' => $resultStatus->id,
                        'class_level' => $level,
                        'curriculum_type' => 'cambridge',
                        'student_type' => 'boarding',
                        'adjustment_amount' => floatval($adjustments['cambridge_boarding']),
                        'adjustment_type' => 'fixed'
                    ]);
                }
            }
        }
        
        // Save fee structures by level groups
        if ($request->has('fee_structures')) {
            foreach ($request->fee_structures as $groupId => $groupFees) {
                foreach ($groupFees as $key => $fees) {
                    // Key format: {curriculum}_{studentType}_{isNew} e.g., "zimsec_day_existing" or "zimsec_day_new"
                    $parts = explode('_', $key);
                    if (count($parts) < 3) continue;
                    
                    $curriculumType = $parts[0];
                    $studentType = $parts[1];
                    $isNewStudent = ($parts[2] === 'new');
                    
                    if (is_array($fees)) {
                        foreach ($fees as $feeData) {
                            if (!empty($feeData['fee_type_id']) && isset($feeData['amount']) && floatval($feeData['amount']) > 0) {
                                FeeStructure::create([
                                    'results_status_id' => $resultStatus->id,
                                    'fee_level_group_id' => $groupId,
                                    'fee_type_id' => $feeData['fee_type_id'],
                                    'student_type' => $studentType,
                                    'curriculum_type' => $curriculumType,
                                    'is_for_new_student' => $isNewStudent,
                                    'amount' => floatval($feeData['amount'])
                                ]);
                            }
                        }
                    }
                }
            }
        }
        
        // Save attendance settings
        $sessionMode = $request->session_mode ?? 'morning';
        SchoolSetting::set('attendance_session_mode', $sessionMode, 'text', 'Attendance session mode (morning, afternoon, or dual)');
        SchoolSetting::set('attendance_late_grace_minutes', $request->late_grace_minutes ?? 0, 'number', 'Grace period in minutes before marking as late');
        
        // Save session times based on mode
        if ($sessionMode === 'morning') {
            SchoolSetting::set('attendance_check_in_time', $request->check_in_time ?? '07:30', 'time', 'Morning session check-in time');
            SchoolSetting::set('attendance_check_out_time', $request->check_out_time ?? '12:30', 'time', 'Morning session check-out time');
            $checkIn = Carbon::parse($request->check_in_time ?? '07:30');
            $checkOut = Carbon::parse($request->check_out_time ?? '12:30');
            $workHours = $checkIn->diffInHours($checkOut);
            SchoolSetting::set('attendance_work_hours', $workHours, 'number', 'Morning session work hours');
        } elseif ($sessionMode === 'afternoon') {
            SchoolSetting::set('attendance_check_in_time', $request->check_in_time ?? '12:30', 'time', 'Afternoon session check-in time');
            SchoolSetting::set('attendance_check_out_time', $request->check_out_time ?? '17:30', 'time', 'Afternoon session check-out time');
            $checkIn = Carbon::parse($request->check_in_time ?? '12:30');
            $checkOut = Carbon::parse($request->check_out_time ?? '17:30');
            $workHours = $checkIn->diffInHours($checkOut);
            SchoolSetting::set('attendance_work_hours', $workHours, 'number', 'Afternoon session work hours');
        } elseif ($sessionMode === 'dual') {
            SchoolSetting::set('attendance_check_in_time', $request->check_in_time ?? '07:30', 'time', 'Morning session check-in time');
            SchoolSetting::set('attendance_check_out_time', $request->check_out_time ?? '12:30', 'time', 'Morning session check-out time');
            $checkIn = Carbon::parse($request->check_in_time ?? '07:30');
            $checkOut = Carbon::parse($request->check_out_time ?? '12:30');
            $morningHours = $checkIn->diffInHours($checkOut);
            SchoolSetting::set('attendance_work_hours', $morningHours, 'number', 'Morning session work hours');
            
            SchoolSetting::set('attendance_afternoon_check_in_time', $request->afternoon_check_in_time ?? '12:30', 'time', 'Afternoon session check-in time');
            SchoolSetting::set('attendance_afternoon_check_out_time', $request->afternoon_check_out_time ?? '17:30', 'time', 'Afternoon session check-out time');
            $afternoonIn = Carbon::parse($request->afternoon_check_in_time ?? '12:30');
            $afternoonOut = Carbon::parse($request->afternoon_check_out_time ?? '17:30');
            $afternoonHours = $afternoonIn->diffInHours($afternoonOut);
            SchoolSetting::set('attendance_afternoon_work_hours', $afternoonHours, 'number', 'Afternoon session work hours');
        }

        return redirect()->route('results_status.index')->with('success', 'Term updated successfully with fee structures and attendance settings!');
    }
}