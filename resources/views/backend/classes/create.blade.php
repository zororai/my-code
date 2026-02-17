@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Add New Class</h1>
                <p class="mt-2 text-sm text-gray-600">Create a new classroom and assign a class teacher</p>
            </div>
            <a href="{{ route('classes.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to List
            </a>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <form action="{{ route('classes.store') }}" method="POST">
            @csrf
            
            <!-- Class Details Section -->
            <div class="px-8 py-6 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <span class="w-8 h-8 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </span>
                    Class Details
                </h3>
            </div>
            
            <div class="px-8 py-6 space-y-6">
                <!-- Class Name Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Classes <span class="text-red-500">*</span></label>
                    <p class="text-xs text-gray-500 mb-3">Check all the classes you want to create. Already existing classes are disabled.</p>
                    
                    <!-- Select All / Deselect All -->
                    <div class="flex items-center gap-4 mb-4">
                        <button type="button" onclick="selectAllClasses()" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                            Select All Available
                        </button>
                        <span class="text-gray-300">|</span>
                        <button type="button" onclick="deselectAllClasses()" class="text-sm text-gray-600 hover:text-gray-800 font-medium">
                            Deselect All
                        </button>
                    </div>
                    
                    @error('selected_classes')
                        <p class="mb-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @error('selected_classes.*')
                        <p class="mb-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 max-h-64 overflow-y-auto p-4 border border-gray-200 rounded-lg bg-gray-50">
                        @foreach ($classFormats as $format)
                            @php
                                $exists = in_array($format->display_name, $existingClasses ?? []);
                            @endphp
                            <label class="flex items-center p-3 rounded-lg border transition-all cursor-pointer
                                {{ $exists ? 'bg-gray-100 border-gray-200 opacity-50 cursor-not-allowed' : 'bg-white border-gray-200 hover:border-blue-400 hover:bg-blue-50' }}">
                                <input type="checkbox" 
                                    name="selected_classes[]" 
                                    value="{{ $format->display_name }}" 
                                    data-numeric="{{ $format->numeric_value }}"
                                    class="class-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                    {{ $exists ? 'disabled' : '' }}
                                    {{ is_array(old('selected_classes')) && in_array($format->display_name, old('selected_classes')) ? 'checked' : '' }}>
                                <span class="ml-2 text-sm font-medium {{ $exists ? 'text-gray-400' : 'text-gray-700' }}">
                                    {{ $format->display_name }}
                                    @if($exists)
                                        <span class="text-xs text-gray-400">(exists)</span>
                                    @endif
                                </span>
                            </label>
                        @endforeach
                    </div>
                    
                    <div class="mt-3 flex items-center justify-between">
                        <p class="text-sm text-gray-600">
                            <span id="selectedCount">0</span> class(es) selected
                        </p>
                    </div>
                </div>
            </div>

            <!-- Submit Section -->
            <div class="px-8 py-6 bg-gray-50 border-t border-gray-200 flex items-center justify-end space-x-4">
                <a href="{{ route('classes.index') }}" class="px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Create Classes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function updateSelectedCount() {
        const checkboxes = document.querySelectorAll('.class-checkbox:checked:not(:disabled)');
        document.getElementById('selectedCount').textContent = checkboxes.length;
    }
    
    function selectAllClasses() {
        document.querySelectorAll('.class-checkbox:not(:disabled)').forEach(cb => cb.checked = true);
        updateSelectedCount();
    }
    
    function deselectAllClasses() {
        document.querySelectorAll('.class-checkbox:not(:disabled)').forEach(cb => cb.checked = false);
        updateSelectedCount();
    }
    
    // Update count on checkbox change
    document.querySelectorAll('.class-checkbox').forEach(cb => {
        cb.addEventListener('change', updateSelectedCount);
    });
    
    // Initial count
    updateSelectedCount();
</script>
@endsection