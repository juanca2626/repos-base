<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    <style type="text/css" rel="stylesheet" media="all">
        /* Media Queries */
        @media only screen and (max-width: 500px) {
            .button {
                width: 100% !important;
            }
        }
    </style>
</head>

<body style="margin: 0; padding: 0; width: 100%; background-color: #F2F4F6;">
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td style="width: 100%; margin: 0; padding: 0; background-color: #F2F4F6;" align="center">
            <table width="100%" cellpadding="0" cellspacing="0">

                <!-- Logo -->
                <tr>
                    <td style="padding: 25px 0; text-align: center;">
                        <a style="font-family: Arial, &#039;Helvetica Neue&#039;, Helvetica, sans-serif; font-size: 16px; font-weight: bold; color: #2F3133; text-decoration: none; text-shadow: 0 1px 0 white;"
                           href="{{ url('/') }}" target="_blank">
                            <img src="{{ asset('images/logo.png') }}" width="300" title="LimaTours" alt="LimaTours">
                        </a>
                    </td>
                </tr>

                <!-- Email Body -->
                <tr>
                    <td style="width: 100%; margin: 0; padding: 0; border-top: 1px solid #EDEFF2; border-bottom: 1px solid #EDEFF2; background-color: #FFF;"
                        width="100%">
                        <table style="width: 100%; max-width: 660px; margin: 0 auto; padding: 0;" align="center"
                               width="660" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="font-family: Arial, &#039;Helvetica Neue&#039;, Helvetica, sans-serif; padding: 35px;">
                                    <!-- Greeting -->
                                    <h1 style="margin-top: 0; color: #2F3133; font-size: 16px; font-weight: bold; text-align: left;">
                                        El sistema no encontró precios en algunos paquetes y los siguientes planes tarifarios fueron desactivados:</h1>
                                    <p style="margin-top: 0px; color: #74787E; font-size: 19px; line-height: 1.5em; text-align: center;">

                                    </p>
                                    <hr>
                                    <p style="color: #74787E; font-size: 14px; line-height: 1.5em;">
                                        @foreach( $data as $pack )
                                            <span style="font-size: 19px; !important;background: #890005; color: white;padding: 2px 7px;border-radius: 4px;">◙ {{ $pack['code'] }} - {{ $pack['name'] }}</span><br>
                                            @foreach( $pack['plan_rates'] as $plan_rate )
                                                <span style="margin-left: 20px; background: #e2ffff;">
                                                        - PLAN TARIFARIO: "({{  $plan_rate['id'] }}) {{ $plan_rate['name'] }}"
                                                </span><br>
                                                @foreach( $plan_rate['categories'] as $category )
                                                    <span style="margin-left: 40px;">
                                                         O CATEGORÍA: "({{  $category['id'] }}) {{ $category['name'] }}"
                                                    </span><br>
                                                    @foreach( $category['services'] as $service )
                                                        <span style="margin-left: 60px;">
                                                            <b>[{{ strtoupper( $service['type'] ) }}]</b> {{  $service['date'] }} - ({{ $service['code'] }})
                                                        </span>
                                                        <br>
                                                        <span style="margin-left: 60px;">
                                                            {{ $service['name'] }}
                                                        </span>
                                                        <br>
                                                        <span style="margin-left: 60px;">
                                                            <b style="font-size: 12px !important;">({{ $service['error'] }})</b>
                                                        </span><br>
                                                    @endforeach
                                                @endforeach
                                                <br>
                                            @endforeach
                                        @endforeach
                                    </p>
                                    <table width="20" border="0" cellpadding="0" cellspacing="0" align="left"
                                           style="border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt;"
                                           class="erase">
                                        <tbody>
                                        <tr>
                                            <td width="100%" height="10" style="font-size: 1px; line-height: 1px;"
                                                class="erase">&nbsp;
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>

                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td>
                        <table style="width: auto; max-width: 660px; margin: 0 auto; padding: 0; text-align: center;"
                               align="center" width="660" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="font-family: Arial, &#039;Helvetica Neue&#039;, Helvetica, sans-serif; color: #AEAEAE; padding: 35px; text-align: center;">
                                    <p style="margin-top: 0; color: #74787E; font-size: 12px; line-height: 1.5em;">
                                        &copy; {{ date('Y') }}
                                        <a style="color: #3869D4;" href="{{ url('/') }}" target="_blank">LimaTours</a>.
                                        Todos los derechos reservados.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>
</body>
</html>
