<?php

$clients = $data; $report = $table;
$title = [
'', 'Clientes nuevos', 'Clientes con acceso a A2', 'Clientes que han ingresado a A2',
'Reservas y Cotizaciones de clientes A2', 'Ingreso de datos por clientes A2',
];

?>

@if(isset($report))
<table>
<thead>
<tr>
<th colspan="{{ ($report == 3) ? 5 : 4 }}" scope="row">{{ $title[$report] }}</th>
</tr>
</thead>
</table>
@endif

@if($report == 1 OR $report == 2 OR $report == 3)
<table class="table table-striped">
<thead>
<tr>
<th>Código</th>
<th>Descripción</th>
<th>Sector</th>
<th>Fecha</th>
@if($report == 3)
<th>Cantidad Login</th>
@endif
</tr>
</thead>
<tbody>
@foreach($clients as $key => $client)
<tr>
<td>{{ $client['code'] }}</td>
<td>{{ $client['business_name'] }}</td>
<td>{{ $client['markets']['code'] }} - {{ $client['markets']['name'] }}</td>
@if($report == 3)
<td>{{ date("Y-m-d H:i:s", strtotime($client['updated_at'])) }}</td>
@else
<td>{{ date("Y-m-d H:i:s", strtotime($client['created_at'])) }}</td>
@endif
@if($report == 3)
<td>{{ $client['count_login'] }}</td>
@endif
</tr>
@endforeach
</tbody>
</table>
@endif

@if($report == 4)
<?php /*
<table>
<tbody>
<tr>
<td>Cotizaciones y/o reservas realizadas por cliente con acceso a A2</td>
<td>{{ $clients['reservations'] }} files</td>
</tr>
<tr>
<td>Total de clientes con acceso a A2</td>
<td>{{ $clients['clients'] }} clientes</td>
</tr>
<tr>
<td>Porcentaje</td>
<td>{{ $clients['total'] }}%</td>
</tr>
</tbody>
</table>
*/ ?>



@if(isset($clients['reservations']) && $clients['reservations'] > 0)
<table class="table table-striped">
<thead>
<tr>
<th>File</th>
<th>Cliente</th>
<th>Usuario</th>
<th>Fecha Apertura</th>
<th>Aperturado desde</th>
</tr>
</thead>
<tbody>
@foreach($clients['reservations_items'] as $reservation)
<tr>
<td>{{ $reservation['file_code'] }}</td>
<td>{{ $reservation['client_code'] }}</td>
<td>{{ $reservation['create_user']['code'] }} - {{ $reservation['create_user']['email'] }}</td>
<td>{{ $reservation['date_init'] }}</td>
<td>{{ $reservation['entity'] }}</td>
</tr>
@endforeach
</tbody>
</table>
@endif

@if(isset($clients['quotes']) &&  $clients['quotes'] > 0)
<table class="table table-striped">
<thead>
<tr>
<th>Cotización</th>
<th>Cliente</th>
<th>Fecha Inicio</th>
</tr>
</thead>
<tbody>
@foreach($clients['quotes_items'] as $quote)
<tr>
<td>{{ $quote['id'] }}</td>
<td>{{ $quote['user']['code'] }} - {{ $quote['user']['email'] }}</td>
<td>{{ $quote['date_in'] }}</td>
</tr>
@endforeach
</tbody>
</table>
@endif

@if(isset($clients['clients']) && $clients['clients'] > 0)
    <table class="table table-striped">
        <thead>
        <tr>
            <th>Código</th>
            <th>Descripción</th>
            <th>Sector</th>
            <th>Fecha</th>
        </tr>
        </thead>
        <tbody>
        @foreach($clients['clients_items'] ?? [] as $client)
            <tr>
                <td>{{ $client['code'] ?? 'N/A' }}</td>
                <td>{{ $client['business_name'] ?? 'N/A' }}</td>
                <td>
                    {{ $client['markets']['code'] ?? 'N/A' }} -
                    {{ $client['markets']['name'] ?? 'N/A' }}
                </td>
                <td>{{ $client['created_at'] ?? 'N/A' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

@endif


@if($report == 5)
<?php /*
<table>
<tbody>
<tr>
<td>Número de files con ingreso de datos desde A2 o link</td>
<td>{{ $clients['logs'] }} files</td>
</tr>
<tr>
<td>Total de files</td>
<td>{{ $clients['reservations'] }} clientes</td>
</tr>
<tr>
<td>Porcentaje</td>
<td>{{ $clients['total'] }}%</td>
</tr>
</tbody>
</table>
*/ ?>

@if($clients['logs'] > 0)
<table class="table table-striped">
<thead>
<tr>
<th>File</th>
<th>Usuario</th>
<th>Cliente</th>
<th>Tipo</th>
</tr>
</thead>
<tbody>
@foreach($clients['logs_items'] as $client)
<tr>
<td>{{ $client['nrofile'] }}</td>
<td>{{ $client['user']['code'] }} - {{ $client['user']['name'] }}</td>
<td>{{ $client['client']['code'] }} - {{ $client['client']['business_name'] }}</td>
<td>{{ $client['type'] }}</td>
</tr>
@endforeach
</tbody>
</table>
@endif


@if($clients['reservations'] > 0)
<table class="table table-striped">
<thead>
<tr>
<th>File</th>
<th>Cliente</th>
<th>Usuario</th>
<th>Fecha Apertura</th>
<th>Aperturado desde</th>
</tr>
</thead>
<tbody>
@foreach($clients['reservations_items'] as $reservation)
<tr>
<td>{{ $reservation['booking_code'] }}</td>
<td>{{ $reservation['client_code'] }}</td>
<td>{{ $reservation['create_user']['code'] }} - {{ $reservation['create_user']['email'] }}</td>
<td>{{ $reservation['date_init'] }}</td>
<td>{{ $reservation['entity'] }}</td>
</tr>
@endforeach
</tbody>
</table>
@endif

@endif
