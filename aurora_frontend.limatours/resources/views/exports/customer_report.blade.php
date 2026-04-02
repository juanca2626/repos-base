<?php $customers = $data['customers']; $all = $data['all']; ?>
<table class="table text-center table-facturacion">
    <thead>
    <tr>
        <th scope="col">AREA</th>
        <th scope="col">CLIENTE</th>
        <th scope="col">PEDIDOS RECIBIDOS</th>
        <th scope="col">VALOR DE LOS PEDIDOS RECIBIDOS</th>
        <th scope="col">COTIZACIONES TRABAJADAS</th>
        <th scope="col">COTIZACIONES RESPONDIDAS A TIEMPO</th>
        <th scope="col">INDICADOR TIEMPO DE RESPUESTA</th>
        <th scope="col">FILES CONCRETADOS</th>
        <th scope="col">MONTO CONCRETADO</th>
        <th scope="col">INDICADOR PEDIDOS CONCRETADOS</th>
        <th scope="col">RATIO DE TRABAJO <i class="fa fa-info-circle" v-b-tooltip title="#Recotizaciones"></i></th>
        <th scope="col">RATIO DE TRABAJO <i class="fa fa-info-circle" v-b-tooltip title="#cotis trabajadas por file contratado"></i></th>
    </tr>
    </thead>
    <tbody>
    @foreach($customers as $k => $customer)
    <tr>
        <?php // $order = $customer['orders']; ?>
        <td>{{ @$customer['area'] }}</td>
        <td>({{ $k }})</td>
        <td>{{ $customer['stats']['all_orders'] }}</td>
        <td>{{ $customer['stats']['mount_all_orders'] }}</td>
        <td>{{ $customer['stats']['all_quotes'] }}</td>
        <td>{{ $customer['stats']['quotes_ok'] }}</td>
        <td class="{!! ($customer['stats']['time_response'] < 90) ? 'danger' : '' !!}">{{ $customer['stats']['time_response'] }}</td>
        <td>{{ $customer['stats']['files_placed'] }}</td>
        <td>{{ $customer['stats']['mount_orders_placed'] }}</td>
        <td>{{ $customer['stats']['percent_placed'] }}</td>
        <td class="{!! ($customer['stats']['work_rate_orders'] > 3) ? 'danger' : '' !!}">{{ $customer['stats']['work_rate_orders'] }}</td>
        <td class="{!! ($customer['stats']['work_rate'] > 3) ? 'danger' : '' !!}">{{ $customer['stats']['work_rate'] }}</td>
    </tr>
    @endforeach
    </tbody>
    <tfoot>
    <tr>
        <th scope="col" colspan="2" >TOTAL GENERAL</th>
        <th>{{ $all['all_orders'] }}</th>
        <th>{{ $all['mount_all_orders'] }}</th>
        <th>{{ $all['all_quotes'] }}</th>
        <th>{{ $all['quotes_ok'] }}</th>
        <th class="{!! ($all['time_response'] < 90) ? 'danger' : '' !!}">{{ $all['time_response'] }}</th>
        <th>{{ $all['orders_placed'] }}</th>
        <th>{{ $all['mount_orders_placed'] }}</th>
        <th>{{ $all['percent_placed'] }}</th>
        <th class="{!! ($all['work_rate_orders'] > 3) ? 'danger' : '' !!}">{{ $all['work_rate_orders'] }}</th>
        <th class="{!! ($all['work_rate'] > 3) ? 'danger' : '' !!}">{{ $all['work_rate'] }}</th>
    </tr>
    </tfoot>
</table>
