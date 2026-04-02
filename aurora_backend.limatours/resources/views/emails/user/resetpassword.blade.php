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

<body style="margin: 0; padding: 0; width: 100%; background-color: #ffffff;">
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td style="width: 100%; margin: 0; padding: 0; background-color: #ffffff;" align="center">
            <table width="100%" cellpadding="0" cellspacing="0">

                <!-- Logo -->
                <tr>
                    <td style="padding: 25px 0; text-align: center;">
                        <a style="font-family: Arial, &#039;Helvetica Neue&#039;, Helvetica, sans-serif; font-size: 16px; font-weight: bold; color: #2F3133; text-decoration: none; text-shadow: 0 1px 0 white;"
                           href="{{ url('/') }}" target="_blank">
                            <img src="{{ asset('images/logo_aurora.png') }}" width="300" title="Aurora - LimaTours" alt="Aurora - LimaTours">
                        </a>

                    </td>
                </tr>

                <!-- Email Body -->
                <tr>
                    <td style="width: 100%; margin: 0; padding: 0; border-top: 1px solid #ffffff;background-color: #ffffff;"
                        width="100%">
                        <table style="width: 100%; max-width: 660px; margin: 0 auto; padding: 0;" align="center"
                               width="660" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="font-family: Arial, &#039;Helvetica Neue&#039;, Helvetica, sans-serif; padding: 2px;padding-bottom: 32px;border-bottom: 15px solid #991F1C;">
                                    <!-- Greeting -->
                                    <p style="margin-top: 0; color: #74787E; font-size: 14px; line-height: 1.5em; text-align: left;">
                                        {{trans('password.forgot_password.messages.hello',[],$lang)}}
                                    </p>

                                    <!-- Intro -->
                                    <p style="margin-top: 0; color: #74787E; font-size: 14px; line-height: 1.5em; text-align: left;">
                                        {{trans('password.forgot_password.messages.we_received',[],$lang)}}
                                    </p>

                                    <p style="margin-top: 60px; color: #74787E; font-size: 19px; line-height: 1.5em; text-align: center;">

                                    </p>

                                    <table width="300" border="0" cellpadding="0" cellspacing="0" align="center"
                                           style="text-align: center; border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt;"
                                           class="fullCenter">
                                        <tr>
                                            <td width="100%" align="center">
                                                <table border="0" cellpadding="0" cellspacing="0" align="center"
                                                       class="buttonScale">
                                                    <tr>
                                                        <td align="center" height="40" bgcolor="#991F1C"
                                                            style=" padding-left: 25px; padding-right: 25px; font-family: 'Open Sans', Helvetica, Arial, sans-serif; color: rgb(255, 255, 255); font-size: 13px; font-weight: 700; line-height: 1px; background-color: #991F1C;">

                                                            <a href="{{ $link }}"
                                                               style="color: rgb(255, 255, 255); text-decoration: none; width: 100%;">{{trans('password.forgot_password.reset_password',[],$lang)}}</a>

                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                    <!-- Salutation -->
                                    <p style="margin-top: 60px; color: #74787E; font-size: 14px; line-height: 1.5em; text-align: left;">
                                        {{trans('password.forgot_password.messages.this_password_reset',[],$lang)}} <span style="color: #991F1C; font-size: 14px;font-weight: bold;text-decoration: underline;">{{trans('password.forgot_password.messages.expire_in',[],$lang)}}</span>
                                    </p>
                                    <p style="margin-top: 0px; color: #74787E; font-size: 14px; line-height: 1.5em; text-align: left;">
                                        {{trans('password.forgot_password.messages.if_you_did_not',[],$lang)}}
                                    </p>
                                    <p style="margin-top: 0px; color: #74787E; font-size: 14px; line-height: 1.5em; text-align: left;">
                                        {{trans('password.forgot_password.messages.king_regards',[],$lang)}}
                                    </p>
                                    <p style="margin-top: 0px; color: #991F1C; font-size: 14px; line-height: 1.5em;font-weight: bold; text-align: left;">
                                        {{trans('password.forgot_password.messages.your_friends',[],$lang)}}
                                    </p>
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
                                        {{trans('password.forgot_password.messages.all_rights_reserved',[],$lang)}}
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
