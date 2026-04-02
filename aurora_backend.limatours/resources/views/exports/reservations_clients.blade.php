<html>
<table>
    <thead>
    <tr>
        <th>
            <b>Nombre</b>
        </th>
        <th>
            <b>Codigo</b>
        </th>
        <th>
            <b>Mercado</b>
        </th>
        <th>
            <b>País</b>
        </th>
        <th>
            <b>RUC</b>
        </th>
        <th>
            <b>Editar</b>
        </th>
    </tr>
    </thead>
    <tbody>
    @foreach($clients as $key => $value)
        <tr>
            <td>
                {{ $value['id'] }} - {{ $value['name'] }}
            </td>
            <td>
                {{ $value['code'] }}
            </td>
            <td>
                {{ $value['markets']['name'] }}
            </td>
            <td>
                @if($value['countries'])
                    {{ $value['countries']['translations'][0]['value'] }}
                @else
                    -
                @endif
            </td>
            @if($value['validate_ruc'])
                <td>
                    {{ $value['ruc'] }}
                </td>
            @else
                <td style="background-color: red">
                    {{ $value['ruc'] }}
                </td>
            @endif
            <td>
                {{secure_url('/#/clients/edit') .'/'. $value['id']}}
            </td>
        </tr>
    @endforeach

    </tbody>
</table>
</html>


{{--<html>--}}
{{--<table>--}}
{{--    <thead>--}}
{{--    <tr>--}}
{{--        <th>--}}
{{--            <b>File</b>--}}
{{--        </th>--}}
{{--        <th>--}}
{{--            <b>Cliente</b>--}}
{{--        </th>--}}
{{--        <th>--}}
{{--            <b>Especialista</b>--}}
{{--        </th>--}}
{{--        <th>--}}
{{--            <b>RUC</b>--}}
{{--        </th>--}}
{{--        <th>--}}
{{--            <b>Fecha de creación</b>--}}
{{--        </th>--}}
{{--        <th>--}}
{{--            <b>Editar Cliente (URL)</b>--}}
{{--        </th>--}}
{{--    </tr>--}}
{{--    </thead>--}}
{{--    <tbody>--}}
{{--    @foreach($clients as $key => $value)--}}
{{--        <tr>--}}
{{--            <td>--}}
{{--                {{ $value['file_code'] }}--}}
{{--            </td>--}}
{{--            <td>--}}
{{--                {{ $value['client']['code'] }} - {{ $value['client']['name'] }}--}}
{{--            </td>--}}
{{--            <td>--}}
{{--                {{ $value['executive']['name'] }}--}}
{{--            </td>--}}
{{--            @if($value['validate_ruc'])--}}
{{--                <td>--}}
{{--                    {{ $value['client']['ruc'] }}--}}
{{--                </td>--}}
{{--            @else--}}
{{--                <td style="background-color: red">--}}
{{--                    {{ $value['client']['ruc'] }}--}}
{{--                </td>--}}
{{--            @endif--}}
{{--            <td>--}}
{{--                {{ $value['created_at'] }}--}}
{{--            </td>--}}
{{--            <td>--}}
{{--                {{secure_url('/#/clients/edit') .'/'. $value['client']['id']}}--}}
{{--            </td>--}}
{{--        </tr>--}}
{{--    @endforeach--}}

{{--    </tbody>--}}
{{--</table>--}}
{{--</html>--}}
