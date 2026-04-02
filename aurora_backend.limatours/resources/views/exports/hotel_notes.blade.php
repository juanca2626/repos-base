<?php $langs = ['ESPAÑOL', 'INGLES', 'PORTUGUES']; ?>
<table>
<thead>
<tr>
<td>
<b>HOTEL</b>
</td>
@foreach($langs as $key => $value)
<td>
<b>NOTAS - {{ $value }}</b>
</td>
@endforeach
</tr>
</thead>
<tbody>
@foreach($data['hotels'] as $key => $value)
<tr>
<td>
{{ $value['name'] }}
</td>
@foreach($langs as $l => $language)
<td>
@if(isset($value['translations'][$l]) AND !empty($value['translations'][$l]))
    {{ $value['translations'][$l]['value'] }}
@endif
</td>
@endforeach
</tr>
@endforeach
</tbody>
</table>