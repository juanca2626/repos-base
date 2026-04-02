<html>
<table>
    <thead>
    <tr>
        <th style="background-color: #bd0d12;color: #ffffff;">
            <b>ID HOTEL</b>
        </th>
        <th style="background-color: #bd0d12;color: #ffffff;">
            <b>NOMBRE DE HOTEL</b>
        </th>
        <th style="background-color: #bd0d12;color: #ffffff;">
            <b>ID DE HAB.</b>
        </th>
        <th style="background-color: #bd0d12;color: #ffffff;">
            <b>NOMBRE HABITACIÓN</b>
        </th>
        <th style="background-color: #bd0d12;color: #ffffff;">
            <b>LINK AURORA</b>
        </th>
    </tr>
    </thead>
    <tbody>

    @foreach($hotels as $key => $hotel)
        <tr>
            <td style="background-color: #bd0d12;color: #ffffff;">
                {{ $hotel['id'] }}
            </td>
            <td style="background-color: #bd0d12;color: #ffffff;">
                {{ $hotel['name'] }}
            </td>
            <td style="background-color: #bd0d12;color: #ffffff;"></td>
            <td style="background-color: #bd0d12;color: #ffffff;"></td>
            <td style="background-color: #bd0d12;color: #ffffff;"></td>
        </tr>
        @foreach($hotel['rooms'] as $key => $room)
            <tr>
                <td></td>
                <td></td>
                <td>{{$room['id']}}</td>
                <td>{{$room['name']}}</td>
                <td>
                    {{ secure_url('/#/hotels/' . $hotel['id'] . '/manage_hotel/rooms/edit/'.$room['id']) }}
                </td>
            </tr>
        @endforeach
    @endforeach

    </tbody>
</table>
</html>
