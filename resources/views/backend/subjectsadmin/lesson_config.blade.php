@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <a href="{{ route('admin.subjects.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <h1 class="text-3xl font-bold text-gray-900">Weekly Lesson Configuration</h1>
                </div>
                <p class="mt-1 text-sm text-gray-500 ml-8">Configure how many lessons of each type every subject has per week</p>
            </div>
            <a href="{{ route('admin.subjects.index') }}" class="inline-flex items-center px-5 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-semibold rounded-xl transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Subjects
            </a>
        </div>

        {{-- Legend --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 mb-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Lesson Types</h3>
            <div class="flex flex-wrap gap-4 text-sm">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                    <span class="font-medium text-blue-700">Single</span>
                    <span class="text-gray-500">— 1 period per lesson</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-indigo-500"></span>
                    <span class="font-medium text-indigo-700">Double</span>
                    <span class="text-gray-500">— 2 periods per lesson</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-purple-500"></span>
                    <span class="font-medium text-purple-700">Triple</span>
                    <span class="text-gray-500">— 3 periods per lesson</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                    <span class="font-medium text-rose-700">Quad</span>
                    <span class="text-gray-500">— 4 periods per lesson</span>
                </div>
                <div class="ml-auto flex items-center gap-2 text-gray-500">
                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Total Periods/wk = (Single×1) + (Double×2) + (Triple×3) + (Quad×4)
                </div>
            </div>
        </div>

        {{-- Success message --}}
        @if(session('success'))
            <div class="mb-5 bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded-xl flex items-center gap-2">
                <svg class="w-5 h-5 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Search --}}
        <div class="flex items-center justify-between mb-4">
            <p class="text-sm text-gray-500">{{ $allSubjects->count() }} subjects total</p>
            <div class="relative">
                <input type="text" id="subjectSearch" placeholder="Search subjects…"
                    class="pl-9 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 w-64">
                <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 0 5 11a6 6 0 0 0 12 0z"/>
                </svg>
            </div>
        </div>

        {{-- Table --}}
        @if($allSubjects->count() > 0)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">#</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Subject</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Code</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Class(es)</th>
                            <th class="px-5 py-3 text-center text-xs font-semibold text-blue-600 uppercase tracking-wider">
                                <div>Single</div>
                                <div class="text-gray-400 font-normal normal-case">1 period</div>
                            </th>
                            <th class="px-5 py-3 text-center text-xs font-semibold text-indigo-600 uppercase tracking-wider">
                                <div>Double</div>
                                <div class="text-gray-400 font-normal normal-case">2 periods</div>
                            </th>
                            <th class="px-5 py-3 text-center text-xs font-semibold text-purple-600 uppercase tracking-wider">
                                <div>Triple</div>
                                <div class="text-gray-400 font-normal normal-case">3 periods</div>
                            </th>
                            <th class="px-5 py-3 text-center text-xs font-semibold text-rose-600 uppercase tracking-wider">
                                <div>Quad</div>
                                <div class="text-gray-400 font-normal normal-case">4 periods</div>
                            </th>
                            <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Total / wk</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100" id="lessonConfigBody">
                        @foreach($allSubjects as $i => $subject)
                        <tr class="hover:bg-purple-50 transition-colors lesson-row" data-name="{{ strtolower($subject->name) }}">
                            <td class="px-5 py-3 text-sm text-gray-400 whitespace-nowrap">{{ $i + 1 }}</td>
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
                            <form method="POST" action="{{ route('admin.subjects.update-lesson-config', $subject->id) }}" class="contents">
                                @csrf
                                @method('PUT')
                                <td class="px-5 py-3 text-center">
                                    <input type="number" name="single_lessons_per_week"
                                        value="{{ $subject->single_lessons_per_week }}"
                                        min="0" max="20"
                                        class="lesson-input w-16 text-center border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400">
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <input type="number" name="double_lessons_per_week"
                                        value="{{ $subject->double_lessons_per_week }}"
                                        min="0" max="10"
                                        class="lesson-input w-16 text-center border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400">
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <input type="number" name="triple_lessons_per_week"
                                        value="{{ $subject->triple_lessons_per_week }}"
                                        min="0" max="5"
                                        class="lesson-input w-16 text-center border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-purple-400">
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <input type="number" name="quad_lessons_per_week"
                                        value="{{ $subject->quad_lessons_per_week }}"
                                        min="0" max="5"
                                        class="lesson-input w-16 text-center border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-rose-400">
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="periods-total inline-flex items-center justify-center min-w-[2.5rem] px-2.5 py-1 rounded-full text-xs font-bold {{ $subject->periods_per_week > 0 ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                        {{ $subject->periods_per_week }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <button type="submit"
                                        class="save-btn hidden inline-flex items-center px-4 py-1.5 bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Save
                                    </button>
                                    <span class="no-change-msg text-xs text-gray-400">—</span>
                                </td>
                            </form>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @else
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253"/>
                    </svg>
                </div>
                <p class="text-gray-900 font-semibold">No subjects found</p>
                <p class="text-gray-500 text-sm mt-1">Add subjects to classes first before configuring lessons.</p>
                <a href="{{ route('admin.subjects.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700">
                    Add Subject
                </a>
            </div>
        @endif

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

    // Per-row: show Save button on change, live-recalc total
    document.querySelectorAll('#lessonConfigBody .lesson-row').forEach(function (row) {
        const inputs      = row.querySelectorAll('.lesson-input');
        const saveBtn     = row.querySelector('.save-btn');
        const noChangeMsg = row.querySelector('.no-change-msg');
        const totalSpan   = row.querySelector('.periods-total');
        const origValues  = Array.from(inputs).map(i => i.value);

        function recalc() {
            const vals  = Array.from(inputs).map(i => parseInt(i.value) || 0);
            const total = vals[0] * 1 + vals[1] * 2 + vals[2] * 3 + vals[3] * 4;
            totalSpan.textContent = total;
            totalSpan.className = 'periods-total inline-flex items-center justify-center min-w-[2.5rem] px-2.5 py-1 rounded-full text-xs font-bold '
                + (total > 0 ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500');

            const changed = vals.some((v, idx) => String(v) !== origValues[idx]);
            saveBtn.classList.toggle('hidden', !changed);
            noChangeMsg.classList.toggle('hidden', changed);
        }

        inputs.forEach(i => i.addEventListener('input', recalc));
    });
</script>
@endsection
