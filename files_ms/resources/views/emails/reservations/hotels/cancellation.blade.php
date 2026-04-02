@extends('emails.reservations.layout')
@section('content')

    <!-- Section: End: Full-width Image -->
    <!-- Section: Start: Text Coloumn -->
    <tr mc:repeatable="module" mc:variant="module_text_row" mc:hideable>
        <td align="center" valign="top">
            <table border="0" cellpadding="0" cellspacing="0" width="600" class="full_width"
                    style="margin-bottom: 0;">
                <tr>
                    <td style="" align="center" valign="top" bgcolor="#ffffff" class="full_width_text"
                        mc:edit="module_body">

                        <img src="https://res.cloudinary.com/litodti/image/upload/v1744302639/aurora/iconos/icon-cancel.png" alt="#" width="100%" style="margin: 12px; max-width:10%; display:block; width:10%; height:auto; padding-top: 0rem;border-radius: .5rem;"
                        mc:label="header_image" mc:edit="module_image" mc:allowdesigner mc:allowtext/>

                        <h3 style="padding: 0rem;color: #EB5757; font-size: 1.5rem; font-weight: 700;text-align: center; margin-bottom: 0rem;">
                            Cancelación de Reserva
                        </h3>

                        <p style="font-weight: 500; text-align: center; margin-top: 0.37rem; color: #3D3D3D; font-size: 16px;letter-spacing: 1.5%;">
                            {{ $file['itineraries']['name'] }}
                        </p>

                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <!-- Section: End: Text Coloumn -->
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

    {{--    {{ json_encode($reservation) }}--}}
    <!-- Section: Start: Text Coloumn -->
    <tr mc:repeatable="module" mc:variant="module_text_row" mc:hideable>
        <td align="center" valign="top">
            <table border="0" cellpadding="0" cellspacing="0" width="600" class="full_width">
                <tr>
                    <td align="center" valign="top" bgcolor="#ffffff" class="full_width_text"
                        mc:edit="module_body">
                        <div style="margin-bottom: 0rem; padding: 0 1rem;">
                            <p style="font-weight: 500; text-align: left; margin-bottom: 1rem; color: #3D3D3D;">
                                Su ejecutivo(a) <strong>{{ $file['executive_name'] }}</strong>  acaba de cancelar la reserva.
                            </p>

                            <p style="font-weight: 500; text-align: left; margin-bottom: 1rem; color: #3D3D3D;">
                                Esperamos su anulación en la respuesta de este e-mail.
                            </p>

                            <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #3D3D3D;"><strong style="color: #3D3D3D;">Número de file:</strong> {{ $file['file_number']}}</p>
                            <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #3D3D3D;"><strong style="color: #3D3D3D;">Nombre de file:</strong> {{ $file['description'] }}</p>
                            <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #3D3D3D;"><strong style="color: #3D3D3D;">Nacionalidad:</strong>{{ $file['client_nationality'] }}</p>

                            <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #3D3D3D;">
                                <strong style="color: #3D3D3D;">Fecha de reserva:</strong>                                
                                {{ \Carbon\Carbon::parse($file['itineraries']['created_at'])->format('d/m/Y') }}
                            </p>
                            <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 2rem;"><strong style="color: #3D3D3D;">E-mail del ejecutivo:</strong>
                                <a href="#" style="color: #EB5757; border-bottom: 1px solid #EB5757; text-decoration: none;">{{ $file['executive_email'] }}</a>
                            </p>
                            {{--                            <a href="https://aurora.limatours.com.pe" target="_blank" class="button">Ir a Aurora</a>--}}
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <!-- Section: End: Text Coloumn -->
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
    <!-- Section: Start: Text Coloumn -->
    <tr mc:repeatable="module" mc:variant="module_text_row" mc:hideable>
        <td align="center" valign="top">
            <table border="0" cellpadding="0" cellspacing="0" width="600" class="full_width">
                <tr>
                    <td align="center" valign="top" bgcolor="#ffffff" class="full_width_text"
                        mc:edit="module_body" style="padding-bottom: 2rem;">

                        <h3 style="color: #EB5757; font-size: 1.5rem; font-weight: 600; letter-spacing: -1%;text-align: left">
                                            Detalles de la cancelación</h3>

                        <div style="display: flex;">
                            <div style="display: flex; padding: 0rem 0rem 1.5rem;">
                                <img src="https://res.cloudinary.com/litodti/image/upload/v1744314216/aurora/iconos/check-icon.png"
                                        alt="#" width="24" height="20"
                                        style="display:block; padding-right: 0.2rem;"
                                        mc:label="header_image" mc:edit="module_image" mc:allowdesigner
                                        mc:allowtext/>
                                    <p style="font-weight: 600; text-align: left; color: #EB5757; margin: 0rem 0.5rem 0rem 0rem;">
                                        Código de confirmación:
                                    </p>
                                    <p
                                        style="font-weight: 600; text-align: left; color: #3D3D3D; margin: 0;">
                                        {{ $file['itineraries']['confirmation_code'] }}
                                    </p>
                            </div>
                        </div>

                        @if( isset($notas))
                            <div style="background-color: #DFFFE9; border-radius: 6px; padding: 20px 20px;margin-bottom: 20px;">
                                <div style="display: flex; align-items: center;">
                                    <img src="https://res.cloudinary.com/litodti/image/upload/v1744314216/aurora/iconos/pencil.png"
                                         alt="#" width="24" height="18"
                                         style="display:block; padding-right: 0.2rem;"
                                         mc:label="header_image" mc:edit="module_image" mc:allowdesigner
                                         mc:allowtext/>
                                    <p style="font-weight: 600; text-align: left; color: #EB5757; margin:0;">Notas para el proveedor: </p>
                                </div>
                                <p style="font-weight: 400; text-align: left; color: #3D3D3D; margin:0; padding-left: 30px">
                                    {{ $notas }}
                                    {{-- <span style="font-weight: 500; text-align: left; margin-bottom: .5rem; color: #2E2E2E;">
                                    </span> --}}
                                </p>
                            </div>
                        @endif

                        <div style="background-color: #FAFAFA; border-radius: 6px;padding: 20px 20px; margin-bottom: 20px">
                            <p
                                style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.6rem; color: #3D3D3D;">
                                <strong style="color: #3D3D3D; font-size: 16px;">
                                    Ingreso único:
                                </strong>
                            </p>

                            <hr style="border-style: solid; color: #E9E9E9; margin: 15px 0px;">

                            <div style="display: flex; align-items: center;">
                                <img src="https://res.cloudinary.com/litodti/image/upload/v1744314216/aurora/iconos/calendar.png" alt="#"
                                    width="25" height="18" style="display:block; padding-right: 0.2rem"
                                    mc:label="header_image" mc:edit="module_image" mc:allowdesigner
                                    mc:allowtext />
                                <p
                                    style="font-weight: 600; text-align: left; color: #EB5757; margin:0;">
                                    Fechas
                                </p>
                            </div>

                            <div style="padding: 0 0.3rem;">
                                <p
                                    style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #3D3D3D">
                                    <strong style="color: #3D3D3D; font-size: 14px">Check in:</strong>
                                    <span style="padding-right: 0.5rem"> {{ \Carbon\Carbon::parse($file['itineraries']['date_in'])->format('d/m/Y') }}</span>
                                    <strong style="color: #EB5757; padding-right: 0.5rem">|</strong>
                                    {{ $file['itineraries']['start_time'] }}
                                </p>
                                <p
                                    style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #3D3D3D">
                                    <strong style="color: #3D3D3D; font-size: 14px;">Check out:</strong>
                                    <span style="padding-right: 0.5rem"> {{ \Carbon\Carbon::parse($file['itineraries']['date_out'])->format('d/m/Y') }}</span>
                                    <strong style="color: #EB5757; padding-right: 0.5rem">|</strong>
                                    {{ $file['itineraries']['departure_time'] }}
                                </p>
                                @php
                                    $totalRooms = collect($file['itineraries']['rooms'])->sum('total_rooms');
                                @endphp
                                <p
                                    style="font-weight: 500; text-align: left; margin: 0rem 0.5rem 0rem auto; color: #3D3D3D">
                                    <strong style="color: #3D3D3D; font-size: 14px;">Cantidad:</strong>
                                    {{ $file['itineraries']['nights'] }} {{ $file['itineraries']['nights'] > 1 ? 'noches' : 'noche' }}
                                    <strong style="color: #EB5757; padding-right: 0.5rem">|</strong> {{ $totalRooms }} {{ $totalRooms > 1 ? 'habitaciones' : 'habitación' }}
                                </p>
                            </div>

                            <div style="display: flex; padding: 1.5rem 0rem; align-items: center;">
                                <img src="https://res.cloudinary.com/litodti/image/upload/v1744314216/aurora/iconos/bed.png" alt="#"
                                    width="24" height="20" style="display:block; padding-right: 0.2rem;"
                                    mc:label="header_image" mc:edit="module_image" mc:allowdesigner
                                    mc:allowtext />
                                <p
                                    style="font-weight: 600; text-align: left; color: #EB5757; margin:0;">
                                    Detalles para habitaciones</p>
                            </div>

                            @foreach($file['itineraries']['rooms'] as $k => $resHotRoom)
                                <div style="padding: 0rem 0.3rem 1rem;">
                                    <p
                                        style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #3D3D3D;">
                                        <strong style="color: #3D3D3D; font-size: 14px;">
                                            Tipo de habitación:
                                        </strong> {{ $resHotRoom['room_name'] }}
                                    </p>
                                    <p
                                        style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #3D3D3D;">
                                        <strong style="color: #3D3D3D; font-size: 14px;">
                                            Cantidad:
                                        </strong> {{ $resHotRoom['total_rooms'] }} {{ $resHotRoom['total_rooms'] > 1 ? 'Habitaciones' : 'habitación' }}
                                    </p>
                                    <p
                                        style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #3D3D3D;">
                                        <strong style="color: #3D3D3D; font-size: 14px;">
                                            Ocupantes:</strong> {{ $resHotRoom['occupants'] }} {{ $resHotRoom['occupants'] > 1 ? 'pasajeros' : 'pasajero' }}
                                    </p>

                                    <p
                                        style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #3D3D3D; ">
                                        <strong style="color: #3D3D3D; font-size: 14px;">Plan tarifario:
                                        </strong>
                                        {{ $resHotRoom['rate_plan_name'] }} | {{ $resHotRoom['rate_plan_code'] }}
                                    </p>
                                    <div style="display: flex; align-items: center; margin-bottom: 0.3rem;">
                                        <img src="https://res.cloudinary.com/litodti/image/upload/v1744302640/aurora/iconos/icon-dolar.png" alt="#"
                                            width="10"
                                            height="18"
                                            style="display:block; padding-right: 0.2rem;"
                                            mc:label="header_image" mc:edit="module_image" mc:allowdesigner
                                            mc:allowtext />
                                        <p
                                            style="font-weight: 600; font-size: 14px; text-align: left; color: #EB5757; margin:0;">
                                            Total de tarifa:
                                            <strong style="color: #3D3D3D"> {{ $resHotRoom['amount_cost'] }} </strong>
                                        </p>
                                    </div>


                                    @if($resHotRoom['channel_id'] == 6 and $resHotRoom['channel_reservation_code_master'] )
                                        <p
                                            style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #3D3D3D; ">
                                            <strong style="color: #3D3D3D; font-size: 14px;">Código Confirmación Channel: </strong>
                                            {{ $resHotRoom['channel_reservation_code_master'] }}
                                        </p>
                                    @endif

                                    <p
                                        style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #3D3D3D; ">
                                        <strong
                                            style="padding-right:0.5rem; color: #3D3D3D; font-size: 14px; float: left">
                                            Status de la reserva:
                                        </strong>
                                    </p>
                                    <span
                                        style="float: left; display: flex;background: #D80404
                                        ;border-radius: .5rem;width: max-content;padding: 0.4rem;color: white;font-weight: 700;font-size: 14px;">
                                        <img src="https://backend.limatours.com.pe/images/error-icon.png"
                                            alt="#" width="20"
                                            style="display:block; padding-right: 0.2rem; align-items: center;"
                                            mc:label="header_image" mc:edit="module_image" mc:allowdesigner
                                            mc:allowtext />
                                        Cancelada
                                    </span>
                                    <p style="clear: both"></p>
                                </div>
                                <p
                                    style="font-weight: 600; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #3D3D3D;font-size: 14px;">
                                    Políticas de cancelación:
                                    @if($resHotRoom['penality_total'] > 0 )
                                        <strong style="color: #E4B804">
                                            Con penalidad USD {{ $resHotRoom['penality_total'] }}
                                        </strong>
                                    @else
                                        Sin Penalidad
                                    @endif
                                </p>
                            @endforeach
                        </div>

                        {{-- <div>
                            <div style="padding: 0rem 0 0 1.5rem;">
                                <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E;"><strong style="color: #2E2E2E; font-size: 13px;">Total tarifa: </strong> USD {{ $file['itineraries']['total_cost_amount'] }}</p>
                            </div>
                        </div> --}}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <!-- Section: End: Text Coloumn -->

@endsection
