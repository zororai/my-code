@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                </div>
                Master Timetable
            </h1>
            <p class="text-gray-500 mt-1 ml-13">All class schedules on one sheet - {{ $academicYear }} Term {{ $term }}</p>
        </div>
        <div class="mt-4 md:mt-0 flex flex-wrap gap-3 items-center no-print">
            <!-- Font Size Control -->
            <div class="flex items-center gap-2 bg-white border border-gray-200 rounded-xl px-3 py-2">
                <span class="text-sm text-gray-600 font-medium">Font:</span>
                <button onclick="changeFontSize('decrease')" class="w-8 h-8 flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-700 font-bold transition-colors" title="Decrease font size">
                    A-
                </button>
                <span id="fontSizeDisplay" class="text-sm font-semibold text-gray-700 min-w-[40px] text-center">100%</span>
                <button onclick="changeFontSize('increase')" class="w-8 h-8 flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-700 font-bold transition-colors" title="Increase font size">
                    A+
                </button>
                <button onclick="changeFontSize('reset')" class="px-2 py-1 text-xs bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-600 transition-colors" title="Reset font size">
                    Reset
                </button>
            </div>
            <button onclick="window.print()" class="inline-flex items-center gap-2 bg-gray-600 hover:bg-gray-700 text-white px-5 py-2.5 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print
            </button>
            <a href="{{ route('admin.timetable.index') }}" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Timetables
            </a>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" action="{{ route('admin.timetable.master') }}" class="flex flex-wrap items-end gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Academic Year</label>
                <select name="year" class="px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500">
                    @php $currentYear = date('Y'); @endphp
                    @for($y = $currentYear - 2; $y <= $currentYear + 1; $y++)
                        <option value="{{ $y }}" {{ $academicYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Term</label>
                <select name="term" class="px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="1" {{ $term == 1 ? 'selected' : '' }}>Term 1</option>
                    <option value="2" {{ $term == 2 ? 'selected' : '' }}>Term 2</option>
                    <option value="3" {{ $term == 3 ? 'selected' : '' }}>Term 3</option>
                </select>
            </div>
            <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-xl font-semibold hover:bg-purple-700 transition-colors">
                Filter
            </button>
        </form>
        
        <!-- Subject Search Bar -->
        <div class="border-t border-gray-200 pt-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Search Subject</label>
            <div class="relative max-w-md">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" id="subject-search" 
                       class="block w-full pl-10 pr-10 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-white text-sm"
                       placeholder="Search for a subject (e.g., Mathematics, Building)...">
                <button type="button" id="clear-search" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 hidden">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <p class="text-xs text-gray-500 mt-1" id="search-results-count"></p>
        </div>
    </div>

    @if(count($classTimetables) === 0)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <p class="text-gray-500 font-medium">No timetables found for {{ $academicYear }} Term {{ $term }}</p>
            <p class="text-gray-400 text-sm mt-1">Generate timetables first to view the master schedule</p>
            <a href="{{ route('admin.timetable.create') }}" class="inline-flex items-center gap-2 mt-4 px-6 py-2.5 bg-emerald-600 text-white rounded-xl font-semibold hover:bg-emerald-700 transition-colors">
                Generate Timetable
            </a>
        </div>
    @else
        <!-- Master Timetable Grid -->
        @foreach($days as $day)
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <span class="w-8 h-8 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-lg flex items-center justify-center text-white text-sm font-bold">
                        {{ substr($day, 0, 1) }}
                    </span>
                    {{ $day }}
                </h2>
                
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gradient-to-r from-purple-50 to-indigo-50">
                                    <th class="px-3 py-3 text-left font-semibold text-gray-700 border-b border-gray-200 sticky left-0 bg-purple-50 z-10 min-w-[100px]">
                                        Class
                                    </th>
                                    @foreach($allTimeSlots as $timeKey => $timeSlot)
                                        <th class="px-2 py-2 text-center font-medium text-gray-600 border-b border-gray-200 min-w-[90px]
                                            @if($timeSlot['slot_type'] === 'break') bg-amber-50
                                            @elseif($timeSlot['slot_type'] === 'lunch') bg-orange-50
                                            @elseif($timeSlot['slot_type'] === 'clubs') bg-pink-50
                                            @elseif($timeSlot['slot_type'] === 'special') bg-cyan-50
                                            @endif">
                                            <div class="text-xs">
                                                {{ \Carbon\Carbon::parse($timeSlot['start_time'])->format('H:i') }}
                                            </div>
                                            <div class="text-xs text-gray-400">
                                                {{ \Carbon\Carbon::parse($timeSlot['end_time'])->format('H:i') }}
                                            </div>
                                            @if($timeSlot['slot_type'] === 'break')
                                                <span class="text-xs text-amber-600 font-semibold">BREAK</span>
                                            @elseif($timeSlot['slot_type'] === 'lunch')
                                                <span class="text-xs text-orange-600 font-semibold">LUNCH</span>
                                            @elseif($timeSlot['slot_type'] === 'clubs')
                                                <span class="text-xs text-pink-600 font-semibold">CLUBS</span>
                                            @elseif($timeSlot['slot_type'] === 'special')
                                                <span class="text-xs text-cyan-600 font-semibold">SPECIAL</span>
                                            @endif
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($classTimetables as $classId => $data)
                                    <tr class="hover:bg-gray-50 border-b border-gray-100">
                                        <td class="px-3 py-2 font-semibold text-gray-800 sticky left-0 bg-white z-10 border-r border-gray-100">
                                            {{ $data['class']->class_name ?? $data['class']->name }}
                                        </td>
                                        @foreach($allTimeSlots as $timeKey => $timeSlot)
                                            @php
                                                $daySlots = $data['slots'][$day] ?? collect();
                                                $slot = $daySlots->first(function($s) use ($timeSlot) {
                                                    return $s->start_time == $timeSlot['start_time'] && $s->end_time == $timeSlot['end_time'];
                                                });
                                            @endphp
                                            <td class="px-1 py-1 text-center border-r border-gray-50
                                                @if($timeSlot['slot_type'] === 'break') bg-amber-50
                                                @elseif($timeSlot['slot_type'] === 'lunch') bg-orange-50
                                                @elseif($timeSlot['slot_type'] === 'practical') bg-purple-50
                                                @elseif($timeSlot['slot_type'] === 'clubs') bg-pink-50
                                                @elseif($timeSlot['slot_type'] === 'special') bg-cyan-50
                                                @endif">
                                                @if($slot)
                                                    @if($slot->slot_type === 'break')
                                                        <span class="text-amber-600 text-xs font-medium">Break</span>
                                                    @elseif($slot->slot_type === 'lunch')
                                                        <span class="text-orange-600 text-xs font-medium">Lunch</span>
                                                    @elseif($slot->slot_type === 'practical')
                                                        <div class="bg-purple-100 rounded-lg px-1 py-1 border border-purple-300">
                                                            <div class="text-purple-800 text-xs font-bold">🔧 PRACTICAL</div>
                                                            @if($slot->subject)
                                                                <div class="font-semibold text-purple-900 text-xs" title="{{ $slot->subject->name }}">
                                                                    {{ \Illuminate\Support\Str::limit($slot->subject->name, 12) }}
                                                                </div>
                                                                @if($slot->teacher && $slot->teacher->user)
                                                                    <div class="text-purple-600 text-xs" title="{{ $slot->teacher->user->name }}">
                                                                        {{ \Illuminate\Support\Str::limit($slot->teacher->user->name, 10) }}
                                                                    </div>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    @elseif($slot->slot_type === 'clubs')
                                                        <div class="bg-pink-100 rounded-lg px-1 py-1">
                                                            <span class="text-pink-700 text-xs font-medium">{{ $slot->slot_name ?? 'Clubs' }}</span>
                                                        </div>
                                                    @elseif($slot->slot_type === 'special')
                                                        <div class="bg-cyan-100 rounded-lg px-1 py-1">
                                                            <span class="text-cyan-700 text-xs font-medium">{{ $slot->slot_name ?? 'Special' }}</span>
                                                        </div>
                                                    @elseif($slot->subject)
                                                        <div class="bg-gradient-to-br from-emerald-100 to-teal-100 rounded-lg px-1 py-1">
                                                            <div class="font-semibold text-emerald-800 text-xs" title="{{ $slot->subject->name }}">
                                                                {{ \Illuminate\Support\Str::limit($slot->subject->name, 12) }}
                                                            </div>
                                                            @if($slot->teacher && $slot->teacher->user)
                                                                <div class="text-emerald-600 text-xs" title="{{ $slot->teacher->user->name }}">
                                                                    {{ \Illuminate\Support\Str::limit($slot->teacher->user->name, 10) }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <span class="text-gray-400 text-xs">Free</span>
                                                    @endif
                                                @else
                                                    <span class="text-gray-300 text-xs">-</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Legend -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mt-6">
            <h3 class="font-semibold text-gray-700 mb-3">Legend</h3>
            <div class="flex flex-wrap gap-4">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 bg-gradient-to-br from-emerald-100 to-teal-100 rounded"></div>
                    <span class="text-sm text-gray-600">Subject Lesson</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 bg-purple-100 rounded border border-purple-300"></div>
                    <span class="text-sm text-gray-600">Practicals</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 bg-amber-50 rounded border border-amber-200"></div>
                    <span class="text-sm text-gray-600">Break</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 bg-orange-50 rounded border border-orange-200"></div>
                    <span class="text-sm text-gray-600">Lunch</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 bg-pink-100 rounded border border-pink-200"></div>
                    <span class="text-sm text-gray-600">Clubs</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 bg-cyan-100 rounded border border-cyan-200"></div>
                    <span class="text-sm text-gray-600">Special Activity</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 bg-gray-100 rounded border border-gray-200"></div>
                    <span class="text-sm text-gray-600">Free Period</span>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
    @page {
        size: A4 landscape;
        margin: 3mm;
    }
    
    @media print {
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        
        html, body {
            width: 100%;
            height: auto;
            font-size: 8pt !important;
            overflow: visible !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        .no-print { display: none !important; }
        
        /* Hide website header, navigation, and legend */
        header, nav, .page-header, .legend, .mt-6.flex-wrap { display: none !important; }
        
        /* Hide the main header section with logo */
        .flex.flex-col.md\:flex-row.md\:items-center.md\:justify-between.mb-6 > div:first-child { display: none !important; }
        
        .container-fluid { 
            padding: 0 !important; 
            margin: 0 !important;
            width: 100% !important;
            max-width: none !important;
        }
        
        .rounded-2xl { border-radius: 0 !important; }
        .shadow-sm, .shadow-lg, .shadow-xl { box-shadow: none !important; }
        
        /* Hide filter section, buttons, and legend */
        form, button, a.inline-flex { display: none !important; }
        .bg-white.rounded-2xl.shadow-sm.border.border-gray-100.p-4.mt-6 { display: none !important; }
        
        /* Day headers */
        h2 { 
            font-size: 12pt !important; 
            margin-bottom: 3px !important;
            margin-top: 5px !important;
        }
        
        /* Hide page title and subtitle */
        h1, .text-gray-500.mt-1 { display: none !important; }
        p.text-gray-500 { display: none !important; }
        
        /* Table container - full width */
        .overflow-x-auto {
            overflow: visible !important;
            width: 100% !important;
        }
        
        .bg-white.rounded-2xl.shadow-sm.border.border-gray-100.overflow-hidden {
            border: 1px solid #ccc !important;
            margin-bottom: 8px !important;
        }
        
        /* Table - full width, fixed layout to fit all columns */
        table {
            width: 100% !important;
            table-layout: fixed !important;
            font-size: 8pt !important;
            page-break-inside: auto;
            border-collapse: collapse !important;
        }
        
        /* Table headers */
        th {
            padding: 4px 2px !important;
            font-size: 8pt !important;
            font-weight: bold !important;
            word-wrap: break-word !important;
            white-space: normal !important;
            border: 1px solid #ddd !important;
            background-color: #f5f5f5 !important;
            vertical-align: top !important;
            overflow: hidden !important;
        }
        
        /* Table cells */
        td {
            padding: 4px 2px !important;
            font-size: 8pt !important;
            word-wrap: break-word !important;
            white-space: normal !important;
            border: 1px solid #ddd !important;
            vertical-align: top !important;
            overflow: hidden !important;
        }
        
        /* Class name column */
        th:first-child, td:first-child {
            width: 60px !important;
            min-width: 60px !important;
            font-weight: bold !important;
        }
        
        /* Remove text truncation */
        .truncate {
            overflow: visible !important;
            text-overflow: clip !important;
            white-space: normal !important;
        }
        
        tr { page-break-inside: avoid; page-break-after: auto; }
        
        /* Each day section - one page per day */
        .mb-8 { 
            page-break-after: always !important;
            page-break-inside: avoid !important;
            margin-bottom: 0 !important;
        }
        
        /* Don't break after last day */
        .mb-8:last-of-type {
            page-break-after: auto !important;
        }
        
        /* Hide sticky positioning for print */
        .sticky { position: static !important; }
        
        /* Ensure backgrounds print */
        .bg-gradient-to-br, .bg-gradient-to-r,
        .bg-emerald-100, .bg-teal-100, .bg-amber-50, .bg-orange-50,
        .bg-purple-50, .bg-indigo-50 {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        
        /* Subject cells */
        .bg-gradient-to-br.from-emerald-100.to-teal-100 {
            padding: 2px !important;
        }
        
        .bg-gradient-to-br.from-emerald-100.to-teal-100 .font-semibold,
        .bg-gradient-to-br.from-emerald-100.to-teal-100 .text-xs,
        .font-semibold.text-emerald-800,
        .text-emerald-600 {
            font-size: 7pt !important;
            white-space: normal !important;
            overflow: visible !important;
            text-overflow: clip !important;
        }
        
        /* Time header text */
        .text-xs {
            font-size: 6pt !important;
        }
        
        /* Break/Lunch labels */
        span.text-amber-600, span.text-orange-600 {
            font-size: 7pt !important;
        }
    }
</style>

<script>
    let currentFontSize = 100;
    const timetableContainer = document.querySelector('.container-fluid');
    
    function changeFontSize(action) {
        if (action === 'increase' && currentFontSize < 150) {
            currentFontSize += 10;
        } else if (action === 'decrease' && currentFontSize > 60) {
            currentFontSize -= 10;
        } else if (action === 'reset') {
            currentFontSize = 100;
        }
        
        document.getElementById('fontSizeDisplay').textContent = currentFontSize + '%';
        
        // Apply font size to tables
        const tables = document.querySelectorAll('table');
        tables.forEach(table => {
            table.style.fontSize = (currentFontSize / 100) * 0.875 + 'rem';
        });
        
        // Apply to table cells
        const cells = document.querySelectorAll('th, td');
        cells.forEach(cell => {
            if (currentFontSize >= 100) {
                cell.style.padding = (currentFontSize / 100) * 0.5 + 'rem ' + (currentFontSize / 100) * 0.25 + 'rem';
            }
        });
    }
    
    // Subject Search Functionality
    const subjectSearchInput = document.getElementById('subject-search');
    const clearSearchBtn = document.getElementById('clear-search');
    const searchResultsCount = document.getElementById('search-results-count');
    
    if (subjectSearchInput) {
        subjectSearchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            
            // Show/hide clear button
            if (searchTerm) {
                clearSearchBtn.classList.remove('hidden');
            } else {
                clearSearchBtn.classList.add('hidden');
            }
            
            // Get all subject cells
            const subjectCells = document.querySelectorAll('.bg-gradient-to-br.from-emerald-100.to-teal-100');
            let matchCount = 0;
            
            // Reset all cells
            subjectCells.forEach(cell => {
                cell.style.outline = '';
                cell.style.outlineOffset = '';
                cell.style.backgroundColor = '';
                cell.style.transform = '';
                cell.style.transition = '';
            });
            
            if (searchTerm) {
                subjectCells.forEach(cell => {
                    const subjectNameElement = cell.querySelector('.font-semibold.text-emerald-800');
                    if (subjectNameElement) {
                        const subjectName = subjectNameElement.textContent.toLowerCase();
                        const fullSubjectName = subjectNameElement.getAttribute('title')?.toLowerCase() || subjectName;
                        
                        if (subjectName.includes(searchTerm) || fullSubjectName.includes(searchTerm)) {
                            // Highlight matching cells
                            cell.style.outline = '3px solid #f59e0b';
                            cell.style.outlineOffset = '2px';
                            cell.style.backgroundColor = '#fef3c7';
                            cell.style.transform = 'scale(1.05)';
                            cell.style.transition = 'all 0.2s ease';
                            matchCount++;
                            
                            // Scroll first match into view
                            if (matchCount === 1) {
                                cell.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            }
                        }
                    }
                });
                
                // Update results count
                if (matchCount > 0) {
                    searchResultsCount.textContent = `Found ${matchCount} occurrence(s) of "${this.value}"`;
                    searchResultsCount.classList.remove('text-gray-500');
                    searchResultsCount.classList.add('text-green-600', 'font-medium');
                } else {
                    searchResultsCount.textContent = `No results found for "${this.value}"`;
                    searchResultsCount.classList.remove('text-green-600', 'font-medium');
                    searchResultsCount.classList.add('text-red-500');
                }
            } else {
                searchResultsCount.textContent = '';
            }
        });
        
        // Clear search functionality
        clearSearchBtn.addEventListener('click', function() {
            subjectSearchInput.value = '';
            subjectSearchInput.dispatchEvent(new Event('input'));
            subjectSearchInput.focus();
        });
    }
</script>
@endsection
