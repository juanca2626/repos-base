<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    <style type="text/css" rel="stylesheet" media="all">
        /* Media Queries */
        @media  only screen and (max-width: 500px) {
            .button {
                width: 100% !important;
            }
        }
    </style>
</head>

<body style="margin: 0; padding: 0; width: 100%; background-color: #F2F4F6;">

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    <style type="text/css" rel="stylesheet" media="all">
        /* Media Queries */
        @media  only screen and (max-width: 500px) {
            .button {
                width: 100% !important;
            }
        }
    </style>
</head>

<body style="margin: 0; padding: 0; width: 100%; background-color: #FFF;">

<div style="font-family:verdana,sans-serif">
    <span style="color:rgb(80,0,80)">
        <div style="text-align:center">
            <a style="font-family: Arial, &#039;Helvetica Neue&#039;, Helvetica, sans-serif; font-size: 16px; font-weight: bold; color: #2F3133; text-decoration: none; text-shadow: 0 1px 0 white;" href="{{ url('/') }}" target="_blank">
                <img src="{{ asset('images/logo.png') }}" style="max-height:50px;max-width:100%;" title="LimaTours" alt="LimaTours" />
            </a>
        </div>
{{--        <h2 style="text-align:center;line-height:24.375px">--}}
        {{--            <span style="color:rgb(0,0,0)">RECORDATORIO:</span>--}}
        {{--        </h2>--}}
        <h2 style="text-align:center;line-height:24.375px">
            <span style="color:rgb(204,2,1)">NOTIFICACION POR INVENTARIO DE HABITACION - HOTEL {{ $hotel['name'] }}</span>
        </h2>
        <div>
            <span style="color:rgb(204,2,1)">
                <br>
            </span>
        </div>
    </span>
    <div style="color:rgb(80,0,80)">
        <span style="color:rgb(204,2,1)"><br></span>
    </div>
    <p style="margin:0cm 0cm 8pt;font-family:Calibri,sans-serif;text-align:center;line-height:13.91px">
        <font size="4" color="#000000">
            <br>
        </font>
    </p>
    <p style="margin:0cm 0cm 8pt;font-family:Calibri,sans-serif;text-align:center;line-height:13.91px">
        Hola, necesitamos su colaboración para ingresar los inventarios del hotel <b>{{ $hotel['name'] }}</b>, en las siguientes tarifas:<br />
        <ul>
            @foreach($rate_name as $rate)
                <li><b>{{ $rate }}</b></li>
            @endforeach
        </ul>
    </p>

    <p style="margin:0cm 0cm 8pt;font-family:Calibri,sans-serif;text-align:center;line-height:13.91px">
        Para completar la información pendiente, haga click en el siguiente enlace:
    </p>

    <p style="text-align:center">
        <a href="{{ url('/#/hotels/' . $hotel['id'] . '/manage_hotel/inventories') }}"
           target="_blank"
           style="display:block;padding:15px;background-color:#BF0000;color:#FFF;border-radius:10px;">
            Completar los datos
        </a>
    </p>

    <p style="text-align:center">El equipo de LimaTours</p>
</div>
</body>
</html>
