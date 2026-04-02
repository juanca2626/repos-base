@php
    if (!function_exists('calculatePriceWithCommission')) {
        function calculatePriceWithCommission($price, $data) {
            if ($data["client_commission_status"] == 1 &&
                $data["user_type_id"] == 4 &&
                $data["client_commission"] > 0) {

                $commissionRate = floatval($data["client_commission"]) / 100;
                $priceWithCommission = $price * (1 + $commissionRate);

                // use your existing global helper
                return roundLito($priceWithCommission);
            }

            return roundLito($price);
        }
    }
@endphp

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

        <tr>
            <td >
                <b>
                @if( $data["lang"] === 'es' )
                    Acomodo
                @elseif($data["lang"] === 'pt')
                    Acomodo
                @else
                    Acomodo
                @endif

                @php
                    $ocupations = [];
                    if($data["accommodations"]["single"]>0){
                        array_push($ocupations,$data["accommodations"]["single"].' SGL' );
                    }
                    if($data["accommodations"]["double"]>0){
                        array_push($ocupations,$data["accommodations"]["double"].' DBL' );
                    }
                    if($data["accommodations"]["triple"]>0){
                        array_push($ocupations,$data["accommodations"]["triple"].' TPL' );
                    }
                @endphp

                {{ implode(" + ", $ocupations) }}
                </b>
            </td>
            <td>

            </td>
            @if( $data["client_commission_status"] == 1 && $data["user_type_id"] == 4 && $data["client_commission"] > 0 )
                <td style="background-color: #fffb00;color: #000000;">
                    <b>Con comision</b>
                </td>
            @endif
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

                </b>
            </td>


            @isset($data["accommodations"])
                @if(isset($data["accommodations"]["single"]) and $data["accommodations"]["single"]>0)
                    <td style="background-color: #8d0a0d;color: #ffffff;">Single</td>
                @endif
                @if(isset($data["accommodations"]["double"]) and $data["accommodations"]["double"]>0)
                    <td style="background-color: #8d0a0d;color: #ffffff;">Double</td>
                @endif
                @if(isset($data["accommodations"]["triple"]) and $data["accommodations"]["triple"]>0)
                    <td style="background-color: #8d0a0d;color: #ffffff;">Triple</td>
                @endif
            @endisset

        </tr>
        </thead>
        <tbody>

        @php
            $services = $category["services"];
            $row = count($services) > 0 ? $services[0]['date_in_format'] : 0;
            $imprimirFila = false;

            $totalSingle = 0;
            $totalDouble = 0;
            $totalTriple = 0;
        @endphp

        @foreach( $services as $service)
            @if($service["type"]=="service")

                @php
                    if($row != $service['date_in_format'] ){
                        $row = $service['date_in_format'];
                        $imprimirFila = true;
                    }
                    $price_dynamic = ($service['price_dynamic'] ?? 0) == 1 ? 1 : 0;
                @endphp

                <tr>
                    <td style="{{ $imprimirFila ? 'height:20px; border-top:3px solid red;' : '' }}">
                        {{$service["date_in"]}}
                    </td>
                    <td style="{{ $imprimirFila ? 'height:20px; border-top:3px solid red;' : '' }}">
                        {{ $service["service"]["aurora_code"] }}
                        - {{ $service["service"]["service_translations"][0]["name"] }}


                    </td>

                  @isset($data["accommodations"])
                    @if(isset($data["accommodations"]["single"]) and $data["accommodations"]["single"] > 0)
                        @php
                            $totalSingle += calculatePriceWithCommission($service["single_import"], $data);
                        @endphp
                        <td
                            style="{{ $imprimirFila ? 'border-top:3px solid red;' : '' }} {{ $price_dynamic == 1 ? 'color:red;' : '' }}"
                        >
                            {{ calculatePriceWithCommission($service["single_import"], $data) }}

                        </td>
                    @endif

                    @if(isset($data["accommodations"]["double"]) and $data["accommodations"]["double"] > 0)
                        @php
                            $totalDouble += calculatePriceWithCommission($service["double_import"], $data);
                        @endphp
                        <td style="{{ $imprimirFila ? 'border-top:3px solid red;' : '' }} {{ $price_dynamic == 1 ? 'color:red;' : '' }}">
                            {{ calculatePriceWithCommission($service["double_import"], $data) }}
                        </td>
                    @endif

                    @if(isset($data["accommodations"]["triple"]) and $data["accommodations"]["triple"] > 0)
                        @php
                            $totalTriple += calculatePriceWithCommission($service["triple_import"], $data);
                        @endphp
                        <td
                            style="{{ $imprimirFila ? 'border-top:3px solid red;' : '' }} {{ $price_dynamic == 1 ? 'color:red;' : '' }}"
                        >
                            {{ calculatePriceWithCommission($service["triple_import"], $data) }}
                        </td>
                    @endif
                @endisset

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
                                @if(!$service["hyperguest_pull"])                                    
                                    {{ $service["service_rooms"][0]["rate_plan_room"]["room"]["translations"][0]["value"] ?? $service["service_rooms"][0]["rate_plan_room"]["room"]["room_type"]["type_room"]["translations"][0]["value"] ?? '' }} -
                                    {{ $service["service_rooms"][0]["rate_plan_room"]["rate_plan"]["meal"]["translations"][0]["value"] }}

                                @else                                     
                                    {{ $service["service_rooms_hyperguest"][0]["room"]["translations"][0]["value"] ?? $service["service_rooms_hyperguest"][0]["room"]["room_type"]["type_room"]["translations"][0]["value"] ?? '' }}
                                    {{ $service["service_rooms_hyperguest"][0]["rate_plan"]["meal"]["translations"][0]["value"] }}
                            
                                @endif
                            </td>

                            @isset($data["accommodations"])
                                @php
                                     
                                    if(!$service["hyperguest_pull"])
                                    {
                                        $isPriceDynamic = $service["service_rooms"][0]["rate_plan_room"]["rate_plan"]["price_dynamic"] == 1;
                                    }else{
                                        $isPriceDynamic = 0;
                                    }


                                    $estiloCelda = $imprimirFila
                                        ? 'height: 20px; border-top: 3px solid red; font-weight: bold;' . ($isPriceDynamic ? ' color: red;' : '')
                                        : 'font-weight: bold;' . ($isPriceDynamic ? ' color: red;' : '');
                                @endphp

                                @if(isset($data["accommodations"]["single"]) && $data["accommodations"]["single"] > 0)
                                    @php
                                        $totalSingle += calculatePriceWithCommission($amount["single"], $data);
                                    @endphp
                                    <td style="{{ $estiloCelda }}">
                                       {{ calculatePriceWithCommission(isset($amount["single"]) ? $amount["single"] : 0, $data) }}
                                    </td>
                                @endif
                                @if(isset($data["accommodations"]["double"]) && $data["accommodations"]["double"] > 0)
                                    @php
                                        $totalDouble += calculatePriceWithCommission($amount["double"], $data);
                                    @endphp
                                    <td style="{{ $estiloCelda }}">
                                         {{ calculatePriceWithCommission(isset($amount["double"]) ? $amount["double"] : 0, $data) }}
                                    </td>
                                @endif
                                @if(isset($data["accommodations"]["triple"]) && $data["accommodations"]["triple"] > 0)
                                    @php
                                        $totalTriple += calculatePriceWithCommission($amount["triple"], $data);
                                    @endphp
                                    <td style="{{ $estiloCelda }}">
                                         {{ calculatePriceWithCommission(isset($amount["triple"]) ? $amount["triple"] : 0, $data) }}
                                    </td>
                                @endif
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

            @isset($data["accommodations"])
                @if(isset($data["accommodations"]["single"]) and $data["accommodations"]["single"]>0)
                    <td style="font-weight: bold; text-align: center;">{{ calculatePriceWithCommission($category["services_total"]["single"], $data) }}</td>
                @endif
                @if(isset($data["accommodations"]["double"]) and $data["accommodations"]["double"]>0)
                    <td style="font-weight: bold; text-align: center;">{{ calculatePriceWithCommission($category["services_total"]["double"], $data) }}</td>
                @endif
                @if(isset($data["accommodations"]["triple"]) and $data["accommodations"]["triple"]>0)
                    <td style="font-weight: bold; text-align: center;">{{ calculatePriceWithCommission($category["services_total"]["triple"], $data) }}</td>
                @endif
            @endisset

{{--
            @isset($data["accommodations"])
            @if(isset($data["accommodations"]["single"]) and $data["accommodations"]["single"]>0)
                <td style="font-weight: bold; text-align: center;">{{ roundLito($totalSingle) }}</td>
            @endif
            @if(isset($data["accommodations"]["double"]) and $data["accommodations"]["double"]>0)
                <td style="font-weight: bold; text-align: center;">{{ roundLito($totalDouble) }}</td>
            @endif
            @if(isset($data["accommodations"]["triple"]) and $data["accommodations"]["triple"]>0)
                <td style="font-weight: bold; text-align: center;">{{ roundLito($totalTriple) }}</td>
            @endif
            @endisset
--}}


        </tr>
        </tbody>
    </table>
@endforeach

@if(count($data["categories_optional"][0]["services"])>0)

@php
    $columna = 2;

    if(isset($data["accommodations"])){
        if(isset($data["accommodations"]["single"]) and $data["accommodations"]["single"]>0){
            $columna = $columna + 1;
        }
        if(isset($data["accommodations"]["double"]) and $data["accommodations"]["double"]>0){
            $columna = $columna + 1;
        }
        if(isset($data["accommodations"]["triple"]) and $data["accommodations"]["triple"]>0){
            $columna = $columna + 1;
        }

    }
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
                @isset($data["accommodations"])
                @if(isset($data["accommodations"]["single"]) and $data["accommodations"]["single"]>0)
                    <td style="background-color: #8d0a0d;color: #ffffff;">Single</td>
                @endif
                @if(isset($data["accommodations"]["double"]) and $data["accommodations"]["double"]>0)
                    <td style="background-color: #8d0a0d;color: #ffffff;">Double</td>
                @endif
                @if(isset($data["accommodations"]["triple"]) and $data["accommodations"]["triple"]>0)
                    <td style="background-color: #8d0a0d;color: #ffffff;">Triple</td>
                @endif
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
                    @isset($data["accommodations"])
                    @if(isset($data["accommodations"]["single"]) and $data["accommodations"]["single"]>0)
                        <td>{{ $service["single_import"] }}</td>
                    @endif
                    @if(isset($data["accommodations"]["double"]) and $data["accommodations"]["double"]>0)
                        <td>{{ $service["double_import"] }}</td>
                    @endif
                    @if(isset($data["accommodations"]["triple"]) and $data["accommodations"]["triple"]>0)
                        <td>{{ $service["triple_import"] }}</td>
                    @endif
                    @endisset

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
                            <td style="font-weight: bold;">
                                {{ $service["hotel"]["channel"][0]["code"] ?? '' }}
                                @if (!empty($service["hotel"]["channel"][0]["code"])) - @endif
                                {{ $service["hotel"]["name"] ?? '' }}
                                @if (!empty($service["hotel"]["name"])) - @endif
                                {{ $service["service_rooms"][0]["rate_plan_room"]["room"]["translations"][0]["value"] ?? $service["service_rooms"][0]["rate_plan_room"]["room"]["room_type"]["type_room"]["translations"][0]["value"] ?? '' }}
                                @if (!empty($service["service_rooms"][0]["rate_plan_room"]["room"]["translations"][0]["value"]) || !empty($service["service_rooms"][0]["rate_plan_room"]["room"]["room_type"]["type_room"]["translations"][0]["value"])) - @endif
                                {{ $service["service_rooms"][0]["rate_plan_room"]["rate_plan"]["meal"]["translations"][0]["value"] ?? '' }}
                            </td>

                            @isset($data["accommodations"])
                            @if(isset($data["accommodations"]["single"]) and $data["accommodations"]["single"]>0)
                                <td style='font-weight: bold;'>{{ $amount["single"] }}</td>
                            @endif
                            @if(isset($data["accommodations"]["double"]) and $data["accommodations"]["double"]>0)
                                <td style='font-weight: bold;'>{{ $amount["double"] }}</td>
                            @endif
                            @if(isset($data["accommodations"]["triple"]) and $data["accommodations"]["triple"]>0)
                                <td style='font-weight: bold;'>{{ $amount["triple"] }}</td>
                            @endif
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
