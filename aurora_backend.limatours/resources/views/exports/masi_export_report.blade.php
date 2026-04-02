<html>

@if($data['type'] == 0)
<?php

$items = ['Nada satisfecho', 'Poco satisfecho', 'Neutral', 'Muy satisfecho', 'Totalmente satisfecho'];

$hoteles = []; $hoteles_colors = [];
$restaurantes = []; $restaurantes_colors = [];

foreach($data['q06'] as $key => $value)
{
    if(isset($hoteles[$value['LABEL']]))
    {
        $value['QUANTITY'] = ($value['QUANTITY'] + $hoteles[$value['LABEL']]);
    }

    $hoteles[$value['LABEL']] = $value['QUANTITY'];
    $hoteles_colors[$value['LABEL']] = $value['COLOR'];
}

foreach($data['q07'] as $key => $value)
{
    if(isset($hoteles[$value['LABEL']]))
    {
        $value['QUANTITY'] = ($value['QUANTITY'] + $hoteles[$value['LABEL']]);
    }

    $hoteles[$value['LABEL']] = $value['QUANTITY'];
    $hoteles_colors[$value['LABEL']] = $value['COLOR'];
}

foreach($data['q08'] as $key => $value)
{
    if(isset($hoteles[$value['LABEL']]))
    {
        $value['QUANTITY'] = ($value['QUANTITY'] + $hoteles[$value['LABEL']]);
    }

    $hoteles[$value['LABEL']] = $value['QUANTITY'];
    $hoteles_colors[$value['LABEL']] = $value['COLOR'];
}

foreach($data['q09'] as $key => $value)
{
    if(isset($restaurantes[$value['LABEL']]))
    {
        $value['QUANTITY'] = ($value['QUANTITY'] + $restaurantes[$value['lABEL']]);
    }

    $restaurantes[$value['LABEL']] = $value['QUANTITY'];
    $restaurantes_colors[$value['LABEL']] = $value['COLOR'];
}

foreach($data['q10'] as $key => $value)
{
    if(isset($restaurantes[$value['LABEL']]))
    {
        $value['QUANTITY'] = ($value['QUANTITY'] + $restaurantes[$value['LABEL']]);
    }

    $restaurantes[$value['LABEL']] = $value['QUANTITY'];
    $restaurantes_colors[$value['LABEL']] = $value['COLOR'];
}

?>
<table>
    <thead>
        <tr>
            <th style="font-weight: bold">RESPUESTAS META: 96%</th>
            <th style="font-weight: bold">HOTELES</th>
            <th style="font-weight: bold">RESTAURANTES</th>
            <th style="font-weight: bold">TOTAL</th>
        </tr>
    </thead>
    <tbody>
        <?php $excelentes = 0; $total = 0; ?>
        @foreach($items as $key => $value)
        <?php

        $value_hotel = (isset($hoteles[$value]) ? $hoteles[$value] : 0);
        $value_restaurante = (isset($restaurantes[$value]) ? $restaurantes[$value] : 0);

        if($value == 'Muy satisfecho' OR $value == 'Totalmente satisfecho')
        {
            $excelentes += $value_hotel + $value_restaurante;
        }

        $total += $value_hotel + $value_restaurante;

        ?>
        <tr>
            <td style="font-weight: bold">{{ $value }}</td>
            <td style="font-weight: bold">{{ $value_hotel }}</td>
            <td style="font-weight: bold">{{ $value_restaurante }}</td>
            <td style="font-weight: bold">{{ ($value_hotel + $value_restaurante) }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="3" scope="row">Total de respuestas excelentes + buenas:</td>
            <td>{{ $excelentes }}</td>
        </tr>
        <tr>
            <td colspan="3" scope="row">Total de respuestas:</td>
            <td>{{ $total }}</td>
        </tr>
        <tr>
            <td colspan="3" scope="row">Porcentaje de respuestas excelentes + buenas:</td>
            <td>{{ number_format($excelentes / $total * 100, 2) }}</td>
        </tr>
    </tbody>
</table>
@endif

@if($data['type'] == 1)
@foreach($data['q14'] as $key => $value)
<table>
    <thead>
        <tr>
            <th><b>HOTEL: {{ $value['RAZON'] }}</b></th>
        </tr>
    </thead>
</table>
<table>
    <thead>
        <tr>
            <th style="font-weight: bold">FILE</th>
            <th style="font-weight: bold">INDICADOR</th>
            <th style="font-weight: bold">PUNTAJE</th>
        </tr>
    </thead>
    <tbody>
        @foreach($value['ANSWERS'] as $k => $v)
            <tr>
                <td>{{ $v['NROREF'] }}</td>
                <td>{{ $v['DESPRE'] }}</td>
                <td>{{ $v['PESPRE'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@endforeach

@endif

@if($data['type'] == 2)
<table>
<thead>
        <tr>
            <th style="font-weight: bold">FILE</th>
            <th style="font-weight: bold">TIPO DE ENVIO</th>
            <th style="font-weight: bold">TIPO DE MENSAJE</th>
            <th style="font-weight: bold">ESTADO</th>
            <th style="font-weight: bold">NRO DE PASAJERO</th>
            <th style="font-weight: bold">MENSAJE</th>
            <th style="font-weight: bold">FECHA</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data['mailing'] as $key => $value)
        <tr>
            <?php
                $status = [
                    'email' => ['Otro', 'Rebotado', 'Email inválido', 'Error', 'Entregado', 'Abierto', 'Clickado'],
                    'whatsapp' => ['', '', '', 'No entregado', 'Enviado', 'Entregado', 'Leído'],
                ];
            ?>
            <td>{{ $value['file'] }}</td>
            <td>{{ $value['type_send'] }}</td>
            <td>{{ $value['type_message'] }}</td>
            <?php $_status = ($value['type_send'] == 'email') ? 'status_email' : 'status_wsp'; ?>
            <td>{{ $status[$value['type_send']][$value[$_status]] }}</td>
            <td>{{ $value['nropax'] }}</td>
            <td>{{ $value['message'] }}</td>
            <td>{{ date("d/m/Y H:i:s", strtotime($value['created_at'])) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

@endif

@if($data['type'] == 3)
<table>
    <thead>
        <tr>
            <th style="font-weight: bold">File</th>
            <th style="font-weight: bold">Pasajero</th>
            <th style="font-weight: bold">Fecha</th>
            <th style="font-weight: bold">Destino</th>
            <th style="font-weight: bold">Excursión</th>
            <th style="font-weight: bold">Guía Local</th>
            <th style="font-weight: bold">¿El destino cumplió con tus expectativas?</th>
            <th style="font-weight: bold">¿Qué te ha gustado más?</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data['items'] as $key => $value)
        <tr>
            <td>{{ $value['NROREF'] }}</td>
            <td>{{ $value['PAX'] }}</td>
            <td>{{ $value['FECHA'] }}</td>
            <td>{{ $value['CIUDAD'] }}</td>
            <td>{{ $value['EXCURSION'] }}</td>
            <td>{{ $value['GUIA'] }}</td>
            <td>{{ $value['DESTINO'] }}</td>
            <td>{{ $value['DESCRI'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

@if($data['type'] == 4)
<table>
    <thead>
        <tr>
            <th style="font-weight: bold">File</th>
            <th style="font-weight: bold">Pasajero</th>
            <th style="font-weight: bold">Fecha</th>
            <th style="font-weight: bold">¿Qué te ha gustado más?</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data['items'] as $key => $value)
        <tr>
            <td>{{ $value['NROREF'] }}</td>
            <td>{{ $value['PAX'] }}</td>
            <td>{{ $value['FECHA'] }}</td>
            <td>{{ $value['DESCRI'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

</html>
