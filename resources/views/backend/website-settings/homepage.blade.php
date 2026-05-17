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
            <h1 class="text-2xl font-bold text-gray-800">Homepage Settings</h1>
        </div>
        <p class="text-gray-600">Manage slideshow slides and homepage content</p>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    {{-- Slideshow Management --}}
    <div class="bg-white rounded-lg shadow-md mb-8">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">Slideshow Slides</h2>
                <p class="text-sm text-gray-500 mt-0.5">These images appear in both the achievements carousel and the students carousel on the homepage.</p>
            </div>
            <a href="{{ route('admin.achievements.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Slide
            </a>
        </div>

        @if($achievements->count() > 0)
        <div class="p-6 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            @foreach($achievements as $achievement)
            <div class="relative group rounded-lg overflow-hidden border border-gray-200 bg-gray-50">
                <img src="{{ asset('storage/' . $achievement->image_path) }}"
                     alt="{{ $achievement->student_name }}"
                     class="w-full h-36 object-cover {{ $achievement->is_active ? '' : 'opacity-40' }}">

                {{-- Name overlay --}}
                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-r from-red-700 to-green-700 text-white text-xs font-bold px-2 py-1 truncate">
                    {{ strtoupper($achievement->student_name) }}
                </div>

                {{-- Status badge --}}
                <div class="absolute top-1 right-1">
                    @if($achievement->is_active)
                        <span class="bg-green-500 text-white text-xs px-1.5 py-0.5 rounded-full">ON</span>
                    @else
                        <span class="bg-gray-400 text-white text-xs px-1.5 py-0.5 rounded-full">OFF</span>
                    @endif
                </div>

                {{-- Actions overlay on hover --}}
                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all flex items-center justify-center opacity-0 group-hover:opacity-100">
                    <div class="flex space-x-2">
                        <a href="{{ route('admin.achievements.edit', $achievement) }}"
                           class="bg-white text-gray-800 text-xs px-2 py-1 rounded hover:bg-blue-600 hover:text-white transition-colors">
                            Edit
                        </a>
                        <form action="{{ route('admin.achievements.toggle', $achievement) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit"
                                    class="bg-white text-gray-800 text-xs px-2 py-1 rounded hover:bg-yellow-500 hover:text-white transition-colors"
                                    title="{{ $achievement->is_active ? 'Disable' : 'Enable' }}">
                                {{ $achievement->is_active ? 'Hide' : 'Show' }}
                            </button>
                        </form>
                        <form action="{{ route('admin.achievements.destroy', $achievement) }}" method="POST" class="inline"
                              onsubmit="return confirm('Delete {{ addslashes($achievement->student_name) }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="bg-white text-red-600 text-xs px-2 py-1 rounded hover:bg-red-600 hover:text-white transition-colors">
                                Del
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="p-10 text-center">
            <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <p class="mt-2 text-sm text-gray-500">No slides yet. Click <strong>Add Slide</strong> to get started.</p>
        </div>
        @endif
    </div>

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
                    
                    @if($setting->type === 'textarea')
                        <textarea 
                            name="{{ $setting->key }}" 
                            id="{{ $setting->key }}" 
                            rows="4"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >{{ $setting->value }}</textarea>
                    @elseif($setting->type === 'image')
                        <div class="space-y-2">
                            @if($setting->value)
                            <div class="mb-2">
                                <img src="{{ asset($setting->value) }}" alt="{{ $setting->label }}" class="h-32 rounded border">
                            </div>
                            @endif
                            <input 
                                type="file" 
                                name="{{ $setting->key }}" 
                                id="{{ $setting->key }}" 
                                accept="image/*"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                        </div>
                    @else
                        <input 
                            type="text" 
                            name="{{ $setting->key }}" 
                            id="{{ $setting->key }}" 
                            value="{{ $setting->value }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
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
