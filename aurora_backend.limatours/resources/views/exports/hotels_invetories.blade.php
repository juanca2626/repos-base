<html>
<table>
    <thead>

    <tr>
        <th rowspan="2" align="center" style="background-color: black;color: white; font-size: 18px;vertical-align: middle;" >TARIFA</th>
        @php
            $color =  "#555d63";
        @endphp

        @foreach ($hotel->inventories['columns'] as $mes => $days)
        @php
            $color = $color == "black" ? "#555d63" : "black" ;
        @endphp

        <th colspan="{{ count($days)}}" align="center" style="background-color: {{ $color }};color: white; font-size: 18px">
            {{ strtoupper($mes)  }}
        </th>
        @endforeach
    </tr>

    <tr>
        @php
            $color =  "#555d63";
        @endphp
        @foreach ($hotel->inventories['columns'] as $mes => $days)
            @php
                $color = $color == "black" ? "#555d63" : "black" ;
            @endphp
            @foreach ($days as $day)
                <th align="center" style="background-color: {{ $color }};color: white;">
                    {{ $day['day_format']  }}
                    {{ $day['day']  }} <br>
                </th>
            @endforeach
        @endforeach
    </tr>

    @foreach ($hotel->inventories['inventories'] as $inventory_index => $item)
    <tr>
        <td>{{ $item['rate_name'] }}</td>
        @foreach ($item['inventory'] as $day_index => $day)

            @if($hotel->inventories['inventories'][$inventory_index]['inventory'][$day_index]['class_locked']  )
                <th align="center" style="background-color: #cb2027; color: white">
            @else
                <th align="center">
            @endif


                @if($hotel->inventories['inventories'][$inventory_index]['inventory'][$day_index]['inventory_num'] >=0   )
                    {{$hotel->inventories['inventories'][$inventory_index]['inventory'][$day_index]['inventory_num']}}
                @else
                    0
                @endif

            </th>
        @endforeach
    </tr>
    @endforeach

</table>
</html>

