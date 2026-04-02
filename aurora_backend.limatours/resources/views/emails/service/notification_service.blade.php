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
                                    <!-- Greeting -->
                                    <h1 style="margin-top: 0; color: #2F3133; font-size: 19px; font-weight: bold; text-align: left;">
                                        Usuario:</h1>

                                    <!-- User -->
                                    <p style="margin-top: 0; color: #74787E; font-size: 16px; line-height: 1.5em; text-align: left;">
                                        El usuario {{\Illuminate\Support\Facades\Auth::user()->code}}
                                        - {{\Illuminate\Support\Facades\Auth::user()->name}} ha realizado una
                                        actualizacion
                                    </p>
                                    @if (!empty($data['message']))
                                        <h1 style="margin-top: 0; color: #2F3133; font-size: 19px; font-weight: bold; text-align: left;">
                                            Mensaje de usuario:</h1>
                                        <!-- Message -->
                                        <p style="margin-top: 0; color: #74787E; font-size: 16px; line-height: 1.5em; text-align: left;">
                                            {{$data['message']}}
                                        </p>
                                    @endif
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
                                                            <table
                                                                style="width: 600px; margin: 0 auto; background: #ffffff;">
                                                                <tr>
                                                                    <td><b>ID - Codigo Aurora:</b></td>
                                                                </tr>
                                                                <tr>
                                                                    <td>{{ $data['service_id'] }}
                                                                        - {{ $data['aurora_code'] }}</td>
                                                                </tr>
                                                                @if (count($data['service']) > 0)
                                                                    @if (isset($data['service']['aurora_code']))
                                                                        <tr>
                                                                            <td><b>Codigo de aurora:</b></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>{{ $data['service']['aurora_code'] }} </td>
                                                                        </tr>
                                                                    @endif
                                                                    @if (isset($data['service']['name']))
                                                                        <tr>
                                                                            <td><b>Nombre:</b></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>{{ $data['service']['name'] }} </td>
                                                                        </tr>
                                                                    @endif
                                                                    @if (isset($data['service']['equivalence_aurora']))
                                                                        <tr>
                                                                            <td><b>Equivalencia aurora:</b></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>{{ $data['service']['equivalence_aurora'] }} </td>
                                                                        </tr>
                                                                    @endif
                                                                    @if (isset($data['service']['currency_id']))
                                                                        <tr>
                                                                            <td><b>Moneda:</b></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>{{ $data['service']['currency_id'] }} </td>
                                                                        </tr>
                                                                    @endif
                                                                    @if (isset($data['service']['affected_igv']))
                                                                        <tr>
                                                                            <td><b>Afecto a igv:</b></td>
                                                                        </tr>
                                                                        <tr>
                                                                            @if ($data['service']['affected_igv'])
                                                                                <td>Si</td>
                                                                            @else
                                                                                <td>No</td>
                                                                            @endif
                                                                        </tr>
                                                                    @endif
                                                                    @if (isset($data['service']['include_accommodation']))
                                                                        <tr>
                                                                            <td><b>Incluye acomodación:</b></td>
                                                                        </tr>
                                                                        <tr>
                                                                            @if ($data['service']['include_accommodation'])
                                                                                <td>Si</td>
                                                                            @else
                                                                                <td>No</td>
                                                                            @endif
                                                                        </tr>
                                                                    @endif
                                                                    @if (isset($data['service']['unit_id']))
                                                                        <tr>
                                                                            <td><b>Unidad:</b></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>{{$data['service']['unit_id']}}</td>
                                                                        </tr>
                                                                    @endif
                                                                    @if (isset($data['service']['affected_markup']))
                                                                        <tr>
                                                                            <td><b>Afecto a markup:</b></td>
                                                                        </tr>
                                                                        <tr>
                                                                            @if ($data['service']['affected_markup'])
                                                                                <td>Si</td>
                                                                            @else
                                                                                <td>No</td>
                                                                            @endif
                                                                        </tr>
                                                                    @endif
                                                                    @if (isset($data['service']['qty_reserve']))
                                                                        <tr>
                                                                            <td><b>Reserva a partir de:</b></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>{{$data['service']['qty_reserve']}}</td>
                                                                        </tr>
                                                                    @endif
                                                                    @if (isset($data['service']['unit_duration_id']))
                                                                        <tr>
                                                                            <td><b>Unidad de duración:</b></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>{{$data['service']['unit_duration_id']}}</td>
                                                                        </tr>
                                                                    @endif
                                                                    @if (isset($data['service']['service_type_id']))
                                                                        <tr>
                                                                            <td><b>Categoria:</b></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>{{$data['service']['service_type_id']}}</td>
                                                                        </tr>
                                                                    @endif
                                                                    @if (isset($data['service']['classification_id']))
                                                                        <tr>
                                                                            <td><b>Clasificación:</b></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>{{$data['service']['classification_id']}}</td>
                                                                        </tr>
                                                                    @endif
                                                                    @if (isset($data['service']['service_sub_category_id']))
                                                                        <tr>
                                                                            <td><b>Tipo - Sub Tipo:</b></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>{{$data['service']['service_sub_category_id']}}</td>
                                                                        </tr>
                                                                    @endif
                                                                    @if (isset($data['service']['duration']))
                                                                        <tr>
                                                                            <td><b>Duración:</b></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>{{$data['service']['duration']}}</td>
                                                                        </tr>
                                                                    @endif
                                                                    @if (isset($data['service']['pax_min']))
                                                                        <tr>
                                                                            <td><b>Pax minimo:</b></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>{{$data['service']['pax_min']}}</td>
                                                                        </tr>
                                                                    @endif
                                                                    @if (isset($data['service']['pax_max']))
                                                                        <tr>
                                                                            <td><b>Pax maximo:</b></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>{{$data['service']['pax_max']}}</td>
                                                                        </tr>
                                                                    @endif
                                                                    @if (isset($data['service']['min_age']))
                                                                        <tr>
                                                                            <td><b>Edad minima:</b></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>{{$data['service']['min_age']}}</td>
                                                                        </tr>
                                                                    @endif
                                                                    @if (isset($data['service']['require_itinerary']))
                                                                        <tr>
                                                                            <td><b>Requiere itinerario:</b></td>
                                                                        </tr>
                                                                        <tr>
                                                                            @if ($data['service']['require_itinerary'])
                                                                                <td>Si</td>
                                                                            @else
                                                                                <td>No</td>
                                                                            @endif
                                                                        </tr>
                                                                    @endif
                                                                    @if (isset($data['service']['require_image_itinerary']))
                                                                        <tr>
                                                                            <td><b>Requiere foto en el itinerario:</b></td>
                                                                        </tr>
                                                                        <tr>
                                                                            @if ($data['service']['require_image_itinerary'])
                                                                                <td>Si</td>
                                                                            @else
                                                                                <td>No</td>
                                                                            @endif
                                                                        </tr>
                                                                    @endif
                                                                    @if (isset($data['service']['status']))
                                                                        <tr>
                                                                            <td><b>Estado:</b></td>
                                                                        </tr>
                                                                        <tr>
                                                                            @if ($data['service']['status'])
                                                                                <td>Activo</td>
                                                                            @else
                                                                                <td>Inactivo</td>
                                                                            @endif
                                                                        </tr>
                                                                    @endif
                                                                    @if (isset($data['service']['notes']))
                                                                        <tr>
                                                                            <td><b>Notas:</b></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>{{$data['service']['notes']}}</td>
                                                                        </tr>
                                                                    @endif
                                                                @endif
                                                            </table>

                                                            @if (count($data['translations']) > 0)
                                                                <table
                                                                    style="width: 600px; margin: 0 auto; background: #ffffff;">
                                                                    <tr>
                                                                        <td><b>Textos:</b></td>
                                                                        <td></td>
                                                                    </tr>
                                                                    @foreach($data['translations'] as $translation)
                                                                        <tr>
                                                                            <td style="color: #dd2b1e">
                                                                                <b>({{trim(strtoupper($translation['language']))}}
                                                                                    )</b>:
                                                                            </td>
                                                                            <td></td>
                                                                        </tr>
                                                                        @if(isset($translation['name_commercial']))
                                                                            <tr>
                                                                                <td><b>Nombre comercial:</b></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>{{ $translation['name_commercial'] }} </td>
                                                                            </tr>
                                                                        @endif
                                                                        @if(isset($translation['description']))
                                                                            <tr>
                                                                                <td>
                                                                                    <b>Descripción:</b>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>{{ $translation['description'] }} </td>
                                                                            </tr>
                                                                        @endif
                                                                        @if(isset($translation['summary']))
                                                                            <tr>
                                                                                <td>
                                                                                    <b>Summary:</b>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>{{ $translation['summary'] }} </td>
                                                                            </tr>
                                                                        @endif
                                                                        @if(isset($translation['summary_commercial']))
                                                                            <tr>
                                                                                <td>
                                                                                    <b>Summary (Notas para clientes):</b>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>{{ $translation['summary_commercial'] }} </td>
                                                                            </tr>
                                                                        @endif
                                                                        @if(isset($translation['itinerary']))
                                                                            <tr>
                                                                                <td>
                                                                                    <b>Itinerario:</b>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>{{ $translation['itinerary'] }} </td>
                                                                            </tr>
                                                                        @endif
                                                                    @endforeach
                                                                </table>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                    <p style="margin-top: 50px; color: #74787E; font-size: 19px; line-height: 1.5em; text-align: center;">

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
