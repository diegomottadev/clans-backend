<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="pragma" content="no-cache" />
    <meta http-equiv="cache-control" content="max-age=604800" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ config('app.name', 'Instituto Clans') }}</title>
    <link href="{{ asset('libs/bootstrap/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @stack('styles')
</head>
<body>
    @yield('content')

    <script src="{{ asset('libs/jquery/jquery-2.1.1.js') }}" type="text/javascript"></script>

    <script src="{{ asset('libs/bootstrap/bootstrap.js') }}" type="text/javascript"></script>
</body>
</html>
