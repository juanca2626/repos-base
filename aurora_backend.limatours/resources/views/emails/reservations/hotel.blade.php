@extends('emails.reservations.layout_new')
@section('content')
    {{--    {{ json_encode($reservation) }}--}}
    <!-- Section: Start: Text Coloumn -->
    <tr mc:repeatable="module" mc:variant="module_text_row" mc:hideable>
        <td align="center" valign="top">
            <table border="0" cellpadding="0" cellspacing="0" width="600" class="full_width">
                <tr>
                    <td align="center" valign="top" bgcolor="#ffffff" class="full_width_text"
                        mc:edit="module_body">
                        <div style="margin-bottom: 0rem; padding: 0 1rem;">
                            <p style="font-weight: 500; text-align: left; margin-bottom: 1rem; color: #2E2E2E;">
                                Su ejecutivo(a) <strong>{{ $reservation['hotels'][0]['executive_name'] }}</strong>  acaba de
                                @switch($reservation['confirmed'])
                                    @case(0)
                                    solicitar una reserva.
                                    @break
                                    @case(2)
                                    cancelar la reserva.
                                    @break
                                    @default
                                    realizar una reserva.
                                @endswitch
                            </p>

                            @switch($reservation['confirmed'])
                                @case(0)
                                <p style="font-weight: 500; text-align: left; margin-bottom: 1rem; color: #2E2E2E;">
                                    Esperamos su pronta confirmación respondiendo este e-mail. Los datos de la reserva son los siguientes:
                                </p>
                                @break
                                @case(2)
                                <p style="font-weight: 500; text-align: left; margin-bottom: 1rem; color: #2E2E2E;">
                                    Esperamos su respuesta con la anulación respondiendo este e-mail.
                                </p>
                                @break
                                @default
                                <p style="font-weight: 500; text-align: left; margin-bottom: 1rem; color: #2E2E2E;">
                                    Para terminar el proceso, por favor registra la confirmación y el código en nuestra plataforma. Los datos de la reserva son los siguientes:
                                </p>
                            @endswitch

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

                        @if( $reservation['confirmed'] == 2 )
                            <h3 style="padding: 0 1rem;color: #EB5757; font-size: 1.5rem; font-weight: 600; letter-spacing: 1px;text-align: left; margin-bottom: 0;">Detalles de la cancelación</h3>

                            {{--                            <div style="display: flex;">--}}
                            {{--                                <div style="display: flex; padding: 1rem;">--}}
                            {{--                                    <img src="https://backend.limatours.com.pe/images/check-icon.png"--}}
                            {{--                                         alt="#" width="24" height="20"--}}
                            {{--                                         style="display:block; padding-right: 0.2rem;"--}}
                            {{--                                         mc:label="header_image" mc:edit="module_image" mc:allowdesigner--}}
                            {{--                                         mc:allowtext/>--}}
                            {{--                                        <p style="font-weight: 600; text-align: left; color: #EB5757; margin: 0;">Código de confirmación:--}}
                            {{--                                            @foreach($reservation['hotels'] as $h => $hotelReservation)--}}
                            {{--                                                @foreach($hotelReservation['reservations_hotel_rooms'] as $resHotRoom)--}}
                            {{--                                                    @if( isset($resHotRoom['status']) and $resHotRoom['status'] !== 0 )--}}
                            {{--                                                    <span style="color: #2E2E2E; font-weight: 500; margin-right: 10px" >--}}
                            {{--                                                        - {{ $resHotRoom['channel_reservation_code'] }}--}}
                            {{--                                                    </span>--}}
                            {{--                                                    @endif--}}
                            {{--                                                @endforeach--}}
                            {{--                                            @endforeach--}}
                            {{--                                        </p>--}}
                            {{--                                </div>--}}
                            {{--                            </div>--}}
                        @else
                            <h3 style="padding: 0 1rem;color: #EB5757; font-size: 1.5rem; font-weight: 600; letter-spacing: 1px;text-align: left; margin-bottom: 0;">Detalles de la reserva</h3>
                        @endif

                        @if( isset($reservation['comment']) && $reservation['comment'] !== '' )
                            <div>
                                <div style="display: flex; padding:  1rem 1rem 0.3rem 1rem;">
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
                                         style="display:block; padding-right: 0.2rem"
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
                                <div style="display: flex; padding:  1rem 1rem 0.3rem 1rem;">
                                    <img src="https://backend.limatours.com.pe/images/bed.png"
                                         alt="#" width="24" height="20"
                                         style="display:block; padding-right: 0.2rem;"
                                         mc:label="header_image" mc:edit="module_image" mc:allowdesigner
                                         mc:allowtext/>
                                    <p style="font-weight: 600; text-align: left; color: #EB5757; margin:0;">Detalles de la habitación:</p>
                                </div>
                                <div style="padding: 0rem 0 0 1.5rem;">
                                    <p style="padding-left: 17px;font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 1.5rem; color: #2E2E2E;">
                                        <strong style="color: #2E2E2E; font-size: 15px;">Cantidad de habitaciones: </strong> {{ count($hotelReservation['reservations_hotel_rooms']) }}
                                        <br>
                                        @if($hotelReservation['notes']!=="" && $hotelReservation['notes']!==null)
                                            <strong style="color: #2E2E2E; font-size: 15px;">Nota: </strong> {{ $hotelReservation['notes'] }}
                                        @endif
                                    </p>

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

                                            <!--SI ES: HYPERGUEST -->
                                            @if($resHotRoom['channel_code'] != 'AURORA')
                                                @if($reservation['mail_type'] != 'cancelation')
                                                    <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E;"><strong style="color: #2E2E2E; font-size: 13px;">Channel:</strong> {{ $resHotRoom['channel_code'] }}</p>
                                                @endif
                                                <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E;"><strong style="color: #2E2E2E; font-size: 13px;">Status Channel:</strong>
                                                    @switch($resHotRoom['status_in_channel'])
                                                        @case(0)
                                                        <b>Cancelado</b> {{ $resHotRoom['channel_reservation_code_master'] ?? '' }}
                                                        @break

                                                        @case(3)
                                                        <b>On Request</b> {{ $resHotRoom['channel_reservation_code_master'] ?? '' }}
                                                        @break

                                                        @default
                                                        @if($resHotRoom['onRequest'] == "1" and  !empty($resHotRoom['channel_reservation_code_master']))
                                                           <b>Confirmado</b>
                                                        @else
                                                           <b>Booking failure</b>
                                                        @endif

                                                    @endswitch
                                                </p>

                                                <!-- SI ESTA OK, Y TIENE CODIGO DE RESERVA DE HYPERGUEST -->
                                                @if($resHotRoom['onRequest'] == "1" and !empty($resHotRoom['channel_reservation_code_master']))
                                                    <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E;"><strong style="color: #2E2E2E; font-size: 13px;">Código Confirmación Channel:</strong> {{ $resHotRoom['channel_reservation_code_master'] }}</p>
                                                @endif
                                            <!-- SI ES: AURORA -->
                                            @else
                                                <!-- SI ESTA EN RQ/OK, Y TIENE CODIGO DE CONFIRMACION DE AURORA -->
                                                @if(!empty($resHotRoom['channel_reservation_code']))
                                                    <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E;"><strong style="color: #2E2E2E; font-size: 13px;"> Código de Confirmación:</strong> {{ $resHotRoom['channel_reservation_code'] }}</p>
                                                @endif
                                            @endif

                                            <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E; "><strong style="color: #2E2E2E; font-size: 13px;">Plan tarifario: </strong> {{ $resHotRoom['rate_plan_name'] }} | {{ $resHotRoom['rate_plan_code'] }} </p>

                                            @if($reservation['mail_type'] == 'cancelation')
                                                <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E; ">
                                                    <strong style="color: #2E2E2E; font-size: 13px;">Penalidad: </strong>
                                                    @if( isset($reservation['mail_config_to']) && $reservation['mail_config_to']==='hotel')
                                                        USD {{ $resHotRoom['penality_base'] }}
                                                    @else
                                                        USD {{ $resHotRoom['penality'] }}
                                                    @endif
                                                </p>
                                            @endif

                                            @empty(!$resHotRoom['guest_note'])
                                                <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E; "><strong style="color: #2E2E2E; font-size: 13px;">Nota Ejecutiva: </strong> {{ $resHotRoom['guest_note'] }} </p>
                                            @endempty
                                            <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E; ">

                                                @if( $resHotRoom['status'] !== 0 )
                                                    @if( $reservation['confirmed'] !== 1 && $reservation['confirmed'] !== 4 )
                                                        @if( $resHotRoom['onRequest'] == 1 )
                                                            <span style="float: left; display: flex;background: #06C270;border-radius: .5rem;width: max-content;padding: 0.5rem;color: white;font-weight: 600;font-size: 14px;">
                                                                    <img src="https://backend.limatours.com.pe/images/check-list.png" alt="#" width="26" height="15" style="display:block; padding-right: 0.2rem;"
                                                                         mc:label="header_image" mc:edit="module_image" mc:allowdesigner mc:allowtext/>
                                                                    Confirmada
                                                                </span>
                                                            <a style="float: left; margin-left: 10px; border-radius: .5rem;" href="https://aurora.limatours.com.pe/register_codcfm/{{ $reservation['file_code'] }}?lang={{ $reservation['lang'] }}&codsvs={{ $hotelReservation['hotel_code'] }}"
                                                               target="_blank" class="button">Registrar</a>
                                                        @else
                                                            <span style="float: left; display: flex; background: #FFCC00; border-radius: .5rem;width: max-content;padding: 0.5rem;color: white;font-weight: 600;font-size: 14px;">
                                                                    <img src="https://backend.limatours.com.pe/images/warning.png" alt="#" width="26" height="15" style="display:block; padding-right: 0.2rem;"
                                                                         mc:label="header_image" mc:edit="module_image" mc:allowdesigner mc:allowtext/>
                                                                    On request
                                                                </span>
                                                        @endif
                                                    @else
                                                        {{--                                                             Es confirmado pero debe salir generalizado --}}
                                                    @endif
                                                @else
                                                    <span style="float: left; display: flex; background: #FF3B3B; border-radius: .5rem;width: max-content;padding: 0.5rem;color: white;font-weight: 600;font-size: 14px;">
                                                            <img src="https://backend.limatours.com.pe/images/error-icon.png" alt="#" width="28" height="15" style="display:block; padding-right: 0.2rem;"
                                                                 mc:label="header_image" mc:edit="module_image" mc:allowdesigner mc:allowtext/>
                                                            Cancelado
                                                        </span>
                                                @endif

                                            </p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div>
                                <div style="padding: 0rem 0 0 1.5rem;">
                                    <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E;"><strong style="color: #2E2E2E; font-size: 13px;">Total tarifa: </strong> USD {{ $hotelReservation['total_amount_base'] }}</p>
                                </div>
                            </div>

                            <hr>

                        @endforeach

                        @if($reservation['mail_type'] != 'cancelation' && isset($reservation['confirmed']))
                            <div>
                                @if( $reservation['confirmed'] == 4 )
                                    <div>
                                        <div style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E; display: flex; align-items: center; margin-top: 1.5rem;">
                                            <strong style="color: #2E2E2E; font-size: 13px; margin-top: 7px; margin-right: 1.5rem; padding: 0rem 0 0 1.5rem;">Status reserva:</strong>
                                            <div style="display: flex; background: #FFCC00; border-radius: .5rem;width: max-content;padding: 0.5rem;color: white;font-weight: 600;font-size: 14px;">
                                                <img src="https://backend.limatours.com.pe/images/warning.png" alt="#" width="24" height="15" style="display:block; padding-right: 0.2rem;"
                                                     mc:label="header_image" mc:edit="module_image" mc:allowdesigner mc:allowtext/>
                                                On request
                                            </div>

                                        </div>

                                    </div>
                                @endif

                                @if( $reservation['confirmed'] == 1 )
                                    <div>
                                        <div style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E; display: flex; align-items: center; margin-top: 1.5rem;">
                                            <strong style="color: #2E2E2E; font-size: 13px; margin-top: 7px; margin-right: 1.5rem; padding: 0rem 0 0 1.5rem;">Status reserva:</strong>
                                            <div style="display: flex; background: #06C270; border-radius: .5rem;width: max-content;padding: 0.5rem;color: white;font-weight: 600;font-size: 14px;">
                                                <img src="https://backend.limatours.com.pe/images/check-list.png" alt="#" width="24" height="15" style="display:block; padding-right: 0.2rem;"
                                                     mc:label="header_image" mc:edit="module_image" mc:allowdesigner mc:allowtext/>
                                                Confirmado
                                            </div>

                                        </div>

                                    </div>
                                @endif

                                @if( $reservation['confirmed'] == 1 || $reservation['confirmed'] == 4 )
                                    <div>
                                        <p style="font-weight: 500; text-align: left; margin-top: 1rem; margin-bottom: 1rem; color: #2E2E2E; padding: 0 1.5rem;">Registrar la confirmación de la reserva y el código dando click en el botón:</p>
                                    </div>
                                    <a href="https://aurora.limatours.com.pe/register_codcfm/{{ $reservation['file_code'] }}?lang={{ $reservation['lang'] }}&codsvs={{ $hotelReservation['hotel_code'] }}"
                                       target="_blank" class="button">Registrar</a>
                                @endif

                            </div>
                        @endif

                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <!-- Section: End: Text Coloumn -->

@endsection
