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
                        
                        <img src="https://backend.limatours.com.pe/images/cancel.png" alt="#" width="100%" style="margin: 12px; max-width:10%; display:block; width:10%; height:auto; padding-top: 0rem;border-radius: .5rem;"
                        mc:label="header_image" mc:edit="module_image" mc:allowdesigner mc:allowtext/>

                        <h3 style="padding: 0rem;color: #EB5757; font-size: 1.5rem; font-weight: 700; letter-spacing: 1px;text-align: center; margin-bottom: 0rem;">
                            Cancelación de Reserva
                        </h3>
                        
                        <p style="font-weight: 500; text-align: center; margin-top: 0.5rem; color: #2E2E2E; font-size: 16px;">
                        {{ $composition['code'] }} - {{ $composition['name'] }}
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
                                Su ejecutivo(a) <strong>{{ $file['executive_name'] }}</strong>  acaba de cancelar la reserva.                                
                            </p>

                            <p style="font-weight: 500; text-align: left; margin-bottom: 1rem; color: #2E2E2E;">
                                Esperamos su anulación en la respuesta de este e-mail.
                            </p>

                            <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E;"><strong style="color: #2E2E2E;">Número de file:</strong> {{ $file['file_number']}}</p>
                            <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E;"><strong style="color: #2E2E2E;">Nombre de file:</strong> {{ $file['description'] }}</p>                            
                            <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E;"><strong style="color: #2E2E2E;">Nacionalidad:</strong>{{ $file['client_nationality'] }}</p>
                            
                            <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E;">
                                <strong style="color: #2E2E2E;">Fecha de reserva:</strong>                                
                                {{ \Carbon\Carbon::parse($file['itineraries']['created_at'])->format('d/m/Y') }}
                            </p>
                            <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 2rem;"><strong style="color: #2E2E2E;">E-mail del ejecutivo:</strong>
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

                      
                        <h3 style="padding: 0 1rem;color: #EB5757; font-size: 1.5rem; font-weight: 600; letter-spacing: 1px;text-align: left; margin-bottom: 0;">Detalles de la cancelación</h3>

                        <!-- <div style="display: flex;">
                            <div style="display: flex; padding: 1rem;">
                                <img src="https://backend.limatours.com.pe/images/check-icon.png"
                                        alt="#" width="24" height="20"
                                        style="display:block; padding-right: 0.2rem;"
                                        mc:label="header_image" mc:edit="module_image" mc:allowdesigner
                                        mc:allowtext/>
                                    <p style="font-weight: 600; text-align: left; color: #EB5757; margin: 0;">
                                        Código de confirmación:  $file['itineraries']['confirmation_code'] }}                                
                                    </p>
                            </div>
                        </div> -->
                     
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
 
                        <!-- <div>
                            <div style="display: flex; padding: 1rem 1rem 0.3rem 1rem;">
                                <img src="https://backend.limatours.com.pe/images/calendar.png"
                                        alt="#" width="25" height="18"
                                        style="display:block; padding-right: 0.2rem"
                                        mc:label="header_image" mc:edit="module_image" mc:allowdesigner
                                        mc:allowtext/>
                                <p style="font-weight: 600; text-align: left; color: #EB5757; margin:0;">
                                    {{ $file['itineraries']['date_in'] }} - {{ $file['itineraries']['date_out'] }}
                                   
                                </p>
                            </div>
                        </div>

                        <div style="overflow: hidden; padding: 0 0.8rem;">
                            <div style="float:left; display: block; width: 492px; margin-left: 10px; margin-right: 10px;">
                                <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E;padding-left: 17px;">
                                    <strong style="color: #2E2E2E; font-size: 15px;margin-right: 10px;">Check in:</strong> <span style="padding-right: 0.5rem"> {{ $file['itineraries']['start_time'] }}</span>
                                    <strong style="color: #2E2E2E; font-size: 15px;">Check out:</strong> <span style="padding-right: 0.5rem"> {{ $file['itineraries']['departure_time'] }}</span>
                                    <strong style="color: #2E2E2E; font-size: 15px;">Cantidad de noches:</strong> {{ $file['itineraries']['nights'] }}
                                </p>
                            </div>
                        </div> -->

 
                        <div>
                            <div style="display: flex; padding:  1rem 1rem 0.3rem 1rem;">
                                <img src="https://backend.limatours.com.pe/images/bed.png"
                                        alt="#" width="24" height="20"
                                        style="display:block; padding-right: 0.2rem;"
                                        mc:label="header_image" mc:edit="module_image" mc:allowdesigner
                                        mc:allowtext/>
                                <p style="font-weight: 600; text-align: left; color: #EB5757; margin:0;">Detalles de la Servicio:</p>
                            </div>
                          
                            <div style="overflow: hidden; padding: 0 0.8rem;">

                                <div style="float:left; display: block; margin-bottom: 30px; margin-left: 10px; margin-right: 10px;">
                                                                                                                                  
                                    <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E;"><strong style="color: #2E2E2E; font-size: 13px;">
                                        Ocupantes:</strong> {{  ($composition['total_adults'] + $composition['total_children']) }}                                          
                                    </p>
                                                                         
                                    <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E; ">
                                        <strong style="color: #2E2E2E; font-size: 13px;">Total tarifa: </strong> {{ $composition['amount_cost'] }} 
                                    </p>

                                    <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E; ">     
                                        
                                        <strong style="color: #2E2E2E; font-size: 13px; float: left;padding-top: 15px"> 
                                            Status de la reserva :
                                        </strong>
                                        <span style="float: left; display: flex; background: #FF3B3B; border-radius: .5rem;width: max-content;padding: 0.5rem;color: white;font-weight: 600;font-size: 14px; margin-left: 5px">
                                            <img src="https://backend.limatours.com.pe/images/error-icon.png" alt="#" width="28" height="15" style="display:block; padding-right: 0.2rem;"
                                                    mc:label="header_image" mc:edit="module_image" mc:allowdesigner mc:allowtext/>
                                            Cancelado
                                        </span> 
                                        <p style="clear: both"></p>                                         
                                    </p>
                                    
                                    <p style="font-weight: 500; text-align: left; margin-top: 1.5rem; margin-bottom: 0.5rem; color: #2E2E2E; ">
                                        <strong style="color: #2E2E2E; font-size: 13px;">Politicas de Penalidad:  </strong>

                                        @if($composition['penality']['penality_price'] > 0 )
                                            USD {{ $composition['penality']['penality_price'] }}
                                        @else
                                            Sin Penalidad
                                        @endif

                                    </p>

                                </div>
                        
                            </div>
                        </div>


                        <hr>                     

                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <!-- Section: End: Text Coloumn -->

@endsection
