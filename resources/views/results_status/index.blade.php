@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Academic Terms</h1>
                    <p class="mt-2 text-sm text-gray-600">Manage academic years and result periods</p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('fee_types.index') }}" class="inline-flex items-center px-5 py-3 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Fee Payment Types
                    </a>
                    <a href="{{ route('results_status.create') }}" class="inline-flex items-center px-5 py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 448 512">
                            <path d="M416 208H272V64c0-17.67-14.33-32-32-32h-32c-17.67 0-32 14.33-32 32v144H32c-17.67 0-32 14.33-32 32v32c0 17.67 14.33 32 32 32h144v144c0 17.67 14.33 32 32 32h32c17.67 0 32-14.33 32-32V304h144c17.67 0 32-14.33 32-32v-32c0-17.67-14.33-32-32-32z"/>
                        </svg>
                        Create New Term
                    </a>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative">
                {{ session('success') }}
            </div>
        @endif

        <!-- Terms Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Academic Year
                            </th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Result Period
                            </th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider" colspan="2">
                                Fee Breakdown (ZIMSEC & Cambridge)
                            </th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($resultsStatuses as $resultStatus)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-indigo-100 text-indigo-800">
                                            {{ $resultStatus->year }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900 capitalize">{{ $resultStatus->result_period }} Term</div>
                                </td>
                                <td class="px-6 py-4" colspan="2">
                                    @if($resultStatus->feeStructures && $resultStatus->feeStructures->count() > 0)
                                        <div class="space-y-3">
                                            @php 
                                                $groupedFees = $resultStatus->feeStructures->groupBy('fee_level_group_id');
                                            @endphp
                                            @foreach($groupedFees as $groupId => $fees)
                                                @php 
                                                    $group = $fees->first()->feeLevelGroup;
                                                    $zimsecFees = $fees->where('curriculum_type', 'zimsec');
                                                    $cambridgeFees = $fees->where('curriculum_type', 'cambridge');
                                                    
                                                    // Existing students
                                                    $zimsecDayExisting = $zimsecFees->where('student_type', 'day')->where('is_for_new_student', false);
                                                    $zimsecBoardingExisting = $zimsecFees->where('student_type', 'boarding')->where('is_for_new_student', false);
                                                    $cambridgeDayExisting = $cambridgeFees->where('student_type', 'day')->where('is_for_new_student', false);
                                                    $cambridgeBoardingExisting = $cambridgeFees->where('student_type', 'boarding')->where('is_for_new_student', false);
                                                    
                                                    // New students
                                                    $zimsecDayNew = $zimsecFees->where('student_type', 'day')->where('is_for_new_student', true);
                                                    $zimsecBoardingNew = $zimsecFees->where('student_type', 'boarding')->where('is_for_new_student', true);
                                                    $cambridgeDayNew = $cambridgeFees->where('student_type', 'day')->where('is_for_new_student', true);
                                                    $cambridgeBoardingNew = $cambridgeFees->where('student_type', 'boarding')->where('is_for_new_student', true);
                                                @endphp
                                                <div class="bg-gray-50 rounded-lg p-3 text-xs border border-gray-200">
                                                    <div class="font-bold text-rose-600 mb-2 pb-1 border-b border-rose-200">{{ $group->name ?? 'Unknown Group' }}</div>
                                                    <div class="grid grid-cols-2 gap-4">
                                                        <!-- ZIMSEC Column -->
                                                        <div class="bg-indigo-50 rounded p-2 border border-indigo-200">
                                                            <div class="font-bold text-indigo-700 mb-2 text-center">ZIMSEC</div>
                                                            
                                                            <!-- Existing Students Section -->
                                                            <div class="mb-2 p-1 bg-indigo-100 rounded">
                                                                <div class="text-center text-indigo-800 font-semibold text-xs mb-1">Existing Students</div>
                                                                <!-- Day Scholar Existing -->
                                                                <div class="mb-1 bg-white rounded p-2">
                                                                    <div class="font-semibold text-blue-600 mb-1">Day Scholar</div>
                                                                    @if($zimsecDayExisting->sum('amount') > 0)
                                                                        @foreach($zimsecDayExisting as $fee)
                                                                            @if($fee->amount > 0)
                                                                            <div class="flex justify-between text-gray-600">
                                                                                <span>{{ $fee->feeType->name ?? 'Fee' }}</span>
                                                                                <span>${{ number_format($fee->amount, 2) }}</span>
                                                                            </div>
                                                                            @endif
                                                                        @endforeach
                                                                        <div class="flex justify-between font-bold text-blue-700 border-t border-blue-200 mt-1 pt-1">
                                                                            <span>Total</span>
                                                                            <span>${{ number_format($zimsecDayExisting->sum('amount'), 2) }}</span>
                                                                        </div>
                                                                    @else
                                                                        <span class="text-gray-400">-</span>
                                                                    @endif
                                                                </div>
                                                                <!-- Boarding Existing -->
                                                                <div class="bg-white rounded p-2">
                                                                    <div class="font-semibold text-green-600 mb-1">Boarding</div>
                                                                    @if($zimsecBoardingExisting->sum('amount') > 0)
                                                                        @foreach($zimsecBoardingExisting as $fee)
                                                                            @if($fee->amount > 0)
                                                                            <div class="flex justify-between text-gray-600">
                                                                                <span>{{ $fee->feeType->name ?? 'Fee' }}</span>
                                                                                <span>${{ number_format($fee->amount, 2) }}</span>
                                                                            </div>
                                                                            @endif
                                                                        @endforeach
                                                                        <div class="flex justify-between font-bold text-green-700 border-t border-green-200 mt-1 pt-1">
                                                                            <span>Total</span>
                                                                            <span>${{ number_format($zimsecBoardingExisting->sum('amount'), 2) }}</span>
                                                                        </div>
                                                                    @else
                                                                        <span class="text-gray-400">-</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- New Students Section -->
                                                            <div class="p-1 bg-emerald-100 rounded">
                                                                <div class="text-center text-emerald-800 font-semibold text-xs mb-1">New Students</div>
                                                                <!-- Day Scholar New -->
                                                                <div class="mb-1 bg-white rounded p-2">
                                                                    <div class="font-semibold text-blue-600 mb-1">Day Scholar</div>
                                                                    @if($zimsecDayNew->sum('amount') > 0)
                                                                        @foreach($zimsecDayNew as $fee)
                                                                            @if($fee->amount > 0)
                                                                            <div class="flex justify-between text-gray-600">
                                                                                <span>{{ $fee->feeType->name ?? 'Fee' }}</span>
                                                                                <span>${{ number_format($fee->amount, 2) }}</span>
                                                                            </div>
                                                                            @endif
                                                                        @endforeach
                                                                        <div class="flex justify-between font-bold text-blue-700 border-t border-blue-200 mt-1 pt-1">
                                                                            <span>Total</span>
                                                                            <span>${{ number_format($zimsecDayNew->sum('amount'), 2) }}</span>
                                                                        </div>
                                                                    @else
                                                                        <span class="text-gray-400">-</span>
                                                                    @endif
                                                                </div>
                                                                <!-- Boarding New -->
                                                                <div class="bg-white rounded p-2">
                                                                    <div class="font-semibold text-green-600 mb-1">Boarding</div>
                                                                    @if($zimsecBoardingNew->sum('amount') > 0)
                                                                        @foreach($zimsecBoardingNew as $fee)
                                                                            @if($fee->amount > 0)
                                                                            <div class="flex justify-between text-gray-600">
                                                                                <span>{{ $fee->feeType->name ?? 'Fee' }}</span>
                                                                                <span>${{ number_format($fee->amount, 2) }}</span>
                                                                            </div>
                                                                            @endif
                                                                        @endforeach
                                                                        <div class="flex justify-between font-bold text-green-700 border-t border-green-200 mt-1 pt-1">
                                                                            <span>Total</span>
                                                                            <span>${{ number_format($zimsecBoardingNew->sum('amount'), 2) }}</span>
                                                                        </div>
                                                                    @else
                                                                        <span class="text-gray-400">-</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Cambridge Column -->
                                                        <div class="bg-purple-50 rounded p-2 border border-purple-200">
                                                            <div class="font-bold text-purple-700 mb-2 text-center">Cambridge</div>
                                                            
                                                            <!-- Existing Students Section -->
                                                            <div class="mb-2 p-1 bg-purple-100 rounded">
                                                                <div class="text-center text-purple-800 font-semibold text-xs mb-1">Existing Students</div>
                                                                <!-- Day Scholar Existing -->
                                                                <div class="mb-1 bg-white rounded p-2">
                                                                    <div class="font-semibold text-blue-600 mb-1">Day Scholar</div>
                                                                    @if($cambridgeDayExisting->sum('amount') > 0)
                                                                        @foreach($cambridgeDayExisting as $fee)
                                                                            @if($fee->amount > 0)
                                                                            <div class="flex justify-between text-gray-600">
                                                                                <span>{{ $fee->feeType->name ?? 'Fee' }}</span>
                                                                                <span>${{ number_format($fee->amount, 2) }}</span>
                                                                            </div>
                                                                            @endif
                                                                        @endforeach
                                                                        <div class="flex justify-between font-bold text-blue-700 border-t border-blue-200 mt-1 pt-1">
                                                                            <span>Total</span>
                                                                            <span>${{ number_format($cambridgeDayExisting->sum('amount'), 2) }}</span>
                                                                        </div>
                                                                    @else
                                                                        <span class="text-gray-400">-</span>
                                                                    @endif
                                                                </div>
                                                                <!-- Boarding Existing -->
                                                                <div class="bg-white rounded p-2">
                                                                    <div class="font-semibold text-green-600 mb-1">Boarding</div>
                                                                    @if($cambridgeBoardingExisting->sum('amount') > 0)
                                                                        @foreach($cambridgeBoardingExisting as $fee)
                                                                            @if($fee->amount > 0)
                                                                            <div class="flex justify-between text-gray-600">
                                                                                <span>{{ $fee->feeType->name ?? 'Fee' }}</span>
                                                                                <span>${{ number_format($fee->amount, 2) }}</span>
                                                                            </div>
                                                                            @endif
                                                                        @endforeach
                                                                        <div class="flex justify-between font-bold text-green-700 border-t border-green-200 mt-1 pt-1">
                                                                            <span>Total</span>
                                                                            <span>${{ number_format($cambridgeBoardingExisting->sum('amount'), 2) }}</span>
                                                                        </div>
                                                                    @else
                                                                        <span class="text-gray-400">-</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- New Students Section -->
                                                            <div class="p-1 bg-pink-100 rounded">
                                                                <div class="text-center text-pink-800 font-semibold text-xs mb-1">New Students</div>
                                                                <!-- Day Scholar New -->
                                                                <div class="mb-1 bg-white rounded p-2">
                                                                    <div class="font-semibold text-blue-600 mb-1">Day Scholar</div>
                                                                    @if($cambridgeDayNew->sum('amount') > 0)
                                                                        @foreach($cambridgeDayNew as $fee)
                                                                            @if($fee->amount > 0)
                                                                            <div class="flex justify-between text-gray-600">
                                                                                <span>{{ $fee->feeType->name ?? 'Fee' }}</span>
                                                                                <span>${{ number_format($fee->amount, 2) }}</span>
                                                                            </div>
                                                                            @endif
                                                                        @endforeach
                                                                        <div class="flex justify-between font-bold text-blue-700 border-t border-blue-200 mt-1 pt-1">
                                                                            <span>Total</span>
                                                                            <span>${{ number_format($cambridgeDayNew->sum('amount'), 2) }}</span>
                                                                        </div>
                                                                    @else
                                                                        <span class="text-gray-400">-</span>
                                                                    @endif
                                                                </div>
                                                                <!-- Boarding New -->
                                                                <div class="bg-white rounded p-2">
                                                                    <div class="font-semibold text-green-600 mb-1">Boarding</div>
                                                                    @if($cambridgeBoardingNew->sum('amount') > 0)
                                                                        @foreach($cambridgeBoardingNew as $fee)
                                                                            @if($fee->amount > 0)
                                                                            <div class="flex justify-between text-gray-600">
                                                                                <span>{{ $fee->feeType->name ?? 'Fee' }}</span>
                                                                                <span>${{ number_format($fee->amount, 2) }}</span>
                                                                            </div>
                                                                            @endif
                                                                        @endforeach
                                                                        <div class="flex justify-between font-bold text-green-700 border-t border-green-200 mt-1 pt-1">
                                                                            <span>Total</span>
                                                                            <span>${{ number_format($cambridgeBoardingNew->sum('amount'), 2) }}</span>
                                                                        </div>
                                                                    @else
                                                                        <span class="text-gray-400">-</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400 italic">No fee structures</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-2">
                                        <button type="button" onclick="openTeacherSessionsModal({{ $resultStatus->id }}, '{{ $resultStatus->year }} {{ ucfirst($resultStatus->result_period) }} Term')" class="inline-flex items-center p-2 bg-amber-100 hover:bg-amber-200 text-amber-600 rounded-lg transition-colors" title="Manage Teacher Sessions">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </button>
                                        @if($resultStatus->feeStructures && $resultStatus->feeStructures->count() > 0)
                                        <button type="button" onclick="openCopyFeesModal({{ $resultStatus->id }}, '{{ $resultStatus->year }} {{ ucfirst($resultStatus->result_period) }} Term')" class="inline-flex items-center p-2 bg-blue-100 hover:bg-blue-200 text-blue-600 rounded-lg transition-colors" title="Copy Fee Breakdown to Another Term">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                            </svg>
                                        </button>
                                        @endif
                                        <a href="{{ route('results_status.edit', $resultStatus->id) }}" class="inline-flex items-center p-2 bg-green-100 hover:bg-green-200 text-green-600 rounded-lg transition-colors" title="Edit">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 448 512">
                                                <path d="M400 480H48c-26.5 0-48-21.5-48-48V80c0-26.5 21.5-48 48-48h352c26.5 0 48 21.5 48 48v352c0 26.5-21.5 48-48 48zM238.1 177.9L102.4 313.6l-6.3 57.1c-.8 7.6 5.6 14.1 13.3 13.3l57.1-6.3L302.2 242c2.3-2.3 2.3-6.1 0-8.5L246.7 178c-2.5-2.4-6.3-2.4-8.6-.1zM345 165.1L314.9 135c-9.4-9.4-24.6-9.4-33.9 0l-23.1 23.1c-2.3 2.3-2.3 6.1 0 8.5l55.5 55.5c2.3 2.3 6.1 2.3 8.5 0L345 199c9.3-9.3 9.3-24.5 0-33.9z"/>
                                            </svg>
                                        </a>
                                        <button type="button" onclick="openDeleteModal({{ $resultStatus->id }}, '{{ $resultStatus->year }} - {{ ucfirst($resultStatus->result_period) }} Term')" class="inline-flex items-center p-2 bg-red-100 hover:bg-red-200 text-red-600 rounded-lg transition-colors" title="Delete">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 448 512">
                                                <path d="M432 32H312l-9.4-18.7A24 24 0 0 0 281.1 0H166.8a23.72 23.72 0 0 0-21.4 13.3L136 32H16A16 16 0 0 0 0 48v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16zM53.2 467a48 48 0 0 0 47.9 45h245.8a48 48 0 0 0 47.9-45L416 128H32z"/>
                                            </svg>
                                        </button>
                                        <form id="delete-form-{{ $resultStatus->id }}" action="{{ route('results_status.destroy', $resultStatus->id) }}" method="POST" class="hidden">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <p class="text-gray-500 text-lg font-medium">No academic terms found</p>
                                        <p class="text-gray-400 text-sm mt-1">Get started by creating your first term</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Teacher Sessions Modal -->
    <div id="teacherSessionsModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-5 border max-w-4xl shadow-lg rounded-lg bg-white">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-900">
                    <span class="flex items-center">
                        <svg class="w-6 h-6 mr-2 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Manage Teacher Sessions
                    </span>
                </h3>
                <button onclick="closeTeacherSessionsModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <p class="text-sm text-gray-600 mb-4">Term: <span id="selectedTermName" class="font-semibold text-gray-900"></span></p>
            
            <form id="teacherSessionsForm" action="{{ route('teacher.update-sessions') }}" method="POST">
                @csrf
                <div class="max-h-96 overflow-y-auto border rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Teacher</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Morning</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Afternoon</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Both</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach(\App\Teacher::with('user')->get() as $teacher)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $teacher->user->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $teacher->phone }}</div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="sessions[{{ $teacher->id }}]" value="morning" 
                                            class="form-radio h-4 w-4 text-amber-500 focus:ring-amber-500"
                                            {{ $teacher->session === 'morning' ? 'checked' : '' }}>
                                    </label>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="sessions[{{ $teacher->id }}]" value="afternoon" 
                                            class="form-radio h-4 w-4 text-indigo-500 focus:ring-indigo-500"
                                            {{ $teacher->session === 'afternoon' ? 'checked' : '' }}>
                                    </label>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="sessions[{{ $teacher->id }}]" value="both" 
                                            class="form-radio h-4 w-4 text-purple-500 focus:ring-purple-500"
                                            {{ $teacher->session === 'both' || $teacher->session === null ? 'checked' : '' }}>
                                    </label>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Quick Actions -->
                <div class="mt-4 flex items-center gap-2 border-t pt-4">
                    <span class="text-sm text-gray-600">Set all to:</span>
                    <button type="button" onclick="setAllSessions('morning')" class="px-3 py-1 text-xs font-medium bg-amber-100 text-amber-700 rounded-full hover:bg-amber-200">Morning</button>
                    <button type="button" onclick="setAllSessions('afternoon')" class="px-3 py-1 text-xs font-medium bg-indigo-100 text-indigo-700 rounded-full hover:bg-indigo-200">Afternoon</button>
                    <button type="button" onclick="setAllSessions('both')" class="px-3 py-1 text-xs font-medium bg-purple-100 text-purple-700 rounded-full hover:bg-purple-200">Both</button>
                </div>
                
                <div class="flex gap-3 mt-4 pt-4 border-t">
                    <button type="button" onclick="closeTeacherSessionsModal()" class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 text-sm font-medium rounded-md hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-amber-500 text-white text-sm font-medium rounded-md hover:bg-amber-600">
                        Save Sessions
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Copy Fee Breakdown Modal -->
    <div id="copyFeesModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-blue-100 rounded-full">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="mt-4 text-center">
                    <h3 class="text-lg font-semibold text-gray-900">Copy Fee Breakdown</h3>
                    <div class="mt-2 px-4 py-3 text-left">
                        <p class="text-sm text-gray-500 mb-4 text-center">
                            Copy all fees from <span id="copySourceName" class="font-bold text-gray-900"></span> to:
                        </p>
                        <div class="mb-3">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Year</label>
                            <select id="copyYearSelect" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select year</option>
                                <option value="2025">2025</option>
                                <option value="2026">2026</option>
                                <option value="2027">2027</option>
                                <option value="2028">2028</option>
                                <option value="2029">2029</option>
                                <option value="2030">2030</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Select Term</label>
                            <select id="copyTermSelect" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select a Term</option>
                                <option value="first">First Term</option>
                                <option value="second">Second Term</option>
                                <option value="third">Third Term</option>
                            </select>
                        </div>
                        <div id="copyTermNotFound" class="hidden text-xs text-red-600 bg-red-50 border border-red-200 rounded p-2 mb-2">
                            That term does not exist yet. Please create it first before copying fees to it.
                        </div>
                        <p class="text-xs text-amber-600">This will replace any existing fee structures on the target term.</p>
                    </div>
                    <div class="flex gap-3 mt-4 px-4">
                        <button onclick="closeCopyFeesModal()" class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 text-sm font-medium rounded-md hover:bg-gray-300 focus:outline-none">
                            Cancel
                        </button>
                        <button onclick="confirmCopyFees()" class="flex-1 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none">
                            Copy Fees
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <form id="copy-fees-form" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="target_term_id" id="copyTargetInput">
    </form>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="mt-4 text-center">
                    <h3 class="text-lg font-semibold text-gray-900">Delete Term</h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-gray-500">
                            Are you sure you want to delete <span id="termName" class="font-bold text-gray-900"></span>?
                        </p>
                        <p class="text-xs text-red-600 mt-2">
                            This action cannot be undone. All associated fee records will be permanently deleted.
                        </p>
                    </div>
                    <div class="flex gap-3 mt-4 px-4">
                        <button onclick="closeDeleteModal()" class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 text-sm font-medium rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-300">
                            Cancel
                        </button>
                        <button onclick="confirmDelete()" class="flex-1 px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let deleteFormId = null;

        function openDeleteModal(id, termName) {
            deleteFormId = id;
            document.getElementById('termName').textContent = termName;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            deleteFormId = null;
        }

        function confirmDelete() {
            if (deleteFormId) {
                document.getElementById('delete-form-' + deleteFormId).submit();
            }
        }

        // Teacher Sessions Modal Functions
        function openTeacherSessionsModal(termId, termName) {
            document.getElementById('selectedTermName').textContent = termName;
            document.getElementById('teacherSessionsModal').classList.remove('hidden');
        }

        function closeTeacherSessionsModal() {
            document.getElementById('teacherSessionsModal').classList.add('hidden');
        }

        function setAllSessions(session) {
            const radios = document.querySelectorAll('input[type="radio"][value="' + session + '"]');
            radios.forEach(function(radio) {
                radio.checked = true;
            });
        }

        // Map of "year_period" => term id for lookup
        const termLookup = {
            @foreach($resultsStatuses as $term)
            '{{ $term->year }}_{{ $term->result_period }}': {{ $term->id }},
            @endforeach
        };

        let copySourceId = null;
        let copySourceKey = null;

        function openCopyFeesModal(sourceId, sourceName) {
            copySourceId = sourceId;
            // Store the source key so we can exclude it
            const sourceEntry = Object.entries(termLookup).find(([k, v]) => v === sourceId);
            copySourceKey = sourceEntry ? sourceEntry[0] : null;
            document.getElementById('copySourceName').textContent = sourceName;
            document.getElementById('copyYearSelect').value = '';
            document.getElementById('copyTermSelect').value = '';
            document.getElementById('copyTermNotFound').classList.add('hidden');
            document.getElementById('copyFeesModal').classList.remove('hidden');
        }

        function closeCopyFeesModal() {
            document.getElementById('copyFeesModal').classList.add('hidden');
            copySourceId = null;
            copySourceKey = null;
        }

        function confirmCopyFees() {
            const year = document.getElementById('copyYearSelect').value;
            const period = document.getElementById('copyTermSelect').value;
            document.getElementById('copyTermNotFound').classList.add('hidden');

            if (!year || !period) {
                alert('Please select both a year and a term.');
                return;
            }

            const key = year + '_' + period;
            if (key === copySourceKey) {
                alert('The target term is the same as the source. Please select a different term.');
                return;
            }

            const targetId = termLookup[key];
            if (!targetId) {
                document.getElementById('copyTermNotFound').classList.remove('hidden');
                return;
            }

            const form = document.getElementById('copy-fees-form');
            form.action = '/Term/' + copySourceId + '/copy-fees';
            document.getElementById('copyTargetInput').value = targetId;
            form.submit();
        }

        document.getElementById('copyFeesModal').addEventListener('click', function(e) {
            if (e.target === this) closeCopyFeesModal();
        });

        // Close modal when clicking outside
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });

        document.getElementById('teacherSessionsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeTeacherSessionsModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDeleteModal();
                closeTeacherSessionsModal();
                closeCopyFeesModal();
            }
        });
    </script>
@endsection
