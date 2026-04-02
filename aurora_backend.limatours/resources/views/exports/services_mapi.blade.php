<html>
    <table>
        <thead>
        <tr>
            <td rowspan="2" scope="col" style="background-color: #8d0a0d;color: #ffffff;" >
                <b>Servicio</b>
            </td>
            <td colspan="6" scope="row" style="background-color: #8d0a0d;color: #ffffff;">Español</td>
            <td colspan="6" scope="row" style="background-color: #8d0a0d;color: #ffffff;">Inglés</td>
            <td colspan="6" scope="row" style="background-color: #8d0a0d;color: #ffffff;">Portugués</td>
            <td colspan="6" scope="row" style="background-color: #8d0a0d;color: #ffffff;">Italiano</td>
        </tr>
        <tr>
            @for($i=1;$i<=4;$i++)
                <td style="background-color: #8d0a0d;color: #ffffff;">Nombre</td>
                <td style="background-color: #8d0a0d;color: #ffffff;">Nombre - Comercial</td>
                <td style="background-color: #8d0a0d;color: #ffffff;">Description</td>
                <td style="background-color: #8d0a0d;color: #ffffff;">Description - Comercial</td>
                <td style="background-color: #8d0a0d;color: #ffffff;">Itinerario</td>
                <td style="background-color: #8d0a0d;color: #ffffff;">Itinerario - Comercial</td>
            @endfor
        </tr>
        </thead>
        <tbody>
        @foreach( $data["services"] as $service)
            <tr>
                <td>{{$service["aurora_code"]}}</td>
                @foreach( $service["service_translations"] as $translation)
                    <td>{{ $translation["name"] }}</td>
                    <td>{{ $translation["name_commercial"] }}</td>
                    <td>{{ $translation["description"] }}</td>
                    <td>{{ $translation["description_commercial"] }}</td>
                    <td>{{ $translation["itinerary"] }}</td>
                    <td>{{ $translation["itinerary_commercial"] }}</td>
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
</html>
