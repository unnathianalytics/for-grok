<!DOCTYPE html>
<html lang="en">

<head>
    <title>{{ \App\Models\Company::find(1)->name }} - Wire Accounting</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Billing software for small businesses">
    @livewireStyles
    <link rel="stylesheet" href="{{ asset('assets/css/theme.bundle.css') }}?v={{ date('YmdHis') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}?v={{ date('YmdHis') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/jquery-ui.min.css') }}?v={{ date('YmdHis') }}">
    @stack('body-styles')
</head>

<body>
    @if (Auth::check())
        @include('layouts.navbar')
    @endif
    <div class="container-fluid mt-3">
        @yield('content')
    </div>
    @include('layouts.footer')
    <script data-navigate-once="true" src="{{ asset('assets/js/bootstrap.bundle.min.js') }}?v={{ date('YmdHis') }}">
    </script>
    <script data-navigate-once="true" src="{{ asset('assets/js/jquery.js') }}?v={{ date('YmdHis') }}"></script>
    <script data-navigate-once="true" src="{{ asset('assets/js/jquery-ui.min.js') }}?v={{ date('YmdHis') }}"></script>
    <script data-navigate-once="true" src="{{ asset('/assets/js/app.js') }}?v={{ date('YmdHis') }}"></script>
    @livewireScripts
    @stack('scripts')
</body>

</html>
