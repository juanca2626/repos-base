@extends('emails.reservations.layout')
@section('content')
        <table style="width: 600px; margin: 0 auto; background: #ffffff;">
            <tr>
                <td><b>Detalle Compra:</b></td>
                <td></td>
            </tr>
            <tr>
                <td>Nombre de Grupo:</td>
                <td>{{ $reservation['customer_name'] }}</td>
            </tr>
            <tr>
                <td>Hotel:</td>
                <td>{{ $reservation['hotel_name'] }}</td>
            </tr>
            <tr>
                <td>Fecha reserva:</td>
                <td>{{ $reservation['created_at'] }}</td>
            </tr>
            <tr>
                <td>Ejecutivo:</td>
                <td>{{ $reservation['executive_name'] }}</td>
            </tr>
            <tr>
                <td>Ejecutivo Email:</td>
                <td>{{ $reservation['executive_email'] }}</td>
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
            @foreach($hotelReservation['reservations_hotel_rooms'] as $resHotRoom)
                <tr>
                    <td>Habitacion:</td>
                    <td>{{ $resHotRoom['room_name'] }} | {{ $resHotRoom['room_code'] }} </td>
                </tr>
                @if( $resHotRoom['rate_plan_name'] !== '' )
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

                @if($reservation['mail_type'] != 'CANCELATION')
                <tr>
                    <td>Status Reserva:</td>
                    @switch($resHotRoom['onRequest'])
                        @case(1)
                        <td><span style="color: darkgreen; font-weight: bold ">Confirmado</span><br>
                            @if(isset($reservation['link']))
                                <a style="color: #910009;" href="{{ $reservation['link'] }}&lang={{ $reservation['lang'] }}"
                                   target="_blank">Por favor registrar la confirmacion de la reserva y el código aquí</a>
                            @endif
                        </td>
                        @break
                        @case(0)
                        <td><span style="color: darkred; font-weight: bold ">On Request</span>
{{--                            <br>--}}
{{--                            @if(isset($reservation['link']))--}}
{{--                            <a style="color: #910009;" href="{{ $reservation['link'] }}&lang={{ $reservation['lang'] }}"--}}
{{--                               target="_blank">Por favor registrar la confirmacion de la reserva y el código aquí</a>--}}
{{--                            @endif--}}
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

            @endforeach

            <tr>
                <td>Total</td>
                <td>USD {{ $hotelReservation['total_amount_base'] }}</td>
            </tr>
            @endforeach

        </table>
@endsection
