<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="google" value="notranslate">

    <title>{{ config('app.name') }}</title>
    <meta name="environment" content="{{ config('app.env') }}">
    {{-- <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css"
          integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous"> --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <link rel="stylesheet" href="{{ mix('css/vendor.css') }}">
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">

    <link rel="shortcut icon" href="/images/favicon.png"/>
    <link rel="icon" type="image/png" href="/images/favicon.png" sizes="78x78">
</head>
<body class="header-fixed sidebar-fixed aside-menu-fixed aside-menu-off-canvas sidebar-lg-show">
<div id="app">
    <div class="app flex-row align-items-center">
        <div class="container">
            <div class="row justify-content-center text-center">
                <div class="col-md-12">
                    <img src="/images/logo.png" alt="loading"/>
                </div>
                <div class="col-md-12">
                    <img src="/images/loading.svg" alt="loading"/>
                </div>
            </div>
        </div>
    </div>
</div>
@include('scripts')
</body>
</html>
