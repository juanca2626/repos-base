<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

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
                                <th style="font-family: Arial, &#039;Helvetica Neue&#039;, Helvetica, sans-serif; padding-top: 35px; width: 180px; vertical-align: top;">
                                    File                                                                                                                                
                                </th>
                                <td style="font-family: Arial, &#039;Helvetica Neue&#039;, Helvetica, sans-serif; padding-top: 35px; width: 180px; vertical-align: top;">{{ $reservations['file'] }}</td>
                            </tr>

                            <tr>
                                <th style="font-family: Arial, &#039;Helvetica Neue&#039;, Helvetica, sans-serif; padding-top: 35px; width: 180px; vertical-align: top;">
                                    Hotel                                                                                                                                
                                </th>
                                <td style="font-family: Arial, &#039;Helvetica Neue&#039;, Helvetica, sans-serif; padding-top: 35px; width: 180px; vertical-align: top;">{{ $reservations['hotel_name'] }}</td>
                            </tr>

                            <tr>
                                <th style="font-family: Arial, &#039;Helvetica Neue&#039;, Helvetica, sans-serif; padding-top: 35px; width: 180px; vertical-align: top;">
                                    Check In                                                                                                                                
                                </th>
                                <td style="font-family: Arial, &#039;Helvetica Neue&#039;, Helvetica, sans-serif; padding-top: 35px; width: 180px; vertical-align: top;">{{ $reservations['date_from'] }}</td>
                            </tr>

                            <tr>
                                <th style="font-family: Arial, &#039;Helvetica Neue&#039;, Helvetica, sans-serif; padding-top: 35px; width: 180px; vertical-align: top;">
                                    Check Out                                                                                                                               
                                </th>
                                <td style="font-family: Arial, &#039;Helvetica Neue&#039;, Helvetica, sans-serif; padding-top: 35px; width: 180px; vertical-align: top;">{{ $reservations['date_to'] }}</td>
                            </tr>

                            <tr>
                                <th style="font-family: Arial, &#039;Helvetica Neue&#039;, Helvetica, sans-serif; padding-top:35px; width: 180px; vertical-align: top;">
                                    Request                                                                                                                                
                                </th>
                                <td style="font-family: Arial, &#039;Helvetica Neue&#039;, Helvetica, sans-serif; padding-top: 35px; width: 180px; vertical-align: top;">{{ $log_request }}</td>
                            </tr>
                            
                            <tr>
                                <th style="font-family: Arial, &#039;Helvetica Neue&#039;, Helvetica, sans-serif; padding-top: 35px; width: 180px; vertical-align: top;">
                                    Response                                                                                                                                
                                </th>
                                <td style="font-family: Arial, &#039;Helvetica Neue&#039;, Helvetica, sans-serif; padding-top: 35px; width: 180px; vertical-align: top;">{{ $log_response }}</td>
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
