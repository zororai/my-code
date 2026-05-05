@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
                <p class="mt-2 text-sm text-gray-600">Welcome back, {{ Auth::user()->name }}!</p>
            </div>
            <div class="flex items-center gap-3 flex-wrap">

                {{-- Term switcher — only shown for roles that have dashboard term data --}}
                @if(isset($availableTerms) && $availableTerms->count() > 0)
                @php
                    $selectedTermId = isset($currentTerm) ? $currentTerm->id : null;
                @endphp
                <form method="GET" action="{{ route('home') }}" class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <select name="term_id" onchange="this.form.submit()"
                        class="border border-gray-300 rounded-lg text-sm px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white text-gray-700 font-medium">
                        @foreach($availableTerms as $t)
                            <option value="{{ $t['id'] }}" {{ $t['id'] == $selectedTermId ? 'selected' : '' }}>
                                {{ $t['label'] }}
                            </option>
                        @endforeach
                    </select>
                </form>
                @endif

                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                    <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                    </svg>
                    {{ now()->format('l, M d, Y') }}
                </span>
            </div>
        </div>
    </div>

    @if(Auth::user()->hasRole('Admin'))
        @include('dashboard.admin')
    @elseif(Auth::user()->hasRole('Parent'))
        @include('dashboard.parents')
    @elseif(Auth::user()->hasRole('Teacher'))
        @include('dashboard.teacher')
    @elseif(Auth::user()->hasRole('Student'))
        @include('dashboard.student')
    @else
        @include('dashboard.admin')
    @endif
</div>
@endsection
