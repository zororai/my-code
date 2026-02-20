@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Add New Book</h1>
                <p class="text-gray-600">Add a book to the library archive</p>
            </div>
            <a href="{{ route('admin.library.books') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
                </svg>
                Back
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
            <form action="{{ route('admin.library.books.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Book Title -->
                    <div class="md:col-span-2">
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Book Title *</label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Enter book title">
                    </div>

                    <!-- Book Number -->
                    <div>
                        <label for="book_number" class="block text-sm font-medium text-gray-700 mb-2">Book Number *</label>
                        <input type="text" name="book_number" id="book_number" value="{{ old('book_number') }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="e.g., LIB-001">
                    </div>

                    <!-- Author -->
                    <div>
                        <label for="author" class="block text-sm font-medium text-gray-700 mb-2">Author</label>
                        <input type="text" name="author" id="author" value="{{ old('author') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Enter author name">
                    </div>

                    <!-- ISBN -->
                    <div>
                        <label for="isbn" class="block text-sm font-medium text-gray-700 mb-2">ISBN</label>
                        <input type="text" name="isbn" id="isbn" value="{{ old('isbn') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Enter ISBN">
                    </div>

                    <!-- Category -->
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <input type="text" name="category" id="category" value="{{ old('category') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="e.g., Fiction, Science, History">
                    </div>

                    <!-- Add Copies with ISBN -->
                    <div class="md:col-span-2">
                        <div class="flex items-center mb-2">
                            <input type="checkbox" id="add_copies" name="add_copies" class="mr-2 h-4 w-4 text-blue-600 border-gray-300 rounded" onchange="toggleCopiesSection()">
                            <label for="add_copies" class="text-sm font-medium text-gray-700">Add individual copies with unique ISBN numbers</label>
                        </div>
                        <p class="text-xs text-gray-500">Check this to add multiple copies, each with a unique ISBN number for tracking</p>
                    </div>

                    <!-- Simple Quantity (when not adding individual copies) -->
                    <div id="simpleQuantitySection">
                        <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">Quantity *</label>
                        <input type="number" name="quantity" id="quantity" value="{{ old('quantity', 1) }}" min="1"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Condition -->
                    <div>
                        <label for="condition" class="block text-sm font-medium text-gray-700 mb-2">Book Condition *</label>
                        <select name="condition" id="condition" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="excellent" {{ old('condition') == 'excellent' ? 'selected' : '' }}>Excellent</option>
                            <option value="good" {{ old('condition', 'good') == 'good' ? 'selected' : '' }}>Good</option>
                            <option value="fair" {{ old('condition') == 'fair' ? 'selected' : '' }}>Fair</option>
                            <option value="poor" {{ old('condition') == 'poor' ? 'selected' : '' }}>Poor</option>
                            <option value="damaged" {{ old('condition') == 'damaged' ? 'selected' : '' }}>Damaged</option>
                        </select>
                    </div>

                    <!-- Condition Notes -->
                    <div class="md:col-span-2">
                        <label for="condition_notes" class="block text-sm font-medium text-gray-700 mb-2">Condition Notes</label>
                        <textarea name="condition_notes" id="condition_notes" rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Describe the book's condition (e.g., torn pages, water damage, etc.)">{{ old('condition_notes') }}</textarea>
                    </div>

                    <!-- Book Image -->
                    <div class="md:col-span-2">
                        <label for="image" class="block text-sm font-medium text-gray-700 mb-2">Book Image (Optional)</label>
                        <input type="file" name="image" id="image" accept="image/*"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Accepted formats: JPEG, PNG, JPG, GIF. Max size: 2MB</p>
                    </div>
                </div>

                <!-- Book Copies Section (hidden by default) -->
                <div id="copiesSection" class="md:col-span-2 hidden">
                    <div class="border-t pt-4 mt-4">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Book Copies (with unique ISBNs)</h3>
                            <button type="button" onclick="addCopyRow()" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm">
                                + Add Copy
                            </button>
                        </div>
                        <div id="copiesContainer">
                            <div class="copy-row bg-gray-50 border rounded-lg p-3 mb-2" data-index="0">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                                    <div class="flex items-center">
                                        <span class="text-sm font-medium text-gray-600 mr-2">#1</span>
                                    </div>
                                    <div>
                                        <input type="text" name="copies[0][isbn]" placeholder="ISBN *" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <select name="copies[0][condition]" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                            <option value="excellent">Excellent</option>
                                            <option value="good" selected>Good</option>
                                            <option value="fair">Fair</option>
                                            <option value="poor">Poor</option>
                                            <option value="damaged">Damaged</option>
                                        </select>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="text" name="copies[0][condition_notes]" placeholder="Notes (optional)" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                        <button type="button" onclick="removeCopyRow(this)" class="ml-2 text-red-500 hover:text-red-700 hidden remove-btn">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Each copy must have a unique ISBN number for individual tracking and borrowing.</p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <a href="{{ route('admin.library.books') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg">
                        Cancel
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                        Add Book
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let copyIndex = 1;

function toggleCopiesSection() {
    const checkbox = document.getElementById('add_copies');
    const copiesSection = document.getElementById('copiesSection');
    const simpleQuantitySection = document.getElementById('simpleQuantitySection');
    const quantityInput = document.getElementById('quantity');
    const isbnInput = document.getElementById('isbn');
    
    if (checkbox.checked) {
        copiesSection.classList.remove('hidden');
        simpleQuantitySection.classList.add('hidden');
        quantityInput.removeAttribute('required');
        // Disable the single ISBN field when adding individual copies
        isbnInput.disabled = true;
        isbnInput.value = '';
        isbnInput.classList.add('bg-gray-100', 'cursor-not-allowed');
        isbnInput.placeholder = 'Disabled - using individual ISBNs below';
    } else {
        copiesSection.classList.add('hidden');
        simpleQuantitySection.classList.remove('hidden');
        quantityInput.setAttribute('required', 'required');
        // Re-enable the single ISBN field
        isbnInput.disabled = false;
        isbnInput.classList.remove('bg-gray-100', 'cursor-not-allowed');
        isbnInput.placeholder = 'Enter ISBN';
    }
}

function addCopyRow() {
    const container = document.getElementById('copiesContainer');
    const newRow = document.createElement('div');
    newRow.className = 'copy-row bg-gray-50 border rounded-lg p-3 mb-2';
    newRow.dataset.index = copyIndex;
    
    newRow.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div class="flex items-center">
                <span class="text-sm font-medium text-gray-600 mr-2">#${copyIndex + 1}</span>
            </div>
            <div>
                <input type="text" name="copies[${copyIndex}][isbn]" placeholder="ISBN *" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <select name="copies[${copyIndex}][condition]" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="excellent">Excellent</option>
                    <option value="good" selected>Good</option>
                    <option value="fair">Fair</option>
                    <option value="poor">Poor</option>
                    <option value="damaged">Damaged</option>
                </select>
            </div>
            <div class="flex items-center">
                <input type="text" name="copies[${copyIndex}][condition_notes]" placeholder="Notes (optional)" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <button type="button" onclick="removeCopyRow(this)" class="ml-2 text-red-500 hover:text-red-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    `;
    
    container.appendChild(newRow);
    copyIndex++;
    updateCopyNumbers();
}

function removeCopyRow(button) {
    const row = button.closest('.copy-row');
    row.remove();
    updateCopyNumbers();
}

function updateCopyNumbers() {
    const rows = document.querySelectorAll('.copy-row');
    rows.forEach((row, index) => {
        const numberSpan = row.querySelector('.text-gray-600');
        if (numberSpan) {
            numberSpan.textContent = `#${index + 1}`;
        }
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
