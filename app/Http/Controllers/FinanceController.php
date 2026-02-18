<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Student;
use App\Parents;
use App\SchoolIncome;
use App\SchoolExpense;
use App\Product;
use App\StudentPayment;
use App\CashBookEntry;
use App\PaymentVerification;
use App\FeeStructure;
use App\FeeLevelGroup;
use DB;

class FinanceController extends Controller
{
    public function studentPayments(Request $request)
    {
        $currentTerm = \App\ResultsStatus::with(['termFees.feeType', 'feeStructures.feeType', 'feeStructures.feeLevelGroup'])
            ->orderBy('year', 'desc')
            ->orderBy('result_period', 'desc')
            ->first();
        
        // Get ALL students for the Record Payment modal (unfiltered)
        $allStudentsForModal = Student::with(['user', 'class', 'parent.user', 'payments.termFee.feeType', 'payments.feeStructure.feeType', 'payments.resultsStatus'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get all terms for cumulative calculation
        $allTermsForCalc = \App\ResultsStatus::with(['termFees.feeType', 'feeStructures.feeType', 'feeStructures.feeLevelGroup'])
            ->orderBy('year', 'asc')
            ->orderBy('result_period', 'asc')
            ->get();
        
        // Calculate total fees and amount paid for modal students based on student type, curriculum, and scholarship
        foreach ($allStudentsForModal as $student) {
            $feeBreakdown = $this->calculateCumulativeFees($student, $allTermsForCalc, $currentTerm);
            $student->current_term_fees = $feeBreakdown['current_term_fees'];
            $student->balance_bf = $feeBreakdown['balance_bf'];
            $student->total_fees = $feeBreakdown['total_fees'];
            $student->amount_paid = floatval(\App\StudentPayment::where('student_id', $student->id)
                ->sum('amount_paid'));
            $student->balance = $student->total_fees - $student->amount_paid;
        }
        
        // Build query for table (with filters)
        $query = Student::with(['user', 'class', 'parent.user', 'payments.termFee.feeType', 'payments.feeStructure.feeType', 'payments.resultsStatus']);
        
        // Apply class filter if provided
        if ($request->has('class_id') && $request->class_id != '') {
            $query->where('class_id', $request->class_id);
        }
        
        // Apply student type filter if provided
        if ($request->has('student_type') && $request->student_type != '') {
            $query->where('student_type', $request->student_type);
        }
        
        $filteredStudents = $query->orderBy('created_at', 'desc')->get();
        
        // Calculate total fees and amount paid for filtered students based on student type, curriculum, and scholarship
        foreach ($filteredStudents as $student) {
            $feeBreakdown = $this->calculateCumulativeFees($student, $allTermsForCalc, $currentTerm);
            $student->current_term_fees = $feeBreakdown['current_term_fees'];
            $student->balance_bf = $feeBreakdown['balance_bf'];
            $student->total_fees = $feeBreakdown['total_fees'];
            $student->amount_paid = floatval(\App\StudentPayment::where('student_id', $student->id)
                ->sum('amount_paid'));
            $student->balance = $student->total_fees - $student->amount_paid;
        }
        
        // Apply status filter after calculating balances
        if ($request->has('status') && $request->status != '') {
            $status = $request->status;
            $filteredStudents = $filteredStudents->filter(function($student) use ($status) {
                $balance = $student->balance;
                $totalFees = $student->total_fees;
                $amountPaid = $student->amount_paid;
                
                if ($status === 'paid') {
                    return $balance == 0 && $totalFees > 0;
                } elseif ($status === 'partial') {
                    return $amountPaid > 0 && $balance > 0;
                } elseif ($status === 'unpaid') {
                    return $amountPaid == 0 || $balance == $totalFees;
                }
                return true;
            });
        }
        
        // Manual pagination for table
        $perPage = 20;
        $currentPage = $request->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        
        $students = new \Illuminate\Pagination\LengthAwarePaginator(
            $filteredStudents->slice($offset, $perPage)->values(),
            $filteredStudents->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        $classes = \App\Grade::orderBy('class_name')->get();
        
        // Get all available terms for the dropdown
        $allTerms = \App\ResultsStatus::with(['termFees.feeType', 'feeStructures.feeType', 'feeStructures.feeLevelGroup'])
            ->orderBy('year', 'desc')
            ->orderBy('result_period', 'desc')
            ->get();
        
        // Get pending payment verifications from parents
        $pendingVerifications = PaymentVerification::with(['parent.user', 'student.user', 'student.class'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('backend.finance.student-payments', compact('students', 'currentTerm', 'classes', 'allTerms', 'allStudentsForModal', 'pendingVerifications'));
    }

    public function storePayment(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'results_status_id' => 'required|exists:results_statuses,id',
            'fee_amounts' => 'required|array',
            'fee_amounts.*' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $totalPaid = 0;
        $feesPaidFor = [];

        foreach ($validated['fee_amounts'] as $feeId => $amount) {
            // Skip if amount is 0 or empty
            if ($amount <= 0) {
                continue;
            }

            // Try to find as FeeStructure first (new system), then fall back to TermFee (legacy)
            $feeRecord = FeeStructure::find($feeId);
            $isFeeStructure = true;
            
            if (!$feeRecord) {
                $feeRecord = \App\TermFee::find($feeId);
                $isFeeStructure = false;
            }
            
            if (!$feeRecord) {
                continue; // Skip if fee not found
            }
            
            // Validate amount doesn't exceed fee amount
            if ($amount > $feeRecord->amount) {
                $feeName = $feeRecord->feeType->name ?? 'Fee';
                return redirect()->back()
                    ->withErrors(['fee_amounts' => 'Payment amount cannot exceed the fee amount for ' . $feeName])
                    ->withInput();
            }
            
            // Create payment record - use appropriate column based on fee type
            $paymentData = [
                'student_id' => $validated['student_id'],
                'results_status_id' => $validated['results_status_id'],
                'amount_paid' => $amount,
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'reference_number' => $validated['reference_number'],
                'notes' => $validated['notes'],
            ];
            
            if ($isFeeStructure) {
                $paymentData['fee_structure_id'] = $feeId;
            } else {
                $paymentData['term_fee_id'] = $feeId;
            }
            
            \App\StudentPayment::create($paymentData);

            $totalPaid += $amount;
            $feeName = $feeRecord->feeType->name ?? 'Fee';
            $feesPaidFor[] = $feeName . ' ($' . number_format($amount, 2) . ')';
        }

        // Calculate student's new balance for success message
        $student = Student::find($validated['student_id']);
        $currentTerm = \App\ResultsStatus::find($validated['results_status_id']);
        $totalFees = $currentTerm ? $currentTerm->total_fees : 0;
        $totalAmountPaid = \App\StudentPayment::where('student_id', $validated['student_id'])->sum('amount_paid');
        $remainingBalance = $totalFees - $totalAmountPaid;

        // Change student status from "New Student" to "Existing Student" after first payment
        if ($student && $student->is_new_student && $totalPaid > 0) {
            $student->is_new_student = false;
            $student->save();
        }

        // Auto-create School Income and CashBookEntry if payment was made
        if ($totalPaid > 0) {
            $studentName = $student->user->name ?? ($student->name . ' ' . $student->surname);
            $feeDescription = implode(', ', $feesPaidFor);
            $termName = $currentTerm ? ucfirst($currentTerm->result_period) . ' ' . $currentTerm->year : '';
            
            // Create School Income record
            SchoolIncome::create([
                'date' => $validated['payment_date'],
                'category' => 'School Fees',
                'description' => 'Fees Paid For: ' . $studentName . ' - ' . $termName . ' | ' . $feeDescription,
                'amount' => $totalPaid,
                'payment_method' => $validated['payment_method'],
                'reference_number' => $validated['reference_number'],
            ]);

            // Auto-create CashBookEntry
            $lastEntry = CashBookEntry::orderBy('id', 'desc')->first();
            $currentBalance = $lastEntry ? $lastEntry->balance : 0;
            $newBalance = $currentBalance + $totalPaid;

            $cashEntry = CashBookEntry::create([
                'entry_date' => $validated['payment_date'],
                'reference_number' => CashBookEntry::generateReferenceNumber(),
                'transaction_type' => 'receipt',
                'category' => 'school_fees',
                'description' => '[Fees Paid For] ' . $studentName . ' - ' . $feeDescription,
                'amount' => $totalPaid,
                'balance' => $newBalance,
                'payment_method' => $validated['payment_method'],
                'payer_payee' => $studentName,
                'created_by' => auth()->id(),
                'notes' => 'Auto-generated from Student Payment - ' . $studentName . ' | Term: ' . $termName,
            ]);
            $cashEntry->postToLedger();
        }

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            // Get the last payment created for receipt data
            $lastPayment = \App\StudentPayment::where('student_id', $validated['student_id'])
                ->orderBy('id', 'desc')
                ->first();
            
            return response()->json([
                'success' => true,
                'message' => 'Payment of $' . number_format($totalPaid, 2) . ' recorded successfully!',
                'remaining_balance' => $remainingBalance,
                'total_paid' => $totalPaid,
                'receipt' => [
                    'id' => $lastPayment->id,
                    'student_name' => $student->user->name ?? ($student->name . ' ' . $student->surname),
                    'amount' => $totalPaid,
                    'date' => $validated['payment_date'],
                    'method' => $validated['payment_method'],
                    'reference' => $validated['reference_number'],
                    'term' => $currentTerm ? ucfirst($currentTerm->result_period) . ' ' . $currentTerm->year : '',
                    'fees' => implode(', ', $feesPaidFor),
                ]
            ]);
        }

        return redirect()->route('finance.student-payments')
            ->with('success', 'Payment of $' . number_format($totalPaid, 2) . ' recorded successfully! Remaining balance: $' . number_format($remainingBalance, 2));
    }

    public function getStudentFeePayments(Request $request)
    {
        $studentId = $request->input('student_id');
        $termId = $request->input('results_status_id');
        
        if (!$studentId || !$termId) {
            return response()->json([]);
        }
        
        // Get all payments for this student and term
        $payments = \App\StudentPayment::where('student_id', $studentId)
            ->where('results_status_id', $termId)
            ->get();
        
        // Group payments by fee (either fee_structure_id or term_fee_id)
        $feePayments = [];
        
        foreach ($payments as $payment) {
            $feeId = $payment->fee_structure_id ?? $payment->term_fee_id;
            if ($feeId) {
                if (!isset($feePayments[$feeId])) {
                    $feePayments[$feeId] = 0;
                }
                $feePayments[$feeId] += floatval($payment->amount_paid);
            }
        }
        
        return response()->json($feePayments);
    }

    public function paynowPayment(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'results_status_id' => 'required|exists:results_statuses,id',
            'fee_amounts' => 'required|array',
            'fee_amounts.*' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'paynow_phone' => 'required|string',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            // Get Paynow credentials
            $paynowId = \App\PaynowSetting::getPaynowId();
            $paynowKey = \App\PaynowSetting::getPaynowKey();
            
            if (!$paynowId || !$paynowKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'Paynow is not configured. Please contact administrator.',
                ]);
            }

            // Calculate total amount
            $totalPaid = 0;
            $feesPaidFor = [];

            foreach ($validated['fee_amounts'] as $feeId => $amount) {
                if ($amount <= 0) continue;
                
                $feeRecord = FeeStructure::find($feeId);
                if (!$feeRecord) {
                    $feeRecord = \App\TermFee::find($feeId);
                }
                
                if (!$feeRecord) continue;
                
                if ($amount > $feeRecord->amount) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Payment amount cannot exceed the fee amount',
                    ]);
                }
                
                $totalPaid += $amount;
                $feeName = $feeRecord->feeType->name ?? 'Fee';
                $feesPaidFor[] = $feeName . ' ($' . number_format($amount, 2) . ')';
            }

            if ($totalPaid <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid fees selected for payment',
                ]);
            }

            // Get student info
            $student = Student::find($validated['student_id']);
            $studentName = $student->user->name ?? ($student->name . ' ' . $student->surname);
            $currentTerm = \App\ResultsStatus::find($validated['results_status_id']);
            $termName = $currentTerm ? ucfirst($currentTerm->result_period) . ' ' . $currentTerm->year : '';

            // Initialize Paynow
            $paynow = new \Paynow\Payments\Paynow(
                $paynowId,
                $paynowKey,
                \App\PaynowSetting::getReturnUrl() ?: route('finance.student-payments'),
                \App\PaynowSetting::getResultUrl() ?: route('finance.student-payments')
            );

            // Create payment
            $payment = $paynow->createPayment(
                'PAYMENT-' . time() . '-' . $student->id,
                $validated['paynow_phone']
            );

            // Add items
            $payment->add('School Fees - ' . $studentName . ' - ' . $termName, $totalPaid);

            // Send payment
            $response = $paynow->sendMobile($payment, $validated['paynow_phone'], 'ecocash');

            // Log Paynow initiation
            AuditTrail::create([
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name,
                'user_role' => auth()->user()->roles->first()->name ?? 'Admin',
                'action' => 'create',
                'description' => "Initiated Paynow payment for $studentName - Amount: $$totalPaid",
                'old_values' => json_encode([
                    'payment_method' => 'paynow',
                    'phone' => $validated['paynow_phone'],
                    'status' => $response->success() ? 'initiated' : 'failed'
                ]),
                'new_values' => json_encode([
                    'student_id' => $validated['student_id'],
                    'amount' => $totalPaid,
                    'fees' => $feesPaidFor,
                    'poll_url' => $response->success() ? $response->pollUrl() : null
                ]),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Check if request was successful
            if ($response->success()) {
                // Poll for payment status
                $status = $paynow->pollTransaction($response->pollUrl());

                if ($status->paid()) {
                    // Payment successful - record it
                    DB::beginTransaction();
                    
                    try {
                        foreach ($validated['fee_amounts'] as $feeId => $amount) {
                            if ($amount <= 0) continue;
                            
                            $feeRecord = FeeStructure::find($feeId);
                            $isFeeStructure = true;
                            
                            if (!$feeRecord) {
                                $feeRecord = \App\TermFee::find($feeId);
                                $isFeeStructure = false;
                            }
                            
                            if (!$feeRecord) continue;
                            
                            $paymentData = [
                                'student_id' => $validated['student_id'],
                                'results_status_id' => $validated['results_status_id'],
                                'amount_paid' => $amount,
                                'payment_date' => $validated['payment_date'],
                                'payment_method' => 'paynow',
                                'reference_number' => $response->reference() ?? 'PAYNOW-' . time(),
                                'notes' => 'Paynow Payment - Phone: ' . $validated['paynow_phone'] . ($validated['notes'] ? ' | ' . $validated['notes'] : ''),
                            ];
                            
                            if ($isFeeStructure) {
                                $paymentData['fee_structure_id'] = $feeId;
                            } else {
                                $paymentData['term_fee_id'] = $feeId;
                            }
                            
                            \App\StudentPayment::create($paymentData);
                        }

                        // Change student status from new to existing
                        if ($student && $student->is_new_student) {
                            $student->is_new_student = false;
                            $student->save();
                        }

                        // Calculate balances
                        $totalFees = $currentTerm ? $currentTerm->total_fees : 0;
                        $totalAmountPaid = \App\StudentPayment::where('student_id', $validated['student_id'])->sum('amount_paid');
                        $remainingBalance = $totalFees - $totalAmountPaid;

                        // Create School Income record
                        SchoolIncome::create([
                            'date' => $validated['payment_date'],
                            'category' => 'School Fees',
                            'description' => 'Paynow Payment - ' . $studentName . ' - ' . $termName . ' | ' . implode(', ', $feesPaidFor),
                            'amount' => $totalPaid,
                            'payment_method' => 'paynow',
                            'reference_number' => $response->reference(),
                        ]);

                        // Auto-create CashBookEntry
                        $lastEntry = CashBookEntry::orderBy('id', 'desc')->first();
                        $currentBalance = $lastEntry ? $lastEntry->balance : 0;
                        $newBalance = $currentBalance + $totalPaid;

                        $cashEntry = CashBookEntry::create([
                            'entry_date' => $validated['payment_date'],
                            'reference_number' => CashBookEntry::generateReferenceNumber(),
                            'transaction_type' => 'receipt',
                            'category' => 'school_fees',
                            'description' => '[Paynow Payment] ' . $studentName . ' - ' . implode(', ', $feesPaidFor),
                            'amount' => $totalPaid,
                            'balance' => $newBalance,
                            'payment_method' => 'paynow',
                            'payer_payee' => $studentName,
                            'created_by' => auth()->id(),
                            'notes' => 'Auto-generated from Paynow Payment - Phone: ' . $validated['paynow_phone'] . ' | Term: ' . $termName,
                        ]);
                        $cashEntry->postToLedger();

                        DB::commit();

                        // Log successful payment
                        AuditTrail::create([
                            'user_id' => auth()->id(),
                            'user_name' => auth()->user()->name,
                            'user_role' => auth()->user()->roles->first()->name ?? 'Admin',
                            'action' => 'create',
                            'description' => "Paynow payment successful for $studentName - Amount: $$totalPaid",
                            'old_values' => json_encode(['payment_method' => 'paynow', 'status' => 'paid']),
                            'new_values' => json_encode([
                                'student_id' => $validated['student_id'],
                                'amount' => $totalPaid,
                                'reference' => $response->reference(),
                                'remaining_balance' => $remainingBalance
                            ]),
                            'ip_address' => $request->ip(),
                            'user_agent' => $request->userAgent(),
                        ]);

                        // Get last payment for receipt
                        $lastPayment = \App\StudentPayment::where('student_id', $validated['student_id'])
                            ->orderBy('id', 'desc')
                            ->first();

                        return response()->json([
                            'success' => true,
                            'message' => 'Paynow payment of $' . number_format($totalPaid, 2) . ' completed successfully!',
                            'remaining_balance' => $remainingBalance,
                            'total_paid' => $totalPaid,
                            'receipt' => [
                                'id' => $lastPayment->id,
                                'student_name' => $studentName,
                                'amount' => $totalPaid,
                                'date' => $validated['payment_date'],
                                'method' => 'paynow',
                                'reference' => $response->reference(),
                                'term' => $termName,
                                'fees' => implode(', ', $feesPaidFor),
                            ]
                        ]);

                    } catch (\Exception $e) {
                        DB::rollBack();
                        
                        // Log failed payment
                        AuditTrail::create([
                            'user_id' => auth()->id(),
                            'user_name' => auth()->user()->name,
                            'user_role' => auth()->user()->roles->first()->name ?? 'Admin',
                            'action' => 'error',
                            'description' => "Paynow payment failed for $studentName - Error: " . $e->getMessage(),
                            'old_values' => json_encode(['payment_method' => 'paynow', 'status' => 'error']),
                            'new_values' => json_encode(['error' => $e->getMessage()]),
                            'ip_address' => $request->ip(),
                            'user_agent' => $request->userAgent(),
                        ]);

                        return response()->json([
                            'success' => false,
                            'message' => 'Failed to record payment: ' . $e->getMessage(),
                        ]);
                    }
                } else {
                    // Payment not completed
                    AuditTrail::create([
                        'user_id' => auth()->id(),
                        'user_name' => auth()->user()->name,
                        'user_role' => auth()->user()->roles->first()->name ?? 'Admin',
                        'action' => 'update',
                        'description' => "Paynow payment pending/cancelled for $studentName",
                        'old_values' => json_encode(['payment_method' => 'paynow', 'status' => 'pending']),
                        'new_values' => json_encode(['poll_url' => $response->pollUrl()]),
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Payment not completed. Please check your phone and approve the payment.',
                    ]);
                }
            } else {
                // Paynow request failed
                AuditTrail::create([
                    'user_id' => auth()->id(),
                    'user_name' => auth()->user()->name,
                    'user_role' => auth()->user()->roles->first()->name ?? 'Admin',
                    'action' => 'error',
                    'description' => "Paynow request failed for $studentName",
                    'old_values' => json_encode(['payment_method' => 'paynow', 'status' => 'failed']),
                    'new_values' => json_encode(['errors' => $response->errors()]),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Paynow request failed: ' . implode(', ', $response->errors()),
                ]);
            }

        } catch (\Exception $e) {
            // Log exception
            AuditTrail::create([
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name,
                'user_role' => auth()->user()->roles->first()->name ?? 'Admin',
                'action' => 'error',
                'description' => 'Paynow payment exception: ' . $e->getMessage(),
                'old_values' => json_encode(['payment_method' => 'paynow']),
                'new_values' => json_encode(['exception' => $e->getMessage()]),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ]);
        }
    }

    public function parentsArrears(Request $request)
    {
        // Get all terms for cumulative calculation (ordered by date)
        $allTerms = \App\ResultsStatus::with(['termFees.feeType', 'feeStructures.feeType', 'feeStructures.feeLevelGroup'])
            ->orderBy('year', 'asc')
            ->orderBy('result_period', 'asc')
            ->get();
        
        // Get current term
        $currentTerm = \App\ResultsStatus::with(['termFees.feeType', 'feeStructures.feeType', 'feeStructures.feeLevelGroup'])
            ->orderBy('year', 'desc')
            ->orderBy('result_period', 'desc')
            ->first();
        
        // Get classes for filter
        $classes = \App\Grade::orderBy('class_name')->get();
        
        // STUDENT-CENTRIC APPROACH: Get all students with their arrears
        $studentsQuery = \App\Student::with(['user', 'class', 'parents.user', 'payments.termFee.feeType', 'payments.resultsStatus']);
        
        // Apply filters
        if ($request->has('class_id') && $request->class_id != '') {
            $studentsQuery->where('class_id', $request->class_id);
        }
        if ($request->has('student_type') && $request->student_type != '') {
            $studentsQuery->where('student_type', $request->student_type);
        }
        
        $allStudents = $studentsQuery->get();
        
        // Calculate arrears for each student
        $studentsWithArrears = $allStudents->map(function($student) use ($allTerms, $currentTerm) {
            $studentType = $student->student_type ?? 'day';
            $curriculumType = $student->curriculum_type ?? 'zimsec';
            $scholarshipPercentage = floatval($student->scholarship_percentage ?? 0);
            $studentCreatedAt = $student->created_at;
            
            $totalFees = 0;
            $balanceBf = 0;
            $currentTermFees = 0;
            $arrearsBreakdown = [];
            
            foreach ($allTerms as $term) {
                // Skip terms before student was enrolled
                if ($studentCreatedAt && $term->created_at < $studentCreatedAt) {
                    continue;
                }
                
                // Get base fee - first try fee structures, then fallback to legacy fields
                $baseFee = $this->getStudentTermFee($student, $term);
                
                // Apply scholarship discount
                if ($scholarshipPercentage > 0 && $scholarshipPercentage <= 100) {
                    $baseFee = $baseFee - ($baseFee * ($scholarshipPercentage / 100));
                }
                
                $totalFees += $baseFee;
                
                // Track current term vs previous terms
                if ($currentTerm && $term->id === $currentTerm->id) {
                    $currentTermFees += $baseFee;
                } else if (!$currentTerm || $term->id < $currentTerm->id) {
                    $balanceBf += $baseFee;
                }
                
                // Calculate term-specific arrears
                $termPaid = floatval(\App\StudentPayment::where('student_id', $student->id)
                    ->where('results_status_id', $term->id)
                    ->sum('amount_paid'));
                $termArrears = $baseFee - $termPaid;
                
                if ($termArrears > 0) {
                    $arrearsBreakdown[] = [
                        'term' => ucfirst($term->result_period) . ' ' . $term->year,
                        'term_id' => $term->id,
                        'fees' => $baseFee,
                        'paid' => $termPaid,
                        'arrears' => $termArrears
                    ];
                }
            }
            
            // Calculate total paid
            $totalPaid = floatval(\App\StudentPayment::where('student_id', $student->id)->sum('amount_paid'));
            $arrears = $totalFees - $totalPaid;
            
            $student->balance_bf = $balanceBf;
            $student->current_term_fees = $currentTermFees;
            $student->total_fees = $totalFees;
            $student->total_paid = $totalPaid;
            $student->arrears = $arrears;
            $student->arrears_breakdown = $arrearsBreakdown;
            $student->parent_name = $student->parents->first()->user->name ?? 'No Parent';
            $student->parent_phone = $student->parents->first()->phone ?? ($student->parents->first()->user->phone ?? '-');
            
            return $student;
        })->sortByDesc('arrears'); // Show ALL students - no filtering
        
        // Also keep the old parent-based approach for backwards compatibility
        $parentsWithArrears = Parents::with(['user', 'students.user', 'students.class', 'students.payments.termFee.feeType', 'students.payments.resultsStatus'])
            ->get()
            ->map(function($parent) use ($allTerms, $currentTerm, $request) {
                // Filter students by class if provided
                $students = $parent->students;
                if ($request->has('class_id') && $request->class_id != '') {
                    $students = $students->filter(function($student) use ($request) {
                        return $student->class_id == $request->class_id;
                    });
                }
                
                // Filter students by type if provided
                if ($request->has('student_type') && $request->student_type != '') {
                    $students = $students->filter(function($student) use ($request) {
                        return ($student->student_type ?? 'day') == $request->student_type;
                    });
                }
                
                // Calculate cumulative fees from ALL terms based on student type, curriculum, and scholarship
                $totalFees = 0;
                $balanceBf = 0;
                $currentTermFees = 0;
                
                foreach ($students as $student) {
                    $studentType = $student->student_type ?? 'day';
                    $curriculumType = $student->curriculum_type ?? 'zimsec';
                    $scholarshipPercentage = floatval($student->scholarship_percentage ?? 0);
                    $studentCreatedAt = $student->created_at;
                    
                    foreach ($allTerms as $term) {
                        // Skip terms before student was enrolled
                        if ($studentCreatedAt && $term->created_at < $studentCreatedAt) {
                            continue;
                        }
                        
                        // Get base fee based on curriculum and student type
                        $baseFee = 0;
                        if ($curriculumType === 'cambridge') {
                            $baseFee = $studentType === 'boarding' 
                                ? floatval($term->cambridge_boarding_fees ?? 0) 
                                : floatval($term->cambridge_day_fees ?? 0);
                        } else {
                            $baseFee = $studentType === 'boarding' 
                                ? floatval($term->zimsec_boarding_fees ?? $term->total_boarding_fees ?? $term->total_fees) 
                                : floatval($term->zimsec_day_fees ?? $term->total_day_fees ?? $term->total_fees);
                        }
                        
                        // Apply scholarship discount
                        if ($scholarshipPercentage > 0 && $scholarshipPercentage <= 100) {
                            $baseFee = $baseFee - ($baseFee * ($scholarshipPercentage / 100));
                        }
                        
                        $totalFees += $baseFee;
                        
                        // Track current term vs previous terms
                        if ($currentTerm && $term->id === $currentTerm->id) {
                            $currentTermFees += $baseFee;
                        } else if (!$currentTerm || $term->id < $currentTerm->id) {
                            $balanceBf += $baseFee;
                        }
                    }
                }
                
                // Calculate total paid across all terms
                $totalPaid = 0;
                foreach ($students as $student) {
                    $totalPaid += floatval(\App\StudentPayment::where('student_id', $student->id)->sum('amount_paid'));
                }
                
                $arrears = $totalFees - $totalPaid;
                
                $parent->filtered_students = $students;
                $parent->balance_bf = $balanceBf;
                $parent->current_term_fees = $currentTermFees;
                $parent->total_fees = $totalFees;
                $parent->total_paid = $totalPaid;
                $parent->arrears = $arrears;
                
                // Get arrears breakdown by term for each student
                $arrearsBreakdown = [];
                foreach ($students as $student) {
                    $studentType = $student->student_type ?? 'day';
                    $curriculumType = $student->curriculum_type ?? 'zimsec';
                    $scholarshipPercentage = floatval($student->scholarship_percentage ?? 0);
                    $studentArrears = [];
                    
                    foreach ($allTerms as $term) {
                        // Get fees based on curriculum and student type
                        $termFees = 0;
                        if ($curriculumType === 'cambridge') {
                            $termFees = $studentType === 'boarding' 
                                ? floatval($term->cambridge_boarding_fees ?? 0) 
                                : floatval($term->cambridge_day_fees ?? 0);
                        } else {
                            $termFees = $studentType === 'boarding' 
                                ? floatval($term->zimsec_boarding_fees ?? $term->total_boarding_fees ?? $term->total_fees) 
                                : floatval($term->zimsec_day_fees ?? $term->total_day_fees ?? $term->total_fees);
                        }
                        
                        // Apply scholarship discount
                        if ($scholarshipPercentage > 0 && $scholarshipPercentage <= 100) {
                            $termFees = $termFees - ($termFees * ($scholarshipPercentage / 100));
                        }
                        
                        $termPaid = floatval(\App\StudentPayment::where('student_id', $student->id)
                            ->where('results_status_id', $term->id)
                            ->sum('amount_paid'));
                        $termArrears = $termFees - $termPaid;
                        
                        if ($termArrears > 0) {
                            $studentArrears[] = [
                                'term' => ucfirst($term->result_period) . ' ' . $term->year,
                                'term_id' => $term->id,
                                'fees' => $termFees,
                                'paid' => $termPaid,
                                'arrears' => $termArrears
                            ];
                        }
                    }
                    if (!empty($studentArrears)) {
                        $arrearsBreakdown[$student->id] = [
                            'student_name' => $student->user->name ?? $student->name,
                            'class' => $student->class->class_name ?? 'N/A',
                            'student_type' => ucfirst($studentType),
                            'curriculum_type' => strtoupper($curriculumType),
                            'scholarship' => $scholarshipPercentage . '%',
                            'terms' => $studentArrears,
                            'total_arrears' => array_sum(array_column($studentArrears, 'arrears'))
                        ];
                    }
                }
                $parent->arrears_breakdown = $arrearsBreakdown;
                
                return $parent;
            })
            ->filter(function($parent) {
                return $parent->arrears > 0 && $parent->filtered_students->count() > 0;
            })
            ->sortByDesc('arrears');
        
        // Get students without parents (check both pivot table and direct parent_id)
        $studentsInPivot = \DB::table('student_parent')->pluck('student_id')->toArray();
        $orphanStudentsQuery = \App\Student::with(['user', 'class', 'payments.termFee.feeType', 'payments.resultsStatus'])
            ->whereNotIn('id', $studentsInPivot)
            ->whereNull('parent_id');
        
        // Apply filters to orphan students
        if ($request->has('class_id') && $request->class_id != '') {
            $orphanStudentsQuery->where('class_id', $request->class_id);
        }
        if ($request->has('student_type') && $request->student_type != '') {
            $orphanStudentsQuery->where('student_type', $request->student_type);
        }
        
        $orphanStudents = $orphanStudentsQuery->get();
        
        // Create a virtual "No Parent" entry for orphan students with arrears
        $orphanArrearsBreakdown = [];
        $orphanTotalFees = 0;
        $orphanTotalPaid = 0;
        
        foreach ($orphanStudents as $student) {
            $studentType = $student->student_type ?? 'day';
            $curriculumType = $student->curriculum_type ?? 'zimsec';
            $scholarshipPercentage = floatval($student->scholarship_percentage ?? 0);
            $studentFees = 0;
            $studentArrears = [];
            
            foreach ($allTerms as $term) {
                $termFees = 0;
                if ($curriculumType === 'cambridge') {
                    $termFees = $studentType === 'boarding' 
                        ? floatval($term->cambridge_boarding_fees ?? 0) 
                        : floatval($term->cambridge_day_fees ?? 0);
                } else {
                    $termFees = $studentType === 'boarding' 
                        ? floatval($term->zimsec_boarding_fees ?? $term->total_boarding_fees ?? $term->total_fees) 
                        : floatval($term->zimsec_day_fees ?? $term->total_day_fees ?? $term->total_fees);
                }
                
                if ($scholarshipPercentage > 0 && $scholarshipPercentage <= 100) {
                    $termFees = $termFees - ($termFees * ($scholarshipPercentage / 100));
                }
                
                $studentFees += $termFees;
                
                $termPaid = floatval(\App\StudentPayment::where('student_id', $student->id)
                    ->where('results_status_id', $term->id)
                    ->sum('amount_paid'));
                $termArrears = $termFees - $termPaid;
                
                if ($termArrears > 0) {
                    $studentArrears[] = [
                        'term' => ucfirst($term->result_period) . ' ' . $term->year,
                        'term_id' => $term->id,
                        'fees' => $termFees,
                        'paid' => $termPaid,
                        'arrears' => $termArrears
                    ];
                }
            }
            
            $studentPaid = floatval(\App\StudentPayment::where('student_id', $student->id)->sum('amount_paid'));
            $orphanTotalFees += $studentFees;
            $orphanTotalPaid += $studentPaid;
            
            if (!empty($studentArrears)) {
                $orphanArrearsBreakdown[$student->id] = [
                    'student_name' => $student->user->name ?? $student->name,
                    'class' => $student->class->class_name ?? 'N/A',
                    'student_type' => ucfirst($studentType),
                    'curriculum_type' => strtoupper($curriculumType),
                    'scholarship' => $scholarshipPercentage . '%',
                    'terms' => $studentArrears,
                    'total_arrears' => array_sum(array_column($studentArrears, 'arrears'))
                ];
            }
        }
        
        $orphanArrears = $orphanTotalFees - $orphanTotalPaid;
        
        // Create virtual parent object for orphan students if there are any with arrears
        $orphanParent = null;
        if ($orphanArrears > 0 && !empty($orphanArrearsBreakdown)) {
            $orphanParent = new \stdClass();
            $orphanParent->id = 0;
            $orphanParent->user = new \stdClass();
            $orphanParent->user->name = 'No Parent Assigned';
            $orphanParent->user->email = '-';
            $orphanParent->phone = '-';
            $orphanParent->filtered_students = $orphanStudents;
            $orphanParent->total_fees = $orphanTotalFees;
            $orphanParent->total_paid = $orphanTotalPaid;
            $orphanParent->arrears = $orphanArrears;
            $orphanParent->arrears_breakdown = $orphanArrearsBreakdown;
            $orphanParent->is_orphan = true;
        }
        
        return view('backend.finance.parents-arrears', compact('parentsWithArrears', 'classes', 'orphanParent', 'studentsWithArrears'));
    }
    
    /**
     * Get the fee for a student for a specific term based on their class, curriculum, and student type
     */
    private function getStudentTermFee($student, $term)
    {
        $studentType = $student->student_type ?? 'day';
        $curriculumType = $student->curriculum_type ?? 'zimsec';
        $classNumeric = $student->class->class_numeric ?? 1;
        $isNewStudent = $student->is_new_student ?? false;
        
        // First try to get fee from fee structures (new system)
        if ($term->feeStructures && $term->feeStructures->count() > 0) {
            // Find the appropriate fee level group for this student's class
            $applicableFees = $term->feeStructures->filter(function($fs) use ($classNumeric, $curriculumType, $studentType, $isNewStudent) {
                if (!$fs->feeLevelGroup) return false;
                
                $inRange = $classNumeric >= $fs->feeLevelGroup->min_class_numeric && 
                           $classNumeric <= $fs->feeLevelGroup->max_class_numeric;
                $matchesCurriculum = $fs->curriculum_type === $curriculumType;
                $matchesType = $fs->student_type === $studentType;
                $matchesNewStatus = $fs->is_for_new_student == $isNewStudent;
                
                return $inRange && $matchesCurriculum && $matchesType && $matchesNewStatus;
            });
            
            if ($applicableFees->count() > 0) {
                return $applicableFees->sum('amount');
            }
        }
        
        // Fallback to legacy fee fields
        if ($curriculumType === 'cambridge') {
            return $studentType === 'boarding' 
                ? floatval($term->cambridge_boarding_fees ?? 0) 
                : floatval($term->cambridge_day_fees ?? 0);
        } else {
            return $studentType === 'boarding' 
                ? floatval($term->zimsec_boarding_fees ?? $term->total_boarding_fees ?? $term->total_fees ?? 0) 
                : floatval($term->zimsec_day_fees ?? $term->total_day_fees ?? $term->total_fees ?? 0);
        }
    }

    public function exportParentsArrears(Request $request)
    {
        $allTerms = \App\ResultsStatus::with(['termFees.feeType', 'feeStructures.feeType', 'feeStructures.feeLevelGroup'])
            ->orderBy('year', 'asc')
            ->orderBy('result_period', 'asc')
            ->get();
        
        // Get all students with their data
        $studentsQuery = \App\Student::with(['user', 'class', 'parents.user']);
        
        // Apply filters
        if ($request->has('class_id') && $request->class_id != '') {
            $studentsQuery->where('class_id', $request->class_id);
        }
        if ($request->has('student_type') && $request->student_type != '') {
            $studentsQuery->where('student_type', $request->student_type);
        }
        
        $students = $studentsQuery->get()->map(function($student) use ($allTerms) {
            $studentType = $student->student_type ?? 'day';
            $curriculumType = $student->curriculum_type ?? 'zimsec';
            $scholarshipPercentage = floatval($student->scholarship_percentage ?? 0);
            
            $totalFees = 0;
            foreach ($allTerms as $term) {
                $baseFee = $this->getStudentTermFee($student, $term);
                if ($scholarshipPercentage > 0 && $scholarshipPercentage <= 100) {
                    $baseFee = $baseFee - ($baseFee * ($scholarshipPercentage / 100));
                }
                $totalFees += $baseFee;
            }
            
            $totalPaid = floatval(\App\StudentPayment::where('student_id', $student->id)->sum('amount_paid'));
            $arrears = $totalFees - $totalPaid;
            
            $student->total_fees = $totalFees;
            $student->total_paid = $totalPaid;
            $student->arrears = $arrears;
            $student->parent_name = $student->parents->first()->user->name ?? 'No Parent';
            
            return $student;
        })->sortByDesc('arrears');

        $filename = 'student_arrears_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($students) {
            $file = fopen('php://output', 'w');
            
            // CSV Header
            fputcsv($file, ['#', 'Student Name', 'Class', 'Type', 'Curriculum', 'Parent', 'Total Fees', 'Amount Paid', 'Arrears']);
            
            $index = 1;
            foreach ($students as $student) {
                fputcsv($file, [
                    $index++,
                    $student->user->name ?? $student->name ?? 'N/A',
                    $student->class->class_name ?? 'N/A',
                    ucfirst($student->student_type ?? 'day'),
                    strtoupper($student->curriculum_type ?? 'zimsec'),
                    $student->parent_name,
                    number_format($student->total_fees, 2),
                    number_format($student->total_paid, 2),
                    number_format($student->arrears, 2)
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportStudentPayments(Request $request)
    {
        $currentTerm = \App\ResultsStatus::with(['termFees.feeType', 'feeStructures.feeType', 'feeStructures.feeLevelGroup'])
            ->orderBy('year', 'desc')
            ->orderBy('result_period', 'desc')
            ->first();
        
        // Get all terms for cumulative calculation
        $allTermsForCalc = \App\ResultsStatus::with(['termFees.feeType', 'feeStructures.feeType', 'feeStructures.feeLevelGroup'])
            ->orderBy('year', 'asc')
            ->orderBy('result_period', 'asc')
            ->get();
        
        $query = Student::with(['user', 'class', 'parent.user', 'payments.termFee.feeType']);
        
        if ($request->has('class_id') && $request->class_id != '') {
            $query->where('class_id', $request->class_id);
        }
        
        if ($request->has('student_type') && $request->student_type != '') {
            $query->where('student_type', $request->student_type);
        }
        
        $students = $query->orderBy('created_at', 'desc')->get();
        
        foreach ($students as $student) {
            $feeBreakdown = $this->calculateCumulativeFees($student, $allTermsForCalc, $currentTerm);
            $student->balance_bf = $feeBreakdown['balance_bf'];
            $student->current_term_fees = $feeBreakdown['current_term_fees'];
            $student->total_fees = $feeBreakdown['total_fees'];
            $student->amount_paid = floatval(\App\StudentPayment::where('student_id', $student->id)->sum('amount_paid'));
            $student->balance = $student->total_fees - $student->amount_paid;
            
            if ($student->balance == 0 && $student->total_fees > 0) {
                $student->status = 'Fully Paid';
            } elseif ($student->amount_paid > 0 && $student->balance > 0) {
                $student->status = 'Partially Paid';
            } else {
                $student->status = 'Unpaid';
            }
        }
        
        if ($request->has('status') && $request->status != '') {
            $status = $request->status;
            $students = $students->filter(function($student) use ($status) {
                if ($status === 'paid') return $student->status === 'Fully Paid';
                if ($status === 'partial') return $student->status === 'Partially Paid';
                if ($status === 'unpaid') return $student->status === 'Unpaid';
                return true;
            });
        }

        $filename = 'student_payments_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($students) {
            $file = fopen('php://output', 'w');
            
            // CSV Header
            fputcsv($file, ['#', 'Roll Number', 'Student Name', 'Class', 'Type', 'Parent', 'Balance B/F', 'Current Term Fees', 'Total Fees', 'Amount Paid', 'Balance', 'Status']);
            
            $index = 1;
            foreach ($students as $student) {
                fputcsv($file, [
                    $index++,
                    $student->roll_number,
                    $student->user->name ?? $student->name,
                    $student->class->class_name ?? 'N/A',
                    ucfirst($student->student_type ?? 'day'),
                    $student->parent->user->name ?? 'N/A',
                    number_format($student->balance_bf, 2),
                    number_format($student->current_term_fees, 2),
                    number_format($student->total_fees, 2),
                    number_format($student->amount_paid, 2),
                    number_format($student->balance, 2),
                    $student->status
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function schoolIncome(Request $request)
    {
        // Get current active term from ResultsStatus
        $currentTerm = \App\ResultsStatus::orderBy('year', 'desc')
            ->orderBy('result_period', 'desc')
            ->first();
        
        // Get available years and terms for filter
        $years = range(date('Y'), date('Y') - 5);
        $terms = ['first' => 'First Term', 'second' => 'Second Term', 'third' => 'Third Term'];
        
        // Get all terms from database for filter
        $allTerms = \App\ResultsStatus::orderBy('year', 'desc')
            ->orderBy('result_period', 'desc')
            ->get();
        
        // Default to current term if no filter specified
        $selectedYear = $request->has('year') ? $request->year : ($currentTerm ? $currentTerm->year : null);
        $selectedTerm = $request->has('term') ? $request->term : ($currentTerm ? $currentTerm->result_period : null);
        
        // Get manual income records - filter by term/year fields
        $manualQuery = SchoolIncome::orderBy('date', 'desc');
        if ($selectedYear) {
            $manualQuery->where('year', $selectedYear);
        }
        if ($selectedTerm) {
            $manualQuery->where('term', $selectedTerm);
        }
        $manualIncomes = $manualQuery->get()->map(function($income) {
            return [
                'id' => $income->id,
                'date' => $income->date,
                'description' => $income->description,
                'category' => $income->category ?? 'General',
                'amount' => $income->amount,
                'source' => 'manual',
                'deletable' => true
            ];
        });

        // Get student payments as income (student payments don't have term/year fields yet, show all)
        $studentPayments = StudentPayment::with('student')->orderBy('payment_date', 'desc')
            ->get()->map(function($payment) {
                $studentName = $payment->student ? $payment->student->name . ' ' . $payment->student->surname : 'Unknown Student';
                return [
                    'id' => 'sp_' . $payment->id,
                    'date' => $payment->payment_date,
                    'description' => 'Student Payment - ' . $studentName,
                    'category' => 'Student Fees',
                    'amount' => $payment->amount_paid,
                    'source' => 'student_payment',
                    'deletable' => false
                ];
            });

        // Get product sales as income - filter by term/year fields
        $productQuery = Product::where('quantity_sold', '>', 0);
        if ($selectedYear) {
            $productQuery->where('year', $selectedYear);
        }
        if ($selectedTerm) {
            $productQuery->where('term', $selectedTerm);
        }
        $productSales = $productQuery->get()->map(function($product) {
                return [
                    'id' => 'prod_' . $product->id,
                    'date' => $product->updated_at,
                    'description' => 'Product Sale - ' . $product->name . ' (' . $product->quantity_sold . ' units)',
                    'category' => 'Products Sold',
                    'amount' => $product->price * $product->quantity_sold,
                    'source' => 'product',
                    'deletable' => false
                ];
            });

        // Combine all income sources
        $allIncomes = $manualIncomes->concat($studentPayments)->concat($productSales);
        
        // Sort by date descending
        $allIncomes = $allIncomes->sortByDesc('date')->values();
        
        // Calculate totals (filtered)
        $totalManualIncome = $manualIncomes->sum('amount');
        $totalStudentPayments = $studentPayments->sum('amount');
        $totalProductSales = $productSales->sum('amount');
        $totalIncome = $totalManualIncome + $totalStudentPayments + $totalProductSales;

        // Paginate the combined collection
        $page = request()->get('page', 1);
        $perPage = 20;
        $incomes = new \Illuminate\Pagination\LengthAwarePaginator(
            $allIncomes->forPage($page, $perPage),
            $allIncomes->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
        
        return view('backend.finance.school-income', compact('incomes', 'totalIncome', 'totalManualIncome', 'totalStudentPayments', 'totalProductSales', 'years', 'terms', 'selectedYear', 'selectedTerm'));
    }

    public function schoolExpenses()
    {
        $expenses = SchoolExpense::orderBy('date', 'desc')->paginate(20);
        $totalExpenses = SchoolExpense::sum('amount');
        
        return view('backend.finance.school-expenses', compact('expenses', 'totalExpenses'));
    }

    public function products(Request $request)
    {
        // Year and term filter setup
        $years = range(date('Y'), date('Y') - 5);
        $terms = ['first' => 'First Term', 'second' => 'Second Term', 'third' => 'Third Term'];
        
        $selectedYear = $request->year;
        $selectedTerm = $request->term;
        
        $query = Product::orderBy('created_at', 'desc');
        
        // Apply year/term filter using term/year fields
        if ($selectedYear) {
            $query->where('year', $selectedYear);
        }
        if ($selectedTerm) {
            $query->where('term', $selectedTerm);
        }
        
        $products = $query->paginate(20)->appends($request->query());
        $totalRevenue = (clone $query)->sum(\DB::raw('price * quantity_sold'));
        
        return view('backend.finance.products', compact('products', 'totalRevenue', 'years', 'terms', 'selectedYear', 'selectedTerm'));
    }

    public function storeIncome(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'term' => 'required|string|in:first,second,third',
            'year' => 'required|integer',
            'description' => 'required|string|max:255',
            'category' => 'required|string',
            'amount' => 'required|numeric|min:0',
        ]);

        $income = SchoolIncome::create($validated);

        // Auto-create CashBookEntry
        $lastEntry = CashBookEntry::orderBy('id', 'desc')->first();
        $currentBalance = $lastEntry ? $lastEntry->balance : 0;
        $newBalance = $currentBalance + $validated['amount'];

        $cashEntry = CashBookEntry::create([
            'entry_date' => $validated['date'],
            'term' => $validated['term'],
            'year' => $validated['year'],
            'reference_number' => CashBookEntry::generateReferenceNumber(),
            'transaction_type' => 'receipt',
            'category' => 'other_income',
            'description' => '[School Income] ' . $validated['description'],
            'amount' => $validated['amount'],
            'balance' => $newBalance,
            'payment_method' => 'cash',
            'payer_payee' => $validated['category'],
            'created_by' => auth()->id(),
            'notes' => 'Auto-generated from School Income #' . $income->id,
        ]);
        $cashEntry->postToLedger();

        return redirect()->route('finance.school-income')->with('success', 'Income recorded successfully!');
    }

    /**
     * Reverse an income record (GOVERNANCE COMPLIANT)
     * Instead of deleting, create a reversal entry to maintain audit trail
     */
    public function destroyIncome($id)
    {
        $income = SchoolIncome::findOrFail($id);
        
        // Find the original cash book entry
        $originalEntry = CashBookEntry::where('notes', 'Auto-generated from School Income #' . $id)->first();
        
        if ($originalEntry) {
            // Create reversal entry instead of deleting (GOVERNANCE COMPLIANT)
            $lastEntry = CashBookEntry::orderBy('id', 'desc')->first();
            $currentBalance = $lastEntry ? $lastEntry->balance : 0;
            $newBalance = $currentBalance - $originalEntry->amount; // Reverse the receipt
            
            $reversalEntry = CashBookEntry::create([
                'entry_date' => now(),
                'reference_number' => CashBookEntry::generateReferenceNumber(),
                'transaction_type' => 'payment', // Opposite of receipt
                'category' => $originalEntry->category,
                'description' => '[REVERSAL] ' . $originalEntry->description,
                'amount' => $originalEntry->amount,
                'balance' => $newBalance,
                'payment_method' => $originalEntry->payment_method,
                'payer_payee' => $originalEntry->payer_payee,
                'created_by' => auth()->id(),
                'notes' => 'Reversal of Income #' . $id . ' - Original ref: ' . $originalEntry->reference_number,
            ]);
            $reversalEntry->postToLedger();
        }
        
        // Soft-mark income as reversed instead of deleting
        $income->update(['description' => '[REVERSED] ' . $income->description]);

        return redirect()->route('finance.school-income')->with('success', 'Income record reversed successfully!');
    }

    public function storeExpense(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'description' => 'required|string|max:255',
            'category' => 'required|string',
            'amount' => 'required|numeric|min:0',
        ]);

        $expense = SchoolExpense::create($validated);

        // Auto-create CashBookEntry
        $lastEntry = CashBookEntry::orderBy('id', 'desc')->first();
        $currentBalance = $lastEntry ? $lastEntry->balance : 0;
        $newBalance = $currentBalance - $validated['amount'];

        $cashEntry = CashBookEntry::create([
            'entry_date' => $validated['date'],
            'reference_number' => CashBookEntry::generateReferenceNumber(),
            'transaction_type' => 'payment',
            'category' => 'other_expense',
            'description' => '[School Expense] ' . $validated['description'],
            'amount' => $validated['amount'],
            'balance' => $newBalance,
            'payment_method' => 'cash',
            'payer_payee' => $validated['category'],
            'created_by' => auth()->id(),
            'notes' => 'Auto-generated from School Expense #' . $expense->id,
        ]);
        $cashEntry->postToLedger();

        return redirect()->route('finance.school-expenses')->with('success', 'Expense recorded successfully!');
    }

    /**
     * Reverse an expense record (GOVERNANCE COMPLIANT)
     * Instead of deleting, create a reversal entry to maintain audit trail
     */
    public function destroyExpense($id)
    {
        $expense = SchoolExpense::findOrFail($id);
        
        // Find the original cash book entry
        $originalEntry = CashBookEntry::where('notes', 'Auto-generated from School Expense #' . $id)->first();
        
        if ($originalEntry) {
            // Create reversal entry instead of deleting (GOVERNANCE COMPLIANT)
            $lastEntry = CashBookEntry::orderBy('id', 'desc')->first();
            $currentBalance = $lastEntry ? $lastEntry->balance : 0;
            $newBalance = $currentBalance + $originalEntry->amount; // Reverse the payment
            
            $reversalEntry = CashBookEntry::create([
                'entry_date' => now(),
                'reference_number' => CashBookEntry::generateReferenceNumber(),
                'transaction_type' => 'receipt', // Opposite of payment
                'category' => $originalEntry->category,
                'description' => '[REVERSAL] ' . $originalEntry->description,
                'amount' => $originalEntry->amount,
                'balance' => $newBalance,
                'payment_method' => $originalEntry->payment_method,
                'payer_payee' => $originalEntry->payer_payee,
                'created_by' => auth()->id(),
                'notes' => 'Reversal of Expense #' . $id . ' - Original ref: ' . $originalEntry->reference_number,
            ]);
            $reversalEntry->postToLedger();
        }
        
        // Soft-mark expense as reversed instead of deleting
        $expense->update(['description' => '[REVERSED] ' . $expense->description]);

        return redirect()->route('finance.school-expenses')->with('success', 'Expense record reversed successfully!');
    }

    public function storeProduct(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'term' => 'required|string|in:first,second,third',
            'year' => 'required|integer',
            'category' => 'required|string',
            'price' => 'required|numeric|min:0',
            'quantity_sold' => 'required|integer|min:0',
        ]);

        $product = Product::create($validated);

        // Auto-create School Income and CashBookEntry if quantity sold > 0
        $totalSale = $validated['price'] * $validated['quantity_sold'];
        if ($totalSale > 0) {
            // Create School Income record
            $income = SchoolIncome::create([
                'date' => now()->format('Y-m-d'),
                'term' => $validated['term'],
                'year' => $validated['year'],
                'category' => 'Products Sold',
                'description' => 'Product Sale: ' . $validated['name'] . ' (' . $validated['quantity_sold'] . ' units @ $' . number_format($validated['price'], 2) . ')',
                'amount' => $totalSale,
            ]);

            // Auto-create CashBookEntry
            $lastEntry = CashBookEntry::orderBy('id', 'desc')->first();
            $currentBalance = $lastEntry ? $lastEntry->balance : 0;
            $newBalance = $currentBalance + $totalSale;

            $cashEntry = CashBookEntry::create([
                'entry_date' => now()->format('Y-m-d'),
                'term' => $validated['term'],
                'year' => $validated['year'],
                'reference_number' => CashBookEntry::generateReferenceNumber(),
                'transaction_type' => 'receipt',
                'category' => 'other_income',
                'description' => '[Product Sale] ' . $validated['name'] . ' (' . $validated['quantity_sold'] . ' units)',
                'amount' => $totalSale,
                'balance' => $newBalance,
                'payment_method' => 'cash',
                'payer_payee' => 'Product Sales',
                'created_by' => auth()->id(),
                'notes' => 'Auto-generated from Product #' . $product->id,
            ]);
            $cashEntry->postToLedger();
        }

        return redirect()->route('finance.products')->with('success', 'Product added successfully!');
    }

    public function destroyProduct($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('finance.products')->with('success', 'Product deleted!');
    }

    public function financialStatements(Request $request)
    {
        // Get current active term from ResultsStatus
        $activeTerm = \App\ResultsStatus::orderBy('year', 'desc')
            ->orderBy('result_period', 'desc')
            ->first();
        
        // Year and term filter setup
        $years = range(date('Y'), date('Y') - 5);
        $terms = ['first' => 'First Term', 'second' => 'Second Term', 'third' => 'Third Term'];
        
        // Get all terms from database for filter
        $allTerms = \App\ResultsStatus::orderBy('year', 'desc')
            ->orderBy('result_period', 'desc')
            ->get();
        
        // Default to current term if no filter specified
        $selectedYear = $request->has('year') ? $request->year : ($activeTerm ? $activeTerm->year : null);
        $selectedTerm = $request->has('term') ? $request->term : ($activeTerm ? $activeTerm->result_period : null);
        
        // Build base query with term/year filter
        $incomeQuery = CashBookEntry::where('transaction_type', 'receipt');
        $expenseQuery = CashBookEntry::where('transaction_type', 'payment');
        
        if ($selectedYear) {
            $incomeQuery->where('year', $selectedYear);
            $expenseQuery->where('year', $selectedYear);
        }
        if ($selectedTerm) {
            $incomeQuery->where('term', $selectedTerm);
            $expenseQuery->where('term', $selectedTerm);
        }
        
        // Pull from CashBookEntry for complete financial picture
        $totalIncome = (clone $incomeQuery)->sum('amount');
        $totalExpenses = (clone $expenseQuery)->sum('amount');
        $netProfit = $totalIncome - $totalExpenses;
        
        $incomeByCategory = (clone $incomeQuery)
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->get();
        
        $expensesByCategory = (clone $expenseQuery)
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->get();
        
        // Get school fees income breakdown by fee type from StudentPayments
        // Exclude graduated students (is_transferred = true)
        $schoolFeesQuery = \App\StudentPayment::with(['feeStructure.feeType', 'student'])
            ->whereHas('student', function($q) {
                $q->where('is_transferred', false);
            });
        if ($selectedYear) {
            $schoolFeesQuery->whereYear('payment_date', $selectedYear);
        }
        if ($selectedTerm) {
            $schoolFeesQuery->whereHas('resultsStatus', function($q) use ($selectedTerm) {
                $q->where('result_period', $selectedTerm);
            });
        }
        
        $schoolFeesPayments = $schoolFeesQuery->get();
        
        // Group by fee type
        $schoolFeesByType = [];
        $schoolFeesByCategory = [
            'zimsec_day' => ['label' => 'ZIMSEC Day', 'total' => 0],
            'zimsec_boarding' => ['label' => 'ZIMSEC Boarding', 'total' => 0],
            'cambridge_day' => ['label' => 'Cambridge Day', 'total' => 0],
            'cambridge_boarding' => ['label' => 'Cambridge Boarding', 'total' => 0],
        ];
        
        foreach ($schoolFeesPayments as $payment) {
            $feeTypeName = 'General Fees';
            if ($payment->feeStructure && $payment->feeStructure->feeType) {
                $feeTypeName = $payment->feeStructure->feeType->name;
            }
            
            if (!isset($schoolFeesByType[$feeTypeName])) {
                $schoolFeesByType[$feeTypeName] = 0;
            }
            $schoolFeesByType[$feeTypeName] += floatval($payment->amount_paid);
            
            // Also categorize by student type
            if ($payment->student) {
                $studentType = $payment->student->student_type ?? 'day';
                $curriculumType = $payment->student->curriculum_type ?? 'zimsec';
                $categoryKey = $curriculumType . '_' . $studentType;
                
                if (isset($schoolFeesByCategory[$categoryKey])) {
                    $schoolFeesByCategory[$categoryKey]['total'] += floatval($payment->amount_paid);
                }
            }
        }
        
        arsort($schoolFeesByType);
        $totalSchoolFeesIncome = array_sum($schoolFeesByType);
        
        $monthlyIncome = (clone $incomeQuery)
            ->select(
                DB::raw('MONTH(entry_date) as month'),
                DB::raw('YEAR(entry_date) as year'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy(DB::raw('YEAR(entry_date)'), DB::raw('MONTH(entry_date)'))
            ->orderBy(DB::raw('YEAR(entry_date)'), 'desc')
            ->orderBy(DB::raw('MONTH(entry_date)'), 'desc')
            ->limit(12)
            ->get();
        
        $monthlyExpenses = (clone $expenseQuery)
            ->select(
                DB::raw('MONTH(entry_date) as month'),
                DB::raw('YEAR(entry_date) as year'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy(DB::raw('YEAR(entry_date)'), DB::raw('MONTH(entry_date)'))
            ->orderBy(DB::raw('YEAR(entry_date)'), 'desc')
            ->orderBy(DB::raw('MONTH(entry_date)'), 'desc')
            ->limit(12)
            ->get();
        
        // Calculate outstanding student fees with balance B/F
        $allTermsForCalc = \App\ResultsStatus::with(['termFees.feeType', 'feeStructures.feeType', 'feeStructures.feeLevelGroup'])
            ->orderBy('year', 'asc')
            ->orderBy('result_period', 'asc')
            ->get();
        
        $currentTerm = \App\ResultsStatus::orderBy('year', 'desc')->orderBy('result_period', 'desc')->first();
        
        $totalStudentFees = 0;
        $totalBalanceBf = 0;
        $totalCurrentTermFees = 0;
        $totalStudentPayments = 0;
        
        // Fee breakdown by fee type for current term
        $feesByType = [];
        
        // Fee breakdown by category (student type + curriculum)
        $feesByCategory = [
            'zimsec_day' => ['label' => 'ZIMSEC Day', 'fees' => [], 'total' => 0, 'count' => 0],
            'zimsec_boarding' => ['label' => 'ZIMSEC Boarding', 'fees' => [], 'total' => 0, 'count' => 0],
            'cambridge_day' => ['label' => 'Cambridge Day', 'fees' => [], 'total' => 0, 'count' => 0],
            'cambridge_boarding' => ['label' => 'Cambridge Boarding', 'fees' => [], 'total' => 0, 'count' => 0],
        ];
        
        // Exclude graduated students (is_transferred = true) from fee calculations
        $students = Student::with(['class', 'payments'])
            ->where('is_transferred', false)
            ->get();
        foreach ($students as $student) {
            $feeBreakdown = $this->calculateCumulativeFees($student, $allTermsForCalc, $currentTerm);
            $totalBalanceBf += $feeBreakdown['balance_bf'];
            $totalCurrentTermFees += $feeBreakdown['current_term_fees'];
            $totalStudentFees += $feeBreakdown['total_fees'];
            $totalStudentPayments += floatval(\App\StudentPayment::where('student_id', $student->id)->sum('amount_paid'));
            
            // Calculate fee breakdown by type for current term
            if ($currentTerm) {
                $studentFeesByType = $this->getStudentFeesByType($student, $currentTerm);
                foreach ($studentFeesByType as $feeTypeName => $amount) {
                    if (!isset($feesByType[$feeTypeName])) {
                        $feesByType[$feeTypeName] = 0;
                    }
                    $feesByType[$feeTypeName] += $amount;
                }
                
                // Categorize by student type and curriculum
                $studentType = $student->student_type ?? 'day';
                $curriculumType = $student->curriculum_type ?? 'zimsec';
                $categoryKey = $curriculumType . '_' . $studentType;
                $isNewStudent = $student->is_new_student ?? false;
                $scholarshipPercentage = floatval($student->scholarship_percentage ?? 0);
                
                if (isset($feesByCategory[$categoryKey])) {
                    $feesByCategory[$categoryKey]['count']++;
                    
                    // Track new vs existing students separately
                    if ($isNewStudent) {
                        $feesByCategory[$categoryKey]['new_count'] = ($feesByCategory[$categoryKey]['new_count'] ?? 0) + 1;
                    } else {
                        $feesByCategory[$categoryKey]['existing_count'] = ($feesByCategory[$categoryKey]['existing_count'] ?? 0) + 1;
                    }
                    
                    // Track scholarship students
                    if ($scholarshipPercentage > 0) {
                        $feesByCategory[$categoryKey]['scholarship_count'] = ($feesByCategory[$categoryKey]['scholarship_count'] ?? 0) + 1;
                        $feesByCategory[$categoryKey]['total_scholarship_discount'] = ($feesByCategory[$categoryKey]['total_scholarship_discount'] ?? 0);
                    }
                    
                    foreach ($studentFeesByType as $feeTypeName => $amount) {
                        if (!isset($feesByCategory[$categoryKey]['fees'][$feeTypeName])) {
                            $feesByCategory[$categoryKey]['fees'][$feeTypeName] = 0;
                        }
                        $feesByCategory[$categoryKey]['fees'][$feeTypeName] += $amount;
                        $feesByCategory[$categoryKey]['total'] += $amount;
                        
                        // Track payments for progress bar
                        $studentPaymentsForFee = \App\StudentPayment::where('student_id', $student->id)
                            ->whereHas('feeStructure', function($q) use ($feeTypeName) {
                                $q->whereHas('feeType', function($q2) use ($feeTypeName) {
                                    $q2->where('name', $feeTypeName);
                                });
                            })
                            ->sum('amount_paid');
                        
                        if (!isset($feesByCategory[$categoryKey]['payments'][$feeTypeName])) {
                            $feesByCategory[$categoryKey]['payments'][$feeTypeName] = 0;
                        }
                        $feesByCategory[$categoryKey]['payments'][$feeTypeName] += $studentPaymentsForFee;
                    }
                }
            }
        }
        
        // Sort fee types by amount descending
        arsort($feesByType);
        
        // Sort fees within each category
        foreach ($feesByCategory as &$category) {
            arsort($category['fees']);
        }
        
        $totalOutstandingFees = $totalStudentFees - $totalStudentPayments;
        
        return view('backend.finance.statements', compact(
            'totalIncome',
            'totalExpenses',
            'netProfit',
            'incomeByCategory',
            'expensesByCategory',
            'monthlyIncome',
            'monthlyExpenses',
            'years',
            'terms',
            'selectedYear',
            'selectedTerm',
            'totalStudentFees',
            'totalBalanceBf',
            'totalCurrentTermFees',
            'totalStudentPayments',
            'totalOutstandingFees',
            'feesByType',
            'feesByCategory',
            'currentTerm',
            'schoolFeesByType',
            'schoolFeesByCategory',
            'totalSchoolFeesIncome'
        ));
    }

    /**
     * Display double-entry accounting guide
     */
    public function accountingGuide()
    {
        return view('backend.finance.accounting-guide');
    }

    /**
     * Display payment history for parent's children
     */
    public function parentPaymentHistory()
    {
        $parent = Parents::where('user_id', auth()->id())->first();
        
        if (!$parent) {
            return redirect()->route('home')->with('error', 'Parent profile not found.');
        }

        $children = $parent->children()->with(['user', 'class'])->get();
        
        // Get all terms for reference
        $allTerms = \App\ResultsStatus::orderBy('year', 'desc')
            ->orderBy('result_period', 'desc')
            ->get();

        $paymentData = [];
        
        foreach ($children as $child) {
            // Get all payments for this child
            $payments = StudentPayment::where('student_id', $child->id)
                ->with(['resultsStatus', 'termFee.feeType'])
                ->orderBy('payment_date', 'desc')
                ->get();

            // Calculate fees and balances per term
            $termSummary = [];
            $studentType = $child->student_type ?? 'day';
            $curriculumType = $child->curriculum_type ?? 'zimsec';
            $scholarshipPercentage = floatval($child->scholarship_percentage ?? 0);
            
            foreach ($allTerms as $term) {
                // Get base fee based on curriculum and student type
                $termFees = 0;
                if ($curriculumType === 'cambridge') {
                    $termFees = $studentType === 'boarding' 
                        ? floatval($term->cambridge_boarding_fees ?? 0) 
                        : floatval($term->cambridge_day_fees ?? 0);
                } else {
                    $termFees = $studentType === 'boarding' 
                        ? floatval($term->zimsec_boarding_fees ?? $term->total_boarding_fees ?? 0) 
                        : floatval($term->zimsec_day_fees ?? $term->total_day_fees ?? 0);
                }
                
                // Apply level-based fee adjustment if exists
                if ($child->class && $child->class->level) {
                    $levelAdjustment = \App\LevelFeeAdjustment::where('level', $child->class->level)->first();
                    if ($levelAdjustment) {
                        $termFees += floatval($levelAdjustment->adjustment_amount ?? 0);
                    }
                }
                
                // Apply scholarship discount
                if ($scholarshipPercentage > 0 && $scholarshipPercentage <= 100) {
                    $termFees = $termFees - ($termFees * ($scholarshipPercentage / 100));
                }
                
                $termPaid = StudentPayment::where('student_id', $child->id)
                    ->where('results_status_id', $term->id)
                    ->sum('amount_paid');

                $termSummary[] = [
                    'term' => ucfirst($term->result_period) . ' ' . $term->year,
                    'term_id' => $term->id,
                    'fees' => $termFees,
                    'paid' => floatval($termPaid),
                    'balance' => $termFees - floatval($termPaid)
                ];
            }

            // Calculate totals
            $totalFees = collect($termSummary)->sum('fees');
            $totalPaid = $payments->sum('amount_paid');
            $totalBalance = $totalFees - $totalPaid;

            $paymentData[] = [
                'student' => $child,
                'payments' => $payments,
                'termSummary' => $termSummary,
                'totalFees' => $totalFees,
                'totalPaid' => $totalPaid,
                'totalBalance' => $totalBalance
            ];
        }

        return view('parent.payment-history', compact('paymentData', 'children'));
    }

    /**
     * Calculate total fees for a student based on curriculum type, student type, and scholarship percentage
     * Now uses the new FeeStructure model with FeeLevelGroups
     */
    private function calculateStudentFees($student, $currentTerm)
    {
        if (!$currentTerm) {
            return 0;
        }

        $studentType = $student->student_type ?? 'day';
        $curriculumType = $student->curriculum_type ?? 'zimsec';
        $isNewStudent = $student->is_new_student ?? false;
        $scholarshipPercentage = floatval($student->scholarship_percentage ?? 0);

        // Get student's class numeric
        $classNumeric = optional($student->class)->class_numeric;
        if (!$classNumeric) {
            // Fallback to old method if no class assigned
            return $this->calculateStudentFeesLegacy($student, $currentTerm);
        }

        // Find the appropriate fee level group for this student's class
        $levelGroup = FeeLevelGroup::where('is_active', true)
            ->where('min_class_numeric', '<=', $classNumeric)
            ->where('max_class_numeric', '>=', $classNumeric)
            ->first();

        if (!$levelGroup) {
            // Fallback to old method if no level group found
            return $this->calculateStudentFeesLegacy($student, $currentTerm);
        }

        // Get fee structures for this term, level group, student type, and curriculum
        $feeStructures = FeeStructure::where('results_status_id', $currentTerm->id)
            ->where('fee_level_group_id', $levelGroup->id)
            ->where('student_type', $studentType)
            ->where('curriculum_type', $curriculumType)
            ->where('is_for_new_student', $isNewStudent)
            ->get();

        // If no fees found for new student status, try existing student fees
        if ($feeStructures->isEmpty() && $isNewStudent) {
            $feeStructures = FeeStructure::where('results_status_id', $currentTerm->id)
                ->where('fee_level_group_id', $levelGroup->id)
                ->where('student_type', $studentType)
                ->where('curriculum_type', $curriculumType)
                ->where('is_for_new_student', false)
                ->get();
        }

        // Calculate total from fee structures
        $baseFee = $feeStructures->sum('amount');

        // If still no fees, fallback to legacy calculation
        if ($baseFee == 0) {
            return $this->calculateStudentFeesLegacy($student, $currentTerm);
        }

        // Apply scholarship discount
        if ($scholarshipPercentage > 0 && $scholarshipPercentage <= 100) {
            $discount = $baseFee * ($scholarshipPercentage / 100);
            $baseFee = $baseFee - $discount;
        }

        return $baseFee;
    }

    /**
     * Get fee breakdown by fee type for a student in a specific term
     */
    private function getStudentFeesByType($student, $currentTerm)
    {
        $feesByType = [];
        
        if (!$currentTerm) {
            return $feesByType;
        }

        $studentType = $student->student_type ?? 'day';
        $curriculumType = $student->curriculum_type ?? 'zimsec';
        $isNewStudent = $student->is_new_student ?? false;
        $scholarshipPercentage = floatval($student->scholarship_percentage ?? 0);

        // Get student's class numeric
        $classNumeric = optional($student->class)->class_numeric;
        if (!$classNumeric) {
            return $feesByType;
        }

        // Find the appropriate fee level group for this student's class
        $levelGroup = FeeLevelGroup::where('is_active', true)
            ->where('min_class_numeric', '<=', $classNumeric)
            ->where('max_class_numeric', '>=', $classNumeric)
            ->first();

        if (!$levelGroup) {
            return $feesByType;
        }

        // Get fee structures for this term with fee type details
        $feeStructures = FeeStructure::where('results_status_id', $currentTerm->id)
            ->where('fee_level_group_id', $levelGroup->id)
            ->where('student_type', $studentType)
            ->where('curriculum_type', $curriculumType)
            ->where('is_for_new_student', $isNewStudent)
            ->with('feeType')
            ->get();

        // If no fees found for new student status, try existing student fees
        if ($feeStructures->isEmpty() && $isNewStudent) {
            $feeStructures = FeeStructure::where('results_status_id', $currentTerm->id)
                ->where('fee_level_group_id', $levelGroup->id)
                ->where('student_type', $studentType)
                ->where('curriculum_type', $curriculumType)
                ->where('is_for_new_student', false)
                ->with('feeType')
                ->get();
        }

        // Group fees by type
        foreach ($feeStructures as $feeStructure) {
            $feeTypeName = $feeStructure->feeType ? $feeStructure->feeType->name : 'Other Fees';
            $amount = floatval($feeStructure->amount);
            
            // Apply scholarship discount per fee type
            if ($scholarshipPercentage > 0 && $scholarshipPercentage <= 100) {
                $discount = $amount * ($scholarshipPercentage / 100);
                $amount = $amount - $discount;
            }
            
            if (!isset($feesByType[$feeTypeName])) {
                $feesByType[$feeTypeName] = 0;
            }
            $feesByType[$feeTypeName] += $amount;
        }

        return $feesByType;
    }

    /**
     * Legacy fee calculation for backward compatibility
     */
    private function calculateStudentFeesLegacy($student, $currentTerm)
    {
        $studentType = $student->student_type ?? 'day';
        $curriculumType = $student->curriculum_type ?? 'zimsec';
        $scholarshipPercentage = floatval($student->scholarship_percentage ?? 0);

        // Get base fee based on curriculum and student type
        $baseFee = 0;
        if ($curriculumType === 'cambridge') {
            $baseFee = $studentType === 'boarding' 
                ? floatval($currentTerm->cambridge_boarding_fees ?? 0) 
                : floatval($currentTerm->cambridge_day_fees ?? 0);
        } else {
            // Default to ZIMSEC
            $baseFee = $studentType === 'boarding' 
                ? floatval($currentTerm->zimsec_boarding_fees ?? $currentTerm->total_boarding_fees ?? 0) 
                : floatval($currentTerm->zimsec_day_fees ?? $currentTerm->total_day_fees ?? 0);
        }

        // Apply scholarship discount
        if ($scholarshipPercentage > 0 && $scholarshipPercentage <= 100) {
            $discount = $baseFee * ($scholarshipPercentage / 100);
            $baseFee = $baseFee - $discount;
        }

        return $baseFee;
    }

    /**
     * Calculate cumulative fees for a student across all terms
     * Returns current term fees, balance brought forward, and total fees
     */
    private function calculateCumulativeFees($student, $allTerms, $currentTerm)
    {
        $currentTermFees = 0;
        $previousTermsFees = 0;
        
        if (!$currentTerm) {
            return [
                'current_term_fees' => 0,
                'balance_bf' => 0,
                'total_fees' => 0,
            ];
        }

        // Get student's creation date to only count terms after enrollment
        $studentCreatedAt = $student->created_at;

        foreach ($allTerms as $term) {
            // Skip terms before student was enrolled
            if ($studentCreatedAt && $term->created_at < $studentCreatedAt) {
                continue;
            }

            $termFees = $this->calculateStudentFees($student, $term);
            
            if ($currentTerm && $term->id === $currentTerm->id) {
                $currentTermFees = $termFees;
            } else if (!$currentTerm || $term->id < $currentTerm->id) {
                // This is a previous term
                $previousTermsFees += $termFees;
            }
        }

        // Calculate balance brought forward (previous terms fees - previous payments)
        // For simplicity, we calculate total fees and let the caller compute actual BF
        $totalFees = $previousTermsFees + $currentTermFees;

        return [
            'current_term_fees' => $currentTermFees,
            'balance_bf' => $previousTermsFees,
            'total_fees' => $totalFees,
        ];
    }

    /**
     * Enforce/apply fee structures to students based on their category
     * This is useful when students were added before fee structures were created
     */
    public function enforceFees(Request $request)
    {
        $currentTerm = \App\ResultsStatus::with('feeStructures.feeLevelGroup')
            ->orderBy('year', 'desc')
            ->orderBy('result_period', 'desc')
            ->first();

        if (!$currentTerm) {
            return redirect()->back()->with('error', 'No active term found. Please create a term first.');
        }

        // Check if fee structures exist for this term
        if (!$currentTerm->feeStructures || $currentTerm->feeStructures->isEmpty()) {
            return redirect()->back()->with('error', 'No fee structures found for the current term. Please set up fee structures first.');
        }

        // Get students to process
        $studentId = $request->input('student_id');
        
        if ($studentId) {
            // Process single student
            $students = Student::with('class')->where('id', $studentId)->get();
        } else {
            // Process all students
            $students = Student::with('class')->get();
        }

        $updated = 0;
        $skipped = 0;
        $errors = [];

        foreach ($students as $student) {
            $classNumeric = optional($student->class)->class_numeric;
            
            if (!$classNumeric) {
                $skipped++;
                continue;
            }

            // Find the appropriate fee level group
            $levelGroup = FeeLevelGroup::where('is_active', true)
                ->where('min_class_numeric', '<=', $classNumeric)
                ->where('max_class_numeric', '>=', $classNumeric)
                ->first();

            if (!$levelGroup) {
                $skipped++;
                continue;
            }

            // Calculate fees using the new method
            $totalFees = $this->calculateStudentFees($student, $currentTerm);
            
            if ($totalFees > 0) {
                $updated++;
            } else {
                $skipped++;
            }
        }

        $message = "Fee enforcement completed. {$updated} student(s) have fees calculated. {$skipped} student(s) skipped (no class or fee structure match).";
        
        return redirect()->back()->with('success', $message);
    }
}
