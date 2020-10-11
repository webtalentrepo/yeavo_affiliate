<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="msvalidate.01" content="E609F18415240C86FD92B60DA20A9014" />
    <meta name="msvalidate.01" content="FE5A808EE5CBC333A64544E214FDD82F" />

    <meta name="google-site-verification" content="5QrAQ3qUtmTD2RwJ02qDDaTfnh4SPa9Ph33FcfGmA8k" />

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">

    <!-- Styles -->
    <link href="{{ asset('css/0.vuetify-components.css') }}" rel="stylesheet"/>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet"/>

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
<script type="text/javascript" src="{{ url(mix('js/manifest.js')) }}"></script>
<script type="text/javascript" src="{{ url(mix('js/vendor.js')) }}"></script>
<script type="text/javascript" src="{{ url(mix('js/app.js')) }}" defer></script>

</body>
</html>
