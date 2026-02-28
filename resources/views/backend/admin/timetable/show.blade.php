@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                {{ $class->class_name }} Timetable
            </h1>
            <p class="text-gray-500 mt-1 ml-13">Weekly schedule for Form {{ $class->class_numeric }}</p>
        </div>
        <div class="mt-4 md:mt-0 flex items-center gap-3 no-print">
            <button onclick="window.print()" class="inline-flex items-center gap-2 bg-gray-600 text-white px-4 py-2 rounded-xl font-semibold hover:bg-gray-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print
            </button>
            <a href="{{ route('admin.timetable.edit', $class->id) }}" 
               class="inline-flex items-center gap-2 bg-amber-500 text-white px-4 py-2 rounded-xl font-semibold hover:bg-amber-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit
            </a>
            <a href="{{ route('admin.timetable.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-r-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @include('backend.admin.timetable._alerts')

    @if(session('warning'))
        <div class="bg-amber-100 border-l-4 border-amber-500 text-amber-700 p-4 mb-6 rounded-r-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                {{ session('warning') }}
            </div>
        </div>
    @endif

    <!-- Academic Period Info -->
    @if($settings)
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
        <p class="text-sm text-blue-800">
            <span class="font-semibold">Viewing:</span> {{ $settings->academic_year }} - Term {{ $settings->term }}
        </p>
    </div>
    @endif

    <!-- Settings Summary -->
    @if($settings)
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Start</p>
            <p class="text-lg font-bold text-gray-800">{{ \Carbon\Carbon::parse($settings->start_time)->format('H:i') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Break</p>
            <p class="text-lg font-bold text-amber-600">{{ \Carbon\Carbon::parse($settings->break_start)->format('H:i') }} - {{ \Carbon\Carbon::parse($settings->break_end)->format('H:i') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Lunch</p>
            <p class="text-lg font-bold text-orange-600">{{ \Carbon\Carbon::parse($settings->lunch_start)->format('H:i') }} - {{ \Carbon\Carbon::parse($settings->lunch_end)->format('H:i') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide">End</p>
            <p class="text-lg font-bold text-gray-800">{{ \Carbon\Carbon::parse($settings->end_time)->format('H:i') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Duration</p>
            <p class="text-lg font-bold text-indigo-600">{{ $settings->subject_duration }} min</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Subjects</p>
            <p class="text-lg font-bold text-emerald-600">{{ $class->subjects->count() }}</p>
        </div>
    </div>
    @endif

    <!-- Timetable Grid -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[800px]">
                <thead>
                    <tr style="background: linear-gradient(to right, #10b981, #14b8a6) !important;">
                        <th class="px-4 py-4 text-left text-xs font-semibold uppercase tracking-wider w-24" style="color: #616161ff !important; background-color: transparent !important;">Time</th>
                        <th class="px-4 py-4 text-center text-xs font-semibold uppercase tracking-wider" style="color: #616161ff !important; background-color: transparent !important;">Monday</th>
                        <th class="px-4 py-4 text-center text-xs font-semibold uppercase tracking-wider" style="color: #616161ff !important; background-color: transparent !important;">Tuesday</th>
                        <th class="px-4 py-4 text-center text-xs font-semibold uppercase tracking-wider" style="color: #616161ff !important; background-color: transparent !important;">Wednesday</th>
                        <th class="px-4 py-4 text-center text-xs font-semibold uppercase tracking-wider" style="color: #616161ff !important; background-color: transparent !important;">Thursday</th>
                        <th class="px-4 py-4 text-center text-xs font-semibold uppercase tracking-wider" style="color: #616161ff !important; background-color: transparent !important;">Friday</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @php
                        $daysArray = isset($days) && is_array($days) ? $days : ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                        $maxSlots = 0;
                        foreach($daysArray as $day) {
                            if(isset($timetable[$day]) && $timetable[$day]->count() > $maxSlots) {
                                $maxSlots = $timetable[$day]->count();
                            }
                        }
                        
                        // Pre-process timetable for rendering rules
                        // Track: merged lessons, slots to skip, slots to show as free period
                        $renderData = [];
                        foreach($daysArray as $day) {
                            $renderData[$day] = [];
                            $subjectsSeenInSegment = []; // Reset after break/lunch
                            $skipNext = 0; // How many slots to skip (for merged lessons)
                            
                            if (!isset($timetable[$day])) continue;
                            
                            $slots = $timetable[$day]->values();
                            for ($idx = 0; $idx < $slots->count(); $idx++) {
                                $slot = $slots[$idx];
                                
                                // Break/Lunch resets tracking
                                if (in_array($slot->slot_type, ['break', 'lunch'])) {
                                    $subjectsSeenInSegment = [];
                                    $renderData[$day][$idx] = [
                                        'action' => 'show',
                                        'type' => $slot->slot_type,
                                        'rowspan' => 1,
                                    ];
                                    continue;
                                }
                                
                                // Subject slot
                                if ($slot->slot_type === 'subject' && $slot->subject_id) {
                                    $subjectId = $slot->subject_id;
                                    $teacherId = $slot->teacher_id;
                                    
                                    // Check if this subject already appeared in this segment (non-continuous)
                                    if (isset($subjectsSeenInSegment[$subjectId])) {
                                        // Same subject appeared before but not continuous - show as FREE
                                        $renderData[$day][$idx] = [
                                            'action' => 'free',
                                            'type' => 'free_repeated',
                                            'rowspan' => 1,
                                        ];
                                        continue;
                                    }
                                    
                                    // Check for back-to-back merging (same subject, same teacher, continuous time)
                                    $mergeCount = 1;
                                    $endTime = $slot->end_time;
                                    
                                    // Look ahead for continuous lessons
                                    for ($j = $idx + 1; $j < $slots->count(); $j++) {
                                        $nextSlot = $slots[$j];
                                        
                                        // If break/lunch, stop merging
                                        if (in_array($nextSlot->slot_type, ['break', 'lunch'])) {
                                            break;
                                        }
                                        
                                        // Check if same subject, same teacher, and continuous time
                                        if ($nextSlot->subject_id === $subjectId && 
                                            $nextSlot->teacher_id === $teacherId &&
                                            $nextSlot->start_time === $endTime) {
                                            $mergeCount++;
                                            $endTime = $nextSlot->end_time;
                                        } else {
                                            break;
                                        }
                                    }
                                    
                                    // Mark this slot to show with rowspan
                                    $renderData[$day][$idx] = [
                                        'action' => 'show',
                                        'type' => 'subject',
                                        'rowspan' => $mergeCount,
                                        'merged_end_time' => $endTime,
                                    ];
                                    
                                    // Mark next slots as skipped (merged)
                                    for ($k = 1; $k < $mergeCount; $k++) {
                                        $renderData[$day][$idx + $k] = [
                                            'action' => 'skip',
                                            'type' => 'merged',
                                            'rowspan' => 0,
                                        ];
                                    }
                                    
                                    // Mark this subject as seen in segment
                                    $subjectsSeenInSegment[$subjectId] = true;
                                    
                                    // Skip the merged slots in the loop
                                    $idx += ($mergeCount - 1);
                                } else {
                                    // Empty subject slot or free period
                                    $renderData[$day][$idx] = [
                                        'action' => 'show',
                                        'type' => 'free',
                                        'rowspan' => 1,
                                    ];
                                }
                            }
                        }
                    @endphp

                    @if($maxSlots == 0)
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <p class="text-gray-500 text-lg font-medium">No timetable generated yet</p>
                                    <p class="text-gray-400 text-sm mt-2">Click "Edit" to create a timetable for this class</p>
                                </div>
                            </td>
                        </tr>
                    @endif

                    @for($i = 0; $i < $maxSlots; $i++)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">
                                @if(isset($timetable['Monday'][$i]))
                                    {{ \Carbon\Carbon::parse($timetable['Monday'][$i]->start_time)->format('H:i') }}
                                    <br>
                                    <span class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($timetable['Monday'][$i]->end_time)->format('H:i') }}</span>
                                @endif
                            </td>
                            @foreach($daysArray as $day)
                                @php
                                    $render = $renderData[$day][$i] ?? ['action' => 'show', 'type' => 'empty', 'rowspan' => 1];
                                @endphp
                                
                                @if($render['action'] === 'skip')
                                    {{-- Skip this cell - it's merged with previous --}}
                                @else
                                    <td class="px-2 py-2" @if($render['rowspan'] > 1) rowspan="{{ $render['rowspan'] }}" style="vertical-align: top;" @endif>
                                        @if(isset($timetable[$day][$i]))
                                            @php $slot = $timetable[$day][$i]; @endphp
                                            
                                            @if($render['action'] === 'free' || $render['type'] === 'free_repeated')
                                                {{-- Repeated non-continuous subject - show as Free Period --}}
                                                <div class="bg-gray-100 border border-gray-200 rounded-xl p-3 text-center h-full">
                                                    <p class="font-medium text-gray-500 text-sm">📚 Free Period</p>
                                                </div>
                                            @elseif($slot->slot_type == 'break')
                                                <div class="bg-amber-100 border border-amber-200 rounded-xl p-3 text-center">
                                                    <p class="font-semibold text-amber-700">☕ Break</p>
                                                </div>
                                            @elseif($slot->slot_type == 'lunch')
                                                <div class="bg-orange-100 border border-orange-200 rounded-xl p-3 text-center">
                                                    <p class="font-semibold text-orange-700">🍽️ Lunch</p>
                                                </div>
                                            @elseif($slot->slot_type == 'practical')
                                                <div class="bg-purple-100 border border-purple-300 rounded-xl p-3 h-full">
                                                    <div class="flex items-center justify-center gap-1 mb-1">
                                                        <span class="text-lg">🔧</span>
                                                        <p class="font-bold text-purple-800 text-xs uppercase">Practical</p>
                                                    </div>
                                                    @if($slot->subject)
                                                        <p class="font-semibold text-gray-800 text-sm truncate">
                                                            {{ $slot->subject->name }}
                                                        </p>
                                                        @php
                                                            $teacherName = null;
                                                            if ($slot->teacher) {
                                                                $teacherName = $slot->teacher->user->name ?? null;
                                                            } elseif ($slot->subject && $slot->subject->teacher) {
                                                                $teacherName = $slot->subject->teacher->user->name ?? null;
                                                            }
                                                        @endphp
                                                        @if($teacherName)
                                                            <p class="text-xs text-gray-600 mt-1 truncate">
                                                                {{ $teacherName }}
                                                            </p>
                                                        @else
                                                            <p class="text-xs text-red-400 mt-1">No teacher assigned</p>
                                                        @endif
                                                    @else
                                                        <p class="text-xs text-gray-500">No subject assigned</p>
                                                    @endif
                                                </div>
                                            @elseif($slot->slot_type == 'clubs')
                                                <div class="bg-pink-100 border border-pink-200 rounded-xl p-3 text-center">
                                                    <p class="font-semibold text-pink-700">🎭 {{ $slot->slot_name ?? 'Clubs' }}</p>
                                                </div>
                                            @elseif($slot->slot_type == 'special')
                                                <div class="bg-cyan-100 border border-cyan-200 rounded-xl p-3 text-center">
                                                    <p class="font-semibold text-cyan-700">⭐ {{ $slot->slot_name ?? 'Special' }}</p>
                                                </div>
                                            @elseif($slot->slot_type == 'subject')
                                                @if($slot->subject_id)
                                                    <div class="bg-gradient-to-br from-indigo-50 to-purple-50 border border-indigo-100 rounded-xl p-3 hover:shadow-md transition-shadow h-full">
                                                        <p class="font-semibold text-gray-800 text-sm truncate">
                                                            {{ $slot->subject->name ?? 'Unknown Subject' }}
                                                        </p>
                                                        @if($render['rowspan'] > 1)
                                                            <p class="text-xs text-indigo-600 font-medium">
                                                                ({{ $render['rowspan'] }} periods)
                                                            </p>
                                                        @endif
                                                        @php
                                                            $teacherName = null;
                                                            if ($slot->teacher) {
                                                                $teacherName = $slot->teacher->user->name ?? null;
                                                            } elseif ($slot->subject && $slot->subject->teacher) {
                                                                $teacherName = $slot->subject->teacher->user->name ?? null;
                                                            }
                                                        @endphp
                                                        @if($teacherName)
                                                            <p class="text-xs text-gray-500 mt-1 truncate">
                                                                {{ $teacherName }}
                                                            </p>
                                                        @else
                                                            <p class="text-xs text-red-400 mt-1">No teacher assigned</p>
                                                        @endif
                                                    </div>
                                                @else
                                                    <div class="bg-gray-100 border border-gray-200 rounded-xl p-3 text-center h-full">
                                                        <p class="font-medium text-gray-500 text-sm">📚 Free Period</p>
                                                    </div>
                                                @endif
                                            @else
                                                <div class="bg-gray-50 border border-gray-200 rounded-xl p-3 text-center h-full">
                                                    <p class="text-gray-400 text-sm">Free</p>
                                                </div>
                                            @endif
                                        @endif
                                    </td>
                                @endif
                            @endforeach
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </div>

    <!-- Legend -->
    <div class="mt-6 flex flex-wrap gap-4">
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 bg-gradient-to-br from-indigo-50 to-purple-50 border border-indigo-100 rounded"></div>
            <span class="text-sm text-gray-600">Subject</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 bg-purple-100 border border-purple-300 rounded"></div>
            <span class="text-sm text-gray-600">Practicals</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 bg-amber-100 border border-amber-200 rounded"></div>
            <span class="text-sm text-gray-600">Break</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 bg-orange-100 border border-orange-200 rounded"></div>
            <span class="text-sm text-gray-600">Lunch</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 bg-pink-100 border border-pink-200 rounded"></div>
            <span class="text-sm text-gray-600">Clubs</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 bg-cyan-100 border border-cyan-200 rounded"></div>
            <span class="text-sm text-gray-600">Special Activity</span>
        </div>
    </div>

    <!-- Print Button -->
    <div class="mt-6 flex justify-end">
        <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Print Timetable
        </button>
    </div>
</div>

<style>
    @media print {
        /* Reset and base */
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        html, body { 
            width: 100% !important;
            height: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            background: white !important;
            font-size: 10px !important;
        }
        
        /* Page setup - landscape A4 */
        @page {
            size: A4 landscape;
            margin: 10mm;
        }
        
        /* Hide non-essential elements */
        .no-print, 
        nav, 
        aside, 
        header,
        footer,
        button,
        a[href*="edit"],
        a[href*="index"],
        .flex.flex-col.md\\:flex-row.md\\:items-center.md\\:justify-between > div:last-child,
        .bg-blue-50,
        .grid.grid-cols-2,
        .mt-6.flex.flex-wrap,
        .mt-6.flex.justify-end { 
            display: none !important; 
        }
        
        /* Container */
        .container-fluid {
            padding: 0 !important;
            max-width: 100% !important;
        }
        
        /* Title styling */
        h1 {
            font-size: 16px !important;
            margin-bottom: 5px !important;
        }
        h1 .w-10 { display: none !important; }
        p.text-gray-500 { font-size: 10px !important; margin: 0 !important; }
        
        /* Table container */
        .bg-white.rounded-2xl {
            border: none !important;
            box-shadow: none !important;
            border-radius: 0 !important;
            overflow: visible !important;
        }
        
        .overflow-x-auto {
            overflow: visible !important;
        }
        
        /* Table */
        table {
            width: 100% !important;
            min-width: unset !important;
            border-collapse: collapse !important;
            font-size: 9px !important;
            table-layout: fixed !important;
        }
        
        /* Table header */
        thead tr {
            background: #10b981 !important;
        }
        
        th {
            padding: 6px 4px !important;
            font-size: 9px !important;
            border: 1px solid #ccc !important;
        }
        
        /* Table cells */
        td {
            padding: 4px 2px !important;
            border: 1px solid #ddd !important;
            vertical-align: top !important;
        }
        
        /* Time column */
        td:first-child {
            width: 50px !important;
            font-size: 8px !important;
        }
        
        /* Subject cells */
        .bg-gradient-to-br, 
        .bg-amber-100, 
        .bg-orange-100,
        .bg-gray-50 {
            padding: 4px !important;
            border-radius: 4px !important;
            margin: 0 !important;
        }
        
        .bg-gradient-to-br p,
        .bg-amber-100 p,
        .bg-orange-100 p {
            font-size: 8px !important;
            margin: 0 !important;
            line-height: 1.2 !important;
        }
        
        /* Truncate fix */
        .truncate {
            white-space: normal !important;
            overflow: visible !important;
            text-overflow: clip !important;
        }
        
        /* Remove shadows and hover effects */
        .shadow-sm, .hover\\:shadow-md {
            box-shadow: none !important;
        }
        
        /* Alerts */
        .bg-green-100, .bg-amber-100.border-l-4 {
            display: none !important;
        }
    }
</style>
@endsection
