@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Landing Page Settings</h2>
                <p class="text-gray-600 mt-1">Choose which page visitors see when they first access the site</p>
            </div>
            <a href="{{ route('admin.website.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back
            </a>
        </div>

        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
            {{ session('success') }}
        </div>
        @endif

        <!-- Settings Card -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                <h3 class="text-lg font-semibold text-white flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Default Landing Page
                </h3>
            </div>

            <form action="{{ route('admin.website.landing-page.update') }}" method="POST" class="p-6">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    <p class="text-gray-600 mb-4">Select which page users will see when they visit the root URL of your website:</p>

                    <!-- Option: Website (Public Site) -->
                    <label class="block cursor-pointer">
                        <div class="flex items-start p-4 border-2 rounded-lg transition-all {{ $currentLandingPage == 'web' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-blue-300' }}">
                            <input type="radio" name="landing_page" value="web" {{ $currentLandingPage == 'web' ? 'checked' : '' }} class="mt-1 h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <div class="ml-3">
                                <span class="block text-lg font-semibold text-gray-800">Website (/web)</span>
                                <span class="block text-sm text-gray-500 mt-1">Public-facing website with school information, news, and about pages. Best for showcasing your school to prospective students and parents.</span>
                                <div class="mt-2 flex items-center text-xs text-blue-600">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                    </svg>
                                    Preview: <a href="{{ url('/web') }}" target="_blank" class="ml-1 underline">{{ url('/web') }}</a>
                                </div>
                            </div>
                        </div>
                    </label>

                    <!-- Option: Login Page -->
                    <label class="block cursor-pointer">
                        <div class="flex items-start p-4 border-2 rounded-lg transition-all {{ $currentLandingPage == 'logins' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-blue-300' }}">
                            <input type="radio" name="landing_page" value="logins" {{ $currentLandingPage == 'logins' ? 'checked' : '' }} class="mt-1 h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <div class="ml-3">
                                <span class="block text-lg font-semibold text-gray-800">Login Page (/logins)</span>
                                <span class="block text-sm text-gray-500 mt-1">Direct login page for students, teachers, and parents. Best for schools where users primarily need to access the portal quickly.</span>
                                <div class="mt-2 flex items-center text-xs text-green-600">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                    </svg>
                                    Preview: <a href="{{ url('/logins') }}" target="_blank" class="ml-1 underline">{{ url('/logins') }}</a>
                                </div>
                            </div>
                        </div>
                    </label>
                </div>

                <div class="mt-6 flex items-center justify-end space-x-3">
                    <a href="{{ route('admin.website.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        <!-- Info Card -->
        <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-yellow-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="ml-3">
                    <h4 class="text-sm font-medium text-yellow-800">How it works</h4>
                    <p class="text-sm text-yellow-700 mt-1">When visitors access your website's root URL ({{ url('/') }}), they will be automatically redirected to your chosen landing page. Both pages remain accessible at their direct URLs regardless of this setting.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
