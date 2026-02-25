@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Assign Subject to Teacher</h1>
                <p class="mt-2 text-sm text-gray-600">Link subjects to teachers for class assignments</p>
            </div>
            <a href="{{ route('admin.subjects.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Subjects
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-r-lg">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span class="text-green-700">{{ session('success') }}</span>
        </div>
    </div>
    @endif

    @if(session('warning'))
    <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-r-lg">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-yellow-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <div class="text-yellow-700 text-sm">{!! session('warning') !!}</div>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-red-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <div class="text-red-700 text-sm">{!! session('error') !!}</div>
        </div>
    </div>
    @endif

    <!-- Bulk Import Section -->
    <div class="mb-8 bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-purple-500 to-indigo-600">
            <h3 class="text-lg font-semibold text-white flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                Bulk Import Assignments
            </h3>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Instructions -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-900 mb-3">How to Import</h4>
                    <ol class="text-sm text-gray-600 space-y-2 list-decimal list-inside">
                        <li>Download the CSV template below</li>
                        <li>Fill in teacher email/name and subject codes</li>
                        <li>Save the file as CSV format</li>
                        <li>Upload the completed file</li>
                    </ol>
                    
                    <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-3">
                        <p class="text-xs text-blue-800 font-medium mb-1">Required Columns:</p>
                        <ul class="text-xs text-blue-700 space-y-1">
                            <li><strong>teacher_email</strong> or <strong>teacher_name</strong> - To identify teacher</li>
                            <li><strong>subject_code</strong> - Subject code (e.g., F1R EH)</li>
                        </ul>
                    </div>
                    
                    <a href="{{ route('admin.subjects.assign.template') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-white border-2 border-purple-600 text-purple-600 hover:bg-purple-50 text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Download CSV Template
                    </a>
                </div>
                
                <!-- Upload Form -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-900 mb-3">Upload File</h4>
                    <form action="{{ route('admin.subjects.assign.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                        @csrf
                        
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-purple-500 transition-colors">
                            <input type="file" name="import_file" id="import_file" accept=".csv,.xlsx,.xls" required class="hidden" onchange="updateFileName(this)">
                            <label for="import_file" class="cursor-pointer">
                                <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-sm text-gray-600 mb-1">Click to select file</p>
                                <p class="text-xs text-gray-500">CSV, XLSX, or XLS (Max 2MB)</p>
                            </label>
                            <p id="fileName" class="mt-3 text-sm text-gray-700 font-medium hidden"></p>
                        </div>
                        
                        @error('import_file')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        
                        <button type="submit" class="mt-4 w-full px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            Import Assignments
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Assignment Form -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-green-600">
                <h3 class="text-lg font-semibold text-white flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    New Assignment
                </h3>
            </div>
            
            <form action="{{ route('admin.subjects.assign.store') }}" method="POST" class="p-6 space-y-6">
                @csrf
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Teacher <span class="text-red-500">*</span></label>
                    <select name="teacher_id" required class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors @error('teacher_id') border-red-500 @enderror">
                        <option value="">-- Choose a Teacher --</option>
                        @foreach ($teachers as $teacher)
                            @if($teacher->user)
                                <option value="{{ $teacher->id }}">{{ $teacher->user->name }}</option>
                            @endif
                        @endforeach
                    </select>
                    @error('teacher_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Subjects <span class="text-red-500">*</span></label>
                    
                    <!-- Filter Field -->
                    <div class="mb-2">
                        <div class="relative">
                            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                            <input type="text" id="subjectSearch" placeholder="Filter subjects..." class="w-full px-4 py-2 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">
                        </div>
                    </div>
                    
                    <div class="border border-gray-300 rounded-lg max-h-48 overflow-y-auto p-2 @error('subject_ids') border-red-500 @enderror" id="subjectList">
                        @forelse ($subjects as $subject)
                            <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer subject-item" data-subject-name="{{ strtolower($subject->name) }}" data-subject-code="{{ strtolower($subject->subject_code) }}">
                                <input type="checkbox" name="subject_ids[]" value="{{ $subject->id }}" class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                                <span class="ml-3 text-sm text-gray-700">{{ $subject->name }} <span class="text-gray-400">({{ $subject->subject_code }})</span></span>
                            </label>
                        @empty
                            <p class="p-2 text-sm text-gray-500 text-center">No unassigned subjects available</p>
                        @endforelse
                        <p id="noResults" class="p-2 text-sm text-gray-500 text-center hidden">No subjects match your search</p>
                    </div>
                    @error('subject_ids')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @if($subjects->isEmpty())
                        <p class="mt-2 text-sm text-amber-600">
                            <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            All subjects are already assigned. Create new subjects or unassign existing ones.
                        </p>
                    @endif
                    <p class="mt-1 text-xs text-gray-500">Select one or more subjects to assign</p>
                </div>

                <button type="submit" class="w-full px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all flex items-center justify-center" {{ $subjects->isEmpty() ? 'disabled' : '' }}>
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                    Assign Subject
                </button>
            </form>
        </div>

        <!-- Current Assignments -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-500 to-indigo-600">
                <h3 class="text-lg font-semibold text-white flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Current Assignments (<span id="assignmentCount">{{ $assignedSubjects->count() }}</span>)
                </h3>
            </div>
            
            <!-- Filter Field and Bulk Actions for Assignments -->
            <div class="px-4 pt-4 pb-2">
                <div class="relative mb-3">
                    <svg class="w-5 h-5 text-gray-400 absolute left-7 top-1/2 transform -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <input type="text" id="assignmentSearch" placeholder="Filter assignments..." class="w-full px-4 py-2 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                </div>
                <div id="bulkActions" class="hidden bg-red-50 border border-red-200 rounded-lg p-3 flex items-center justify-between">
                    <span class="text-sm text-red-700"><span id="selectedCount">0</span> assignment(s) selected</span>
                    <button type="button" onclick="showBulkUnassignDialog()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
                        Remove Selected
                    </button>
                </div>
            </div>
            
            <div class="divide-y divide-gray-100 max-h-96 overflow-y-auto" id="assignmentList">
                @forelse ($assignedSubjects as $subject)
                    <div class="p-4 hover:bg-gray-50 transition-colors flex items-center justify-between assignment-item" data-subject-name="{{ strtolower($subject->name) }}" data-teacher-name="{{ strtolower(optional(optional($subject->teacher)->user)->name ?? 'Unknown') }}" data-subject-code="{{ strtolower($subject->subject_code) }}">
                        <div class="flex items-center space-x-3 flex-1">
                            <input type="checkbox" class="assignment-checkbox w-5 h-5 text-red-600 border-gray-300 rounded focus:ring-red-500" value="{{ $subject->id }}" onchange="updateBulkActions()">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white text-sm font-bold">
                                {{ strtoupper(substr(optional(optional($subject->teacher)->user)->name ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $subject->name }}</p>
                                <p class="text-xs text-gray-500">{{ optional(optional($subject->teacher)->user)->name ?? 'Unknown Teacher' }} • Code: {{ $subject->subject_code }}</p>
                            </div>
                        </div>
                        <form action="{{ route('admin.subjects.unassign', $subject->id) }}" method="POST" id="unassignForm{{ $subject->id }}">
                            @csrf
                            @method('DELETE')
                            <button type="button" onclick="showUnassignDialog('{{ $subject->id }}', '{{ $subject->name }}', '{{ optional(optional($subject->teacher)->user)->name ?? 'Unknown Teacher' }}')" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Remove Assignment">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                @empty
                    <div class="p-8 text-center" id="emptyAssignments">
                        <div class="w-16 h-16 mx-auto rounded-full bg-gray-100 flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                        </div>
                        <p class="text-gray-500 text-sm">No subjects assigned yet</p>
                    </div>
                @endforelse
                <div id="noAssignmentResults" class="p-8 text-center hidden">
                    <div class="w-16 h-16 mx-auto rounded-full bg-gray-100 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <p class="text-gray-500 text-sm">No assignments match your filter</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Unassign Confirmation Dialog -->
<div id="unassignDialog" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform transition-all">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mr-4">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Remove Assignment</h3>
                    <p class="text-sm text-gray-500">This action cannot be undone</p>
                </div>
            </div>
        </div>
        <div class="px-6 py-4">
            <p class="text-gray-700 mb-2">Are you sure you want to remove this assignment?</p>
            <div class="bg-gray-50 rounded-lg p-3 mt-3">
                <p class="text-sm text-gray-600"><strong>Subject:</strong> <span id="dialogSubjectName"></span></p>
                <p class="text-sm text-gray-600 mt-1"><strong>Teacher:</strong> <span id="dialogTeacherName"></span></p>
            </div>
        </div>
        <div class="px-6 py-4 bg-gray-50 rounded-b-2xl flex justify-end space-x-3">
            <button type="button" onclick="closeUnassignDialog()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors font-medium">
                Cancel
            </button>
            <button type="button" onclick="confirmUnassign()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors font-medium">
                Remove Assignment
            </button>
        </div>
    </div>
</div>

<!-- Bulk Unassign Confirmation Dialog -->
<div id="bulkUnassignDialog" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform transition-all">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mr-4">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Remove Multiple Assignments</h3>
                    <p class="text-sm text-gray-500">This action cannot be undone</p>
                </div>
            </div>
        </div>
        <div class="px-6 py-4">
            <p class="text-gray-700 mb-2">Are you sure you want to remove <strong><span id="bulkDialogCount">0</span> assignment(s)</strong>?</p>
            <p class="text-sm text-gray-500 mt-2">All selected teacher assignments will be removed.</p>
        </div>
        <div class="px-6 py-4 bg-gray-50 rounded-b-2xl flex justify-end space-x-3">
            <button type="button" onclick="closeBulkUnassignDialog()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors font-medium">
                Cancel
            </button>
            <button type="button" onclick="confirmBulkUnassign()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors font-medium">
                Remove All Selected
            </button>
        </div>
    </div>
</div>

<script>
// Filter unassigned subjects
document.getElementById('subjectSearch').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase().trim();
    const subjectItems = document.querySelectorAll('.subject-item');
    const noResults = document.getElementById('noResults');
    let visibleCount = 0;
    
    subjectItems.forEach(item => {
        const subjectName = item.dataset.subjectName;
        const subjectCode = item.dataset.subjectCode;
        
        if (subjectName.includes(searchTerm) || subjectCode.includes(searchTerm)) {
            item.classList.remove('hidden');
            visibleCount++;
        } else {
            item.classList.add('hidden');
        }
    });
    
    // Show/hide "no results" message
    if (visibleCount === 0 && subjectItems.length > 0) {
        noResults.classList.remove('hidden');
    } else {
        noResults.classList.add('hidden');
    }
});

// Filter assigned subjects
document.getElementById('assignmentSearch').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase().trim();
    const assignmentItems = document.querySelectorAll('.assignment-item');
    const noResults = document.getElementById('noAssignmentResults');
    const assignmentCount = document.getElementById('assignmentCount');
    let visibleCount = 0;
    
    assignmentItems.forEach(item => {
        const subjectName = item.dataset.subjectName;
        const teacherName = item.dataset.teacherName;
        const subjectCode = item.dataset.subjectCode;
        
        if (subjectName.includes(searchTerm) || teacherName.includes(searchTerm) || subjectCode.includes(searchTerm)) {
            item.classList.remove('hidden');
            visibleCount++;
        } else {
            item.classList.add('hidden');
        }
    });
    
    // Update count
    assignmentCount.textContent = visibleCount;
    
    // Show/hide "no results" message
    if (visibleCount === 0 && assignmentItems.length > 0) {
        noResults.classList.remove('hidden');
    } else {
        noResults.classList.add('hidden');
    }
});

// Unassign dialog functions
let currentSubjectId = null;

function showUnassignDialog(subjectId, subjectName, teacherName) {
    currentSubjectId = subjectId;
    document.getElementById('dialogSubjectName').textContent = subjectName;
    document.getElementById('dialogTeacherName').textContent = teacherName;
    document.getElementById('unassignDialog').classList.remove('hidden');
    document.getElementById('unassignDialog').classList.add('flex');
}

function closeUnassignDialog() {
    document.getElementById('unassignDialog').classList.add('hidden');
    document.getElementById('unassignDialog').classList.remove('flex');
    currentSubjectId = null;
}

function confirmUnassign() {
    if (currentSubjectId) {
        document.getElementById('unassignForm' + currentSubjectId).submit();
    }
}

// Close dialog on background click
document.getElementById('unassignDialog').addEventListener('click', function(e) {
    if (e.target === this) {
        closeUnassignDialog();
    }
});

// Bulk actions functions
function updateBulkActions() {
    const checkboxes = document.querySelectorAll('.assignment-checkbox:checked');
    const count = checkboxes.length;
    const bulkActions = document.getElementById('bulkActions');
    const selectedCount = document.getElementById('selectedCount');
    
    selectedCount.textContent = count;
    
    if (count > 0) {
        bulkActions.classList.remove('hidden');
    } else {
        bulkActions.classList.add('hidden');
    }
}

function showBulkUnassignDialog() {
    const checkboxes = document.querySelectorAll('.assignment-checkbox:checked');
    const count = checkboxes.length;
    
    if (count === 0) return;
    
    document.getElementById('bulkDialogCount').textContent = count;
    document.getElementById('bulkUnassignDialog').classList.remove('hidden');
    document.getElementById('bulkUnassignDialog').classList.add('flex');
}

function closeBulkUnassignDialog() {
    document.getElementById('bulkUnassignDialog').classList.add('hidden');
    document.getElementById('bulkUnassignDialog').classList.remove('flex');
}

function confirmBulkUnassign() {
    const checkboxes = document.querySelectorAll('.assignment-checkbox:checked');
    const subjectIds = Array.from(checkboxes).map(cb => cb.value);
    
    if (subjectIds.length === 0) return;
    
    // Create a form to submit all subject IDs at once
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.subjects.bulkUnassign") }}';
    
    // Add CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    form.appendChild(csrfInput);
    
    // Add subject IDs
    subjectIds.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'subject_ids[]';
        input.value = id;
        form.appendChild(input);
    });
    
    document.body.appendChild(form);
    form.submit();
}

// Close bulk dialog on background click
document.getElementById('bulkUnassignDialog').addEventListener('click', function(e) {
    if (e.target === this) {
        closeBulkUnassignDialog();
    }
});

// Update file name display when file is selected
function updateFileName(input) {
    const fileNameDisplay = document.getElementById('fileName');
    if (input.files && input.files[0]) {
        fileNameDisplay.textContent = '📄 ' + input.files[0].name;
        fileNameDisplay.classList.remove('hidden');
    } else {
        fileNameDisplay.classList.add('hidden');
    }
}
</script>
@endsection
