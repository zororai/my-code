@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $class->class_name }} - Assessments</h1>
                    <p class="mt-2 text-sm text-gray-600">Manage assessments for this class</p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('teacher.assessment') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back to Classes
                    </a>
                    <button type="button" onclick="openGradeSystemModal()" class="inline-flex items-center px-5 py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 576 512">
                            <path d="M528 32H48C21.5 32 0 53.5 0 80v352c0 26.5 21.5 48 48 48h480c26.5 0 48-21.5 48-48V80c0-26.5-21.5-48-48-48zm0 400H48V80h480v352zM208 256c35.3 0 64-28.7 64-64s-28.7-64-64-64-64 28.7-64 64 28.7 64 64 64zm-89.6 128h179.2c12.4 0 22.4-8.6 22.4-19.2v-19.2c0-31.8-30.1-57.6-67.2-57.6-10.8 0-18.7 8-44.8 8-26.9 0-33.4-8-44.8-8-37.1 0-67.2 25.8-67.2 57.6v19.2c0 10.6 10 19.2 22.4 19.2zM360 320h112c4.4 0 8-3.6 8-8v-16c0-4.4-3.6-8-8-8H360c-4.4 0-8 3.6-8 8v16c0 4.4 3.6 8 8 8zm0-64h112c4.4 0 8-3.6 8-8v-16c0-4.4-3.6-8-8-8H360c-4.4 0-8 3.6-8 8v16c0 4.4 3.6 8 8 8zm0-64h112c4.4 0 8-3.6 8-8v-16c0-4.4-3.6-8-8-8H360c-4.4 0-8 3.6-8 8v16c0 4.4 3.6 8 8 8z"/>
                        </svg>
                        Grade System
                    </button>
                    <button type="button" onclick="openAssessmentModal()" class="inline-flex items-center px-5 py-3 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 448 512">
                            <path d="M416 208H272V64c0-17.67-14.33-32-32-32h-32c-17.67 0-32 14.33-32 32v144H32c-17.67 0-32 14.33-32 32v32c0 17.67 14.33 32 32 32h144v144c0 17.67 14.33 32 32 32h32c17.67 0 32-14.33 32-32V304h144c17.67 0 32-14.33 32-32v-32c0-17.67-14.33-32-32-32z"/>
                        </svg>
                        Create New Assessment
                    </button>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Assessments Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Assessed Subject & Class
                            </th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Assessment Type
                            </th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Date
                            </th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Topic
                            </th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Exam
                            </th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Due Date
                            </th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($assessments as $assessment)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900">{{ $assessment->subject->name ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-500">{{ $class->class_name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold 
                                        @if($assessment->assessment_type == 'Quiz') bg-blue-100 text-blue-800
                                        @elseif($assessment->assessment_type == 'Test') bg-purple-100 text-purple-800
                                        @elseif($assessment->assessment_type == 'Assignment') bg-green-100 text-green-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ $assessment->assessment_type ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $assessment->date ? date('M d, Y', strtotime($assessment->date)) : 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">{{ $assessment->topic ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $assessment->exam ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $assessment->due_date ? date('M d, Y', strtotime($assessment->due_date)) : 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-2">
                                        @if($assessment->exercise)
                                            <a href="{{ route('exercises.show', $assessment->exercise->id) }}" class="inline-flex items-center px-2 py-1 bg-amber-100 hover:bg-amber-200 text-amber-700 rounded-lg transition-colors text-xs font-medium" title="View Online Exercise">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                Awaiting Marks
                                            </a>
                                        @else
                                            <a href="{{ route('exercises.create', ['assessment_id' => $assessment->id]) }}" class="inline-flex items-center p-2 bg-purple-100 hover:bg-purple-200 text-purple-600 rounded-lg transition-colors" title="Create Online Exercise">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                                </svg>
                                            </a>
                                        @endif
                                        <button type="button" onclick="viewAssessment({{ $assessment->id }})" class="inline-flex items-center p-2 bg-blue-100 hover:bg-blue-200 text-blue-600 rounded-lg transition-colors" title="View">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </button>
                                        <button type="button" onclick="editAssessment({{ $assessment->id }})" class="inline-flex items-center p-2 bg-green-100 hover:bg-green-200 text-green-600 rounded-lg transition-colors" title="Edit">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 512 512">
                                                <path d="M471.6 21.7c-21.9-21.9-57.3-21.9-79.2 0L362.3 51.7l97.9 97.9 30.1-30.1c21.9-21.9 21.9-57.3 0-79.2L471.6 21.7zm-299.2 220c-6.1 6.1-10.8 13.6-13.5 21.9l-29.6 88.8c-2.9 8.6-.6 18.1 5.8 24.6s15.9 8.7 24.6 5.8l88.8-29.6c8.2-2.7 15.7-7.4 21.9-13.5L437.7 172.3 339.7 74.3 172.4 241.7zM96 64C43 64 0 107 0 160V416c0 53 43 96 96 96H352c53 0 96-43 96-96V320c0-17.7-14.3-32-32-32s-32 14.3-32 32v96c0 17.7-14.3 32-32 32H96c-17.7 0-32-14.3-32-32V160c0-17.7 14.3-32 32-32h96c17.7 0 32-14.3 32-32s-14.3-32-32-32H96z"/>
                                            </svg>
                                        </button>
                                        <button type="button" onclick="deleteAssessment({{ $assessment->id }})" class="inline-flex items-center p-2 bg-red-100 hover:bg-red-200 text-red-600 rounded-lg transition-colors" title="Delete">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 448 512">
                                                <path d="M432 32H312l-9.4-18.7A24 24 0 0 0 281.1 0H166.8a23.72 23.72 0 0 0-21.4 13.3L136 32H16A16 16 0 0 0 0 48v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16zM53.2 467a48 48 0 0 0 47.9 45h245.8a48 48 0 0 0 47.9-45L416 128H32z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                        <p class="text-gray-500 text-lg font-medium">No assessments found</p>
                                        <p class="text-gray-400 text-sm mt-1">Create your first assessment to get started</p>
                                        <button type="button" onclick="openAssessmentModal()" class="mt-4 inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition-colors">
                                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 448 512">
                                                <path d="M416 208H272V64c0-17.67-14.33-32-32-32h-32c-17.67 0-32 14.33-32 32v144H32c-17.67 0-32 14.33-32 32v32c0 17.67 14.33 32 32 32h144v144c0 17.67 14.33 32 32 32h32c17.67 0 32-14.33 32-32V304h144c17.67 0 32-14.33 32-32v-32c0-17.67-14.33-32-32-32z"/>
                                            </svg>
                                            Create Assessment
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Marked Assessments Section -->
        @if(isset($markedAssessments) && $markedAssessments->count() > 0)
        <div class="mt-8">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Marked Assessments</h2>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gradient-to-r from-green-50 to-green-100">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Subject & Class
                                </th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Assessment Type
                                </th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Date
                                </th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Topic
                                </th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Action
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($markedAssessments as $assessment)
                                <tr class="hover:bg-green-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-gray-900">{{ $assessment->subject->name ?? 'N/A' }}</div>
                                        <div class="text-xs text-gray-500">{{ $class->class_name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold 
                                            @if($assessment->assessment_type == 'Quiz') bg-blue-100 text-blue-800
                                            @elseif($assessment->assessment_type == 'Test') bg-purple-100 text-purple-800
                                            @elseif($assessment->assessment_type == 'Assignment') bg-green-100 text-green-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ $assessment->assessment_type ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $assessment->date ? date('M d, Y', strtotime($assessment->date)) : 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">{{ $assessment->topic ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                            Marked
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-2">
                                            <button type="button" onclick="viewMarkedAssessment({{ $assessment->id }}, '{{ $assessment->subject->name ?? 'N/A' }}', '{{ $assessment->assessment_type ?? 'N/A' }}', '{{ addslashes($assessment->topic ?? 'N/A') }}')" class="inline-flex items-center p-2 bg-blue-100 hover:bg-blue-200 text-blue-600 rounded-lg transition-colors" title="View Marks">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </button>
                                            <a href="{{ route('teacher.assessment.marking.scheme', $class->id) }}?assessment={{ $assessment->id }}" class="inline-flex items-center p-2 bg-green-100 hover:bg-green-200 text-green-600 rounded-lg transition-colors" title="View Full Marking Scheme">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Assessment Modal -->
    <div id="assessmentModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-8 w-full max-w-2xl">
            <div class="bg-white rounded-2xl shadow-2xl">
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-8 py-6 border-b border-gray-200">
                    <h3 class="text-2xl font-bold text-gray-900">ASSESSMENT DETAILS</h3>
                    <button type="button" onclick="closeAssessmentModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <form action="{{ route('teacher.assessment.store') }}" method="POST" class="px-8 py-6" id="assessmentForm" onsubmit="return validateAssessmentForm()">
                    @csrf
                    <input type="hidden" name="class_id" value="{{ $class->id }}">

                    <!-- Current Term/Year Info -->
                    <div class="mb-5 flex items-center gap-3 px-4 py-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <svg class="w-5 h-5 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <div>
                            <span class="text-xs text-blue-500 font-medium uppercase tracking-wide">Auto-assigned term</span>
                            <p class="text-sm font-semibold text-blue-800">
                                @if($currentTerm)
                                    Term {{ $currentTerm->result_period === 'first' ? '1' : ($currentTerm->result_period === 'second' ? '2' : '3') }}
                                    &nbsp;&bull;&nbsp; {{ $currentTerm->year }}
                                @else
                                    No active term found — go to <a href="{{ route('results_status.index') }}" class="underline">Term settings</a> to create one.
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Validation Errors -->
                    @if($errors->any())
                        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative" role="alert">
                            <strong class="font-bold">Validation Error!</strong>
                            <ul class="mt-2 list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Form Validation Error Container -->
                    <div id="assessmentValidationError" class="hidden mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative" role="alert">
                        <strong class="font-bold">Please fix the following errors:</strong>
                        <ul id="assessmentErrorList" class="mt-2 list-disc list-inside"></ul>
                    </div>

                    <!-- Topic and Date Row -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <input type="text" name="topic" placeholder="Topic (min 3 characters)" maxlength="255"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        </div>
                        <div>
                            <input type="date" name="date" value="{{ date('Y-m-d') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        </div>
                    </div>

                    <!-- Subject and Assessment Type Row -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <select name="subject_id" id="assessmentSubjectId" onchange="loadSyllabusTopics(this.value)"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all appearance-none bg-white">
                                <option value="">Select Subject</option>
                                @foreach($class->subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->subject_code ?? '' }} - {{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <select name="assessment_type"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all appearance-none bg-white">
                                <option value="">Assessment Type</option>
                                <option value="Quiz">Quiz</option>
                                <option value="Test">Test</option>
                                <option value="In Class Test">In Class Test</option>
                                <option value="Monthly Test">Monthly Test</option>
                                <option value="Assignment">Assignment</option>
                                <option value="Exercise">Exercise</option>
                                <option value="Project">Project</option>
                                <option value="Fort Night">Fort Night</option>
                                <option value="Vacation Exam">Vacation Exam</option>
                                <option value="National Exam">National Exam</option>
                            </select>
                        </div>
                    </div>

                    <!-- Syllabus Topic Selection (for data analytics) -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Syllabus Topic <span class="text-gray-400">(optional - for performance tracking)</span>
                        </label>
                        <select name="syllabus_topic_id" id="syllabusTopicSelect"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all appearance-none bg-white">
                            <option value="">Select a syllabus topic (select subject first)</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Linking to a syllabus topic enables performance analytics in Schemes of Work</p>
                    </div>

                    <!-- Exam Field -->
                    <div class="mb-6">
                        <input type="text" name="exam" placeholder="Exam Name (Optional)" maxlength="255"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>

                    <!-- Papers Section -->
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-4">
                            <p class="text-sm font-medium text-gray-700">Define papers & their weights (They must add up to 100%)</p>
                            <p class="text-sm font-semibold" id="totalWeightDisplay">Total: <span id="totalWeight">0</span>%</p>
                        </div>
                        
                        <div id="papersContainer">
                            <!-- Initial Paper Row -->
                            <div class="paper-row mb-3">
                                <div class="flex items-center gap-2">
                                    <input type="text" name="papers[0][name]" placeholder="Paper Name" maxlength="100"
                                        class="w-32 px-2 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <input type="number" name="papers[0][total_marks]" placeholder="Marks" max="1000" oninput="validatePaperMarks(this)"
                                        class="w-40 px-2 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <input type="number" name="papers[0][weight]" placeholder="Weight%" max="100" oninput="updateTotalWeight()"
                                        class="w-40 px-2 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <button type="button" onclick="addPaper()" class="p-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex-shrink-0" title="Add Paper">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 448 512">
                                            <path d="M416 208H272V64c0-17.67-14.33-32-32-32h-32c-17.67 0-32 14.33-32 32v144H32c-17.67 0-32 14.33-32 32v32c0 17.67 14.33 32 32 32h144v144c0 17.67 14.33 32 32 32h32c17.67 0 32-14.33 32-32V304h144c17.67 0 32-14.33 32-32v-32c0-17.67-14.33-32-32-32z"/>
                                        </svg>
                                    </button>
                                    <button type="button" onclick="removePaper(this)" class="p-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors flex-shrink-0" title="Remove Paper">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 448 512">
                                            <path d="M432 32H312l-9.4-18.7A24 24 0 0 0 281.1 0H166.8a23.72 23.72 0 0 0-21.4 13.3L136 32H16A16 16 0 0 0 0 48v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16zM53.2 467a48 48 0 0 0 47.9 45h245.8a48 48 0 0 0 47.9-45L416 128H32z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Due Date -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Due Date</label>
                        <input type="date" name="due_date"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200">
                        <button type="button" onclick="closeAssessmentModal()" 
                            class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit" 
                            class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">
                            Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Grade System Modal -->
    <div id="gradeSystemModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-8 w-full max-w-4xl">
            <div class="bg-white rounded-2xl shadow-2xl">
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-8 py-6 border-b border-gray-200">
                    <h3 class="text-2xl font-bold text-gray-900">Assessment General Comments</h3>
                    <button type="button" onclick="closeGradeSystemModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="px-8 py-6">
                    <!-- Validation Errors -->
                    @if($errors->any())
                        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative" role="alert">
                            <strong class="font-bold">Validation Error!</strong>
                            <ul class="mt-2 list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <!-- Existing Comments Table -->
                    <div class="mb-6 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Subject</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Comment</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Grade</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="commentsTableBody">
                                @foreach($assessmentComments ?? [] as $comment)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $comment->subject->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $comment->comment }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $comment->grade }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <form action="{{ route('teacher.assessment.comment.delete', $comment->id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800 font-semibold">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Success Message Container -->
                    <div id="formSuccessMessage" class="hidden mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative" role="alert">
                        <strong class="font-bold">Success!</strong>
                        <span id="successMessageText"></span>
                    </div>

                    <!-- Add New Comment Form -->
                    <form action="{{ route('teacher.assessment.comment.store') }}" method="POST" class="space-y-4" id="assessmentCommentForm" onsubmit="return submitAssessmentCommentForm(event)">
                        @csrf
                        <input type="hidden" name="class_id" value="{{ $class->id }}">

                        <!-- Form Validation Error Container -->
                        <div id="formValidationError" class="hidden mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative" role="alert">
                            <strong class="font-bold">Please fix the following errors:</strong>
                            <ul id="formErrorList" class="mt-2 list-disc list-inside"></ul>
                        </div>

                        <!-- Subject Selection (Single) -->
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Select Subject</label>
                            <select id="selectedSubject" name="subject_id"
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all appearance-none bg-white">
                                <option value="">Choose a subject</option>
                                @foreach($class->subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Select the subject for which you want to add comments and grades</p>
                        </div>

                        <!-- Dynamic Grade Entries Container -->
                        <div class="mb-6">
                            <div class="flex items-center justify-between mb-3">
                                <label class="block text-sm font-semibold text-gray-700">Comments & Grades for Selected Subject</label>
                                <button type="button" onclick="addGradeEntry()" class="inline-flex items-center px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition-colors">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 448 512">
                                        <path d="M416 208H272V64c0-17.67-14.33-32-32-32h-32c-17.67 0-32 14.33-32 32v144H32c-17.67 0-32 14.33-32 32v32c0 17.67 14.33 32 32 32h144v144c0 17.67 14.33 32 32 32h32c17.67 0 32-14.33 32-32V304h144c17.67 0 32-14.33 32-32v-32c0-17.67-14.33-32-32-32z"/>
                                    </svg>
                                    Add Entry
                                </button>
                            </div>
                            
                            <div id="gradeEntriesContainer" class="space-y-3">
                                <!-- Initial Comment and Grade Row -->
                                <div class="grade-entry-row bg-gray-50 p-4 rounded-lg border border-gray-200">
                                    <div class="flex items-start gap-3">
                                        <div class="flex-1">
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Comment</label>
                                            <input type="text" name="entries[0][comment]" placeholder="Enter the comment (min 10, max 500 characters)"
                                                maxlength="500"
                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                                        </div>
                                        <div class="w-48">
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Grade</label>
                                            <select name="entries[0][grade]"
                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all appearance-none bg-white">
                                                <option value="">Select Grade</option>
                                                <option value="A">A (75-100%)</option>
                                                <option value="B">B (65-74%)</option>
                                                <option value="C">C (50-64%)</option>
                                                <option value="D">D (40-49%)</option>
                                                <option value="U">U (0-39%)</option>
                                            </select>
                                        </div>
                                        <div class="pt-6">
                                            <button type="button" onclick="removeGradeEntry(this)" class="p-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors" title="Remove">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 448 512">
                                                    <path d="M432 32H312l-9.4-18.7A24 24 0 0 0 281.1 0H166.8a23.72 23.72 0 0 0-21.4 13.3L136 32H16A16 16 0 0 0 0 48v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16zM53.2 467a48 48 0 0 0 47.9 45h245.8a48 48 0 0 0 47.9-45L416 128H32z"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200">
                            <button type="button" onclick="closeGradeSystemModal()" 
                                class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-colors">
                                Cancel
                            </button>
                            <button type="submit" 
                                class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">
                                Submit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let paperCount = 1;
        let gradeEntryCount = 1;

        function openAssessmentModal() {
            document.getElementById('assessmentModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeAssessmentModal() {
            document.getElementById('assessmentModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function openGradeSystemModal() {
            document.getElementById('gradeSystemModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeGradeSystemModal() {
            document.getElementById('gradeSystemModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function addPaper() {
            const container = document.getElementById('papersContainer');
            
            // Check max papers limit
            if (container.children.length >= 20) {
                alert('You cannot add more than 20 papers.');
                return;
            }
            
            const newRow = document.createElement('div');
            newRow.className = 'paper-row mb-3';
            newRow.innerHTML = `
                <div class="flex items-center gap-2">
                    <input type="text" name="papers[${paperCount}][name]" placeholder="Paper Name" maxlength="100"
                        class="w-32 px-2 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <input type="number" name="papers[${paperCount}][total_marks]" placeholder="Marks" max="1000" oninput="validatePaperMarks(this)"
                        class="w-40 px-2 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <input type="number" name="papers[${paperCount}][weight]" placeholder="Weight%" max="100" oninput="updateTotalWeight()"
                        class="w-40 px-2 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <button type="button" onclick="addPaper()" class="p-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex-shrink-0" title="Add Paper">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 448 512">
                            <path d="M416 208H272V64c0-17.67-14.33-32-32-32h-32c-17.67 0-32 14.33-32 32v144H32c-17.67 0-32 14.33-32 32v32c0 17.67 14.33 32 32 32h144v144c0 17.67 14.33 32 32 32h32c17.67 0 32-14.33 32-32V304h144c17.67 0 32-14.33 32-32v-32c0-17.67-14.33-32-32-32z"/>
                        </svg>
                    </button>
                    <button type="button" onclick="removePaper(this)" class="p-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors flex-shrink-0" title="Remove Paper">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 448 512">
                            <path d="M432 32H312l-9.4-18.7A24 24 0 0 0 281.1 0H166.8a23.72 23.72 0 0 0-21.4 13.3L136 32H16A16 16 0 0 0 0 48v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16zM53.2 467a48 48 0 0 0 47.9 45h245.8a48 48 0 0 0 47.9-45L416 128H32z"/>
                        </svg>
                    </button>
                </div>
            `;
            container.appendChild(newRow);
            paperCount++;
        }

        function removePaper(button) {
            const container = document.getElementById('papersContainer');
            if (container.children.length > 1) {
                button.closest('.paper-row').remove();
                updateTotalWeight();
            } else {
                alert('You must have at least one paper.');
            }
        }

        // Validate paper marks input
        function validatePaperMarks(input) {
            if (input.value && parseInt(input.value) < 1) {
                input.value = 1;
            }
            if (input.value && parseInt(input.value) > 1000) {
                input.value = 1000;
            }
        }

        // Update total weight display
        function updateTotalWeight() {
            const container = document.getElementById('papersContainer');
            const weightInputs = container.querySelectorAll('input[name*="[weight]"]');
            let total = 0;
            
            weightInputs.forEach(input => {
                const value = parseInt(input.value) || 0;
                total += value;
            });
            
            const totalSpan = document.getElementById('totalWeight');
            const totalDisplay = document.getElementById('totalWeightDisplay');
            
            totalSpan.textContent = total;
            
            // Color code based on total
            if (total === 100) {
                totalDisplay.classList.remove('text-red-600', 'text-orange-600');
                totalDisplay.classList.add('text-green-600');
            } else if (total > 100) {
                totalDisplay.classList.remove('text-green-600', 'text-orange-600');
                totalDisplay.classList.add('text-red-600');
            } else {
                totalDisplay.classList.remove('text-green-600', 'text-red-600');
                totalDisplay.classList.add('text-orange-600');
            }
        }

        // Comprehensive Assessment form validation
        function validateAssessmentForm() {
            const errors = [];
            
            // Validate topic
            const topic = document.querySelector('input[name="topic"]');
            if (!topic.value.trim()) {
                errors.push('Topic is required.');
            } else if (topic.value.trim().length < 3) {
                errors.push('Topic must be at least 3 characters long.');
            } else if (topic.value.trim().length > 255) {
                errors.push('Topic cannot exceed 255 characters.');
            }
            
            // Validate date
            const date = document.querySelector('input[name="date"]');
            if (!date.value) {
                errors.push('Assessment date is required.');
            }
            
            // Validate subject
            const subject = document.querySelector('select[name="subject_id"]');
            if (!subject.value) {
                errors.push('Subject selection is required.');
            }
            
            // Validate assessment type
            const assessmentType = document.querySelector('select[name="assessment_type"]');
            if (!assessmentType.value) {
                errors.push('Assessment type is required.');
            }
            
            // Validate papers
            const container = document.getElementById('papersContainer');
            const papers = container.querySelectorAll('.paper-row');
            
            if (papers.length === 0) {
                errors.push('At least one paper is required.');
            }
            
            const paperNames = [];
            let totalWeight = 0;
            
            papers.forEach((paper, index) => {
                const name = paper.querySelector('input[name*="[name]"]');
                const totalMarks = paper.querySelector('input[name*="[total_marks]"]');
                const weight = paper.querySelector('input[name*="[weight]"]');
                
                // Validate paper name
                if (!name.value.trim()) {
                    errors.push(`Paper ${index + 1}: Paper name is required.`);
                } else if (name.value.trim().length < 2) {
                    errors.push(`Paper ${index + 1}: Paper name must be at least 2 characters long.`);
                } else if (name.value.trim().length > 100) {
                    errors.push(`Paper ${index + 1}: Paper name cannot exceed 100 characters.`);
                } else {
                    // Check for duplicate names
                    if (paperNames.includes(name.value.trim().toLowerCase())) {
                        errors.push(`Paper ${index + 1}: Paper name "${name.value.trim()}" is already used. Each paper must have a unique name.`);
                    } else {
                        paperNames.push(name.value.trim().toLowerCase());
                    }
                }
                
                // Validate total marks
                if (!totalMarks.value) {
                    errors.push(`Paper ${index + 1}: Total marks is required.`);
                } else {
                    const marks = parseInt(totalMarks.value);
                    if (marks < 1) {
                        errors.push(`Paper ${index + 1}: Total marks must be at least 1.`);
                    } else if (marks > 1000) {
                        errors.push(`Paper ${index + 1}: Total marks cannot exceed 1000.`);
                    }
                }
                
                // Validate weight
                if (!weight.value) {
                    errors.push(`Paper ${index + 1}: Weight percentage is required.`);
                } else {
                    const w = parseInt(weight.value);
                    if (w < 1) {
                        errors.push(`Paper ${index + 1}: Weight must be at least 1%.`);
                    } else if (w > 100) {
                        errors.push(`Paper ${index + 1}: Weight cannot exceed 100%.`);
                    }
                    totalWeight += w;
                }
            });
            
            // Validate total weight equals 100%
            if (papers.length > 0 && totalWeight !== 100) {
                errors.push(`Paper weights must add up to 100%. Current total: ${totalWeight}%`);
            }
            
            // Validate due date
            const dueDate = document.querySelector('input[name="due_date"]');
            if (!dueDate.value) {
                errors.push('Due date is required.');
            } else if (date.value) {
                const dueDateValue = new Date(dueDate.value);
                const assessmentDate = new Date(date.value);
                if (dueDateValue < assessmentDate) {
                    errors.push('Due date must be on or after the assessment date.');
                }
            }
            
            // Display errors or submit
            if (errors.length > 0) {
                displayAssessmentValidationErrors(errors);
                return false;
            }
            
            // Hide error container if validation passes
            document.getElementById('assessmentValidationError').classList.add('hidden');
            return true;
        }

        // Display assessment validation errors
        function displayAssessmentValidationErrors(errors) {
            const errorContainer = document.getElementById('assessmentValidationError');
            const errorList = document.getElementById('assessmentErrorList');
            
            errorList.innerHTML = '';
            errors.forEach(error => {
                const li = document.createElement('li');
                li.textContent = error;
                errorList.appendChild(li);
            });
            
            errorContainer.classList.remove('hidden');
            errorContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function addGradeEntry() {
            const container = document.getElementById('gradeEntriesContainer');
            
            // Check max entries limit
            if (container.children.length >= 50) {
                alert('You cannot add more than 50 entries at once.');
                return;
            }
            
            const newRow = document.createElement('div');
            newRow.className = 'grade-entry-row bg-gray-50 p-4 rounded-lg border border-gray-200';
            newRow.innerHTML = `
                <div class="flex items-start gap-3">
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Comment</label>
                        <input type="text" name="entries[${gradeEntryCount}][comment]" placeholder="Enter the comment (min 10, max 500 characters)"
                            maxlength="500"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>
                    <div class="w-48">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Grade</label>
                        <select name="entries[${gradeEntryCount}][grade]"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all appearance-none bg-white">
                            <option value="">Select Grade</option>
                            <option value="A">A (75-100%)</option>
                            <option value="B">B (65-74%)</option>
                            <option value="C">C (50-64%)</option>
                            <option value="D">D (40-49%)</option>
                            <option value="U">U (0-39%)</option>
                        </select>
                    </div>
                    <div class="pt-6">
                        <button type="button" onclick="removeGradeEntry(this)" class="p-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors" title="Remove">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 448 512">
                                <path d="M432 32H312l-9.4-18.7A24 24 0 0 0 281.1 0H166.8a23.72 23.72 0 0 0-21.4 13.3L136 32H16A16 16 0 0 0 0 48v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16zM53.2 467a48 48 0 0 0 47.9 45h245.8a48 48 0 0 0 47.9-45L416 128H32z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(newRow);
            gradeEntryCount++;
        }

        function removeGradeEntry(button) {
            const container = document.getElementById('gradeEntriesContainer');
            if (container.children.length > 1) {
                button.closest('.grade-entry-row').remove();
            } else {
                alert('You must have at least one grade entry.');
            }
        }

        // Update character count for comment inputs
        function updateCharCount(input) {
            const charCountSpan = input.parentElement.querySelector('.char-count');
            if (charCountSpan) {
                charCountSpan.textContent = input.value.length;
                
                // Visual feedback for character count
                if (input.value.length < 10) {
                    charCountSpan.classList.add('text-red-500');
                    charCountSpan.classList.remove('text-green-500', 'text-gray-500');
                } else if (input.value.length > 450) {
                    charCountSpan.classList.add('text-orange-500');
                    charCountSpan.classList.remove('text-green-500', 'text-gray-500');
                } else {
                    charCountSpan.classList.add('text-green-500');
                    charCountSpan.classList.remove('text-red-500', 'text-orange-500', 'text-gray-500');
                }
            }
        }

        // Comprehensive form validation
        function validateForm() {
            const errors = [];
            const subjectSelect = document.getElementById('selectedSubject');
            const container = document.getElementById('gradeEntriesContainer');
            const entries = container.querySelectorAll('.grade-entry-row');

            // Validate subject selection
            if (!subjectSelect.value) {
                errors.push('Subject selection is required.');
            }

            // Check if at least one entry exists
            if (entries.length === 0) {
                errors.push('At least one assessment comment entry is required.');
            }

            // Validate each entry
            entries.forEach((entry, index) => {
                const comment = entry.querySelector('input[name*="[comment]"]');
                const grade = entry.querySelector('select[name*="[grade]"]');

                // Validate comment
                if (!comment.value.trim()) {
                    errors.push(`Entry ${index + 1}: Comment is required.`);
                } else if (comment.value.trim().length < 10) {
                    errors.push(`Entry ${index + 1}: Comment must be at least 10 characters long.`);
                } else if (comment.value.trim().length > 500) {
                    errors.push(`Entry ${index + 1}: Comment cannot exceed 500 characters.`);
                }

                // Validate grade
                if (!grade.value) {
                    errors.push(`Entry ${index + 1}: Grade selection is required.`);
                }
            });

            // Display errors or submit
            if (errors.length > 0) {
                displayValidationErrors(errors);
                return false;
            }

            // Hide error container if validation passes
            document.getElementById('formValidationError').classList.add('hidden');
            return true;
        }

        // AJAX form submission
        function submitAssessmentCommentForm(event) {
            event.preventDefault();
            
            // Validate form first
            if (!validateForm()) {
                return false;
            }

            const form = document.getElementById('assessmentCommentForm');
            const formData = new FormData(form);
            const submitButton = form.querySelector('button[type="submit"]');
            
            // Disable submit button
            submitButton.disabled = true;
            submitButton.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2 inline" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Submitting...';

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    const successContainer = document.getElementById('formSuccessMessage');
                    const successText = document.getElementById('successMessageText');
                    successText.textContent = data.message;
                    successContainer.classList.remove('hidden');
                    
                    // Add new comments to table
                    const tableBody = document.getElementById('commentsTableBody');
                    data.comments.forEach(comment => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${comment.subject.name}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">${comment.comment}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${comment.grade}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <form action="/teacher/assessment/comment/${comment.id}" method="POST" class="inline">
                                    <input type="hidden" name="_token" value="${document.querySelector('input[name="_token"]').value}">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="text-red-600 hover:text-red-800 font-semibold">Delete</button>
                                </form>
                            </td>
                        `;
                        tableBody.appendChild(row);
                    });
                    
                    // Reset form
                    form.reset();
                    
                    // Reset entries to just one
                    const container = document.getElementById('gradeEntriesContainer');
                    container.innerHTML = `
                        <div class="grade-entry-row bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <div class="flex items-start gap-3">
                                <div class="flex-1">
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Comment</label>
                                    <input type="text" name="entries[0][comment]" placeholder="Enter the comment (min 10, max 500 characters)"
                                        maxlength="500"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                                </div>
                                <div class="w-48">
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Grade</label>
                                    <select name="entries[0][grade]"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all appearance-none bg-white">
                                        <option value="">Select Grade</option>
                                        <option value="A">A (75-100%)</option>
                                        <option value="B">B (65-74%)</option>
                                        <option value="C">C (50-64%)</option>
                                        <option value="D">D (40-49%)</option>
                                        <option value="U">U (0-39%)</option>
                                    </select>
                                </div>
                                <div class="pt-6">
                                    <button type="button" onclick="removeGradeEntry(this)" class="p-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors" title="Remove">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 448 512">
                                            <path d="M432 32H312l-9.4-18.7A24 24 0 0 0 281.1 0H166.8a23.72 23.72 0 0 0-21.4 13.3L136 32H16A16 16 0 0 0 0 48v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16zM53.2 467a48 48 0 0 0 47.9 45h245.8a48 48 0 0 0 47.9-45L416 128H32z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                    gradeEntryCount = 1;
                    
                    // Hide success message after 5 seconds
                    setTimeout(() => {
                        successContainer.classList.add('hidden');
                    }, 5000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while submitting the form. Please try again.');
            })
            .finally(() => {
                // Re-enable submit button
                submitButton.disabled = false;
                submitButton.innerHTML = 'Submit';
            });

            return false;
        }

        // Display validation errors
        function displayValidationErrors(errors) {
            const errorContainer = document.getElementById('formValidationError');
            const errorList = document.getElementById('formErrorList');
            
            errorList.innerHTML = '';
            errors.forEach(error => {
                const li = document.createElement('li');
                li.textContent = error;
                errorList.appendChild(li);
            });
            
            errorContainer.classList.remove('hidden');
            errorContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        // Initialize character counters on page load
        document.addEventListener('DOMContentLoaded', function() {
            const commentInputs = document.querySelectorAll('input[name*="[comment]"]');
            commentInputs.forEach(input => {
                updateCharCount(input);
            });
        });

        // Close modal when clicking outside
        document.getElementById('assessmentModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAssessmentModal();
            }
        });

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAssessmentModal();
            }
        });

        // View assessment details
        function viewAssessment(assessmentId) {
            fetch(`/teacher/assessment/${assessmentId}/view`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAssessmentDetails(data.assessment);
                    } else {
                        alert('Failed to load assessment details');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while loading assessment details');
                });
        }

        function showAssessmentDetails(assessment) {
            let papersHtml = '';
            if (assessment.papers && assessment.papers.length > 0) {
                papersHtml = '<ul class="list-disc list-inside">';
                assessment.papers.forEach(paper => {
                    papersHtml += `<li><strong>${paper.name}</strong>: ${paper.total_marks} marks (${paper.weight}%)</li>`;
                });
                papersHtml += '</ul>';
            } else {
                papersHtml = '<p class="text-gray-500">No papers defined</p>';
            }

            const modalContent = `
                <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" id="viewModal">
                    <div class="relative top-20 mx-auto p-8 w-full max-w-3xl">
                        <div class="bg-white rounded-2xl shadow-2xl">
                            <div class="flex items-center justify-between px-8 py-6 border-b border-gray-200">
                                <h3 class="text-2xl font-bold text-gray-900">Assessment Details</h3>
                                <button type="button" onclick="closeViewModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="px-8 py-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <p class="text-sm font-medium text-gray-600">Subject</p>
                                        <p class="text-lg font-semibold text-gray-900">${assessment.subject.name}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-600">Assessment Type</p>
                                        <p class="text-lg font-semibold text-gray-900">${assessment.assessment_type}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-600">Topic</p>
                                        <p class="text-lg font-semibold text-gray-900">${assessment.topic}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-600">Date</p>
                                        <p class="text-lg font-semibold text-gray-900">${new Date(assessment.date).toLocaleDateString()}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-600">Due Date</p>
                                        <p class="text-lg font-semibold text-gray-900">${assessment.due_date ? new Date(assessment.due_date).toLocaleDateString() : 'N/A'}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-600">Exam</p>
                                        <p class="text-lg font-semibold text-gray-900">${assessment.exam || 'N/A'}</p>
                                    </div>
                                </div>
                                <div class="mt-6">
                                    <p class="text-sm font-medium text-gray-600 mb-3">Papers</p>
                                    ${papersHtml}
                                </div>
                            </div>
                            <div class="px-8 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                                <button type="button" onclick="closeViewModal()" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-colors">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            document.body.insertAdjacentHTML('beforeend', modalContent);
        }

        function closeViewModal() {
            const modal = document.getElementById('viewModal');
            if (modal) {
                modal.remove();
            }
        }

        // Edit assessment
        function editAssessment(assessmentId) {
            window.location.href = `/teacher/assessment/${assessmentId}/edit`;
        }

        // Delete assessment
        function deleteAssessment(assessmentId) {
            showDeleteConfirmation(assessmentId);
        }

        function showDeleteConfirmation(assessmentId) {
            const modalContent = `
                <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" id="deleteModal">
                    <div class="relative top-20 mx-auto p-8 w-full max-w-md">
                        <div class="bg-white rounded-2xl shadow-2xl">
                            <div class="px-8 py-6">
                                <div class="flex items-center justify-center mb-4">
                                    <div class="bg-red-100 rounded-full p-3">
                                        <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                    </div>
                                </div>
                                <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Delete Assessment</h3>
                                <p class="text-gray-600 text-center mb-6">Are you sure you want to delete this assessment? This action cannot be undone.</p>
                                <div class="flex gap-3">
                                    <button type="button" onclick="closeDeleteModal()" class="flex-1 px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-colors">
                                        Cancel
                                    </button>
                                    <button type="button" onclick="confirmDelete(${assessmentId})" class="flex-1 px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-colors">
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalContent);
        }

        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            if (modal) {
                modal.remove();
            }
        }

        function confirmDelete(assessmentId) {
            closeDeleteModal();
            
            // Show loading indicator
            showNotification('Deleting assessment...', 'info');
            
            fetch(`/teacher/assessment/${assessmentId}/delete`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Assessment deleted successfully!', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotification('Failed to delete assessment: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred while deleting the assessment', 'error');
            });
        }
F
        function showNotification(message, type) {
            const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
            const notification = `
                <div id="notification" class="fixed top-4 right-4 ${bgColor} text-white px-6 py-4 rounded-lg shadow-lg z-50 transform transition-all duration-300">
                    <div class="flex items-center">
                        <span>${message}</span>
                    </div>
                </div>
            `;
            
            const existing = document.getElementById('notification');
            if (existing) {
                existing.remove();
            }
            
            document.body.insertAdjacentHTML('beforeend', notification);
            
            setTimeout(() => {
                const notif = document.getElementById('notification');
                if (notif) {
                    notif.remove();
                }
            }, 3000);
        }

        // View marked assessment with student marks
        function viewMarkedAssessment(assessmentId, subjectName, assessmentType, topic) {
            // Show loading modal
            const loadingModal = `
                <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" id="marksModal">
                    <div class="relative top-10 mx-auto p-8 w-full max-w-4xl">
                        <div class="bg-white rounded-2xl shadow-2xl">
                            <div class="flex items-center justify-between px-8 py-6 border-b border-gray-200">
                                <div>
                                    <h3 class="text-2xl font-bold text-gray-900">${subjectName} - ${assessmentType}</h3>
                                    <p class="text-sm text-gray-500 mt-1">${topic}</p>
                                </div>
                                <button type="button" onclick="closeMarksModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="px-8 py-6">
                                <div class="flex items-center justify-center py-8">
                                    <svg class="animate-spin h-8 w-8 text-blue-600" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span class="ml-3 text-gray-600">Loading marks...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', loadingModal);

            // Fetch marks
            fetch('/api/assessment/' + assessmentId + '/marks')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayStudentMarks(data.marks, subjectName, assessmentType, topic);
                    } else {
                        showNotification('Failed to load marks', 'error');
                        closeMarksModal();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred while loading marks', 'error');
                    closeMarksModal();
                });
        }

        function displayStudentMarks(marks, subjectName, assessmentType, topic) {
            closeMarksModal();

            let studentCardsHtml = '';
            
            if (marks.length === 0) {
                studentCardsHtml = `
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <p class="mt-2 text-gray-500">No marks found for this assessment</p>
                    </div>
                `;
            } else {
                marks.forEach(student => {
                    let papersHtml = '';
                    let totalMark = 0;
                    let totalPossible = 0;
                    
                    Object.values(student.papers).forEach(paper => {
                        totalMark += parseFloat(paper.mark) || 0;
                        totalPossible += parseFloat(paper.total_marks) || 0;
                        const percentage = paper.total_marks > 0 ? ((paper.mark / paper.total_marks) * 100).toFixed(1) : 0;
                        papersHtml += `
                            <div class="flex justify-between items-center py-2 border-b border-gray-100 last:border-0">
                                <span class="text-sm text-gray-600">${paper.paper_name}</span>
                                <span class="text-sm font-semibold ${percentage >= 50 ? 'text-green-600' : 'text-red-600'}">${paper.mark}/${paper.total_marks} (${percentage}%)</span>
                            </div>
                        `;
                    });
                    
                    const overallPercentage = totalPossible > 0 ? ((totalMark / totalPossible) * 100).toFixed(1) : 0;
                    const gradeColor = overallPercentage >= 75 ? 'bg-green-100 text-green-800 border-green-200' : 
                                       overallPercentage >= 50 ? 'bg-blue-100 text-blue-800 border-blue-200' : 
                                       overallPercentage >= 40 ? 'bg-yellow-100 text-yellow-800 border-yellow-200' : 
                                       'bg-red-100 text-red-800 border-red-200';
                    
                    studentCardsHtml += `
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-4 py-3">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center mr-3">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                    <h4 class="text-white font-semibold">${student.student_name}</h4>
                                </div>
                            </div>
                            <div class="p-4">
                                ${papersHtml}
                                <div class="mt-3 pt-3 border-t border-gray-200 flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-700">Total</span>
                                    <span class="px-3 py-1 rounded-full text-sm font-bold ${gradeColor}">${totalMark}/${totalPossible} (${overallPercentage}%)</span>
                                </div>
                            </div>
                        </div>
                    `;
                });
            }

            const modalContent = `
                <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" id="marksModal">
                    <div class="relative top-10 mx-auto p-8 w-full max-w-4xl">
                        <div class="bg-white rounded-2xl shadow-2xl">
                            <div class="flex items-center justify-between px-8 py-6 border-b border-gray-200">
                                <div>
                                    <h3 class="text-2xl font-bold text-gray-900">${subjectName} - ${assessmentType}</h3>
                                    <p class="text-sm text-gray-500 mt-1">${topic}</p>
                                    <p class="text-xs text-gray-400 mt-1">${marks.length} student(s) marked</p>
                                </div>
                                <button type="button" onclick="closeMarksModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="px-8 py-6 max-h-[60vh] overflow-y-auto">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    ${studentCardsHtml}
                                </div>
                            </div>
                            <div class="px-8 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                                <button type="button" onclick="closeMarksModal()" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-colors">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            document.body.insertAdjacentHTML('beforeend', modalContent);
        }

        function closeMarksModal() {
            const modal = document.getElementById('marksModal');
            if (modal) {
                modal.remove();
            }
        }

        // Load syllabus topics for the selected subject
        async function loadSyllabusTopics(subjectId) {
            const topicSelect = document.getElementById('syllabusTopicSelect');
            
            if (!subjectId) {
                topicSelect.innerHTML = '<option value="">Select a syllabus topic (select subject first)</option>';
                return;
            }

            topicSelect.innerHTML = '<option value="">Loading topics...</option>';

            try {
                const response = await fetch(`{{ route('teacher.schemes.syllabus-topics') }}?subject_id=${subjectId}`);
                const data = await response.json();
                
                if (data.success && data.topics.length > 0) {
                    let options = '<option value="">Select a syllabus topic (optional)</option>';
                    data.topics.forEach(topic => {
                        const diffBadge = topic.difficulty_level === 'hard' ? '🔴' : (topic.difficulty_level === 'medium' ? '🟡' : '🟢');
                        options += `<option value="${topic.id}">${diffBadge} ${topic.name} (${topic.term || 'N/A'})</option>`;
                    });
                    topicSelect.innerHTML = options;
                } else {
                    topicSelect.innerHTML = '<option value="">No syllabus topics found for this subject</option>';
                }
            } catch (error) {
                console.error('Failed to load syllabus topics:', error);
                topicSelect.innerHTML = '<option value="">Error loading topics</option>';
            }
        }
    </script>
@endsection
