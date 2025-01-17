<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    @include('theme::partials.head', ['seo' => ($seo ?? null) ])
    <script>
        if (typeof(Storage) !== "undefined") {
            if(localStorage.getItem('theme') && localStorage.getItem('theme') == 'dark'){
                document.documentElement.classList.add('dark');
            }
        }
    </script>
    @stack('styles')
</head>
<body x-data class="flex flex-col lg:min-h-screen bg-zinc-50 dark:bg-zinc-900 @if(config('wave.dev_bar')){{ 'pb-10' }}@endif">
    @livewire('notifications')
    @stack('scripts')
</body>
</html>

