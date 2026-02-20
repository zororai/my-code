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
                    <tr class="hover:bg-gray-50">
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
                            @if($record->status == 'issued')
                                <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">Issued</span>
                            @elseif($record->status == 'returned')
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Returned</span>
                            @elseif($record->status == 'lost')
                                <span class="px-2 py-1 text-xs font-medium bg-gray-800 text-white rounded-full">Lost</span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">Overdue</span>
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
