<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Aurora</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- Fonts -->

    <!-- Styles -->
    <style>
        .wrapper{
            height: 100%;
            overflow: auto;
            width: 100%;
        }

        .login{
            height: 100vh;
        }

        .login .bk-image {
            -o-object-fit: cover;
            object-fit: cover;
            width: 100%;
            height: 100%;
            -o-object-position: center;
            object-position: center;
            position: fixed;
        }

        .login .row {
            height: 100vh;
        }

        .justify-content-center {
            justify-content: center !important;
        }

        .align-items-center {
            align-items: center !important;
        }

        .login-box {
            width: 300px;
            max-width: 100%;
            margin: 0 auto;
            background-color: rgba(0, 0, 0, 0.4);
            border-radius: 15px;
            padding: 15px 32px 44px;
        }

        .carousel-inner{
            height: 150px;
        }
        .carousel-caption{
            color: #fff;
            top: 50%;
            font-size: 40px;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="login">
        <img class="bk-image" src="https://aurora.limatours.com.pe/images/bg-login.jpg">
        <div class="row no-gutters justify-content-center align-items-center">
            <div class="col-md-6">
                <div class="login-box" style="width: 100%;">
                    <div class="login_header text-center">
                        <img src="https://aurora.limatours.com.pe/images/logo/logo-aurora.png" style="width: 250px">
                    </div>
                    <div class="login_body">
                        <div id="textsChanges" class="carousel slide" data-ride="carousel">
                            <div class="carousel-inner">
                                <div class="carousel-item active">
                                    <div class="carousel-caption">
                                        <h3 style="font-size: 28px">Disculpe las molestias ocasionadas estamos en labores de mantenimiento</h3>
                                    </div>
                                </div>
                                <div class="carousel-item">
                                    <div class="carousel-caption">
                                        <h3 style="font-size: 28px">Sorry for the inconvenience we are in maintenance work</h3>
                                    </div>
                                </div>
                                <div class="carousel-item">
                                    <div class="carousel-caption">
                                        <h3 style="font-size: 28px">Desculpe o transtorno, estamos em manutenção</h3>
                                    </div>
                                </div>
                            </div>
                            <a class="carousel-control-prev" href="#textsChanges" role="button" data-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="sr-only">Previous</span>
                            </a>
                            <a class="carousel-control-next" href="#textsChanges" role="button" data-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="sr-only">Next</span>
                            </a>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <img src="https://aurora.limatours.com.pe/images/logo/logo_mini.jpg">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</html>
