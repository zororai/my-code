@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Add Multiple Subjects</h1>
                <p class="mt-2 text-sm text-gray-600">Select subjects and configure timetable settings for a class</p>
            </div>
            <a href="{{ route('admin.subjects.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to List
            </a>
        </div>
    </div>

    @if($errors->any())
    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
        <ul class="list-disc list-inside text-sm text-red-600">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.subjects.store') }}" method="POST" id="subjectForm">
        @csrf
        
        <!-- Class Selection Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <span class="w-8 h-8 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </span>
                    Select Form
                </h3>
            </div>
            <div class="p-6">
                <div class="max-w-md">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Class <span class="text-red-500">*</span></label>
                    <select name="class_id" id="classSelect" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Select a Form --</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" data-name="{{ $class->class_name }}">{{ $class->class_name }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">All selected subjects will be assigned to this class</p>
                </div>
            </div>
        </div>

        <!-- Two Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- LEFT: Subject Materials with Checkboxes -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-green-50 to-emerald-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <span class="w-8 h-8 bg-green-100 text-green-600 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </span>
                        Available Subjects
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 ml-11">Check subjects to add them</p>
                </div>
                <div class="p-4 max-h-[500px] overflow-y-auto">
                    @if($onboardSubjects->count() > 0)
                        <div class="space-y-2">
                            @foreach($onboardSubjects as $index => $subject)
                            <label class="flex items-center p-3 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer transition-colors subject-checkbox-label" data-subject-id="{{ $subject->id }}">
                                <input type="checkbox" 
                                    class="subject-checkbox w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500"
                                    data-subject-id="{{ $subject->id }}"
                                    data-subject-name="{{ $subject->name }}"
                                    onchange="toggleSubject(this)">
                                <span class="ml-3 text-sm font-medium text-gray-700">{{ $subject->name }}</span>
                            </label>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                            <p class="text-gray-500 text-sm">No subjects available</p>
                            <a href="{{ route('admin.onboard-subjects.index') }}" class="text-blue-500 hover:underline text-sm">Add subjects first</a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- RIGHT: Selected Subjects with Settings -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-indigo-50 to-purple-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <span class="w-8 h-8 bg-indigo-100 text-indigo-600 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                            </svg>
                        </span>
                        Subject Settings
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 ml-11"><span id="subjectCount">0</span> subject(s) selected</p>
                </div>
                <div id="selectedSubjectsContainer" class="p-4 max-h-[500px] overflow-y-auto">
                    <div id="emptyState" class="text-center py-12">
                        <svg class="w-16 h-16 text-gray-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <p class="text-gray-400 text-sm">Select subjects from the list to configure their settings</p>
                    </div>
                    <div id="subjectsSettings" class="space-y-4 hidden">
                        <!-- Subject settings will be added here dynamically -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Section -->
        <div class="mt-6 bg-white rounded-2xl shadow-sm border border-gray-200 px-6 py-4 flex items-center justify-between">
            <p class="text-sm text-gray-500">Subject codes are auto-generated based on class selection</p>
            <div class="flex space-x-4">
                <a href="{{ route('admin.subjects.index') }}" class="px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Create Subjects
                </button>
            </div>
        </div>
    </form>
</div>

<script>
let selectedClassName = '';
let selectedSubjects = {};

document.getElementById('classSelect').addEventListener('change', function() {
    selectedClassName = this.options[this.selectedIndex].dataset.name || '';
    updateAllSubjectCodes();
});

function generateSubjectCode(subjectName) {
    if (!subjectName || !selectedClassName) return '----';
    
    const cleanSubject = subjectName.toUpperCase().trim();
    const className = selectedClassName.trim();
    
    if (cleanSubject.length < 1 || className.length < 1) return '----';
    
    const formMatch = className.match(/Form\s+(\d+)\s+(\w+)/i);
    
    if (!formMatch) {
        const cleanClass = className.toUpperCase().replace(/[^A-Za-z0-9]/g, '');
        const firstLetterSubject = cleanSubject.charAt(0);
        const lastLetterSubject = cleanSubject.charAt(cleanSubject.length - 1);
        const firstCharClass = cleanClass.charAt(0);
        const lastCharClass = cleanClass.charAt(cleanClass.length - 1);
        return firstLetterSubject + lastLetterSubject + firstCharClass + lastCharClass;
    }
    
    const formNumber = formMatch[1];
    const stream = formMatch[2];
    const streamInitial = stream.charAt(0).toUpperCase();
    const firstLetterSubject = cleanSubject.charAt(0);
    const lastLetterSubject = cleanSubject.charAt(cleanSubject.length - 1);
    
    return `F${formNumber}${streamInitial} ${firstLetterSubject}${lastLetterSubject}`;
}

function updateAllSubjectCodes() {
    Object.keys(selectedSubjects).forEach(id => {
        const codeInput = document.getElementById(`subject_code_${id}`);
        const codeDisplay = document.getElementById(`subject_code_display_${id}`);
        if (codeInput && codeDisplay) {
            const code = generateSubjectCode(selectedSubjects[id]);
            codeInput.value = code !== '----' ? code : '';
            codeDisplay.textContent = code;
        }
    });
}

function calculateTotal(id) {
    const single = parseInt(document.getElementById(`single_${id}`).value) || 0;
    const double = parseInt(document.getElementById(`double_${id}`).value) || 0;
    const triple = parseInt(document.getElementById(`triple_${id}`).value) || 0;
    const quad = parseInt(document.getElementById(`quad_${id}`)?.value) || 0;
    const total = (single * 1) + (double * 2) + (triple * 3) + (quad * 4);
    document.getElementById(`total_${id}`).textContent = total;
}

function toggleSubject(checkbox) {
    const subjectId = checkbox.dataset.subjectId;
    const subjectName = checkbox.dataset.subjectName;
    
    if (checkbox.checked) {
        selectedSubjects[subjectId] = subjectName;
        addSubjectSettings(subjectId, subjectName);
    } else {
        delete selectedSubjects[subjectId];
        removeSubjectSettings(subjectId);
    }
    
    updateUI();
}

function addSubjectSettings(id, name) {
    const container = document.getElementById('subjectsSettings');
    const code = generateSubjectCode(name);
    
    const html = `
        <div id="subject_setting_${id}" class="bg-gray-50 rounded-xl p-4 border border-gray-200">
            <input type="hidden" name="subjects[${id}][name]" value="${name}">
            <input type="hidden" id="subject_code_${id}" name="subjects[${id}][subject_code]" value="${code !== '----' ? code : ''}">
            
            <div class="flex items-center justify-between mb-3">
                <h4 class="font-semibold text-gray-800">${name}</h4>
                <div class="px-3 py-1 bg-indigo-100 rounded-lg">
                    <span id="subject_code_display_${id}" class="text-sm font-bold text-indigo-600">${code}</span>
                </div>
            </div>
            
            <p class="text-xs text-gray-500 mb-3">Timetable Settings (Lessons per week)</p>
            <div class="grid grid-cols-4 gap-2">
                <div class="bg-white rounded-lg p-2 border border-gray-200">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Single</label>
                    <input type="number" id="single_${id}" name="subjects[${id}][single_lessons_per_week]" value="0" min="0" max="20"
                        onchange="calculateTotal('${id}')" oninput="calculateTotal('${id}')"
                        class="w-full px-2 py-1 border border-gray-200 rounded text-center text-sm font-semibold">
                </div>
                <div class="bg-white rounded-lg p-2 border border-gray-200">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Double</label>
                    <input type="number" id="double_${id}" name="subjects[${id}][double_lessons_per_week]" value="0" min="0" max="10"
                        onchange="calculateTotal('${id}')" oninput="calculateTotal('${id}')"
                        class="w-full px-2 py-1 border border-gray-200 rounded text-center text-sm font-semibold">
                </div>
                <div class="bg-white rounded-lg p-2 border border-gray-200">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Triple</label>
                    <input type="number" id="triple_${id}" name="subjects[${id}][triple_lessons_per_week]" value="0" min="0" max="5"
                        onchange="calculateTotal('${id}')" oninput="calculateTotal('${id}')"
                        class="w-full px-2 py-1 border border-gray-200 rounded text-center text-sm font-semibold">
                </div>
                <div class="bg-indigo-50 rounded-lg p-2 border border-indigo-200">
                    <label class="block text-xs font-medium text-indigo-700 mb-1">Total Periods Per Week</label>
                    <div class="text-center">
                        <span id="total_${id}" class="text-lg font-bold text-indigo-600">0</span>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', html);
}

function removeSubjectSettings(id) {
    const element = document.getElementById(`subject_setting_${id}`);
    if (element) {
        element.remove();
    }
}

function updateUI() {
    const count = Object.keys(selectedSubjects).length;
    document.getElementById('subjectCount').textContent = count;
    
    const emptyState = document.getElementById('emptyState');
    const settingsContainer = document.getElementById('subjectsSettings');
    
    if (count > 0) {
        emptyState.classList.add('hidden');
        settingsContainer.classList.remove('hidden');
    } else {
        emptyState.classList.remove('hidden');
        settingsContainer.classList.add('hidden');
    }
}
</script>
@endsection