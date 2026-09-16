@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Edit Teacher</h1>
                    <p class="mt-1 text-sm text-gray-500">Update teacher information and details</p>
                </div>
                <a href="{{ route('teacher.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 hover:shadow-md transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Teachers
                </a>
            </div>

            <form action="{{ route('teacher.update', $teacher->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                    <!-- Profile Card -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-2xl shadow-lg overflow-hidden sticky top-8">
                            <div class="bg-gradient-to-r from-emerald-500 to-teal-600 px-6 py-8">
                                <div class="flex flex-col items-center">
                                    <div class="relative group">
                                        <img id="preview-image" class="w-28 h-28 rounded-full border-4 border-white shadow-lg object-cover" 
                                             src="{{ asset('images/profile/' . $teacher->user->profile_picture) }}" 
                                             alt="{{ $teacher->user->name }}">
                                        <label for="profile_picture" class="absolute inset-0 flex items-center justify-center bg-black/50 rounded-full opacity-0 group-hover:opacity-100 cursor-pointer transition-opacity duration-200">
                                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                        </label>
                                        <input type="file" name="profile_picture" id="profile_picture" class="hidden" accept="image/*" onchange="previewImage(this)">
                                    </div>
                                    <h2 class="mt-4 text-xl font-bold text-white">{{ $teacher->user->name }}</h2>
                                    <span class="mt-1 inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white/20 text-white backdrop-blur-sm">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0z"/>
                                        </svg>
                                        Teacher
                                    </span>
                                </div>
                            </div>
                            <div class="px-6 py-5">
                                <div class="space-y-3">
                                    <div class="flex items-center text-gray-600">
                                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        <span class="text-sm truncate">{{ $teacher->user->email }}</span>
                                    </div>
                                    <div class="flex items-center text-gray-600">
                                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                        <span class="text-sm">{{ $teacher->phone }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Section -->
                    <div class="lg:col-span-2 space-y-6">

                        <!-- Basic Information Card -->
                        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Basic Information
                                </h3>
                            </div>
                            <div class="px-6 py-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                                        <input name="name" type="text" value="{{ $teacher->user->name }}" 
                                               class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors duration-200">
                                        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                                        <input name="email" type="email" value="{{ $teacher->user->email }}" 
                                               class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors duration-200">
                                        @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                                        <input name="phone" type="text" value="{{ $teacher->phone }}" 
                                               class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors duration-200">
                                        @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth</label>
                                        <input name="dateofbirth" id="datepicker-te" type="text" value="{{ $teacher->dateofbirth }}" autocomplete="off"
                                               class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors duration-200">
                                        @error('dateofbirth')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                                        <div class="flex items-center space-x-6 mt-1">
                                            <label class="inline-flex items-center cursor-pointer">
                                                <input type="radio" name="gender" value="male" {{ ($teacher->gender == 'male') ? 'checked' : '' }} 
                                                       class="w-4 h-4 text-emerald-600 border-gray-300 focus:ring-emerald-500">
                                                <span class="ml-2 text-sm text-gray-700">Male</span>
                                            </label>
                                            <label class="inline-flex items-center cursor-pointer">
                                                <input type="radio" name="gender" value="female" {{ ($teacher->gender == 'female') ? 'checked' : '' }} 
                                                       class="w-4 h-4 text-emerald-600 border-gray-300 focus:ring-emerald-500">
                                                <span class="ml-2 text-sm text-gray-700">Female</span>
                                            </label>
                                            <label class="inline-flex items-center cursor-pointer">
                                                <input type="radio" name="gender" value="other" {{ ($teacher->gender == 'other') ? 'checked' : '' }} 
                                                       class="w-4 h-4 text-emerald-600 border-gray-300 focus:ring-emerald-500">
                                                <span class="ml-2 text-sm text-gray-700">Other</span>
                                            </label>
                                        </div>
                                        @error('gender')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Address Information Card -->
                        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    Address Information
                                </h3>
                            </div>
                            <div class="px-6 py-6 space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Current Address</label>
                                    <input name="current_address" type="text" value="{{ $teacher->current_address }}" 
                                           class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors duration-200">
                                    @error('current_address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Permanent Address</label>
                                    <input name="permanent_address" type="text" value="{{ $teacher->permanent_address }}" 
                                           class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors duration-200">
                                    @error('permanent_address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>

                        <!-- Teacher Roles Card -->
                        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                    </svg>
                                    Teacher Roles
                                </h3>
                                <p class="mt-1 text-sm text-gray-500">Assign special roles and responsibilities</p>
                            </div>
                            <div class="px-6 py-6">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <!-- Class Teacher -->
                                    <label class="flex items-center p-4 rounded-xl border-2 cursor-pointer transition-all duration-200 hover:border-emerald-300 hover:bg-emerald-50 {{ $teacher->is_class_teacher ? 'border-emerald-500 bg-emerald-50' : 'border-gray-200' }}">
                                        <input type="checkbox" name="is_class_teacher" value="1"
                                               class="w-5 h-5 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500"
                                               {{ $teacher->is_class_teacher ? 'checked' : '' }}>
                                        <div class="ml-3 flex-1">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center mr-2">
                                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                                    </svg>
                                                </div>
                                                <span class="text-sm font-semibold text-gray-900">Class Teacher</span>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-1 ml-10">Can manage class students & attendance</p>
                                        </div>
                                    </label>

                                    <!-- HOD -->
                                    <label class="flex items-center p-4 rounded-xl border-2 cursor-pointer transition-all duration-200 hover:border-emerald-300 hover:bg-emerald-50 {{ $teacher->is_hod ? 'border-emerald-500 bg-emerald-50' : 'border-gray-200' }}">
                                        <input type="checkbox" name="is_hod" value="1"
                                               class="w-5 h-5 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500"
                                               {{ $teacher->is_hod ? 'checked' : '' }}>
                                        <div class="ml-3 flex-1">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center mr-2">
                                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                                    </svg>
                                                </div>
                                                <span class="text-sm font-semibold text-gray-900">Head of Department</span>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-1 ml-10">Department management</p>
                                        </div>
                                    </label>

                                    <!-- Sport Director -->
                                    <label class="flex items-center p-4 rounded-xl border-2 cursor-pointer transition-all duration-200 hover:border-emerald-300 hover:bg-emerald-50 {{ $teacher->is_sport_director ? 'border-emerald-500 bg-emerald-50' : 'border-gray-200' }}">
                                        <input type="checkbox" name="is_sport_director" value="1"
                                               class="w-5 h-5 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500"
                                               {{ $teacher->is_sport_director ? 'checked' : '' }}>
                                        <div class="ml-3 flex-1">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center mr-2">
                                                    <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                </div>
                                                <span class="text-sm font-semibold text-gray-900">Sport Director</span>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-1 ml-10">Sports activities & teams</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Employment Status Card -->
                        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                                <h3 class="text-lg font-semibold text-gray-900">Employment Status</h3>
                                <p class="mt-1 text-sm text-gray-500">Resigned or inactive staff are hidden from the active teacher list and cannot log in</p>
                            </div>
                            <div class="px-6 py-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select name="status" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors duration-200">
                                    <option value="active" @selected($teacher->status === 'active')>Active</option>
                                    <option value="resigned" @selected($teacher->status === 'resigned')>Resigned</option>
                                    <option value="inactive" @selected($teacher->status === 'inactive')>Inactive</option>
                                </select>
                                @error('status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-end space-x-4 pt-4">
                            <a href="{{ route('teacher.index') }}" class="px-6 py-3 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-colors duration-200">
                                Cancel
                            </a>
                            <button type="submit" class="px-8 py-3 rounded-lg bg-gradient-to-r from-emerald-500 to-teal-600 text-gray-700  font-semibold shadow-lg hover:from-emerald-600 hover:to-teal-700 hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Update Teacher
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#preview-image').attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    $(function() {       
        $("#datepicker-te").datepicker({ dateFormat: 'yy-mm-dd' });
    })
</script>
@endpush