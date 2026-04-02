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
            <b>ID DE TARIFA.</b>
        </th>
        <th style="background-color: #bd0d12;color: #ffffff;">
            <b>NOMBRE DE TARIFA.</b>
        </th>
        <th style="background-color: #bd0d12;color: #ffffff;">
            <b>NOMBRE TARIFA COMERCIAL - (ES)</b>
        </th>
        <th style="background-color: #bd0d12;color: #ffffff;">
            <b>NOMBRE TARIFA COMERCIAL - (EN)</b>
        </th>
        <th style="background-color: #bd0d12;color: #ffffff;">
            <b>NOMBRE TARIFA COMERCIAL - (PT)</b>
        </th>
        <th style="background-color: #bd0d12;color: #ffffff;">
            <b>ESTADO DE TARIFA</b>
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
            <td style="background-color: #bd0d12;color: #ffffff;"></td>
            <td style="background-color: #bd0d12;color: #ffffff;"></td>
            <td style="background-color: #bd0d12;color: #ffffff;"></td>
        </tr>
        @foreach($hotel['rates_plans'] as $key => $rates_plans)
            <tr>
                <td></td>
                <td></td>
                <td>{{$rates_plans['id']}}</td>
                <td>{{$rates_plans['name']}}</td>
                @foreach($rates_plans['translations'] as $key => $translation)
                    @if($translation['language_id'] === 1)
                        <td>{{$translation['value']}}</td>
                    @endif
                    @if($translation['language_id'] === 2)
                        <td>{{$translation['value']}}</td>
                    @endif
                    @if($translation['language_id'] === 3)
                        <td>{{$translation['value']}}</td>
                    @endif
                @endforeach
                @if($rates_plans['status'] === 1)
                    <td>Activo</td>
                @else
                    <td> Inactivo</td>
                @endif
            </tr>
        @endforeach
    @endforeach

    </tbody>
</table>
</html>
