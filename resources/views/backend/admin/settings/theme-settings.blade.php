@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-3xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Theme Settings</h2>
                <p class="text-gray-600 mt-1">Customize the colors of your admin panel navbar and sidebar</p>
            </div>
            <a href="{{ route('home') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center">
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

        <!-- Preview Section -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-gray-700 to-gray-800 px-6 py-4">
                <h3 class="text-lg font-semibold text-white flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Live Preview
                </h3>
            </div>
            <div class="p-6">
                <div class="flex gap-4">
                    <!-- Mini Sidebar Preview -->
                    <div id="sidebar-preview" class="w-48 h-48 rounded-lg shadow-md p-4" style="background-color: {{ $settings['theme_sidebar_color'] }}">
                        <div class="text-white text-sm font-semibold mb-3">Sidebar</div>
                        <div class="space-y-2">
                            <div class="bg-white/20 rounded px-2 py-1 text-white text-xs">Home</div>
                            <div class="bg-white/20 rounded px-2 py-1 text-white text-xs">Settings</div>
                            <div class="bg-white/20 rounded px-2 py-1 text-white text-xs">Users</div>
                        </div>
                    </div>
                    <!-- Mini Navbar Preview -->
                    <div class="flex-1">
                        <div id="navbar-preview" class="h-12 rounded-lg shadow-md flex items-center px-4" style="background-color: {{ $settings['theme_navbar_color'] }}">
                            <span class="text-white text-sm font-semibold">Navbar / Header</span>
                            <div class="ml-auto flex items-center space-x-2">
                                <div class="w-8 h-8 bg-white/20 rounded-full"></div>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Colors update in real-time as you change them below</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings Form -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-purple-600 to-indigo-600 px-6 py-4">
                <h3 class="text-lg font-semibold text-white flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                    </svg>
                    Color Settings
                </h3>
            </div>

            <form action="{{ route('admin.settings.theme.update') }}" method="POST" class="p-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Sidebar Color -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Sidebar Color</label>
                        <div class="flex items-center space-x-3">
                            <input type="color" name="theme_sidebar_color" id="theme_sidebar_color" 
                                value="{{ $settings['theme_sidebar_color'] }}" 
                                class="h-10 w-20 rounded border border-gray-300 cursor-pointer"
                                onchange="updatePreview()">
                            <input type="text" id="theme_sidebar_color_text" value="{{ $settings['theme_sidebar_color'] }}" 
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm"
                                onchange="document.getElementById('theme_sidebar_color').value = this.value; updatePreview();">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">The background color of the sidebar menu</p>
                    </div>

                    <!-- Navbar Color -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Navbar/Header Color</label>
                        <div class="flex items-center space-x-3">
                            <input type="color" name="theme_navbar_color" id="theme_navbar_color" 
                                value="{{ $settings['theme_navbar_color'] }}" 
                                class="h-10 w-20 rounded border border-gray-300 cursor-pointer"
                                onchange="updatePreview()">
                            <input type="text" id="theme_navbar_color_text" value="{{ $settings['theme_navbar_color'] }}" 
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm"
                                onchange="document.getElementById('theme_navbar_color').value = this.value; updatePreview();">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">The background color of the top navigation bar</p>
                    </div>

                    <!-- Primary Color -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Primary Color</label>
                        <div class="flex items-center space-x-3">
                            <input type="color" name="theme_primary_color" id="theme_primary_color" 
                                value="{{ $settings['theme_primary_color'] }}" 
                                class="h-10 w-20 rounded border border-gray-300 cursor-pointer">
                            <input type="text" id="theme_primary_color_text" value="{{ $settings['theme_primary_color'] }}" 
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm"
                                onchange="document.getElementById('theme_primary_color').value = this.value;">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Used for buttons, links, and accents</p>
                    </div>

                    <!-- Primary Hover Color -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Primary Hover Color</label>
                        <div class="flex items-center space-x-3">
                            <input type="color" name="theme_primary_hover" id="theme_primary_hover" 
                                value="{{ $settings['theme_primary_hover'] }}" 
                                class="h-10 w-20 rounded border border-gray-300 cursor-pointer">
                            <input type="text" id="theme_primary_hover_text" value="{{ $settings['theme_primary_hover'] }}" 
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm"
                                onchange="document.getElementById('theme_primary_hover').value = this.value;">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Color when hovering over buttons/links</p>
                    </div>

                    <!-- Primary Dark Color -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Primary Dark Color</label>
                        <div class="flex items-center space-x-3">
                            <input type="color" name="theme_primary_dark" id="theme_primary_dark" 
                                value="{{ $settings['theme_primary_dark'] }}" 
                                class="h-10 w-20 rounded border border-gray-300 cursor-pointer">
                            <input type="text" id="theme_primary_dark_text" value="{{ $settings['theme_primary_dark'] }}" 
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm"
                                onchange="document.getElementById('theme_primary_dark').value = this.value;">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Darker shade for borders and active states</p>
                    </div>
                </div>

                <!-- Preset Colors -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Quick Presets</label>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" onclick="applyPreset('#2563eb', '#1d4ed8', '#1e40af')" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">Blue (Default)</button>
                        <button type="button" onclick="applyPreset('#059669', '#047857', '#065f46')" class="px-4 py-2 bg-emerald-600 text-white text-sm rounded-lg hover:bg-emerald-700">Emerald</button>
                        <button type="button" onclick="applyPreset('#7c3aed', '#6d28d9', '#5b21b6')" class="px-4 py-2 bg-violet-600 text-white text-sm rounded-lg hover:bg-violet-700">Violet</button>
                        <button type="button" onclick="applyPreset('#dc2626', '#b91c1c', '#991b1b')" class="px-4 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700">Red</button>
                        <button type="button" onclick="applyPreset('#ea580c', '#c2410c', '#9a3412')" class="px-4 py-2 bg-orange-600 text-white text-sm rounded-lg hover:bg-orange-700">Orange</button>
                        <button type="button" onclick="applyPreset('#0891b2', '#0e7490', '#155e75')" class="px-4 py-2 bg-cyan-600 text-white text-sm rounded-lg hover:bg-cyan-700">Cyan</button>
                        <button type="button" onclick="applyPreset('#4b5563', '#374151', '#1f2937')" class="px-4 py-2 bg-gray-600 text-white text-sm rounded-lg hover:bg-gray-700">Gray</button>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end space-x-3">
                    <a href="{{ route('home') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Save Theme
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updatePreview() {
    const sidebarColor = document.getElementById('theme_sidebar_color').value;
    const navbarColor = document.getElementById('theme_navbar_color').value;
    
    document.getElementById('sidebar-preview').style.backgroundColor = sidebarColor;
    document.getElementById('navbar-preview').style.backgroundColor = navbarColor;
    
    document.getElementById('theme_sidebar_color_text').value = sidebarColor;
    document.getElementById('theme_navbar_color_text').value = navbarColor;
}

function applyPreset(primary, hover, dark) {
    document.getElementById('theme_primary_color').value = primary;
    document.getElementById('theme_primary_color_text').value = primary;
    document.getElementById('theme_primary_hover').value = hover;
    document.getElementById('theme_primary_hover_text').value = hover;
    document.getElementById('theme_primary_dark').value = dark;
    document.getElementById('theme_primary_dark_text').value = dark;
    document.getElementById('theme_sidebar_color').value = primary;
    document.getElementById('theme_sidebar_color_text').value = primary;
    document.getElementById('theme_navbar_color').value = primary;
    document.getElementById('theme_navbar_color_text').value = primary;
    updatePreview();
}

// Sync color inputs with text inputs
document.querySelectorAll('input[type="color"]').forEach(input => {
    input.addEventListener('input', function() {
        const textInput = document.getElementById(this.id + '_text');
        if (textInput) textInput.value = this.value;
    });
});
</script>
@endsection
