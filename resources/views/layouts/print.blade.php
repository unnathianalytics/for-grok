<!DOCTYPE html>
<html lang="en">

<head>
    <title>{{ \App\Models\Company::find(1)->name }} - Wire Accounting</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Billing software for small businesses">
    <link rel="stylesheet" href="{{ asset('assets/css/theme.bundle.css') }}?v={{ date('YmdHis') }}">
</head>

<body>
    <div class="container-fluid">
        @yield('content')
    </div>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}?v={{ date('YmdHis') }}"></script>
</body>

</html>
