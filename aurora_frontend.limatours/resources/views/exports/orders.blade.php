<?php $orders = $data; ?>

<table class="table table-hover">
    <thead>
    <tr>
        <th>Número Pedido</th>
        <th>Fecha Pedido</th>
        <th>Fecha de Respuesta</th>
        <th>Tiempo de Respuesta</th>
        <th>Número de Cotización</th>
        <th>Programación Referente</th>
        <th>Monto Estimado</th>
        <th>Número File</th>
        <th>Monto Concretado</th>
        <th>Fecha de viaje estimado</th>
        <th>Especialista</th>
        <th>Producto</th>
        <th>Cliente</th>
        <th>Nompax / Grupo</th>
        <th>Observaciones</th>
    </tr>
    </thead>
    <tbody>
    @foreach($orders as $key => $order)
        <tr>
            <td style="width:85px; text-align:center;">{{ $order['nroped'] }}</td>
            <td>{{ $order['fecrec'] }} {{$order['horrec']}}</td>
            <td>{{ $order['fecres'] }} {{$order['horres']}}</td>
            <td>{{ $order['horas'] }} horas</td>
            <td>{{ ($order['fecres'] != '' AND $order['fecres'] != NULL) ? ( ($order['nroref'] != '' AND $order['nroref'] != NULL) ? $order['nroref'] : (($order['chkpro'] > 0 AND $order['chkpro'] != NULL) ? ( ($order['chkpro'] == 1) ? 'PROGRAMACION CLIENTE' : 'PROGRAMACION LITO' ) : '')) : '' }}</td>
            <td>{{ @$order['nompaq'] }}</td>
            <td>{{ $order['price_estimated'] }}</td>
            <td class="center">{{ $order['nrofile'] }}</td>
            <td>{{ $order['price_end'] }}</td>
            <td>{{ ($order['fectravel_tca'] != '' AND $order['fectravel_tca'] != null) ? ($order['fectravel_tca']) : ($order['fectravel']) }}</td>
            <td class="center"><b>{{ $order['codusu'] }}</b></td>
            <td class="center">{{ $order['producto'] }}</td>
            <td class="center"><b>{{ $order['codigo'] }}</b></td>
            <td class="center"><b>{{ $order['nompax'] }}</b></td>
            <td class="center"><b>{{ $order['observ'] }}</b></td>
        </tr>
    @endforeach
    </tbody>
</table>
