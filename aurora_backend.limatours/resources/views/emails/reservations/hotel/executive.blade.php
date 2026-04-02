@extends('emails.reservations.layout')
@section('content')

    @if(isset($reservation['hotels']) && count($reservation['hotels']) > 0)

        @foreach($reservation['hotels'] as $hotelReservation)
            <table style="width: 600px; margin: 0 auto; background: #ffffff;">
                <tr>
                    <td><b>Detalle compra hotel(es):</b></td>
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
                        <tr>
                            <td>Plan Tarifario:</td>
                            <td>{{ $resHotRoom['rate_plan_name'] }} | {{ $resHotRoom['rate_plan_code'] }}</td>
                        </tr>
                        <tr>
                            <td>Ocupantes:</td>
                            <td>Adultos {{ $resHotRoom['adult_num'] }} | Niños {{ $resHotRoom['child_num'] }} |
                                Extras {{ $resHotRoom['extra_num'] }}</td>
                        </tr>
                        <tr>
                            <td>Subtotal:</td>
                            <td>
                                USD {{ $resHotRoom['total_amount_base'] - $resHotRoom['total_tax_and_services_amount']  }}</td>
                        </tr>
{{--                        <tr>--}}
{{--                            <td>Markup (% {{ $resHotRoom['markup'] }}):</td>--}}
{{--                            <td>--}}
{{--                                USD {{ ($resHotRoom['total_amount'] - $resHotRoom['total_tax_and_services_amount'] - $resHotRoom['total_amount_base']) }}</td>--}}
{{--                        </tr>--}}
                        <tr>
                            <td>Total</td>
                            <td>USD {{ $resHotRoom['total_amount'] - $resHotRoom['total_tax_and_services_amount']  }}</td>
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

                        <tr>
                            <td>Status Reserva:</td>
                            @switch($resHotRoom['onRequest'])
                                @case(1)
                                <td style="color: darkgreen; font-weight: bold ">Confirmado por vía Allotment</td>
                                @break
                                @case(0)
                                <td style="color: darkred; font-weight: bold ">On Request</td>
                                @break
                            @endswitch
                        </tr>

                        @if($resHotRoom['channel_code'] != 'AURORA')
                            <tr>
                                <td>Channel:</td>
                                <td>{{ $resHotRoom['channel_code'] }}</td>
                            </tr>
                            <tr>
                                <td>Status Channel:</td>
                                @switch($resHotRoom['status_in_channel'])
                                    @case(0)
                                    <td>
                                        Cancelado {{ $resHotRoom['channel_reservation_code'] ? $resHotRoom['channel_reservation_code']: '' }}</td>
                                    @break

                                    @case(3)
                                    <td>On
                                        Request {{ $resHotRoom['channel_reservation_code'] ? $resHotRoom['channel_reservation_code']: '' }}</td>
                                    @break

                                    @default
                                    <td>
                                        @if($resHotRoom['onRequest'] == "1" and $resHotRoom['channel_reservation_code'])                                             
                                             <b>Confirmado</b>
                                        @else
                                            <b>Booking failure</b>
                                        @endif
                                    </td>

                                @endswitch
                            </tr>
                            @if($resHotRoom['onRequest'] == "1" and $resHotRoom['channel_reservation_code'])
                                <tr>
                                    <td>Codigo Confirmación Channel:</td>
                                    <td style="font-weight: bold">{{ $resHotRoom['channel_reservation_code'] ? $resHotRoom['channel_reservation_code']: '' }}</td>
                                </tr>
                            @endif

                        @endif

                        
                        @empty(!$resHotRoom['guest_note'])
                            <tr>
                                <td>Nota Ejecutiva</td>
                                <td>{{ $resHotRoom['guest_note'] }}</td>
                            </tr>
                        @endempty
                        @empty(!$resHotRoom['observations'])
                            <tr>
                                <td>Observacion</td>
                                <td>{{ $resHotRoom['observations'] }}</td>
                            </tr>
                        @endempty

                        @if(isset($resHotRoom['policies_cancellation']['hayperguest_pull']))

                            <tr>                              
                                <td>Politicas de Cancelación:</td>                               
                                <td>{{ $resHotRoom['policies_cancellation']['name'] }}</td>
                            </tr>                        

                        @else
                            @foreach($resHotRoom['policies_cancellation'] as $penalty)
                                <tr>
                                    @if ($loop->first)
                                        <td>Politicas de Cancelación:</td>
                                    @else
                                        <td></td>
                                    @endif
                                    <td>{{ $penalty['message'] }}</td>
                                </tr>
                            @endforeach
                        @endif  


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
                <tr>
                    <td>Subtotal</td>
                    <td>
                        USD {{ $hotelReservation['total_amount'] - $hotelReservation['total_tax_and_services_amount'] }}</td>
                </tr>
                <tr>
                    <td>Impuestos y Servicios</td>
                    <td>USD {{ $hotelReservation['total_tax_and_services_amount'] }}</td>
                </tr>
                <tr>
                    <td>Total</td>
                    <td>USD {{ $hotelReservation['total_amount'] }}</td>
                </tr>
            </table>
        @endforeach
    @endif
    <hr>
    @if(isset($reservation['services']) && count($reservation['services']) > 0)
        @foreach($reservation['services'] as $serviceReservation)
            <table style="width: 600px; margin: 0 auto; background: #ffffff;">
                <tr>
                    <td><b>Detalle compra servicio(s):</b></td>
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
                    <td>Fecha salida:</td>
                    <td>{{ $serviceReservation['date'] }} {{ $serviceReservation['time'] }}</td>
                </tr>
                @foreach($serviceReservation['reservations_service_rates_plans'] as $resService)
                    <tr>
                        <td>Plan Tarifario:</td>
                        <td>{{ $resService['service_rate_name'] }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td>Ocupantes:</td>
                    <td>Adultos {{ $serviceReservation['adult_num'] }} | Niños {{ $serviceReservation['child_num'] }} |
                        Infantes {{ $serviceReservation['infant_num'] }}
                    </td>
                </tr>
                <tr>
                    <td>Subtotal:</td>
                    <td>
                        USD {{ $serviceReservation['total_amount'] - $serviceReservation['total_amount_taxes']  }}</td>
                </tr>
                
                <tr>
                    <td>Total</td>
                    <td>USD {{ $serviceReservation['total_amount'] }}</td>
                </tr>
                <tr>
                    <td>Status:</td>
                    @switch($serviceReservation['status'])
                        @case(0)
                        <td>Cancelado</td>
                        @break

                        @case(3)
                        <td>ON REQUEST (Pendiente de confirmación) </td>
                        @break

                        @default
                        @if (isset($serviceReservation['on_request']) && $serviceReservation['on_request'] == 1)
                            <td>ON REQUEST (Pendiente de confirmación) </td>
                        @else
                            <td>Confirmado</td>
                        @endif           
                    @endswitch
                </tr>
            </table>
        @endforeach
    @endif
@endsection
