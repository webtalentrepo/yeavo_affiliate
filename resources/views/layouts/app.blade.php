<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet"/>

    <!-- Scripts -->
    <script src="{{ asset('vendor/pace.js') }}"></script>

    <script>
        window.laravel = '{!! json_encode(['csrfToken' => csrf_token()]) !!}';

        window._scoutConfig = {
            BASE: '{{ request()->root() }}',
            Token: JSON.parse(window.laravel).csrfToken
        };
    </script>
</head>
<body>
<div id="app">
    @yield('content')
</div>
@section('scripts')
@show
<script type="text/javascript" src="{{ asset('js/manifest.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/vendor.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/app.js') }}" defer></script>
</body>
</html>
