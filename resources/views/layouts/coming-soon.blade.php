<!DOCTYPE html>
<html class="h-full" data-kt-theme="true" data-kt-theme-mode="light" dir="ltr" lang="en">
    <head>
        @include('layouts.partials.head')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased flex h-full text-base text-foreground bg-background">
        @include('partials.theme-toggle')

        <!-- Page -->
        <div class="flex grow">
            <!-- Main -->
            <div class="flex flex-col grow items-stretch rounded-xl bg-background w-full">
                <div class="flex flex-col grow kt-scrollable-y-auto [--kt-scrollbar-width:auto] pt-5" id="scrollable_content">
                    <main class="grow" role="content">
                        <!-- Container -->
                        <div class="kt-container-fixed">
                            @yield('content')
                        </div>
                        <!-- End of Container -->
                    </main>
                    <footer class="py-5 flex flex-center">
                        <div class="text-gray-600 text-center">
                            <span class="text-muted">© {{ date('Y') }} Space</span>
                        </div>
                    </footer>
                </div>
            </div>
            <!-- End of Main -->
        </div>
        <!-- End of Page -->

        @include('layouts.partials.scripts')
        @yield('scripts')
    </body>
</html> 