@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-6">
    <div class="mb-6">
        <div class="flex items-center mb-2">
            <a href="{{ route('admin.website.index') }}" class="text-blue-600 hover:text-blue-800 mr-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-2xl font-bold text-gray-800">General Settings</h1>
        </div>
        <p class="text-gray-600">Configure your website's basic information and footer content</p>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-md">
        <form action="{{ route('admin.website.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="p-6 space-y-6">
                @foreach($settings as $setting)
                <div class="border-b border-gray-200 pb-6 last:border-0 last:pb-0">
                    <label for="{{ $setting->key }}" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ $setting->label }}
                    </label>
                    @if($setting->description)
                    <p class="text-xs text-gray-500 mb-2">{{ $setting->description }}</p>
                    @endif

                    @if($setting->type === 'image')
                        @if($setting->value)
                        <div class="mb-3">
                            <img src="{{ asset($setting->value) }}" alt="{{ $setting->label }}"
                                 class="h-20 w-auto rounded border border-gray-200 object-contain bg-gray-50 p-1">
                        </div>
                        @endif
                        <input
                            type="file"
                            name="{{ $setting->key }}"
                            id="{{ $setting->key }}"
                            accept="image/*"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                        >
                        <p class="text-xs text-gray-400 mt-1">Leave empty to keep the current image</p>
                    @elseif($setting->type === 'textarea')
                        <textarea
                            name="{{ $setting->key }}"
                            id="{{ $setting->key }}"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >{{ $setting->value }}</textarea>
                    @else
                        <input
                            type="{{ $setting->type === 'color' ? 'color' : 'text' }}"
                            name="{{ $setting->key }}"
                            id="{{ $setting->key }}"
                            value="{{ $setting->value }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ $setting->type === 'color' ? 'h-12' : '' }}"
                        >
                    @endif
                </div>
                @endforeach
            </div>

            <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex justify-end space-x-3">
                <a href="{{ route('admin.website.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
