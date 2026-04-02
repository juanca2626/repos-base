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
                                <td style="font-family: Arial, &#039;Helvetica Neue&#039;, Helvetica, sans-serif; padding: 35px;">
                                    <!-- User -->
                                    <p style="margin-top: 0; color: #74787E; font-size: 16px; line-height: 1.5em; text-align: left;">
                                        {{$data['user_from']}}  {{ trans('quote.has_shared_a_quote_with_you') }}
                                    </p>
                                    <p style="margin-top: 0px; color: #74787E; font-size: 19px; line-height: 1.5em; text-align: center;">

                                    </p>
                                    <hr>
                                    <table width="300" border="0" cellpadding="0" cellspacing="0" align="center"
                                           style="text-align: center; border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt;"
                                           class="fullCenter">
                                        <tr>
                                            <td width="100%" align="center">
                                                <table border="0" cellpadding="0" cellspacing="0" align="center"
                                                       class="buttonScale">
                                                    <tr>
                                                        <td valign="middle" width="100%" height="40"
                                                            style="text-align: left; font-family: 'Open Sans', Helvetica, Arial, sans-serif; color: rgb(50, 50, 50); font-size: 17px; font-weight: 300; line-height: 30px;"
                                                            class="fullCenter" mc:edit="8">
                                                            <table style="width: 600px; margin: 0 auto; background: #ffffff;">
                                                                <tr>
                                                                    <td><b>{{ trans('quote.quotation') }} N°: {{$data['quote_id']}} </b></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><b>{{ trans('quote.date') }}: {{$data['quote_date']}}</b></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><b>{{ trans('quote.name') }}: {{$data['quote_name']}}</b></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><b>{{ trans('quote.name') }}: {{ $data['quote_client'] }}</b></td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                    <p style="margin-top: 50px; color: #74787E; font-size: 19px; line-height: 1.5em; text-align: center;">
                                        <a href="https://aurora.limatours.com.pe/" class="btn btn-primary">{{ trans('quote.open') }}</a>
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
