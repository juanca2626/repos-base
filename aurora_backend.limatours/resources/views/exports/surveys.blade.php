<html>
<table>
<thead>
<tr>
<th>
<b>File</b>
</th>
<th>
<b>Nombre del Pasajero</b>
</th>
<th>
<b>Nombre del Programa</b>
</th>
<th>
<b>Nacionalidad</b>
</th>
<th>
<b>Review</b>
</th>
</tr>
</thead>
<tbody>
@foreach($surveys as $key => $value)
<tr>
<td>
{{ $value['nroref'] }}
</td>
<td>
{{ ($value['passenger_name']) }}
</td>
<td>
@if(isset($packages[$value['nroref']]))
{{ $packages[$value['nroref']]['translations'][0]['tradename'] }}
@else
-
@endif
</td>
<td>
{{ $value['passenger_nacion'] }}
</td>
<td>
{{ ($value['descri']) }}
</td>
</tr>
@endforeach
</tbody>
</table>
</html>