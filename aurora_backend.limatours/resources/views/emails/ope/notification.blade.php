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
        .button {
            font-size: 16px;
            font-weight: 700;
            line-height: 30px;
            display: inline-block;
            text-align: center;
            text-decoration: none;
            color: #fff;
            border: 0;
            border-radius: 5px;
            background-color: #EB5757;
            -webkit-text-size-adjust: none; 
            padding: 6px 34px;
        }

    </style>
</head>

<body style="margin: 0; padding: 0; width: 100%; background-color: #F2F4F6;">
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td style="width: 100%; margin: 0; padding: 0; background-color: #F2F4F6;">
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
                    <td style="width: 100%; margin: 0; padding-top: 1rem; background-color: #FFF;" width="100%">
                        <table style="width: 100%; max-width: 660px; margin: 0 auto; padding: 0;" width="660" cellpadding="0" cellspacing="0">
                            <tr>
                                <td>
                                   {!! $content !!}
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
