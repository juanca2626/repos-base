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
            @foreach( $data["ranges_quote"] as $range)
                <td style="background-color: #8d0a0d;color: #ffffff"></td>
            @endforeach
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
            @foreach( $data["ranges_quote"] as $range)
                <td style="background-color: #8d0a0d;color: #ffffff"></td>
            @endforeach
        </tr>
        <tr>
            <td style="background-color: #8d0a0d;color: #ffffff">
                @if( $data["lang"] === 'es' )
                    Idioma
                @elseif($data["lang"] === 'pt')
                    Idioma
                @else
                    Idiom
                @endif
                : {{ $data["lang"] }}</td>
            <td style="background-color: #8d0a0d;color: #ffffff"></td>
            @foreach( $data["ranges_quote"] as $range)
                <td style="background-color: #8d0a0d;color: #ffffff"></td>
                <td style="background-color: #8d0a0d;color: #ffffff"></td>
                <td style="background-color: #8d0a0d;color: #ffffff"></td>
            @endforeach
        </tr>

        <tr>
            <td style="background-color: #8d0a0d;color: #ffffff">
                @if( $data["lang"] === 'es' )
                    Ocupación
                @elseif($data["lang"] === 'pt')
                    Ocupação
                @else 
                    Occupation
                @endif

                @php
                    $ocupations = [];
                    if($data["accommodation"]["single"]>0){
                        array_push($ocupations,$data["accommodation"]["single"].' SGL' );
                    }
                    if($data["accommodation"]["double"]>0){
                        array_push($ocupations,$data["accommodation"]["double"].' DBL' );
                    }     
                    if($data["accommodation"]["triple"]>0){
                        array_push($ocupations,$data["accommodation"]["triple"].' TPL' );
                    }                                    
                @endphp

                : {{ implode(" + ", $ocupations) }} 
            </td>
            <td style="background-color: #8d0a0d;color: #ffffff"></td>
            @foreach( $data["ranges_quote"] as $range)
                <td style="background-color: #8d0a0d;color: #ffffff"></td>
                <td style="background-color: #8d0a0d;color: #ffffff"></td>
                <td style="background-color: #8d0a0d;color: #ffffff"></td>
            @endforeach
        </tr>

    </table>



    <table>
        <thead>
        <tr>
            <td style="background-color: #8d0a0d;color: #ffffff;" height="20">
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
            @php
                $columns = 0;
                if($data["accommodation"]["single"] == "1"){
                    $columns = $columns + 1;
                }
                if($data["accommodation"]["double"] == "1"){
                    $columns = $columns + 1;
                }
                if($data["accommodation"]["triple"] == "1"){
                    $columns = $columns + 1;
                }                
            @endphp
                
            @foreach( $data["ranges_quote"] as $range)
                <td style="background-color: #8d0a0d;color: #ffffff" colspan="{{ $columns }}"><b>MIN {{ $range["from"]}} - {{ $range["to"] }}</b>
                </td>
            @endforeach
        </tr>
        <tr>
            <td style="background-color: #8d0a0d;color: #ffffff;" height="20"></td>
            <td style="background-color: #8d0a0d;color: #ffffff;" height="20"></td>
            @for ($i = 0; $i < count($data["ranges_quote"]); $i++)
                @if($data["accommodation"]["single"] == "1")
                <td style="background-color: #8d0a0d;color: #ffffff;" height="20">Single Room</td>
                @endif
                @if($data["accommodation"]["double"] == "1")
                <td style="background-color: #8d0a0d;color: #ffffff;" height="20">Double Room</td>
                @endif
                @if($data["accommodation"]["triple"] == "1")
                <td style="background-color: #8d0a0d;color: #ffffff;" height="20">Triple Room</td>
                @endif
            @endfor
        </tr>
        </thead>
        <tbody>

        @php 
            $services = $category["services"];            
            $row = count($services) > 0 ? $services[0]['date_in_format'] : 0;
            $imprimirFila = false;
        @endphp             
    
        @foreach( $category["services"] as $service)

            @php 
                if($row != $service['date_in_format'] ){
                    $row = $service['date_in_format'];
                    $imprimirFila = true;
                }
            @endphp 

            <tr>
                


                @if($service["type"] == "hotel")
                    <td {!! $imprimirFila ? " style='height: 20px; border-top: 3px solid red;font-weight: bold;'  " : " style='font-weight: bold;'  "  !!} >{{$service["date_service"]}}</td>
                    <td {!! $imprimirFila ? " style='height: 20px; border-top: 3px solid red;font-weight: bold;'  " : "  style='font-weight: bold;'  "  !!} > 
                        {{ $service["service_code"] }} - {{ clearNameRoom($service["service_name"]) }}  - {{ $service["rate_meals"] }}
                    </td>
                    @foreach( $service["ranges"] as $range_service)
                            
                        @if($data["accommodation"]["single"] == "1")
                        <td {!! $imprimirFila ? " style='height: 20px; border-top: 3px solid red;font-weight: bold;' " : " style='font-weight: bold;' "  !!}>{{ $range_service["simple"] }}</td>
                        @endif
                        @if($data["accommodation"]["double"] == "1")
                        <td {!! $imprimirFila ? " style='height: 20px; border-top: 3px solid red;font-weight: bold;' "  : " style='font-weight: bold;' "  !!}>{{ $range_service["double"] }}</td>
                        @endif
                        @if($data["accommodation"]["triple"] == "1")
                        <td {!! $imprimirFila ? " style='height: 20px; border-top: 3px solid red;font-weight: bold;' " : " style='font-weight: bold;' "  !!}>{{ $range_service["triple"] }}</td>
                        @endif

                    @endforeach                    
                @else
                    <td {!! $imprimirFila ? " style='height: 20px; border-top: 3px solid red;'  " : ""  !!} >{{$service["date_service"]}}</td>    
                    <td {!! $imprimirFila ? " style='height: 20px; border-top: 3px solid red;'  " : ""  !!}>
                        {{ $service["service_code"] }} - {{ $service["service_name"] }}
                    </td>  
                    @foreach( $service["ranges"] as $range_service)
                                                    
                        @if($data["accommodation"]["single"] == "1")
                        <td {!! $imprimirFila ? " style='height: 20px; border-top: 3px solid red;'  " : ""  !!}>{{ $range_service["simple"] }}</td>
                        @endif
                        @if($data["accommodation"]["double"] == "1")
                        <td {!! $imprimirFila ? " style='height: 20px; border-top: 3px solid red;'  " : ""  !!}>{{ $range_service["double"] }}</td>
                        @endif
                        @if($data["accommodation"]["triple"] == "1")
                        <td {!! $imprimirFila ? " style='height: 20px; border-top: 3px solid red;'  " : ""  !!}>{{ $range_service["triple"] }}</td>
                        @endif

                    @endforeach                                          
                @endif                                    





            </tr>
            @php
            $imprimirFila = false;
            @endphp  

        @endforeach 

        <tr>
            <td></td>
            <td style="font-weight: bold; text-align: center;">Total</td>
            @foreach( $data["ranges_quote"] as $range) 
                                        
                @if($data["accommodation"]["single"] == "1")
                <td style="font-weight: bold; text-align: center;">{{ $range["simple"] }}</td>
                @endif
                @if($data["accommodation"]["double"] == "1")
                <td style="font-weight: bold; text-align: center;">{{ $range["double"] }}</td>
                @endif
                @if($data["accommodation"]["triple"] == "1")
                <td style="font-weight: bold; text-align: center;">{{ $range["triple"] }}</td>
                @endif

            @endforeach
        </tr>

        @if( $discount > 0 )
            <tr>
                <td></td>
                <td>
                    @if( $data["lang"] === 'es' )
                        Descuento por
                    @elseif($data["lang"] === 'pt')
                        Desconto para
                    @else
                        Discount for
                    @endif
                    : {{ $discount_detail }} (-{{ $discount }}%)</td>
                @foreach( $data["ranges_quote"] as $range)
                    <td>{{ round( $range["amount"] - ( $range["amount"] * ( $discount / 100 ) ), 2 ) }}</td>
                @endforeach
            </tr>
        @endif
        </tbody>
    </table>
@endforeach



@if(count($data["categories_optional"][0]["services_optional"])>0)
@foreach( $data["categories_optional"] as $category)
    <table>
        <tr>
            <td style="background-color: #8d0a0d;color: #ffffff">
                @if( $data["lang"] === 'es' )
                    Opcionales
                @elseif($data["lang"] === 'pt')
                    Opcional
                @else
                    Optional
                @endif
            </td>
        </tr>
    </table>
    <table>
        <thead>
        <tr>
            <td style="background-color: #8d0a0d;color: #ffffff;" height="20">
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
            @foreach( $data["ranges_quote_optional"] as $range)
                <td style="background-color: #8d0a0d;color: #ffffff" colspan="3"><b>MIN {{ $range["from"]}} - {{ $range["to"] }}</b>
                </td>
            @endforeach
        </tr>
        <tr>
            <td style="background-color: #8d0a0d;color: #ffffff;" height="20"></td>
            <td style="background-color: #8d0a0d;color: #ffffff;" height="20"></td>
            @for ($i = 0; $i < count($data["ranges_quote"]); $i++)
            @if($data["accommodation"]["single"] == "1")
                <td style="background-color: #8d0a0d;color: #ffffff;" height="20">Single Room</td>
                @endif
                @if($data["accommodation"]["double"] == "1")
                <td style="background-color: #8d0a0d;color: #ffffff;" height="20">Double Room</td>
                @endif
                @if($data["accommodation"]["triple"] == "1")
                <td style="background-color: #8d0a0d;color: #ffffff;" height="20">Triple Room</td>
                @endif
            
            @endfor
        </tr>        
        </thead>
        <tbody>
        @if( count( $category["services_optional"] ) == 0 )
            <tr>
                <td>
                    @if( $data["lang"] === 'es' )
                        Servicios sin tarifas encontradas
                    @elseif($data["lang"] === 'pt')
                        Serviços sem taxas encontrados
                    @else
                        Services without fees found
                    @endif
                </td>
            </tr>
        @else
            @foreach( $category["services_optional"] as $service)
                <tr>
                    @if($service["type"] == "hotel")
                        <td style="font-weight: bold;">{{$service["date_service"]}}</td>
                        <td style="font-weight: bold;">                            
                            {{ $service["service_code"] }} - {{ $service["service_name"] }} - {{ $service["room_types"] }} - {{ $service["rate_meals"] }}                                              
                        </td>
                        @foreach( $service["ranges_optional"] as $range_service)
                                                                        
                            @if($data["accommodation"]["single"] == "1")
                            <td style="font-weight: bold;">{{ $range_service["simple"] }}</td>
                            @endif
                            @if($data["accommodation"]["double"] == "1")
                            <td style="font-weight: bold;">{{ $range_service["double"] }}</td>
                            @endif
                            @if($data["accommodation"]["triple"] == "1")
                            <td style="font-weight: bold;">{{ $range_service["triple"] }}</td>
                            @endif

                        @endforeach
                    @else
                        <td>{{$service["date_service"]}}</td>
                        <td>                   
                            {{ $service["service_code"] }} - {{ $service["service_name"] }}                        
                        </td>
                        @foreach( $service["ranges_optional"] as $range_service)
                                                                      
                            @if($data["accommodation"]["single"] == "1")
                            <td>{{ $range_service["simple"] }}</td>
                            @endif
                            @if($data["accommodation"]["double"] == "1")
                            <td>{{ $range_service["double"] }}</td>
                            @endif
                            @if($data["accommodation"]["triple"] == "1")
                            <td>{{ $range_service["triple"] }}</td>
                            @endif

                        @endforeach                    
                    @endif
                </tr>
            @endforeach
        @endif
        {{-- <tr>
            <td></td>
            <td></td>
            @foreach( $data["ranges_quote_optional"] as $range)
                <td>{{ isset($range["simple"]) ? $range["simple"] : 0 }}</td>
                <td>{{ isset($range["double"]) ? $range["double"] : 0 }}</td>
                <td>{{ isset($range["triple"]) ? $range["triple"] : 0 }}</td>
            @endforeach
        </tr> --}}

        @if( $discount > 0 )
            <tr>
                <td></td>
                <td>
                    @if( $data["lang"] === 'es' )
                        Descuento por
                    @elseif($data["lang"] === 'pt')
                        Desconto para
                    @else
                        Discount for
                    @endif
                    : {{ $discount_detail }} (-{{ $discount }}%)</td>
                @foreach( $data["ranges_quote_optional"] as $range)
                    <td>{{ round( $range["amount"] - ( $range["amount"] * ( $discount / 100 ) ), 2 ) }}</td>
                @endforeach
            </tr>
        @endif
        </tbody>
    </table>
@endforeach
@endif
</html>
