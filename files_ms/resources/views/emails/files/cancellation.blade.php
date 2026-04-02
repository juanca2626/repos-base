@extends('emails.files.layout')
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
                            Anulación de file {{ $services['file'] }}
                        </h3>
                   
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
                        mc:edit="module_body">
                        <div style="margin-bottom: 0rem; padding: 0 1rem;">
                            <p style="font-weight: 500; text-align: left; margin-bottom: 1rem; color: #2E2E2E;">
                                El ejecutivo(a) <strong>{{ $services['executive_name'] }}</strong>  acaba de anular el file n° {{ $services['file'] }}
                            </p>
 
                            <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.8rem; color: #2E2E2E;"><strong style="color: #2E2E2E;">Motivo: </strong> {{ $services['file']}}</p>
                            <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.8rem; color: #2E2E2E;"><strong style="color: #2E2E2E;">Cliente: </strong> {{ $services['client'] }}</p>                            
                            <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.8rem; color: #2E2E2E;"><strong style="color: #2E2E2E;">Día In: </strong>{{ $services['date_in'] }}</p>
                            <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.8rem; color: #2E2E2E;"><strong style="color: #2E2E2E;">Importe: USD </strong>{{ $services['import'] }}</p>
                            <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.8rem; color: #2E2E2E;"><strong style="color: #2E2E2E;">Fecha de reserva: </strong>{{ $services['created_at'] }}</p>
                            
                            <p style="font-weight: 500; text-align: left; margin-top: 2rem; margin-bottom: 1rem; color: #EB5757; position: relative;">
                                <svg width="30" height="30" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_13705_19791)">
                                    <path d="M18.3327 9.23355V10.0002C18.3317 11.7972 17.7498 13.5458 16.6738 14.9851C15.5978 16.4244 14.0854 17.4773 12.3621 17.9868C10.6389 18.4963 8.79707 18.4351 7.11141 17.8124C5.42575 17.1896 3.98656 16.0386 3.00848 14.5311C2.0304 13.0236 1.56584 11.2403 1.68408 9.44714C1.80232 7.65402 2.49702 5.94715 3.66458 4.58111C4.83214 3.21506 6.41 2.26303 8.16284 1.867C9.91568 1.47097 11.7496 1.65216 13.391 2.38355" stroke="#EB5757" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M18.3333 3.33398L10 11.6757L7.5 9.17565" stroke="#EB5757" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </g>
                                    <defs>
                                    <clipPath id="clip0_13705_19791">
                                    <rect width="20" height="20" fill="white"/>
                                    </clipPath>
                                    </defs>
                                </svg>                            
                                <strong style="color: #EB5757;top: 5px; position: absolute; left: 34px;"> Códigos de confirmación:</strong> 
                            </p> 
                            <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 1rem; color: #2E2E2E; margin-left: 39px;">
                                <strong style="color: #2E2E2E;">Hoteles</strong> 
                            </p> 

                            @foreach($services['hotel_code'] as $hotel => $hotel_code)
                            <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.8rem; color: #2E2E2E;">{{ $hotel }}: {{ implode(",",$hotel_code) }}</p> 
                            @endforeach 
                            <p style="font-weight: 500; text-align: left; margin-top: 2rem; margin-bottom: 1rem; color: #2E2E2E;">
                                    <strong style="color: #2E2E2E;">Status del file: </strong> 
                                    <span class="cancelation">Anulado</span>
                            </p>
                            <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.8rem; color: #2E2E2E;"><strong style="color: #2E2E2E;">Politicas de cancelación: </strong> {{ $services['cancelWithPenality'] }}</p>

                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
     

@endsection
