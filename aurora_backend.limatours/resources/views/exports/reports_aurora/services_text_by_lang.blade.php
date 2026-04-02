<html>
<table>
    <thead>
    <tr>
        <th style="background-color: #bd0d12;color: #ffffff;">
            <b>ID SERVICIO</b>
        </th>
        <th style="background-color: #bd0d12;color: #ffffff;">
            <b>CODIGO SERVICIO</b>
        </th>
        <th style="background-color: #bd0d12;color: #ffffff;">
            <b>NOMBRE DEL SERVICIO ({{$lang}})</b>
        </th>
        <th style="background-color: #bd0d12;color: #ffffff;">
            <b>DESCRIPCION DEL SERVICIO ({{$lang}})</b>
        </th>
        <th style="background-color: #bd0d12;color: #ffffff;">
            <b>ITINERARIO DEL SERVICIO ({{$lang}})</b>
        </th>
    </tr>
    </thead>
    <tbody>

    @foreach($services as $key => $service)
        <tr>
            <td>
                {{ $service['id'] }}
            </td>
            <td>
                {{ $service['aurora_code'] }}
            </td>
            @foreach($service['service_translations'] as $k => $translation)
                <td>
                    {{ $translation['name'] }}
                </td>
                <td>
                    {{ $translation['description'] }}
                </td>
                <td>
                    {{ $translation['itinerary'] }}
                </td>
            @endforeach
        </tr>
    @endforeach

    </tbody>
</table>
</html>
