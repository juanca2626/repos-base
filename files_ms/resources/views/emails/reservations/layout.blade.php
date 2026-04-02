<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,600,700,800&display=swap" rel="stylesheet">
    <style type="text/css">

        html {
            width: 100%;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            font-weight: 100;
            width: 100% !important;
            margin: 0;
            padding: 0;
            color: #FAFAFA;
            background-color: #ffffff;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        body > table {
            background-color: #FAFAFA;
        }

        img {
            display: block !important;
            height: auto !important;
            margin: 0;
            padding: 0;
            text-decoration: none;
            border: 0;
            outline: none;
            text-align: center;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: 'Montserrat', sans-serif;
            font-weight: bold;
            color: #5b5b5f;
        }

        p,
        ul,
        ol,
        small {
            font-family: 'Montserrat', sans-serif;
            font-weight: 300;
            text-align: left;
            color: #5b5b5f;
        }

        a {
            color: #006dc7;
        }

        table td {
            border-collapse: collapse;
        }

        table {
            table-layout: fixed;
        }

        /* table table table {
            table-layout: auto;
        } */

        .avatar-preview {
            width: 110px;
            height: 110px;
            position: relative;
            border-radius: 100%;
            border: 1px solid #F8F8F8;
            /*box-shadow: 0px 2px 4px 0px rgba(0, 0, 0, 0.1);*/
        }
        .avatar-img {
            width: 100%;
            height: 100%;
            border-radius: 100%;
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
        }

        .media-wrapper {
            margin: 0 auto;
            width: 100%;
        }

        .video-wrapper {
            position: relative;
            /*padding-top: 56.25%;*/
        }

        .video-wrapper__video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        #outlook a {
            padding: 0;
            color: #fff !important;
        }

        .preheader {
            font-size: 12px;
            margin: 0;
            padding: 10px 20px;
            color: #79818a;
            border: 0;
        }

        .preheader p,
        .preheader a {
            font-size: 12px;
        }

        .branding {
            padding: 20px 20px;
            background-color: #30363d;
        }

        .full_width,
        .full_wrapper {
            max-width: 600px;
        }

        .full_width_text {
            padding: 30px 50px 30px 50px;
            background-color: #ffffff !important;
            border-radius: 6px;
        }

        .branding .mb_hide,
        .branding .mb_hide a {
            color: #ffffff;
        }

        .button {
            font-size: 18px;
            font-weight: 600;
            line-height: 30px;
            display: inline-block;
            text-align: center;
            text-decoration: none;
            color: #fff;
            border: 0;
            border-radius: 5px;
            background-color: #EB5757;
            padding: 1rem 3rem;
            -webkit-text-size-adjust: none;
            /* mso-hide: all; */
        }

        /*--------------------------------------------------------*/
        .disclaimer {
            padding: 30px 20px 20px 20px;
            color: #006dc7;
            text-align: left;
            color: white;
        }

        .disclaimer h5 {
            font-size: 14px;
            font-weight: 100;
            margin: 0;
            margin-bottom: 20px;
            padding: 0;
            color: #30363d;
            text-align: left;
        }

        .disclaimer p {
            font-size: 11px;
            font-weight: 100;
            margin: 0;
            margin-bottom: 10px;
            padding: 0;
            color: #a9adb7;
            text-align: left;
        }

        @media only screen and (max-width: 700px) {
            table[class='full_width'],
            table[class='table-inner'] {
                width: 100% !important;
                max-width: 600px !important;
            }

            table[class='full_wrapper'] {
                width: 100% !important;
                max-width: 600px !important;
            }
        }

        @media only screen and (max-width: 599px) {
            table[class='table-full'] {
                width: 100% !important;
                margin: 0 2% 0 2% !important;
            }

            *[class='mb_hide'] {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<table border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#eeeeee">
    <tr>
        <td align="center" valign="top" width="100%">
            <table align="center" border="0" cellpadding="0" cellspacing="0" width="600" class="full_wrapper">

                <!-- Section: Start: Preheader -->
                <tr class="mb_hide" mc:hideable>
                    <td align="center"  valign="top">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%" class="full_width">
                            <tr>
                                <td valign="top" class="preheader">
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="full_width">
                                        <tr>
                                            <td valign="top" align="left" mc:edit="module_preheader">
                                                {{--                                                <p><strong>Reserva confirmada [309569] - CARLOS NIETO X2 CUSHSO</strong></p>--}}
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <!-- Section: End: Preheader -->

                <!-- Section: Start: Mensaje proveedor -->
{{--                @if( isset($reservation['comment']) && $reservation['comment'] !== '' )--}}
{{--                    <tr style="border: 1px solid #93a3a3;background: #f3ffd5;">--}}
{{--                        <td colspan="2" style="background: #eeeeee;">--}}
{{--                            <h1 style="text-align: center;font-size:16px;margin:0;letter-spacing: 1px;padding: 1.5rem; background: #daefff8c; color: #006dc7; font-weight: 500; border-radius: 0.5rem;border: 1px solid #318edc1a; line-height: 1.5;">--}}
{{--                                @if( isset($reservation['mail_config_to']) && $reservation['mail_config_to'] !== "hotel" )--}}
{{--                                    <span style="color:#0967b4; font-weight: 700;">--}}
{{--                                        Mensaje para el proveedor:--}}
{{--                                    </span>--}}
{{--                                @endif--}}
{{--                                {{ $reservation['comment'] }}--}}
{{--                            </h1>--}}
{{--                        </td>--}}
{{--                    </tr>--}}
{{--                @endif--}}
                <!-- Section: End: Mensaje proveedor -->

                <!-- Section: Start: Space -->
                <tr class="mb_hide" mc:hideable>
                    <td align="center" valign="top">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%" class="full_width">
                            <tr>
                                <td valign="top" class="preheader">
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="full_width">
                                        <tr>
                                            <td valign="top" align="left" mc:edit="module_preheader">
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <!-- Section: End: Space -->
                <!-- Section: Start: Full-width Image -->
                {{-- <tr mc:repeatable="module" mc:variant="module_image_row" mc:hideable>
                    <td align="center" width="100%" class="full_width_image">
                        <table border="0" cellpadding="0" cellspacing="0" width="600" class="full_width">
                            <tr>
                                <td width="100%" valign="top" align="center"
                                    style="position:sticky; transform: translateY(40%); background-color:none">
                                    <img src="https://backend.limatours.com.pe/images/logo.png" alt="#" width="30%"
                                        style="max-width:30%; display:block; width:30%; height:auto; padding-top:0px;box-shadow: 0 4px 4px rgba(0,0,0,0.2);"
                                        mc:label="header_image" mc:edit="module_image" mc:allowdesigner
                                        mc:allowtext />
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr> --}}

                {{-- <tr mc:repeatable="module" mc:variant="module_image_row" mc:hideable>
                    <td align="center" width="100%" class="full_width_image">
                        <table border="0" cellpadding="0" cellspacing="0" width="600" class="full_width">
                            <tr>
                                <td width="100%" valign="top" align="center" style="padding-bottom: -120px; background-color: transparent;">
                                    <table border="0" cellpadding="0" cellspacing="0" align="center" style="box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                                        <tr>
                                            <td align="center">
                                                <img src="https://aurora.limatours.com.pe/images/logo.jpg" alt="#"
                                                     width="180" style="width: 180px; max-width: 180px; height: auto; display: block; border: 0;"
                                                     mc:label="header_image" mc:edit="module_image" mc:allowdesigner mc:allowtext />
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr> --}}

                <tr mc:repeatable="module" mc:variant="module_image_row" mc:hideable>
                    <td align="center" width="100%" class="full_width_image">
                        <table border="0" cellpadding="0" cellspacing="0" width="600" class="full_width">
                            <tr>
                                <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td rowspan="2" valign="top" bgcolor="#ffffff" align="center"><img src="https://aurora.limatours.com.pe/images/logo.jpg" alt="#" width="100%"
                                    style="max-width:100%; display:block; width:100%; height:auto; padding-top:0px;box-shadow: 0 4px 4px rgba(0,0,0,0.2);"
                                    mc:label="header_image" mc:edit="module_image" mc:allowdesigner mc:allowtext/></td>
                                <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                              </tr>
                              <tr>
                                <td bgcolor="#ffffff">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td bgcolor="#ffffff" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                              </tr>
                        </table>
                    </td>
                </tr>

                @yield('content')

                <!-- Section: Start: Space -->
                <tr class="mb_hide" mc:hideable>
                    <td align="center" valign="top">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%" class="full_width">
                            <tr>
                                <td valign="top" class="preheader">
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="full_width">
                                        <tr>
                                            <td valign="top" align="left" mc:edit="module_preheader">
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <!-- Section: End: Space -->
                <!-- Section: Start: Full-width Image -->
                <tr mc:repeatable="module" mc:variant="module_image_row" mc:hideable>
                    <td colspan="2" align="center" width="100%" class="full_width_image">
                        {{-- <table border="0" cellpadding="0" cellspacing="0" width="600" class="full_width">
                            <tr>
                                <td width="100%" valign="top" bgcolor="#ffffff" align="center">
                                    <img src="https://backend.limatours.com.pe/images/banner-correo.png" alt="#"
                                         width="100%"
                                         style="max-width:100%; display:block; width:100%; height:auto; padding-top: 0;"
                                         mc:label="header_image" mc:edit="module_image" mc:allowdesigner mc:allowtext/>
                                </td>
                            </tr>
                        </table> --}}
                        <br>
                        <div
                            style="font-size: 10px; font-weight: 500; text-align: justify; color: #2E2E2E;">
                            En virtud de lo establecido por la disposición de Protección de Datos Personales, Ley
                            Peruana 29733, LIMA TOURS SAC garantiza la confidencialidad de datos de personas naturales.
                            Nuestra única finalidad es prestarle los servicios solicitados, en su condición de cliente,
                            proveedor, o cualquier circunstancia por la que nos contactó. LIMA TOURS SAC evita el envío
                            intencional de correo no deseado, por lo cual es libre de ejercer su derecho ARCO, acceso
                            rectificación, cancelación y oposición de sus datos de carácter personal, para ello puede
                            contactarse al correo dpo@limatours.com.pe.
                        </div>
                    </td>
                </tr>
                <!-- Section: End: Full-width Image -->
                <!-- Section: Start: Space -->
                <tr class="mb_hide" mc:hideable>
                    <td align="center" valign="top">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%" class="full_width">
                            <tr>
                                <td valign="top" class="preheader">
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="full_width">
                                        <tr>
                                            <td valign="top" align="left" mc:edit="module_preheader">
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <!-- Section: End: Space -->
                <!-- Section: Start: Disclaimer -->
                <tr>
                    <td align="center" valign="top">
                        <table border="0" cellpadding="0" cellspacing="0" width="600" class="full_width">
                            <tr>
                                <td valign="top" class="disclaimer" bgcolor="#B5B5B5">
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="full_width"
                                           style="font-size: 14px; text-align: center;">
                                        <tr align="left" style=" font-weight: 800;">
                                            <td style="text-align: center">
                                                <div
                                                    style="display: inline-flex;align-items: center; margin-bottom: 1rem;">
                                                    <a href="/" style="padding: 0 .5rem;">
                                                        <img
                                                            src="https://res.cloudinary.com/litodti/image/upload/v1744314216/aurora/iconos/linkedin.png"
                                                            alt="#"
                                                            width="100%"
                                                            style="display:block; width:20px; height:auto; padding-top: 0;"
                                                            mc:label="header_image" mc:edit="module_image"
                                                            mc:allowdesigner mc:allowtext/>
                                                    </a>
                                                    <a href="/" style="padding: 0 .5rem;">
                                                        <img
                                                            src="https://res.cloudinary.com/litodti/image/upload/v1744314216/aurora/iconos/face.png"
                                                            alt="#"
                                                            width="100%"
                                                            style="display:block; width:20px; height:auto; padding-top: 0;"
                                                            mc:label="header_image" mc:edit="module_image"
                                                            mc:allowdesigner mc:allowtext/>
                                                    </a>
                                                    <a href="/" style="padding: 0 .5rem;">
                                                        <img
                                                            src="https://res.cloudinary.com/litodti/image/upload/v1744314216/aurora/iconos/insta.png"
                                                            alt="#"
                                                            width="100%"
                                                            style="display:block; width:20px; height:auto; padding-top: 0;"
                                                            mc:label="header_image" mc:edit="module_image"
                                                            mc:allowdesigner mc:allowtext/>
                                                    </a>
                                                    <a href="/" style="padding: 0 .5rem;">
                                                        <img
                                                            src="https://res.cloudinary.com/litodti/image/upload/v1744314217/aurora/iconos/vimeo.png"
                                                            alt="#"
                                                            width="100%"
                                                            style="display:block; width:20px; height:auto; padding-top: 0;"
                                                            mc:label="header_image" mc:edit="module_image"
                                                            mc:allowdesigner mc:allowtext/>
                                                    </a>
                                                </div>
                                                <p style="font-weight: 600; text-align: center; color: #ffffff;">
                                                    Juan de Arona 755, Piso 3 - San Isidro - Lima - Perú, +5116196900
                                                </p>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <!-- Section: End: Disclaimer -->
            </table>
        </td>
    </tr>
</table>
</body>
</html>
