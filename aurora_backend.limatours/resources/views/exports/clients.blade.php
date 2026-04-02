<html>
<table>
    <thead>
    <tr>
        <th>
            <b>ID</b>
        </th>
        <th>
            <b>Codigo</b>
        </th>
        <th>
            <b>Nombre</b>
        </th>
        <th>
            <b>Mercado</b>
        </th>
        <th>
            <b>KAM</b>
        </th>
    </tr>
    </thead>
    <tbody>

    @foreach($clients as $key => $value)
        <tr>
            <td>
                {{ $value['id'] }}
            </td>
            <td>
                {{ $value['code'] }}
            </td>
            <td>
                {{ $value['name'] }}
            </td>
            <td>
                {{ $value['markets']['name'] }}
            </td>
            <td>
                {{ $value['executive_code'] }}
            </td>
{{--            <td>--}}
{{--                @if($value['countries'])--}}
{{--                    {{ $value['countries']['translations'][0]['value'] }}--}}
{{--                @else--}}
{{--                    ---}}
{{--                @endif--}}
{{--            </td>--}}
{{--            @if($value['validate_ruc'])--}}
{{--                <td>--}}
{{--                    {{ $value['ruc'] }}--}}
{{--                </td>--}}
{{--            @else--}}
{{--                <td style="background-color: red">--}}
{{--                    {{ $value['ruc'] }}--}}
{{--                </td>--}}
{{--            @endif--}}
{{--            <td>--}}
{{--                {{secure_url('/#/clients/edit') .'/'. $value['id']}}--}}
{{--            </td>--}}
        </tr>
    @endforeach

    </tbody>
</table>
</html>
