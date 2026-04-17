@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Create Newsletter</h1>
                    <p class="mt-1 text-sm text-gray-500">Compose and publish a new newsletter for parents and students</p>
                </div>
                <a href="{{ route('newsletters.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 hover:shadow-md transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Newsletters
                </a>
            </div>

            <form action="{{ route('newsletters.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Newsletter Content Card -->
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-6">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-cyan-500 to-blue-600">
                        <h3 class="text-lg font-semibold text-white flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                            </svg>
                            Newsletter Content
                        </h3>
                    </div>
                    <div class="px-6 py-6 space-y-6">
                        <!-- Title -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Newsletter Title</label>
                            <input name="title" type="text" value="{{ old('title') }}" required
                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-colors duration-200"
                                   placeholder="Enter an engaging title for your newsletter">
                            @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <!-- Content -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Content</label>
                            <textarea name="content" rows="6" required
                                      class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-colors duration-200"
                                      placeholder="Write your newsletter content here...">{{ old('content') }}</textarea>
                            @error('content')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <!-- Image Upload -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Featured Image (Optional)</label>
                            <div id="upload-area" class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:border-cyan-400 transition-colors duration-200 cursor-pointer" onclick="document.getElementById('image').click()">
                                <!-- Upload placeholder -->
                                <div id="upload-placeholder" class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <div class="flex text-sm text-gray-600 justify-center">
                                        <span class="font-medium text-cyan-600 hover:text-cyan-500">Upload a file</span>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                                </div>
                                <!-- Image preview (hidden until file selected) -->
                                <div id="image-preview-wrap" class="hidden w-full text-center">
                                    <img id="image-preview" src="#" alt="Preview" class="mx-auto max-h-64 rounded-lg object-contain shadow-md">
                                    <p id="image-name" class="mt-2 text-xs text-gray-500 truncate"></p>
                                    <button type="button" onclick="clearImage(event)" class="mt-2 text-xs text-red-500 hover:text-red-700 font-medium">Remove image</button>
                                </div>
                            </div>
                            <input id="image" name="image" type="file" class="sr-only" accept="image/*">
                        </div>
                    </div>
                </div>

                <!-- Publishing Options Card -->
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-6">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Publishing Options
                        </h3>
                    </div>
                    <div class="px-6 py-6">
                        <label class="flex items-center p-4 rounded-xl border-2 border-gray-200 hover:border-cyan-300 hover:bg-cyan-50 cursor-pointer transition-all duration-200">
                            <input type="checkbox" name="is_published" class="w-5 h-5 text-cyan-600 border-gray-300 rounded focus:ring-cyan-500">
                            <div class="ml-4">
                                <p class="text-sm font-semibold text-gray-900">Publish Immediately</p>
                                <p class="text-xs text-gray-500">Newsletter will be visible to all users right away</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-end space-x-4">
                    <a href="{{ route('newsletters.index') }}" class="px-6 py-3 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-colors duration-200">
                        Cancel
                    </a>
                    <button type="submit" name="action" value="post_next" class="px-8 py-3 rounded-lg bg-gradient-to-r from-emerald-500 to-teal-600 text-gray-700 font-semibold shadow-lg hover:from-emerald-600 hover:to-teal-700 hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Post Next Letter
                    </button>
                    <button type="submit" name="action" value="save" class="px-8 py-3 rounded-lg bg-gradient-to-r from-cyan-500 to-blue-600 text-gray-700  font-semibold shadow-lg hover:from-cyan-600 hover:to-blue-700 hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Create Newsletter
                    </button>
                </div>
            </form>
        </div>
    </div>
@push('scripts')
<script>
    document.getElementById('image').addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('upload-placeholder').classList.add('hidden');
            document.getElementById('image-preview-wrap').classList.remove('hidden');
            document.getElementById('image-preview').src = e.target.result;
            document.getElementById('image-name').textContent = file.name;
            document.getElementById('upload-area').classList.remove('border-dashed');
            document.getElementById('upload-area').classList.add('border-cyan-400', 'border-solid');
        };
        reader.readAsDataURL(file);
    });

    function clearImage(event) {
        event.stopPropagation();
        const input = document.getElementById('image');
        input.value = '';
        document.getElementById('image-preview-wrap').classList.add('hidden');
        document.getElementById('upload-placeholder').classList.remove('hidden');
        document.getElementById('upload-area').classList.add('border-dashed');
        document.getElementById('upload-area').classList.remove('border-cyan-400', 'border-solid');
    }
</script>
@endpush

@endsection
