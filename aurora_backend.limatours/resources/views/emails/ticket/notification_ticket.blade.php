<!DOCTYPE html>
<html lang="es">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title>Notificación Ticket</title>
</head>
<body style="margin: 0; padding: 0; width: 100%; background-color: #F2F4F6;">
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td style="width: 100%; margin: 0; padding: 0; background-color: #F2F4F6;" align="center">
            <table width="100%" cellpadding="0" cellspacing="0">

                <!-- Logo -->
                <tr>
                    <td style="padding: 25px 0; text-align: center;">
                        <a href="{{ url('/') }}" target="_blank" style="font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; font-size: 16px; font-weight: bold; color: #2F3133; text-decoration: none; text-shadow: 0 1px 0 white;">
                            <img src="{{ asset('images/logo.png') }}" width="300" alt="LimaTours">
                        </a>
                    </td>
                </tr>

                <!-- Email Body -->
                <tr>
                    <td style="width: 100%; margin: 0; padding: 0; border-top: 1px solid #EDEFF2; border-bottom: 1px solid #EDEFF2; background-color: #FFF;">
                        <table style="width: 100%; max-width: 660px; margin: 0 auto; padding: 0;" align="center" width="660" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; padding: 35px;">
                                    <!-- Greeting -->
                                    <h1 style="margin-top: 0; color: #2F3133; font-size: 19px; font-weight: bold; text-align: left;">
                                        Usuario:
                                    </h1>

                                    <!-- User Information -->
                                    <p style="margin-top: 0; color: #74787E; font-size: 16px; line-height: 1.5em; text-align: left;">
                                        El usuario [{{$data['reservation']['create_user']['code']}}] - {{ $data['reservation']['create_user']['name'] ?? 'N/A' }}
                                        @if ($data['type'] === 'file')
                                            solicita la cancelación del file.
                                        @elseif ($data['type'] === 'service')
                                            solicita la cancelación de un servicio.
                                        @endif
                                    </p>
                                    <hr>

                                    <!-- Ticket Details -->
                                    <table width="300" cellpadding="0" cellspacing="0" align="center" style="text-align: center; border-collapse: collapse; background: #ffffff; margin: 20px auto;">
                                        <tr>
                                            <td align="left" style="font-family: 'Open Sans', Helvetica, Arial, sans-serif; font-size: 16px; color: #323232; padding: 10px 0;">
                                                <b>ID:</b> {{ e($data['id']) ?? 'N/A' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="left" style="font-family: 'Open Sans', Helvetica, Arial, sans-serif; font-size: 16px; color: #323232; padding: 10px 0;">
                                                <b>File:</b> {{ e($data['file_code']) ?? 'N/A' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="left" style="font-family: 'Open Sans', Helvetica, Arial, sans-serif; font-size: 16px; color: #323232; padding: 10px 0;">
                                                <b>Cliente:</b> {{ e($data['client']['code'] ?? 'N/A') }} - {{ e($data['client']['name'] ?? 'N/A') }}
                                            </td>
                                        </tr>
                                        @if ($data['type'] === 'service')
                                            <tr>
                                                <td align="left" style="font-family: 'Open Sans', Helvetica, Arial, sans-serif; font-size: 16px; color: #323232; padding: 10px 0;">
                                                    <b>Servicio:</b> {{ e($data['service']['id'] ?? 'N/A') }} - {{ e($data['service']['aurora_code'] ?? 'N/A') }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td align="left" style="font-family: 'Open Sans', Helvetica, Arial, sans-serif; font-size: 16px; color: #323232; padding: 10px 0;">
                                                    <b>Fecha de Servicio:</b> {{ e($data['date_service'] ?? 'N/A') }}
                                                </td>
                                            </tr>
                                        @endif
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td>
                        <table style="width: auto; max-width: 660px; margin: 0 auto; padding: 0; text-align: center;" align="center" width="660" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; color: #AEAEAE; padding: 35px; text-align: center;">
                                    <p style="margin-top: 0; color: #74787E; font-size: 12px; line-height: 1.5em;">
                                        &copy; {{ date('Y') }}
                                        <a href="{{ url('/') }}" target="_blank" style="color: #3869D4;">LimaTours</a>. Todos los derechos reservados.
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
