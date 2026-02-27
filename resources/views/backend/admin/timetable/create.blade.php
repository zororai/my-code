@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                </div>
                Generate Timetable
            </h1>
            <p class="text-gray-500 mt-1 ml-13">Configure school hours and generate class timetable</p>
        </div>
        <a href="{{ route('admin.timetable.index') }}" class="mt-4 md:mt-0 inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Timetables
        </a>
    </div>

    @if($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r-lg">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.timetable.store') }}" method="POST">
        @csrf
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - Class Selection -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        Select Classes
                    </h2>
                    
                    <div class="mb-3">
                        <label class="flex items-center p-3 bg-emerald-50 border-2 border-emerald-200 rounded-xl cursor-pointer hover:bg-emerald-100 transition-all">
                            <input type="checkbox" id="select-all-classes" 
                                   class="w-4 h-4 text-emerald-600 focus:ring-emerald-500 rounded">
                            <span class="ml-3 font-semibold text-emerald-700">Select All Classes</span>
                        </label>
                    </div>
                    
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        @foreach($classes as $class)
                            <label class="flex items-center p-4 border-2 rounded-xl cursor-pointer transition-all hover:border-emerald-300 border-gray-200 class-checkbox-label">
                                <input type="checkbox" name="class_ids[]" value="{{ $class->id }}" 
                                       class="w-4 h-4 text-emerald-600 focus:ring-emerald-500 rounded class-checkbox"
                                       {{ is_array(old('class_ids')) && in_array($class->id, old('class_ids')) ? 'checked' : '' }}>
                                <div class="ml-3">
                                    <p class="font-semibold text-gray-800">{{ $class->name }}</p>
                                    <p class="text-sm text-gray-500">Form {{ $class->class_numeric }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Right Column - Time Settings -->
            <div class="lg:col-span-2 space-y-6">
                <!-- School Hours -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        School Hours
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Start Time</label>
                            <input type="time" name="start_time" value="{{ old('start_time', '07:30') }}" 
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">End Time</label>
                            <input type="time" name="end_time" value="{{ old('end_time', '15:30') }}" 
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        </div>
                    </div>
                </div>

                <!-- Break Time -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Break Time
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Break Start</label>
                            <input type="time" name="break_start" value="{{ old('break_start', '10:00') }}" 
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Break End</label>
                            <input type="time" name="break_end" value="{{ old('break_end', '10:30') }}" 
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        </div>
                    </div>
                </div>

                <!-- Lunch Time -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Lunch Time
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Lunch Start</label>
                            <input type="time" name="lunch_start" value="{{ old('lunch_start', '12:30') }}" 
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Lunch End</label>
                            <input type="time" name="lunch_end" value="{{ old('lunch_end', '13:30') }}" 
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        </div>
                    </div>
                </div>

                <!-- Subject Duration -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Subject Duration
                    </h2>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Duration per Subject (minutes)</label>
                        <input type="number" name="subject_duration" value="{{ old('subject_duration', '40') }}" 
                               min="20" max="120" step="5"
                               class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        <p class="text-sm text-gray-500 mt-2">Recommended: 35-45 minutes per subject</p>
                    </div>
                </div>

                <!-- Clubs Section -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Clubs / Activities
                    </h2>
                    
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <input type="checkbox" name="include_clubs" id="include_clubs" value="1" 
                                   class="w-4 h-4 text-pink-600 focus:ring-pink-500 rounded"
                                   {{ old('include_clubs') ? 'checked' : '' }}>
                            <label for="include_clubs" class="ml-3 text-sm font-medium text-gray-700">Include Clubs Period</label>
                        </div>
                        
                        <div id="clubs-options" class="grid grid-cols-1 md:grid-cols-2 gap-4 {{ old('include_clubs') ? '' : 'hidden' }}">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Clubs Days</label>
                                <div class="flex flex-wrap gap-4">
                                    @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $day)
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" name="clubs_days[]" value="{{ $day }}" 
                                                   class="w-4 h-4 text-pink-600 focus:ring-pink-500 rounded"
                                                   {{ (is_array(old('clubs_days')) && in_array($day, old('clubs_days'))) || (!old('clubs_days') && $day == 'Wednesday') ? 'checked' : '' }}>
                                            <span class="ml-2 text-sm text-gray-700">{{ $day }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Clubs Position</label>
                                <select name="clubs_position" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                                    <option value="after_lunch" {{ old('clubs_position', 'after_lunch') == 'after_lunch' ? 'selected' : '' }}>After Lunch</option>
                                    <option value="end_of_day" {{ old('clubs_position') == 'end_of_day' ? 'selected' : '' }}>End of Day</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Clubs Start Time</label>
                                <input type="time" name="clubs_start" value="{{ old('clubs_start', '14:00') }}" 
                                       class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Clubs End Time</label>
                                <input type="time" name="clubs_end" value="{{ old('clubs_end', '15:00') }}" 
                                       class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Number of Periods</label>
                                <select name="clubs_periods" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                                    <option value="1" {{ old('clubs_periods', '2') == '1' ? 'selected' : '' }}>1 Period</option>
                                    <option value="2" {{ old('clubs_periods', '2') == '2' ? 'selected' : '' }}>2 Periods</option>
                                    <option value="3" {{ old('clubs_periods') == '3' ? 'selected' : '' }}>3 Periods</option>
                                    <option value="4" {{ old('clubs_periods') == '4' ? 'selected' : '' }}>4 Periods</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Clubs will span this many consecutive periods</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Practicals Section -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                        </svg>
                        Practicals
                    </h2>
                    <p class="text-sm text-gray-500 mb-4">Select subjects that are practicals (e.g., Building, Agriculture). Practicals will get 6 periods per week (2 periods + 4 periods on alternating days) in the Clubs/Activities section and won't occupy regular timetable slots.</p>
                    
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <input type="checkbox" name="include_practicals" id="include_practicals" value="1" 
                                   class="w-4 h-4 text-purple-600 focus:ring-purple-500 rounded"
                                   {{ old('include_practicals') ? 'checked' : '' }}>
                            <label for="include_practicals" class="ml-3 text-sm font-medium text-gray-700">Include Practicals</label>
                        </div>
                        
                        <div id="practicals-warning" class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg {{ old('include_practicals') ? '' : 'hidden' }}">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3 flex-1">
                                    <p class="text-sm font-medium text-blue-800">
                                        <strong>Multiple Classes - Avoid Teacher Conflicts</strong>
                                    </p>
                                    <p class="text-xs text-blue-700 mt-1">
                                        When generating for multiple classes, enable auto-randomization to assign different practical days to each class automatically.
                                    </p>
                                    <div class="mt-3">
                                        <label class="inline-flex items-center p-2 bg-white border-2 border-blue-300 rounded-lg cursor-pointer hover:bg-blue-100 transition-all">
                                            <input type="checkbox" name="randomize_practical_days" id="randomize_practical_days" value="1" 
                                                   class="w-4 h-4 text-blue-600 focus:ring-blue-500 rounded"
                                                   {{ old('randomize_practical_days', true) ? 'checked' : '' }}>
                                            <span class="ml-2 text-sm font-medium text-blue-800">Auto-randomize days for each class</span>
                                        </label>
                                        <p class="text-xs text-blue-600 mt-1 ml-6">Each class will get different days to prevent teacher conflicts</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div id="practicals-options" class="space-y-3 {{ old('include_practicals') ? '' : 'hidden' }}">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Select Practical Subjects</label>
                                <p class="text-xs text-gray-500 mb-3">These subjects will get 2 periods on one day and 4 periods on another day (total 6 periods/week)</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-64 overflow-y-auto p-3 border border-purple-200 rounded-xl bg-purple-50">
                                    @foreach($subjects as $subject)
                                        <label class="inline-flex items-center p-3 bg-white border border-purple-200 rounded-lg cursor-pointer hover:bg-purple-50 transition-all">
                                            <input type="checkbox" name="practical_subjects[]" value="{{ $subject->id }}" 
                                                   class="w-4 h-4 text-purple-600 focus:ring-purple-500 rounded"
                                                   {{ (is_array(old('practical_subjects')) && in_array($subject->id, old('practical_subjects'))) || $subject->is_practical ? 'checked' : '' }}>
                                            <div class="ml-2">
                                                <span class="text-sm font-medium text-gray-800">{{ $subject->name }}</span>
                                                <span class="text-xs text-gray-500 ml-1">({{ $subject->subject_code }})</span>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Day for 2 Periods</label>
                                    <select name="practicals_day_2periods" class="w-full px-4 py-3 border border-purple-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 bg-white">
                                        @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $day)
                                            <option value="{{ $day }}" {{ old('practicals_day_2periods', 'Monday') == $day ? 'selected' : '' }}>{{ $day }}</option>
                                        @endforeach
                                    </select>
                                    <p class="text-xs text-gray-500 mt-1">This day will have 2 practical periods</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Day for 4 Periods</label>
                                    <select name="practicals_day_4periods" class="w-full px-4 py-3 border border-purple-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 bg-white">
                                        @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $day)
                                            <option value="{{ $day }}" {{ old('practicals_day_4periods', 'Wednesday') == $day ? 'selected' : '' }}>{{ $day }}</option>
                                        @endforeach
                                    </select>
                                    <p class="text-xs text-gray-500 mt-1">This day will have 4 practical periods</p>
                                </div>
                            </div>
                            <div class="bg-purple-100 border border-purple-300 rounded-lg p-3">
                                <p class="text-xs text-purple-800">
                                    <strong>Example:</strong> If you select Monday for 2 periods and Wednesday for 4 periods, practicals will be scheduled on alternating days with different durations.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Special Slots Section -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                        </svg>
                        Special Time Slots
                    </h2>
                    <p class="text-sm text-gray-500 mb-4">Add special periods like assembly, sports, or prep time</p>
                    
                    <div class="space-y-4">
                        <!-- Before Break Slot -->
                        <div class="p-4 border border-amber-200 rounded-xl bg-amber-50">
                            <div class="flex items-center mb-3">
                                <input type="checkbox" name="include_before_break" id="include_before_break" value="1" 
                                       class="w-4 h-4 text-amber-600 focus:ring-amber-500 rounded"
                                       {{ old('include_before_break') ? 'checked' : '' }}>
                                <label for="include_before_break" class="ml-3 text-sm font-semibold text-amber-800">Before Break Slot (e.g., Assembly)</label>
                            </div>
                            <div id="before-break-options" class="grid grid-cols-1 md:grid-cols-4 gap-3 {{ old('include_before_break') ? '' : 'hidden' }}">
                                <input type="text" name="before_break_name" value="{{ old('before_break_name', 'Assembly') }}" 
                                       placeholder="Name (e.g., Assembly)"
                                       class="px-3 py-2 border border-amber-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm">
                                <select name="before_break_days[]" multiple 
                                        class="px-3 py-2 border border-amber-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm">
                                    <option value="Monday" {{ is_array(old('before_break_days')) && in_array('Monday', old('before_break_days')) ? 'selected' : '' }}>Monday</option>
                                    <option value="Tuesday" {{ is_array(old('before_break_days')) && in_array('Tuesday', old('before_break_days')) ? 'selected' : '' }}>Tuesday</option>
                                    <option value="Wednesday" {{ is_array(old('before_break_days')) && in_array('Wednesday', old('before_break_days')) ? 'selected' : '' }}>Wednesday</option>
                                    <option value="Thursday" {{ is_array(old('before_break_days')) && in_array('Thursday', old('before_break_days')) ? 'selected' : '' }}>Thursday</option>
                                    <option value="Friday" {{ is_array(old('before_break_days')) && in_array('Friday', old('before_break_days')) ? 'selected' : '' }}>Friday</option>
                                </select>
                                <select name="before_break_periods" class="px-3 py-2 border border-amber-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm">
                                    <option value="1" {{ old('before_break_periods', '1') == '1' ? 'selected' : '' }}>1 Period</option>
                                    <option value="2" {{ old('before_break_periods') == '2' ? 'selected' : '' }}>2 Periods</option>
                                    <option value="3" {{ old('before_break_periods') == '3' ? 'selected' : '' }}>3 Periods</option>
                                </select>
                                <p class="text-xs text-amber-600">Hold Ctrl/Cmd to select multiple days</p>
                            </div>
                        </div>

                        <!-- After Break Slot -->
                        <div class="p-4 border border-green-200 rounded-xl bg-green-50">
                            <div class="flex items-center mb-3">
                                <input type="checkbox" name="include_after_break" id="include_after_break" value="1" 
                                       class="w-4 h-4 text-green-600 focus:ring-green-500 rounded"
                                       {{ old('include_after_break') ? 'checked' : '' }}>
                                <label for="include_after_break" class="ml-3 text-sm font-semibold text-green-800">After Break Slot (e.g., Sports)</label>
                            </div>
                            <div id="after-break-options" class="grid grid-cols-1 md:grid-cols-4 gap-3 {{ old('include_after_break') ? '' : 'hidden' }}">
                                <input type="text" name="after_break_name" value="{{ old('after_break_name', 'Sports') }}" 
                                       placeholder="Name (e.g., Sports)"
                                       class="px-3 py-2 border border-green-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm">
                                <select name="after_break_days[]" multiple 
                                        class="px-3 py-2 border border-green-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm">
                                    <option value="Monday" {{ is_array(old('after_break_days')) && in_array('Monday', old('after_break_days')) ? 'selected' : '' }}>Monday</option>
                                    <option value="Tuesday" {{ is_array(old('after_break_days')) && in_array('Tuesday', old('after_break_days')) ? 'selected' : '' }}>Tuesday</option>
                                    <option value="Wednesday" {{ is_array(old('after_break_days')) && in_array('Wednesday', old('after_break_days')) ? 'selected' : '' }}>Wednesday</option>
                                    <option value="Thursday" {{ is_array(old('after_break_days')) && in_array('Thursday', old('after_break_days')) ? 'selected' : '' }}>Thursday</option>
                                    <option value="Friday" {{ is_array(old('after_break_days')) && in_array('Friday', old('after_break_days')) ? 'selected' : '' }}>Friday</option>
                                </select>
                                <select name="after_break_periods" class="px-3 py-2 border border-green-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm">
                                    <option value="1" {{ old('after_break_periods', '1') == '1' ? 'selected' : '' }}>1 Period</option>
                                    <option value="2" {{ old('after_break_periods') == '2' ? 'selected' : '' }}>2 Periods</option>
                                    <option value="3" {{ old('after_break_periods') == '3' ? 'selected' : '' }}>3 Periods</option>
                                </select>
                                <p class="text-xs text-green-600">Hold Ctrl/Cmd to select multiple days</p>
                            </div>
                        </div>

                        <!-- After Lunch Slot -->
                        <div class="p-4 border border-blue-200 rounded-xl bg-blue-50">
                            <div class="flex items-center mb-3">
                                <input type="checkbox" name="include_after_lunch" id="include_after_lunch" value="1" 
                                       class="w-4 h-4 text-blue-600 focus:ring-blue-500 rounded"
                                       {{ old('include_after_lunch') ? 'checked' : '' }}>
                                <label for="include_after_lunch" class="ml-3 text-sm font-semibold text-blue-800">After Lunch Slot (e.g., Reading)</label>
                            </div>
                            <div id="after-lunch-options" class="grid grid-cols-1 md:grid-cols-4 gap-3 {{ old('include_after_lunch') ? '' : 'hidden' }}">
                                <input type="text" name="after_lunch_name" value="{{ old('after_lunch_name', 'Reading') }}" 
                                       placeholder="Name (e.g., Reading)"
                                       class="px-3 py-2 border border-blue-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                                <select name="after_lunch_days[]" multiple 
                                        class="px-3 py-2 border border-blue-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                                    <option value="Monday" {{ is_array(old('after_lunch_days')) && in_array('Monday', old('after_lunch_days')) ? 'selected' : '' }}>Monday</option>
                                    <option value="Tuesday" {{ is_array(old('after_lunch_days')) && in_array('Tuesday', old('after_lunch_days')) ? 'selected' : '' }}>Tuesday</option>
                                    <option value="Wednesday" {{ is_array(old('after_lunch_days')) && in_array('Wednesday', old('after_lunch_days')) ? 'selected' : '' }}>Wednesday</option>
                                    <option value="Thursday" {{ is_array(old('after_lunch_days')) && in_array('Thursday', old('after_lunch_days')) ? 'selected' : '' }}>Thursday</option>
                                    <option value="Friday" {{ is_array(old('after_lunch_days')) && in_array('Friday', old('after_lunch_days')) ? 'selected' : '' }}>Friday</option>
                                </select>
                                <select name="after_lunch_periods" class="px-3 py-2 border border-blue-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                                    <option value="1" {{ old('after_lunch_periods', '1') == '1' ? 'selected' : '' }}>1 Period</option>
                                    <option value="2" {{ old('after_lunch_periods') == '2' ? 'selected' : '' }}>2 Periods</option>
                                    <option value="3" {{ old('after_lunch_periods') == '3' ? 'selected' : '' }}>3 Periods</option>
                                </select>
                                <p class="text-xs text-blue-600">Hold Ctrl/Cmd to select multiple days</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Academic Period -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Academic Period
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Academic Year</label>
                            <input type="text" name="academic_year" 
                                   value="{{ old('academic_year', $currentTerm ? $currentTerm->year : date('Y')) }}" 
                                   placeholder="e.g., 2024"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Term</label>
                            @php
                                $defaultTerm = 1;
                                if ($currentTerm && $currentTerm->result_period) {
                                    // Extract term number from result_period (e.g., "Term 1" -> 1)
                                    preg_match('/\d+/', $currentTerm->result_period, $matches);
                                    $defaultTerm = $matches[0] ?? 1;
                                }
                            @endphp
                            <select name="term" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                                <option value="1" {{ old('term', $defaultTerm) == '1' ? 'selected' : '' }}>Term 1</option>
                                <option value="2" {{ old('term', $defaultTerm) == '2' ? 'selected' : '' }}>Term 2</option>
                                <option value="3" {{ old('term', $defaultTerm) == '3' ? 'selected' : '' }}>Term 3</option>
                            </select>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 mt-2">
                        @if($currentTerm)
                            <span class="text-emerald-600 font-medium">Current Term: {{ $currentTerm->result_period }} {{ $currentTerm->year }}</span> - 
                        @endif
                        This allows you to create separate timetables for different terms
                    </p>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end gap-4 mt-6">
                    <a href="{{ route('admin.timetable.index') }}" 
                       class="px-6 py-3 border border-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center gap-2 px-8 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all"
                            style="color: black">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Generate Timetable
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    // Select All functionality
    const selectAllCheckbox = document.getElementById('select-all-classes');
    const classCheckboxes = document.querySelectorAll('.class-checkbox');
    const classLabels = document.querySelectorAll('.class-checkbox-label');
    
    selectAllCheckbox.addEventListener('change', function() {
        classCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateLabelStyles();
    });
    
    // Update Select All state when individual checkboxes change
    classCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            selectAllCheckbox.checked = [...classCheckboxes].every(cb => cb.checked);
            updateLabelStyles();
        });
    });
    
    // Update label styles based on checkbox state
    function updateLabelStyles() {
        classLabels.forEach(label => {
            const checkbox = label.querySelector('.class-checkbox');
            if (checkbox.checked) {
                label.classList.remove('border-gray-200');
                label.classList.add('border-emerald-500', 'bg-emerald-50');
            } else {
                label.classList.remove('border-emerald-500', 'bg-emerald-50');
                label.classList.add('border-gray-200');
            }
        });
    }
    
    // Initialize styles on page load
    updateLabelStyles();
    
    // Toggle Clubs options
    const includeClubs = document.getElementById('include_clubs');
    const clubsOptions = document.getElementById('clubs-options');
    includeClubs.addEventListener('change', function() {
        clubsOptions.classList.toggle('hidden', !this.checked);
    });
    
    // Toggle Before Break options
    const includeBeforeBreak = document.getElementById('include_before_break');
    const beforeBreakOptions = document.getElementById('before-break-options');
    includeBeforeBreak.addEventListener('change', function() {
        beforeBreakOptions.classList.toggle('hidden', !this.checked);
    });
    
    // Toggle After Break options
    const includeAfterBreak = document.getElementById('include_after_break');
    const afterBreakOptions = document.getElementById('after-break-options');
    includeAfterBreak.addEventListener('change', function() {
        afterBreakOptions.classList.toggle('hidden', !this.checked);
    });
    
    // Toggle After Lunch options
    const includeAfterLunch = document.getElementById('include_after_lunch');
    const afterLunchOptions = document.getElementById('after-lunch-options');
    includeAfterLunch.addEventListener('change', function() {
        afterLunchOptions.classList.toggle('hidden', !this.checked);
    });
    
    // Toggle Practicals options
    const includePracticals = document.getElementById('include_practicals');
    const practicalsOptions = document.getElementById('practicals-options');
    const practicalsWarning = document.getElementById('practicals-warning');
    includePracticals.addEventListener('change', function() {
        practicalsOptions.classList.toggle('hidden', !this.checked);
        if (practicalsWarning) {
            practicalsWarning.classList.toggle('hidden', !this.checked);
        }
    });
</script>
@endsection
