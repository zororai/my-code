@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Add Book Copies</h1>
                <p class="text-gray-600">Add copies with unique ISBN numbers for: <span class="font-semibold">{{ $book->title }}</span></p>
            </div>
            <a href="{{ route('admin.library.books.copies', $book->id) }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
                </svg>
                Back to Copies
            </a>
        </div>

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                <h3 class="font-semibold text-blue-800">Book Information</h3>
                <div class="grid grid-cols-2 gap-4 mt-2 text-sm">
                    <div><span class="text-gray-600">Title:</span> {{ $book->title }}</div>
                    <div><span class="text-gray-600">Author:</span> {{ $book->author ?? 'N/A' }}</div>
                    <div><span class="text-gray-600">Book Number:</span> {{ $book->book_number }}</div>
                    <div><span class="text-gray-600">Current Copies:</span> {{ $book->copies()->count() }}</div>
                </div>
            </div>

            <form action="{{ route('admin.library.books.copies.store', $book->id) }}" method="POST" id="copiesForm">
                @csrf

                <div class="mb-4 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-800">Book Copies</h3>
                    <button type="button" onclick="addCopyRow()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Add Another Copy
                    </button>
                </div>

                <div id="copiesContainer">
                    <div class="copy-row border rounded-lg p-4 mb-4 bg-gray-50" data-index="0">
                        <div class="flex justify-between items-center mb-3">
                            <span class="font-medium text-gray-700">Copy #1</span>
                            <button type="button" onclick="removeCopyRow(this)" class="text-red-500 hover:text-red-700 hidden remove-btn">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">ISBN *</label>
                                <input type="text" name="copies[0][isbn]" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    placeholder="Enter unique ISBN">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Condition *</label>
                                <select name="copies[0][condition]" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="excellent">Excellent</option>
                                    <option value="good" selected>Good</option>
                                    <option value="fair">Fair</option>
                                    <option value="poor">Poor</option>
                                    <option value="damaged">Damaged</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Condition Notes</label>
                                <input type="text" name="copies[0][condition_notes]"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    placeholder="Optional notes">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <a href="{{ route('admin.library.books.copies', $book->id) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg">
                        Cancel
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                        Add Copies
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let copyIndex = 1;

function addCopyRow() {
    const container = document.getElementById('copiesContainer');
    const newRow = document.createElement('div');
    newRow.className = 'copy-row border rounded-lg p-4 mb-4 bg-gray-50';
    newRow.dataset.index = copyIndex;
    
    newRow.innerHTML = `
        <div class="flex justify-between items-center mb-3">
            <span class="font-medium text-gray-700">Copy #${copyIndex + 1}</span>
            <button type="button" onclick="removeCopyRow(this)" class="text-red-500 hover:text-red-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ISBN *</label>
                <input type="text" name="copies[${copyIndex}][isbn]" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    placeholder="Enter unique ISBN">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Condition *</label>
                <select name="copies[${copyIndex}][condition]" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="excellent">Excellent</option>
                    <option value="good" selected>Good</option>
                    <option value="fair">Fair</option>
                    <option value="poor">Poor</option>
                    <option value="damaged">Damaged</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Condition Notes</label>
                <input type="text" name="copies[${copyIndex}][condition_notes]"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    placeholder="Optional notes">
            </div>
        </div>
    `;
    
    container.appendChild(newRow);
    copyIndex++;
    updateRemoveButtons();
}

function removeCopyRow(button) {
    const row = button.closest('.copy-row');
    row.remove();
    updateCopyNumbers();
    updateRemoveButtons();
}

function updateCopyNumbers() {
    const rows = document.querySelectorAll('.copy-row');
    rows.forEach((row, index) => {
        row.querySelector('.font-medium').textContent = `Copy #${index + 1}`;
    });
}

function updateRemoveButtons() {
    const rows = document.querySelectorAll('.copy-row');
    rows.forEach((row, index) => {
        const removeBtn = row.querySelector('.remove-btn, button[onclick="removeCopyRow(this)"]');
        if (removeBtn) {
            if (rows.length === 1) {
                removeBtn.classList.add('hidden');
            } else {
                removeBtn.classList.remove('hidden');
            }
        }
    });
}
</script>
@endsection
