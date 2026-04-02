@extends('emails.reservations.layout')
@section('content')

    <table style="width: 600px; margin: 0 auto; background: #ffffff;">
        <tr>
            <td><b>Detalle compra hotel(es)
                    @if( $reservation['mail_type'] === 'modification' )
                        actualizado(s)
                    @endif
                    :
                </b></td>
            <td></td>
        </tr>
        <tr>
            <td>Nombre de Grupo:</td>
            <td>{{ $reservation['customer_name'] }}</td>
        </tr>
        <tr>
            <td>Fecha reserva:</td>
            <td>{{ $reservation['hotels'][0]['created_at'] }}</td>
        </tr>
        <tr>
            <td>Ejecutivo:</td>
            <td>{{ $reservation['hotels'][0]['executive_name'] }}</td>
        </tr>
        <tr>
            <td>Ejecutivo Email:</td>
            <td>{{ $reservation['executive_email'] }}</td>
        </tr>
        <tr>
            <td>Hotel:</td>
            <td>{{ $reservation['hotels'][0]['hotel_name'] }}</td>
        </tr>
        @foreach($reservation['hotels'] as $hotelReservation)
            <tr>
                <td colspan="2">
                    -----------------------------------------------------------------------------------------------------
                </td>
            </tr>
            <tr>
                <td>Fecha llegada:</td>
                <td>{{ $hotelReservation['check_in'] }} {{ $hotelReservation['check_in_time'] }}</td>
            </tr>
            <tr>
                <td>Fecha salida:</td>
                <td>{{ $hotelReservation['check_out'] }} {{ $hotelReservation['check_out_time'] }}</td>
            </tr>
            <tr>
                <td>Número de habitaciones:</td>
                <td>{{ count($hotelReservation['reservations_hotel_rooms']) }}</td>
            </tr>
            <tr>
                <td>Notas:</td>
                <td>{{ $hotelReservation['notes'] }}</td>
            </tr>
            @foreach($hotelReservation['reservations_hotel_rooms'] as $resHotRoom)

                @if( isset($resHotRoom['status']) and $resHotRoom['status'] !== 0 )

                    <tr>
                        <td>Habitacion:</td>
                        <td>{{ $resHotRoom['room_name'] }} | {{ $resHotRoom['room_code'] }} </td>
                    </tr>
                    @if( $resHotRoom['channel_reservation_code'] !== '' &&  $resHotRoom['channel_reservation_code'] !== null )
                        <tr>
                            <td>Código de Reserva:</td>
                            <td>{{ $resHotRoom['channel_reservation_code'] }}</td>
                        </tr>
                    @endif
                    @if( $resHotRoom['rate_plan_name'] !== '-' )
                        <tr>
                            <td>Plan Tarifario:</td>
                            <td>{{ $resHotRoom['rate_plan_name'] }} | {{ $resHotRoom['rate_plan_code'] }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td>Ocupantes</td>
                        <td>Adultos {{ $resHotRoom['adult_num'] }} | Niños {{ $resHotRoom['child_num'] }} |
                            Extras {{ $resHotRoom['extra_num'] }}</td>
                    </tr>
                    {{-- <tr>
                        <td>Status Stela:</td>
                        @switch($resHotRoom['status'])
                            @case(0)
                            <td>Cancelado</td>
                            @break

                            @case(3)
                            <td>On Request</td>
                            @break

                            @default
                            <td>Confirmado</td>
                        @endswitch
                    </tr> --}}

                    @if($reservation['mail_type'] != 'CANCELATION')
                    <tr>
                        <td>Status Reserva:</td>
                        @switch($resHotRoom['onRequest'])
                            @case(1)
                            <td><span style="color: darkgreen; font-weight: bold ">Confirmado por vía Allotment</span>
                                <br>
                                <a style="color: #910009;" href="https://aurora.limatours.com.pe/register_codcfm/{{ $reservation['file_code'] }}?lang={{ $reservation['lang'] }}&codsvs={{ $hotelReservation['hotel_code'] }}"
                                   target="_blank">Por favor registrar la confirmacion de la reserva y el código aquí</a>
                            </td>
                            @break
                            @case(0)
                            <td><span style="color: darkred; font-weight: bold ">On Request</span>
    {{--                            <br>--}}
    {{--                            <a style="color: #910009;" href="https://aurora.limatours.com.pe/register_codcfm/{{ $reservation['file_code'] }}?lang={{ $reservation['lang'] }}&codsvs={{ $hotelReservation['hotel_code'] }}"--}}
    {{--                               target="_blank">Por favor registrar la confirmacion de la reserva y el código aquí</a>--}}
                            </td>
                            @break

                        @endswitch
                    </tr>
                    @endif

                    @if($resHotRoom['channel_code'] != 'AURORA')
                        @if($reservation['mail_type'] != 'CANCELATION')
                        <tr>
                            <td>Channel:</td>
                            <td>{{ $resHotRoom['channel_code'] }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td>Status Channel:</td>
                            @switch($resHotRoom['status_in_channel'])
                                @case(0)
                                <td>
                                    <b>Cancelado</b> {{ $resHotRoom['channel_reservation_code'] ? $resHotRoom['channel_reservation_code']: '' }}</td>
                                @break

                                @case(3)
                                <td>On
                                    Request {{ $resHotRoom['channel_reservation_code'] ? $resHotRoom['channel_reservation_code']: '' }}</td>
                                @break

                                @default
                                <td>
                                    Confirmado {{ $resHotRoom['channel_reservation_code'] ? $resHotRoom['channel_reservation_code']: '' }}</td>
                            @endswitch
                        </tr>
                        <tr>
                            <td>Codigo Confirmación Channel:</td>
                            <td style="font-weight: bold">{{ $resHotRoom['channel_reservation_code'] ? $resHotRoom['channel_reservation_code']: '' }}</td>
                        </tr>

                    @endif

                    @empty(!$resHotRoom['guest_note'])
                        <tr>
                            <td>Nota Ejecutiva</td>
                            <td>{{ $resHotRoom['guest_note'] }}</td>
                        </tr>
                    @endempty

                    {{--@empty(!$resHotRoom['observations'])--}}
                    {{--<tr>--}}
                    {{--<td>Observacion</td>--}}
                    {{--<td>{{ $resHotRoom['observations'] }}</td>--}}
                    {{--</tr>--}}
                    {{--@endempty--}}


                    {{-- @foreach($resHotRoom['policies_cancellation'] as $penalty)
                        <tr>
                            @if ($loop->first)
                                <td>Politicas de Cancelación:</td>
                            @else
                                <td></td>
                            @endif
                            <td>{{ $penalty['message'] }}</td>
                        </tr>
                    @endforeach --}}


                @else
                        <tr>
                            <td>Habitación:</td>
                            <td>{{ $resHotRoom['room_name'] }} | {{ $resHotRoom['room_code'] }} </td>
                        </tr>
                        @if( $resHotRoom['channel_reservation_code'] !== '' &&  $resHotRoom['channel_reservation_code'] !== null )
                            <tr>
                                <td>Código de Reserva:</td>
                                <td>{{ $resHotRoom['channel_reservation_code'] }}</td>
                            </tr>
                        @endif
                @endif

            @endforeach
            {{-- <tr>
                <td>Subtotal</td>
                <td>
                    USD {{ $hotelReservation['total_amount'] - $hotelReservation['total_tax_and_services_amount'] }}</td>
            </tr>
            <tr>
                <td>Impuestos y Servicios</td>
                <td>USD {{ $hotelReservation['total_tax_and_services_amount'] }}</td>
            </tr> --}}
            <tr>
                <td>Total</td>
                <td>USD {{ $hotelReservation['total_amount_base'] }}</td>
            </tr>
        @endforeach

        </table>
@endsection
