@extends('layouts.app')

@section('content')

    <div class="login">
        <img class="bk-image" src="{{asset('images/bg-login.jpg')}}">
        <div class="row no-gutters justify-content-center align-items-center">
            <div class="col-md-6">
                <div class="login-box" style="width: 100%;">
                    <div class="login_header text-center">
                        <img src="{{asset('images/logo/logo-aurora.png')}}" style="width: 250px">
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
                        <img src="{{asset('images/logo/logo_mini.jpg')}}">
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('css')
    <style>
        .carousel-inner{
            height: 150px;
        }
        .carousel-caption{
            color: #fff;
            top: 50%;
            font-size: 40px;
        }
    </style>
    @include('auth.css.auth_css')
@endsection
