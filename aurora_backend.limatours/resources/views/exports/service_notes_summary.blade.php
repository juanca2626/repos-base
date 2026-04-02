<?php $langs = ['ESPAÑOL', 'INGLES', 'PORTUGUES']; ?>
<table>
    <thead>
        <tr>
            <td>
                <b>ID</b>
            </td>
            <td>
                <b>CODIGO</b>
            </td>
            <td>
                <b>SERVICIO</b>
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
                    {{ $value['aurora_code'] }}
                </td>
                <td>
                    {{ $value['name'] }}
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
