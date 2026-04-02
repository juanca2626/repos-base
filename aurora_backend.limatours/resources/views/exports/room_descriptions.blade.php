<?php $langs = ['ESPAÑOL', 'INGLES', 'PORTUGUES']; ?>
<table>
<thead>
<tr>
<td>
<b>ID</b>
</td>
<td>
<b>HOTEL</b>
</td>
@foreach($langs as $key => $value)
<td>
<b>DESCRIPCIÓN - {{ $value }}</b>
</td>
@endforeach
</tr>
</thead>
<tbody>
@foreach($data['rooms'] as $key => $value)
<tr>
<td>
{{ $value['id'] }}
</td>
<td>
{{ $value['hotel']['name'] }}
</td>
@foreach($langs as $k => $v)
<td>
{{ (isset($value['description'][$k])) ? $value['description'][$k] : '' }}
</td>
@endforeach
</tr>
@endforeach
</tbody>
</table>
