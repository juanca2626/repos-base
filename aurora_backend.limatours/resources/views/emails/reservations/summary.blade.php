@extends('emails.reservations.layout')
@section('content')
    @if(count($reservation['hotels']) > 0)
        <h3>Hoteles</h3>
        <hr>
        @foreach($reservation['hotels'] as $hotelReservation)
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
                    <td>{{ $hotelReservation['hotel_name'] }}</td>
                </tr>
                <tr>
                    <td>Fecha reserva:</td>
                    <td>{{ $hotelReservation['created_at'] }}</td>
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
                    <tr>
                        <td>Ocupantes</td>
                        <td>Adultos {{ $resHotRoom['adult_num'] }} | Niños {{ $resHotRoom['child_num'] }} |
                            Extras {{ $resHotRoom['extra_num'] }}</td>
                    </tr>


                    @if($resHotRoom['channel_code'] != 'AURORA')
                        <tr>
                            <td>Status Channel:</td>
                            @switch($resHotRoom['status_in_channel'])
                                @case(0)
                                <td>
                                    <b>Cancelado</b> {{ $resHotRoom['channel_reservation_code'] ? $resHotRoom['channel_reservation_code']: '' }}
                                </td>
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
            </table>
        @endforeach
    @endif
    @if(count($reservation['services']) > 0)
        <h3>Servicios</h3>
        <hr>
        @foreach($reservation['services'] as $serviceReservation)
            <table style="width: 600px; margin: 0 auto; background: #ffffff;">
                <tr>
                    <td><b>Detalle compra:</b></td>
                    <td></td>
                </tr>
                <tr>
                    <td>Nombre de Grupo:</td>
                    <td>{{ $reservation['customer_name'] }}</td>
                </tr>
                <tr>
                    <td>Servicio:</td>
                    <td>{{ $serviceReservation['service_code'] }} - {{ $serviceReservation['service_name'] }}</td>
                </tr>
                <tr>
                    <td>Fecha reserva:</td>
                    <td>{{ $serviceReservation['created_at'] }}</td>
                </tr>
                <tr>
                    <td>Fecha de servicio:</td>
                    <td>{{ $serviceReservation['date'] }} {{ $serviceReservation['time'] }}</td>
                </tr>
                <tr>
                    <td>Ocupantes:</td>
                    <td>Adultos {{ $serviceReservation['adult_num'] }} | Niños {{ $serviceReservation['child_num'] }} |
                        Infantes {{ $serviceReservation['infant_num'] }}
                    </td>
                </tr>
                @empty(!$serviceReservation['guest_note'])
                    <tr>
                        <td>Nota Ejecutiva</td>
                        <td>{{ $serviceReservation['guest_note'] }}</td>
                    </tr>
                @endempty
                <tr>
                    <td>Total</td>
                    <td>USD {{ $serviceReservation['total_amount'] }}</td>
                </tr>
            </table>
        @endforeach
    @endif
@endsection
