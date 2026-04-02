@extends('emails.reservations.layout')
@section('content')

    <table style="width: 600px; margin: 0 auto; background: #ffffff;">
        <tr>
            <td><b>Detalle compra habitacion(es) cancelada(s)
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
                <td>Codigo de aurora:</td>
                <td>{{ $hotelReservation['id'] }}</td>
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

                    <tr>
                        <td>Status Reserva:</td>
                        <td><span style="color: #eead3c; font-weight: bold ">Cancelado</span>
                        </td>
                    </tr>

                    <tr>
                        <td>Código de Reserva:</td>
                        <td><b>{{ $resHotRoom['channel_reservation_code'] }}</b></td>
                    </tr>

                    <tr>
                        <td>Penalidad:</td>
                        @if($reservation['mail_config_to']==='hotel')
                            <td>USD {{ $resHotRoom['penality_base'] }}</td>
                        @else
                            <td>USD {{ $resHotRoom['penality'] }}</td>
                        @endif
                    </tr>

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
{{--            <tr>--}}
{{--                <td>Total</td>--}}
{{--                <td>USD {{ $hotelReservation['total_amount_base'] }}</td>--}}
{{--            </tr>--}}
        @endforeach

        </table>
@endsection
