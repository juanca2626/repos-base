<table>
<tr>
<td>LIMA TOURS S.A.C</td>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
<td>Fecha: {{ date("d/m/Y") }}</td>
</tr>
<tr>
<td>Empresa de Viajes y Turismo</td>
</tr>
</table>

<table>
<tr>
</tr>
<tr>
<td></td>
<td></td>
<td></td>
<td>LISTA DE REFERENCIAS</td>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
</tr>
</table>

<table>
<thead>
<tr>
<th>Cod. Client</th>
<th>Client</th>
<th>Description</th>
<th>Specialist</th>
<th>KAM</th>
<th>File</th>
<th>N° Pax</th>
<th>Date in</th>
<th>Date out</th>
<th>MU Client File (%)</th>
<th>MU QR (%)</th>
<th>Final MU (%)</th>
</tr>
</thead>
<tbody>
@foreach($files as $file)
<tr>
<td>{{ $file['client_code'] }}</td>
<td>{{ $file['client_name'] }}</td>
<td>{{ $file['description'] }}</td>
<td>{{ $file['executive_code'] }}</td>
<td>{{ $file['executive_code_sale'] }}</td>
<td>{{ $file['file_number'] }}</td>
<td>{{ $file['total_pax'] }}</td>
<td>{{ date("d/m/Y", strtotime($file['date_in'])) }}</td>
<td>{{ date("d/m/Y", strtotime($file['date_out'])) }}</td>
<td>{{ $file['markup_client'] }}</td>
<td></td>
<td>{{ $file['profitability'] }}</td>
</tr>
@endforeach
</tbody>
</table>
