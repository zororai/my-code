@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Subjects Management</h1>
                    <p class="mt-1 text-sm text-gray-500">Manage academic subjects by class</p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('admin.onboard-subjects.index') }}" class="inline-flex items-center px-5 py-3 bg-green-600 text-white text-sm font-semibold rounded-xl shadow-lg hover:bg-green-700 hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        Onboard Subject
                    </a>
                    <a href="{{ route('admin.subjects.create') }}" class="inline-flex items-center px-5 py-3 bg-blue-600 text-white text-sm font-semibold rounded-xl shadow-lg hover:bg-blue-700 hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Add New Subject
                    </a>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                <div class="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-blue-500">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Subjects</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $totalSubjects }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-indigo-500">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-indigo-100 text-indigo-600">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Classes</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $classes->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Classes Grid -->
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Classes</h2>
                <p class="text-sm text-gray-500 mb-6">Click on a class card to view and manage its subjects</p>
            </div>

            @if($classes->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($classes as $class)
                        <a href="{{ route('admin.subjects.byClass', $class->id) }}" class="group bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300">
                            <div class="h-3 bg-gradient-to-r from-blue-500 to-indigo-600"></div>
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-100 to-indigo-100 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                    </div>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-blue-100 text-blue-800">
                                        {{ $class->subjects->count() }} subjects
                                    </span>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 group-hover:text-blue-600 transition-colors">{{ $class->class_name }}</h3>
                                <div class="mt-4 flex items-center text-sm text-gray-500">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                    <span class="group-hover:text-blue-600 transition-colors">View subjects</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
                    <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <p class="text-gray-900 text-lg font-semibold">No classes found</p>
                    <p class="text-gray-500 text-sm mt-1">Create classes first to manage subjects</p>
                </div>
            @endif

            {{-- ── Weekly Lesson Configuration ── --}}
            <div id="lesson-config" class="mt-12">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">Weekly Lesson Configuration</h2>
                        <p class="text-sm text-gray-500 mt-1">Configure how many lessons of each type every subject has per week</p>
                    </div>
                    <div class="relative">
                        <input type="text" id="subjectSearch" placeholder="Search subjects…"
                            class="pl-9 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 w-56">
                        <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 0 5 11a6 6 0 0 0 12 0z"/>
                        </svg>
                    </div>
                </div>

                @if(session('success'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
                        {{ session('success') }}
                    </div>
                @endif

                @if($allSubjects->count() > 0)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200" id="lessonConfigTable">
                            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Subject</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Code</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Class(es)</th>
                                    <th class="px-5 py-3 text-center text-xs font-semibold text-blue-600 uppercase tracking-wider" title="1 period each">Single</th>
                                    <th class="px-5 py-3 text-center text-xs font-semibold text-indigo-600 uppercase tracking-wider" title="2 periods each">Double</th>
                                    <th class="px-5 py-3 text-center text-xs font-semibold text-purple-600 uppercase tracking-wider" title="3 periods each">Triple</th>
                                    <th class="px-5 py-3 text-center text-xs font-semibold text-rose-600 uppercase tracking-wider" title="4 periods each">Quad</th>
                                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Total Periods/wk</th>
                                    <th class="px-5 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100" id="lessonConfigBody">
                                @foreach($allSubjects as $subject)
                                <tr class="hover:bg-gray-50 transition-colors lesson-row" data-name="{{ strtolower($subject->name) }}">
                                    <td class="px-5 py-3 whitespace-nowrap">
                                        <div class="font-semibold text-gray-900 text-sm">{{ $subject->name }}</div>
                                    </td>
                                    <td class="px-5 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-medium bg-gray-100 text-gray-700">{{ $subject->subject_code }}</span>
                                    </td>
                                    <td class="px-5 py-3">
                                        <div class="flex flex-wrap gap-1">
                                            @forelse($subject->grades as $grade)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">{{ $grade->class_name }}</span>
                                            @empty
                                                <span class="text-xs text-gray-400 italic">—</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    {{-- Inline edit form --}}
                                    <form method="POST" action="{{ route('admin.subjects.lesson-config', $subject->id) }}" class="contents">
                                        @csrf
                                        @method('PUT')
                                        <td class="px-5 py-3 text-center">
                                            <input type="number" name="single_lessons_per_week" value="{{ $subject->single_lessons_per_week }}"
                                                min="0" max="20"
                                                class="lesson-input w-14 text-center border border-gray-300 rounded-md px-1 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                                                title="Single lessons (1 period) per week">
                                        </td>
                                        <td class="px-5 py-3 text-center">
                                            <input type="number" name="double_lessons_per_week" value="{{ $subject->double_lessons_per_week }}"
                                                min="0" max="10"
                                                class="lesson-input w-14 text-center border border-gray-300 rounded-md px-1 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                                title="Double lessons (2 periods) per week">
                                        </td>
                                        <td class="px-5 py-3 text-center">
                                            <input type="number" name="triple_lessons_per_week" value="{{ $subject->triple_lessons_per_week }}"
                                                min="0" max="5"
                                                class="lesson-input w-14 text-center border border-gray-300 rounded-md px-1 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400"
                                                title="Triple lessons (3 periods) per week">
                                        </td>
                                        <td class="px-5 py-3 text-center">
                                            <input type="number" name="quad_lessons_per_week" value="{{ $subject->quad_lessons_per_week }}"
                                                min="0" max="5"
                                                class="lesson-input w-14 text-center border border-gray-300 rounded-md px-1 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-rose-400"
                                                title="Quad lessons (4 periods) per week">
                                        </td>
                                        <td class="px-5 py-3 text-center">
                                            <span class="periods-total inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $subject->periods_per_week > 0 ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                                {{ $subject->periods_per_week }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-3 text-right">
                                            <button type="submit"
                                                class="save-btn hidden inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg transition-colors">
                                                Save
                                            </button>
                                            <span class="no-change-msg text-xs text-gray-400 italic">—</span>
                                        </td>
                                    </form>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @else
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-10 text-center text-gray-500">
                        No subjects found. Add subjects to classes first.
                    </div>
                @endif
            </div>

        </div>
    </div>

    <script>
        // Search filter
        document.getElementById('subjectSearch').addEventListener('input', function () {
            const q = this.value.toLowerCase();
            document.querySelectorAll('#lessonConfigBody .lesson-row').forEach(function (row) {
                row.style.display = row.dataset.name.includes(q) ? '' : 'none';
            });
        });

        // Show Save button and update live total when any input changes
        document.querySelectorAll('#lessonConfigBody .lesson-row').forEach(function (row) {
            const inputs = row.querySelectorAll('.lesson-input');
            const saveBtn = row.querySelector('.save-btn');
            const noChangeMsg = row.querySelector('.no-change-msg');
            const totalSpan = row.querySelector('.periods-total');

            const origValues = Array.from(inputs).map(i => i.value);

            function recalc() {
                const vals = Array.from(inputs).map(i => parseInt(i.value) || 0);
                const total = vals[0] * 1 + vals[1] * 2 + vals[2] * 3 + vals[3] * 4;
                totalSpan.textContent = total;
                totalSpan.className = 'periods-total inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold ' +
                    (total > 0 ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500');

                const changed = vals.some((v, i) => String(v) !== origValues[i]);
                saveBtn.classList.toggle('hidden', !changed);
                noChangeMsg.classList.toggle('hidden', changed);
            }

            inputs.forEach(function (input) {
                input.addEventListener('input', recalc);
            });
        });
    </script>
@endsection
