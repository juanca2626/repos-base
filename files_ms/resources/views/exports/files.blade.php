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
<th>NUMERO</th>
<th>PAX</th>
<th>CANT</th>
<th>INICIO</th>
<th>FIN</th>
<th>CLIENT</th>
<th>CODSEC</th>
<th>VO</th>
<th>TK</th>
<th>FC</th>
<th>ST</th>
<th>KAM</th>
<th>QRV</th>
<th>QRR</th>
<th>PROMOC</th>
<th>NRO PEDIDO</th>
<th>UBI</th>
</tr>
</thead>
<tbody>
@foreach($files as $file)
<tr>
<td>{{ $file['file_number'] }}</td>
<td>{{ $file['description'] }}</td>
<td>{{ $file['total_pax'] }}</td>
<td>{{ date("d/m/Y", strtotime($file['date_in'])) }}</td>
<td>{{ date("d/m/Y", strtotime($file['date_out'])) }}</td>
<td>{{ $file['client_name'] }}</td>
<td>{{ $file['sector_code'] }}</td>
<td>{{ ($file['have_voucher']) ? 'OK' : '' }}</td>
<td>{{ ($file['have_ticket']) ? 'OK' : '' }}</td>
<td>{{ ($file['have_invoice']) ? 'OK' : '' }}</td>
<td>{{ $file['status'] }}</td>
<td>{{ $file['executive_code'] }}</td>
<td>{{ $file['executive_code_sale'] }}</td>
<td>{{ $file['executive_code_process'] }}</td>
<td>{{ $file['promotion'] }}</td>
<td>{{ $file['order_number'] }}</td>
<td>{{ ($file['revision_stages'] == 2) ? 'OPE' : 'DTR' }}</td>
</tr>
@endforeach
</tbody>
</table>