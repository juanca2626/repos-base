<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!--Lang -->
    <meta name="lang" content="{{ App::getLocale() }}">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @yield('metas')
    <title>{{ config('app.name', 'Laravel') }}</title>
    <!-- Scripts -->
    <script src="{{ asset('/js/app.js') }}"></script>
    <script src="{{ asset('/js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('/js/custom.notify.min.js') }}"></script>
    <script src="{{ asset('/js/custom.scrollbar.min.js') }}"></script>
    <script src="{{ asset('/js/custom.combo.js') }}"></script>
    <script src="{{ asset('/js/custom.carousel.drag.js') }}"></script>
    <link rel="shortcut icon" href="https://backend.limatours.com.pe/images/favicon.png"/>
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <!-- Styles -->
    <link href="{{ asset('/css/jquery-ui.min.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/app.css') }}" rel="stylesheet">
    @yield('css')
    {{-- PUSH NOTIFICATION --}}
    <script src="https://www.gstatic.com/firebasejs/5.5.2/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/5.5.2/firebase-auth.js"></script>
    <script src="https://www.gstatic.com/firebasejs/5.5.2/firebase-database.js"></script>
    <script src="https://www.gstatic.com/firebasejs/5.5.2/firebase-firestore.js"></script>
    <script src="https://www.gstatic.com/firebasejs/5.5.2/firebase-messaging.js"></script>
    <script src="https://www.gstatic.com/firebasejs/5.5.2/firebase-functions.js"></script>
    <link rel="manifest" href="{{ URL::to('/') }}/manifest.json">
    {{-- PUSH NOTIFICATION --}}
</head>
<body>
<div id="app" class="wrapper">
@yield('content')
</div>
@yield('js')
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-160291791-1"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'UA-160291791-1');
</script>
<script data-jsd-embedded data-key="7b467f3d-286d-47fb-acc0-e7adb80f9464" data-base-url="https://jsd-widget.atlassian.com" src="https://jsd-widget.atlassian.com/assets/embed.js"></script>
</body>
</html>
<style>
    .dg-btn--cancel {
        color: #fefefe;
        background-color: #890005;
    }
    .dg-btn--ok {
        color: #890005;
        background-color: #fefefe;
        border-color: #890005;
    }
</style>
