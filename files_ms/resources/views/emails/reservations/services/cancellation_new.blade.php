@extends('emails.reservations.layout')
@section('content')

    <!-- Section: End: Full-width Image -->
    <!-- Section: Start: Text Coloumn -->
    <tr mc:repeatable="module" mc:variant="module_text_row" mc:hideable>
        <td align="center" valign="top">
            <table border="0" cellpadding="0" cellspacing="0" width="600" class="full_width"
                    style="margin-bottom: 0;">
                <tr>
                    <td  align="center" valign="top" bgcolor="#ffffff" class="full_width_text"
                        mc:edit="module_body"> 
                        
                        <img src="https://backend.limatours.com.pe/images/cancel.png" alt="#" width="100%" style="margin: 12px; max-width:10%; display:block; width:10%; height:auto; padding-top: 0rem;border-radius: .5rem;"
                        mc:label="header_image" mc:edit="module_image" mc:allowdesigner mc:allowtext/> 

                        <h3 style="padding: 0rem;color: #EB5757; font-size: 1.5rem; font-weight: 700; letter-spacing: 1px;text-align: center; margin-bottom: 0rem;">
                            Cancelación de Reserva
                        </h3>
                        
                        <p style="font-weight: 500; text-align: center; margin-top: 0.5rem; color: #2E2E2E; font-size: 16px;">
                            {{ $services['supplier_name'] }}
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
                                El ejecutivo(a) <strong>{{ $file['executive_name'] }}</strong>  acaba de realizar una reserva.                              
                            </p>

                            <p style="font-weight: 500; text-align: left; margin-bottom: 1rem; color: #2E2E2E;">
                                Esperamos su anulación en la respuesta de este e-mail.
                            </p>

                            <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E;"><strong style="color: #2E2E2E;">Número de file:</strong> {{ $file['file_number']}}</p>
                            <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E;"><strong style="color: #2E2E2E;">Nombre de file:</strong> {{ $file['description'] }}</p>                            
                            <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E;"><strong style="color: #2E2E2E;">Nacionalidad:</strong>{{ $file['client_nationality'] }}</p>
                            
                            <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E;">
                                <strong style="color: #2E2E2E;">Fecha de reserva:</strong>                                
                                {{ \Carbon\Carbon::parse($file['created_at'])->format('d/m/Y') }}
                            </p>
                            <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 2rem;"><strong style="color: #2E2E2E;">E-mail del ejecutivo:</strong>
                                <a href="#" style="color: #EB5757; border-bottom: 1px solid #EB5757; text-decoration: none;">{{ $file['executive_email'] }}</a>
                            </p>
                      
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
                                           
                        @if($services['notas'])
                            <div style="padding-bottom: 30px;">
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
                                    {{ $services['notas'] }}                                     
                                </span>
                                </p>
                            </div>
                        @endif
 
                 
 

                        <div>
                            <div style="display: flex; padding:  1rem 1rem 0rem 1rem;width: 100%;">
                                <img src="https://backend.limatours.com.pe/images/bed.png"
                                        alt="#" width="24" height="20"
                                        style="display:block; padding-right: 0.2rem;"
                                        mc:label="header_image" mc:edit="module_image" mc:allowdesigner
                                        mc:allowtext/>
                                <p style="font-weight: 600; text-align: left; color: #EB5757; margin:0; width: 100%;">
                                    
                                    <strong >Cantidad y tipo de pasajeros</strong> 
                                    <strong style=" float: right; margin-right: 68px; font-weight: 500; color:black">Adultos <span style="margin-left: 12px;">{{  $file['adults'] }}</span></strong>                                     
                                </p>
                            </div>
                          
                            <div style="overflow: hidden; padding: 0 0.8rem;margin-bottom: 14px;">

                                <div style="float:left; display: block; margin-left: 10px; margin-right: 10px; width: 100%;">
                                                                                                                                  
                                                                 
                                    <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 1.5rem; color: #2E2E2E; ">
                                        <span style="color: #2E2E2E; "></span> 
                                        <span style="color: #2E2E2E;  float: right; margin-right: 49px;">Niños: <span style="margin-left: 25px;">{{  $file['children'] }}</span>  </span> 
                                    </p>
                                    <div style="clear: both;"></div>
                                    <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E; ">
                                        <span style="color: #2E2E2E; "></span> 
                                        <span style="color: #2E2E2E;  float: right; margin-right: 48px;">Infantes: <span style="margin-left: 6px;">{{  $file['infants'] }}</span>  </span> 
                                    </p>   
                                    <div style="clear: both;"></div>                                 
                                </div>                        
                            </div>
 
                        </div>
                        
                        <div style="    margin-bottom: 35px;">
                            <div style="display: flex; padding:  1rem 1rem 0.3rem 1rem;width: 100%;">
                                <img src="https://backend.limatours.com.pe/images/bed.png"
                                        alt="#" width="24" height="20"
                                        style="display:block; padding-right: 0.2rem;"
                                        mc:label="header_image" mc:edit="module_image" mc:allowdesigner
                                        mc:allowtext/>
                                <p style="font-weight: 600; text-align: left; color: #EB5757; margin:0; width: 100%;">
                                    
                                    <strong >Nombre de los servicios a reservar</strong> 
                                    <strong style=" float: right; margin-right: 58px;">$ Penalidad</strong>                                     
                                </p>
                            </div>
                          
                            <div style="overflow: hidden; padding: 0 0.8rem;">

                                <div style="float:left; display: block; margin-left: 10px; margin-right: 10px; width: 100%;">
                                                                                                                                  
                                    @foreach($services['components'] as $component)                                
                                    <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E; ">
                                        <span style="color: #2E2E2E; ">{{ \Carbon\Carbon::parse($component['date_in'])->format('d/m/Y') }} </span> -
                                        <span style="color: #2E2E2E; ">{{ $component['name'] }}  </span> 
                                        <span style="color: #2E2E2E;  float: right; margin-right: 65px;">USD  {{ number_format($component['penality'], 2, '.', '') }}  </span> 
                                    </p>
                                    @endforeach                                   
                                </div>                        
                            </div>
                        </div>

                        <hr>   

                        <div style="    margin-bottom: 35px;">    
                            <div style="display: flex; padding:  0.5rem 1rem 0.3rem 1rem;width: 100%;">
                                
                                <p style="font-weight: 500; text-align: left; color: #EB5757; margin:0; width: 100%;">
                                    
                                    <span style="color: black;margin-left: 7px;" >Total penalidad</span> 
                                    <strong style=" float: right; margin-right: 81px;">USD  {{ number_format($services['total_penality'], 2, '.', '') }} </strong>                                     
                                </p>
                            </div>

                        </div>

                           
                        <div style="margin-bottom: 35px;">

                            <div style="overflow: hidden; padding: 0 0.8rem;">

                                <div style="float:left; display: block; margin-left: 10px; margin-right: 10px; width: 100%;">                                                                                                                                                                           
                                    <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E; ">
                                        <span style="color: #2E2E2E; float: left; height: 50px;padding-top: 10px;font-weight: 700;">Status de la reserva:</span> 
                                        <span style="color: #2E2E2E;  float: left; margin-left: 20px;">
                                            
                                            <span style="float: left; display: flex; background: #FF3B3B; border-radius: .5rem;width: max-content;padding: 0.5rem;color: white;font-weight: 600;font-size: 14px; margin-left: 5px">
                                                <img src="https://backend.limatours.com.pe/images/error-icon.png" alt="#" width="26" height="15" style="display:block; padding-right: 0.2rem;"
                                                mc:label="header_image" mc:edit="module_image" mc:allowdesigner mc:allowtext/>
                                                            Cancelada
                                            </span> 

                                        </span> 
                                    </p>
                                                            
                                </div>                        
                            </div>                        
                            
                            @foreach($services['components'] as $index => $component) 
                            <div style="overflow: hidden; padding: 0 0.8rem;">
                                <div style="float:left; display: block; margin-left: 10px; margin-right: 10px; width: 100%;">                                                                                                                                                                           
                                    @if($index == 0)
                                    <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E; ">
                                        <span style="color: black;font-weight: 700;" >Políticas de cancelación</span> 
                                        <span style="color: #2E2E2E; float: left; height: 50px;padding-top: 10px;">{{ $component['penality_detail'] }}</span> 
                                         
                                    </p> 
                                    @else
                                    <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #2E2E2E; ">
                                        <span style="color: #2E2E2E; float: left; " ></span> 
                                        <span style="color: #2E2E2E; float: left; height: 50px;padding-top: 10px;">{{ $component['penality_detail'] }}</span>                                        
                                    </p> 
                                    @endif
                                </div>                                 
                            </div>
                            @endforeach 
                            
                        </div>
           

                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <!-- Section: End: Text Coloumn -->

@endsection
