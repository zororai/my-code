@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Student Library Records</h1>
            <p class="text-gray-600 mt-1">Manage book issuance and returns</p>
        </div>
        <div class="mt-4 sm:mt-0 flex flex-wrap gap-3">
            <a href="{{ route('admin.library.books') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                Book Archive
            </a>
            <a href="{{ route('admin.library.books.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Add Book
            </a>
            <a href="{{ route('admin.library.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Issue Book
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif

    {{-- Overdue Alert Banner --}}
    @if($overdueRecords->count() > 0)
    <div class="bg-red-50 border border-red-400 text-red-800 px-4 py-4 rounded-lg mb-6 flex items-start gap-3" role="alert">
        <svg class="w-6 h-6 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
        <div>
            <p class="font-semibold text-red-800">
                {{ $overdueRecords->count() }} overdue {{ Str::plural('book', $overdueRecords->count()) }} require attention!
            </p>
            <p class="text-sm text-red-700 mt-0.5">
                The following borrowers have not returned their books by the due date. Please follow up with them.
            </p>
            <button onclick="document.getElementById('overdueSection').scrollIntoView({behavior:'smooth'})"
                class="mt-2 text-sm font-medium text-red-700 underline hover:text-red-900">
                View overdue books &darr;
            </button>
        </div>
    </div>
    @endif

    {{-- Overdue Books Section --}}
    @if($overdueRecords->count() > 0)
    <div id="overdueSection" class="bg-white rounded-xl shadow-sm border border-red-300 overflow-hidden mb-6">
        <div class="bg-red-600 px-6 py-3 flex items-center gap-2">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h2 class="text-white font-semibold text-sm uppercase tracking-wider">
                Overdue Books ({{ $overdueRecords->count() }})
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-red-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">Borrower</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">Book</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">Due Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">Days Overdue</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-red-700 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-red-100">
                    @foreach($overdueRecords as $od)
                    @php
                        $daysOverdue = \Carbon\Carbon::parse($od->due_date)->diffInDays(\Carbon\Carbon::today());
                        $borrowerName = $od->borrower_type === 'teacher'
                            ? ($od->teacher->user->name ?? 'Unknown Teacher')
                            : ($od->student->user->name ?? 'Unknown Student');
                        $borrowerInfo = $od->borrower_type === 'teacher'
                            ? 'Teacher'
                            : (($od->student->class->class_name ?? '') . ' • ' . ($od->student->roll_number ?? ''));
                    @endphp
                    <tr class="hover:bg-red-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-9 h-9 rounded-full flex items-center justify-center {{ $od->borrower_type === 'teacher' ? 'bg-purple-100' : 'bg-blue-100' }}">
                                    <span class="font-medium text-sm {{ $od->borrower_type === 'teacher' ? 'text-purple-600' : 'text-blue-600' }}">
                                        {{ strtoupper(substr($borrowerName, 0, 2)) }}
                                    </span>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $borrowerName }}</p>
                                    <p class="text-xs text-gray-500">{{ $borrowerInfo }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-medium text-gray-900">{{ $od->book_title }}</p>
                            <p class="text-xs text-gray-500">Book #: {{ $od->book_number }}</p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-red-700 font-medium">
                            {{ \Carbon\Carbon::parse($od->due_date)->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">
                                {{ $daysOverdue }} {{ Str::plural('day', $daysOverdue) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            <button type="button"
                                onclick="openReturnModal({{ $od->id }}, '{{ addslashes($od->book_title) }}', '{{ $od->copy_isbn ?? $od->book_number }}')"
                                class="text-green-600 hover:text-green-800 font-medium">
                                Return
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Lost Books Section --}}
    @if($lostRecords->count() > 0)
    <div id="lostSection" class="bg-white rounded-xl shadow-sm border border-gray-400 overflow-hidden mb-6">
        <div class="bg-gray-800 px-6 py-3 flex items-center gap-2">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            <h2 class="text-white font-semibold text-sm uppercase tracking-wider">
                Lost Books — Pending Resolution ({{ $lostRecords->count() }})
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Borrower</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Book</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Marked Lost</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resolution</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach($lostRecords as $lr)
                    @php
                        $lrName = $lr->borrower_type === 'teacher'
                            ? ($lr->teacher->user->name ?? 'Unknown Teacher')
                            : ($lr->student->user->name ?? 'Unknown Student');
                        $lrInfo = $lr->borrower_type === 'teacher'
                            ? 'Teacher'
                            : (($lr->student->class->class_name ?? '') . ' • ' . ($lr->student->roll_number ?? ''));
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-9 h-9 rounded-full flex items-center justify-center bg-gray-200">
                                    <span class="font-medium text-sm text-gray-600">
                                        {{ strtoupper(substr($lrName, 0, 2)) }}
                                    </span>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $lrName }}</p>
                                    <p class="text-xs text-gray-500">{{ $lrInfo }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-medium text-gray-900">{{ $lr->book_title }}</p>
                            <p class="text-xs text-gray-500">Book #: {{ $lr->book_number }}</p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $lr->return_date ? \Carbon\Carbon::parse($lr->return_date)->format('M d, Y') : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">Pending</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            <button type="button"
                                onclick="openResolveModal({{ $lr->id }}, '{{ addslashes($lr->book_title) }}', '{{ addslashes($lrName) }}')"
                                class="inline-flex items-center px-3 py-1.5 bg-gray-800 hover:bg-gray-900 text-white text-xs font-medium rounded-lg transition-colors">
                                Resolve
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
        <form method="GET" action="{{ route('admin.library.index') }}" class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" 
                    placeholder="Search by student name, book title or number..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="sm:w-48">
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Status</option>
                    <option value="issued" {{ request('status') == 'issued' ? 'selected' : '' }}>Issued</option>
                    <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>Returned</option>
                    <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                    <option value="lost" {{ request('status') == 'lost' ? 'selected' : '' }}>Lost</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors">
                Filter
            </button>
            <a href="{{ route('admin.library.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition-colors text-center">
                Reset
            </a>
        </form>
    </div>

    <!-- Records Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Book Details</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Issue Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Issued By</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($records as $record)
                    @php
                        $isOverdue = $record->status === 'issued' && $record->due_date && \Carbon\Carbon::parse($record->due_date)->isPast();
                    @endphp
                    <tr class="hover:bg-gray-50 {{ $isOverdue ? 'bg-red-50' : '' }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-blue-600 font-medium text-sm">
                                        {{ strtoupper(substr($record->student->user->name ?? 'U', 0, 2)) }}
                                    </span>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $record->student->user->name ?? 'Unknown' }}</p>
                                    <p class="text-xs text-gray-500">{{ $record->student->class->class_name ?? 'N/A' }} • {{ $record->student->roll_number ?? '' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-medium text-gray-900">{{ $record->book_title }}</p>
                            <p class="text-xs text-gray-500">Book #: {{ $record->book_number }}</p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ \Carbon\Carbon::parse($record->issue_date)->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ $record->due_date ? \Carbon\Carbon::parse($record->due_date)->format('M d, Y') : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($isOverdue)
                                <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">Overdue</span>
                            @elseif($record->status == 'issued')
                                <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">Issued</span>
                            @elseif($record->status == 'returned')
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Returned</span>
                            @elseif($record->status == 'lost')
                                <span class="px-2 py-1 text-xs font-medium bg-gray-800 text-white rounded-full">Lost</span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">{{ ucfirst($record->status) }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ $record->issuedBy->name ?? 'Unknown' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            <div class="flex items-center justify-end space-x-2">
                                @if($record->status == 'issued')
                                <button type="button" onclick="openReturnModal({{ $record->id }}, '{{ addslashes($record->book_title) }}', '{{ $record->copy_isbn ?? $record->book_number }}')" 
                                    class="text-green-600 hover:text-green-800 font-medium">
                                    Return
                                </button>
                                @endif
                                <a href="{{ route('admin.library.student-history', $record->student_id) }}" 
                                    class="text-blue-600 hover:text-blue-800">
                                    History
                                </a>
                                <form action="{{ route('admin.library.destroy', $record->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800" 
                                        onclick="return confirm('Are you sure you want to delete this record?')">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No library records found</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by issuing a book to a student.</p>
                            <div class="mt-6">
                                <a href="{{ route('admin.library.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Issue Book
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($records->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $records->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Return Book Modal -->
<div id="returnModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-xl bg-white">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Return Book</h3>
            <button onclick="closeReturnModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <div class="mb-4 p-3 bg-gray-50 rounded-lg">
            <p class="text-sm text-gray-600">Book: <span id="modalBookTitle" class="font-medium text-gray-900"></span></p>
            <p class="text-sm text-gray-600">ISBN/Number: <span id="modalBookIsbn" class="font-mono text-gray-900"></span></p>
        </div>

        <form id="returnForm" method="POST">
            @csrf
            @method('PATCH')
            
            <!-- Return Type -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Return Status *</label>
                <div class="space-y-2">
                    <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors return-type-option" data-value="returned">
                        <input type="radio" name="return_type" value="returned" class="mr-3" checked>
                        <div class="flex-1">
                            <span class="font-medium text-green-700">Book Returned</span>
                            <p class="text-xs text-gray-500">Book has been returned in acceptable condition</p>
                        </div>
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </label>
                    <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors return-type-option" data-value="lost">
                        <input type="radio" name="return_type" value="lost" class="mr-3">
                        <div class="flex-1">
                            <span class="font-medium text-red-700">Book Lost</span>
                            <p class="text-xs text-gray-500">Book has been lost or cannot be returned</p>
                        </div>
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </label>
                </div>
            </div>

            <!-- Book Condition (shown only when returned) -->
            <div id="conditionSection" class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Book Condition on Return *</label>
                <select name="return_condition" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="excellent">Excellent - Like new</option>
                    <option value="good" selected>Good - Minor wear</option>
                    <option value="fair">Fair - Noticeable wear</option>
                    <option value="poor">Poor - Significant damage</option>
                    <option value="damaged">Damaged - Needs repair</option>
                </select>
            </div>

            <!-- Condition Notes -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                <textarea name="return_notes" rows="2" placeholder="Any notes about the book's condition..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
            </div>

            <!-- Submit Buttons -->
            <div class="flex justify-end space-x-3 pt-4 border-t">
                <button type="button" onclick="closeReturnModal()" 
                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition-colors">
                    Cancel
                </button>
                <button type="submit" id="submitReturnBtn"
                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                    Confirm Return
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Resolve Lost Book Modal -->
<div id="resolveModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-xl bg-white mb-10">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Resolve Lost Book</h3>
            <button onclick="closeResolveModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="mb-4 p-3 bg-gray-50 rounded-lg">
            <p class="text-sm text-gray-600">Lost Book: <span id="resolveBookTitle" class="font-medium text-gray-900"></span></p>
            <p class="text-sm text-gray-600">Borrower: <span id="resolveBorrowerName" class="font-medium text-gray-900"></span></p>
        </div>

        <form id="resolveForm" method="POST">
            @csrf
            @method('PATCH')

            <!-- Resolution Type -->
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-2">How was this resolved? *</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-start p-3 border-2 rounded-lg cursor-pointer hover:bg-blue-50 transition-colors has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                        <input type="radio" name="resolution_type" value="replace" class="mr-3 mt-0.5" id="res_replace" onchange="toggleResolutionSection()">
                        <div>
                            <span class="font-medium text-blue-700 block">Book Replaced</span>
                            <p class="text-xs text-gray-500 mt-0.5">Borrower provided a replacement copy</p>
                        </div>
                    </label>
                    <label class="flex items-start p-3 border-2 rounded-lg cursor-pointer hover:bg-green-50 transition-colors has-[:checked]:border-green-500 has-[:checked]:bg-green-50">
                        <input type="radio" name="resolution_type" value="pay_fee" class="mr-3 mt-0.5" id="res_fee" onchange="toggleResolutionSection()">
                        <div>
                            <span class="font-medium text-green-700 block">Fee Paid</span>
                            <p class="text-xs text-gray-500 mt-0.5">Borrower paid a monetary fee</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- ── REPLACEMENT BOOK DETAILS ── -->
            <div id="replacementBookSection" class="hidden mb-5 border border-blue-200 rounded-lg p-4 bg-blue-50">
                <h4 class="text-sm font-semibold text-blue-800 mb-3 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    Replacement Book Details
                </h4>

                <div class="grid grid-cols-2 gap-3">
                    <!-- Book Title -->
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Book Title *</label>
                        <input type="text" name="replacement_title" id="replacement_title"
                            placeholder="Enter book title"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Book Number -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Book Number *</label>
                        <input type="text" name="replacement_book_number" id="replacement_book_number"
                            placeholder="e.g., LIB-001"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Author -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Author</label>
                        <input type="text" name="replacement_author"
                            placeholder="Enter author name"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- ISBN -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">ISBN</label>
                        <input type="text" name="replacement_isbn"
                            placeholder="Enter ISBN"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Category</label>
                        <input type="text" name="replacement_category"
                            placeholder="e.g., Fiction, Science, History"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <!-- Add individual copies toggle -->
                <div class="mt-3 p-3 bg-white rounded-lg border border-blue-100">
                    <label class="flex items-start gap-2 cursor-pointer">
                        <input type="checkbox" name="replacement_add_copies" id="replacement_add_copies"
                            class="mt-0.5" onchange="toggleReplacementCopies()">
                        <div>
                            <span class="text-xs font-medium text-gray-800 block">Add individual copies with unique ISBN numbers</span>
                            <span class="text-xs text-gray-500">Check this to add multiple copies, each with a unique ISBN number for tracking</span>
                        </div>
                    </label>
                </div>

                <!-- Quantity (shown when NOT using copies) -->
                <div id="replacementQuantitySection" class="grid grid-cols-2 gap-3 mt-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Quantity *</label>
                        <input type="number" name="replacement_quantity" value="1" min="1"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Book Condition *</label>
                        <select name="replacement_condition" id="replacement_condition_main"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="excellent">Excellent</option>
                            <option value="good" selected>Good</option>
                            <option value="fair">Fair</option>
                            <option value="poor">Poor</option>
                            <option value="damaged">Damaged</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Condition Notes</label>
                        <input type="text" name="replacement_condition_notes"
                            placeholder="Optional notes about the book's condition"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <!-- Individual copies list (shown when using copies) -->
                <div id="replacementCopiesSection" class="hidden mt-3">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-medium text-gray-700">Copies</span>
                        <button type="button" onclick="addReplacementCopy()"
                            class="text-xs text-blue-600 hover:text-blue-800 font-medium">+ Add Copy</button>
                    </div>
                    <div id="replacementCopiesList" class="space-y-2">
                        <!-- copies injected by JS -->
                    </div>
                </div>
            </div>

            <!-- ── FEE SECTION ── -->
            <div id="feeAmountSection" class="mb-5 hidden">
                <label class="block text-sm font-medium text-gray-700 mb-1">Fee Amount *</label>
                <div class="relative">
                    <span class="absolute left-3 top-2.5 text-gray-500 text-sm font-medium">$</span>
                    <input type="number" name="fee_amount" id="feeAmountInput" min="0" step="0.01"
                        placeholder="0.00"
                        class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
            </div>

            <!-- Resolution Notes -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                <textarea name="resolution_notes" rows="2" placeholder="Any additional details..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end space-x-3 pt-4 border-t">
                <button type="button" onclick="closeResolveModal()"
                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition-colors">
                    Cancel
                </button>
                <button type="submit"
                    class="px-4 py-2 bg-gray-800 hover:bg-gray-900 text-white font-medium rounded-lg transition-colors">
                    Mark as Resolved
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openResolveModal(recordId, bookTitle, borrowerName) {
    document.getElementById('resolveModal').classList.remove('hidden');
    document.getElementById('resolveBookTitle').textContent = bookTitle;
    document.getElementById('resolveBorrowerName').textContent = borrowerName;
    document.getElementById('resolveForm').action = '/admin/library/' + recordId + '/resolve-lost';
    // Reset
    document.querySelectorAll('input[name="resolution_type"]').forEach(r => r.checked = false);
    document.getElementById('replacementBookSection').classList.add('hidden');
    document.getElementById('feeAmountSection').classList.add('hidden');
    document.getElementById('feeAmountInput').value = '';
    document.getElementById('replacement_add_copies').checked = false;
    document.getElementById('replacementCopiesSection').classList.add('hidden');
    document.getElementById('replacementQuantitySection').classList.remove('hidden');
    document.getElementById('replacementCopiesList').innerHTML = '';
    document.getElementById('resolveForm').reset();
}

function closeResolveModal() {
    document.getElementById('resolveModal').classList.add('hidden');
}

function toggleResolutionSection() {
    const isReplace = document.getElementById('res_replace').checked;
    const isFee     = document.getElementById('res_fee').checked;
    document.getElementById('replacementBookSection').classList.toggle('hidden', !isReplace);
    document.getElementById('feeAmountSection').classList.toggle('hidden', !isFee);
    // Required toggling
    document.getElementById('replacement_title').required        = isReplace;
    document.getElementById('replacement_book_number').required  = isReplace;
    document.getElementById('replacement_condition_main').required = isReplace;
    document.getElementById('feeAmountInput').required           = isFee;
}

function toggleReplacementCopies() {
    const useCopies = document.getElementById('replacement_add_copies').checked;
    document.getElementById('replacementQuantitySection').classList.toggle('hidden', useCopies);
    document.getElementById('replacementCopiesSection').classList.toggle('hidden', !useCopies);
    if (useCopies && document.getElementById('replacementCopiesList').children.length === 0) {
        addReplacementCopy();
    }
}

let replacementCopyIndex = 0;
function addReplacementCopy() {
    const i = replacementCopyIndex++;
    const div = document.createElement('div');
    div.className = 'grid grid-cols-3 gap-2 items-end border border-gray-200 rounded-lg p-2 bg-white';
    div.innerHTML = `
        <div>
            <label class="block text-xs text-gray-600 mb-1">ISBN (optional)</label>
            <input type="text" name="replacement_copies[${i}][isbn]" placeholder="Auto-generated if blank"
                class="w-full px-2 py-1.5 border border-gray-300 rounded text-xs focus:ring-1 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-xs text-gray-600 mb-1">Condition *</label>
            <select name="replacement_copies[${i}][condition]"
                class="w-full px-2 py-1.5 border border-gray-300 rounded text-xs focus:ring-1 focus:ring-blue-500">
                <option value="excellent">Excellent</option>
                <option value="good" selected>Good</option>
                <option value="fair">Fair</option>
                <option value="poor">Poor</option>
                <option value="damaged">Damaged</option>
            </select>
        </div>
        <div class="flex items-end">
            <button type="button" onclick="this.closest('div.grid').remove()"
                class="w-full px-2 py-1.5 bg-red-100 hover:bg-red-200 text-red-700 text-xs rounded transition-colors">
                Remove
            </button>
        </div>
    `;
    document.getElementById('replacementCopiesList').appendChild(div);
}

document.getElementById('resolveModal').addEventListener('click', function(e) {
    if (e.target === this) closeResolveModal();
});
</script>

<script>
function openReturnModal(recordId, bookTitle, bookIsbn) {
    document.getElementById('returnModal').classList.remove('hidden');
    document.getElementById('modalBookTitle').textContent = bookTitle;
    document.getElementById('modalBookIsbn').textContent = bookIsbn;
    document.getElementById('returnForm').action = '/admin/library/' + recordId + '/return';
    
    // Reset form
    document.querySelector('input[name="return_type"][value="returned"]').checked = true;
    document.getElementById('conditionSection').classList.remove('hidden');
    document.getElementById('submitReturnBtn').textContent = 'Confirm Return';
    document.getElementById('submitReturnBtn').classList.remove('bg-red-600', 'hover:bg-red-700');
    document.getElementById('submitReturnBtn').classList.add('bg-green-600', 'hover:bg-green-700');
}

function closeReturnModal() {
    document.getElementById('returnModal').classList.add('hidden');
}

// Handle return type change
document.querySelectorAll('input[name="return_type"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        const conditionSection = document.getElementById('conditionSection');
        const submitBtn = document.getElementById('submitReturnBtn');
        
        if (this.value === 'lost') {
            conditionSection.classList.add('hidden');
            submitBtn.textContent = 'Mark as Lost';
            submitBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
            submitBtn.classList.add('bg-red-600', 'hover:bg-red-700');
        } else {
            conditionSection.classList.remove('hidden');
            submitBtn.textContent = 'Confirm Return';
            submitBtn.classList.remove('bg-red-600', 'hover:bg-red-700');
            submitBtn.classList.add('bg-green-600', 'hover:bg-green-700');
        }
    });
});

// Close modal on outside click
document.getElementById('returnModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeReturnModal();
    }
});
</script>
@endsection
