@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-6">
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center mb-6">
            <a href="{{ route('admin.library.index') }}" class="text-gray-500 hover:text-gray-700 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Issue Book</h1>
                <p class="text-gray-600 mt-1">Issue a book to a student or teacher</p>
            </div>
        </div>

        @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6" role="alert">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    Book Issue Form
                </h2>
            </div>

            <form action="{{ route('admin.library.store') }}" method="POST" class="p-6 space-y-6" x-data="borrowerForm()">
                @csrf

                <!-- Borrower Type Selection -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                        Issue Book To <span class="text-red-500">*</span>
                    </label>
                    <div class="flex space-x-4">
                        <label class="flex items-center p-4 border rounded-lg cursor-pointer transition-all"
                            :class="borrowerType === 'student' ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:bg-gray-50'">
                            <input type="radio" name="borrower_type" value="student" x-model="borrowerType" class="sr-only">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3"
                                    :class="borrowerType === 'student' ? 'bg-blue-600' : 'bg-gray-400'">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium" :class="borrowerType === 'student' ? 'text-blue-700' : 'text-gray-700'">Student</p>
                                    <p class="text-sm text-gray-500">Issue to a student</p>
                                </div>
                            </div>
                        </label>
                        <label class="flex items-center p-4 border rounded-lg cursor-pointer transition-all"
                            :class="borrowerType === 'teacher' ? 'border-green-500 bg-green-50' : 'border-gray-300 hover:bg-gray-50'">
                            <input type="radio" name="borrower_type" value="teacher" x-model="borrowerType" class="sr-only">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3"
                                    :class="borrowerType === 'teacher' ? 'bg-green-600' : 'bg-gray-400'">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium" :class="borrowerType === 'teacher' ? 'text-green-700' : 'text-gray-700'">Teacher</p>
                                    <p class="text-sm text-gray-500">Issue to a teacher</p>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Student Search (shown when borrower_type is student) -->
                <div x-show="borrowerType === 'student'" x-data="studentSearch()" class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                        Search Student <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="text" 
                            x-model="searchQuery"
                            @input.debounce.300ms="searchStudents()"
                            @focus="showDropdown = true"
                            placeholder="Type student name or roll number..."
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        
                        <input type="hidden" name="student_id" x-model="selectedStudentId" required>

                        <!-- Dropdown Results -->
                        <div x-show="showDropdown && students.length > 0" 
                            @click.away="showDropdown = false"
                            class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                            <template x-for="student in students" :key="student.id">
                                <div @click="selectStudent(student)" 
                                    class="px-4 py-3 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0">
                                    <p class="font-medium text-gray-900" x-text="student.name"></p>
                                    <p class="text-sm text-gray-500">
                                        <span x-text="student.class"></span> • 
                                        <span>Roll: </span><span x-text="student.roll_number"></span>
                                    </p>
                                </div>
                            </template>
                        </div>

                        <!-- No results message -->
                        <div x-show="showDropdown && searchQuery.length > 2 && students.length === 0 && !loading" 
                            class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg p-4 text-center text-gray-500">
                            No students found
                        </div>
                    </div>

                    <!-- Selected Student Display -->
                    <div x-show="selectedStudent" class="mt-3 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
                                    <span class="text-white font-medium text-sm" x-text="selectedStudent ? selectedStudent.name.substring(0,2).toUpperCase() : ''"></span>
                                </div>
                                <div class="ml-3">
                                    <p class="font-medium text-gray-900" x-text="selectedStudent ? selectedStudent.name : ''"></p>
                                    <p class="text-sm text-gray-600">
                                        <span x-text="selectedStudent ? selectedStudent.class : ''"></span> • 
                                        <span>Roll: </span><span x-text="selectedStudent ? selectedStudent.roll_number : ''"></span>
                                    </p>
                                </div>
                            </div>
                            <button type="button" @click="clearSelection()" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Teacher Search (shown when borrower_type is teacher) -->
                <div x-show="borrowerType === 'teacher'" x-data="teacherSearch()" class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                        Search Teacher <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="text" 
                            x-model="searchQuery"
                            @input.debounce.300ms="searchTeachers()"
                            @focus="showDropdown = true"
                            placeholder="Type teacher name..."
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        
                        <input type="hidden" name="teacher_id" x-model="selectedTeacherId">

                        <!-- Dropdown Results -->
                        <div x-show="showDropdown && teachers.length > 0" 
                            @click.away="showDropdown = false"
                            class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                            <template x-for="teacher in teachers" :key="teacher.id">
                                <div @click="selectTeacher(teacher)" 
                                    class="px-4 py-3 hover:bg-green-50 cursor-pointer border-b border-gray-100 last:border-b-0">
                                    <p class="font-medium text-gray-900" x-text="teacher.name"></p>
                                    <p class="text-sm text-gray-500">
                                        <span>Phone: </span><span x-text="teacher.phone"></span>
                                    </p>
                                </div>
                            </template>
                        </div>

                        <!-- No results message -->
                        <div x-show="showDropdown && searchQuery.length > 2 && teachers.length === 0 && !loading" 
                            class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg p-4 text-center text-gray-500">
                            No teachers found
                        </div>
                    </div>

                    <!-- Selected Teacher Display -->
                    <div x-show="selectedTeacher" class="mt-3 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center">
                                    <span class="text-white font-medium text-sm" x-text="selectedTeacher ? selectedTeacher.name.substring(0,2).toUpperCase() : ''"></span>
                                </div>
                                <div class="ml-3">
                                    <p class="font-medium text-gray-900" x-text="selectedTeacher ? selectedTeacher.name : ''"></p>
                                    <p class="text-sm text-gray-600">
                                        <span>Phone: </span><span x-text="selectedTeacher ? selectedTeacher.phone : ''"></span>
                                    </p>
                                </div>
                            </div>
                            <button type="button" @click="clearSelection()" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Book Copy Search by ISBN -->
                <div x-data="bookCopySearch()" class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                        Search Book by ISBN <span class="text-red-500">*</span>
                    </label>
                    <p class="text-xs text-gray-500 mb-2">Search for a specific book copy by its unique ISBN number</p>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                            </svg>
                        </div>
                        <input type="text" 
                            x-model="copySearchQuery"
                            @input.debounce.300ms="searchCopies()"
                            @focus="showCopyDropdown = true"
                            placeholder="Enter ISBN number or book title..."
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        
                        <input type="hidden" name="book_id" x-model="selectedBookId">
                        <input type="hidden" name="book_copy_id" x-model="selectedCopyId">
                        <input type="hidden" name="book_title" x-model="selectedBookTitle">
                        <input type="hidden" name="book_number" x-model="selectedBookNumber">
                        <input type="hidden" name="copy_isbn" x-model="selectedCopyIsbn">

                        <!-- Dropdown Results -->
                        <div x-show="showCopyDropdown && copies.length > 0" 
                            @click.away="showCopyDropdown = false"
                            class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                            <template x-for="copy in copies" :key="copy.id">
                                <div @click="selectCopy(copy)" 
                                    class="px-4 py-3 hover:bg-green-50 cursor-pointer border-b border-gray-100 last:border-b-0">
                                    <div class="flex items-center justify-between">
                                        <p class="font-medium text-gray-900" x-text="copy.title"></p>
                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Available</span>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1">
                                        <span class="font-mono bg-gray-100 px-1 rounded" x-text="'ISBN: ' + copy.isbn"></span>
                                    </p>
                                    <p class="text-xs text-gray-400 mt-1">
                                        <span x-text="'Copy #: ' + copy.copy_number"></span> • 
                                        <span x-text="copy.author"></span> •
                                        <span x-text="'Condition: ' + copy.condition"></span>
                                    </p>
                                </div>
                            </template>
                        </div>

                        <!-- No results message -->
                        <div x-show="showCopyDropdown && copySearchQuery.length > 2 && copies.length === 0 && !copyLoading" 
                            class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg p-4 text-center text-gray-500">
                            No available book copies found with that ISBN. <a href="{{ route('admin.library.books') }}" class="text-blue-600 hover:underline">Manage books</a>
                        </div>
                    </div>

                    <!-- Selected Book Copy Display -->
                    <div x-show="selectedCopy" class="mt-3 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-green-600 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="font-medium text-gray-900" x-text="selectedCopy ? selectedCopy.title : ''"></p>
                                    <p class="text-sm text-green-700 font-mono">
                                        <span>ISBN: </span><span x-text="selectedCopy ? selectedCopy.isbn : ''"></span>
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        <span>Copy #: </span><span x-text="selectedCopy ? selectedCopy.copy_number : ''"></span> • 
                                        <span x-text="selectedCopy ? selectedCopy.condition : ''"></span>
                                    </p>
                                </div>
                            </div>
                            <button type="button" @click="clearCopySelection()" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Issue Date and Due Date -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label for="issue_date" class="block text-sm font-medium text-gray-700">
                            Issue Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="issue_date" name="issue_date" 
                            value="{{ old('issue_date', date('Y-m-d')) }}" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="space-y-2">
                        <label for="due_date" class="block text-sm font-medium text-gray-700">
                            Due Date
                        </label>
                        <input type="date" id="due_date" name="due_date" value="{{ old('due_date') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <!-- Notes -->
                <div class="space-y-2">
                    <label for="notes" class="block text-sm font-medium text-gray-700">
                        Notes (Optional)
                    </label>
                    <textarea id="notes" name="notes" rows="3" 
                        placeholder="Any additional notes..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('notes') }}</textarea>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-end space-x-4 pt-4 border-t border-gray-200">
                    <a href="{{ route('admin.library.index') }}" 
                        class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition-colors">
                        Cancel
                    </a>
                    <button type="submit" 
                        class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                        Issue Book
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function borrowerForm() {
    return {
        borrowerType: 'student'
    }
}

function studentSearch() {
    return {
        searchQuery: '',
        students: [],
        selectedStudent: null,
        selectedStudentId: '',
        showDropdown: false,
        loading: false,

        async searchStudents() {
            if (this.searchQuery.length < 2) {
                this.students = [];
                return;
            }

            this.loading = true;
            try {
                const response = await fetch(`{{ route('admin.library.search-students') }}?q=${encodeURIComponent(this.searchQuery)}`);
                this.students = await response.json();
            } catch (error) {
                console.error('Error searching students:', error);
                this.students = [];
            }
            this.loading = false;
        },

        selectStudent(student) {
            this.selectedStudent = student;
            this.selectedStudentId = student.id;
            this.searchQuery = student.name;
            this.showDropdown = false;
        },

        clearSelection() {
            this.selectedStudent = null;
            this.selectedStudentId = '';
            this.searchQuery = '';
        }
    }
}

function bookCopySearch() {
    return {
        copySearchQuery: '',
        copies: [],
        selectedCopy: null,
        selectedCopyId: '',
        selectedBookId: '',
        selectedBookTitle: '',
        selectedBookNumber: '',
        selectedCopyIsbn: '',
        showCopyDropdown: false,
        copyLoading: false,

        async searchCopies() {
            if (this.copySearchQuery.length < 2) {
                this.copies = [];
                return;
            }

            this.copyLoading = true;
            try {
                const response = await fetch(`{{ route('admin.library.search-copies') }}?q=${encodeURIComponent(this.copySearchQuery)}`);
                this.copies = await response.json();
            } catch (error) {
                console.error('Error searching book copies:', error);
                this.copies = [];
            }
            this.copyLoading = false;
        },

        selectCopy(copy) {
            this.selectedCopy = copy;
            this.selectedCopyId = copy.id;
            this.selectedBookId = copy.book_id;
            this.selectedBookTitle = copy.title;
            this.selectedBookNumber = copy.book_number;
            this.selectedCopyIsbn = copy.isbn;
            this.copySearchQuery = copy.isbn + ' - ' + copy.title;
            this.showCopyDropdown = false;
        },

        clearCopySelection() {
            this.selectedCopy = null;
            this.selectedCopyId = '';
            this.selectedBookId = '';
            this.selectedBookTitle = '';
            this.selectedBookNumber = '';
            this.selectedCopyIsbn = '';
            this.copySearchQuery = '';
        }
    }
}

function teacherSearch() {
    return {
        searchQuery: '',
        teachers: [],
        selectedTeacher: null,
        selectedTeacherId: '',
        showDropdown: false,
        loading: false,

        async searchTeachers() {
            if (this.searchQuery.length < 2) {
                this.teachers = [];
                return;
            }

            this.loading = true;
            try {
                const response = await fetch(`{{ route('admin.library.search-teachers') }}?q=${encodeURIComponent(this.searchQuery)}`);
                this.teachers = await response.json();
            } catch (error) {
                console.error('Error searching teachers:', error);
                this.teachers = [];
            }
            this.loading = false;
        },

        selectTeacher(teacher) {
            this.selectedTeacher = teacher;
            this.selectedTeacherId = teacher.id;
            this.searchQuery = teacher.name;
            this.showDropdown = false;
        },

        clearSelection() {
            this.selectedTeacher = null;
            this.selectedTeacherId = '';
            this.searchQuery = '';
        }
    }
}
</script>
@endsection
