<html>
    <table>
        <tr>
            <th>ID</th>
            <th>CODE</th>
            <th>NAME</th>
            <th>TRADENAME</th>
        </tr>
        @foreach( $packages as $package)
            @if( count($package['plan_rates']) > 0 )
                <tr style="background: #bcbcbc;">
                    <td>
                        {{ $package['id'] }}
                    </td>
                    <td>
                        @if( $package['code'] != '' &&  $package['code'] != null )
                            {{  $package['code']  }}
                        @else
                            @if( $package['extension'] == 1 )
                                E{{  $package['id']  }}
                            @else
                                P{{  $package['id']  }}
                            @endif
                        @endif
                    </td>
                    @if( count($package['translations']) > 0 )
                        <td>
                            {{ $package['translations'][0]['name'] }}
                        </td>
                        <td>
                            {{  $package['translations'][0]['tradename'] }}
                        </td>
                    @else
                        <td>-</td>
                        <td>-</td>
                    @endif
                </tr>

                @foreach( $package['plan_rates'] as $plan_rate)
                    @if( count($plan_rate['plan_rate_categories']) > 0 )
                        <tr style="background: #c7d5d5;">
                            <td colspan="2">
                                <b>Plan Tarifario (PC):</b>
                            </td>
                            <td>
                                {{ $plan_rate['name'] }}
                            </td>
                            <td>
                                {{ $plan_rate['id'] }}
                            </td>
                        </tr>

                        @foreach( $plan_rate['plan_rate_categories'] as $plan_rate_category)
                            @if( count($plan_rate_category['services']) > 0 )
                                <tr style="background: #ffe8e8;">
                                    <td colspan="2">
                                        <b>Categoría:</b>
                                    </td>
                                    @if( count($plan_rate_category['category']['translations']) > 0 )
                                        <td>
                                            {{ $plan_rate_category['category']['translations'][0]['value'] }}
                                        </td>
                                    @else
                                        <td>-</td>
                                    @endif
                                    <td>
                                        {{ $plan_rate_category['id'] }}
                                    </td>
                                </tr>

                                <tr>
                                    <th style="background: #66f0aa;">ID</th>
                                    <th style="background: #66f0aa;">CODE</th>
                                    <th style="background: #66f0aa;">SERVICE</th>
                                    <th style="background: #66f0aa;">PAXS</th>
                                </tr>

                                @foreach( $plan_rate_category['services'] as $service)
                                    <tr>
                                        <td>
                                            {{$service['service']['id']}}
                                        </td>
                                        <td>
                                            {{$service['service']['aurora_code']}} - {{$service['service']['equivalence_aurora']}}
                                        </td>
                                        <td>
                                            {{$service['service']['name']}}
                                        </td>
                                        <td>
                                            {{$service['service']['pax_min']}} - {{$service['service']['pax_max']}}
                                        </td>
                                    </tr>
                                @endforeach

                            @endif
                        @endforeach

                    @endif
                @endforeach

            @endif
        @endforeach
    </table>
    <style>
        th, td {
            padding: 15px;
            text-align: left;
        }th {
             background-color: #04AA6D;
             color: white;
         }
    </style>
</html>
