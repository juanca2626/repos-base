<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Mailing </title>
    <style type="text/css">
        td, table, tr {
            border: 0;
            padding: 10px;
        }
    </style>
</head>
<body style="margin: 0;">
<table border="0" cellpadding="0" cellspacing="0" bgcolor="#e0e2e6"
       style="width:100%;margin:0;padding: 0; background-color:#e0e2e6;">
    <tr style="border:0;">
        <td style="width: 600px; border:0;">
            <table style="width: 600px; margin: 0 auto; background: #ffffff;">
{{--                @if(!\Illuminate\Support\Facades\App::environment('production'))--}}
{{--                    <tr style="background-color: #fff3cd;border-color: #ffeeba; border: 0;">--}}
{{--                        <td colspan="2" style="text-align: center">--}}
{{--                            <h3 style="text-align: center;color: #856404;margin: 0px;text-transform:uppercase;">{{trans('mails.test_message',[],$reservation['lang'])}}</h3>--}}
{{--                        </td>--}}
{{--                    </tr>--}}
{{--                @endif--}}
                @if( isset($reservation['comment']) && $reservation['comment'] !== '' )
                <tr style="border: 1px solid #93a3a3;background: #f3ffd5;">
                    <td colspan="2">
                        <h1 style="color:#393333; padding: 0 0 0 20px; text-align: left; font-family:'Calibri', Helvetica, Arial, sans-serif;text-transform:uppercase;font-size:16px;margin:0 0 3px 0;letter-spacing: 2px;font-weight: normal">
                        @if( $reservation['mail_config_to'] && $reservation['mail_config_to'] !== "hotel" )
                            <span>
                                Mensaje para el proveedor:
                            </span>
                        @endif
                           {{ $reservation['comment'] }}
                        </h1>
                    </td>
                </tr>
                @endif
                <tr style="background: #a11216; border: 0;">
                    <td>
                        <br/>
                        <h1 style="color:#ffffff; padding: 0 0 0 20px; text-align: left; font-family:'Calibri', Helvetica, Arial, sans-serif;text-transform:uppercase;font-size:16px;margin:0 0 3px 0;letter-spacing: 2px;font-weight: normal">
                            @if (isset($reservation['mail_type']) && $reservation['mail_type'] === 'confirmation')
                                @if (isset($reservation['lang']))
                                    @switch($reservation['lang'])
                                        @case('en')
                                            Your reservation has been created.
                                            @break
                                        @case('es')
                                            Tu reserva ha sido creada.
                                            @break
                                        @case('pt')
                                            Sua reserva foi criada.
                                            @break
                                        @default
                                            {{ $reservation['mail_type'] }}
                                    @endswitch
                                @else
                                    {{ $reservation['mail_type'] }}
                                @endif
                            @else
                                {{ $reservation['mail_type'] }}
                            @endif
                        </h1>
{{--                        @if($reservation['mail_type'] === 'summary')--}}
{{--                            <h2 style="color:#ffffff; padding: 0 0 0 20px; text-align: left; font-family:'Calibri', Helvetica, Arial, sans-serif;text-transform: inherit;font-weight: normal;font-size:14px;margin:0;letter-spacing: 2px;">--}}
{{--                                Transaction Code :{{ $reservation['booking_code'] }}--}}
{{--                            </h2>--}}
{{--                        @else--}}
{{--                            <h2 style="color:#ffffff; padding: 0 0 0 20px; text-align: left; font-family:'Calibri', Helvetica, Arial, sans-serif;text-transform: inherit;font-weight: normal;font-size:14px;margin:0;letter-spacing: 2px;">--}}
{{--                                Transaction Code :{{ $reservation['booking_code'] }}--}}
{{--                            </h2>--}}
                            <h2 style="color:#ffffff; padding: 0 0 0 20px; text-align: left; font-family:'Calibri', Helvetica, Arial, sans-serif;text-transform: inherit;font-weight: normal;font-size:14px;margin:0;letter-spacing: 2px;">
                                File Number :{{ $reservation['file_code'] }}
                            </h2>
{{--                        @endif--}}
                        <br/>
                    </td>
                    <td style="text-align: right; padding: 0 10px 0 0; vertical-align: middle;">
                        <img src="https://aurora.limatours.com.pe/images/logo.jpg" alt=""
                             style="width: 145px;"/>
                    </td>
                </tr>
                <tr>
                    <td>
                        @yield('content')
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
