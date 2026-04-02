<html>
<table>
    <thead>
    <tr>
        <th style="background-color: #bd0d12;color: #ffffff;">
            <b>ID HOTEL</b>
        </th>
        <th style="background-color: #bd0d12;color: #ffffff;">
            <b>NOMBRE DEL HOTEL</b>
        </th>
        <th style="background-color: #bd0d12;color: #ffffff;">
            <b>ID DE TARIFA</b>
        </th>
        <th style="background-color: #bd0d12;color: #ffffff;">
            <b>NOMBRE DE LA TARIFA</b>
        </th>
        <th style="background-color: #bd0d12;color: #ffffff;">
            <b>PROTECCIÓN</b>
        </th>
    </tr>
    </thead>
    <tbody>

    @foreach($hotels as $key => $hotel)
        <tr>
            <td style="background-color: #c9c5c5;color: #000000;">
                {{ $hotel['hotel_id'] }}
            </td>
            <td style="background-color: #c9c5c5;color: #000000;">
                {{ $hotel['name'] }}
            </td>
            <td style="background-color: #c9c5c5;color: #000000;"></td>
            <td style="background-color: #c9c5c5;color: #000000;"></td>
            <td style="background-color: #c9c5c5;color: #000000;"></td>
        </tr>
        @foreach($hotel['rates'] as $key_rate => $rate)
            <tr>
                <td></td>
                <td></td>
                <td>{{$rate['id']}}</td>
                <td>{{$rate['name']}}</td>
                @if($rate['protection'])
                    <td>CON PROTECCIÓN</td>
                @else
                    <td>SINCERADO</td>
                @endif
            </tr>
        @endforeach
    @endforeach

    </tbody>
</table>
</html>
