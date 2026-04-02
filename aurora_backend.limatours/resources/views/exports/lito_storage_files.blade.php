<html>
<table>
    <thead>
    <tr>
        <th>
            <b>FILE</b>
        </th>
        <th>
            <b>TIPO</b>
        </th>
        <th>
            <b>NOMBRE DEL ARCHIVO</b>
        </th>
        <th>
            <b>ENLACE AL ARCHIVO</b>
        </th>
    </tr>
    </thead>
    <tbody>

    @foreach($files as $key => $value)
        <tr>
            <td>
                {{ $value['file'] }}
            </td>
            <td>
                {{ $value['type']['name'] }}
            </td>
            <td>
                {{ $value['original_name'] }}
            </td>
            <td>
                {{ $value['link'] }}
            </td>
        </tr>
    @endforeach

    </tbody>
</table>
</html>
