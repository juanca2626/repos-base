<html>
    <table>
        <tr>
            <th>ID</th>
            <th>CODE</th>
            <th>HOTEL</th>
            <th>PLAN TARIFARIO</th>
            <th>ROOM</th>
        </tr>
        @foreach( $rates_plans_rooms as $rates_plans_room)

                <tr style="background: #bcbcbc;">
                    <td>
                        {{ $rates_plans_room['rate_plan']['hotel']['id'] }}
                    </td>
                    <td>
                        {{ $rates_plans_room['rate_plan']['hotel']['channel'][0]['code'] }}
                    </td>
                    <td>
                        {{ $rates_plans_room['rate_plan']['hotel']['name'] }}
                    </td>
                    <td>
                        {{ $rates_plans_room['rate_plan']['name'] }}
                    </td>
                    <td>
                        {{ $rates_plans_room['room']['room_type']['occupation'] }}
                    </td>
                </tr>

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
