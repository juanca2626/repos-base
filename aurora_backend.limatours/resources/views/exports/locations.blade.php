<html>
<table>
    <thead>
    <tr>
        <td>
            <b>{{ ($type == 'hotel' || $type == 'point') ? 'Hotel' : 'Service' }}</b>
        </td>
        @if($type == 'hotel')
            <td colspan="4" scope="row">
                <b>Ubigeo</b>
            </td>
            <td>
                <b>Zona / Punto de interés</b>
            </td>
        @endif
        @if($type == 'service')
            <td colspan="3" scope="row">
                <b>Ubigeo - Origen</b>
            </td>
            <td>
                <b>Zona - Origen</b>
            </td>
            <td colspan="3" scope="row">
                <b>Ubigeo - Destino</b>
            </td>
            <td>
                <b>Zona - Destino</b>
            </td>
        @endif
        <td>
            <b>Enlace</b>
        </td>
    </tr>
    @foreach($data as $key => $value)
        <tr>
            <td>
                <b>{{ $value['name'] }}</b>
            </td>
            @if($type == 'hotel')
                <td>
                    @if($value['country_id'] > 0)
                        <b>{{ $value['country']['translations'][0]['value'] }}</b>
                    @endif
                </td>
                <td>
                    @if($value['state_id'] > 0)
                        <b>{{ $value['state']['translations'][0]['value'] }}</b>
                    @endif
                </td>
                <td>
                    @if($value['city_id'] > 0)
                        <b>{{ $value['city']['translations'][0]['value'] }}</b>
                    @endif
                </td>
                <td>
                    @if($value['district_id'] > 0)
                        <b>{{ $value['district']['translations'][0]['value'] }}</b>
                    @endif
                </td>
                <td>
                    @if($value['zone_id'] > 0)
                        <b>{{ $value['zone']['translations'][0]['value'] }}</b>
                    @endif
                </td>
            @endif
            @if($type == 'service')

                @foreach($value['service_origin'] as $k => $v)
                    <td>
                        @if($v['country_id'] > 0)
                            <b>{{ $v['country']['translations'][0]['value'] }}</b>
                        @endif
                    </td>
                    <td>
                        @if($v['state_id'] > 0)
                            <b>{{ $v['state']['translations'][0]['value'] }}</b>
                        @endif
                    </td>
                    <td>
                        @if($v['city_id'] > 0)
                            <b>{{ $v['city']['translations'][0]['value'] }}</b>
                        @endif
                    </td>
                    <td>
                        @if($v['zone_id'] > 0)
                            <b>{{ $v['zone']['translations'][0]['value'] }}</b>
                        @endif
                    </td>
                @endforeach

                @foreach($value['service_destination'] as $k => $v)
                    <td>
                        @if($v['country_id'] > 0)
                            <b>{{ $v['country']['translations'][0]['value'] }}</b>
                        @endif
                    </td>
                    <td>
                        @if($v['state_id'] > 0)
                            <b>{{ $v['state']['translations'][0]['value'] }}</b>
                        @endif
                    </td>
                    <td>
                        @if($v['city_id'] > 0)
                            <b>{{ $v['city']['translations'][0]['value'] }}</b>
                        @endif
                    </td>
                    <td>
                        @if($v['zone_id'] > 0)
                            <b>{{ $v['zone']['translations'][0]['value'] }}</b>
                        @endif
                    </td>
                @endforeach
            @endif

            @if($type == 'service')
                <td>
                    <b>{{ url('/#/services_new/edit/' . $value['id']) }}</b>
                </td>
            @else
                <td>
                    <b>{{ url('/#/hotels/edit/' . $value['id']) }}</b>
                </td>
            @endif
        </tr>
    @endforeach
</table>
</html>
