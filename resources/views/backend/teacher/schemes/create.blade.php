@extends('layouts.app')

@section('content')
<div x-data="schemeForm()" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Create Scheme of Work</h1>
                <p class="mt-2 text-sm text-gray-600">Build a data-driven scheme based on historical performance</p>
            </div>
            <a href="{{ route('teacher.schemes.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Schemes
            </a>
        </div>
    </div>

    <form action="{{ route('teacher.schemes.store') }}" method="POST" @submit="validateForm">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Class *</label>
                            <select name="class_id" x-model="classId" @change="loadTopics()" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                <option value="">Select Class</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>{{ $class->class_name }}</option>
                                @endforeach
                            </select>
                            @error('class_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Subject *</label>
                            <select name="subject_id" x-model="subjectId" @change="loadTopics()" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                <option value="">Select Subject</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ $subjectId == $subject->id ? 'selected' : '' }}>{{ $subject->subject_code }} - {{ $subject->name }}</option>
                                @endforeach
                            </select>
                            @error('subject_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Term *</label>
                            <select name="term" x-model="term" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                @foreach($terms as $t)
                                    <option value="{{ $t }}">{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Academic Year *</label>
                            <select name="academic_year" x-model="academicYear" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Scheme Title *</label>
                            <input type="text" name="title" x-model="title" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="e.g., Mathematics Form 2A Term 1 Scheme" required>
                            @error('title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="description" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Brief description of the scheme..."></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                            <input type="date" name="start_date" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                            <input type="date" name="end_date" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Expected Performance (%)</label>
                            <input type="number" name="expected_performance" min="0" max="100" step="0.1" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="e.g., 65">
                        </div>
                    </div>
                </div>

                <!-- Auto-Generate from Assessments -->
                <div class="bg-gradient-to-r from-orange-50 to-amber-50 rounded-xl shadow-sm border border-orange-200 p-6 mb-6">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-gray-900 mb-1">Auto-Generate from Assessment Data</h3>
                            <p class="text-sm text-gray-600 mb-3">Automatically select topics where students performed poorly (below 60%)</p>
                            
                            <!-- Requirement Notice -->
                            <div x-show="!classId || !subjectId" class="mb-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-yellow-600 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-semibold text-yellow-800">Required Fields</p>
                                        <p class="text-xs text-yellow-700 mt-1">
                                            Please select <strong>Class</strong> and <strong>Subject</strong> above before using auto-generate.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <button type="button" @click="autoGenerateScheme()" 
                                    :disabled="!classId || !subjectId"
                                    :class="!classId || !subjectId ? 'bg-gray-400 cursor-not-allowed' : 'bg-orange-600 hover:bg-orange-700'"
                                    class="inline-flex items-center px-4 py-2 text-white text-sm font-semibold rounded-lg shadow transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                <span x-text="!classId || !subjectId ? 'Select Class & Subject First' : 'Auto-Select Weak Topics'"></span>
                            </button>
                            
                            <div x-show="autoGenerateMessage" class="mt-3 p-3 rounded-lg" :class="autoGenerateSuccess ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                <p class="text-sm" x-text="autoGenerateMessage"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Topics Selection -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Syllabus Topics</h2>
                        <span class="text-sm text-gray-500">
                            <span x-text="selectedTopics.length"></span> topics selected
                        </span>
                    </div>

                    <template x-if="!subjectId">
                        <div class="text-center py-8 text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                            <p>Select a subject to load syllabus topics</p>
                        </div>
                    </template>

                    <template x-if="subjectId && loading">
                        <div class="text-center py-8">
                            <svg class="animate-spin h-8 w-8 mx-auto text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="mt-2 text-gray-500">Loading topics...</p>
                        </div>
                    </template>

                    <template x-if="subjectId && !loading && availableTopics.length === 0">
                        <div class="text-center py-8 text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            <p>No syllabus topics found for this subject</p>
                            <p class="text-sm mt-1">Add topics in the admin syllabus management</p>
                        </div>
                    </template>

                    <template x-if="subjectId && !loading && availableTopics.length > 0">
                        <div class="space-y-3">
                            <template x-for="(topic, index) in availableTopics" :key="topic.id">
                                <div class="border rounded-lg p-4 transition-all" :class="isTopicSelected(topic.id) ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'" x-data="{ showHistory: false }">
                                    <div class="flex items-start">
                                        <input type="checkbox" 
                                               :checked="isTopicSelected(topic.id)"
                                               @change="toggleTopic(topic)"
                                               class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <div class="ml-3 flex-1">
                                            <div class="flex items-center justify-between flex-wrap gap-2">
                                                <h4 class="font-medium text-gray-900" x-text="topic.name"></h4>
                                                <div class="flex items-center gap-2">
                                                    <!-- Difficulty Badge -->
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                                          :class="topic.difficulty_level === 'hard' ? 'bg-red-100 text-red-800' : (topic.difficulty_level === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800')">
                                                        <span x-text="topic.difficulty_level"></span>
                                                    </span>
                                                    <!-- Performance Badge -->
                                                    <template x-if="topic.historical_performance && topic.historical_performance.length > 0">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                                              :class="topic.historical_performance[0].mastery_level === 'mastered' ? 'bg-green-100 text-green-800' : (topic.historical_performance[0].mastery_level === 'partial' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')">
                                                            <span x-text="topic.historical_performance[0].average_score + '%'"></span>
                                                            <span class="ml-1 text-gray-500" x-text="'(' + topic.historical_performance[0].term + ')'"></span>
                                                        </span>
                                                    </template>
                                                    <template x-if="!topic.historical_performance || topic.historical_performance.length === 0">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                                            No data
                                                        </span>
                                                    </template>
                                                </div>
                                            </div>
                                            <p class="text-sm text-gray-500 mt-1" x-text="topic.description || 'No description'"></p>
                                            
                                            <!-- Performance Summary Row -->
                                            <div class="mt-2 flex items-center gap-4 text-xs">
                                                <span class="text-gray-500">
                                                    <strong>Suggested:</strong> <span x-text="topic.suggested_periods"></span> periods
                                                </span>
                                                <span class="text-gray-500">
                                                    <strong>Term:</strong> <span x-text="topic.term || 'N/A'"></span>
                                                </span>
                                                <template x-if="topic.historical_performance && topic.historical_performance.length > 0">
                                                    <button type="button" @click="showHistory = !showHistory" class="text-blue-600 hover:text-blue-800 font-medium">
                                                        <span x-text="showHistory ? 'Hide History' : 'View History'"></span>
                                                        <svg class="w-3 h-3 inline ml-1 transition-transform" :class="showHistory ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                        </svg>
                                                    </button>
                                                </template>
                                            </div>

                                            <!-- Expandable Performance History Section -->
                                            <template x-if="showHistory && topic.historical_performance && topic.historical_performance.length > 0">
                                                <div class="mt-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                                    <h5 class="text-xs font-semibold text-gray-700 mb-2 flex items-center">
                                                        <svg class="w-4 h-4 mr-1 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                                        </svg>
                                                        Historical Performance
                                                    </h5>
                                                    <div class="space-y-2">
                                                        <template x-for="(perf, pIndex) in topic.historical_performance" :key="pIndex">
                                                            <div class="flex items-center justify-between text-xs py-1 border-b border-gray-100 last:border-0">
                                                                <div class="flex items-center gap-2">
                                                                    <span class="font-medium text-gray-700" x-text="perf.term + ' ' + (perf.year || '')"></span>
                                                                    <span class="text-gray-400" x-text="perf.class_name || ''"></span>
                                                                </div>
                                                                <div class="flex items-center gap-2">
                                                                    <div class="w-20 bg-gray-200 rounded-full h-2">
                                                                        <div class="h-2 rounded-full" 
                                                                             :class="perf.average_score >= 70 ? 'bg-green-500' : (perf.average_score >= 50 ? 'bg-yellow-500' : 'bg-red-500')"
                                                                             :style="'width: ' + perf.average_score + '%'"></div>
                                                                    </div>
                                                                    <span class="font-semibold w-10 text-right" 
                                                                          :class="perf.average_score >= 70 ? 'text-green-600' : (perf.average_score >= 50 ? 'text-yellow-600' : 'text-red-600')"
                                                                          x-text="perf.average_score + '%'"></span>
                                                                    <span class="px-1.5 py-0.5 rounded text-xs"
                                                                          :class="perf.mastery_level === 'mastered' ? 'bg-green-100 text-green-700' : (perf.mastery_level === 'partial' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700')"
                                                                          x-text="perf.mastery_level"></span>
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </div>
                                                    <!-- Performance Trend -->
                                                    <template x-if="topic.historical_performance.length >= 2">
                                                        <div class="mt-2 pt-2 border-t border-gray-200">
                                                            <div class="flex items-center text-xs">
                                                                <span class="text-gray-600 mr-2">Trend:</span>
                                                                <template x-if="topic.historical_performance[0].average_score > topic.historical_performance[topic.historical_performance.length - 1].average_score">
                                                                    <span class="flex items-center text-green-600">
                                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                                                        </svg>
                                                                        Improving (+<span x-text="(topic.historical_performance[0].average_score - topic.historical_performance[topic.historical_performance.length - 1].average_score).toFixed(1)"></span>%)
                                                                    </span>
                                                                </template>
                                                                <template x-if="topic.historical_performance[0].average_score < topic.historical_performance[topic.historical_performance.length - 1].average_score">
                                                                    <span class="flex items-center text-red-600">
                                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"/>
                                                                        </svg>
                                                                        Declining (<span x-text="(topic.historical_performance[0].average_score - topic.historical_performance[topic.historical_performance.length - 1].average_score).toFixed(1)"></span>%)
                                                                    </span>
                                                                </template>
                                                                <template x-if="topic.historical_performance[0].average_score === topic.historical_performance[topic.historical_performance.length - 1].average_score">
                                                                    <span class="flex items-center text-gray-600">
                                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14"/>
                                                                        </svg>
                                                                        Stable
                                                                    </span>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                            
                                            <!-- Topic Details when selected -->
                                            <template x-if="isTopicSelected(topic.id)">
                                                <div class="mt-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
                                                    <!-- Performance Comparison Indicator -->
                                                    <template x-if="topic.historical_performance && topic.historical_performance.length > 0 && getSelectedTopic(topic.id).expected_performance">
                                                        <div class="mb-3 p-2 rounded-lg" 
                                                             :class="getSelectedTopic(topic.id).expected_performance > topic.historical_performance[0].average_score ? 'bg-green-50 border border-green-200' : (getSelectedTopic(topic.id).expected_performance < topic.historical_performance[0].average_score ? 'bg-red-50 border border-red-200' : 'bg-gray-50 border border-gray-200')">
                                                            <div class="flex items-center justify-between text-xs">
                                                                <span class="text-gray-600">
                                                                    <strong>Last:</strong> <span x-text="topic.historical_performance[0].average_score + '%'"></span>
                                                                    <span class="mx-2">→</span>
                                                                    <strong>Target:</strong> <span x-text="getSelectedTopic(topic.id).expected_performance + '%'"></span>
                                                                </span>
                                                                <span class="font-semibold"
                                                                      :class="getSelectedTopic(topic.id).expected_performance > topic.historical_performance[0].average_score ? 'text-green-600' : (getSelectedTopic(topic.id).expected_performance < topic.historical_performance[0].average_score ? 'text-red-600' : 'text-gray-600')">
                                                                    <template x-if="getSelectedTopic(topic.id).expected_performance > topic.historical_performance[0].average_score">
                                                                        <span>+<span x-text="(getSelectedTopic(topic.id).expected_performance - topic.historical_performance[0].average_score).toFixed(1)"></span>% improvement target</span>
                                                                    </template>
                                                                    <template x-if="getSelectedTopic(topic.id).expected_performance < topic.historical_performance[0].average_score">
                                                                        <span><span x-text="(getSelectedTopic(topic.id).expected_performance - topic.historical_performance[0].average_score).toFixed(1)"></span>% (below last)</span>
                                                                    </template>
                                                                    <template x-if="getSelectedTopic(topic.id).expected_performance == topic.historical_performance[0].average_score">
                                                                        <span>Same as last term</span>
                                                                    </template>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </template>
                                                    
                                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                                        <div>
                                                            <label class="block text-xs font-medium text-gray-600 mb-1">Week #</label>
                                                            <input type="number" 
                                                                   :name="'topics[' + getTopicIndex(topic.id) + '][week_number]'"
                                                                   x-model="getSelectedTopic(topic.id).week_number"
                                                                   min="1" max="20"
                                                                   class="w-full text-sm rounded border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs font-medium text-gray-600 mb-1">Periods *</label>
                                                            <input type="number" 
                                                                   :name="'topics[' + getTopicIndex(topic.id) + '][planned_periods]'"
                                                                   x-model="getSelectedTopic(topic.id).planned_periods"
                                                                   min="1" required
                                                                   class="w-full text-sm rounded border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                            <p class="text-xs text-gray-400 mt-1">Suggested: <span x-text="topic.suggested_periods"></span></p>
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs font-medium text-gray-600 mb-1">Expected % *</label>
                                                            <input type="number" 
                                                                   :name="'topics[' + getTopicIndex(topic.id) + '][expected_performance]'"
                                                                   x-model="getSelectedTopic(topic.id).expected_performance"
                                                                   min="0" max="100" step="0.1"
                                                                   class="w-full text-sm rounded border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                                   :placeholder="topic.historical_performance && topic.historical_performance.length > 0 ? 'Last: ' + topic.historical_performance[0].average_score + '%' : ''">
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs font-medium text-gray-600 mb-1">Teaching Methods</label>
                                                            <input type="text" 
                                                                   :name="'topics[' + getTopicIndex(topic.id) + '][teaching_methods]'"
                                                                   placeholder="e.g., Discussion, Demo"
                                                                   class="w-full text-sm rounded border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                        </div>
                                                        <input type="hidden" :name="'topics[' + getTopicIndex(topic.id) + '][syllabus_topic_id]'" :value="topic.id">
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Data Insights Panel -->
                <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-sm p-6 text-white">
                    <h3 class="font-semibold text-lg mb-3">Data-Driven Insights</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            <span>Historical performance data loaded</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Period suggestions based on past results</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <span>Weak topics highlighted in red</span>
                        </div>
                    </div>
                </div>

                <!-- Summary Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Scheme Summary</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-600">Topics Selected</span>
                            <span class="font-semibold text-gray-900" x-text="selectedTopics.length"></span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-600">Total Periods</span>
                            <span class="font-semibold text-gray-900" x-text="totalPeriods"></span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-sm text-gray-600">Weak Topics</span>
                            <span class="font-semibold text-red-600" x-text="weakTopicsCount"></span>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full inline-flex items-center justify-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="selectedTopics.length === 0">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Create Scheme
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function schemeForm() {
    return {
        classId: '{{ $classId ?? "" }}',
        subjectId: '{{ $subjectId ?? "" }}',
        term: '{{ $terms[0] ?? "Term 1" }}',
        academicYear: '{{ date("Y") }}',
        title: '',
        loading: false,
        availableTopics: @json($syllabusTopics ?? []),
        selectedTopics: [],
        autoGenerateMessage: '',
        autoGenerateSuccess: false,

        init() {
            // Pre-populate historical performance data if available
            @if(isset($historicalPerformance))
                const historicalData = @json($historicalPerformance);
                this.availableTopics = this.availableTopics.map(topic => ({
                    ...topic,
                    suggested_periods: historicalData[topic.id]?.suggested_periods || topic.suggested_periods,
                    historical_performance: historicalData[topic.id]?.history || []
                }));
            @endif
        },

        async loadTopics() {
            if (!this.subjectId) {
                this.availableTopics = [];
                return;
            }

            this.loading = true;
            try {
                const response = await fetch(`{{ route('teacher.schemes.syllabus-topics') }}?subject_id=${this.subjectId}&class_id=${this.classId}`);
                const data = await response.json();
                if (data.success) {
                    this.availableTopics = data.topics;
                }
            } catch (error) {
                console.error('Failed to load topics:', error);
            }
            this.loading = false;
        },

        isTopicSelected(topicId) {
            return this.selectedTopics.some(t => t.syllabus_topic_id === topicId);
        },

        getSelectedTopic(topicId) {
            return this.selectedTopics.find(t => t.syllabus_topic_id === topicId);
        },

        getTopicIndex(topicId) {
            return this.selectedTopics.findIndex(t => t.syllabus_topic_id === topicId);
        },

        toggleTopic(topic) {
            if (this.isTopicSelected(topic.id)) {
                this.selectedTopics = this.selectedTopics.filter(t => t.syllabus_topic_id !== topic.id);
            } else {
                this.selectedTopics.push({
                    syllabus_topic_id: topic.id,
                    week_number: null,
                    planned_periods: topic.suggested_periods || 1,
                    expected_performance: null,
                    teaching_methods: '',
                    resources: ''
                });
            }
        },

        get totalPeriods() {
            return this.selectedTopics.reduce((sum, t) => sum + (parseInt(t.planned_periods) || 0), 0);
        },

        get weakTopicsCount() {
            return this.availableTopics.filter(t => {
                if (this.isTopicSelected(t.id) && t.historical_performance && t.historical_performance.length > 0) {
                    return t.historical_performance[0].mastery_level === 'weak';
                }
                return false;
            }).length;
        },

        async autoGenerateScheme() {
            if (!this.classId || !this.subjectId) {
                this.autoGenerateMessage = 'Please select both class and subject first.';
                this.autoGenerateSuccess = false;
                return;
            }

            this.autoGenerateMessage = '';
            this.loading = true;

            try {
                const response = await fetch(`{{ route('teacher.schemes.auto-generate') }}?class_id=${this.classId}&subject_id=${this.subjectId}&term=${this.term}`);
                const data = await response.json();

                if (data.success) {
                    this.autoGenerateMessage = data.message;
                    this.autoGenerateSuccess = true;

                    // Auto-select weak topics
                    if (data.weak_topics && data.weak_topics.length > 0) {
                        data.weak_topics.forEach(weakTopic => {
                            const topic = this.availableTopics.find(t => t.id === weakTopic.id);
                            if (topic && !this.isTopicSelected(topic.id)) {
                                this.selectedTopics.push({
                                    syllabus_topic_id: topic.id,
                                    week_number: null,
                                    planned_periods: weakTopic.suggested_periods || topic.suggested_periods || 6,
                                    expected_performance: Math.min(weakTopic.average_score + 15, 100), // Target 15% improvement
                                    teaching_methods: '',
                                    resources: ''
                                });
                            }
                        });
                    }
                } else {
                    this.autoGenerateMessage = data.message || 'Failed to generate scheme from assessments.';
                    this.autoGenerateSuccess = false;
                }
            } catch (error) {
                console.error('Auto-generate error:', error);
                this.autoGenerateMessage = 'Error connecting to server. Please try again.';
                this.autoGenerateSuccess = false;
            }

            this.loading = false;
        },

        validateForm(e) {
            if (this.selectedTopics.length === 0) {
                e.preventDefault();
                alert('Please select at least one topic');
                return false;
            }
            return true;
        }
    }
}
</script>
@endsection
