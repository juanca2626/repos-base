<html>
<table>
    <tr style="float: left">
        <img src="{{ resource_path('images/logo.png') }}" alt="" style="float: left;">
    </tr>
    <tr></tr>
    <tr></tr>
    <tr>
        <td>
            <b>{{ $city["date_download"] }}</b>
        </td>
        <td></td>
        <td></td>
        <td style="font-size: 15px; text-align: center">
            <b>
                {{trans('export_excel.service_breakdown',[],$lang)}} {{ $year }}
            </b>
        </td>
    </tr>
    <tr>
        <td>
            <b>{{ $city["client_code"] }}</b>
        </td>
        <td>
            <b>{{ $city["client_name"] }}</b>
        </td>
        <td></td>
        <td style="font-size: 15px; text-align: center;">
            <b>
                {{trans('export_excel.net_rates',[],$lang)}}
            </b>
        </td>
    </tr>
    <tr></tr>
    <tr></tr>
</table>
<table>
    <thead>
    <tr>
        <td style="background-color: #000000;color: #ffffff;"></td>
        <td style="background-color: #000000;color: #ffffff;"></td>
        <td style="background-color: #000000;color: #ffffff;"></td>
        <td style="background-color: #000000;color: #ffffff;">{{ $city["city_name"] }}</td>
        <td style="background-color: #000000;color: #ffffff;"></td>
        @for ($i = 0; $i < 40; $i++)
            <td style="background-color: #000000;color: #ffffff;"></td>
        @endfor
    </tr>
    <tr>
        <td style="background-color: #A71B20;color: #ffffff;">

        </td>
        <td style="background-color: #A71B20;color: #ffffff;">
            {{trans('export_excel.codes',[],$lang)}}
        </td>
        <td style="background-color: #A71B20;color: #ffffff;">

        </td>
        <td style="background-color: #A71B20;color: #ffffff;">
            <b>{{trans('export_excel.service',[],$lang)}}</b>
        </td>
        <td style="background-color: #A71B20;color: #ffffff;">
            <b>{{trans('export_excel.dates',[],$lang)}}</b>
        </td>
        <td style="background-color: #A71B20;color: #ffffff;">
            <b>{{trans('export_excel.code_sim',[],$lang)}}</b>
        </td>
        @foreach( $city["ranges"] as $range)
            <td style="background-color: #A71B20;color: #ffffff;">{{ $range["pax_from"] }}</td>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @foreach( $city["categories"] as $category)
        @foreach( $category["subcategories"] as $subcategory)
            <tr>
                <td style="background-color: #A71B20;color: #ffffff;">
                    <b>{{trans('export_excel.code_sim',[],$lang)}}</b>
                </td>
                <td style="background-color: #A71B20;color: #ffffff;">
                    <b>{{trans('export_excel.code_pc',[],$lang)}}</b>
                </td>
                <td style="background-color: #A71B20;color: #ffffff;">
                    <b>{{trans('export_excel.code_na',[],$lang)}}</b>
                </td>
                <td style="background-color: #A71B20;color: #ffffff;">
                    <b>{{ $subcategory["subcategory_name"] }}</b>
                </td>
            </tr>
            @foreach( $subcategory["services"] as $service)
                @foreach( $service["rates"] as $rate)
                    @foreach( $rate["rate_plans"] as $index_rate_plan=>$rate_plan)
                        <tr>
                            <td>{{ $service["aurora_code_sim"] }}</td>
                            <td>{{ $service["aurora_code_pc"] }}</td>
                            <td>{{ $service["aurora_code_na"] }}</td>
                            @if( $index_rate_plan === 0)
                                <td>{{ $service["service_name"] }}<br>
                                    {{ trans('export_excel.include',[],$lang) }}:
                                    @if(count($service["inclusions"])>1)
                                        @foreach( $service["inclusions"] as $inclusion)
                                            {{ trans('export_excel.day',[],$lang) }} {{ $inclusion["day"] }}
                                            : {{ $inclusion["inclusion_name"] }}
                                        @endforeach
                                    @else
                                        @foreach( $service["inclusions"] as $inclusion)
                                            {{ $inclusion["inclusion_name"] }}
                                        @endforeach
                                    @endif
                                </td>
                            @else
                                <td></td>
                            @endif
                            <td>{{ $rate_plan["date_from_label"] }} - {{$rate_plan["date_to_label"]}}</td>
                            @if( $service["service_type_id"] == 1 && $service["equivalence"])
                                @php
                                    $style = ($rate_plan["ranges"][1]["flag_migrate"] == 1) ? "" : "color:#4bc910;font-weight: bold;";
                                @endphp
                                <td style="{{ $style }}">{{ $rate_plan["ranges"][1]["price_adult"] }}</td>

                                @foreach( $rate_plan["ranges_equivalence"] as $range)
                                    {{-- @foreach( $rate_plan["ranges"] as $range) --}}
                                    {{--Todo si el servicio no es un semi privado muestran los precios--}}
                                    @if($service["aurora_code_semi"] == "")
                                        @php
                                            $style = ($range["flag_migrate"] == 1) ? "" : "color:#4bc910;font-weight: bold;";
                                        @endphp
                                        <td style="{{ $style }}">{{ $range["price_adult"] }}</td>
                                    @else
                                        <td></td>
                                    @endif
                                    {{-- @endforeach --}}
                                @endforeach

                            @endif
                            @if( $service["service_type_id"] == 1 && !$service["equivalence"])
                                @php
                                    $style = ($rate_plan["ranges"][1]["flag_migrate"] == 1) ? "" : "color:#4bc910;font-weight: bold;";
                                @endphp
                                <td style="{{ $style }}">{{ $rate_plan["ranges"][1]["price_adult"] }}</td>
                                @foreach( $rate_plan["ranges_equivalence"] as $range)
                                    <td></td>
                                @endforeach
                            @endif
                            @if( ($service["service_type_id"] == 2 ||  $service["service_type_id"] == 3) && $service["equivalence"])
                                <td>{{  $rate_plan["ranges_equivalence"][0]["price_adult"] }}</td>
                                @foreach( $rate_plan["ranges"] as $range)
                                    {{--Todo si el servicio no es un semi privado muestran los precios--}}
                                    @if($service["aurora_code_semi"] == "")
                                        @php
                                            $style = ($range["flag_migrate"] == 1) ? "" : "color:#4bc910;font-weight: bold;";
                                        @endphp
                                        <td style="{{ $style }}">{{ $range["price_adult"] }}</td>
                                    @else
                                        <td></td>
                                    @endif
                                @endforeach
                            @endif
                            @if( ($service["service_type_id"] == 2 ||  $service["service_type_id"]==3) && !$service["equivalence"])
                                <td></td>
                                @foreach( $rate_plan["ranges"] as $range)
                                    @php
                                        $style = ($range["flag_migrate"] == 1) ? "" : "color:#4bc910;font-weight: bold;";
                                    @endphp
                                    <td style="{{ $style }}">{{ $range["price_adult"] }}</td>
                                @endforeach
                            @endif
                        </tr>
                    @endforeach
                    {{--                    <tr></tr>--}}
                @endforeach
            @endforeach
        @endforeach
    @endforeach
    </tbody>
</table>
</html>
