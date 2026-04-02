<html>
<table>
    <thead>
    <tr>
        <td style="background-color: #8d0a0d;color: #ffffff;border: 1px solid #8d0a0d;width: 10px!important;"></td>
        <td style="background-color: #8d0a0d;color: #ffffff;border: 1px solid #8d0a0d">
            <b>{{ $city["date_download"] }}</b>
        </td>
        <td style="background-color: #8d0a0d;color: #ffffff;border: 1px solid #8d0a0d">
            <b>{{ $city["client_code"] }} {{ $city["client_name"] }}</b>
        </td>
        <td style="background-color: #8d0a0d;color: #ffffff;border: 1px solid #8d0a0d"></td>
        <td style="background-color: #8d0a0d;color: #ffffff;border: 1px solid #8d0a0d"></td>
        <td style="background-color: #8d0a0d;color: #ffffff;border: 1px solid #8d0a0d"></td>
        <td style="background-color: #8d0a0d;color: #ffffff;border: 1px solid #8d0a0d"></td>
        <td style="background-color: #8d0a0d;color: #ffffff;border: 1px solid #8d0a0d"></td>
        <td style="background-color: #8d0a0d;color: #ffffff;border: 1px solid #8d0a0d"></td>
    </tr>
    <tr>
            <td style="background-color: #8d0a0d;color: #ffffff;border: 1px solid #8d0a0d;width: 10px!important;"></td>
            <td style="background-color: #8d0a0d;color: #ffffff;border: 1px solid #8d0a0d">
                <b>{{  $city["th"][0] }}</b>
            </td>
            <td style="background-color: #8d0a0d;color: #ffffff;border: 1px solid #8d0a0d">
                <b>{{  $city["th"][1] }}</b>
            </td>
            <td style="background-color: #8d0a0d;color: #ffffff;border: 1px solid #8d0a0d">
                <b>{{  $city["th"][2] }}</b>
            </td>
            <td style="background-color: #8d0a0d;color: #ffffff;border: 1px solid #8d0a0d">
                <b>{{  $city["th"][7] }}</b>
            </td>
            <td style="background-color: #8d0a0d;color: #ffffff;border: 1px solid #8d0a0d">
                <b>{{  $city["th"][3] }}</b>
            </td>
            <td style="background-color: #8d0a0d;color: #ffffff;border: 1px solid #8d0a0d">
                <b>{{  $city["th"][4] }}</b>
            </td>
            <td style="background-color: #8d0a0d;color: #ffffff;border: 1px solid #8d0a0d; text-align: center">
                <b>{{  $city["th"][5] }}</b>
            </td>
            @if($year != '2023')
                <td style="background-color: #8d0a0d;color: #ffffff;border: 1px solid #8d0a0d; text-align: center">
                    <b>{{  $city["th"][6] }}</b>
                </td>
            @endif
            <td style="background-color: #8d0a0d;color: #ffffff;border: 1px solid #8d0a0d"></td>
        {{--        <td style="background-color: #8d0a0d;color: #ffffff;">$/Nino</td>--}}
    </tr>
    </thead>
    <tbody>
    @foreach( $city["categories"] as $category)
        <tr style="border: 0px solid #ffffff">
            <td style="background-color: #8d0a0d;color: #ffffff;border: 1px solid #8d0a0d;width: 10px!important;"></td>
            <td style="background-color: #ffffff;border: 0px solid #ffffff"></td>
            <td style="background-color: {{ $category["color"] }};color: #ffffff;border: 1px solid {{ $category["color"] }}">
                <b>{{ $category["hotel_class_name"] }}</b></td>
            <td style="background-color: #ffffff;border: 0px solid #ffffff"></td>
            <td style="background-color: #ffffff;border: 0px solid #ffffff"></td>
            <td style="background-color: #ffffff;border: 0px solid #ffffff"></td>
            <td style="background-color: #ffffff;border: 0px solid #ffffff"></td>
            <td style="background-color: #ffffff;border: 0px solid #ffffff; text-align: center"></td>
            @if($year != '2023')
                <td style="background-color: #ffffff;border: 0px solid #ffffff; text-align: center"></td>
            @endif
            <td style="background-color: #8d0a0d;color: #ffffff;border: 1px solid #8d0a0d"></td>
        </tr>
        @foreach( $category["hotels"] as $hotel)
            <tr>
                <td style="background-color: #8d0a0d;color: #ffffff;border: 1px solid #8d0a0d;width: 10px!important;"></td>
                <td style="background-color: #D9D9D9;color: black;border: 1px solid #D9D9D9">
                    <b>{{ $hotel["aurora_code"] }}</b></td>
                <td style="background-color: #D9D9D9;color: black;border: 1px solid #D9D9D9">
                    <b>{{ $hotel["hotel_name"] }}</b></td>
                <td style="background-color: #ffffff;border: 0px solid #ffffff"></td>
                <td style="background-color: #ffffff;border: 0px solid #ffffff"></td>
                <td style="background-color: #ffffff;border: 0px solid #ffffff"></td>
                <td style="background-color: #ffffff;border: 0px solid #ffffff; text-align: center"></td>
                <td style="background-color: #ffffff;border: 0px solid #ffffff; text-align: center"></td>
                @if($year != '2023')
                    <td style="background-color: #ffffff;border: 0px solid #ffffff; text-align: center"></td>
                @endif
                <td style="background-color: #8d0a0d;color: #ffffff;border: 1px solid #8d0a0d"></td>
            </tr>
            @foreach( $hotel["hotel_rooms"] as $room)
                @foreach( $room["rates_plan_room"] as $rate_plan_room)
                    @foreach( $rate_plan_room["calendars"] as $index_calendar =>$calendar)
                        <tr>
                            <td style="background-color: #8d0a0d;color: #ffffff;border: 1px solid #8d0a0d;width: 10px!important;"></td>
                            @if($index_calendar == 0)
                                <td style="border-top: 1px solid #000000"></td>
                            @else
                                <td style="border: 0px solid #ffffff"></td>
                            @endif
                            @if($index_calendar == 0)
                                <td style="border: 0px solid #ffffff;border-top: 1px solid #000000">{{ $room["room_name"] }} </td>
                                <td style="border: 0px solid #ffffff;border-top: 1px solid #000000">{{ $rate_plan_room["rate_plan_id"] }} - {{ $rate_plan_room["rate_plan_name"] }}   </td>
                            @else
                                <td style="border: 0px solid #ffffff"></td>
                                <td style="border: 0px solid #ffffff"></td>
                            @endif
                            @php
                            $style = $calendar["sincerada"] == 1 ? "color:#4bc910;font-weight: bold;" : "";
                            @endphp
                            @if(count($rate_plan_room["calendars"]) > 1)                                                        
                                @if($index_calendar == 0)
                                   
                                    <td style="border: 0px solid #ffffff;border-top: 1px solid #000000; {{ $style }}">{{ $calendar["policy_name"] }}</td>
                                    <td style="border: 0px solid #ffffff;border-top: 1px solid #000000; {{ $style }}">{{ $calendar["date_from"] }}</td>
                                    <td style="border: 0px solid #ffffff;border-top: 1px solid #000000; {{ $style }}">{{ $calendar["date_to"] }}</td>
                                    <td style="border: 0px solid #ffffff;border-top: 1px solid #000000; {{ $style }} text-align: center">{{ $calendar["price_adult"] }}</td>
                                    @if($year != '2023')
                                        <td style="border: 0px solid #ffffff;border-top: 1px solid #000000; text-align: center; {{ $style }}">{{ $calendar["price_child"] }}</td>
                                    @endif
                                @else
                                    <td style="border: 0px solid #ffffff;border-top: 1px solid #000000; {{ $style }}">{{ $calendar["policy_name"] }}</td>
                                    <td style="border: 0px solid #ffffff; {{ $style }}">{{ $calendar["date_from"] }}</td>
                                    <td style="border: 0px solid #ffffff; {{ $style }}">{{ $calendar["date_to"] }}</td>
                                    <td style="border: 0px solid #ffffff; {{ $style }}; text-align: center">{{ $calendar["price_adult"] }}</td>
                                    @if($year != '2023')
                                        <td style="border: 0px solid #ffffff; text-align: center; {{ $style }}">{{ $calendar["price_child"] }}</td>
                                    @endif
                                @endif
                            @else
                                <td style="border: 0px solid #ffffff;border-top: 1px solid #; {{ $style }}">{{ $calendar["policy_name"] }}</td>
                                <td style="border-top: 1px solid #000000; {{ $style }}">{{ $calendar["date_from"] }}</td>
                                <td style="border-top: 1px solid #000000;{{ $style }} ">{{ $calendar["date_to"] }}</td>
                                <td style="border-top: 1px solid #000000; text-align: center; {{ $style }}">{{ $calendar["price_adult"] }}</td>
                                @if($year != '2023')
                                    <td style="border-top: 1px solid #000000; text-align: center;{{ $style }}">{{ $calendar["price_child"] }}</td>
                                @endif
                            @endif
                                <td style="background-color: #8d0a0d;color: #ffffff;border: 1px solid #8d0a0d;width: 10px!important;"></td>
                        </tr>
                    @endforeach
                @endforeach
            @endforeach
            <tr>
                <td style="background-color: #8d0a0d;color: #ffffff;border: 1px solid #8d0a0d;width: 10px!important;"></td>
                <td style="background-color: #ffffff;color: #ffffff;border: 0px solid #ffffff;border-top: 1px solid #000000"></td>
                <td style="background-color: #ffffff;color: #ffffff;border: 0px solid #ffffff;border-top: 1px solid #000000"></td>
                <td style="background-color: #ffffff;color: #ffffff;border: 0px solid #ffffff;border-top: 1px solid #000000"></td>
                <td style="background-color: #ffffff;color: #ffffff;border: 0px solid #ffffff;border-top: 1px solid #000000"></td>
                <td style="background-color: #ffffff;color: #ffffff;border: 0px solid #ffffff;border-top: 1px solid #000000"></td>
                <td style="background-color: #ffffff;color: #ffffff;border: 0px solid #ffffff;border-top: 1px solid #000000"></td>
                <td style="background-color: #ffffff;color: #ffffff;border: 0px solid #ffffff;border-top: 1px solid #000000; text-align: center"></td>
                <td style="background-color: #8d0a0d;color: #ffffff;border: 1px solid #8d0a0d; text-align: center"></td>
            </tr>
        @endforeach
    @endforeach
    </tbody>
    <tfoot>
    <tr>
        <td style="background-color: #8d0a0d;color: #ffffff;border: 1px solid #8d0a0d;width: 10px!important;"></td>
        <td style="background-color: #8d0a0d;color: #ffffff;border: 1px solid #8d0a0d">{{ $city["note_1"] }}</td>
        <td style="background-color: #8d0a0d;color: #ffffff;border: 1px solid #8d0a0d">{{ $city["note_2"] }}</td>
        <td style="background-color: #8d0a0d;color: #ffffff;border: 1px solid #8d0a0d">{{ $city["note_3"] }}</td>
        <td style="background-color: #8d0a0d;color: #ffffff;border: 1px solid #8d0a0d">{{ $city["note_4"] }}</td>
        <td style="background-color: #8d0a0d;color: #ffffff;border: 1px solid #8d0a0d"></td>
        <td style="background-color: #8d0a0d;color: #ffffff;border: 1px solid #8d0a0d"></td>
        <td style="background-color: #8d0a0d;color: #ffffff;border: 1px solid #8d0a0d"></td>
        <td style="background-color: #8d0a0d;color: #ffffff;border: 1px solid #8d0a0d"></td>
    </tr>
    </tfoot>
</table>
</html>
