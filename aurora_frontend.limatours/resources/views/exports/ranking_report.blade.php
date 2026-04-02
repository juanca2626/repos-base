<?php $ranking = $data['ranking']; $acumulado = $data['acumulado']; $responseExecutives = $acumulado['executives'];
$responseCustomers = $acumulado['customers']; $all = $acumulado['all']; $quantity = $acumulado['quantity']; ?>

@if($table == 'customers')
<table class="table">
    <thead>
    <tr>
        <th scope="col" rowspan="2">CLIENTE</th>
        @if($quantity > 0)
            <th colspan="4" scope="row">ACUMULADO</th>
        @endif
        @foreach($ranking as $k => $r)
            <th colspan="4" scope="row">{{ $k }}</th>
        @endforeach
    </tr>
    <tr>
        @if($quantity > 0)
            <th scope="col">INDICADOR TIEMPO DE RESPUESTA (%)</th>
            <th scope="col">INDICADOR PEDIDOS CONCRETADOS (%)</th>
            <th scope="col">RATIO DE TRABAJO <i class="fa fa-info-circle" v-b-tooltip title="#Recotizaciones"></i></th>
            <th scope="col">RATIO DE TRABAJO <i class="fa fa-info-circle" v-b-tooltip title="#cotis trabajadas por file contratado"></i></th>
        @endif
        @foreach($ranking as $k => $r)
            <th scope="col">INDICADOR TIEMPO DE RESPUESTA (%)</th>
            <th scope="col">INDICADOR PEDIDOS CONCRETADOS (%)</th>
            <th scope="col">RATIO DE TRABAJO <i class="fa fa-info-circle" v-b-tooltip title="#Recotizaciones"></i></th>
            <th scope="col">RATIO DE TRABAJO <i class="fa fa-info-circle" v-b-tooltip title="#cotis trabajadas por file contratado"></i></th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @foreach($responseCustomers as $c => $customer)
        <tr>
            <td>{{ $c }}</td>
            @if($quantity > 0)
                <td class="{!! ($customer['stats']['time_response'] < 90) ? 'danger' : '' !!}">{{ $customer['stats']['time_response'] }}</td>
                <td>{{ $customer['stats']['percent_placed'] }}</td>
                <td class="{!! ($customer['stats']['work_rate_orders'] > 3) ? 'danger' : '' !!}">{{ $customer['stats']['work_rate_orders'] }}</td>
                <td class="{!! ($customer['stats']['work_rate'] > 3) ? 'danger' : '' !!}">{{ $customer['stats']['work_rate'] }}</td>
            @endif
            @foreach($ranking as $k => $r)
                <?php $rcustomer = $r['customers'][$c]; ?>
                <td class="{!! ($rcustomer['stats']['time_response'] < 90) ? 'danger' : '' !!}">{{ $rcustomer['stats']['time_response'] }}</td>
                <td>{{ $rcustomer['stats']['percent_placed'] }}</td>
                <td class="{!! ($rcustomer['stats']['work_rate_orders'] > 3) ? 'danger' : '' !!}">{{ $rcustomer['stats']['work_rate_orders'] }}</td>
                <td class="{!! ($rcustomer['stats']['work_rate'] > 3) ? 'danger' : '' !!}">{{ $rcustomer['stats']['work_rate'] }}</td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
    <tfoot>
    <tr>
        <th>TOTAL GENERAL</th>
        @if($quantity > 0)
            <td class="{!! ($all['time_response'] < 90) ? 'danger' : '' !!}">{{ $all['time_response'] }}</td>
            <td>{{ $all['percent_placed'] }}</td>
            <td class="{!! ($all['work_rate_orders'] > 3) ? 'danger' : '' !!}">{{ $all['work_rate_orders'] }}</td>
            <td class="{!! ($all['work_rate'] > 3) ? 'danger' : '' !!}">{{ $all['work_rate'] }}</td>
        @endif
        @foreach($ranking as $k => $r)
            <td class="{!! ($r['all']['time_response'] < 90) ? 'danger' : '' !!}">{{ $r['all']['time_response'] }}</td>
            <td>{{ $r['all']['percent_placed'] }}</td>
            <td class="{!! ($r['all']['work_rate_orders'] > 3) ? 'danger' : '' !!}">{{ $r['all']['work_rate_orders'] }}</td>
            <td class="{!! ($r['all']['work_rate'] > 3) ? 'danger' : '' !!}">{{ $r['all']['work_rate'] }}</td>
        @endforeach
    </tr>
    </tfoot>
</table>
@endif

@if($table == 'executives')
<table class="table">
    <thead>
    <tr>
        <th scope="col" rowspan="2">ESPECIALISTA</th>
        @if($quantity > 0)
            <th colspan="4" scope="row">ACUMULADO</th>
        @endif
        @foreach($ranking as $k => $r)
        <th colspan="4" scope="row">{{ $k }}</th>
        @endforeach
    </tr>
    <tr>
        @if($quantity > 0)
            <th scope="col">INDICADOR TIEMPO DE RESPUESTA (%)</th>
            <th scope="col">INDICADOR PEDIDOS CONCRETADOS (%)</th>
            <th scope="col">RATIO DE TRABAJO <i class="fa fa-info-circle" v-b-tooltip title="#Recotizaciones"></i></th>
            <th scope="col">RATIO DE TRABAJO <i class="fa fa-info-circle" v-b-tooltip title="#cotis trabajadas por file contratado"></i></th>
        @endif
        @foreach($ranking as $k => $r)
            <th scope="col">INDICADOR TIEMPO DE RESPUESTA (%)</th>
            <th scope="col">INDICADOR PEDIDOS CONCRETADOS (%)</th>
            <th scope="col">RATIO DE TRABAJO <i class="fa fa-info-circle" v-b-tooltip title="#Recotizaciones"></i></th>
            <th scope="col">RATIO DE TRABAJO <i class="fa fa-info-circle" v-b-tooltip title="#cotis trabajadas por file contratado"></i></th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @foreach($responseExecutives as $e => $executive)
        <tr>
            <td>{{ $e }}</td>
            @if($quantity > 0)
                <td class="{!! ($executive['stats']['time_response'] < 90) ? 'danger' : '' !!}">{{ $executive['stats']['time_response'] }}</td>
                <td>{{ $executive['stats']['percent_placed'] }}</td>
                <td class="{!! ($executive['stats']['work_rate_orders'] > 3) ? 'danger' : '' !!}">{{ $executive['stats']['work_rate_orders'] }}</td>
                <td class="{!! ($executive['stats']['work_rate'] > 3) ? 'danger' : '' !!}">{{ $executive['stats']['work_rate'] }}</td>
            @endif
            @foreach($ranking as $k => $r)
                <?php $rexecutive = $r['executives'][$e]; ?>
                <td class="{!! ($rexecutive['stats']['time_response'] < 90) ? 'danger' : '' !!}">{{ $rexecutive['stats']['time_response'] }}</td>
                <td>{{ $rexecutive['stats']['percent_placed'] }}</td>
                <td class="{!! ($rexecutive['stats']['work_rate_orders'] > 3) ? 'danger' : '' !!}">{{ $rexecutive['stats']['work_rate_orders'] }}</td>
                <td class="{!! ($rexecutive['stats']['work_rate'] > 3) ? 'danger' : '' !!}">{{ $rexecutive['stats']['work_rate'] }}</td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
    <tfoot>
    <tr>
        <th>TOTAL GENERAL</th>
        @if($quantity > 0)
            <td class="{!! ($all['time_response'] < 90) ? 'danger' : '' !!}">{{ $all['time_response'] }}</td>
            <td>{{ $all['percent_placed'] }}</td>
            <td class="{!! ($all['work_rate_orders'] > 3) ? 'danger' : '' !!}">{{ $all['work_rate_orders'] }}</td>
            <td class="{!! ($all['work_rate'] > 3) ? 'danger' : '' !!}">{{ $all['work_rate'] }}</td>
        @endif
        @foreach($ranking as $k => $r)
            <td class="{!! ($r['all']['time_response'] < 90) ? 'danger' : '' !!}">{{ $r['all']['time_response'] }}</td>
            <td>{{ $r['all']['percent_placed'] }}</td>
            <td class="{!! ($r['all']['work_rate_orders'] > 3) ? 'danger' : '' !!}">{{ $r['all']['work_rate_orders'] }}</td>
            <td class="{!! ($r['all']['work_rate'] > 3) ? 'danger' : '' !!}">{{ $r['all']['work_rate'] }}</td>
        @endforeach
    </tr>
    </tfoot>
</table>
@endif
