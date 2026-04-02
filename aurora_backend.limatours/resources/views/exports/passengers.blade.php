<html>
@foreach( $data["categories"] as $category)
    <table>
        <tr>
            <td style="background-color: #8d0a0d;color: #ffffff">
                @if( $data["lang"] === 'es' )
                    Cotización
                @elseif($data["lang"] === 'pt')
                    Preço
                @else
                    Quotation
                @endif
                N°: {{ $data["quote_id"] }}</td>
            <td style="background-color: #8d0a0d;color: #ffffff"></td>
        </tr>
        <tr>
            <td style="background-color: #8d0a0d;color: #ffffff">
                @if( $data["lang"] === 'es' )
                    Tabla tarifas cotización
                @elseif($data["lang"] === 'pt')
                    Tabela de taxas de cotação
                @else
                    Quote rates table
                @endif
                : {{ $data["quote_name"] }}</td>
            <td style="background-color: #8d0a0d;color: #ffffff"></td>
        </tr>
        <tr>
            <td style="background-color: #8d0a0d;color: #ffffff">
                @if( $data["lang"] === 'es' )
                    Cliente
                @elseif($data["lang"] === 'pt')
                    Cliente
                @else
                    Client
                @endif
                : {{ $data["client_code"] }}
                ({{ $data["client_name"] }} -
                @if( $data["lang"] === 'es' )
                    Programación General
                @elseif($data["lang"] === 'pt')
                    Programação Geral
                @else
                    General Programming
                @endif
                )
            </td>
            <td style="background-color: #8d0a0d;color: #ffffff"></td>
        </tr>
        <tr>
            <td style="background-color: #8d0a0d;color: #ffffff">
                @if( $data["lang"] === 'es' )
                    Idioma
                @elseif($data["lang"] === 'pt')
                    Idioma
                @else
                    Language
                @endif
                : {{ $data["lang"] }}</td>
            <td style="background-color: #8d0a0d;color: #ffffff"></td>
        </tr>
    </table>
    <table>
        <tr>
            <td>
            
                <b>

                @php 
                    $alerta = '';
                    if( $data["lang"] === 'es' ){
                        $alerta = "Mínimo {$pax} personas ";
                    }elseif($data["lang"] === 'pt'){
                        $alerta = "Mínimo {$pax} pessoas";
                    }else{
                        $alerta = "Minimum {$pax} people";
                    }
                @endphp                                     
                    {{ $alerta }}
                </b>
 
        </tr>
        <thead>
        <tr>
            <td style="background-color: #8d0a0d;color: #ffffff;">
                <b>
                    @if( $data["lang"] === 'es' )
                        Fecha de Inicio
                    @elseif($data["lang"] === 'pt')
                        Data de início
                    @else
                        Start date
                    @endif
                </b>
            </td>
            <td style="background-color: #8d0a0d;color: #ffffff;"><b>

                    @if( $data["lang"] === 'es' )
                        Descripción
                    @elseif($data["lang"] === 'pt')
                        Descrição
                    @else
                        Description
                    @endif

                </b></td>
            @isset($data["passengers"])
                @foreach( $data["passengers"] as $p => $passenger)
                    @if($passenger["first_name"] != "")
                        <td style="background-color: #8d0a0d;color: #ffffff;">{{ $passenger["first_name"] }} {{ $passenger["last_name"] }}
                            -
                            {{ $passenger["type"] }}</td>
                    @else
                        @if( $passenger["type"] == "ADL")
                            @if( $data["lang"] === 'es' )
                                
                                <td style="background-color: #8d0a0d;color: #ffffff;"> Adulto {{ $passenger["index"] }}</td>  {{-- {{ $passenger["type"] }} --}}  
                            @elseif($data["lang"] === 'pt')
                                <td style="background-color: #8d0a0d;color: #ffffff;"> Adulto {{ $passenger["index"] }} </td>
                            @else
                                <td style="background-color: #8d0a0d;color: #ffffff;"> Adult {{ $passenger["index"] }} </td>
                            @endif

                        @else
                            @if( $data["lang"] === 'es' )
                                <td style="background-color: #8d0a0d;color: #ffffff;"> Niño {{ $passenger["index"] }} ( {{ $passenger["age"]  }} años) </td>
                            @elseif($data["lang"] === 'pt')
                                <td style="background-color: #8d0a0d;color: #ffffff;"> Niño {{ $passenger["index"] }} ( {{ $passenger["age"] }} anos) </td>
                            @else
                                <td style="background-color: #8d0a0d;color: #ffffff;"> Child {{ $passenger["index"] }} ( {{ $passenger["age"] }} years) </td>
                            @endif
                        @endif
                    @endif
                @endforeach
            @endisset
        </tr>
        </thead>
        <tbody>

        @php 
            $services = $category["services"];            
            $row = count($services) > 0 ? $services[0]['date_in_format'] : 0;
            $imprimirFila = false;
        @endphp    

        @foreach( $services as $service)
            @if($service["type"]=="service")

                @php 
                    if($row != $service['date_in_format'] ){
                        $row = $service['date_in_format'];
                        $imprimirFila = true;
                    }

                @endphp 
                
                <tr>
                    <td {!! $imprimirFila ? " style='height: 20px; border-top: 3px solid red;'  " : ""  !!} >
                        {{$service["date_in"]}}
                    </td>
                    <td {!! $imprimirFila ? " style='height: 20px; border-top: 3px solid red;'  " : ""  !!} > 
                        {{ $service["service"]["aurora_code"] }}
                        - {{ $service["service"]["service_translations"][0]["name"] }}
                    </td>
                    @foreach( $service["passengers"] as $key => $passenger)
                        <td {!! $imprimirFila ? " style='height: 20px; border-top: 3px solid red;'  " : ""  !!} >{{ $passenger["amount"] }}</td>
                    @endforeach
                </tr>

             
                @php
                $imprimirFila = false;
                @endphp  

            @endif

            @if($service["type"]=="hotel")
                @php
                    $total_accommodations = (int)$service['single'] + (int)$service['double'] + (int)$service['triple'] + (int)$service['double_child'] + (int)$service['triple_child'];                                  
                @endphp               
                @if($total_accommodations > 0)

                    @foreach($service["amount"] as $amount)

                        @php                    
                            if($row != $amount["date_in"]){
                                $row = $amount["date_in"];
                                $imprimirFila = true;
                            }                    
                        @endphp

                        <tr>
                            <td {!! $imprimirFila ? " style='height: 20px; border-top: 3px solid red;font-weight: bold;'  " : " style='font-weight: bold;' "  !!} >{{ $amount["date_service"] }}</td>
                            <td {!! $imprimirFila ? " style='height: 20px; border-top: 3px solid red;font-weight: bold;'  " : " style='font-weight: bold;' "  !!}  >
                                {{$service["hotel"]["channel"][0]["code"]}} - {{ $service["hotel"]["name"] }} -
                                {{ $service["service_rooms"][0]["rate_plan_room"]["room"]["translations"][0]["value"] }}
                                - {{ $service["service_rooms"][0]["rate_plan_room"]["rate_plan"]["meal"]["translations"][0]["value"] }}
                            </td>
                            @isset($amount["passengers"])
                                @foreach( $amount["passengers"] as $key => $passenger)
                                    <td {!! $imprimirFila ? " style='height: 20px; border-top: 3px solid red;font-weight: bold;'  " : " style='font-weight: bold;' "  !!}>{{ $passenger["amount"] }}</td>
                                @endforeach
                            @endisset
                        </tr>

                        @php
                            $imprimirFila = false;
                        @endphp 

                    @endforeach

             

                @endif
                                
             
                

            @endif
           

        @endforeach
        <tr>
            <td></td>
            <td style="font-weight: bold; text-align: center;">
                @if( $data["lang"] === 'es' )
                    Total
                @elseif($data["lang"] === 'pt')
                    Total
                @else
                    Total
                @endif

            </td> 
            @isset($data["passengers"])
                @foreach($data["passengers"] as $p => $passenger)
                    <td style="font-weight: bold; text-align: center;">{{ $passenger["total"] }}</td>
                @endforeach
            @endisset
        </tr>
        </tbody>
    </table>
@endforeach

@if(count($data["categories_optional"][0]["services"])>0)

@php
    $columna = count($data["passengers"]) + 2;
@endphp

<table>
    <thead>
        <tr><td></td></tr> 
        <tr>
            <td style="background-color: #8d0a0d;color: #ffffff; text-align: center;" colspan="{{ $columna }}">
                <b>
                    @if( $data["lang"] === 'es' )
                        Opcionales
                    @elseif($data["lang"] === 'pt')
                        Opcional
                    @else
                        Optional
                    @endif
                </b>
            </td>

        </tr>
        </thead>
</table>


@foreach( $data["categories_optional"] as $category)
    <table>
        <thead>
        <tr>
            <td style="background-color: #8d0a0d;color: #ffffff;">
                <b>
                    @if( $data["lang"] === 'es' )
                        Fecha de Inicio
                    @elseif($data["lang"] === 'pt')
                        Data de início
                    @else
                        Start date
                    @endif
                </b>
            </td>
            <td style="background-color: #8d0a0d;color: #ffffff;"><b>

                    @if( $data["lang"] === 'es' )
                        Descripción
                    @elseif($data["lang"] === 'pt')
                        Descrição
                    @else
                        Description
                    @endif

                </b></td>
            @isset($data["passengers"])
                @foreach( $data["passengers"] as $p => $passenger)
                    @if($passenger["first_name"] != "")
                        <td style="background-color: #8d0a0d;color: #ffffff;">{{ $passenger["first_name"] }} {{ $passenger["last_name"] }}
                            -
                            {{ $passenger["type"] }}</td>
                    @else
                        @if( $passenger["type"] == "ADL")
                            @if( $data["lang"] === 'es' )
                                <td style="background-color: #8d0a0d;color: #ffffff;"> Adulto {{ $passenger["index"] }}
                                    {{ $passenger["type"] }}</td>
                            @elseif($data["lang"] === 'pt')
                                <td style="background-color: #8d0a0d;color: #ffffff;"> Adulto {{ $passenger["index"] }}
                                    {{ $passenger["type"] }}</td>
                            @else
                                <td style="background-color: #8d0a0d;color: #ffffff;"> Adult {{ $passenger["index"] }}
                                    {{ $passenger["type"] }}</td>
                            @endif

                        @else
                            @if( $data["lang"] === 'es' )
                                <td style="background-color: #8d0a0d;color: #ffffff;"> Niño {{ $passenger["index"] }}
                                    {{ $passenger["type"] }}</td>
                            @elseif($data["lang"] === 'pt')
                                <td style="background-color: #8d0a0d;color: #ffffff;"> Niño {{ $passenger["index"] }}
                                    {{ $passenger["type"] }}</td>
                            @else
                                <td style="background-color: #8d0a0d;color: #ffffff;"> Child {{ $passenger["index"] }}
                                    {{ $passenger["type"] }}</td>
                            @endif
                        @endif
                    @endif
                @endforeach
            @endisset
        </tr>
        </thead>
        <tbody>
        @foreach( $category["services"] as $service)
            @if($service["type"]=="service")
                <tr>
                    <td>{{$service["date_in"]}}</td>
                    <td>
                        {{ $service["service"]["aurora_code"] }}
                        - {{ $service["service"]["service_translations"][0]["name"] }}
                    </td>
                    @foreach( $service["passengers"] as $key => $passenger)
                        <td>{{ $passenger["amount"] }}</td>
                    @endforeach
                </tr>
            @endif

            @if($service["type"]=="hotel")
                @php
                    $total_accommodations = (int)$service['single'] + (int)$service['double'] + (int)$service['triple'] + (int)$service['double_child'] + (int)$service['triple_child'];
                @endphp
                @if($total_accommodations > 0)
                    @foreach($service["amount"] as $amount)
                        <tr>
                            <td style="font-weight: bold;">{{ $amount["date_service"] }}</td>
                            <td style="font-weight: bold;">{{$service["hotel"]["channel"][0]["code"]}} - {{ $service["hotel"]["name"] }} -
                                {{ $service["service_rooms"][0]["rate_plan_room"]["room"]["translations"][0]["value"] }} 
                                - {{ $service["service_rooms"][0]["rate_plan_room"]["rate_plan"]["meal"]["translations"][0]["value"] }}
                            </td>
                            @isset($amount["passengers"])
                                @foreach( $amount["passengers"] as $key => $passenger)
                                    @if(isset($passenger["amount"]))
                                        <td style='font-weight: bold;'>{{ $passenger["amount"] }}</td>
                                    @else
                                        <td style='font-weight: bold;'>0</td>
                                    @endif
                                @endforeach
                            @endisset
                        </tr>
                    @endforeach
                @endif
            @endif
        @endforeach
        <!-- <tr>
            <td></td>
            <td style="font-weight: bold; text-align: center;">
                @if( $data["lang"] === 'es' )
                    Total
                @elseif($data["lang"] === 'pt')
                    Total
                @else
                    Total
                @endif

            </td> 
            @isset($data["passengers"])
                @foreach($data["passengers"] as $p => $passenger)
                    <td style="font-weight: bold; text-align: center;">{{ $passenger["total_optional"] }}</td>
                @endforeach
            @endisset
        </tr> -->
        </tbody>
    </table>
@endforeach

@endif

</html>
