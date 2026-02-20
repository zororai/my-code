@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Student Collections</h1>
            <p class="text-gray-600">Track uniforms, report cards, student IDs, and other item collections</p>
        </div>
        <div class="flex gap-2">
            <button onclick="openItemModal()" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">
                <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Record Item (Report Card/ID)
            </button>
            <a href="{{ route('finance.products.pos') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Record Uniform (POS)
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mr-4">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-yellow-600">Pending Collection</p>
                    <p class="text-2xl font-bold text-yellow-700">{{ $pendingCount }}</p>
                </div>
            </div>
        </div>
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-green-600">Collected</p>
                    <p class="text-2xl font-bold text-green-700">{{ $collectedCount }}</p>
                </div>
            </div>
        </div>
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-blue-600">Total Records</p>
                    <p class="text-2xl font-bold text-blue-700">{{ $pendingCount + $collectedCount }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border">
        <div class="p-4 border-b">
            <form method="GET" class="flex gap-4 flex-wrap">
                <div class="flex-1 min-w-64">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by student name or roll number..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <select name="item_type" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">All Types</option>
                    @foreach($itemTypes as $key => $label)
                    <option value="{{ $key }}" {{ request('item_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="collected" {{ request('status') == 'collected' ? 'selected' : '' }}>Collected</option>
                </select>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Filter</button>
                @if(request()->hasAny(['search', 'status', 'item_type']))
                <a href="{{ route('finance.uniform-collections') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300">Clear</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Class</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Details</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Collected By</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($collections as $collection)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900">{{ $collection->student->user->name ?? 'Unknown' }}</div>
                            <div class="text-sm text-gray-500">{{ $collection->student->roll_number ?? '-' }}</div>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $collection->student->class->class_name ?? 'N/A' }}</td>
                        <td class="px-4 py-3">
                            @php
                                $typeColors = [
                                    'uniform' => 'bg-green-100 text-green-800',
                                    'report_card' => 'bg-blue-100 text-blue-800',
                                    'student_id' => 'bg-purple-100 text-purple-800',
                                    'certificate' => 'bg-yellow-100 text-yellow-800',
                                    'other' => 'bg-gray-100 text-gray-800',
                                ];
                                $color = $typeColors[$collection->item_type] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $color }}">
                                {{ $itemTypes[$collection->item_type] ?? ucfirst($collection->item_type) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900">{{ $collection->item_name ?? $collection->product_name }}</div>
                            @if($collection->unit_price > 0)
                            <div class="text-sm text-gray-500">${{ number_format($collection->unit_price, 2) }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            @if($collection->item_type === 'uniform')
                                Size: {{ $collection->size ?? '-' }} | Qty: {{ $collection->quantity }}
                            @elseif($collection->academic_year || $collection->term)
                                {{ $collection->academic_year ?? '' }} {{ $collection->term ? '- ' . $collection->term : '' }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($collection->status === 'collected')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                Collected
                            </span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                </svg>
                                Pending
                            </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $collection->collector->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-600">
                            @if($collection->collected_at)
                            {{ $collection->collected_at->format('M d, Y H:i') }}
                            @else
                            {{ $collection->created_at->format('M d, Y') }}
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            No collection records found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($collections->hasPages())
        <div class="px-4 py-3 border-t">
            {{ $collections->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Record Item Modal -->
<div id="itemModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg p-6 max-w-lg w-full mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-800">Record Item Collection</h3>
            <button onclick="closeItemModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <form id="itemForm" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search Student</label>
                <input type="text" id="modalStudentSearch" onkeyup="searchAllStudents()" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500" placeholder="Search by name or roll number...">
                <div id="modalStudentResults" class="mt-2 max-h-32 overflow-y-auto hidden"></div>
                <input type="hidden" id="modalStudentId" name="student_id">
                <div id="modalSelectedStudent" class="mt-2 hidden bg-purple-50 p-3 rounded-lg"></div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Item Type</label>
                <select id="modalItemType" name="item_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500" required>
                    <option value="">Select type...</option>
                    <option value="report_card">Report Card</option>
                    <option value="student_id">Student ID</option>
                    <option value="certificate">Certificate</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Item Name/Description</label>
                <input type="text" id="modalItemName" name="item_name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500" placeholder="e.g., Term 1 Report Card, 2024 Student ID..." required>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Academic Year</label>
                    <input type="text" id="modalAcademicYear" name="academic_year" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500" placeholder="e.g., 2024">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Term</label>
                    <select id="modalTerm" name="term" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        <option value="">Select...</option>
                        <option value="Term 1">Term 1</option>
                        <option value="Term 2">Term 2</option>
                        <option value="Term 3">Term 3</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                <textarea id="modalNotes" name="notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500" placeholder="Any additional notes..."></textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 bg-purple-600 text-white py-2 rounded-lg hover:bg-purple-700 font-semibold">
                    Record Collection
                </button>
                <button type="button" onclick="closeItemModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let modalSelectedStudent = null;
let modalSearchTimeout = null;

function openItemModal() {
    document.getElementById('itemModal').classList.remove('hidden');
}

function closeItemModal() {
    document.getElementById('itemModal').classList.add('hidden');
    document.getElementById('itemForm').reset();
    document.getElementById('modalStudentResults').classList.add('hidden');
    document.getElementById('modalSelectedStudent').classList.add('hidden');
    document.getElementById('modalStudentId').value = '';
    modalSelectedStudent = null;
}

function searchAllStudents() {
    clearTimeout(modalSearchTimeout);
    const search = document.getElementById('modalStudentSearch').value.trim();
    
    if (search.length < 2) {
        document.getElementById('modalStudentResults').classList.add('hidden');
        return;
    }
    
    modalSearchTimeout = setTimeout(() => {
        fetch('{{ route("finance.uniform-collections.search-all-students") }}?search=' + encodeURIComponent(search))
            .then(res => res.json())
            .then(data => {
                const container = document.getElementById('modalStudentResults');
                if (data.students.length === 0) {
                    container.innerHTML = '<p class="text-gray-500 text-center py-2 text-sm">No students found</p>';
                } else {
                    container.innerHTML = data.students.map(student => `
                        <button type="button" onclick="selectModalStudent(${student.id}, '${student.name}', '${student.roll_number}', '${student.class}')" 
                            class="w-full flex items-center justify-between p-2 border-b hover:bg-purple-50 text-left text-sm">
                            <div>
                                <div class="font-medium text-gray-900">${student.name}</div>
                                <div class="text-xs text-gray-500">${student.roll_number} • ${student.class}</div>
                            </div>
                        </button>
                    `).join('');
                }
                container.classList.remove('hidden');
            })
            .catch(err => console.error('Search error:', err));
    }, 300);
}

function selectModalStudent(id, name, rollNumber, className) {
    modalSelectedStudent = { id, name, roll_number: rollNumber, class: className };
    document.getElementById('modalStudentId').value = id;
    document.getElementById('modalStudentResults').classList.add('hidden');
    document.getElementById('modalStudentSearch').value = '';
    
    document.getElementById('modalSelectedStudent').innerHTML = `
        <div class="flex justify-between items-center">
            <div>
                <div class="font-semibold text-gray-900">${name}</div>
                <div class="text-sm text-gray-600">${rollNumber} • ${className}</div>
            </div>
            <button type="button" onclick="clearModalStudent()" class="text-red-500 hover:text-red-700 text-sm">Clear</button>
        </div>
    `;
    document.getElementById('modalSelectedStudent').classList.remove('hidden');
}

function clearModalStudent() {
    modalSelectedStudent = null;
    document.getElementById('modalStudentId').value = '';
    document.getElementById('modalSelectedStudent').classList.add('hidden');
}

document.getElementById('itemForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!modalSelectedStudent) {
        alert('Please select a student');
        return;
    }
    
    const data = {
        student_id: modalSelectedStudent.id,
        item_type: document.getElementById('modalItemType').value,
        item_name: document.getElementById('modalItemName').value,
        academic_year: document.getElementById('modalAcademicYear').value || null,
        term: document.getElementById('modalTerm').value || null,
        notes: document.getElementById('modalNotes').value || null,
    };
    
    fetch('{{ route("finance.uniform-collections.record-item") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            closeItemModal();
            location.reload();
        } else {
            alert(data.message || 'Failed to record collection');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Error recording collection');
    });
});
</script>
@endsection
