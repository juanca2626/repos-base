<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reestablece tu contraseña</title>
    <style>
        /* Estilos básicos */
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            color: #2F3133;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            padding: 20px;
            border-radius: 5px;
        }
        .logo {
            text-align: center;
            padding: 25px 0;
        }
        .logo img {
            width: 300px;
        }
        h1 {
            text-align: center;
            color: #333333;
        }
        p {
            line-height: 1.6;
        }
        .button {
            display: inline-block;
            background-color: #EB5757;
            color: #ffffff;
            padding: 10px 20px;
            margin: 20px auto;
            text-align: center;
            text-decoration: none;
            border-radius: 4px;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #a0a0a0;
            text-align: center;
            border-top: 1px solid #eaeaea;
            padding-top: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Sección del logo -->
    <div class="logo">
        <a href="{{ url('/') }}" target="_blank">
            <img src="{{ asset('/images/logo/logo_nav.jpg') }}" alt="LimaTours">
        </a>
    </div>

    <!-- Contenido principal -->
    <h1>Reestablece tu contraseña</h1>
    <p>Hola,</p>
    <p>
        Recibiste este correo porque se solicitó restablecer la contraseña de tu cuenta.
        Haz clic en el siguiente enlace para reestablecer tu contraseña:
    </p>

    <div style="text-align: center;">
        <a class="button" href="{{ url(route('password.reset', ['token' => $token, 'email' => $email], false)) }}">
            Restablecer Contraseña
        </a>
    </div>

    <p>
        Si no solicitaste este cambio, puedes ignorar este correo.
    </p>
    <p>
        Saludos,<br>
        {{ config('app.name') }}
    </p>

    <!-- Sección de subcopy para casos en los que el botón falle -->
    <div class="footer">
        <p>
            Si tienes problemas haciendo clic en el botón "Restablecer Contraseña", copia y pega la siguiente URL en tu navegador:
        </p>
        <p>
            <a href="{{ url(route('password.reset', ['token' => $token, 'email' => $email], false)) }}">
                {{ url(route('password.reset', ['token' => $token, 'email' => $email], false)) }}
            </a>
        </p>
    </div>
</div>
</body>
</html>
