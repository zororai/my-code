@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Students</h1>
                    <p class="mt-2 text-sm text-gray-600">Manage and view all registered students by class</p>
                </div>
                <div class="flex items-center space-x-3">
                    <form action="{{ route('students.bulk-update-to-existing') }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to change ALL new students to existing students? This action cannot be undone.')">
                        @csrf
                        <button type="submit" disabled class="inline-flex items-center px-5 py-3 bg-amber-600 hover:bg-amber-700 text-gray-800 text-sm font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Mark All as Existing
                        </button>
                    </form>
                    <a href="{{ url('/student-with-parents/create') }}" class="inline-flex items-center px-5 py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 640 512">
                            <path d="M96 224c35.3 0 64-28.7 64-64s-28.7-64-64-64-64 28.7-64 64 28.7 64 64 64zm448 0c35.3 0 64-28.7 64-64s-28.7-64-64-64-64 28.7-64 64 28.7 64 64 64zm32 32h-64c-17.6 0-33.5 7.1-45.1 18.6 40.3 22.1 68.9 62 75.1 109.4h66c17.7 0 32-14.3 32-32v-32c0-35.3-28.7-64-64-64zm-256 0c61.9 0 112-50.1 112-112S381.9 32 320 32 208 82.1 208 144s50.1 112 112 112zm76.8 32h-8.3c-20.8 10-43.9 16-68.5 16s-47.6-6-68.5-16h-8.3C179.6 288 128 339.6 128 403.2V432c0 26.5 21.5 48 48 48h288c26.5 0 48-21.5 48-48v-28.8c0-63.6-51.6-115.2-115.2-115.2zm-223.7-13.4C161.5 263.1 145.6 256 128 256H64c-35.3 0-64 28.7-64 64v32c0 17.7 14.3 32 32 32h65.9c6.3-47.4 34.9-87.3 75.2-109.4z"/>
                        </svg>
                        Add Student + Parents
                    </a>
                </div>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="mb-6">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" id="studentSearch" placeholder="Search students by name, roll number, or email..." class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-all">
            </div>
        </div>

        <!-- Class Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mb-8">
            @forelse($classes as $class)
                <div class="class-card bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-lg hover:border-blue-300 transition-all cursor-pointer transform hover:-translate-y-1" data-class-id="{{ $class->id }}" data-class-name="{{ $class->class_name }}">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-blue-100 text-blue-800">
                                {{ $class->students_count }}
                            </span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">{{ $class->class_name }}</h3>
                        <p class="text-sm text-gray-500 mt-1">{{ $class->students_count }} {{ Str::plural('student', $class->students_count) }}</p>
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <p class="text-gray-500 text-lg font-medium">No classes found</p>
                        <p class="text-gray-400 text-sm mt-1">Create classes first to organize students</p>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Students List Section (Initially Hidden) -->
        <div id="studentsListSection" class="hidden">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <button id="backToClasses" class="mr-4 p-2 bg-white/20 hover:bg-white/30 rounded-lg transition-colors">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                            </button>
                            <div>
                                <h2 id="selectedClassName" class="text-xl font-bold text-white">Class Name</h2>
                                <p id="selectedClassCount" class="text-sm text-white/80">0 students</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <button id="bulkDeleteBtn" class="hidden px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-colors">
                                <svg class="w-4 h-4 inline mr-2" fill="currentColor" viewBox="0 0 448 512">
                                    <path d="M432 32H312l-9.4-18.7A24 24 0 0 0 281.1 0H166.8a23.72 23.72 0 0 0-21.4 13.3L136 32H16A16 16 0 0 0 0 48v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16zM53.2 467a48 48 0 0 0 47.9 45h245.8a48 48 0 0 0 47.9-45L416 128H32z"/>
                                </svg>
                                Delete Selected (<span id="selectedCount">0</span>)
                            </button>
                            <div class="relative">
                                <input type="text" id="classStudentSearch" placeholder="Search in class..." class="block w-64 pl-10 pr-4 py-2 border-0 rounded-lg bg-white/20 text-white placeholder-white/70 focus:ring-2 focus:ring-white/50 text-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-4 w-4 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    <input type="checkbox" id="selectAllStudents" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                </th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Student</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Roll Number</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Parent Status</th>
                                <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="studentsTableBody" class="bg-white divide-y divide-gray-200">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Search Results Section (Initially Hidden) -->
        <div id="searchResultsSection" class="hidden">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-600 to-gray-700 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <button id="clearSearch" class="mr-4 p-2 bg-white/20 hover:bg-white/30 rounded-lg transition-colors">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                            <div>
                                <h2 class="text-xl font-bold text-white">Search Results</h2>
                                <p id="searchResultsCount" class="text-sm text-white/80">0 students found</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    <input type="checkbox" id="selectAllSearchResults" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                </th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Student</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Roll Number</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Class</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Parent Status</th>
                                <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="searchResultsTableBody" class="bg-white divide-y divide-gray-200">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @include('backend.modals.delete',['name' => 'student'])

        <!-- SMS Success Modal -->
        @if(session('success') || session('warning') || session('error'))
        <div id="smsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 transform transition-all">
                <div class="p-6">
                    @if(session('success'))
                    <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-green-100 rounded-full">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-center text-gray-900 mb-2">Success!</h3>
                    <p class="text-center text-gray-600">{{ session('success') }}</p>
                    @elseif(session('warning'))
                    <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-yellow-100 rounded-full">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-center text-gray-900 mb-2">Warning</h3>
                    <p class="text-center text-gray-600">{{ session('warning') }}</p>
                    @elseif(session('error'))
                    <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-center text-gray-900 mb-2">Error</h3>
                    <p class="text-center text-gray-600">{{ session('error') }}</p>
                    @endif
                </div>
                <div class="px-6 pb-6">
                    <button onclick="document.getElementById('smsModal').classList.add('hidden')" class="w-full px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">
                        OK
                    </button>
                </div>
            </div>
        </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    const isSuperAdmin = {{ auth()->user()?->is_super_admin ? 'true' : 'false' }};

    $(function() {
        // Student data from server
        const students = @json($students);
        let currentClassId = null;
        let currentClassStudents = [];

        // Helper function to get parent status HTML
        function getParentStatusHtml(student) {
            const parents = student.parents || [];
            const totalParents = parents.length;
            const verifiedParents = parents.filter(p => p.registration_completed).length;
            const pendingParents = totalParents - verifiedParents;

            if (totalParents === 0) {
                return `<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                    <svg class="w-3 h-3 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    No Parents
                </span>`;
            } else if (verifiedParents === totalParents) {
                return `<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    <svg class="w-3 h-3 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    All Verified (${verifiedParents})
                </span>`;
            } else {
                return `<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    <svg class="w-3 h-3 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                    </svg>
                    Pending: ${pendingParents}/${totalParents}
                </span>`;
            }
        }

        // Helper function to get action buttons HTML
        function getActionsHtml(student, showResendSms = true) {
            const parents = student.parents || [];
            const pendingParents = parents.filter(p => !p.registration_completed).length;
            
            let html = `<div class="flex items-center justify-end space-x-2">
                <form action="/student/${student.id}/force-password-reset" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center p-2 bg-purple-100 hover:bg-purple-200 text-purple-600 rounded-lg transition-colors" title="Force Password Reset">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 512 512">
                            <path d="M336 0c-26.5 0-48 21.5-48 48v128c0 26.5 21.5 48 48 48h128c26.5 0 48-21.5 48-48V48c0-26.5-21.5-48-48-48H336zm12 68h104a12 12 0 0 1 12 12v56a12 12 0 0 1-12 12H348a12 12 0 0 1-12-12V80a12 12 0 0 1 12-12zm-116 76v128c0 26.5 21.5 48 48 48h128c26.5 0 48-21.5 48-48V256h-48v80H280V144H128v80h48V144c0-26.5 21.5-48 48-48h128v48H232z"/>
                        </svg>
                    </button>
                </form>
                <a href="/student/${student.id}" class="inline-flex items-center p-2 bg-blue-100 hover:bg-blue-200 text-blue-600 rounded-lg transition-colors" title="View Profile">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 576 512">
                        <path d="M288 144a110.94 110.94 0 0 0-31.24 5 55.4 55.4 0 0 1 7.24 27 56 56 0 0 1-56 56 55.4 55.4 0 0 1-27-7.24A111.71 111.71 0 1 0 288 144zm284.52 97.4C518.29 135.59 410.93 64 288 64S57.68 135.64 3.48 241.41a32.35 32.35 0 0 0 0 29.19C57.71 376.41 165.07 448 288 448s230.32-71.64 284.52-177.41a32.35 32.35 0 0 0 0-29.19zM288 400c-98.65 0-189.09-55-237.93-144C98.91 167 189.34 112 288 112s189.09 55 237.93 144C477.1 345 386.66 400 288 400z"/>
                    </svg>
                </a>
                <a href="/student/${student.id}/edit" class="inline-flex items-center p-2 bg-green-100 hover:bg-green-200 text-green-600 rounded-lg transition-colors" title="Edit">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 448 512">
                        <path d="M400 480H48c-26.5 0-48-21.5-48-48V80c0-26.5 21.5-48 48-48h352c26.5 0 48 21.5 48 48v352c0 26.5-21.5 48-48 48zM238.1 177.9L102.4 313.6l-6.3 57.1c-.8 7.6 5.6 14.1 13.3 13.3l57.1-6.3L302.2 242c2.3-2.3 2.3-6.1 0-8.5L246.7 178c-2.5-2.4-6.3-2.4-8.6-.1zM345 165.1L314.9 135c-9.4-9.4-24.6-9.4-33.9 0l-23.1 23.1c-2.3 2.3-2.3 6.1 0 8.5l55.5 55.5c2.3 2.3 6.1 2.3 8.5 0L345 199c9.3-9.3 9.3-24.5 0-33.9z"/>
                    </svg>
                </a>`;
            
            if (showResendSms && pendingParents > 0) {
                html += `<form action="/student/${student.id}/resend-parent-sms" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center p-2 bg-yellow-100 hover:bg-yellow-200 text-yellow-600 rounded-lg transition-colors" title="Resend SMS to Pending Parents">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 512 512">
                            <path d="M256 8C119.043 8 8 119.083 8 256c0 136.997 111.043 248 248 248s248-111.003 248-248C504 119.083 392.957 8 256 8zm0 448c-110.532 0-200-89.431-200-200 0-110.495 89.472-200 200-200 110.491 0 200 89.471 200 200 0 110.53-89.431 200-200 200zm42-104c0 23.159-18.841 42-42 42s-42-18.841-42-42 18.841-42 42-42 42 18.841 42 42zm-81.37-211.401l6.8 136c.319 6.387 5.591 11.401 11.985 11.401h44.262c6.394 0 11.666-5.014 11.985-11.401l6.8-136C298.909 137.596 295.653 128 288 128h-64c-7.653 0-10.909 9.596-7.37 12.599z"/>
                        </svg>
                    </button>
                </form>`;
            }
            
            if (isSuperAdmin) {
                html += `<a href="/student/${student.id}" data-url="/student/${student.id}" class="deletestudent inline-flex items-center p-2 bg-red-100 hover:bg-red-200 text-red-600 rounded-lg transition-colors" title="Delete">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 448 512">
                        <path d="M432 32H312l-9.4-18.7A24 24 0 0 0 281.1 0H166.8a23.72 23.72 0 0 0-21.4 13.3L136 32H16A16 16 0 0 0 0 48v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16zM53.2 467a48 48 0 0 0 47.9 45h245.8a48 48 0 0 0 47.9-45L416 128H32z"/>
                    </svg>
                </a>`;
            }
            html += `</div>`;
            
            return html;
        }

        // Helper function to render student row
        function renderStudentRow(student, showClass = false) {
            const profilePicture = student.user?.profile_picture || 'avatar.png';
            const className = student.class?.class_name || 'N/A';
            
            let html = `<tr class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap">
                    <input type="checkbox" class="student-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" data-student-id="${student.id}">
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                            <img class="h-10 w-10 rounded-full object-cover border-2 border-gray-200" src="/images/profile/${profilePicture}" alt="${student.user?.name || ''}">
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-semibold text-gray-900">${student.user?.name || ''}</div>
                            <div class="text-xs text-gray-500">${student.user?.email || ''}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                        ${student.roll_number || ''}
                    </span>
                </td>`;
            
            if (showClass) {
                html += `<td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">${className}</div>
                </td>`;
            }
            
            html += `<td class="px-6 py-4 whitespace-nowrap">
                    ${getParentStatusHtml(student)}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    ${getActionsHtml(student)}
                </td>
            </tr>`;
            
            return html;
        }

        // Show students for a specific class
        function showClassStudents(classId, className) {
            currentClassId = classId;
            currentClassStudents = students.filter(s => s.class_id == classId);
            
            $('#selectedClassName').text(className);
            $('#selectedClassCount').text(currentClassStudents.length + ' student' + (currentClassStudents.length !== 1 ? 's' : ''));
            
            renderClassStudents(currentClassStudents);
            
            $('.grid').addClass('hidden');
            $('#searchResultsSection').addClass('hidden');
            $('#studentsListSection').removeClass('hidden');
            $('#classStudentSearch').val('');
        }

        // Render class students table
        function renderClassStudents(studentsList) {
            let html = '';
            if (studentsList.length === 0) {
                html = `<tr>
                    <td colspan="4" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <p class="text-gray-500 text-lg font-medium">No students found</p>
                        </div>
                    </td>
                </tr>`;
            } else {
                studentsList.forEach(student => {
                    html += renderStudentRow(student, false);
                });
            }
            $('#studentsTableBody').html(html);
            bindDeleteEvents();
        }

        // Show search results
        function showSearchResults(searchTerm) {
            const filtered = students.filter(s => {
                const name = (s.user?.name || '').toLowerCase();
                const email = (s.user?.email || '').toLowerCase();
                const rollNumber = (s.roll_number || '').toLowerCase();
                const term = searchTerm.toLowerCase();
                return name.includes(term) || email.includes(term) || rollNumber.includes(term);
            });
            
            $('#searchResultsCount').text(filtered.length + ' student' + (filtered.length !== 1 ? 's' : '') + ' found');
            
            let html = '';
            if (filtered.length === 0) {
                html = `<tr>
                    <td colspan="5" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <p class="text-gray-500 text-lg font-medium">No students found</p>
                            <p class="text-gray-400 text-sm mt-1">Try a different search term</p>
                        </div>
                    </td>
                </tr>`;
            } else {
                filtered.forEach(student => {
                    html += renderStudentRow(student, true);
                });
            }
            $('#searchResultsTableBody').html(html);
            
            $('.grid').addClass('hidden');
            $('#studentsListSection').addClass('hidden');
            $('#searchResultsSection').removeClass('hidden');
            bindDeleteEvents();
        }

        // Back to classes view
        function backToClasses() {
            $('#studentsListSection').addClass('hidden');
            $('#searchResultsSection').addClass('hidden');
            $('.grid').removeClass('hidden');
            $('#studentSearch').val('');
            currentClassId = null;
        }

        // Bind delete events
        function bindDeleteEvents() {
            $(".deletestudent").off("click").on("click", function(event) {
                event.preventDefault();
                $("#deletemodal").removeClass("hidden");
                var url = $(this).attr('data-url');
                $(".remove-record").attr("action", url);
            });
        }

        // Event: Click on class card
        $('.class-card').on('click', function() {
            const classId = $(this).data('class-id');
            const className = $(this).data('class-name');
            showClassStudents(classId, className);
        });

        // Event: Back to classes button
        $('#backToClasses').on('click', function() {
            backToClasses();
        });

        // Event: Clear search button
        $('#clearSearch').on('click', function() {
            backToClasses();
        });

        // Event: Global search
        let searchTimeout;
        $('#studentSearch').on('input', function() {
            clearTimeout(searchTimeout);
            const searchTerm = $(this).val().trim();
            
            if (searchTerm.length === 0) {
                backToClasses();
                return;
            }
            
            searchTimeout = setTimeout(() => {
                if (searchTerm.length >= 2) {
                    showSearchResults(searchTerm);
                }
            }, 300);
        });

        // Event: Class student search
        $('#classStudentSearch').on('input', function() {
            const searchTerm = $(this).val().trim().toLowerCase();
            
            if (searchTerm.length === 0) {
                renderClassStudents(currentClassStudents);
                return;
            }
            
            const filtered = currentClassStudents.filter(s => {
                const name = (s.user?.name || '').toLowerCase();
                const email = (s.user?.email || '').toLowerCase();
                const rollNumber = (s.roll_number || '').toLowerCase();
                return name.includes(searchTerm) || email.includes(searchTerm) || rollNumber.includes(searchTerm);
            });
            
            renderClassStudents(filtered);
        });

        // Event: Delete modal close
        $("#deletemodelclose").on("click", function(event) {
            event.preventDefault();
            $("#deletemodal").addClass("hidden");
        });

        // Bulk delete functionality
        let selectedStudents = [];

        // Update selected count and button visibility
        function updateBulkDeleteButton() {
            const count = selectedStudents.length;
            $('#selectedCount').text(count);
            if (count > 0) {
                $('#bulkDeleteBtn').removeClass('hidden');
            } else {
                $('#bulkDeleteBtn').addClass('hidden');
            }
        }

        // Handle individual checkbox change
        $(document).on('change', '.student-checkbox', function() {
            const studentId = $(this).data('student-id');
            if ($(this).is(':checked')) {
                if (!selectedStudents.includes(studentId)) {
                    selectedStudents.push(studentId);
                }
            } else {
                selectedStudents = selectedStudents.filter(id => id !== studentId);
                $('#selectAllStudents, #selectAllSearchResults').prop('checked', false);
            }
            updateBulkDeleteButton();
        });

        // Handle select all for class students
        $(document).on('change', '#selectAllStudents', function() {
            const isChecked = $(this).is(':checked');
            $('.student-checkbox').prop('checked', isChecked);
            
            if (isChecked) {
                selectedStudents = currentClassStudents.map(s => s.id);
            } else {
                selectedStudents = [];
            }
            updateBulkDeleteButton();
        });

        // Handle select all for search results
        $(document).on('change', '#selectAllSearchResults', function() {
            const isChecked = $(this).is(':checked');
            $('.student-checkbox').prop('checked', isChecked);
            
            if (isChecked) {
                selectedStudents = [];
                $('.student-checkbox').each(function() {
                    selectedStudents.push($(this).data('student-id'));
                });
            } else {
                selectedStudents = [];
            }
            updateBulkDeleteButton();
        });

        // Handle bulk delete button click
        $('#bulkDeleteBtn').on('click', function() {
            if (selectedStudents.length === 0) return;
            
            if (confirm(`Are you sure you want to delete ${selectedStudents.length} student(s)? This action cannot be undone.`)) {
                // Create form and submit
                const form = $('<form>', {
                    method: 'POST',
                    action: '{{ route("students.bulk-delete") }}'
                });
                
                form.append($('<input>', {
                    type: 'hidden',
                    name: '_token',
                    value: '{{ csrf_token() }}'
                }));
                
                selectedStudents.forEach(id => {
                    form.append($('<input>', {
                        type: 'hidden',
                        name: 'student_ids[]',
                        value: id
                    }));
                });
                
                $('body').append(form);
                form.submit();
            }
        });

        // Reset selections when changing views
        function resetSelections() {
            selectedStudents = [];
            updateBulkDeleteButton();
            $('#selectAllStudents, #selectAllSearchResults').prop('checked', false);
        }

        // Update back to classes to reset selections
        const originalBackToClasses = backToClasses;
        backToClasses = function() {
            resetSelections();
            originalBackToClasses();
        };
    });
</script>
@endpush
