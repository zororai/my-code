@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Parents with Arrears</h1>
        <a href="{{ route('finance.parents-arrears.export', request()->query()) }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Export to Excel
        </a>
    </div>

    <!-- Filter -->
    <form method="GET" action="{{ route('finance.parents-arrears') }}" id="filterForm">
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
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
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                        Apply Filter
                    </button>
                    <a href="{{ route('finance.parents-arrears') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">
                        Clear
                    </a>
                </div>
            </div>
        </div>
    </form>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Total Students</p>
                    <p class="text-2xl font-bold text-blue-600">{{ isset($studentsWithArrears) ? $studentsWithArrears->count() : 0 }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Total Fees</p>
                    <p class="text-2xl font-bold text-purple-600">${{ number_format(isset($studentsWithArrears) ? $studentsWithArrears->sum('total_fees') : 0, 2) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Total Paid</p>
                    <p class="text-2xl font-bold text-green-600">${{ number_format(isset($studentsWithArrears) ? $studentsWithArrears->sum('total_paid') : 0, 2) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Total Arrears</p>
                    <p class="text-2xl font-bold text-red-600">${{ number_format(isset($studentsWithArrears) ? $studentsWithArrears->sum('arrears') : 0, 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Arrears Table (Student-Centric View) -->
    @if(isset($studentsWithArrears) && $studentsWithArrears->count() > 0)
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="bg-blue-600 px-6 py-3">
            <h2 class="text-lg font-semibold text-white">Student Arrears ({{ $studentsWithArrears->count() }} students)</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Class</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Parent</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Fees</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paid</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Arrears</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($studentsWithArrears as $index => $student)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $index + 1 }}</td>
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-gray-900">{{ $student->user->name ?? $student->name ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500">{{ strtoupper($student->curriculum_type ?? 'zimsec') }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $student->class->class_name ?? 'N/A' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded-full {{ ($student->student_type ?? 'day') == 'boarding' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ ucfirst($student->student_type ?? 'day') }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $student->parent_name ?? 'No Parent' }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">${{ number_format($student->total_fees ?? 0, 2) }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-green-600">${{ number_format($student->total_paid ?? 0, 2) }}</td>
                        <td class="px-4 py-3">
                            @if(($student->arrears ?? 0) > 0)
                            <span class="px-2 py-1 text-sm font-bold rounded-full bg-red-100 text-red-800">
                                ${{ number_format($student->arrears, 2) }}
                            </span>
                            @elseif(($student->total_fees ?? 0) == 0)
                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600">No fees set</span>
                            @else
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Paid</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="bg-white rounded-lg shadow p-8 mb-6 text-center">
        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
        <p class="text-lg font-medium text-gray-600">No students found</p>
        <p class="text-sm text-gray-400">There are no students in the system or no fees have been configured yet.</p>
    </div>
    @endif

    <!-- Parents Arrears Table (Legacy View) -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Parent Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Students</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance B/F</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Term</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Fees</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount Paid</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Arrears</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($parentsWithArrears as $index => $parent)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $parent->user->name ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500">{{ $parent->user->email ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $parent->phone ?? $parent->user->phone ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                @foreach($parent->filtered_students as $student)
                                    <div class="inline-flex flex-col items-start px-2 py-1 rounded-lg text-xs font-medium bg-blue-100 text-blue-800 mb-1">
                                        <span class="font-semibold">{{ $student->user->name ?? $student->name }}</span>
                                        <span class="text-blue-600">{{ $student->class->class_name ?? 'N/A' }} | {{ strtoupper($student->curriculum_type ?? 'zimsec') }}</span>
                                        @if(($student->scholarship_percentage ?? 0) > 0)
                                            <span class="text-green-600">{{ $student->scholarship_percentage }}% Scholarship</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold {{ ($parent->balance_bf ?? 0) > 0 ? 'text-orange-600' : 'text-gray-500' }}">
                            ${{ number_format($parent->balance_bf ?? 0, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-blue-600">
                            ${{ number_format($parent->current_term_fees ?? 0, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                            ${{ number_format($parent->total_fees, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">
                            ${{ number_format($parent->total_paid, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-bold rounded-full bg-red-100 text-red-800">
                                ${{ number_format($parent->arrears, 2) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button type="button" 
                                    onclick="showArrearsDetails({{ $parent->id }}, '{{ addslashes($parent->user->name ?? 'N/A') }}', {{ json_encode($parent->arrears_breakdown) }})"
                                    class="text-blue-600 hover:text-blue-900" title="View Arrears Details">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-6 py-4 text-center text-gray-500">
                            <div class="flex flex-col items-center py-8">
                                <svg class="w-16 h-16 text-green-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-lg font-medium text-gray-600">No parents with arrears found</p>
                                <p class="text-sm text-gray-400">All fees are paid up!</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                    
                    {{-- Students without parents --}}
                    @if(isset($orphanParent) && $orphanParent)
                    <tr class="hover:bg-yellow-50 bg-yellow-25">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">-</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-orange-700">
                                <svg class="w-4 h-4 inline-block mr-1 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                {{ $orphanParent->user->name }}
                            </div>
                            <div class="text-xs text-orange-500">Students not linked to any parent</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">-</td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                @foreach($orphanParent->filtered_students as $student)
                                    <div class="inline-flex flex-col items-start px-2 py-1 rounded-lg text-xs font-medium bg-orange-100 text-orange-800 mb-1">
                                        <span class="font-semibold">{{ $student->user->name ?? $student->name }}</span>
                                        <span class="text-orange-600">{{ $student->class->class_name ?? 'N/A' }} | {{ strtoupper($student->curriculum_type ?? 'zimsec') }}</span>
                                        @if(($student->scholarship_percentage ?? 0) > 0)
                                            <span class="text-green-600">{{ $student->scholarship_percentage }}% Scholarship</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-500">
                            $0.00
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-blue-600">
                            $0.00
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                            ${{ number_format($orphanParent->total_fees, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">
                            ${{ number_format($orphanParent->total_paid, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-bold rounded-full bg-orange-100 text-orange-800">
                                ${{ number_format($orphanParent->arrears, 2) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button type="button" 
                                    onclick="showArrearsDetails(0, 'Students Without Parents', {{ json_encode($orphanParent->arrears_breakdown) }})"
                                    class="text-orange-600 hover:text-orange-900" title="View Arrears Details">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Arrears Details Modal -->
<div id="arrearsModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white mb-10 max-h-[90vh] overflow-y-auto">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-900">
                    <svg class="w-6 h-6 inline-block mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Arrears Breakdown
                </h3>
                <button onclick="closeArrearsModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Parent Name -->
            <div class="mb-4 p-3 bg-red-50 rounded-lg">
                <p class="text-sm text-gray-600">Parent</p>
                <p class="text-lg font-bold text-gray-900" id="arrears_parent_name">-</p>
            </div>

            <!-- Arrears Details -->
            <div id="arrears_details_content">
                <!-- Populated by JavaScript -->
            </div>

            <!-- Total Summary -->
            <div class="mt-4 p-4 bg-gray-100 rounded-lg">
                <div class="flex justify-between items-center">
                    <span class="text-lg font-bold text-gray-700">Total Arrears:</span>
                    <span class="text-2xl font-bold text-red-600" id="arrears_total">$0.00</span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex justify-between">
                <button onclick="printArrearsDetails()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Print
                </button>
                <button onclick="closeArrearsModal()" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let currentArrearsData = null;
    let currentParentName = '';

    function showArrearsDetails(parentId, parentName, arrearsBreakdown) {
        currentParentName = parentName;
        currentArrearsData = arrearsBreakdown;
        
        document.getElementById('arrears_parent_name').textContent = parentName;
        
        const container = document.getElementById('arrears_details_content');
        container.innerHTML = '';
        
        let grandTotal = 0;
        
        // Loop through each student
        for (const studentId in arrearsBreakdown) {
            const student = arrearsBreakdown[studentId];
            grandTotal += student.total_arrears;
            
            const studentHtml = `
                <div class="mb-4 border border-gray-200 rounded-lg overflow-hidden">
                    <div class="bg-gray-50 px-4 py-3 border-b">
                        <div class="flex justify-between items-center">
                            <div>
                                <span class="font-semibold text-gray-900">${student.student_name}</span>
                                <span class="ml-2 px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">${student.class}</span>
                            </div>
                            <span class="text-red-600 font-bold">$${student.total_arrears.toFixed(2)}</span>
                        </div>
                    </div>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Term</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Fees</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Paid</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Arrears</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            ${student.terms.map(term => `
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-900">${term.term}</td>
                                    <td class="px-4 py-2 text-sm text-gray-600 text-right">$${term.fees.toFixed(2)}</td>
                                    <td class="px-4 py-2 text-sm text-green-600 text-right">$${term.paid.toFixed(2)}</td>
                                    <td class="px-4 py-2 text-sm text-red-600 font-semibold text-right">$${term.arrears.toFixed(2)}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', studentHtml);
        }
        
        document.getElementById('arrears_total').textContent = '$' + grandTotal.toFixed(2);
        document.getElementById('arrearsModal').classList.remove('hidden');
    }

    function closeArrearsModal() {
        document.getElementById('arrearsModal').classList.add('hidden');
    }

    function printArrearsDetails() {
        const printWindow = window.open('', '_blank');
        let content = '';
        let grandTotal = 0;
        
        for (const studentId in currentArrearsData) {
            const student = currentArrearsData[studentId];
            grandTotal += student.total_arrears;
            
            content += `
                <div class="student-section">
                    <div class="student-header">
                        <span class="student-name">${student.student_name}</span>
                        <span class="student-class">${student.class}</span>
                        <span class="student-total">$${student.total_arrears.toFixed(2)}</span>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Term</th>
                                <th style="text-align: right;">Fees</th>
                                <th style="text-align: right;">Paid</th>
                                <th style="text-align: right;">Arrears</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${student.terms.map(term => `
                                <tr>
                                    <td>${term.term}</td>
                                    <td style="text-align: right;">$${term.fees.toFixed(2)}</td>
                                    <td style="text-align: right; color: #059669;">$${term.paid.toFixed(2)}</td>
                                    <td style="text-align: right; color: #dc2626; font-weight: bold;">$${term.arrears.toFixed(2)}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        }
        
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Arrears Statement - ${currentParentName}</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; }
                    .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #e5e7eb; padding-bottom: 20px; }
                    .header h1 { margin: 0; color: #dc2626; font-size: 24px; }
                    .header p { margin: 5px 0; color: #6b7280; }
                    .parent-info { background: #fef2f2; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
                    .parent-info p { margin: 0; }
                    .parent-info .name { font-size: 18px; font-weight: bold; color: #1f2937; }
                    .student-section { margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; }
                    .student-header { background: #f3f4f6; padding: 12px; display: flex; justify-content: space-between; align-items: center; }
                    .student-name { font-weight: bold; color: #1f2937; }
                    .student-class { background: #dbeafe; color: #1e40af; padding: 2px 8px; border-radius: 12px; font-size: 12px; margin-left: 10px; }
                    .student-total { color: #dc2626; font-weight: bold; }
                    table { width: 100%; border-collapse: collapse; }
                    th { background: #f9fafb; padding: 10px 8px; text-align: left; font-size: 12px; text-transform: uppercase; color: #6b7280; }
                    td { padding: 10px 8px; border-bottom: 1px solid #e5e7eb; font-size: 14px; }
                    .total-section { background: #f3f4f6; padding: 15px; border-radius: 8px; margin-top: 20px; display: flex; justify-content: space-between; align-items: center; }
                    .total-label { font-size: 18px; font-weight: bold; color: #374151; }
                    .total-amount { font-size: 24px; font-weight: bold; color: #dc2626; }
                    .footer { margin-top: 30px; text-align: center; color: #9ca3af; font-size: 12px; }
                    @media print { body { padding: 0; } }
                </style>
            </head>
            <body>
                <div class="header">
                    <h1>Arrears Statement</h1>
                    <p>Generated on ${new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</p>
                </div>
                <div class="parent-info">
                    <p style="color: #6b7280; font-size: 12px;">Parent/Guardian</p>
                    <p class="name">${currentParentName}</p>
                </div>
                ${content}
                <div class="total-section">
                    <span class="total-label">Total Outstanding Arrears:</span>
                    <span class="total-amount">$${grandTotal.toFixed(2)}</span>
                </div>
                <div class="footer">
                    <p>Please settle outstanding arrears at your earliest convenience.</p>
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
</script>
@endsection
