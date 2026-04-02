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
                                        Ha llegado una nueva reserva de TourCMS a Central OTS (Aurora):
                                    </h1>
                                    <p style="color: #74787E; font-size: 16px; line-height: 1.5em;text-align:center;padding:1em 0;">
                                        <strong style="font-size:18px;display:block;">Booking Details</strong>
                                        <span>Agent: <strong>{{ $data['agent_name'] }}</strong></span>
                                    </p>
                                    <ul style="color: #74787E; font-size: 15px; line-height: 1.5em;padding:0 0 1.5em 0;margin:0;">
                                        <li style="list-style: none;padding: 0;margin: 0; line-height:1.5em;">
                                            Tour name: <strong>{{ $data['booking_name'] }}</strong>
                                        </li>
                                        <li style="list-style: none;padding: 0;margin: 0; line-height:1.5em;">
                                            Travel Date: <strong>{{ $data['start_date'] }}</strong>
                                        </li>
                                        <li style="list-style: none;padding: 0;margin: 0; line-height:1.5em;">
                                            Lead Traveler Name: <strong>{{ $data['lead_customer_name'] }}</strong>
                                        </li>
                                        <li style="list-style: none;padding: 0;margin: 0; line-height:1.5em;">
                                            Travelers: <strong>{{ $data['customers_agecat_breakdown'] }}</strong>
                                        </li>
                                        <li style="list-style: none;padding: 0;margin: 0; line-height:1.5em;">
                                            Product Code: <strong>{{ $data['product_code'] }}</strong>
                                        </li>
                                        <li style="list-style: none;padding: 0;margin: 0; line-height:1.5em;">
                                            Booking ID: <strong>{{ $data['agent_ref'] }}</strong>
                                        </li>

                                    </ul>

                                    <h1 style="margin-top: 0; color: #2F3133; font-size: 16px; font-weight: bold; text-align: left;">
                                        Toque el siguiente link para darle seguimiento:
                                    </h1>
                                    <p style="margin-top: 0px; color: #74787E; font-size: 19px; line-height: 1.5em; text-align: center;">
                                        <a href="https://aurora.limatours.com.pe/central_bookings/tourcms" target="_blank">Central Reservas OTS</a>
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
