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
                            <img src="https://aurora.limatours.com.pe/images/logo.jpg" width="300" title="LimaTours" alt="LimaTours">
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
                                        Estimad@s colegas de Marketing,
                                    </h1>

                                    <p style="margin-top: 0px; color: #74787E; font-size: 19px; line-height: 1.5em; text-align: center;">
                                        <b>{{ $data['who'] }}</b> ha realizado un ajuste en el texto de las notas de:

                                        @if($data['rate_id']!="")
                                            <br>
                                            La tarifa: <b>"{{ $data['rate_name'] }}"</b><br>
                                            <a href="https://backend.limatours.com.pe/#/hotels/{{ $data['hotel_id'] }}/manage_hotel/rates/rates/cost/edit/{{ $data['rate_id'] }}" target="_blank">Ver Tarifa en el Backend</a>
                                        @endif
                                        <br>
                                        El Hotel: <b>"{{ $data['hotel_name'] }}"</b><br>
                                        <a href="https://backend.limatours.com.pe/#/hotels/edit/{{ $data['hotel_id'] }}" target="_blank">Editar Hotel</a>
                                    </p>
                                    @if($data['rate_id']!="")
                                        <h2>Y se solicita su revisión en cuanto a los textos generales del Hotel.</h2>
                                    @else
                                        <h2>Y se solicita su revisión.</h2>
                                    @endif
                                    <br>
                                    <h2>¡Gracias!.</h2>

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
