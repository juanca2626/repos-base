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
                        <img src="https://res.cloudinary.com/litodti/image/upload/v1744302640/aurora/iconos/icon-modification.png" alt="#" width="100%" style="margin: 12px; max-width:10%; display:block; width:10%; height:auto; padding-top: 0rem;border-radius: .5rem;"
                        mc:label="header_image" mc:edit="module_image" mc:allowdesigner mc:allowtext/>
                        <h3 style="padding: 0rem;color: #EB5757; font-size: 1.5rem; font-weight: 700; letter-spacing: 1px;text-align: center; margin-bottom: 0rem;">
                            Reserva Modificada
                        </h3>

                        <p style="font-weight: 500; text-align: center; margin-top: 0.5rem; color: #2E2E2E; font-size: 16px;">
                            {{ $reservation['itineraries']['name'] }}
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
                        <div>
                            <p style="font-weight: 500; text-align: left; margin-bottom: 1rem; color: #3D3D3D;">
                                Su ejecutivo(a) <strong>{{ $reservation['executive_name'] }}</strong>  acaba de solicitar una reserva.
                            </p>

                            <p style="font-weight: 500; text-align: left; margin-bottom: 1rem; color: #3D3D3D;">
                                Para terminar el proceso, por favor registra la confirmación y el código en nuestra plataforma. Los datos de la reserva son los siguientes:
                            </p>

                            <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #3D3D3D;"><strong style="color: #2E2E2E;">Número de file:</strong> {{ $reservation['file_number']}}</p>
                            <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #3D3D3D;"><strong style="color: #2E2E2E;">Nombre de file:</strong> {{ $reservation['description'] }}</p>
                            <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #3D3D3D;"><strong style="color: #2E2E2E;">Nacionalidad:</strong>{{ $reservation['client_nationality'] }}</p>

                            <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #3D3D3D;">
                                <strong style="color: #3D3D3D;">Fecha de envío de reserva:</strong>                               
                                {{ \Carbon\Carbon::parse($reservation['created_at'])->format('d/m/Y') }}
                            </p>
                            <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 2rem;"><strong style="color: #2E2E2E;">E-mail del ejecutivo:</strong>
                                <a href="#" style="color: #EB5757; border-bottom: 1px solid #EB5757; text-decoration: none;">{{ $reservation['executive_email'] }}</a>
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
            <!-- Reserva Cancelada-->
            <table border="0" cellpadding="0" cellspacing="0" width="600" class="full_width" style="margin-bottom: 20px">
                <tr>
                    <td align="center" valign="top" bgcolor="#ffffff" class="full_width_text"  mc:edit="module_body" style="padding-bottom: 2rem;">
                        <h3 style="margin-top: 0; color: #EB5757; font-size: 1.5rem; font-weight: 600; letter-spacing: -1%;text-align: left">Detalles de la reserva</h3>

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
                            <p
                                style="font-weight: 400; text-align: left; color: #3D3D3D; margin:0; padding-left: 30px">
                               {{ $notas }}
                            </p>
                        </div>
                        @endif
                        <div style="background-color: #FFF2F2; border-radius: 6px;padding: 20px 20px; margin-bottom: 20px">
                            <p
                                style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.6rem; color: #3D3D3D;">
                                <strong style="color: #3D3D3D; font-size: 16px;">
                                    Reserva Anterior
                                </strong>
                            </p>
                            <p
                                style="font-size: 14px;font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.6rem; color: #3D3D3D;">
                                Código de confirmación: <strong
                                    style="color: #EB5757; font-size: 16px;">
                                    {{ $file['itineraries']['confirmation_code'] }}
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
                                    <strong style="color: #EB5757; padding-right: 0.5rem">|</strong> {{ $totalRooms }}
                                    {{ $totalRooms > 1 ? 'Habitaciones': 'Habitación' }}
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
                            <div style="padding: 0rem 0.3rem 2.5rem;">
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
                                    </strong> {{ $resHotRoom['total_rooms'] }} {{ $resHotRoom['total_rooms'] > 1 ? 'habitaciones' : 'habitación' }}
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
                                        width="10" height="18"
                                        style="display:block; padding-right: 0.2rem;"
                                        mc:label="header_image" mc:edit="module_image" mc:allowdesigner
                                        mc:allowtext />
                                    <p
                                        style="font-weight: 600; font-size: 14px; text-align: left; color: #EB5757; margin:0;">
                                        Total de tarifa:
                                        <strong style="color: #3D3D3D">
                                            @if($type == 'cost')
                                                {{ $resHotRoom['amount_cost'] }}
                                            @else
                                                {{ $resHotRoom['amount_sale'] }}
                                            @endif
                                        </strong>
                                    </p>

                                </div>
                                <p
                                    style="font-weight: 600; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #3D3D3D;font-size: 14px;">
                                    Políticas de cancelación:
                                    @if($resHotRoom['penality_total'] > 0 )
                                        USD                                         
                                        @if($type == 'cost')
                                            {{ $resHotRoom['penality_total'] }}
                                        @else
                                            {{ $resHotRoom['penality_total_sale'] }}
                                        @endif                                        
                                    @else
                                        Sin Penalidad
                                    @endif 
                                </p>
                            </div>
                            @endforeach
                        </div>
                        {{-- <h3 style="padding: 0 1rem;color: black; font-size: 1rem; font-weight: 600; letter-spacing: 1px;text-align: left; margin-bottom: 0;">Reserva Anterior  <span style="float: right;font-weight: 200;font-size: 13px;">Código de confirmación: </span> {{ $file['itineraries']['confirmation_code'] }}</h3>
                         <hr /> --}}
                        <div style="background-color: #DFFFE9; border-radius: 6px;padding: 20px 20px; margin-bottom: 20px">
                            <p
                                style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.6rem; color: #3D3D3D;">
                                <strong style="color: #3D3D3D; font-size: 16px;">
                                    Reserva modificada
                                </strong>
                            </p>
                            <hr style="border-style: solid; color: #E9E9E9; margin: 15px 0px;">
                            
                            @if(isset($reservation['itineraries']['rooms_new']))

                                <p
                                    style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.6rem; color: #3D3D3D;">
                                    <strong style="color: #3D3D3D; font-size: 16px;">
                                        Primer Ingreso
                                    </strong>
                                </p>

                                @include('emails.reservations.hotels.reservation.modification_reservations', [
                                    'date_in' => $reservation['itineraries']['date_in'],
                                    'date_out' => $reservation['itineraries']['date_out'],
                                    'start_time' => $reservation['itineraries']['start_time'],
                                    'departure_time' => $reservation['itineraries']['departure_time'],
                                    'nights' => $reservation['itineraries']['nights'],                                
                                    'rooms' => $reservation['itineraries']['rooms']
                                ])

                                <p
                                    style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.6rem; color: #3D3D3D;">
                                    <strong style="color: #3D3D3D; font-size: 16px;">
                                        Segundo Ingreso
                                    </strong>
                                </p>

                                @include('emails.reservations.hotels.reservation.modification_reservations', [
                                    'date_in' => $reservation['itineraries']['rooms_new_date_in'],
                                    'date_out' => $reservation['itineraries']['rooms_new_date_out'],
                                    'start_time' => $reservation['itineraries']['rooms_new_start_time'],
                                    'departure_time' => $reservation['itineraries']['rooms_new_departure_time'],
                                    'nights' => $reservation['itineraries']['rooms_new_nights'],                                
                                    'rooms' => $reservation['itineraries']['rooms_new']
                                ])

                            @else
                                                     
                                @include('emails.reservations.hotels.reservation.modification_reservations', [
                                    'date_in' => $reservation['itineraries']['date_in'],
                                    'date_out' => $reservation['itineraries']['date_out'],
                                    'start_time' => $reservation['itineraries']['start_time'],
                                    'departure_time' => $reservation['itineraries']['departure_time'],
                                    'nights' => $reservation['itineraries']['nights'],                                
                                    'rooms' => $reservation['itineraries']['rooms']
                                ])

                            @endif

                            

                        </div>

                        <p style="font-weight: 500; text-align:center; margin: 2rem 2rem ; color: #3D3D3D; ">
                            Registrar la confirmación de la reserva y el código dando click en el botón:</p>
                        </p>
                        {{-- <a href="{{ config('services.a3front.endpoint') }}register_supplier/{{ $reservation['file_number'] }}?lang={{ $reservation['lang'] }}&codsvs={{ $reservation['itineraries']['object_code'] }}"
                                target="_blank" class="button">Registrar</a> --}}

                        <a href="{{ config('services.a3front.endpoint') }}register_providers/{{ $reservation['file_number'] }}/{{ $reservation['itineraries']['object_code'] }}?lang={{ $reservation['lang'] }}"
                                target="_blank" class="button" style="color:#fff">Registrar</a>
                        <p style="clear: both"></p>
                    </td>
                </tr>
            </table>

        </td>
    </tr>
    <!-- Section: End: Text Coloumn -->

@endsection
