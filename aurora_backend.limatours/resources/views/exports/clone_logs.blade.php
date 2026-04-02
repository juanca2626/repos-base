<html>
<table>
    <thead>
    <tr>
        <td>
            <b>Código AURORA</b>
        </td>
        <td>
            <b>{{ ($type == 'hotel') ? 'Hotel' : 'Service' }}</b>
        </td>
        <td>
            <b>Enlace a tarifa protegida</b>
        </td>
        <td>
            <b>Fecha</b>
        </td>
    </tr>
    @foreach($data as $key => $value)
        <tr>
            <td>
                @if($type == 'hotel')
                    <b>{{ $value['hotel']['channels'][0]['pivot']['code'] }}</b>
                @else
                    <b>{{ $value['service']['aurora_code'] }}</b>
                @endif
            </td>
            <td>
                {{ ($type == 'hotel') ? $value['hotel']['name'] : $value['service']['name'] }}
            </td>
            @if($type == 'service')
                <td>
                    {{ url('/#/services_new/' . $value['item_id'] . '/manage_service/rates/cost/edit/' . $value['item_rate_id']) }}
                </td>
            @else
                <td>
                    {{ url('/#/hotels/' . $value['item_id'] . '/manage_hotel/rates/rates/cost/edit/' . $value['item_rate_plan_id']) }}
                </td>
            @endif
            <td>
                {{ date("d/m/Y H:i:s", strtotime($value['updated_at'])) }}
            </td>
        </tr>
    @endforeach
</table>
</html>
