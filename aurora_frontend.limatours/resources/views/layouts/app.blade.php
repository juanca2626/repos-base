<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <!-- Google Tag Manager -->
    <script>
        (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime(),
                event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
                'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-P6TVXX26');
    </script>
    <!-- End Google Tag Manager -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!--Lang -->
    @if(@$lang != '' AND @$lang != '')
    <meta name="lang" content="{{ $lang }}">
    @else
    <meta name="lang" content="{{ App::getLocale() }}">
    @endif
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- User Type Id -->
    @auth
    <!-- Code User -->
    <meta name="code" content="{{ Auth::user()->code }}">
    <meta name="username" content="{{ Auth::user()->name }}">
    <meta name="user-type-id" content="{{ Auth::user()->user_type_id }}">
    @endauth
    <meta name="route_name" content="{{ Route::currentRouteName() }}">
    @yield('metas')

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('/js/jquery.js') }}"></script>
    <script src="{{ mix('/js/app.js') }}?_={{ time() }}"></script>
    <script src="{{ asset('/js/jquery-ui.min.js') }}"></script>

    <script src="{{ asset('/js/custom.notify.min.js') }}"></script>
    <script src="{{ asset('/js/custom.scrollbar.min.js') }}"></script>
    <script src="{{ asset('/js/custom.combo.js') }}"></script>
    <script src="{{ asset('/js/custom.carousel.drag.js') }}"></script>
    <link rel="shortcut icon" href="https://backend.limatours.com.pe/images/favicon.png" />
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@iconscout/unicons@3.0.6/css/line.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <!-- Styles -->
    <link href="{{ asset('/css/jquery-ui.min.css') }}" rel="stylesheet">
    <link href="{{ mix('/css/app.css') }}?_={{ time() }}" rel="stylesheet">
    @yield('css')
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <!-- reference your copy Font Awesome here (from our CDN or by hosting yourself) -->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/font-awesome-animation@1.1.1/css/font-awesome-animation.css">
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

    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-P6TVXX26"
            height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <!-- div app class="wrapper wrapper-cart" -->
    <div id="app">
        <block-page></block-page>
        @auth()
        @if(Auth::user()->user_type_id === 4)
        <menu-client-component></menu-client-component>
        @else
        <menu-component></menu-component>
        @endif
        @endauth
        @yield('content')
        @auth()
        {{-- <div class="float-covid">
            <a href="{{route('biosafety-protocols')}}" class="text-white">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        COVID-19
        </a>
    </div> --}}
    @endauth
    <!-- Backdrop // Loader -->
    <div class="backdrop-banners"></div>
    @auth()
    <footer-component></footer-component>
    @endauth
</div>
@yield('js')
<script src="https://lima-tours.reechai.com/widget.js"></script>
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
