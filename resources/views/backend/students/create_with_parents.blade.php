@extends('layouts.app')

@section('content')
    <div class="roles">
        <div class="flex items-center justify-between mb-3">
            <div>
                <h2 class="text-gray-700 uppercase font-bold">Add New Student with Parents</h2>
            </div>
            <div class="flex flex-wrap items-center">
                <a href="{{ auth()->user()->hasRole('Admin') ? route('student.index') : route('teacher.class-students') }}" class="bg-gray-700 text-white text-sm uppercase py-2 px-4 flex items-center rounded">
                    <svg class="w-3 h-3 fill-current" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="long-arrow-alt-left" class="svg-inline--fa fa-long-arrow-alt-left fa-w-14" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M134.059 296H436c6.627 0 12-5.373 12-12v-56c0-6.627-5.373-12-12-12H134.059v-46.059c0-21.382-25.851-32.09-40.971-16.971L7.029 239.029c-9.373 9.373-9.373 24.569 0 33.941l86.059 86.059c15.119 15.119 40.971 4.411 40.971-16.971V296z"></path></svg>
                    <span class="ml-2 text-xs font-medium">Back</span>
                </a>
            </div>
        </div>

        <!-- Stepper -->
        <div class="bg-white rounded-lg shadow-lg p-4 mb-4">
            <div class="flex items-center justify-between mb-4">
                <div class="flex-1 flex items-center step-indicator" data-step="1">
                    <div class="step-circle active flex items-center justify-center w-8 h-8 rounded-full bg-blue-500 text-white text-sm font-bold">
                        1
                    </div>
                    <div class="ml-2">
                        <div class="step-title text-sm font-medium text-gray-700">Student Information</div>
                    </div>
                </div>
                <div class="flex-shrink-0 w-12 h-1 bg-gray-300 step-line"></div>
                <div class="flex-1 flex items-center step-indicator" data-step="2">
                    <div class="step-circle flex items-center justify-center w-8 h-8 rounded-full bg-gray-300 text-white text-sm font-bold">
                        2
                    </div>
                    <div class="ml-2">
                        <div class="step-title text-sm font-medium text-gray-500">Parent Information</div>
                    </div>
                </div>
                <div class="flex-shrink-0 w-12 h-1 bg-gray-300 step-line"></div>
                <div class="flex-1 flex items-center step-indicator" data-step="3">
                    <div class="step-circle flex items-center justify-center w-8 h-8 rounded-full bg-gray-300 text-white text-sm font-bold">
                        3
                    </div>
                    <div class="ml-2">
                        <div class="step-title text-sm font-medium text-gray-500">Review & Submit</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Error/Success Messages -->
        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 mb-3 rounded text-sm" role="alert">
                <strong class="font-bold">Error:</strong> {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 mb-3 rounded text-sm" role="alert">
                <strong class="font-bold">Success:</strong> {{ session('success') }}
            </div>
        @endif

        @if(session('warning'))
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-3 mb-3 rounded text-sm" role="alert">
                <strong class="font-bold">Warning:</strong> {!! session('warning') !!}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 mb-3 rounded text-sm" role="alert">
                <strong class="font-bold">Errors:</strong>
                <ul class="mt-1 ml-4 list-disc">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Bulk Import Section -->
        <div class="mb-4 bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="px-4 py-3 bg-gradient-to-r from-purple-500 to-indigo-600">
                <h3 class="text-base font-bold text-white flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    Bulk Import Students with Parents
                </h3>
            </div>
            
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Instructions -->
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 mb-2">How to Import</h4>
                        <ol class="text-xs text-gray-600 space-y-1 list-decimal list-inside">
                            <li>Download the CSV template</li>
                            <li>Fill in student and parent details</li>
                            <li>Save as CSV format</li>
                            <li>Upload the file</li>
                        </ol>
                        
                        <div class="mt-3 bg-blue-50 border border-blue-200 rounded p-2">
                            <p class="text-xs text-blue-800 font-medium mb-1">Required Columns:</p>
                            <ul class="text-xs text-blue-700 space-y-0.5">
                                <li><strong>student_name, student_email, class_name</strong></li>
                                <li><strong>parent_1_name, parent_1_phone</strong> (optional)</li>
                            </ul>
                        </div>
                        
                        <a href="{{ route('shared.student.template') }}" class="mt-3 inline-flex items-center px-3 py-2 bg-white border-2 border-purple-600 text-purple-600 hover:bg-purple-50 text-xs font-medium rounded transition-colors">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Download Template
                        </a>
                    </div>
                    
                    <!-- Upload Form -->
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 mb-2">Upload File</h4>
                        <form action="{{ route('shared.student.import-with-parents') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <div class="border-2 border-dashed border-gray-300 rounded p-4 text-center hover:border-purple-500 transition-colors">
                                <input type="file" name="import_file" id="student_import_file" accept=".csv,.xlsx,.xls" required class="hidden" onchange="updateStudentFileName(this)">
                                <label for="student_import_file" class="cursor-pointer">
                                    <svg class="w-10 h-10 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="text-xs text-gray-600 mb-1">Click to select file</p>
                                    <p class="text-xs text-gray-500">CSV, XLSX, XLS (Max 2MB)</p>
                                </label>
                                <p id="studentFileName" class="mt-2 text-xs text-gray-700 font-medium hidden"></p>
                            </div>
                            
                            <button type="submit" class="mt-3 w-full px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white text-sm font-semibold rounded shadow hover:shadow-lg transition-all flex items-center justify-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                                Import Students
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-lg shadow-lg">
            <form action="{{ url('/student-with-parents') }}" method="POST" id="studentParentForm" enctype="multipart/form-data">
                @csrf

                <!-- Step 1: Student Information -->
                <div class="step-content px-4 py-4" id="step-1">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                        </svg>
                        Student Information
                    </h3>

                    <div class="max-w-2xl">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                            <input name="student_name" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" type="text" value="{{ old('student_name') }}" placeholder="Enter student's full name">
                            @error('student_name')
                                <p class="text-red-500 text-xs mt-1 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        @php
                            $emailDomain = \App\WebsiteSetting::get('student_email_domain', 'roshs.co.zw');
                        @endphp
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email Address (Auto-generated) *</label>
                            <input name="student_email" id="student_email" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" type="email" value="{{ strtolower($nextRollNumber) }}@{{ $emailDomain }}" readonly placeholder="Auto-generated from roll number">
                            <input type="hidden" id="email_domain" value="{{ $emailDomain }}">
                            <p class="text-xs text-gray-500 mt-1">Email: {{ $nextRollNumber }}@{{ $emailDomain }} | Default Password: 12345678 (must be changed on first login)</p>
                            @error('student_email')
                                <p class="text-red-500 text-xs mt-1 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="grid md:grid-cols-2 gap-3 mb-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Roll Number</label>
                                <div class="w-full px-5 py-4 bg-gradient-to-r from-green-50 to-blue-50 border-2 border-green-400 rounded-lg shadow-md">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center text-gray-600">
                                            <svg class="w-5 h-5 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="text-sm font-medium">Auto-generated:</span>
                                        </div>
                                        <span id="roll-number-display" class="text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-green-600 to-blue-600">{{ $nextRollNumber }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between mt-2">
                                    <p class="text-xs text-gray-500">This roll number will be assigned to the new student</p>
                                    <button type="button" id="generate-new-roll" onclick="generateNewRollNumber()" class="inline-flex items-center px-3 py-1 bg-blue-500 hover:bg-blue-600 text-white text-xs font-medium rounded-lg shadow-sm transition duration-200">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                        Generate New
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number (Optional)</label>
                                <input name="student_phone" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 hover:border-gray-400" type="text" value="{{ old('student_phone') }}" placeholder="(123) 456-7890">
                                @error('student_phone')
                                    <p class="text-red-500 text-xs mt-1 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-3">Gender *</label>
                            <div class="flex gap-3">
                                <label class="flex items-center cursor-pointer group">
                                    <input name="student_gender" class="w-5 h-5 text-blue-500 border-gray-300 focus:ring-2 focus:ring-blue-500" type="radio" value="male">
                                    <span class="ml-3 text-gray-700 group-hover:text-blue-600 font-medium">Male</span>
                                </label>
                                <label class="flex items-center cursor-pointer group">
                                    <input name="student_gender" class="w-5 h-5 text-blue-500 border-gray-300 focus:ring-2 focus:ring-blue-500" type="radio" value="female">
                                    <span class="ml-3 text-gray-700 group-hover:text-blue-600 font-medium">Female</span>
                                </label>
                            </div>
                            @error('student_gender')
                                <p class="text-red-500 text-xs mt-1 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="grid md:grid-cols-2 gap-3 mb-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth *</label>
                                <input name="dateofbirth" id="datepicker-student" autocomplete="off" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 hover:border-gray-400" type="text" value="{{ old('dateofbirth') }}" placeholder="YYYY-MM-DD">
                                @error('dateofbirth')
                                    <p class="text-red-500 text-xs mt-1 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Class *</label>
                                <select name="class_id" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 hover:border-gray-400">
                                    <option value="">Select a class</option>
                                    @foreach ($classes as $class)
                                        <option value="{{ $class->id }}">{{ $class->class_name }}</option>
                                    @endforeach
                                </select>
                                @error('class_id')
                                    <p class="text-red-500 text-xs mt-1 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-3">Student Type *</label>
                            <div class="grid grid-cols-2 gap-4" id="student-type-selector">
                                <div class="student-type-card relative flex items-center p-4 border-2 rounded-lg cursor-pointer hover:border-blue-400 transition-colors border-blue-500 bg-blue-50" data-type="day" onclick="selectStudentType('day')">
                                    <input name="student_type" id="student_type_day" type="radio" value="day" class="hidden" {{ old('student_type', 'day') == 'day' ? 'checked' : '' }}>
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <span class="text-gray-900 font-semibold">Day Scholar</span>
                                            <p class="text-xs text-gray-500">Student goes home daily</p>
                                        </div>
                                    </div>
                                    <div class="check-indicator absolute top-2 right-2 w-5 h-5 border-2 rounded-full bg-blue-500 border-blue-500 flex items-center justify-center">
                                        <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="student-type-card relative flex items-center p-4 border-2 rounded-lg cursor-pointer hover:border-green-400 transition-colors border-gray-300" data-type="boarding" onclick="selectStudentType('boarding')">
                                    <input name="student_type" id="student_type_boarding" type="radio" value="boarding" class="hidden" {{ old('student_type') == 'boarding' ? 'checked' : '' }}>
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <span class="text-gray-900 font-semibold">Boarding</span>
                                            <p class="text-xs text-gray-500">Student stays at school</p>
                                        </div>
                                    </div>
                                    <div class="check-indicator absolute top-2 right-2 w-5 h-5 border-2 rounded-full border-gray-300 flex items-center justify-center hidden">
                                        <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            @error('student_type')
                                <p class="text-red-500 text-xs mt-1 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-3">Curriculum Type *</label>
                            <div class="grid grid-cols-2 gap-4" id="curriculum-type-selector">
                                <div class="curriculum-type-card relative flex items-center p-4 border-2 rounded-lg cursor-pointer hover:border-indigo-400 transition-colors border-indigo-500 bg-indigo-50" data-type="zimsec" onclick="selectCurriculumType('zimsec')">
                                    <input name="curriculum_type" id="curriculum_type_zimsec" type="radio" value="zimsec" class="hidden" {{ old('curriculum_type', 'zimsec') == 'zimsec' ? 'checked' : '' }}>
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center mr-3">
                                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <span class="text-gray-900 font-semibold">ZIMSEC</span>
                                            <p class="text-xs text-gray-500">Zimbabwe curriculum</p>
                                        </div>
                                    </div>
                                    <div class="curriculum-check-indicator absolute top-2 right-2 w-5 h-5 border-2 rounded-full bg-indigo-500 border-indigo-500 flex items-center justify-center">
                                        <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="curriculum-type-card relative flex items-center p-4 border-2 rounded-lg cursor-pointer hover:border-purple-400 transition-colors border-gray-300" data-type="cambridge" onclick="selectCurriculumType('cambridge')">
                                    <input name="curriculum_type" id="curriculum_type_cambridge" type="radio" value="cambridge" class="hidden" {{ old('curriculum_type') == 'cambridge' ? 'checked' : '' }}>
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <span class="text-gray-900 font-semibold">Cambridge</span>
                                            <p class="text-xs text-gray-500">International curriculum</p>
                                        </div>
                                    </div>
                                    <div class="curriculum-check-indicator absolute top-2 right-2 w-5 h-5 border-2 rounded-full border-gray-300 flex items-center justify-center hidden">
                                        <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            @error('curriculum_type')
                                <p class="text-red-500 text-xs mt-1 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Scholarship Percentage (Optional)</label>
                            <div class="flex items-center">
                                <input name="scholarship_percentage" type="number" min="0" max="100" step="0.01" class="w-32 px-3 py-2 bg-white border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 hover:border-gray-400" value="{{ old('scholarship_percentage', 0) }}" placeholder="0">
                                <span class="ml-2 text-gray-600 font-medium">%</span>
                                <span class="ml-4 text-xs text-gray-500">Enter 0-100. E.g., 50 = 50% discount on fees</span>
                            </div>
                            @error('scholarship_percentage')
                                <p class="text-red-500 text-xs mt-1 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-3">Student Status</label>
                            <div class="grid grid-cols-2 gap-4" id="new-student-selector">
                                <button type="button" class="new-student-card relative flex items-center p-4 border-2 rounded-lg focus:outline-none transition-colors {{ old('is_new_student', '1') == '1' ? 'border-emerald-500 bg-emerald-50' : 'border-gray-300' }}" data-status="new" onclick="selectNewStudentStatus(true)" aria-pressed="{{ old('is_new_student', '1') == '1' ? 'true' : 'false' }}">
                                    <input name="is_new_student" id="is_new_student_yes" type="radio" value="1" class="hidden" {{ old('is_new_student', '1') == '1' ? 'checked' : '' }}>
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center mr-3">
                                            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <span class="text-gray-900 font-semibold">New Student</span>
                                            <p class="text-xs text-gray-500">First time enrolling</p>
                                        </div>
                                    </div>
                                    <div class="new-check-indicator absolute top-2 right-2 w-5 h-5 border-2 rounded-full {{ old('is_new_student', '1') == '1' ? 'bg-emerald-500 border-emerald-500 flex items-center justify-center' : 'border-gray-300 hidden' }}">
                                        <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </button>

                                <button type="button" class="new-student-card relative flex items-center p-4 border-2 rounded-lg focus:outline-none transition-colors {{ old('is_new_student') == '0' ? 'border-amber-500 bg-amber-50' : 'border-gray-300' }}" data-status="existing" onclick="selectNewStudentStatus(false)" aria-pressed="{{ old('is_new_student') == '0' ? 'true' : 'false' }}">
                                    <input name="is_new_student" id="is_new_student_no" type="radio" value="0" class="hidden" {{ old('is_new_student') == '0' ? 'checked' : '' }}>
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center mr-3">
                                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <span class="text-gray-900 font-semibold">Existing Student</span>
                                            <p class="text-xs text-gray-500">Returning / continuing</p>
                                        </div>
                                    </div>
                                    <div class="new-check-indicator absolute top-2 right-2 w-5 h-5 border-2 rounded-full {{ old('is_new_student') == '0' ? 'bg-amber-500 border-amber-500 flex items-center justify-center' : 'border-gray-300 hidden' }}">
                                        <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">New students may have additional registration fees applied.</p>
                        </div>

                        <div class="grid md:grid-cols-2 gap-3 mb-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Chair Number (Optional)</label>
                                <input name="chair" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 hover:border-gray-400" type="text" value="{{ old('chair') }}" placeholder="Enter chair number">
                                @error('chair')
                                    <p class="text-red-500 text-xs mt-1 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Desk Number (Optional)</label>
                                <input name="desk" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 hover:border-gray-400" type="text" value="{{ old('desk') }}" placeholder="Enter desk number">
                                @error('desk')
                                    <p class="text-red-500 text-xs mt-1 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                    </div>

                    <div class="flex justify-end mt-8">
                        <button type="button" class="next-btn bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-6 rounded">
                            Next Step →
                        </button>
                    </div>
                </div>

                <!-- Step 2: Parent Information -->
                <div class="step-content hidden px-6 py-8" id="step-2">
                    <h3 class="text-xl font-bold text-gray-800 mb-8 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                        </svg>
                        Parent Information (Optional)
                    </h3>

                    <div class="max-w-3xl">
                        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-3">
                            <div class="flex">
                                <svg class="w-6 h-6 text-blue-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <p class="font-medium text-blue-800">Parent Registration via SMS (Optional)</p>
                                    <p class="text-sm text-blue-700 mt-1">You can skip this step or add parent details. Each parent will receive an SMS with a secure link to complete their registration (set password, email, and addresses).</p>
                                </div>
                            </div>
                        </div>

                        <div id="parents-container">
                            <!-- Parent 1 -->
                            <div class="parent-block bg-gradient-to-br from-blue-50 to-white border-2 border-blue-200 rounded-xl p-6 mb-3 shadow-sm hover:shadow-md transition duration-200" data-parent-index="0">
                                <div class="flex justify-between items-center mb-3">
                                    <h4 class="text-lg font-bold text-blue-700 flex items-center">
                                        <span class="bg-blue-500 text-white w-8 h-8 rounded-full flex items-center justify-center mr-2 text-sm">1</span>
                                        Parent #1
                                    </h4>
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                                    <input name="parents[0][name]" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 hover:border-gray-400" type="text" placeholder="Enter parent's full name">
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                                    <div class="flex items-center">
                                        <span class="inline-flex items-center px-3 py-3 bg-gray-100 border border-r-0 border-gray-300 rounded-l-lg text-gray-600">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                                            </svg>
                                        </span>
                                        <input name="parents[0][phone]" id="parent-phone-0" class="flex-1 px-3 py-2 bg-white border border-gray-300 rounded-r-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 hover:border-gray-400" type="text" placeholder="+27123456789">
                                        <button type="button" onclick="testSms(0)" class="ml-2 px-3 py-2 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium rounded-lg shadow-sm transition duration-200" title="Test SMS">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Include country code (e.g., +27 for South Africa) - Click yellow button to test SMS</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-center mb-3">
                            <button type="button" id="add-parent-btn" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-6 rounded flex items-center shadow-md hover:shadow-lg transition duration-200">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                                </svg>
                                Add Another Parent
                            </button>
                        </div>
                    </div>

                    <div class="flex justify-between mt-8">
                        <button type="button" class="prev-btn bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded">
                            ← Previous
                        </button>
                        <button type="button" class="next-btn bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-6 rounded">
                            Next Step →
                        </button>
                    </div>
                </div>

                <!-- Step 3: Review & Submit -->
                <div class="step-content hidden px-6 py-8" id="step-3">
                    <h3 class="text-lg font-bold text-gray-700 mb-3">Review & Submit</h3>

                    <div class="max-w-3xl">
                        <div class="border-2 border-gray-300 rounded-lg p-6 mb-3">
                            <h4 class="text-md font-bold text-gray-600 mb-4">Student Information</h4>
                            <div id="review-student" class="grid grid-cols-2 gap-4 text-sm"></div>
                        </div>

                        <div class="border-2 border-gray-300 rounded-lg p-6 mb-3">
                            <h4 class="text-md font-bold text-gray-600 mb-4">Parents Information</h4>
                            <div id="review-parents"></div>
                        </div>
                    </div>

                    <div class="flex justify-between mt-8">
                        <button type="button" class="prev-btn bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded">
                            ← Previous
                        </button>
                        <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-6 rounded">
                            Submit All
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(function() {
        let currentStep = 1;
        let parentCount = 1;

        // Datepicker
        $("#datepicker-student").datepicker({ dateFormat: 'yy-mm-dd' });

        // Step navigation
        function showStep(step) {
            $('.step-content').addClass('hidden');
            $(`#step-${step}`).removeClass('hidden');

            $('.step-indicator').each(function() {
                const stepNum = $(this).data('step');
                if (stepNum < step) {
                    $(this).find('.step-circle').removeClass('bg-gray-300 bg-blue-500').addClass('bg-green-500');
                    $(this).find('.step-title').removeClass('text-gray-500').addClass('text-green-600');
                } else if (stepNum === step) {
                    $(this).find('.step-circle').removeClass('bg-gray-300 bg-green-500').addClass('bg-blue-500');
                    $(this).find('.step-title').removeClass('text-gray-500 text-green-600').addClass('text-gray-700');
                } else {
                    $(this).find('.step-circle').removeClass('bg-blue-500 bg-green-500').addClass('bg-gray-300');
                    $(this).find('.step-title').removeClass('text-gray-700 text-green-600').addClass('text-gray-500');
                }
            });

            if (step === 3) {
                updateReview();
            }
        }

        $('.next-btn').click(function() {
            // Clear all previous validation errors
            $('.validation-error').remove();

            if (currentStep === 1) {
                // Validate Step 1: Student Information
                let isValid = true;

                // Check student name
                if (!$('input[name="student_name"]').val().trim()) {
                    isValid = false;
                    showError('input[name="student_name"]', 'Student Full Name is required');
                }

                // Student phone is now optional - no validation needed

                // Check student gender
                if (!$('input[name="student_gender"]:checked').val()) {
                    isValid = false;
                    showError('input[name="student_gender"]', 'Please select a gender', true);
                }

                // Check date of birth
                if (!$('input[name="dateofbirth"]').val().trim()) {
                    isValid = false;
                    showError('input[name="dateofbirth"]', 'Date of Birth is required');
                }

                // Check class
                if (!$('select[name="class_id"]').val()) {
                    isValid = false;
                    showError('select[name="class_id"]', 'Please select a class');
                }

                if (!isValid) {
                    // Scroll to first error
                    $('html, body').animate({
                        scrollTop: $('.validation-error:first').offset().top - 100
                    }, 500);
                    return;
                }
            }

            if (currentStep === 2) {
                // Validate Step 2: Parent Information (Optional)
                // Only validate if parent has started entering data
                let isValid = true;

                $('.parent-block').each(function(index) {
                    const parentIndex = $(this).data('parent-index');
                    const $nameInput = $(`input[name="parents[${parentIndex}][name]"]`);
                    const $phoneInput = $(`input[name="parents[${parentIndex}][phone]"]`);
                    const parentName = $nameInput.val().trim();
                    const parentPhone = $phoneInput.val().trim();

                    // Only validate if either field has data (partial entry validation)
                    if (parentName || parentPhone) {
                        if (!parentName) {
                            isValid = false;
                            showParentError($nameInput, `Parent #${index + 1} Name is required if phone is provided`);
                        }

                        if (!parentPhone) {
                            isValid = false;
                            showParentError($phoneInput, `Parent #${index + 1} Phone Number is required if name is provided`);
                        }
                    }
                });

                if (!isValid) {
                    // Scroll to first error
                    $('html, body').animate({
                        scrollTop: $('.validation-error:first').offset().top - 100
                    }, 500);
                    return;
                }
            }

            if (currentStep < 3) {
                currentStep++;
                showStep(currentStep);
            }
        });

        // Function to show validation error
        function showError(selector, message, isRadio = false) {
            const $field = $(selector);
            const errorHtml = `
                <p class="validation-error text-red-500 text-xs mt-1 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    ${message}
                </p>
            `;

            if (isRadio) {
                // For radio buttons, insert after the parent div
                $field.closest('.mb-3').append(errorHtml);
            } else {
                // For other inputs, insert after the field
                $field.after(errorHtml);
            }

            // Add error border
            $field.addClass('border-red-500');
        }

        // Function to show parent validation error
        function showParentError($field, message) {
            const errorHtml = `
                <p class="validation-error text-red-500 text-xs mt-1 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    ${message}
                </p>
            `;

            $field.closest('.mb-4').append(errorHtml);
            $field.addClass('border-red-500');
        }

        // Clear validation errors when user starts typing
        $(document).on('input change', 'input, select, textarea', function() {
            $(this).removeClass('border-red-500');
            $(this).siblings('.validation-error').remove();
            $(this).closest('.mb-3, .mb-4').find('.validation-error').remove();
        });

        $('.prev-btn').click(function() {
            if (currentStep > 1) {
                currentStep--;
                showStep(currentStep);
            }
        });

        // Add parent
        $('#add-parent-btn').click(function() {
            const newParentHtml = `
                <div class="parent-block bg-gradient-to-br from-blue-50 to-white border-2 border-blue-200 rounded-xl p-6 mb-3 shadow-sm hover:shadow-md transition duration-200" data-parent-index="${parentCount}">
                    <div class="flex justify-between items-center mb-3">
                        <h4 class="text-lg font-bold text-blue-700 flex items-center">
                            <span class="bg-blue-500 text-white w-8 h-8 rounded-full flex items-center justify-center mr-2 text-sm">${parentCount + 1}</span>
                            Parent #${parentCount + 1}
                        </h4>
                        <button type="button" class="remove-parent-btn bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded text-sm shadow-md hover:shadow-lg transition duration-200">
                            Remove
                        </button>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                        <input name="parents[${parentCount}][name]" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 hover:border-gray-400" type="text" placeholder="Enter parent's full name">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                        <div class="flex items-center">
                            <span class="inline-flex items-center px-3 py-3 bg-gray-100 border border-r-0 border-gray-300 rounded-l-lg text-gray-600">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                                </svg>
                            </span>
                            <input name="parents[${parentCount}][phone]" class="flex-1 px-3 py-2 bg-white border border-gray-300 rounded-r-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 hover:border-gray-400" type="text" placeholder="+27123456789">
                            <button type="button" onclick="testSms(${parentCount})" class="ml-2 px-3 py-2 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium rounded-lg shadow-sm transition duration-200" title="Test SMS">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                                </svg>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Include country code (e.g., +27 for South Africa) - Click yellow button to test SMS</p>
                    </div>
                </div>
            `;

            $('#parents-container').append(newParentHtml);
            parentCount++;
        });

        // Remove parent
        $(document).on('click', '.remove-parent-btn', function() {
            $(this).closest('.parent-block').remove();
            // Renumber parents
            $('.parent-block').each(function(index) {
                $(this).find('h4').text(`Parent #${index + 1}`);
            });
        });

        // Update review section
        function updateReview() {
            // Student info
            const studentInfo = {
                'Name': $('input[name="student_name"]').val(),
                'Email': $('input[name="student_email"]').val(),
                'Default Password': '12345678 (must change on first login)',
                'Roll Number': '{{ $nextRollNumber }}',
                'Phone': $('input[name="student_phone"]').val(),
                'Gender': $('input[name="student_gender"]:checked').val(),
                'Date of Birth': $('input[name="dateofbirth"]').val(),
                'Class': $('select[name="class_id"] option:selected').text()
            };

            let studentHtml = '';
            for (const [key, value] of Object.entries(studentInfo)) {
                studentHtml += `<div><strong>${key}:</strong> ${value || 'N/A'}</div>`;
            }
            $('#review-student').html(studentHtml);

            // Parents info
            let parentsHtml = '';
            $('.parent-block').each(function(index) {
                const parentIndex = $(this).data('parent-index');
                const parentInfo = {
                    'Name': $(`input[name="parents[${parentIndex}][name]"]`).val(),
                    'Phone': $(`input[name="parents[${parentIndex}][phone]"]`).val()
                };

                parentsHtml += `<div class="mb-4 pb-4 border-b border-gray-200">
                    <h5 class="font-medium mb-2 flex items-center">
                        <span class="bg-blue-500 text-white w-6 h-6 rounded-full flex items-center justify-center text-xs mr-2">${index + 1}</span>
                        Parent #${index + 1}
                    </h5>
                    <div class="grid grid-cols-2 gap-2 text-sm">`;

                for (const [key, value] of Object.entries(parentInfo)) {
                    parentsHtml += `<div><strong>${key}:</strong> ${value || 'N/A'}</div>`;
                }

                parentsHtml += `</div>
                    <p class="text-xs text-blue-600 mt-2"><i class="fas fa-sms mr-1"></i> SMS will be sent to complete registration</p>
                </div>`;
            });
            $('#review-parents').html(parentsHtml);
        }

        // Form submission handler
        $('#studentParentForm').on('submit', function(e) {
            console.log('=== FORM SUBMISSION DEBUG ===');
            console.log('Form data:');

            // Log all form data
            const formData = new FormData(this);
            for (let [key, value] of formData.entries()) {
                console.log(key + ': ' + value);
            }

            console.log('Parents count:', $('.parent-block').length);
            console.log('=== END DEBUG ===');

            // Continue with normal submission
            // e.preventDefault(); // Uncomment to prevent actual submission for testing
        });
    });

    // Test SMS function - opens SMS test page with phone number and auto-submits
    function testSms(parentIndex) {
        const phoneInput = document.querySelector(`input[name="parents[${parentIndex}][phone]"]`);
        if (phoneInput && phoneInput.value) {
            const phone = encodeURIComponent(phoneInput.value);
            window.open(`{{ route('sms-test.index') }}?phone=${phone}&auto=1`, '_blank');
        } else {
            alert('Please enter a phone number first');
        }
    }

    // Student type selection function
    function selectStudentType(type) {
        const dayCard = document.querySelector('.student-type-card[data-type="day"]');
        const boardingCard = document.querySelector('.student-type-card[data-type="boarding"]');
        const dayRadio = document.getElementById('student_type_day');
        const boardingRadio = document.getElementById('student_type_boarding');
        const dayIndicator = dayCard.querySelector('.check-indicator');
        const boardingIndicator = boardingCard.querySelector('.check-indicator');

        if (type === 'day') {
            // Select day
            dayRadio.checked = true;
            dayCard.classList.remove('border-gray-300');
            dayCard.classList.add('border-blue-500', 'bg-blue-50');
            dayIndicator.classList.remove('hidden', 'border-gray-300');
            dayIndicator.classList.add('bg-blue-500', 'border-blue-500');

            // Deselect boarding
            boardingRadio.checked = false;
            boardingCard.classList.remove('border-green-500', 'bg-green-50');
            boardingCard.classList.add('border-gray-300');
            boardingIndicator.classList.add('hidden');
            boardingIndicator.classList.remove('bg-green-500', 'border-green-500');
            boardingIndicator.classList.add('border-gray-300');
        } else {
            // Select boarding
            boardingRadio.checked = true;
            boardingCard.classList.remove('border-gray-300');
            boardingCard.classList.add('border-green-500', 'bg-green-50');
            boardingIndicator.classList.remove('hidden', 'border-gray-300');
            boardingIndicator.classList.add('bg-green-500', 'border-green-500');

            // Deselect day
            dayRadio.checked = false;
            dayCard.classList.remove('border-blue-500', 'bg-blue-50');
            dayCard.classList.add('border-gray-300');
            dayIndicator.classList.add('hidden');
            dayIndicator.classList.remove('bg-blue-500', 'border-blue-500');
            dayIndicator.classList.add('border-gray-300');
        }
    }

    // Curriculum type selection function
    function selectCurriculumType(type) {
        const zimsecCard = document.querySelector('.curriculum-type-card[data-type="zimsec"]');
        const cambridgeCard = document.querySelector('.curriculum-type-card[data-type="cambridge"]');
        const zimsecRadio = document.getElementById('curriculum_type_zimsec');
        const cambridgeRadio = document.getElementById('curriculum_type_cambridge');
        const zimsecIndicator = zimsecCard.querySelector('.curriculum-check-indicator');
        const cambridgeIndicator = cambridgeCard.querySelector('.curriculum-check-indicator');

        if (type === 'zimsec') {
            zimsecRadio.checked = true;
            zimsecCard.classList.remove('border-gray-300');
            zimsecCard.classList.add('border-indigo-500', 'bg-indigo-50');
            zimsecIndicator.classList.remove('hidden', 'border-gray-300');
            zimsecIndicator.classList.add('bg-indigo-500', 'border-indigo-500');

            cambridgeRadio.checked = false;
            cambridgeCard.classList.remove('border-purple-500', 'bg-purple-50');
            cambridgeCard.classList.add('border-gray-300');
            cambridgeIndicator.classList.add('hidden');
            cambridgeIndicator.classList.remove('bg-purple-500', 'border-purple-500');
            cambridgeIndicator.classList.add('border-gray-300');
        } else {
            cambridgeRadio.checked = true;
            cambridgeCard.classList.remove('border-gray-300');
            cambridgeCard.classList.add('border-purple-500', 'bg-purple-50');
            cambridgeIndicator.classList.remove('hidden', 'border-gray-300');
            cambridgeIndicator.classList.add('bg-purple-500', 'border-purple-500');

            zimsecRadio.checked = false;
            zimsecCard.classList.remove('border-indigo-500', 'bg-indigo-50');
            zimsecCard.classList.add('border-gray-300');
            zimsecIndicator.classList.add('hidden');
            zimsecIndicator.classList.remove('bg-indigo-500', 'border-indigo-500');
            zimsecIndicator.classList.add('border-gray-300');
        }
    }

    // New student status selection function
    window.selectNewStudentStatus = function(isNew) {
        try {
            const newCard = document.querySelector('.new-student-card[data-status="new"]');
            const existingCard = document.querySelector('.new-student-card[data-status="existing"]');
            const newRadio = document.getElementById('is_new_student_yes');
            const existingRadio = document.getElementById('is_new_student_no');

            if (!newCard || !existingCard || !newRadio || !existingRadio) {
                console.error('New student selector elements not found');
                return;
            }

            const newIndicator = newCard.querySelector('.new-check-indicator');
            const existingIndicator = existingCard.querySelector('.new-check-indicator');

            if (isNew) {
                newRadio.checked = true;
                if (newCard.setAttribute) newCard.setAttribute('aria-pressed', 'true');
                if (existingCard.setAttribute) existingCard.setAttribute('aria-pressed', 'false');
                // apply inline colors to ensure they show even if Tailwind utilities were purged
                newCard.classList.remove('border-gray-300');
                newCard.style.borderColor = '#10B981'; // emerald-500
                newCard.style.backgroundColor = '#ECFDF5'; // emerald-50
                if (newIndicator) {
                    newIndicator.classList.remove('hidden', 'border-gray-300');
                    newIndicator.style.backgroundColor = '#10B981';
                    newIndicator.style.borderColor = '#10B981';
                }

                existingRadio.checked = false;
                // reset existing card inline styles
                existingCard.classList.remove('border-amber-500', 'bg-amber-50');
                existingCard.classList.add('border-gray-300');
                existingCard.style.borderColor = '';
                existingCard.style.backgroundColor = '';
                if (existingIndicator) {
                    existingIndicator.classList.add('hidden');
                    existingIndicator.classList.remove('bg-amber-500', 'border-amber-500');
                    existingIndicator.classList.add('border-gray-300');
                    existingIndicator.style.backgroundColor = '';
                    existingIndicator.style.borderColor = '';
                }
            } else {
                existingRadio.checked = true;
                if (existingCard.setAttribute) existingCard.setAttribute('aria-pressed', 'true');
                if (newCard.setAttribute) newCard.setAttribute('aria-pressed', 'false');
                // apply inline amber colors
                existingCard.classList.remove('border-gray-300');
                existingCard.style.borderColor = '#D97706'; // amber-500
                existingCard.style.backgroundColor = '#FFFBEB'; // amber-50
                if (existingIndicator) {
                    existingIndicator.classList.remove('hidden', 'border-gray-300');
                    existingIndicator.style.backgroundColor = '#D97706';
                    existingIndicator.style.borderColor = '#D97706';
                }

                newRadio.checked = false;
                // reset new card inline styles
                newCard.classList.remove('border-emerald-500', 'bg-emerald-50');
                newCard.classList.add('border-gray-300');
                newCard.style.borderColor = '';
                newCard.style.backgroundColor = '';
                if (newIndicator) {
                    newIndicator.classList.add('hidden');
                    newIndicator.classList.remove('bg-emerald-500', 'border-emerald-500');
                    newIndicator.classList.add('border-gray-300');
                    newIndicator.style.backgroundColor = '';
                    newIndicator.style.borderColor = '';
                }
            }
        } catch (error) {
            console.error('Error in selectNewStudentStatus:', error);
        }
    };

    // Generate new roll number via AJAX
    function generateNewRollNumber() {
        console.log('Generate New Roll Number clicked');

        const btn = document.getElementById('generate-new-roll');
        const display = document.getElementById('roll-number-display');
        const emailInput = document.getElementById('student_email');

        if (!btn || !display || !emailInput) {
            console.error('Required elements not found:', { btn, display, emailInput });
            alert('Error: Required elements not found on page');
            return;
        }

        // Disable button and show loading
        btn.disabled = true;
        btn.innerHTML = '<svg class="animate-spin w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Generating...';

        const url = '{{ route("student.generate-roll-number") }}';
        console.log('Fetching:', url);

        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.roll_number) {
                display.textContent = data.roll_number;
                // Also update the email field with the new roll number using dynamic domain
                const emailDomain = document.getElementById('email_domain').value || 'roshs.co.zw';
                emailInput.value = data.roll_number.toLowerCase() + '@' + emailDomain;
                console.log('Roll number updated to:', data.roll_number);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to generate new roll number: ' + error.message);
        })
        .finally(() => {
            // Re-enable button
            btn.disabled = false;
            btn.innerHTML = '<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Generate New';
        });
    }

    // Update file name display when file is selected for bulk import
    function updateStudentFileName(input) {
        const fileNameDisplay = document.getElementById('studentFileName');
        if (input.files && input.files[0]) {
            fileNameDisplay.textContent = '📄 ' + input.files[0].name;
            fileNameDisplay.classList.remove('hidden');
        } else {
            fileNameDisplay.classList.add('hidden');
        }
    }
</script>
@endpush
