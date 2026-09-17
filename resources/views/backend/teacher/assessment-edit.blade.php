@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Edit Assessment</h1>
                    <p class="mt-2 text-sm text-gray-600">{{ $class->class_name }}</p>
                </div>
                <a href="{{ route('teacher.assessment.list', $class->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Assessments
                </a>
            </div>
        </div>

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

        <!-- Edit Form -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
            <form action="{{ route('teacher.assessment.update', $assessment->id) }}" method="POST" id="assessmentForm">
                @csrf
                @method('PUT')

                <!-- Topic and Date Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Topic</label>
                        <input type="text" name="topic" value="{{ old('topic', $assessment->topic) }}" placeholder="Topic (min 3 characters)" maxlength="255" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                        <input type="date" name="date" value="{{ old('date', $assessment->date->format('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>
                </div>

                <!-- Subject and Assessment Type Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                        <select name="subject_id" id="editSubjectId" onchange="loadEditSyllabusTopics(this.value)" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all appearance-none bg-white">
                            <option value="">Select Subject</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ old('subject_id', $assessment->subject_id) == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->subject_code ?? '' }} - {{ $subject->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Assessment Type</label>
                        <select name="assessment_type" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all appearance-none bg-white">
                            <option value="">Assessment Type</option>
                            @foreach(['Quiz', 'Test', 'In Class Test', 'Monthly Test', 'Assignment', 'Midterm', 'Exercise', 'Project', 'Fort Night', 'Exam', 'Vacation Exam', 'National Exam'] as $type)
                                <option value="{{ $type }}" {{ old('assessment_type', $assessment->assessment_type) == $type ? 'selected' : '' }}>
                                    {{ $type }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Syllabus Topic Selection (for data analytics) -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Syllabus Topic <span class="text-gray-400">(optional - for performance tracking)</span>
                    </label>
                    <select name="syllabus_topic_id" id="editSyllabusTopicSelect"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all appearance-none bg-white">
                        <option value="">Select a syllabus topic</option>
                        @if(isset($syllabusTopics))
                            @foreach($syllabusTopics as $topic)
                                <option value="{{ $topic->id }}" {{ old('syllabus_topic_id', $assessment->syllabus_topic_id) == $topic->id ? 'selected' : '' }}>
                                    {{ $topic->name }} ({{ $topic->term ?? 'N/A' }})
                                </option>
                            @endforeach
                        @endif
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Linking to a syllabus topic enables performance analytics in Schemes of Work</p>
                </div>

                <!-- Exam Field -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Exam Name (Optional)</label>
                    <input type="text" name="exam" value="{{ old('exam', $assessment->exam) }}" placeholder="Exam Name (Optional)" maxlength="255"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>

                <!-- Papers Section -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <label class="block text-sm font-medium text-gray-700">Papers & Weights (Must add up to 100%)</label>
                        <p class="text-sm font-semibold" id="totalWeightDisplay">Total: <span id="totalWeight">0</span>%</p>
                    </div>
                    
                    <div id="papersContainer">
                        @foreach(old('papers', $assessment->papers ?? []) as $index => $paper)
                            <div class="paper-row flex items-center gap-3 mb-3">
                                <input type="text" name="papers[{{ $index }}][name]" value="{{ $paper['name'] ?? '' }}" placeholder="Paper Name (min 2 chars)" maxlength="100" required
                                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <input type="number" name="papers[{{ $index }}][total_marks]" value="{{ $paper['total_marks'] ?? '' }}" placeholder="Total Marks" max="1000" required
                                    class="w-32 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <input type="number" name="papers[{{ $index }}][weight]" value="{{ $paper['weight'] ?? '' }}" placeholder="Weight(%)" max="100" oninput="updateTotalWeight()" required
                                    class="w-32 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <button type="button" onclick="addPaper()" class="p-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 448 512">
                                        <path d="M416 208H272V64c0-17.67-14.33-32-32-32h-32c-17.67 0-32 14.33-32 32v144H32c-17.67 0-32 14.33-32 32v32c0 17.67 14.33 32 32 32h144v144c0 17.67 14.33 32 32 32h32c17.67 0 32-14.33 32-32V304h144c17.67 0 32-14.33 32-32v-32c0-17.67-14.33-32-32-32z"/>
                                    </svg>
                                </button>
                                <button type="button" onclick="removePaper(this)" class="p-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 448 512">
                                        <path d="M432 32H312l-9.4-18.7A24 24 0 0 0 281.1 0H166.8a23.72 23.72 0 0 0-21.4 13.3L136 32H16A16 16 0 0 0 0 48v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16zM53.2 467a48 48 0 0 0 47.9 45h245.8a48 48 0 0 0 47.9-45L416 128H32z"/>
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" onclick="addPaper()" class="mt-2 text-sm text-blue-600 hover:text-blue-700 font-medium">
                        + Add Another Paper
                    </button>
                </div>

                <!-- Due Date -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Due Date</label>
                    <input type="date" name="due_date" value="{{ old('due_date', $assessment->due_date ? $assessment->due_date->format('Y-m-d') : '') }}" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>

                <!-- Form Footer -->
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200">
                    <a href="{{ route('teacher.assessment.list', $class->id) }}" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">
                        Update Assessment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let paperCount = {{ count(old('papers', $assessment->papers ?? [])) }};

        function addPaper() {
            const container = document.getElementById('papersContainer');
            
            if (container.children.length >= 20) {
                alert('You cannot add more than 20 papers.');
                return;
            }
            
            const newRow = document.createElement('div');
            newRow.className = 'paper-row flex items-center gap-3 mb-3';
            newRow.innerHTML = `
                <input type="text" name="papers[${paperCount}][name]" placeholder="Paper Name (min 2 chars)" maxlength="100" required
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <input type="number" name="papers[${paperCount}][total_marks]" placeholder="Total Marks" max="1000" required
                    class="w-32 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <input type="number" name="papers[${paperCount}][weight]" placeholder="Weight(%)" max="100" oninput="updateTotalWeight()" required
                    class="w-32 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <button type="button" onclick="addPaper()" class="p-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 448 512">
                        <path d="M416 208H272V64c0-17.67-14.33-32-32-32h-32c-17.67 0-32 14.33-32 32v144H32c-17.67 0-32 14.33-32 32v32c0 17.67 14.33 32 32 32h144v144c0 17.67 14.33 32 32 32h32c17.67 0 32-14.33 32-32V304h144c17.67 0 32-14.33 32-32v-32c0-17.67-14.33-32-32-32z"/>
                    </svg>
                </button>
                <button type="button" onclick="removePaper(this)" class="p-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 448 512">
                        <path d="M432 32H312l-9.4-18.7A24 24 0 0 0 281.1 0H166.8a23.72 23.72 0 0 0-21.4 13.3L136 32H16A16 16 0 0 0 0 48v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16zM53.2 467a48 48 0 0 0 47.9 45h245.8a48 48 0 0 0 47.9-45L416 128H32z"/>
                    </svg>
                </button>
            `;
            container.appendChild(newRow);
            paperCount++;
            updateTotalWeight();
        }

        function removePaper(button) {
            const container = document.getElementById('papersContainer');
            if (container.children.length > 1) {
                button.closest('.paper-row').remove();
                updateTotalWeight();
            } else {
                alert('At least one paper is required.');
            }
        }

        function updateTotalWeight() {
            const weightInputs = document.querySelectorAll('input[name*="[weight]"]');
            let total = 0;
            
            weightInputs.forEach(input => {
                const value = parseFloat(input.value) || 0;
                total += value;
            });
            
            document.getElementById('totalWeight').textContent = total;
            
            const display = document.getElementById('totalWeightDisplay');
            if (total === 100) {
                display.classList.remove('text-red-600', 'text-orange-500');
                display.classList.add('text-green-600');
            } else if (total > 100) {
                display.classList.remove('text-green-600', 'text-orange-500');
                display.classList.add('text-red-600');
            } else {
                display.classList.remove('text-green-600', 'text-red-600');
                display.classList.add('text-orange-500');
            }
        }

        // Initialize total weight on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateTotalWeight();
        });

        // Load syllabus topics for the selected subject (edit form)
        async function loadEditSyllabusTopics(subjectId) {
            const topicSelect = document.getElementById('editSyllabusTopicSelect');
            const currentTopicId = '{{ old('syllabus_topic_id', $assessment->syllabus_topic_id) }}';
            
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
                        const selected = topic.id == currentTopicId ? 'selected' : '';
                        const diffBadge = topic.difficulty_level === 'hard' ? '🔴' : (topic.difficulty_level === 'medium' ? '🟡' : '🟢');
                        options += `<option value="${topic.id}" ${selected}>${diffBadge} ${topic.name} (${topic.term || 'N/A'})</option>`;
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
