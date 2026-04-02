@extends('emails.reservations.layout_new')
@section('content')

    <!-- Section: Start: Text Coloumn -->
    <tr mc:repeatable="module" mc:variant="module_text_row" mc:hideable>
        <td align="center" valign="top">
            <table border="0" cellpadding="0" cellspacing="0" width="600" class="full_width">
                <tr>
                    <td align="center" valign="top" bgcolor="#ffffff" class="full_width_text"
                        mc:edit="module_body">
                        <div style="margin-bottom: 0rem; padding: 0 1rem;">
                            <p style="font-weight: 500; text-align: left; margin-bottom: 1rem; color: #2E2E2E;">
                                Su ejecutivo(a) <strong>{{ $reservation['hotels'][0]['executive_name'] }}</strong>  acaba de solicitar la cancelación parcial de la reserva
                            </p>
                            <p style="font-weight: 500; text-align: left; margin-bottom: 1rem; color: #2E2E2E;">
                                Esperamos su pronta confirmación respondiendo este e-mail. Los datos de la reserva son los siguientes:
                            </p>

                            <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E;"><strong style="color: #2E2E2E;">Número de file:</strong> {{ $reservation['file_code'] }}</p>
                            <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E;"><strong style="color: #2E2E2E;">Nombre de grupo:</strong> {{ $reservation['customer_name'] }}</p>
                            <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E;">
                                <strong style="color: #2E2E2E;">Fecha de envío de reserva:</strong>
                                {{ convertDateTime($reservation['hotels'][0]['created_at'], true) }}
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
            <table border="0" cellpadding="0" cellspacing="0" width="600" class="full_width">
                <tr>
                    <td align="center" valign="top" bgcolor="#ffffff" class="full_width_text"
                        mc:edit="module_body" style="padding-bottom: 2rem;">

                        <h3 style="padding: 0 1rem;color: #EB5757; font-size: 1.5rem; font-weight: 600; letter-spacing: 1px;text-align: left; margin-bottom: 0;">Detalles de la cancelación</h3>

                        {{--                        <div style="display: flex;">--}}
                        {{--                            <div style="display: flex; padding: 1rem;">--}}
                        {{--                                <img src="https://backend.limatours.com.pe/images/check-icon.png"--}}
                        {{--                                     alt="#" width="24" height="20"--}}
                        {{--                                     style="display:block; padding-right: 0.2rem;"--}}
                        {{--                                     mc:label="header_image" mc:edit="module_image" mc:allowdesigner--}}
                        {{--                                     mc:allowtext/>--}}
                        {{--                                <p style="font-weight: 600; text-align: left; color: #EB5757; margin: 0;">Código de confirmación:--}}
                        {{--                                    @foreach($reservation['hotels'] as $h => $hotelReservation)--}}
                        {{--                                        @foreach($hotelReservation['reservations_hotel_rooms'] as $resHotRoom)--}}
                        {{--                                            @if( isset($resHotRoom['status']) and $resHotRoom['status'] !== 0 )--}}
                        {{--                                                <span style="color: #2E2E2E; font-weight: 500; margin-right: 10px" >--}}
                        {{--                                                    - {{ $resHotRoom['channel_reservation_code'] }}--}}
                        {{--                                                </span>--}}
                        {{--                                            @endif--}}
                        {{--                                        @endforeach--}}
                        {{--                                    @endforeach--}}
                        {{--                                </p>--}}
                        {{--                            </div>--}}
                        {{--                        </div>--}}

                        @if( isset($reservation['comment']) && $reservation['comment'] !== '' )
                            <div>
                                <div style="display: flex; padding: 1rem 1rem 0.3rem 1rem;">
                                    <img src="https://backend.limatours.com.pe/images/pencil.png"
                                         alt="#" width="24" height="18"
                                         style="display:block; padding-right: 0.2rem;"
                                         mc:label="header_image" mc:edit="module_image" mc:allowdesigner
                                         mc:allowtext/>
                                    <p style="font-weight: 600; text-align: left; color: #EB5757; margin:0;">Notas para el proveedor: </p>
                                </div>
                                <p style="padding: 0rem 0 0 1.5rem; margin: 0; font-size: 15px; list-style: none; ">
                                <span style="font-weight: 500; text-align: left; margin-bottom: .5rem; color: #2E2E2E;">
                                    {{ $reservation['comment'] }}
                                </span>
                                </p>
                            </div>
                        @endif


                        @foreach($reservation['hotels'] as $h => $hotelReservation)
                            <div>
                                <div style="display: flex; padding: 1rem 1rem 0.3rem 1rem;">
                                    <img src="https://backend.limatours.com.pe/images/calendar.png"
                                         alt="#" width="25" height="18"
                                         style="display:block; padding-right: 0.2rem;"
                                         mc:label="header_image" mc:edit="module_image" mc:allowdesigner
                                         mc:allowtext/>
                                    <p style="font-weight: 600; text-align: left; color: #EB5757; margin:0;">
                                        {{ convertDateTime($hotelReservation['check_in'], 0) }}
                                        @if( $hotelReservation['check_in'] !== $hotelReservation['check_out'] )
                                            - {{ convertDateTime($hotelReservation['check_out'], 0) }}
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div style="overflow: hidden; padding: 0 0.8rem;">
                                <div style="float:left; display: block; width: 492px; margin-left: 10px; margin-right: 10px;">
                                    <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E;padding-left: 17px;">
                                        <strong style="color: #2E2E2E; font-size: 15px;margin-right: 10px;">Check in:</strong> <span style="padding-right: 0.5rem"> {{ substr($hotelReservation['check_in_time'], 0, 5) }}</span>
                                        <strong style="color: #2E2E2E; font-size: 15px;">Check out:</strong> <span style="padding-right: 0.5rem"> {{ substr($hotelReservation['check_out_time'], 0, 5) }}</span>
                                        <strong style="color: #2E2E2E; font-size: 15px;">Cantidad de noches:</strong> {{ $hotelReservation['nights'] }}
                                    </p>
                                </div>
                            </div>


                            <div>
                                <div style="display: flex; padding: 1rem;">
                                    <img src="https://backend.limatours.com.pe/images/bed.png"
                                         alt="#" width="24" height="20"
                                         style="display:block; padding-right: 0.2rem;"
                                         mc:label="header_image" mc:edit="module_image" mc:allowdesigner
                                         mc:allowtext/>
                                    <p style="font-weight: 600; text-align: left; color: #EB5757; margin:0;">Detalles de la habitación:</p>
                                </div>
                                <div style="padding: 0rem 0 0 1.5rem;">
                                    <p style="padding-left: 17px;font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 1.5rem; color: #2E2E2E;"><strong style="color: #2E2E2E; font-size: 15px;">Cantidad de habitaciones: </strong> {{ count($hotelReservation['reservations_hotel_rooms']) }} </p>
                                </div>
                                <div style="overflow: hidden; padding: 0 0.8rem;">
                                    @foreach($hotelReservation['reservations_hotel_rooms'] as $k => $resHotRoom)


                                        <div style="float:left; display: block; margin-bottom: 20px; margin-left: 10px; margin-right: 10px;">
                                            <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E;"><strong style="color: #2E2E2E; font-size: 13px;"> {{ $k+1 }}) Tipo de habitación:</strong> {{ $resHotRoom['room_name'] }}</p>
                                            <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E;"><strong style="color: #2E2E2E; font-size: 13px;">Ocupantes:</strong> Adultos {{ $resHotRoom['adult_num'] }}
                                                @if($resHotRoom['child_num'] > 0)
                                                    | Niños {{ $resHotRoom['child_num'] }}
                                                @endif
                                                @if($resHotRoom['extra_num'] > 0)
                                                    | Extras {{ $resHotRoom['extra_num'] }}
                                                @endif
                                            </p>

                                            @if($resHotRoom['channel_code'] != 'AURORA')
                                                @if($reservation['mail_type'] != 'CANCELATION')
                                                    <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E;"><strong style="color: #2E2E2E; font-size: 13px;">Channel:</strong> {{ $resHotRoom['channel_code'] }}</p>
                                                @endif
                                                <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E;"><strong style="color: #2E2E2E; font-size: 13px;">Status Channel:</strong>
                                                    @switch($resHotRoom['status_in_channel'])
                                                        @case(0)
                                                        <b>Cancelado</b> {{ $resHotRoom['channel_reservation_code'] ? $resHotRoom['channel_reservation_code'] : '' }}
                                                        @break

                                                        @case(3)
                                                        <b>On Request</b> {{ $resHotRoom['channel_reservation_code'] ? $resHotRoom['channel_reservation_code'] : '' }}
                                                        @break

                                                        @default

                                                        @if($resHotRoom['onRequest'] == "1" and $resHotRoom['channel_reservation_code'])
                                                            <b>Confirmado</b>
                                                        @else
                                                            <b>Booking failure</b>
                                                        @endif

                                                    @endswitch
                                                </p>
                                                @if($resHotRoom['onRequest'] == "1" and $resHotRoom['channel_reservation_code'])
                                                    <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E;"><strong style="color: #2E2E2E; font-size: 13px;">Codigo Confirmación Channel:</strong> {{ $resHotRoom['channel_reservation_code'] ? $resHotRoom['channel_reservation_code']: '' }}</p>
                                                @endif

                                            @else
                                                @if( isset($resHotRoom['channel_reservation_code']) && $resHotRoom['channel_reservation_code']!== "" && $resHotRoom['channel_reservation_code']!==null)
                                                    <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E;"><strong style="color: #2E2E2E; font-size: 13px;"> Código de Confirmación:</strong> {{ $resHotRoom['channel_reservation_code'] }}</p>
                                                @endif
                                            @endif

                                            <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E; "><strong style="color: #2E2E2E; font-size: 13px;">Plan tarifario: </strong> {{ $resHotRoom['rate_plan_name'] }} | {{ $resHotRoom['rate_plan_code'] }} </p>

                                            <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E; ">
                                                <strong style="color: #2E2E2E; font-size: 13px;">Penalidad: </strong>
                                                @if($reservation['mail_config_to']==='hotel')
                                                    USD {{ $resHotRoom['penality_base'] }}
                                                @else
                                                    USD {{ $resHotRoom['penality'] }}
                                                @endif
                                            </p>

                                            @empty(!$resHotRoom['guest_note'])
                                                <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E; "><strong style="color: #2E2E2E; font-size: 13px;">Nota Ejecutiva: </strong> {{ $resHotRoom['guest_note'] }} </p>
                                            @endempty

                                        </div>

                                    @endforeach
                                </div>
                            </div>
                            <hr>
                        @endforeach

                        <div>
                            <div style="padding: 0rem 0 0 1.5rem;">
                                <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E;"><strong style="color: #2E2E2E; font-size: 13px;">Total a pagar: </strong> USD {{ $hotelReservation['total_amount_base'] }}</p>
                            </div>
                        </div>

                        @if($reservation['mail_type'] != 'CANCELATION')
                            <div>
                                <div style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E; display: flex; align-items: center; margin-top: 1.5rem;">
                                    <strong style="color: #2E2E2E; font-size: 13px; margin-top: 7px; margin-right: 1.5rem; padding: 0rem 0 0 1.5rem;">Status reserva:</strong>
                                    <div style="display: flex; background: #FF3B3B; border-radius: .5rem;width: max-content;padding: 0.5rem;color: white;font-weight: 600;font-size: 14px;">
                                        <img src="https://backend.limatours.com.pe/images/error-icon.png" alt="#" width="24" height="15" style="display:block; padding-right: 0.2rem;"
                                             mc:label="header_image" mc:edit="module_image" mc:allowdesigner mc:allowtext/>
                                        Cancelada
                                    </div>

                                </div>

                            </div>
                        @endif

                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <!-- Section: End: Text Coloumn -->

@endsection
