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
                    
                    <form action="{{ route('teacher.syllabus.import') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Subject *</label>
                            <select name="subject_id" required class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                <option value="">Select Subject</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->subject_code }} - {{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">PDF File *</label>
                            <input type="file" name="pdf_file" accept=".pdf" required 
                                   class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                        </div>
                        
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-2 text-xs text-gray-700">
                            <strong class="text-blue-900">Required Format:</strong>
                            <ul class="mt-1 ml-3 list-disc space-y-0.5 text-xs">
                                <li><strong>FORM 1-4</strong> markers</li>
                                <li><strong>TOPIC</strong> sections</li>
                                <li><strong>LEARNING OBJECTIVES</strong>, <strong>CONTENT</strong>, <strong>ACTIVITIES</strong>, <strong>RESOURCES</strong></li>
                            </ul>
                        </div>
                        
                        <div class="flex justify-end pt-1">
                            <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold rounded-lg shadow transition-colors">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                                Import ZIMSEC
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
                    
                    <form action="{{ route('teacher.syllabus.import-cambridge') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Subject *</label>
                            <select name="subject_id" required class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                <option value="">Select Subject</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->subject_code }} - {{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Term *</label>
                            <select name="term" required class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                <option value="Term 1">Term 1</option>
                                <option value="Term 2">Term 2</option>
                                <option value="Term 3">Term 3</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">PDF File *</label>
                            <input type="file" name="pdf_file" accept=".pdf" required 
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
                            <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded-lg shadow transition-colors">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                                Import Cambridge
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
</script>
@endsection
