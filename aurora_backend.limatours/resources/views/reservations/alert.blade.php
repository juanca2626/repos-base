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
                                    <h1 style="margin-top: 0; font-size: 19px; font-weight: bold; text-align: left;">
                                        Se ejecuto el proceso correctamente 
                                     </h1>
                                
                                    <br />
                                    Por favor esperar que llegue las notificaciones de confirmación de las reservas aproximadamente entre 1 a 5 minutos. 

                                    <br /> <br /> <br /> 
                                    Si todo esto no soluciona el problema, por favor comunicarse con el área de TI creando un ticket en el siguiente link: <a href="https://solutionsdesk.net/" target="_blank">https://solutionsdesk.net/</a> (Proyecto: Aurora, Solicitud Type: Incidencia, Priority: High), y será atendido inmediatamente.
                                    <br /> <br /> <br /> <br /> 
                                 
                                    <!-- Intro -->
                                </td>
                            </tr>

                            <tr>
                                <td align="center">
                                    <a href="{{ config('services.aurora_front.domain') }}" class="button">Regresar a la aplicación</a>
                                    <br /> <br /> <br /> 
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
