@extends('layouts.app')

@section('content')
<div x-data="syllabusForm()" class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Create Syllabus Topics</h1>
                <p class="mt-2 text-sm text-gray-600">Add multiple topics to your syllabus at once</p>
            </div>
            <a href="{{ route('teacher.syllabus.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
            <strong class="font-bold">Validation Error!</strong>
            <ul class="mt-2 list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- PDF Import Sections -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- ZIMSEC Import -->
        <div class="bg-gradient-to-r from-purple-50 to-blue-50 rounded-xl shadow-sm border border-purple-200 p-6">
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-gray-900 mb-1">ZIMSEC Syllabus Import</h3>
                    <p class="text-xs text-gray-600 mb-3">Import Ministry/ZIMSEC structured syllabus</p>
                    
                    <form id="zimsecPreviewForm" method="POST" enctype="multipart/form-data" class="space-y-3" onsubmit="return false;">
                        @csrf
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Subject *</label>
                            <select id="zimsec_subject_id" name="subject_id" required class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                <option value="">Select Subject</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->subject_code }} - {{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Term *</label>
                            <select id="zimsec_term" name="term" required class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                <option value="Term 1">Term 1</option>
                                <option value="Term 2">Term 2</option>
                                <option value="Term 3">Term 3</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">PDF File *</label>
                            <input type="file" id="zimsec_pdf_file" name="pdf_file" accept=".pdf" required 
                                   class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                        </div>
                        
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-2 text-xs text-gray-700">
                            <strong class="text-blue-900">Commerce Table Format:</strong>
                            <ul class="mt-1 ml-3 list-disc space-y-0.5 text-xs">
                                <li>Section headers: <strong>8.6 FINANCE AND BANKING</strong></li>
                                <li>Subtopics: <strong>Personal Finance</strong>, <strong>Money</strong></li>
                                <li>Bullet points for learning objectives</li>
                            </ul>
                        </div>
                        
                        <div class="flex justify-end gap-2 pt-1">
                            <button type="button" onclick="previewZimsec(event)" class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow transition-colors">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Preview
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Cambridge Import -->
        <div class="bg-gradient-to-r from-green-50 to-teal-50 rounded-xl shadow-sm border border-green-200 p-6">
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Cambridge IGCSE Import</h3>
                    <p class="text-xs text-gray-600 mb-3">Import Cambridge IGCSE syllabus</p>
                    
                    <form id="cambridgePreviewForm" method="POST" enctype="multipart/form-data" class="space-y-3" onsubmit="return false;">
                        @csrf
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Subject *</label>
                            <select id="cambridge_subject_id" name="subject_id" required class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                <option value="">Select Subject</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->subject_code }} - {{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Term *</label>
                            <select id="cambridge_term" name="term" required class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                <option value="Term 1">Term 1</option>
                                <option value="Term 2">Term 2</option>
                                <option value="Term 3">Term 3</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">PDF File *</label>
                            <input type="file" id="cambridge_pdf_file" name="pdf_file" accept=".pdf" required 
                                   class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                        </div>
                        
                        <div class="bg-teal-50 border border-teal-200 rounded-lg p-2 text-xs text-gray-700">
                            <strong class="text-teal-900">Required Format:</strong>
                            <ul class="mt-1 ml-3 list-disc space-y-0.5 text-xs">
                                <li><strong>3 Subject content</strong> section</li>
                                <li>Numbered sections (1, 2, 3...)</li>
                                <li>Subsections (1.1, 1.2, 2.1...)</li>
                                <li><strong>Candidates should be able to:</strong></li>
                                <li><strong>Notes and guidance</strong></li>
                            </ul>
                        </div>
                        
                        <div class="flex justify-end pt-1">
                            <button type="button" onclick="previewCambridge(event)" class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow transition-colors">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Preview
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="relative mb-6">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-300"></div>
        </div>
        <div class="relative flex justify-center text-sm">
            <span class="px-4 bg-gray-50 text-gray-500 font-medium">OR CREATE MANUALLY</span>
        </div>
    </div>

    <form action="{{ route('teacher.syllabus.store') }}" method="POST">
        @csrf
        <input type="hidden" name="multiple" value="1">

        <!-- Subject and Term Selection -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Subject & Term</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Subject *</label>
                    <select name="subject_id" x-model="subjectId" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                        <option value="">Select Subject</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->subject_code }} - {{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Term *</label>
                    <select name="term" x-model="term" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                        @foreach($terms as $t)
                            <option value="{{ $t }}">{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Topics List -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Topics</h2>
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-500"><span x-text="topics.length"></span> topic(s)</span>
                    <button type="button" @click="addTopic()" class="inline-flex items-center px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Topic
                    </button>
                </div>
            </div>

            <div class="space-y-4">
                <template x-for="(topic, index) in topics" :key="index">
                    <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-medium text-gray-700">Topic #<span x-text="index + 1"></span></h4>
                            <button type="button" @click="removeTopic(index)" x-show="topics.length > 1" class="text-red-500 hover:text-red-700 text-sm font-medium">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Topic Name *</label>
                                <input type="text" :name="'topics[' + index + '][name]'" x-model="topic.name" 
                                       class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                       placeholder="e.g., Quadratic Equations" required>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
                                <input type="text" :name="'topics[' + index + '][description]'" x-model="topic.description" 
                                       class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                       placeholder="Brief description...">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Difficulty *</label>
                                <select :name="'topics[' + index + '][difficulty_level]'" x-model="topic.difficulty_level" 
                                        class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                    <option value="easy">Easy</option>
                                    <option value="medium">Medium</option>
                                    <option value="hard">Hard</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Suggested Periods *</label>
                                <input type="number" :name="'topics[' + index + '][suggested_periods]'" x-model="topic.suggested_periods" 
                                       min="1" max="20" class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Order</label>
                                <input type="number" :name="'topics[' + index + '][order_index]'" x-model="topic.order_index" 
                                       min="0" class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div class="flex items-end">
                                <label class="flex items-center">
                                    <input type="checkbox" :name="'topics[' + index + '][is_active]'" x-model="topic.is_active" value="1"
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">Active</span>
                                </label>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Learning Objectives</label>
                            <textarea :name="'topics[' + index + '][learning_objectives]'" x-model="topic.learning_objectives" rows="2"
                                      class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                      placeholder="What students should learn..."></textarea>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Quick Add -->
            <div class="mt-4 pt-4 border-t border-gray-200">
                <button type="button" @click="addTopic()" class="w-full py-3 border-2 border-dashed border-gray-300 rounded-lg text-gray-500 hover:border-blue-400 hover:text-blue-600 transition-colors flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Another Topic
                </button>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-between">
            <p class="text-sm text-gray-500">
                <span x-text="topics.length"></span> topic(s) will be created for the selected subject
            </p>
            <div class="flex items-center space-x-3">
                <a href="{{ route('teacher.syllabus.index') }}" class="px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-colors">
                    Cancel
                </a>
                <button type="submit" :disabled="topics.length === 0 || !subjectId" 
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    Create <span x-text="topics.length"></span> Topic(s)
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function syllabusForm() {
    return {
        subjectId: '',
        term: 'Term 1',
        topics: [
            {
                name: '',
                description: '',
                learning_objectives: '',
                difficulty_level: 'medium',
                suggested_periods: 4,
                order_index: 1,
                is_active: true
            }
        ],
        addTopic() {
            this.topics.push({
                name: '',
                description: '',
                learning_objectives: '',
                difficulty_level: 'medium',
                suggested_periods: 4,
                order_index: this.topics.length + 1,
                is_active: true
            });
        },
        removeTopic(index) {
            if (this.topics.length > 1) {
                this.topics.splice(index, 1);
                // Update order indices
                this.topics.forEach((t, i) => t.order_index = i + 1);
            }
        }
    }
}

// ZIMSEC Preview Functionality
function previewZimsec(event) {
    event.preventDefault();
    event.stopPropagation();
    
    const form = document.getElementById('zimsecPreviewForm');
    const formData = new FormData(form);
    
    // Show loading
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<svg class="animate-spin h-3 w-3 mr-1 inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Processing...';
    
    fetch('{{ route("teacher.syllabus.preview-zimsec") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        
        if (data.success) {
            showPreviewModal(data);
        } else {
            alert('Preview failed: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        alert('Error: ' + error.message);
    });
}

// Global storage for parsed topics
let parsedTopicsData = null;

function showPreviewModal(data) {
    const modal = document.getElementById('previewModal');
    const content = document.getElementById('previewContent');
    
    // Store data globally for import
    parsedTopicsData = data;
    
    let html = `
        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <h3 class="font-bold text-lg text-blue-900">Import Summary</h3>
            <p class="text-sm text-gray-700 mt-1">
                <strong>Subject:</strong> ${data.subject}<br>
                <strong>Term:</strong> ${data.term}<br>
                <strong>Topics Found:</strong> ${data.count}
            </p>
            <div class="mt-3 flex items-center gap-3">
                <label class="flex items-center text-sm">
                    <input type="checkbox" id="selectAllTopics" onchange="toggleAllTopics(this)" checked class="w-4 h-4 text-purple-600 rounded border-gray-300 focus:ring-purple-500 mr-2">
                    Select All
                </label>
                <span id="selectedCount" class="text-xs text-gray-600">${data.count} selected</span>
            </div>
        </div>
        
        <div class="space-y-3 max-h-96 overflow-y-auto" id="topicsList">
    `;
    
    data.topics.forEach((topic, index) => {
        html += `
            <div class="border border-gray-200 rounded-lg p-3 bg-gray-50 topic-item" data-index="${index}">
                <div class="flex items-start gap-3">
                    <input type="checkbox" class="topic-checkbox w-4 h-4 mt-1 text-purple-600 rounded border-gray-300 focus:ring-purple-500" data-index="${index}" checked onchange="updateSelectedCount()">
                    <div class="flex-1">
                        <div class="flex items-start justify-between mb-2">
                            <h4 class="font-semibold text-gray-900">${index + 1}. ${escapeHtmlPreview(topic.name)}</h4>
                            <span class="text-xs px-2 py-1 bg-blue-100 text-blue-800 rounded">${topic.difficulty_level}</span>
                        </div>
                        
                        ${topic.learning_objectives ? `
                            <div class="mb-2">
                                <p class="text-xs font-medium text-green-700 mb-1">📚 Learning Objectives:</p>
                                <p class="text-xs text-gray-700 whitespace-pre-line bg-green-50 p-2 rounded border border-green-100">${escapeHtmlPreview(topic.learning_objectives)}</p>
                            </div>
                        ` : ''}
                        
                        ${topic.description ? `
                            <div class="mb-2">
                                <p class="text-xs font-medium text-blue-700 mb-1">📝 Content (Description):</p>
                                <p class="text-xs text-gray-700 whitespace-pre-line bg-blue-50 p-2 rounded border border-blue-100">${escapeHtmlPreview(topic.description)}</p>
                            </div>
                        ` : ''}
                        
                        <div class="flex gap-4 text-xs text-gray-600 mt-2">
                            <span><strong>Periods:</strong> ${topic.suggested_periods}</span>
                            <span><strong>Order:</strong> ${topic.order_index}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    content.innerHTML = html;
    modal.classList.remove('hidden');
}

function escapeHtmlPreview(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function toggleAllTopics(checkbox) {
    const checkboxes = document.querySelectorAll('.topic-checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
    updateSelectedCount();
}

function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('.topic-checkbox');
    const checked = document.querySelectorAll('.topic-checkbox:checked');
    document.getElementById('selectedCount').textContent = `${checked.length} of ${checkboxes.length} selected`;
    document.getElementById('selectAllTopics').checked = checked.length === checkboxes.length;
}

function closePreviewModal() {
    document.getElementById('previewModal').classList.add('hidden');
}

function confirmImport() {
    if (!parsedTopicsData) {
        alert('No topics data available. Please preview again.');
        return;
    }
    
    // Get selected topics
    const checkboxes = document.querySelectorAll('.topic-checkbox:checked');
    if (checkboxes.length === 0) {
        alert('Please select at least one topic to import.');
        return;
    }
    
    const selectedIndices = [];
    checkboxes.forEach(cb => selectedIndices.push(parseInt(cb.dataset.index)));
    
    // Filter only selected topics
    const selectedTopics = selectedIndices.map(i => parsedTopicsData.topics[i]);
    
    // Get form data
    const subjectId = document.getElementById('zimsec_subject_id').value;
    const term = document.getElementById('zimsec_term').value;
    
    // Close modal
    closePreviewModal();
    
    // Confirm import
    if (confirm(`Import ${selectedTopics.length} topic(s)? Click OK to proceed.`)) {
        // Create request body
        const requestData = {
            subject_id: subjectId,
            term: term,
            topics: selectedTopics,
            _token: '{{ csrf_token() }}'
        };
        
        fetch('{{ route("teacher.syllabus.import-zimsec") }}', {
            method: 'POST',
            body: JSON.stringify(requestData),
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message || `${data.imported} topic(s) imported successfully!`);
                window.location.href = '{{ route("teacher.syllabus.index") }}';
            } else {
                alert('Import failed: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            alert('Error during import: ' + error.message);
        });
    }
}

// Cambridge Preview Functionality
let parsedCambridgeData = null;

function previewCambridge(event) {
    event.preventDefault();
    event.stopPropagation();
    
    const form = document.getElementById('cambridgePreviewForm');
    const formData = new FormData(form);
    
    // Show loading
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<svg class="animate-spin h-3 w-3 mr-1 inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Processing...';
    
    fetch('{{ route("teacher.syllabus.preview-cambridge") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        
        if (data.success) {
            showCambridgePreviewModal(data);
        } else {
            alert('Preview failed: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        alert('Error: ' + error.message);
    });
}

function showCambridgePreviewModal(data) {
    const modal = document.getElementById('cambridgePreviewModal');
    const content = document.getElementById('cambridgePreviewContent');
    
    // Store data globally for import
    parsedCambridgeData = data;
    
    let html = `
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
            <h3 class="font-bold text-lg text-green-900">Import Summary</h3>
            <p class="text-sm text-gray-700 mt-1">
                <strong>Subject:</strong> ${data.subject}<br>
                <strong>Term:</strong> ${data.term}<br>
                <strong>Topics Found:</strong> ${data.count}
            </p>
            <div class="mt-3 flex items-center gap-3">
                <label class="flex items-center text-sm">
                    <input type="checkbox" id="selectAllCambridgeTopics" onchange="toggleAllCambridgeTopics(this)" checked class="w-4 h-4 text-green-600 rounded border-gray-300 focus:ring-green-500 mr-2">
                    Select All
                </label>
                <span id="cambridgeSelectedCount" class="text-xs text-gray-600">${data.count} selected</span>
            </div>
        </div>
        
        <div class="space-y-3 max-h-96 overflow-y-auto" id="cambridgeTopicsList">
    `;
    
    data.topics.forEach((topic, index) => {
        html += `
            <div class="border border-gray-200 rounded-lg p-3 bg-gray-50 cambridge-topic-item" data-index="${index}">
                <div class="flex items-start gap-3">
                    <input type="checkbox" class="cambridge-topic-checkbox w-4 h-4 mt-1 text-green-600 rounded border-gray-300 focus:ring-green-500" data-index="${index}" checked onchange="updateCambridgeSelectedCount()">
                    <div class="flex-1">
                        <div class="flex items-start justify-between mb-2">
                            <h4 class="font-semibold text-gray-900">${index + 1}. ${escapeHtmlPreview(topic.name)}</h4>
                            <span class="text-xs px-2 py-1 bg-green-100 text-green-800 rounded">${topic.difficulty_level || 'medium'}</span>
                        </div>
                        
                        ${topic.learning_objectives ? `
                            <div class="mb-2">
                                <p class="text-xs font-medium text-green-700 mb-1">📚 Learning Objectives:</p>
                                <p class="text-xs text-gray-700 whitespace-pre-line bg-green-50 p-2 rounded border border-green-100">${escapeHtmlPreview(topic.learning_objectives)}</p>
                            </div>
                        ` : ''}
                        
                        ${topic.description ? `
                            <div class="mb-2">
                                <p class="text-xs font-medium text-teal-700 mb-1">📝 Notes & Guidance:</p>
                                <p class="text-xs text-gray-700 whitespace-pre-line bg-teal-50 p-2 rounded border border-teal-100">${escapeHtmlPreview(topic.description)}</p>
                            </div>
                        ` : ''}
                        
                        <div class="flex gap-4 text-xs text-gray-600 mt-2">
                            <span><strong>Periods:</strong> ${topic.suggested_periods || 4}</span>
                            <span><strong>Order:</strong> ${topic.order_index || index + 1}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    content.innerHTML = html;
    modal.classList.remove('hidden');
}

function toggleAllCambridgeTopics(checkbox) {
    const checkboxes = document.querySelectorAll('.cambridge-topic-checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
    updateCambridgeSelectedCount();
}

function updateCambridgeSelectedCount() {
    const checkboxes = document.querySelectorAll('.cambridge-topic-checkbox');
    const checked = document.querySelectorAll('.cambridge-topic-checkbox:checked');
    document.getElementById('cambridgeSelectedCount').textContent = `${checked.length} of ${checkboxes.length} selected`;
    document.getElementById('selectAllCambridgeTopics').checked = checked.length === checkboxes.length;
}

function closeCambridgePreviewModal() {
    document.getElementById('cambridgePreviewModal').classList.add('hidden');
}

function confirmCambridgeImport() {
    if (!parsedCambridgeData) {
        alert('No topics data available. Please preview again.');
        return;
    }
    
    // Get selected topics
    const checkboxes = document.querySelectorAll('.cambridge-topic-checkbox:checked');
    if (checkboxes.length === 0) {
        alert('Please select at least one topic to import.');
        return;
    }
    
    const selectedIndices = [];
    checkboxes.forEach(cb => selectedIndices.push(parseInt(cb.dataset.index)));
    
    // Filter only selected topics
    const selectedTopics = selectedIndices.map(i => parsedCambridgeData.topics[i]);
    
    // Get form data
    const subjectId = document.getElementById('cambridge_subject_id').value;
    const term = document.getElementById('cambridge_term').value;
    
    // Close modal
    closeCambridgePreviewModal();
    
    // Confirm import
    if (confirm(`Import ${selectedTopics.length} Cambridge IGCSE topic(s)? Click OK to proceed.`)) {
        // Create request body
        const requestData = {
            subject_id: subjectId,
            term: term,
            topics: selectedTopics,
            _token: '{{ csrf_token() }}'
        };
        
        fetch('{{ route("teacher.syllabus.import-cambridge") }}', {
            method: 'POST',
            body: JSON.stringify(requestData),
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message || `${data.imported} topic(s) imported successfully!`);
                window.location.href = '{{ route("teacher.syllabus.index") }}';
            } else {
                alert('Import failed: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            alert('Error during import: ' + error.message);
        });
    }
}
</script>

<!-- Preview Modal -->
<div id="previewModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-lg bg-white">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-gray-900">Preview ZIMSEC Import</h3>
            <button onclick="closePreviewModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <div id="previewContent" class="mb-6"></div>
        
        <div class="flex justify-end gap-3">
            <button onclick="closePreviewModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-colors">
                Cancel
            </button>
            <button onclick="confirmImport()" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition-colors">
                Confirm & Import
            </button>
        </div>
    </div>
</div>

<!-- Cambridge Preview Modal -->
<div id="cambridgePreviewModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-lg bg-white">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-gray-900">Preview Cambridge IGCSE Import</h3>
            <button onclick="closeCambridgePreviewModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <div id="cambridgePreviewContent" class="mb-6"></div>
        
        <div class="flex justify-end gap-3">
            <button onclick="closeCambridgePreviewModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-colors">
                Cancel
            </button>
            <button onclick="confirmCambridgeImport()" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors">
                Confirm & Import
            </button>
        </div>
    </div>
</div>

@endsection
