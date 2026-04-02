<html>
<table>
    <thead>
    <tr>
        <th style="background-color: #bd0d12;color: #ffffff;">
            <b>ID SERVICIO</b>
        </th>
        <th style="background-color: #bd0d12;color: #ffffff;">
            <b>NOMBRE DEL SERVICIO</b>
        </th>
        <th style="background-color: #bd0d12;color: #ffffff;">
            <b>CODIGO</b>
        </th>
{{--        <th style="background-color: #bd0d12;color: #ffffff;">--}}
{{--            <b>ID DE TARIFA</b>--}}
{{--        </th>--}}
{{--        <th style="background-color: #bd0d12;color: #ffffff;">--}}
{{--            <b>NOMBRE DE LA TARIFA</b>--}}
{{--        </th>--}}
        <th style="background-color: #bd0d12;color: #ffffff;">
            <b>PROTECCIÓN</b>
        </th>
    </tr>
    </thead>
    <tbody>

    @foreach($services as $key => $service)
        <tr>
            <td>
                {{ $service['service_id'] }}
            </td>
            <td>
                {{ $service['name'] }}
            </td>
            <td>
                {{ $service['aurora_code'] }} - {{ $service['equivalence_aurora'] }}
            </td>
            @if($service['protection'])
                <td>SINCERADO</td>
            @else
                <td>CON PROTECCIÓN</td>
            @endif
{{--            <td style="background-color: #c9c5c5;color: #000000;"></td>--}}
{{--            <td style="background-color: #c9c5c5;color: #000000;"></td>--}}
{{--            <td style="background-color: #c9c5c5;color: #000000;"></td>--}}
        </tr>
{{--        @foreach($service['rates'] as $key_rate => $rate)--}}
{{--            <tr>--}}
{{--                <td></td>--}}
{{--                <td></td>--}}
{{--                <td></td>--}}
{{--                <td>{{$rate['id']}}</td>--}}
{{--                <td>{{$rate['name']}}</td>--}}
{{--                @if($rate['protection'])--}}
{{--                    <td>SINCERADO</td>--}}
{{--                @else--}}
{{--                    <td>CON PROTECCIÓN</td>--}}
{{--                @endif--}}
{{--            </tr>--}}
{{--        @endforeach--}}
    @endforeach

    </tbody>
</table>
</html>
