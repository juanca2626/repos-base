<html lang="en"><head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Not Found</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <style>
        html, body {
            background-color: #fff;
            color: #636b6f;
            font-family: 'Nunito', sans-serif;
            font-weight: 100;
            height: 100vh;
            margin: 0;
        }

        .full-height {
            height: 100vh;
        }

        .flex-center {
            align-items: center;
            display: flex;
            justify-content: center;
        }

        .position-ref {
            position: relative;
        }

        .code {
            border-right: 2px solid;
            font-size: 26px;
            padding: 0 15px 0 15px;
            text-align: center;
        }

        .message {
            font-size: 18px;
            text-align: center;
        }
    </style>
</head>
<body>

    @if( app('request')->input('type') === 'expired' )
        <div class="flex-center position-ref full-height">
            <div class="code">
                Ups!
            </div>
            <div class="message" style="padding: 10px;">
                @if( app('request')->input('lang') === 'es' )
                     El moderador a finalizado la presentación
                @elseif(app('request')->input('lang') === 'pt')
                    O moderador terminou a apresentação
                @elseif(app('request')->input('lang') === 'it')
                    Il moderatore ha terminato la presentazione
                @else
                    The moderator has finished the presentation
                @endif
            </div>
            <div>
                <a href="{{ app('request')->input('redirect') }}">
                    @if( app('request')->input('lang') === 'es' )
                        Intentar acceder
                    @elseif(app('request')->input('lang') === 'pt')
                        Tente acessar
                    @elseif(app('request')->input('lang') === 'it')
                        Prova ad accedere
                    @else
                        Try to access
                    @endif
                </a>
            </div>
        </div>
    @else
        <div class="flex-center position-ref full-height">
            <div class="code">
                404
            </div>
            <div class="message" style="padding: 10px;">
                Not Found
            </div>
        </div>
    @endif

</div>


</body>

</html>
