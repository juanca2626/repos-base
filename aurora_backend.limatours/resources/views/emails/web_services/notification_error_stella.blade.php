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
                           href="https://aurora.limatours.com.pe" target="_blank">
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
                                    <h1 style="margin-top: 0; color: #2F3133; font-size: 19px; font-weight: bold; text-align: center;color: #BD0D12 !important;">
                                        No se pudo procesar el File: {{$reservation->file_code}}</h1>
                                    <!-- Intro -->
                                </td>
                            </tr>
                        </table>
                        <table width="600" border="0" cellpadding="0" cellspacing="0" align="center"
                               style="text-align: center; border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt;margin-bottom: 20px;background: aliceblue;"
                               class="fullCenter">
                            <tr>
                                <td width="100%" align="center">
                                    <table border="0" cellpadding="0" cellspacing="0" align="center"
                                           class="buttonScale">
                                        <tr>
                                            <td valign="middle" width="100%" height="40"
                                                style="text-align: left; font-family: 'Open Sans', Helvetica, Arial, sans-serif; color: rgb(50, 50, 50); font-size: 17px; font-weight: 300; line-height: 30px;"
                                                class="fullCenter" mc:edit="8">
                                                <p style="padding: 10px;margin: 0px !important;"><b>Proceso:</b>
                                                    <span style="color: #7f8c8d;">
                                                        @if($reservation['status_cron_job_reservation_stella'] == 1)
                                                            Creacion de datos de facturación
                                                        @endif
                                                        @if($reservation['status_cron_job_reservation_stella'] == 2)
                                                            Creacion de File
                                                        @endif
                                                        @if($reservation['status_cron_job_reservation_stella'] == 3)
                                                            Creacion de asiento contable
                                                        @endif
                                                    </span>
                                                </p>
                                                <p style="padding: 10px;margin: 0px !important;"><b>Reserva realizada
                                                        por:</b>
                                                    <span style="color: #7f8c8d;">
                                                        @if($reservation['reservator_type'] == 'client')
                                                            Client
                                                        @endif
                                                        @if($reservation['reservator_type'] == 'excecutive')
                                                            Ejecutiva
                                                        @endif
                                                    </span>
                                                </p>
                                                <p style="padding: 10px;margin: 0px !important;"><b>Ejecutiva:</b>
                                                    <span style="color: #7f8c8d;">
                                                        {{$reservation['executive']['code']}} - {{$reservation['executive']['name']}} <br>
                                                        <small
                                                            style="font-size: 10px">{{$reservation['executive']['email']}}</small>
                                                    </span>
                                                </p>
                                                <p style="padding: 10px;margin: 0px !important;"><b>Cliente:</b>
                                                    <span style="color: #7f8c8d;">
                                                        {{$reservation['client']['code']}} - {{$reservation['client']['name']}}
                                                    </span>
                                                </p>
                                                <p style="padding: 10px;margin: 0px !important;"><b
                                                        style="color: darkred !important;">Error response:</b>
                                                    <span style="color: #7f8c8d;">
                                                        {{ json_encode($logs->response_data) }}
                                                    </span>
                                                </p>
                                            </td>
                                        </tr>
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
                                        <a style="color: #3869D4;" href="https://aurora.limatours.com.pe"
                                           target="_blank">LimaTours</a>.
                                        All rights reserved.
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
