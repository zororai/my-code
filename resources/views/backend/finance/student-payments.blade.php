@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Student Payments</h2>
        <div class="flex flex-wrap gap-2">
            <form action="{{ route('finance.enforce-fees') }}" method="POST" class="inline" onsubmit="return confirm('This will recalculate fees for all students based on the current fee structure. Continue?')">
                @csrf
                <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Enforce Fees
                </button>
            </form>
            <a href="{{ route('finance.student-payments.export', request()->query()) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export to Excel
            </a>
            <button onclick="openPaymentModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Record Payment
            </button>
        </div>
    </div>

    <!-- Filter -->
    <form method="GET" action="{{ route('finance.student-payments') }}" id="filterForm">
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Filter by Class</label>
                    <select name="class_id" id="filterClass" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Classes</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->class_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Status</label>
                    <select name="status" id="filterStatus" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Status</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Fully Paid</option>
                        <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partially Paid</option>
                        <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search by Receipt Number</label>
                    <input type="text" name="receipt_search" value="{{ request('receipt_search') }}"
                           placeholder="e.g. 1242"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>
            <div class="mt-3 flex justify-end gap-2">
                <a href="{{ route('finance.student-payments') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">
                    Clear Filters
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                    Apply Filters
                </button>
            </div>
        </div>
    </form>

    @if($receiptSearchResults->isNotEmpty())
    <!-- Receipt Number Search Results -->
    <div class="bg-white rounded-lg shadow p-4 mb-6 border-2 border-blue-200">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Receipt Search Results ({{ $receiptSearchResults->count() }})</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Class</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Date</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Receipt No.</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference No.</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($receiptSearchResults as $payment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-sm text-gray-900">{{ $payment->student->user->name ?? 'Unknown' }}</td>
                            <td class="px-4 py-2 text-sm text-gray-600">{{ $payment->student->class->class_name ?? 'N/A' }}</td>
                            <td class="px-4 py-2 text-sm text-gray-600">{{ $payment->payment_date->format('M d, Y') }}</td>
                            <td class="px-4 py-2 text-sm font-semibold text-gray-900">{{ $payment->receipt_number ?? '-' }}</td>
                            <td class="px-4 py-2 text-sm text-gray-600">{{ $payment->reference_number ?? '-' }}</td>
                            <td class="px-4 py-2 text-sm font-semibold text-green-600 text-right">${{ number_format($payment->amount_paid, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @elseif(request()->filled('receipt_search'))
    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-lg p-4 mb-6 text-sm">
        No payments found matching receipt number "{{ request('receipt_search') }}".
    </div>
    @endif

    <!-- Students Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto overflow-y-visible" style="max-width: 100%;">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Class</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance B/F</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Term</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Fees</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount Paid</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($students as $student)
                    @php
                        $totalFees = $student->total_fees ?? 0;
                        $amountPaid = $student->amount_paid ?? 0;
                        $balance = $totalFees - $amountPaid;
                        
                        if ($balance == 0 && $totalFees > 0) {
                            $status = 'paid';
                            $statusClass = 'bg-green-100 text-green-800';
                            $statusText = 'Fully Paid';
                        } elseif ($amountPaid > 0 && $balance > 0) {
                            $status = 'partial';
                            $statusClass = 'bg-yellow-100 text-yellow-800';
                            $statusText = 'Partially Paid';
                        } else {
                            $status = 'unpaid';
                            $statusClass = 'bg-red-100 text-red-800';
                            $statusText = 'Unpaid';
                        }
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <img class="h-10 w-10 rounded-full" src="https://ui-avatars.com/api/?name={{ urlencode($student->user->name ?? $student->name ?? 'Unknown') }}&background=random" alt="">
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $student->user->name ?? $student->name ?? 'Unknown' }}</div>
                                    <div class="text-sm text-gray-500">{{ $student->user->email ?? $student->email ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $student->class->class_name ?? 'N/A' }}</div>
                            <div class="flex gap-1 mt-1">
                                <span class="px-2 py-0.5 text-xs rounded-full {{ ($student->curriculum_type ?? 'zimsec') == 'cambridge' ? 'bg-purple-100 text-purple-800' : 'bg-indigo-100 text-indigo-800' }}">
                                    {{ strtoupper($student->curriculum_type ?? 'zimsec') }}
                                </span>
                                @if(($student->scholarship_percentage ?? 0) > 0)
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-800">
                                        {{ $student->scholarship_percentage }}% Off
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold {{ ($student->balance_bf ?? 0) > 0 ? 'text-orange-600' : 'text-gray-500' }}">
                            ${{ number_format($student->balance_bf ?? 0, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-blue-600">
                            ${{ number_format($student->current_term_fees ?? 0, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                            ${{ number_format($totalFees, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">
                            ${{ number_format($amountPaid, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold {{ $balance > 0 ? 'text-red-600' : 'text-gray-900' }}">
                            ${{ number_format($balance, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                {{ $statusText }}
                            </span>
                        </td>
                        @php
                            $paymentHistory = $student->payments->map(function($p) {
                                // Try to get fee type from feeStructure first, then termFee
                                $feeType = 'N/A';
                                if ($p->feeStructure && $p->feeStructure->feeType) {
                                    $feeType = $p->feeStructure->feeType->name;
                                } elseif ($p->termFee && $p->termFee->feeType) {
                                    $feeType = $p->termFee->feeType->name;
                                }
                                
                                return [
                                    'id' => $p->id,
                                    'amount' => $p->amount_paid,
                                    'date' => $p->payment_date->format('M d, Y'),
                                    'method' => $p->payment_method,
                                    'fee_type' => $feeType,
                                    'reference' => $p->reference_number,
                                    'receipt_number' => $p->receipt_number,
                                    'term' => ($p->resultsStatus->result_period ?? '') . ' ' . ($p->resultsStatus->year ?? '')
                                ];
                            })->values()->toArray();
                        @endphp
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <button type="button" 
                                        onclick="showPaymentHistory({{ $student->id }}, '{{ addslashes($student->user->name ?? $student->name ?? 'Unknown') }}', {{ json_encode($paymentHistory) }}, {{ $student->current_term_fees ?? 0 }}, {{ $balance }}, {{ $student->scholarship ? 'true' : 'false' }})"
                                        class="text-blue-600 hover:text-blue-900" title="Payment History">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </button>
                                <a href="{{ route('student.show', $student->id) }}" class="text-gray-600 hover:text-gray-900" title="View Details">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                            No students found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $students->links() }}
    </div>

    <!-- Payment History Modal -->
    <div id="paymentHistoryModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-5 border w-full max-w-3xl shadow-lg rounded-md bg-white mb-10 max-h-[90vh] overflow-y-auto">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-gray-900">
                        <svg class="w-6 h-6 inline-block mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Payment History
                    </h3>
                    <button onclick="closePaymentHistoryModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Student Name and Financial Summary -->
                <div class="mb-4 p-4 bg-blue-50 rounded-lg">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Student</p>
                            <p class="text-lg font-bold text-gray-900" id="history_student_name">-</p>
                            <div id="history_scholarship_badge" class="hidden mt-1">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    Scholarship
                                </span>
                            </div>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-600">Expected Fee (Term)</p>
                            <p class="text-xl font-bold text-blue-600" id="history_expected_fee">$0.00</p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-600">Current Balance</p>
                            <p class="text-xl font-bold text-red-600" id="history_balance">$0.00</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-600">Total Paid</p>
                            <p class="text-xl font-bold text-green-600" id="history_total_paid_summary">$0.00</p>
                        </div>
                    </div>
                </div>

                <!-- Payment History Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Term</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fee Type</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Receipt No.</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Receipt</th>
                            </tr>
                        </thead>
                        <tbody id="payment_history_body" class="bg-white divide-y divide-gray-200">
                            <!-- Populated by JavaScript -->
                        </tbody>
                        <tfoot class="bg-gray-100">
                            <tr>
                                <td colspan="7" class="px-4 py-3 text-right text-sm font-bold text-gray-700">Total Paid:</td>
                                <td class="px-4 py-3 text-right text-sm font-bold text-green-600" id="history_total_paid">$0.00</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- No Payments Message -->
                <div id="no_payments_message" class="hidden text-center py-8">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-gray-500">No payment records found for this student.</p>
                </div>

                <!-- Action Buttons -->
                <div class="mt-6 flex justify-between">
                    <button onclick="printPaymentHistory()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        Print
                    </button>
                    <button onclick="closePaymentHistoryModal()" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div id="paymentModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white mb-10 max-h-[90vh] overflow-y-auto">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-gray-900">Record Payment</h3>
                    <button onclick="closePaymentModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Alert Container -->
                <div id="modal_alert" class="hidden mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <span id="modal_alert_message" class="font-medium"></span>
                    </div>
                    <button onclick="hideAlert()" class="absolute top-0 bottom-0 right-0 px-4 py-3">
                        <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                        </svg>
                    </button>
                </div>

                <!-- Stepper -->
                <div class="mb-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center flex-1">
                            <div class="flex items-center text-blue-600 relative">
                                <div class="rounded-full transition duration-500 ease-in-out h-10 w-10 flex items-center justify-center border-2 border-blue-600 bg-blue-600 text-white font-bold" id="step1-circle">1</div>
                                <div class="absolute top-0 -ml-10 text-center mt-12 w-32 text-xs font-medium text-blue-600" id="step1-label">Select Student</div>
                            </div>
                            <div class="flex-auto border-t-2 transition duration-500 ease-in-out border-gray-300" id="line1"></div>
                        </div>
                        <div class="flex items-center flex-1">
                            <div class="flex items-center text-gray-500 relative">
                                <div class="rounded-full transition duration-500 ease-in-out h-10 w-10 flex items-center justify-center border-2 border-gray-300 bg-white font-bold" id="step2-circle">2</div>
                                <div class="absolute top-0 -ml-10 text-center mt-12 w-32 text-xs font-medium text-gray-500" id="step2-label">Select Fees</div>
                            </div>
                            <div class="flex-auto border-t-2 transition duration-500 ease-in-out border-gray-300" id="line2"></div>
                        </div>
                        <div class="flex items-center flex-1">
                            <div class="flex items-center text-gray-500 relative">
                                <div class="rounded-full transition duration-500 ease-in-out h-10 w-10 flex items-center justify-center border-2 border-gray-300 bg-white font-bold" id="step3-circle">3</div>
                                <div class="absolute top-0 -ml-10 text-center mt-12 w-32 text-xs font-medium text-gray-500" id="step3-label">Payment Details</div>
                            </div>
                            <div class="flex-auto border-t-2 transition duration-500 ease-in-out border-gray-300" id="line3"></div>
                        </div>
                        <div class="flex items-center">
                            <div class="flex items-center text-gray-500 relative">
                                <div class="rounded-full transition duration-500 ease-in-out h-10 w-10 flex items-center justify-center border-2 border-gray-300 bg-white font-bold" id="step4-circle">4</div>
                                <div class="absolute top-0 -ml-10 text-center mt-12 w-32 text-xs font-medium text-gray-500" id="step4-label">Review & Submit</div>
                            </div>
                        </div>
                    </div>
                </div>

                <form id="paymentForm" method="POST" action="{{ route('finance.payments.store') }}">
                    @csrf

                    <!-- Step 1: Student Selection -->
                    <div class="step-content" id="step1" style="display: block;">
                        <!-- Term/Year Selection -->
                        <div class="mb-4 bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                            <label class="block text-sm font-bold text-gray-700 mb-3">Select Term & Year</label>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Year</label>
                                    <select id="modal_year" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm" onchange="updateTermOptions()">
                                        <option value="">-- Select Year --</option>
                                        @php
                                            $years = $allTerms->pluck('year')->unique()->sortDesc();
                                        @endphp
                                        @foreach($years as $year)
                                            <option value="{{ $year }}" {{ $currentTerm && $currentTerm->year == $year ? 'selected' : '' }}>{{ $year }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Term</label>
                                    <select name="results_status_id" id="modal_results_status_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm" required onchange="updateFeeTypes()">
                                        <option value="">-- Select Term --</option>
                                        @foreach($allTerms as $term)
                                            @php
                                                $termFeeData = $term->feeStructures && $term->feeStructures->count() > 0 
                                                    ? $term->feeStructures->load('feeType', 'feeLevelGroup') 
                                                    : $term->termFees;
                                            @endphp
                                            <option value="{{ $term->id }}" 
                                                    data-year="{{ $term->year }}"
                                                    data-fees='@json($termFeeData)'
                                                    {{ $currentTerm && $currentTerm->id == $term->id ? 'selected' : '' }}>
                                                {{ ucfirst($term->result_period) }} Term {{ $term->year }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4 bg-gray-50 p-4 rounded-lg">
                        <label class="block text-sm font-bold text-gray-700 mb-3">Find Student</label>
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Filter by Class</label>
                                <select id="modal_class_filter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm" onchange="filterStudents()">
                                    <option value="">All Classes</option>
                                    @php
                                        $modalClasses = $allStudentsForModal->pluck('class')->unique()->filter();
                                    @endphp
                                    @foreach($modalClasses as $class)
                                        @if($class)
                                            <option value="{{ $class->id }}">{{ $class->class_name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Search Student</label>
                                <input type="text" id="modal_student_search" placeholder="Name or Roll Number..." 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"
                                       oninput="filterStudents()">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Select Student</label>
                            <select name="student_id" id="modal_student_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required onchange="updateStudentBalance()">
                                <option value="">-- Select a student --</option>
                                @foreach($allStudentsForModal as $student)
                                    @php
                                        $totalFees = $student->total_fees ?? 0;
                                        $amountPaid = $student->amount_paid ?? 0;
                                        $balance = $totalFees - $amountPaid;
                                    @endphp
                                    <option value="{{ $student->id }}" 
                                            data-name="{{ $student->user->name ?? 'Unknown' }}" 
                                            data-total-fees="{{ $totalFees }}"
                                            data-amount-paid="{{ $amountPaid }}"
                                            data-balance="{{ $balance }}"
                                            data-class="{{ $student->class->id ?? '' }}"
                                            data-class-numeric="{{ $student->class->class_numeric ?? '' }}"
                                            data-roll="{{ $student->roll_number }}"
                                            data-student-type="{{ $student->student_type ?? 'day' }}"
                                            data-curriculum-type="{{ $student->curriculum_type ?? 'zimsec' }}"
                                            data-is-new-student="{{ $student->is_new_student ?? 1 }}"
                                            class="student-option">
                                        {{ $student->user->name ?? 'Unknown' }} ({{ $student->roll_number }}) - {{ $student->class->class_name ?? 'N/A' }} - Balance: ${{ number_format($balance, 2) }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1" id="student_count">{{ $allStudentsForModal->count() }} students available</p>
                        </div>
                        </div>
                        <div class="flex justify-end mt-4">
                            <button type="button" onclick="nextStep(1)" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700" id="step1-next">Next</button>
                        </div>
                    </div>

                    <!-- Step 2: Select Fees -->
                    <div class="step-content" id="step2" style="display: none;">
                        <!-- Student Info -->
                    <div class="bg-blue-50 p-4 rounded-lg mb-4" id="student_info_section" style="display: none;">
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <p class="text-sm text-gray-600">Student Name</p>
                                <p class="font-semibold text-gray-900" id="modal_student_name"></p>
                            </div>
                            <div class="text-center">
                                <p class="text-sm text-gray-600">Original Balance</p>
                                <p class="text-lg font-semibold text-gray-700" id="modal_original_balance">$0.00</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-600">Outstanding Balance</p>
                                <p class="text-2xl font-bold text-red-600" id="modal_balance">$0.00</p>
                                <p class="text-xs text-gray-500 mt-1" id="balance_change_indicator"></p>
                            </div>
                        </div>
                    </div>

                        <!-- Fee Types Selection -->
                        <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-3">Select Fee Types to Pay</label>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-3" id="student_fee_info">
                            <p class="text-sm text-blue-700" id="fee_filter_info">Fee types will be filtered based on selected student's type, curriculum, and class level.</p>
                        </div>
                        <div class="border border-gray-300 rounded-lg p-4 max-h-96 overflow-y-auto" id="fee_types_container">
                            <p class="text-gray-500 text-center py-4">Select a student and term to see applicable fees</p>
                        </div>
                        </div>
                        <div class="flex justify-between mt-4">
                            <button type="button" onclick="prevStep(2)" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Previous</button>
                            <button type="button" onclick="nextStep(2)" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700" id="step2-next">Next</button>
                        </div>
                    </div>

                    <!-- Step 3: Payment Details -->
                    <div class="step-content" id="step3" style="display: none;">
                        <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Payment Date</label>
                            <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                            <select name="payment_method" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                                <option value="">Select Method</option>
                                <option value="Cash">Cash</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Mobile Money">Mobile Money</option>
                                <option value="Cheque">Cheque</option>
                                <option value="Card">Card</option>
                            </select>
                        </div>
                    </div>

                        <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Receipt Number</label>
                        <input type="text" name="receipt_number" id="receipt_number"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                               placeholder="Enter the number from the receipt book" required>
                        <p class="text-xs text-gray-500 mt-1">The number written on the physical/manual receipt issued to the payer.</p>
                    </div>

                        <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Reference Number</label>
                        <div class="flex gap-2">
                            <input type="text" name="reference_number" id="reference_number"
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" 
                                   placeholder="Auto-generated or enter manually" readonly>
                            <button type="button" onclick="generateReferenceNumber()" 
                                    class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-sm font-medium whitespace-nowrap">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Generate
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Format: PAY-YYYYMMDD-CLASS-HHMMSS-XXXX</p>
                    </div>

                        <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                        <textarea name="notes" rows="2" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" 
                                  placeholder="Additional notes about this payment"></textarea>
                        </div>
                        <div class="flex justify-between mt-4">
                            <button type="button" onclick="prevStep(3)" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Previous</button>
                            <button type="button" onclick="nextStep(3)" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Next</button>
                        </div>
                    </div>

                    <!-- Step 4: Review & Submit -->
                    <div class="step-content" id="step4" style="display: none;">
                        <!-- Payment Summary -->
                        <div class="bg-gray-50 p-4 rounded-lg mb-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-gray-600">Selected Fees Total:</span>
                            <span class="text-lg font-bold text-gray-900" id="selected_total">$0.00</span>
                        </div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-gray-600">Current Balance:</span>
                            <span class="text-lg font-semibold text-gray-700" id="current_balance">$0.00</span>
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t border-gray-300">
                            <span class="text-sm font-bold text-gray-700">Remaining Balance:</span>
                            <span class="text-xl font-bold text-red-600" id="remaining_balance">$0.00</span>
                        </div>
                        </div>
                        <!-- Payment Method Selection -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                            <div class="grid grid-cols-2 gap-3">
                                <button type="button" onclick="selectPaymentMethod('cash')" id="cashMethodBtn" class="payment-method-btn border-2 border-gray-300 rounded-lg p-4 hover:border-blue-500 transition-all">
                                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    <span class="text-sm font-medium">Cash/Card</span>
                                </button>
                                <button type="button" onclick="selectPaymentMethod('paynow')" id="paynowMethodBtn" class="payment-method-btn border-2 border-gray-300 rounded-lg p-4 hover:border-blue-500 transition-all">
                                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="text-sm font-medium">Paynow</span>
                                </button>
                            </div>
                        </div>

                        <div class="flex justify-between mt-4">
                            <button type="button" onclick="prevStep(4)" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Previous</button>
                            <button type="button" onclick="submitPayment()" id="submitPaymentBtn" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold">
                                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Record Payment
                            </button>
                        </div>
                    </div>

                    <!-- Step 5: Success with Print Receipt -->
                    <div class="step-content" id="step5" style="display: none;">
                        <div class="text-center py-6">
                            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Payment Recorded!</h3>
                            <p class="text-gray-600 mb-4" id="success_message">Payment has been recorded successfully.</p>
                            
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6 max-w-sm mx-auto">
                                <p class="text-sm text-gray-600">Amount Paid</p>
                                <p class="text-3xl font-bold text-green-600" id="success_amount">$0.00</p>
                                <p class="text-sm text-gray-500 mt-2">Remaining Balance: <span id="success_remaining" class="font-semibold">$0.00</span></p>
                            </div>

                            <div class="flex flex-col sm:flex-row justify-center gap-3">
                                <button type="button" onclick="printNewReceipt()" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                    </svg>
                                    Print Receipt
                                </button>
                                <button type="button" onclick="printViaBluetooth()" class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/>
                                    </svg>
                                    Bluetooth Printer
                                </button>
                                <button type="button" onclick="recordAnotherPayment()" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-semibold">
                                    Record Another Payment
                                </button>
                                <button type="button" onclick="closePaymentModal(); location.reload();" class="px-6 py-3 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Paynow Payment Modal -->
    <div id="paynowModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-gray-900">Paynow Payment</h3>
                    <button onclick="closePaynowModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-2">Student: <span class="font-semibold" id="paynow_student_name"></span></p>
                    <p class="text-sm text-gray-600 mb-2">Amount: <span class="font-semibold text-green-600" id="paynow_amount">$0.00</span></p>
                </div>

                <form id="paynowForm">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payer Phone Number *</label>
                        <input type="tel" id="paynow_phone" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="e.g., 0771234567 or 263771234567"
                               pattern="^(0|\+?263)[0-9]{9}$">
                        <p class="text-xs text-gray-500 mt-1">Enter the phone number that will make the payment</p>
                    </div>

                    <div id="paynow_alert" class="hidden mb-4 p-3 rounded-lg"></div>

                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closePaynowModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                            Cancel
                        </button>
                        <button type="submit" id="paynowSubmitBtn" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            Initiate Paynow Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Receipt Settings from database
    const receiptSchoolShortName = '{{ \App\SchoolSetting::get("receipt_school_short_name", "ROSHS") }}';
    const receiptSchoolFullName = '{{ \App\SchoolSetting::get("receipt_school_full_name", "Rose Of Sharon High School") }}';
    const receiptFooterMessage = '{{ \App\SchoolSetting::get("receipt_footer_message", "Thank You!") }}';
    const receiptFooterNote = '{{ \App\SchoolSetting::get("receipt_footer_note", "This is a computer-generated receipt.") }}';

    let currentBalance = 0;
    let currentStep = 1;
    let currentStudentName = '';
    let currentPayments = [];
    let currentExpectedFee = 0;
    let currentIsScholarship = false;
    let lastReceiptData = null;
    let selectedPaymentMethod = 'cash';
    let currentStudentId = null;
    let currentTermId = null;
    let selectedFeesTotal = 0;

    function openPaymentModal() {
        // Reset form and stepper
        document.getElementById('paymentForm').reset();
        currentStep = 1;
        showStep(1);
        
        document.getElementById('paymentModal').classList.remove('hidden');
    }

    function showStep(step) {
        // Hide all steps
        document.querySelectorAll('.step-content').forEach(el => el.style.display = 'none');
        
        // Show current step
        document.getElementById('step' + step).style.display = 'block';
        
        // Update stepper UI (step 5 is success - mark all as complete)
        const compareStep = step === 5 ? 5 : step;
        for (let i = 1; i <= 4; i++) {
            const circle = document.getElementById('step' + i + '-circle');
            const label = document.getElementById('step' + i + '-label');
            const line = document.getElementById('line' + i);
            
            if (i < compareStep || step === 5) {
                // Completed steps (or all complete if step 5)
                circle.className = 'rounded-full transition duration-500 ease-in-out h-10 w-10 flex items-center justify-center border-2 border-green-600 bg-green-600 text-white font-bold';
                circle.innerHTML = '✓';
                label.className = 'absolute top-0 -ml-10 text-center mt-12 w-32 text-xs font-medium text-green-600';
                if (line) line.className = 'flex-auto border-t-2 transition duration-500 ease-in-out border-green-600';
            } else if (i === step) {
                // Current step
                circle.className = 'rounded-full transition duration-500 ease-in-out h-10 w-10 flex items-center justify-center border-2 border-blue-600 bg-blue-600 text-white font-bold';
                circle.innerHTML = i;
                label.className = 'absolute top-0 -ml-10 text-center mt-12 w-32 text-xs font-medium text-blue-600';
                if (line) line.className = 'flex-auto border-t-2 transition duration-500 ease-in-out border-gray-300';
            } else {
                // Future steps
                circle.className = 'rounded-full transition duration-500 ease-in-out h-10 w-10 flex items-center justify-center border-2 border-gray-300 bg-white font-bold';
                circle.innerHTML = i;
                label.className = 'absolute top-0 -ml-10 text-center mt-12 w-32 text-xs font-medium text-gray-500';
                if (line) line.className = 'flex-auto border-t-2 transition duration-500 ease-in-out border-gray-300';
            }
        }
        
        currentStep = step;
    }

    function showAlert(message) {
        const alertBox = document.getElementById('modal_alert');
        const alertMessage = document.getElementById('modal_alert_message');
        alertMessage.textContent = message;
        alertBox.classList.remove('hidden');
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            hideAlert();
        }, 5000);
        
        // Scroll to top of modal to show alert
        document.querySelector('#paymentModal > div').scrollTop = 0;
    }

    function hideAlert() {
        document.getElementById('modal_alert').classList.add('hidden');
    }

    function showPaymentHistory(studentId, studentName, payments, expectedFee = 0, balance = 0, isScholarship = false) {
        document.getElementById('history_student_name').textContent = studentName;
        
        // Update financial summary
        document.getElementById('history_expected_fee').textContent = '$' + parseFloat(expectedFee).toFixed(2);
        document.getElementById('history_balance').textContent = '$' + parseFloat(balance).toFixed(2);
        
        // Show/hide scholarship badge
        const scholarshipBadge = document.getElementById('history_scholarship_badge');
        if (isScholarship) {
            scholarshipBadge.classList.remove('hidden');
        } else {
            scholarshipBadge.classList.add('hidden');
        }
        
        const tbody = document.getElementById('payment_history_body');
        const noPaymentsMsg = document.getElementById('no_payments_message');
        const table = tbody.closest('table');
        
        tbody.innerHTML = '';
        
        if (!payments || payments.length === 0) {
            table.classList.add('hidden');
            noPaymentsMsg.classList.remove('hidden');
            document.getElementById('history_total_paid').textContent = '$0.00';
            document.getElementById('history_total_paid_summary').textContent = '$0.00';
        } else {
            table.classList.remove('hidden');
            noPaymentsMsg.classList.add('hidden');
            
            let totalPaid = 0;
            currentStudentName = studentName;
            currentPayments = payments;
            currentExpectedFee = expectedFee;
            currentBalance = balance;
            currentIsScholarship = isScholarship;
            
            payments.forEach((payment, index) => {
                totalPaid += parseFloat(payment.amount) || 0;
                const row = `
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-500">${index + 1}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">${payment.date}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">${payment.term || '-'}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 font-medium">${payment.fee_type}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">${payment.method || '-'}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">${payment.reference || '-'}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 font-medium">${payment.receipt_number || '-'}</td>
                        <td class="px-4 py-3 text-sm text-green-600 font-semibold text-right">$${parseFloat(payment.amount).toFixed(2)}</td>
                        <td class="px-4 py-3 text-sm">
                            <button onclick="printReceipt(${index})" class="text-blue-600 hover:text-blue-800" title="Print Receipt">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                `;
                tbody.insertAdjacentHTML('beforeend', row);
            });
            
            document.getElementById('history_total_paid').textContent = '$' + totalPaid.toFixed(2);
            document.getElementById('history_total_paid_summary').textContent = '$' + totalPaid.toFixed(2);
        }
        
        document.getElementById('paymentHistoryModal').classList.remove('hidden');
    }

    function closePaymentHistoryModal() {
        document.getElementById('paymentHistoryModal').classList.add('hidden');
    }

    function printPaymentHistory() {
        const studentName = document.getElementById('history_student_name').textContent;
        const tableContent = document.getElementById('payment_history_body').innerHTML;
        const totalPaid = document.getElementById('history_total_paid').textContent;
        const expectedFee = document.getElementById('history_expected_fee').textContent;
        const balance = document.getElementById('history_balance').textContent;
        const isScholarship = currentIsScholarship || false;
        
        const scholarshipBadge = isScholarship ? '<span style="display: inline-block; background: #f3e8ff; color: #7c3aed; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; margin-left: 10px;">★ SCHOLARSHIP</span>' : '';
        
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Payment History - ${studentName}</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; }
                    .header { text-align: center; margin-bottom: 30px; }
                    .header h1 { margin: 0; color: #1f2937; font-size: 24px; }
                    .header p { margin: 5px 0; color: #6b7280; }
                    .student-info { background: #eff6ff; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
                    .student-info p { margin: 5px 0; }
                    .student-info .name { font-size: 18px; font-weight: bold; color: #1f2937; }
                    .financial-summary { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 20px; padding: 15px; background: #f9fafb; border-radius: 8px; border: 1px solid #e5e7eb; }
                    .summary-item { text-align: center; }
                    .summary-label { font-size: 11px; color: #6b7280; text-transform: uppercase; margin-bottom: 5px; }
                    .summary-value { font-size: 20px; font-weight: bold; }
                    .summary-value.expected { color: #2563eb; }
                    .summary-value.balance { color: #dc2626; }
                    .summary-value.paid { color: #059669; }
                    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    th { background: #f3f4f6; padding: 12px 8px; text-align: left; font-size: 12px; text-transform: uppercase; color: #6b7280; border-bottom: 2px solid #e5e7eb; }
                    td { padding: 12px 8px; border-bottom: 1px solid #e5e7eb; font-size: 14px; }
                    .amount { text-align: right; color: #059669; font-weight: 600; }
                    .total-row { background: #f3f4f6; font-weight: bold; }
                    .total-row td { border-top: 2px solid #e5e7eb; }
                    .footer { margin-top: 30px; text-align: center; color: #9ca3af; font-size: 12px; }
                    @media print { body { padding: 0; } }
                </style>
            </head>
            <body>
                <div class="header">
                    <h1>Payment History Report</h1>
                    <p>Generated on ${new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</p>
                </div>
                <div class="student-info">
                    <p style="color: #6b7280; font-size: 12px;">Student</p>
                    <p class="name">${studentName}${scholarshipBadge}</p>
                </div>
                <div class="financial-summary">
                    <div class="summary-item">
                        <div class="summary-label">Expected Fee (Term)</div>
                        <div class="summary-value expected">${expectedFee}</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-label">Total Paid</div>
                        <div class="summary-value paid">${totalPaid}</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-label">Current Balance</div>
                        <div class="summary-value balance">${balance}</div>
                    </div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Term</th>
                            <th>Fee Type</th>
                            <th>Method</th>
                            <th>Reference</th>
                            <th style="text-align: right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${tableContent}
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="6" style="text-align: right;">Total Paid:</td>
                            <td class="amount">${totalPaid}</td>
                        </tr>
                    </tfoot>
                </table>
                <div class="footer">
                    <p>This is a computer-generated document.</p>
                </div>
            </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 250);
    }

    function printReceipt(paymentIndex) {
        const payment = currentPayments[paymentIndex];
        if (!payment) return;

        const receiptNo = 'RCP-' + String(payment.id).padStart(6, '0');
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Receipt - ${receiptNo}</title>
                <style>
                    @page { size: 58mm auto; margin: 0; }
                    * { margin: 0; padding: 0; box-sizing: border-box; }
                    body { font-family: 'Courier New', monospace; font-size: 9px; width: 58mm; max-width: 58mm; margin: 0 auto; padding: 2mm; background: #fff; }
                    .receipt { width: 100%; border: 1px dashed #000; padding: 3mm; }
                    .header { text-align: center; border-bottom: 1px dashed #000; padding-bottom: 3mm; margin-bottom: 3mm; }
                    .header h1 { font-size: 12px; margin-bottom: 2px; font-weight: bold; }
                    .header p { font-size: 8px; }
                    .receipt-no { font-size: 10px; font-weight: bold; margin: 3px 0; }
                    .details { margin: 3mm 0; }
                    .row { display: flex; justify-content: space-between; padding: 2px 0; border-bottom: 1px dotted #ccc; font-size: 8px; }
                    .row:last-child { border-bottom: none; }
                    .label { font-size: 8px; }
                    .value { font-size: 8px; font-weight: 600; text-align: right; max-width: 55%; }
                    .amount-section { border: 1px solid #000; padding: 3mm; margin: 3mm 0; text-align: center; }
                    .amount-label { font-size: 8px; text-transform: uppercase; }
                    .amount-value { font-size: 14px; font-weight: bold; }
                    .footer { text-align: center; margin-top: 3mm; padding-top: 3mm; border-top: 1px dashed #000; }
                    .footer p { font-size: 7px; margin: 1px 0; }
                    .thank-you { font-weight: bold; font-size: 9px; margin-bottom: 2px; }
                    @media print { 
                        html, body { width: 58mm; max-width: 58mm; }
                        body { padding: 1mm; } 
                    }
                </style>
            </head>
            <body>
                <div class="receipt">
                    <div class="header">
                        <h1>${receiptSchoolShortName}</h1>
                        <p>${receiptSchoolFullName}</p>
                        <p>Payment Receipt</p>
                        <div class="receipt-no">${receiptNo}</div>
                    </div>
                    
                    <div class="details">
                        <div class="row">
                            <span class="label">Student Name</span>
                            <span class="value">${currentStudentName}</span>
                        </div>
                        <div class="row">
                            <span class="label">Payment Date</span>
                            <span class="value">${payment.date}</span>
                        </div>
                        <div class="row">
                            <span class="label">Term</span>
                            <span class="value">${payment.term || '-'}</span>
                        </div>
                        <div class="row">
                            <span class="label">Fee Type</span>
                            <span class="value">${payment.fee_type}</span>
                        </div>
                        <div class="row">
                            <span class="label">Payment Method</span>
                            <span class="value">${payment.method || '-'}</span>
                        </div>
                        <div class="row">
                            <span class="label">Reference No.</span>
                            <span class="value">${payment.reference || '-'}</span>
                        </div>
                        <div class="row">
                            <span class="label">Receipt No.</span>
                            <span class="value">${payment.receipt_number || '-'}</span>
                        </div>
                    </div>

                    <div class="amount-section">
                        <div class="amount-label">Amount Paid</div>
                        <div class="amount-value">$${parseFloat(payment.amount).toFixed(2)}</div>
                    </div>
                    
                    <div class="footer">
                        <p class="thank-you">${receiptFooterMessage}</p>
                        <p>${receiptFooterNote}</p>
                        <p>Printed on ${new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' })}</p>
                    </div>
                </div>
            </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 250);
    }

    function updateTermOptions() {
        const yearSelect = document.getElementById('modal_year');
        const termSelect = document.getElementById('modal_results_status_id');
        const selectedYear = yearSelect.value;
        
        // Show/hide term options based on selected year
        Array.from(termSelect.options).forEach(option => {
            if (option.value === '') {
                option.style.display = '';
            } else {
                const optionYear = option.dataset.year;
                option.style.display = (selectedYear === '' || optionYear === selectedYear) ? '' : 'none';
            }
        });
        
        // Reset term selection if current selection is hidden
        if (termSelect.value && termSelect.options[termSelect.selectedIndex].style.display === 'none') {
            termSelect.value = '';
        }
        
        // Auto-select first visible term if year is selected
        if (selectedYear && !termSelect.value) {
            for (let option of termSelect.options) {
                if (option.value && option.dataset.year === selectedYear && option.style.display !== 'none') {
                    termSelect.value = option.value;
                    break;
                }
            }
        }
        
        updateFeeTypes();
    }

    function updateFeeTypes() {
        const termSelect = document.getElementById('modal_results_status_id');
        const selectedOption = termSelect.options[termSelect.selectedIndex];
        const feeTypesContainer = document.getElementById('fee_types_container');
        const feeFilterInfo = document.getElementById('fee_filter_info');
        
        if (!feeTypesContainer) return;
        
        // Get selected student's type, curriculum, new student status, and class numeric
        const studentSelect = document.getElementById('modal_student_id');
        const selectedStudent = studentSelect.options[studentSelect.selectedIndex];
        
        // Check if student is selected
        if (!selectedStudent || !selectedStudent.value) {
            feeTypesContainer.innerHTML = '<p class="text-gray-500 text-center py-4">Select a student and term to see applicable fees</p>';
            if (feeFilterInfo) {
                feeFilterInfo.textContent = 'Fee types will be filtered based on selected student\'s type, curriculum, and class level.';
            }
            return;
        }
        
        // Get student attributes (normalize to lowercase for comparison)
        const studentType = (selectedStudent.dataset.studentType || 'day').toLowerCase();
        const curriculumType = (selectedStudent.dataset.curriculumType || 'zimsec').toLowerCase();
        const isNewStudent = parseInt(selectedStudent.dataset.isNewStudent) === 1;
        const classNumeric = parseInt(selectedStudent.dataset.classNumeric) || null;
        
        // Debug logging
        console.log('Student Filter:', {studentType, curriculumType, isNewStudent, classNumeric});
        
        // Update filter info display
        if (feeFilterInfo) {
            const typeLabel = studentType === 'boarding' ? 'Boarding' : 'Day';
            const currLabel = curriculumType === 'cambridge' ? 'Cambridge' : 'ZIMSEC';
            const statusLabel = isNewStudent ? 'New Student' : 'Existing Student';
            feeFilterInfo.innerHTML = `Filtering for: <strong class="text-blue-800">${typeLabel}</strong> | <strong class="text-blue-800">${currLabel}</strong> | <strong class="text-blue-800">${statusLabel}</strong>` + 
                (classNumeric ? ` | Class: <strong class="text-blue-800">${classNumeric}</strong>` : '');
        }
        
        if (selectedOption.value && selectedOption.dataset.fees) {
            try {
                const fees = JSON.parse(selectedOption.dataset.fees);
                console.log('All fees from term:', fees);
                
                // Filter fees based on student type, curriculum type, new student status, AND level group
                const filteredFees = fees.filter(fee => {
                    // Normalize fee attributes for comparison
                    const feeStudentType = (fee.student_type || '').toLowerCase();
                    const feeCurriculumType = (fee.curriculum_type || '').toLowerCase();
                    
                    // Check student type (day/boarding) - MUST match exactly
                    // Fee must have student_type and it must match
                    let typeMatch = feeStudentType === studentType;
                    
                    // Check curriculum type (zimsec/cambridge) - MUST match exactly
                    // Fee must have curriculum_type and it must match
                    let curriculumMatch = feeCurriculumType === curriculumType;
                    
                    // Check new student status - STRICT matching
                    // New students should ONLY see new student fees
                    // Existing students should ONLY see existing student fees
                    let newStudentMatch = false;
                    const feeIsForNewStudent = fee.is_for_new_student === true || fee.is_for_new_student === 1 || fee.is_for_new_student === '1';
                    if (isNewStudent && feeIsForNewStudent) {
                        newStudentMatch = true; // New student sees new student fees only
                    } else if (!isNewStudent && !feeIsForNewStudent) {
                        newStudentMatch = true; // Existing student sees existing student fees only
                    }
                    
                    // Check level group - if fee has a level group, student's class must be in range
                    let levelGroupMatch = true;
                    if (fee.fee_level_group && classNumeric) {
                        const minClass = parseInt(fee.fee_level_group.min_class_numeric);
                        const maxClass = parseInt(fee.fee_level_group.max_class_numeric);
                        levelGroupMatch = classNumeric >= minClass && classNumeric <= maxClass;
                    }
                    
                    const match = typeMatch && curriculumMatch && newStudentMatch && levelGroupMatch;
                    console.log('Fee:', fee.fee_type?.name, '| FeeType:', feeStudentType, '| FeeCurr:', feeCurriculumType, '| StudentType:', studentType, '| StudentCurr:', curriculumType, '| Match:', match);
                    
                    return match;
                });
                
                // Clear existing fee checkboxes
                feeTypesContainer.innerHTML = '';
                
                if (filteredFees.length === 0) {
                    const typeLabel = studentType === 'boarding' ? 'Boarding' : 'Day';
                    const currLabel = curriculumType === 'cambridge' ? 'Cambridge' : 'ZIMSEC';
                    feeTypesContainer.innerHTML = '<p class="text-gray-500 text-center py-4">No fee types available for ' + typeLabel + ' / ' + currLabel + ' students in selected term. Please check if fee structures have been set up for this student category.</p>';
                    return;
                }
                
                // Fetch payment history for this student and term
                fetch(`{{ route('finance.student-fee-payments') }}?student_id=${selectedStudent.value}&results_status_id=${selectedOption.value}`)
                    .then(response => response.json())
                    .then(feePayments => {
                        console.log('Fee payments:', feePayments);
                        
                        // Create new fee checkboxes with payment info
                        filteredFees.forEach(fee => {
                            const levelGroupName = fee.fee_level_group ? fee.fee_level_group.name : '';
                            const amountPaid = feePayments[fee.id] || 0;
                            const remaining = parseFloat(fee.amount) - parseFloat(amountPaid);
                            const isPartiallyPaid = amountPaid > 0 && remaining > 0;
                            const isFullyPaid = remaining <= 0;
                            
                            let paymentStatusHtml = '';
                            if (isFullyPaid) {
                                paymentStatusHtml = '<span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Fully Paid</span>';
                            } else if (isPartiallyPaid) {
                                paymentStatusHtml = `<span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Partial: $${parseFloat(amountPaid).toFixed(2)} paid</span>`;
                            }
                            
                            const feeHtml = `
                                <div class="bg-white border ${isPartiallyPaid ? 'border-yellow-300 bg-yellow-50' : isFullyPaid ? 'border-green-300 bg-green-50' : 'border-gray-200'} rounded-lg p-3 mb-2">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center flex-1">
                                            <input type="checkbox" 
                                                   id="fee_${fee.id}"
                                                   class="fee-checkbox h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                                   data-fee-id="${fee.id}"
                                                   data-full-amount="${remaining}"
                                                   data-amount-paid="${amountPaid}"
                                                   ${isFullyPaid ? 'disabled' : ''}
                                                   onchange="toggleFeeAmount(${fee.id})">
                                            <label for="fee_${fee.id}" class="ml-3 text-sm font-medium text-gray-700 flex-1">
                                                ${fee.fee_type ? fee.fee_type.name : 'Fee'}
                                                ${levelGroupName ? '<span class="ml-1 text-xs text-gray-500">(' + levelGroupName + ')</span>' : ''}
                                                ${fee.is_for_new_student === true || fee.is_for_new_student === 1 || fee.is_for_new_student === '1' ? '<span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">New Student</span>' : ''}
                                                ${paymentStatusHtml}
                                            </label>
                                            <span class="text-sm font-semibold ${isFullyPaid ? 'text-green-600' : isPartiallyPaid ? 'text-yellow-600' : 'text-gray-600'}">
                                                ${isPartiallyPaid || isFullyPaid ? 'Remaining: ' : ''}$${parseFloat(remaining).toFixed(2)}
                                            </span>
                                        </div>
                                    </div>
                                    ${isPartiallyPaid ? `
                                    <div class="ml-7 mt-2 text-xs text-gray-600">
                                        <div class="flex justify-between items-center">
                                            <span>Original Amount: <strong>$${parseFloat(fee.amount).toFixed(2)}</strong></span>
                                            <span>Paid: <strong class="text-green-600">$${parseFloat(amountPaid).toFixed(2)}</strong></span>
                                            <span>Balance: <strong class="text-red-600">$${parseFloat(remaining).toFixed(2)}</strong></span>
                                        </div>
                                    </div>
                                    ` : ''}
                                    <div class="ml-7 mt-2 hidden" id="amount_input_${fee.id}">
                                        <div class="flex items-center gap-3">
                                            <div class="flex-1">
                                                <label class="block text-xs text-gray-600 mb-1">Amount to Pay</label>
                                                <input type="number" 
                                                       name="fee_amounts[${fee.id}]"
                                                       id="amount_${fee.id}"
                                                       class="fee-amount-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"
                                                       placeholder="Enter amount"
                                                       min="0"
                                                       max="${remaining}"
                                                       step="0.01"
                                                       value="${remaining}"
                                                       disabled
                                                       oninput="updatePaymentCalculation()">
                                            </div>
                                            <button type="button" 
                                                    onclick="setFullAmount(${fee.id}, ${remaining})"
                                                    class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-xs font-medium mt-5">
                                                Full Amount
                                            </button>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">Maximum: $${parseFloat(remaining).toFixed(2)}</p>
                                    </div>
                                </div>
                            `;
                            feeTypesContainer.insertAdjacentHTML('beforeend', feeHtml);
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching payment history:', error);
                        // Fallback to showing fees without payment info
                        filteredFees.forEach(fee => {
                            const levelGroupName = fee.fee_level_group ? fee.fee_level_group.name : '';
                            const feeHtml = `
                                <div class="bg-white border border-gray-200 rounded-lg p-3 mb-2">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center flex-1">
                                            <input type="checkbox" 
                                                   id="fee_${fee.id}"
                                                   class="fee-checkbox h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                                   data-fee-id="${fee.id}"
                                                   data-full-amount="${fee.amount}"
                                                   onchange="toggleFeeAmount(${fee.id})">
                                            <label for="fee_${fee.id}" class="ml-3 text-sm font-medium text-gray-700 flex-1">
                                                ${fee.fee_type ? fee.fee_type.name : 'Fee'}
                                                ${levelGroupName ? '<span class="ml-1 text-xs text-gray-500">(' + levelGroupName + ')</span>' : ''}
                                                ${fee.is_for_new_student === true || fee.is_for_new_student === 1 || fee.is_for_new_student === '1' ? '<span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">New Student</span>' : ''}
                                            </label>
                                            <span class="text-sm font-semibold text-gray-600">$${parseFloat(fee.amount).toFixed(2)}</span>
                                        </div>
                                    </div>
                                    <div class="ml-7 mt-2 hidden" id="amount_input_${fee.id}">
                                        <div class="flex items-center gap-3">
                                            <div class="flex-1">
                                                <label class="block text-xs text-gray-600 mb-1">Amount to Pay</label>
                                                <input type="number" 
                                                       name="fee_amounts[${fee.id}]"
                                                       id="amount_${fee.id}"
                                                       class="fee-amount-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"
                                                       placeholder="Enter amount"
                                                       min="0"
                                                       max="${fee.amount}"
                                                       step="0.01"
                                                       value="${fee.amount}"
                                                       disabled
                                                       oninput="updatePaymentCalculation()">
                                            </div>
                                            <button type="button" 
                                                    onclick="setFullAmount(${fee.id}, ${fee.amount})"
                                                    class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-xs font-medium mt-5">
                                                Full Amount
                                            </button>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">Maximum: $${parseFloat(fee.amount).toFixed(2)}</p>
                                    </div>
                                </div>
                            `;
                            feeTypesContainer.insertAdjacentHTML('beforeend', feeHtml);
                        });
                    });
            } catch (e) {
                console.error('Error parsing fees:', e);
            }
        }
        
        updatePaymentCalculation();
    }

    function nextStep(step) {
        // Hide any existing alerts
        hideAlert();
        
        // Validate current step before proceeding
        if (step === 1) {
            const termId = document.getElementById('modal_results_status_id').value;
            if (!termId) {
                showAlert('Please select a term before proceeding.');
                return;
            }
            const studentId = document.getElementById('modal_student_id').value;
            if (!studentId) {
                showAlert('Please select a student before proceeding.');
                return;
            }
            // Ensure student info section is visible when moving to Step 2
            document.getElementById('student_info_section').style.display = 'block';
        } else if (step === 2) {
            const checkedFees = document.querySelectorAll('.fee-checkbox:checked');
            if (checkedFees.length === 0) {
                showAlert('Please select at least one fee type to proceed.');
                return;
            }
            // Auto-generate reference number when moving to Step 3
            if (!document.getElementById('reference_number').value) {
                generateReferenceNumber();
            }
        } else if (step === 3) {
            const paymentMethod = document.querySelector('select[name="payment_method"]').value;
            if (!paymentMethod) {
                showAlert('Please select a payment method before proceeding.');
                return;
            }
            const receiptNumber = document.getElementById('receipt_number').value.trim();
            if (!receiptNumber) {
                showAlert('Please enter the receipt number from the receipt book before proceeding.');
                return;
            }
        }
        
        showStep(step + 1);
    }

    function prevStep(step) {
        showStep(step - 1);
    }

    function closePaymentModal() {
        document.getElementById('paymentModal').classList.add('hidden');
        document.getElementById('paymentForm').reset();
        selectedPaymentMethod = 'cash';
    }

    function selectPaymentMethod(method) {
        selectedPaymentMethod = method;
        
        // Update button styles
        document.querySelectorAll('.payment-method-btn').forEach(btn => {
            btn.classList.remove('border-blue-500', 'bg-blue-50');
            btn.classList.add('border-gray-300');
        });
        
        if (method === 'cash') {
            document.getElementById('cashMethodBtn').classList.remove('border-gray-300');
            document.getElementById('cashMethodBtn').classList.add('border-blue-500', 'bg-blue-50');
        } else if (method === 'paynow') {
            document.getElementById('paynowMethodBtn').classList.remove('border-gray-300');
            document.getElementById('paynowMethodBtn').classList.add('border-blue-500', 'bg-blue-50');
        }
    }

    function closePaynowModal() {
        document.getElementById('paynowModal').classList.add('hidden');
        document.getElementById('paynowForm').reset();
        document.getElementById('paynow_alert').classList.add('hidden');
    }

    function openPaynowModal() {
        // Set student info
        document.getElementById('paynow_student_name').textContent = currentStudentName;
        document.getElementById('paynow_amount').textContent = '$' + selectedFeesTotal.toFixed(2);
        
        // Show modal
        document.getElementById('paynowModal').classList.remove('hidden');
    }

    // Handle Paynow form submission
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('paynowForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const phone = document.getElementById('paynow_phone').value;
            const submitBtn = document.getElementById('paynowSubmitBtn');
            const alertDiv = document.getElementById('paynow_alert');
            
            // Disable button
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<svg class="w-5 h-5 inline mr-2 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Processing...';
            
            // Prepare payment data
            const formData = new FormData(document.getElementById('paymentForm'));
            formData.append('payment_method', 'paynow');
            formData.append('paynow_phone', phone);
            
            fetch('{{ route("finance.payments.paynow") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Store receipt data
                    lastReceiptData = data.receipt;
                    
                    // Close Paynow modal
                    closePaynowModal();
                    
                    // Close payment modal
                    closePaymentModal();
                    
                    // Show success message
                    alertDiv.classList.remove('hidden', 'bg-red-100', 'text-red-700');
                    alertDiv.classList.add('bg-green-100', 'text-green-700');
                    alertDiv.textContent = data.message;
                    
                    // Update success screen
                    document.getElementById('success_message').textContent = data.message;
                    document.getElementById('success_amount').textContent = '$' + parseFloat(data.total_paid).toFixed(2);
                    document.getElementById('success_remaining').textContent = '$' + parseFloat(data.remaining_balance).toFixed(2);
                    
                    // Show step 5 (success)
                    showStep(5);
                    
                    // Reload page after 3 seconds
                    setTimeout(() => location.reload(), 3000);
                } else {
                    alertDiv.classList.remove('hidden', 'bg-green-100', 'text-green-700');
                    alertDiv.classList.add('bg-red-100', 'text-red-700');
                    alertDiv.textContent = data.message || 'Paynow payment failed';
                    
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg> Initiate Paynow Payment';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alertDiv.classList.remove('hidden', 'bg-green-100', 'text-green-700');
                alertDiv.classList.add('bg-red-100', 'text-red-700');
                alertDiv.textContent = 'An error occurred. Please try again.';
                
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg> Initiate Paynow Payment';
            });
        });
    });

    function submitPayment() {
        // Check if Paynow is selected
        if (selectedPaymentMethod === 'paynow') {
            // Validate that fees are selected
            if (selectedFeesTotal <= 0) {
                showAlert('Please select fees to pay before proceeding with Paynow payment.');
                return;
            }
            
            // Open Paynow modal
            openPaynowModal();
            return;
        }
        
        // Regular cash/card payment
        const form = document.getElementById('paymentForm');
        const formData = new FormData(form);
        const submitBtn = document.getElementById('submitPaymentBtn');
        
        // Disable button and show loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<svg class="w-5 h-5 inline mr-2 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Processing...';
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Store receipt data for printing
                lastReceiptData = data.receipt;
                
                // Update success screen
                document.getElementById('success_message').textContent = data.message;
                document.getElementById('success_amount').textContent = '$' + parseFloat(data.total_paid).toFixed(2);
                document.getElementById('success_remaining').textContent = '$' + parseFloat(data.remaining_balance).toFixed(2);
                
                // Show step 5 (success)
                showStep(5);
            } else {
                showAlert(data.message || 'Error recording payment');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Record Payment';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred. Please try again.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Record Payment';
        });
    }

    function printNewReceipt() {
        if (!lastReceiptData) return;
        
        const receipt = lastReceiptData;
        const receiptNo = 'RCP-' + String(receipt.id).padStart(6, '0');
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Receipt - ${receiptNo}</title>
                <style>
                    @page { size: 58mm auto; margin: 0; }
                    * { margin: 0; padding: 0; box-sizing: border-box; }
                    body { font-family: 'Courier New', monospace; font-size: 9px; width: 58mm; max-width: 58mm; margin: 0 auto; padding: 2mm; background: #fff; }
                    .receipt { width: 100%; border: 1px dashed #000; padding: 3mm; }
                    .header { text-align: center; border-bottom: 1px dashed #000; padding-bottom: 3mm; margin-bottom: 3mm; }
                    .header h1 { font-size: 12px; margin-bottom: 2px; font-weight: bold; }
                    .header p { font-size: 8px; }
                    .receipt-no { font-size: 10px; font-weight: bold; margin: 3px 0; }
                    .details { margin: 3mm 0; }
                    .row { display: flex; justify-content: space-between; padding: 2px 0; border-bottom: 1px dotted #ccc; font-size: 8px; }
                    .row:last-child { border-bottom: none; }
                    .label { font-size: 8px; }
                    .value { font-size: 8px; font-weight: 600; text-align: right; max-width: 55%; }
                    .amount-section { border: 1px solid #000; padding: 3mm; margin: 3mm 0; text-align: center; }
                    .amount-label { font-size: 8px; text-transform: uppercase; }
                    .amount-value { font-size: 14px; font-weight: bold; }
                    .footer { text-align: center; margin-top: 3mm; padding-top: 3mm; border-top: 1px dashed #000; }
                    .footer p { font-size: 7px; margin: 1px 0; }
                    .thank-you { font-weight: bold; font-size: 9px; margin-bottom: 2px; }
                    @media print { html, body { width: 58mm; max-width: 58mm; } body { padding: 1mm; } }
                </style>
            </head>
            <body>
                <div class="receipt">
                    <div class="header">
                        <h1>${receiptSchoolShortName}</h1>
                        <p>${receiptSchoolFullName}</p>
                        <p>Payment Receipt</p>
                        <div class="receipt-no">${receiptNo}</div>
                    </div>
                    <div class="details">
                        <div class="row">
                            <span class="label">Student Name</span>
                            <span class="value">${receipt.student_name}</span>
                        </div>
                        <div class="row">
                            <span class="label">Payment Date</span>
                            <span class="value">${receipt.date}</span>
                        </div>
                        <div class="row">
                            <span class="label">Term</span>
                            <span class="value">${receipt.term || '-'}</span>
                        </div>
                        <div class="row">
                            <span class="label">Fees Paid For</span>
                            <span class="value">${receipt.fees}</span>
                        </div>
                        <div class="row">
                            <span class="label">Payment Method</span>
                            <span class="value">${receipt.method || '-'}</span>
                        </div>
                        <div class="row">
                            <span class="label">Reference No.</span>
                            <span class="value">${receipt.reference || '-'}</span>
                        </div>
                        <div class="row">
                            <span class="label">Receipt No.</span>
                            <span class="value">${receipt.receipt_number || '-'}</span>
                        </div>
                    </div>
                    <div class="amount-section">
                        <div class="amount-label">Amount Paid</div>
                        <div class="amount-value">$${parseFloat(receipt.amount).toFixed(2)}</div>
                    </div>
                    <div class="footer">
                        <p class="thank-you">${receiptFooterMessage}</p>
                        <p>${receiptFooterNote}</p>
                        <p>Printed on ${new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' })}</p>
                    </div>
                </div>
            </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 250);
    }

    function recordAnotherPayment() {
        // Reset form and go back to step 1
        document.getElementById('paymentForm').reset();
        lastReceiptData = null;
        currentStep = 1;
        showStep(1);
        
        // Re-enable submit button
        const submitBtn = document.getElementById('submitPaymentBtn');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Record Payment';
    }

    // Bluetooth Thermal Printer Support
    let bluetoothDevice = null;
    let bluetoothCharacteristic = null;

    async function printViaBluetooth() {
        if (!lastReceiptData) {
            alert('No receipt data available');
            return;
        }

        // Check if Web Bluetooth is supported
        if (!navigator.bluetooth) {
            alert('Web Bluetooth is not supported in this browser. Please use Chrome or Edge on HTTPS.');
            return;
        }

        try {
            // Request Bluetooth device
            if (!bluetoothDevice) {
                bluetoothDevice = await navigator.bluetooth.requestDevice({
                    filters: [{ services: ['000018f0-0000-1000-8000-00805f9b34fb'] }],
                    optionalServices: ['000018f0-0000-1000-8000-00805f9b34fb']
                });
            }

            // Connect to GATT Server
            const server = await bluetoothDevice.gatt.connect();
            const service = await server.getPrimaryService('000018f0-0000-1000-8000-00805f9b34fb');
            bluetoothCharacteristic = await service.getCharacteristic('00002af1-0000-1000-8000-00805f9b34fb');

            // Generate ESC/POS commands for thermal printer
            const receipt = lastReceiptData;
            const receiptNo = 'RCP-' + String(receipt.id).padStart(6, '0');
            
            const escPosCommands = generateESCPOS(receipt, receiptNo);
            
            // Send to printer
            await bluetoothCharacteristic.writeValue(escPosCommands);
            
            alert('Receipt sent to Bluetooth printer!');
        } catch (error) {
            console.error('Bluetooth printing error:', error);
            alert('Failed to print via Bluetooth: ' + error.message);
        }
    }

    function generateESCPOS(receipt, receiptNo) {
        // ESC/POS Commands
        const ESC = 0x1B;
        const GS = 0x1D;
        const LF = 0x0A;
        const commands = [];

        // Helper functions
        function addText(text) {
            for (let i = 0; i < text.length; i++) {
                commands.push(text.charCodeAt(i));
            }
        }

        function addLine(text = '') {
            addText(text);
            commands.push(LF);
        }

        function center() {
            commands.push(ESC, 0x61, 0x01); // Center align
        }

        function left() {
            commands.push(ESC, 0x61, 0x00); // Left align
        }

        function bold(on = true) {
            commands.push(ESC, 0x45, on ? 0x01 : 0x00);
        }

        function doubleHeight(on = true) {
            commands.push(GS, 0x21, on ? 0x11 : 0x00);
        }

        function cut() {
            commands.push(GS, 0x56, 0x00); // Full cut
        }

        // Initialize printer
        commands.push(ESC, 0x40); // Initialize

        // Header
        center();
        bold(true);
        doubleHeight(true);
        addLine(receiptSchoolShortName);
        doubleHeight(false);
        addLine(receiptSchoolFullName);
        bold(false);
        addLine('Payment Receipt');
        addLine('');
        
        // Receipt Number
        bold(true);
        addLine(receiptNo);
        bold(false);
        addLine('================================');
        
        // Details
        left();
        addLine('');
        addLine('Student: ' + receipt.student_name);
        addLine('Date: ' + receipt.date);
        addLine('Term: ' + (receipt.term || '-'));
        addLine('');
        addLine('Fees Paid For:');
        addLine(receipt.fees);
        addLine('');
        addLine('Payment Method: ' + (receipt.method || '-'));
        addLine('Reference: ' + (receipt.reference || '-'));
        addLine('Receipt No: ' + (receipt.receipt_number || '-'));
        addLine('');
        addLine('================================');
        
        // Amount
        center();
        bold(true);
        doubleHeight(true);
        addLine('AMOUNT PAID');
        addLine('$' + parseFloat(receipt.amount).toFixed(2));
        doubleHeight(false);
        bold(false);
        addLine('================================');
        
        // Footer
        addLine('');
        addLine(receiptFooterMessage);
        addLine('');
        left();
        const now = new Date();
        addLine('Printed: ' + now.toLocaleString());
        addLine('');
        addLine('');
        addLine('');

        // Cut paper
        cut();

        return new Uint8Array(commands);
    }

    function filterStudents() {
        const classFilter = document.getElementById('modal_class_filter').value;
        const searchText = document.getElementById('modal_student_search').value.toLowerCase();
        const studentSelect = document.getElementById('modal_student_id');
        const options = studentSelect.querySelectorAll('.student-option');
        
        let visibleCount = 0;
        
        options.forEach(option => {
            const studentName = option.dataset.name.toLowerCase();
            const studentRoll = option.dataset.roll.toLowerCase();
            const studentClass = option.dataset.class;
            
            // Check class filter
            const classMatch = !classFilter || studentClass === classFilter;
            
            // Check search text
            const searchMatch = !searchText || 
                               studentName.includes(searchText) || 
                               studentRoll.includes(searchText);
            
            // Show or hide option
            if (classMatch && searchMatch) {
                option.style.display = '';
                visibleCount++;
            } else {
                option.style.display = 'none';
            }
        });
        
        // Update count
        document.getElementById('student_count').textContent = visibleCount + ' student' + (visibleCount !== 1 ? 's' : '') + ' found';
        
        // Reset selection if current selection is hidden
        if (studentSelect.value) {
            const selectedOption = studentSelect.options[studentSelect.selectedIndex];
            if (selectedOption.style.display === 'none') {
                studentSelect.value = '';
                updateStudentBalance();
            }
        }
    }

    let totalFees = 0;
    let amountAlreadyPaid = 0;

    function updateStudentBalance() {
        const studentSelect = document.getElementById('modal_student_id');
        const selectedOption = studentSelect.options[studentSelect.selectedIndex];
        
        if (selectedOption.value) {
            const studentName = selectedOption.dataset.name;
            totalFees = parseFloat(selectedOption.dataset.totalFees) || 0;
            amountAlreadyPaid = parseFloat(selectedOption.dataset.amountPaid) || 0;
            const balance = totalFees - amountAlreadyPaid;
            const isNewStudent = parseInt(selectedOption.dataset.isNewStudent) === 1;
            
            // Update student info with new student badge
            const newStudentBadge = isNewStudent 
                ? '<span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">New Student</span>'
                : '<span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">Existing Student</span>';
            document.getElementById('modal_student_name').innerHTML = studentName + newStudentBadge;
            document.getElementById('modal_balance').textContent = '$' + balance.toFixed(2);
            document.getElementById('modal_original_balance').textContent = '$' + balance.toFixed(2);
            currentBalance = balance;
            
            // Show student info section
            document.getElementById('student_info_section').style.display = 'block';
            
            // Update fee types based on student type
            updateFeeTypes();
            
            // Reset checkboxes and calculations
            document.querySelectorAll('.fee-checkbox').forEach(cb => {
                cb.checked = false;
                const feeId = cb.dataset.feeId;
                const amountInput = document.getElementById('amount_input_' + feeId);
                const amountField = document.getElementById('amount_' + feeId);
                if (amountInput) {
                    amountInput.classList.add('hidden');
                }
                if (amountField) {
                    amountField.disabled = true;
                    amountField.value = '';
                }
            });
            updatePaymentCalculation();
        } else {
            // Hide student info section
            document.getElementById('student_info_section').style.display = 'none';
        }
    }

    function toggleFeeAmount(feeId) {
        const checkbox = document.getElementById('fee_' + feeId);
        const amountInput = document.getElementById('amount_input_' + feeId);
        const amountField = document.getElementById('amount_' + feeId);
        
        if (checkbox.checked) {
            amountInput.classList.remove('hidden');
            amountField.disabled = false;
            // Set default to full amount
            const fullAmount = parseFloat(checkbox.dataset.fullAmount);
            amountField.value = fullAmount;
        } else {
            amountInput.classList.add('hidden');
            amountField.disabled = true;
            amountField.value = '';
        }
        
        updatePaymentCalculation();
    }

    function setFullAmount(feeId, amount) {
        document.getElementById('amount_' + feeId).value = amount;
        updatePaymentCalculation();
    }

    function updatePaymentCalculation() {
        let selectedTotal = 0;
        
        // Calculate total from checked fees with their custom amounts
        document.querySelectorAll('.fee-checkbox:checked').forEach(checkbox => {
            const feeId = checkbox.dataset.feeId;
            const amountInput = document.getElementById('amount_' + feeId);
            const amount = parseFloat(amountInput.value) || 0;
            selectedTotal += amount;
        });
        
        const remainingBalance = currentBalance - selectedTotal;
        
        // Update outstanding balance in Step 2 (student info section)
        const balanceElement = document.getElementById('modal_balance');
        const balanceIndicator = document.getElementById('balance_change_indicator');
        
        if (balanceElement) {
            balanceElement.textContent = '$' + remainingBalance.toFixed(2);
            
            // Update color based on remaining balance
            if (remainingBalance < 0) {
                balanceElement.className = 'text-2xl font-bold text-red-600';
                balanceIndicator.textContent = 'Overpayment!';
                balanceIndicator.className = 'text-xs text-red-600 mt-1 font-semibold';
            } else if (remainingBalance === 0) {
                balanceElement.className = 'text-2xl font-bold text-green-600';
                balanceIndicator.textContent = 'Fully Paid ✓';
                balanceIndicator.className = 'text-xs text-green-600 mt-1 font-semibold';
            } else if (selectedTotal > 0) {
                balanceElement.className = 'text-2xl font-bold text-orange-600';
                balanceIndicator.textContent = 'Paying $' + selectedTotal.toFixed(2);
                balanceIndicator.className = 'text-xs text-blue-600 mt-1 font-semibold';
            } else {
                balanceElement.className = 'text-2xl font-bold text-red-600';
                balanceIndicator.textContent = '';
            }
        }
        
        // Update Step 4 summary
        document.getElementById('selected_total').textContent = '$' + selectedTotal.toFixed(2);
        document.getElementById('current_balance').textContent = '$' + currentBalance.toFixed(2);
        document.getElementById('remaining_balance').textContent = '$' + remainingBalance.toFixed(2);
        
        // Update global selectedFeesTotal for Paynow
        selectedFeesTotal = selectedTotal;
        
        // Change color based on remaining balance in Step 4
        const remainingElement = document.getElementById('remaining_balance');
        if (remainingBalance < 0) {
            remainingElement.classList.remove('text-blue-600', 'text-green-600');
            remainingElement.classList.add('text-red-600');
        } else if (remainingBalance === 0) {
            remainingElement.classList.remove('text-blue-600', 'text-red-600');
            remainingElement.classList.add('text-green-600');
        } else {
            remainingElement.classList.remove('text-blue-600', 'text-green-600');
            remainingElement.classList.add('text-red-600');
        }
    }

    // Reference number counter (stored in localStorage for persistence across page loads)
    let paymentCounter = parseInt(localStorage.getItem('paymentCounter') || '0');
    
    function generateReferenceNumber() {
        const studentSelect = document.getElementById('modal_student_id');
        const selectedStudent = studentSelect.options[studentSelect.selectedIndex];
        
        if (!selectedStudent || !selectedStudent.value) {
            alert('Please select a student first');
            return;
        }
        
        // Get current date and time
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        
        // Get class name (abbreviated)
        const classId = selectedStudent.dataset.class || '';
        const studentName = selectedStudent.dataset.name || '';
        // Extract class from the option text or use class ID
        const optionText = selectedStudent.textContent;
        const classMatch = optionText.match(/- ([^-]+) -/);
        let className = classMatch ? classMatch[1].trim().replace(/\s+/g, '') : classId;
        // Abbreviate class name (e.g., "Form 1A" -> "F1A")
        className = className.replace(/Form\s*/i, 'F').replace(/Grade\s*/i, 'G').substring(0, 5);
        
        // Increment counter
        paymentCounter++;
        localStorage.setItem('paymentCounter', paymentCounter.toString());
        const counterStr = String(paymentCounter).padStart(4, '0');
        
        // Generate reference: PAY-YYYYMMDD-CLASS-HHMMSS-XXXX
        const reference = `PAY-${year}${month}${day}-${className}-${hours}${minutes}${seconds}-${counterStr}`;
        
        document.getElementById('reference_number').value = reference;
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize payment method selection (default to cash)
        selectPaymentMethod('cash');
        
        const searchInput = document.getElementById('searchStudent');
        const filterClass = document.getElementById('filterClass');
        const filterStatus = document.getElementById('filterStatus');
        
        // Auto-generate reference number when modal opens
        // Generate reference number automatically when moving to step 3
        
        // Add event listeners for fee checkboxes
        document.querySelectorAll('.fee-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updatePaymentCalculation);
        });
        
        // Close modal when clicking outside
        document.getElementById('paymentModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePaymentModal();
            }
        });
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closePaymentModal();
            }
        });
        
        // Search input - submit form on Enter key
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('filterForm').submit();
            }
        });
    });
</script>

@endsection
