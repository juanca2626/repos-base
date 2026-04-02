<html>
<table>
    <thead>
    <tr>
        <td>
            <b>HOTEL</b>
        </td>
        <td>
            <b>NOTAS - INGLÉS</b>
        </td>
        <td>
            <b>NOTAS - ESPAÑOL</b>
        </td>
        <td>
            <b>NOTAS - PORTUGUÉS</b>
        </td>
    </tr>
    @foreach($data['hotels'] as $key => $value)
        <tr>
            <td>
                {{ $value['name'] }}
            </td>
            <td>
                {{ @$value['translations'][0]['value'] }}
            </td>
            <td>
                {{ @$value['translations'][1]['value'] }}
            </td>
            <td>
                {{ @$value['translations'][2]['value'] }}
            </td>
        </tr>
    @endforeach
</table>
</html>
