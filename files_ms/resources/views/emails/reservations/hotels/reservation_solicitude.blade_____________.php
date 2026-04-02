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
                        <img src="https://backend.limatours.com.pe/images/alert.png" alt="#" width="100%" style="margin: 12px; max-width:10%; display:block; width:10%; height:auto; padding-top: 0rem;border-radius: .5rem;"
                        mc:label="header_image" mc:edit="module_image" mc:allowdesigner mc:allowtext/>                        
                        <h3 style="padding: 0rem;color: #EB5757; font-size: 1.5rem; font-weight: 700; letter-spacing: 1px;text-align: center; margin-bottom: 0rem;">
                            Solicitud de Reserva
                        </h3>
                        
                        <p style="font-weight: 500; text-align: center; margin-top: 0.5rem; color: #2E2E2E; font-size: 16px;">
                            {{ $reservation['reservations_hotel']['hotel_name'] }}
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
                            <p style="font-weight: 500; text-align: left; margin-bottom: 1rem; color: #2E2E2E;">
                                Su ejecutivo(a) <strong>{{ $reservation['executive_name'] }}</strong>  acaba de solicitar una reserva.                                
                            </p>

                            <p style="font-weight: 500; text-align: left; margin-bottom: 1rem; color: #2E2E2E;">
                                Esperamos su pronta confirmación respondiendo este e-mail. Los datos de la reserva son los siguientes:
                            </p>

                            <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E;"><strong style="color: #2E2E2E;">Número de file:</strong> {{ $reservation['file_number']}}</p>
                            <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E;"><strong style="color: #2E2E2E;">Nombre de file:</strong> {{ $reservation['description'] }}</p>                            
                            <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E;"><strong style="color: #2E2E2E;">Nacionalidad:</strong>{{ $reservation['client_nationality'] }}</p>
                            
                            <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E;">
                                <strong style="color: #2E2E2E;">Fecha de envío de reserva:</strong>
                                {{ $reservation['created_at'] }}
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

                      
                        <h3 style="padding: 0 1rem;color: #EB5757; font-size: 1.5rem; font-weight: 600; letter-spacing: 1px;text-align: left; margin-bottom: 0;">Detalles de la reserva</h3>
                      
                        @if( isset($notas))
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
                                    {{ $notas }} 
                                </span>
                                </p>
                            </div>
                        @endif
 
                        <div>
                            <div style="display: flex; padding: 1rem 1rem 0.3rem 1rem;">
                                <img src="https://backend.limatours.com.pe/images/calendar.png"
                                        alt="#" width="25" height="18"
                                        style="display:block; padding-right: 0.2rem"
                                        mc:label="header_image" mc:edit="module_image" mc:allowdesigner
                                        mc:allowtext/>
                                <p style="font-weight: 600; text-align: left; color: #EB5757; margin:0;">
                                    {{ $reservation['reservations_hotel']['check_in'] }} - {{ $reservation['reservations_hotel']['check_out'] }}
                                   
                                </p>
                            </div>
                        </div>

                        <div style="overflow: hidden; padding: 0 0.8rem;">
                            <div style="float:left; display: block; width: 492px; margin-left: 10px; margin-right: 10px;">
                                <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E;padding-left: 17px;">
                                    <strong style="color: #2E2E2E; font-size: 15px;margin-right: 10px;">Check in:</strong> <span style="padding-right: 0.5rem"> {{ $reservation['reservations_hotel']['check_in_time'] }}</span>
                                    <strong style="color: #2E2E2E; font-size: 15px;">Check out:</strong> <span style="padding-right: 0.5rem"> {{ $reservation['reservations_hotel']['check_out_time'] }}</span>
                                    <strong style="color: #2E2E2E; font-size: 15px;">Cantidad de noches:</strong> {{ $reservation['reservations_hotel']['nights'] }}
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
                                {{-- <p style="padding-left: 17px;font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 1.5rem; color: #2E2E2E;">
                                    <strong style="color: #2E2E2E; font-size: 15px;">Cantidad de habitaciones: </strong> {{ count($reservation['rooms']) }}
                                    <br>
                                    <strong style="color: #2E2E2E; font-size: 15px;">Nota: </strong> -- MATRIMONIAL --                                      
                                </p> --}}

                            </div>
                            <div style="overflow: hidden; padding: 0 0.8rem;">
                                @foreach($reservation['reservations_hotel']['rooms'] as $k => $resHotRoom)


                                    <div style="float:left; display: block; margin-bottom: 30px; margin-left: 10px; margin-right: 10px;">
                                      
                                        <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E;">
                                            <strong style="color: #2E2E2E; font-size: 13px;"> 
                                                Tipo de habitación:
                                            </strong> {{ $resHotRoom['room_name'] }}
                                        </p>
                                        <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E;">
                                            <strong style="color: #2E2E2E; font-size: 13px;"> 
                                                Cantidad de habitaciones:
                                            </strong> {{ $resHotRoom['total_rooms'] }}
                                        </p>                                          
                                        <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E;"><strong style="color: #2E2E2E; font-size: 13px;">
                                            Ocupantes:</strong> {{  $resHotRoom['occupants'] }}                                          
                                        </p>
                                    
                                        <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E; ">
                                            <strong style="color: #2E2E2E; font-size: 13px;">Plan tarifario: </strong> {{ $resHotRoom['rate_plan_name'] }} | {{ $resHotRoom['rate_plan_code'] }} 
                                        </p>
                                            
                                        <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E; ">
                                            <strong style="color: #2E2E2E; font-size: 13px;">Total tarifa: </strong> {{ $resHotRoom['amount_cost'] }} 
                                        </p>

                                        <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E; ">     
                                            
                                            <strong style="color: #2E2E2E; font-size: 13px; float: left;padding-top: 15px"> 
                                                Status de la reserva :
                                            </strong>
                                            @if($resHotRoom['onRequest'])

                                                <span style="float: left; display: flex;background: #06C270;border-radius: .5rem;width: max-content;padding: 0.5rem;color: white;font-weight: 600;font-size: 14px;">                                                
                                                    <img src="https://backend.limatours.com.pe/images/check-list.png" alt="#" width="26" height="15" style="display:block; padding-right: 0.2rem;"
                                                        mc:label="header_image" mc:edit="module_image" mc:allowdesigner mc:allowtext/>
                                                    Confirmada
                                            
                                                </span>

                                            @else
                                                <span style="float: left; display: flex;background: #FFCC00;border-radius: .5rem;width: max-content;padding: 0.5rem;color: white;font-weight: 600;font-size: 14px;">                                                
                                                    <img src="https://backend.limatours.com.pe/images/warning.png" alt="#" width="26" height="15" style="display:block; padding-right: 0.2rem;"
                                                        mc:label="header_image" mc:edit="module_image" mc:allowdesigner mc:allowtext/>
                                                    On request
                                                </span>
                                            @endif
                                                
                                            <p style="clear: both"></p>                                         
                                        </p>
                                                                               
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- <div>
                            <div style="padding: 0rem 0 0 1.5rem;">
                                <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E;"><strong style="color: #2E2E2E; font-size: 13px;">Total tarifa: </strong> USD {{ $reservation['total_cost_amount'] }}</p>
                            </div>
                        </div> --}}

                        <hr>                     

                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <!-- Section: End: Text Coloumn -->

@endsection
