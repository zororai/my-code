@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">{{ $class->class_name }}</h2>
            <p class="text-sm text-gray-600">Subject Performance for {{ $currentYear }} - {{ ucfirst($currentPeriod) }} Term</p>
        </div>
        <a href="{{ route('home') }}" class="mt-4 md:mt-0 bg-gray-700 text-white text-sm uppercase py-2 px-4 flex items-center rounded hover:bg-gray-800 transition-colors w-fit">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Dashboard
        </a>
    </div>

    <!-- Assessment Coverage Summary -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h3 class="text-lg font-bold text-gray-800">Assessment Coverage</h3>
                <p class="text-sm text-gray-500">{{ $subjectsWithAssessments }}/{{ $totalSubjects }} subjects have assessments</p>
            </div>
            <div class="mt-4 md:mt-0 flex items-center space-x-4">
                <div class="w-48 bg-gray-200 rounded-full h-3">
                    <div class="h-3 rounded-full {{ $assessmentCoverage == 100 ? 'bg-green-500' : ($assessmentCoverage >= 50 ? 'bg-blue-500' : ($assessmentCoverage > 0 ? 'bg-yellow-500' : 'bg-gray-400')) }}" style="width: {{ $assessmentCoverage }}%"></div>
                </div>
                <span class="text-2xl font-bold {{ $assessmentCoverage == 100 ? 'text-green-600' : ($assessmentCoverage >= 50 ? 'text-blue-600' : ($assessmentCoverage > 0 ? 'text-yellow-600' : 'text-gray-400')) }}">{{ $assessmentCoverage }}%</span>
            </div>
        </div>
    </div>

    @if(count($subjectPerformanceData) > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($subjectPerformanceData as $subjectData)
        <div x-data="{ expanded: false }" class="bg-white border rounded-xl overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 {{ $subjectData['overall_performance'] >= 50 ? 'border-green-200' : ($subjectData['overall_performance'] > 0 ? 'border-red-200' : 'border-gray-200') }}">
            <div @click="expanded = !expanded" class="cursor-pointer p-5 {{ $subjectData['overall_performance'] >= 50 ? 'bg-gradient-to-r from-green-50 to-emerald-50 hover:from-green-100 hover:to-emerald-100' : ($subjectData['overall_performance'] > 0 ? 'bg-gradient-to-r from-red-50 to-rose-50 hover:from-red-100 hover:to-rose-100' : 'bg-gray-50 hover:bg-gray-100') }} transition-colors">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-14 h-14 rounded-xl flex items-center justify-center {{ $subjectData['overall_performance'] >= 50 ? 'bg-green-500' : ($subjectData['overall_performance'] > 0 ? 'bg-red-500' : 'bg-gray-400') }} shadow-lg">
                            <span class="text-white font-bold text-lg">{{ strtoupper(substr($subjectData['subject'], 0, 2)) }}</span>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800 text-lg">{{ $subjectData['subject'] }}</h4>
                            <p class="text-sm text-gray-500">{{ $subjectData['assessments'] }} assessment(s)</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-3xl font-bold {{ $subjectData['overall_performance'] >= 50 ? 'text-green-600' : ($subjectData['overall_performance'] > 0 ? 'text-red-600' : 'text-gray-400') }}">
                            {{ $subjectData['overall_performance'] > 0 ? $subjectData['overall_performance'] . '%' : '--' }}
                        </p>
                        <p class="text-xs text-gray-500">Overall Performance</p>
                    </div>
                </div>
                <div class="flex justify-center mt-3">
                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': expanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>
            
            <div x-show="expanded" x-collapse class="bg-white border-t p-5">
                <h5 class="text-sm font-bold text-gray-700 mb-4 flex items-center">
                    <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Assessment Type Breakdown
                </h5>
                <div class="space-y-3">
                    @foreach($assessmentTypes as $type)
                    @php $typeData = $subjectData['types'][$type] ?? ['given' => 0, 'performance' => 0]; @endphp
                    @if($typeData['given'] > 0)
                    <div class="flex items-center justify-between py-2 px-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-2">
                            <span class="text-sm font-medium text-gray-700">{{ $type }}</span>
                            <span class="text-xs bg-blue-100 text-blue-600 px-2 py-0.5 rounded-full">{{ $typeData['given'] }}</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-24 bg-gray-200 rounded-full h-2">
                                <div class="h-2 rounded-full {{ $typeData['performance'] >= 50 ? 'bg-green-500' : 'bg-red-500' }}" style="width: {{ min($typeData['performance'], 100) }}%"></div>
                            </div>
                            <span class="text-sm font-bold {{ $typeData['performance'] >= 50 ? 'text-green-600' : 'text-red-600' }} w-12 text-right">{{ $typeData['performance'] }}%</span>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">No Assessments Found</h3>
        <p class="text-gray-500">There are no assessments recorded for this class in the current term.</p>
        <a href="{{ route('home') }}" class="inline-flex items-center mt-4 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Dashboard
        </a>
    </div>
    @endif
</div>
@endsection
