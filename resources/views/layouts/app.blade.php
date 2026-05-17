<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ \App\WebsiteSetting::get('school_name', config('app.name', 'Laravel')) }}</title>

    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0f172a">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    
    @stack('styles')
    
</head>
<!-- Log on to codeastro.com for more projects -->
<body class="bg-slate-50 font-sans antialiased" x-data="{ sidebarOpen: false }">
    <div id="app">

        @include('layouts.navbar')
        
        <!-- Mobile sidebar overlay -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="fixed inset-0 bg-black bg-opacity-50 z-30 lg:hidden"
             style="display: none;"></div>
        
        <div class="main flex flex-wrap justify-end mt-16">
            
            @include('layouts.sidebar')

            <div class="content w-full lg:w-5/6 lg:ml-auto">
                <div class="container mx-auto p-4 sm:p-6">

                    @yield('content')
                    
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/app.js') }}"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        $(function() {
            $( "#opennavdropdown" ).on( "click", function() {
                $( "#navdropdown" ).toggleClass( "hidden" );
            })
        })
    </script>

    @stack('scripts')

    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/serviceworker.js')
                .then(function(registration) {
                    console.log('Service Worker registered with scope:', registration.scope);
                })
                .catch(function(error) {
                    console.log('Service Worker registration failed:', error);
                });
        }
    </script>

</body>
</html>