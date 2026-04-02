<?php $executives = $data['executives']; $products = $data['products']; $all = $data['all']; $type = $table; ?>

@if($type == 'executives')
<table class="table text-center table-facturacion">
    <thead>
    <tr>
        <th scope="col">ESPECIALISTA</th>
        <th scope="col">PEDIDOS RECIBIDOS</th>
        <th scope="col">VALOR DE LOS PEDIDOS RECIBIDOS</th>
        <th scope="col">COTIZACIONES TRABAJADAS</th>
        <th scope="col">COTIZACIONES RESPONDIDAS A TIEMPO</th>
        <th scope="col">INDICADOR TIEMPO DE RESPUESTA</th>
        <th scope="col">PEDIDOS CONTRATADOS</th>
        <th scope="col">MONTO CONCRETADO</th>
        <th scope="col">INDICADOR PEDIDOS CONCRETADOS</th>
        <th scope="col">RATIO DE TRABAJO <i class="fa fa-info-circle" v-b-tooltip title="#Recotizaciones"></i></th>
        <th scope="col">RATIO DE TRABAJO <i class="fa fa-info-circle" v-b-tooltip title="#cotis trabajadas por file contratado"></i></th>
    </tr>
    </thead>
    <tbody>
    @foreach($executives as $k => $executive)
        <tr>
            <td>>{{ $k }}</td>
            <td>{{ $executive['stats']['all_orders'] }}</td>
            <td>{{ $executive['stats']['mount_all_orders'] }}</td>
            <td>{{ $executive['stats']['all_quotes'] }}</td>
            <td>{{ $executive['stats']['quotes_ok'] }}</td>
            <td class="{!! ($executive['stats']['time_response'] < 90) ? 'danger' : '' !!}">{{ $executive['stats']['time_response'] }}</td>
            <td>{{ $executive['stats']['orders_placed'] }}</td>
            <td>{{ $executive['stats']['mount_orders_placed'] }}</td>
            <td>{{ $executive['stats']['percent_placed'] }}</td>
            <td class="{!! ($executive['stats']['work_rate_orders'] > 3) ? 'danger' : '' !!}">{{ $executive['stats']['work_rate_orders'] }}</td>
            <td class="{!! ($executive['stats']['work_rate'] > 3) ? 'danger' : '' !!}">{{ $executive['stats']['work_rate'] }}</td>
        </tr>
    @endforeach
    </tbody>
    <tfoot>
    <tr>
        <th>REGION {{ $all['region'] }}</th>
        <th>{{ $all['stats']['all_orders'] }}</th>
        <th>{{ $all['stats']['mount_all_orders'] }}</th>
        <th>{{ $all['stats']['all_quotes'] }}</th>
        <th>{{ $all['stats']['quotes_ok'] }}</th>
        <th class="{!! ($all['stats']['time_response'] < 90) ? 'danger' : '' !!}">{{ $all['stats']['time_response'] }}</th>
        <th>{{ $all['stats']['orders_placed'] }}</th>
        <th>{{ $all['stats']['mount_orders_placed'] }}</th>
        <th>{{ $all['stats']['percent_placed'] }}</th>
        <th class="{!! ($all['stats']['work_rate_orders'] > 3) ? 'danger' : '' !!}">{{ $all['stats']['work_rate_orders'] }}</th>
        <th class="{!! ($all['stats']['work_rate'] > 3) ? 'danger' : '' !!}">{{ $all['stats']['work_rate'] }}</th>
    </tr>
    </tfoot>
</table>
@endif

@if($type == 'products')
<table class="table text-center table-facturacion">
    <thead>
    <tr>
        <th scope="col">PRODUCTO</th>
        <th scope="col">PEDIDOS RECIBIDOS</th>
        <th scope="col">VALOR DE LOS PEDIDOS RECIBIDOS</th>
        <th scope="col">COTIZACIONES TRABAJADAS</th>
        <th scope="col">COTIZACIONES RESPONDIDAS A TIEMPO</th>
        <th scope="col">INDICADOR TIEMPO DE RESPUESTA</th>
        <th scope="col">PEDIDOS CONTRATADOS</th>
        <th scope="col">MONTO CONCRETADO</th>
        <th scope="col">INDICADOR PEDIDOS CONCRETADOS</th>
        <th scope="col">RATIO DE TRABAJO <i class="fa fa-info-circle" v-b-tooltip title="#Recotizaciones"></i></th>
        <th scope="col">RATIO DE TRABAJO <i class="fa fa-info-circle" v-b-tooltip title="#cotis trabajadas por file contratado"></i></th>
    </tr>
    </thead>
    <tbody>
    @foreach($products as $k => $product)
        <tr>
            <td>>{{ $k }}</td>
            <td>{{ $product['stats']['all_orders'] }}</td>
            <td>{{ $product['stats']['mount_all_orders'] }}</td>
            <td>{{ $product['stats']['all_quotes'] }}</td>
            <td>{{ $product['stats']['quotes_ok'] }}</td>
            <td class="{!! ($product['stats']['time_response'] < 90) ? 'danger' : '' !!}">{{ $product['stats']['time_response'] }}</td>
            <td>{{ $product['stats']['orders_placed'] }}</td>
            <td>{{ $product['stats']['mount_orders_placed'] }}</td>
            <td>{{ $product['stats']['percent_placed'] }}</td>
            <td class="{!! ($product['stats']['work_rate_orders'] > 3) ? 'danger' : '' !!}">{{ $product['stats']['work_rate_orders'] }}</td>
            <td class="{!! ($product['stats']['work_rate'] > 3) ? 'danger' : '' !!}">{{ $product['stats']['work_rate'] }}</td>
        </tr>
    @endforeach
    </tbody>
    <tfoot>
    <tr>
        <th>REGION {{ $all['region'] }}</th>
        <th>{{ $all['stats']['all_orders'] }}</th>
        <th>{{ $all['stats']['mount_all_orders'] }}</th>
        <th>{{ $all['stats']['all_quotes'] }}</th>
        <th>{{ $all['stats']['quotes_ok'] }}</th>
        <th class="{!! ($all['stats']['time_response'] < 90) ? 'danger' : '' !!}">{{ $all['stats']['time_response'] }}</th>
        <th>{{ $all['stats']['orders_placed'] }}</th>
        <th>{{ $all['stats']['mount_orders_placed'] }}</th>
        <th>{{ $all['stats']['percent_placed'] }}</th>
        <th class="{!! ($all['stats']['work_rate_orders'] > 3) ? 'danger' : '' !!}">{{ $all['stats']['work_rate_orders'] }}</th>
        <th class="{!! ($all['stats']['work_rate'] > 3) ? 'danger' : '' !!}">{{ $all['stats']['work_rate'] }}</th>
    </tr>
    </tfoot>
</table>
@endif

