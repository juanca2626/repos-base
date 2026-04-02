<?php $langs = ['ESPAÑOL', 'INGLES', 'PORTUGUES']; ?>
<table>
    <thead>
        <tr>
            <td>
                <b>ID</b>
            </td>
            <td>
                <b>SERVICIO</b>
            </td>
            <td>
                <b>DESTINO</b>
            </td>
            <td>
                <b>ÚLTIMA MOD.</b>
            </td>
            <td>
                <b>TOTAL VENTAS</b>
            </td>
            @foreach ($langs as $key => $value)
                <td>
                    <b>NOTAS - {{ $value }}</b>
                </td>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($data['services'] as $key => $value)
            <tr>
                <td>
                    {{ $value['id'] }}
                </td>
                <td>
                    {{ $value['name'] }}
                </td>
                <td>
                    {{ isset($value['service_origin']) && count($value['service_origin']) > 0
                        ? (isset($value['service_origin'][0]['city']) &&
                        isset($value['service_origin'][0]['city']['translations']) &&
                        count($value['service_origin'][0]['city']['translations']) > 0
                            ? $value['service_origin'][0]['city']['translations'][0]['value']
                            : '')
                        : '' }}
                    >
                    {{ isset($value['service_destination']) && count($value['service_destination']) > 0
                        ? (isset($value['service_destination'][0]['city']) &&
                        isset($value['service_destination'][0]['city']['translations']) &&
                        count($value['service_destination'][0]['city']['translations']) > 0
                            ? $value['service_destination'][0]['city']['translations'][0]['value']
                            : '')
                        : '' }}
                </td>
                <td>
                    {{ $value['updated_at'] }}
                </td>
                <td>
                    {{ $value['total_sales'] }}
                </td>
                @foreach ($langs as $k => $v)
                    <td>
                        {{ isset($value['service_translations'][$k]['summary_commercial']) ? $value['service_translations'][$k]['summary_commercial'] : '' }}
                    </td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
