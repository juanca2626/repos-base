<html>
@foreach( $data["categories"] as $category)
    <table>
        <tr>
            <td style="background-color: #BD0D12;color: #ffffff">
            <td style="background-color: #BD0D12;color: #ffffff"></td>
            <td style="background-color: #BD0D12;color: #ffffff"></td>
        </tr>
        <tr>
            <td style="background-color: #BD0D12;color: #ffffff">
            <td style="background-color: #BD0D12;color: #ffffff">
                SOLICITUD DE SERVICIOS
            </td>
            <td style="background-color: #BD0D12;color: #ffffff">
            </td>
        </tr>
        <tr>
            <td style="background-color: #BD0D12;color: #ffffff">
            </td>
            <td style="background-color: #BD0D12;color: #ffffff"></td>
            <td style="background-color: #BD0D12;color: #ffffff"></td>
        </tr>
        <tr>
            <td style="background-color: #BD0D12;color: #ffffff"></td>
            <td style="background-color: #BD0D12;color: #ffffff"></td>
            <td style="background-color: #BD0D12;color: #ffffff"></td>
        </tr>
    </table>
    <table>
        <thead>
        <tr>
            <td style="background-color: #BD0D12;color: #ffffff;" >
                <b>Fecha de Inicio</b>
            </td>
            <td style="background-color: #BD0D12;color: #ffffff;"><b>Descripcion</b></td>
            @foreach( $data['passengers'] as $passenger)
                <td style="background-color: #BD0D12;color: #ffffff;">{{ $passenger["first_name"] }} {{ $passenger["last_name"] }}</td>
            @endforeach
        </tr>
        </thead>
        <tbody>
        @foreach( $category["services"] as $service)
            <tr>
                <td>{{$service["date_service"]}}</td>
                <td>
                {{ $service["service_code"] }} - {{ $service["service_name"] }}
                </td>
                @foreach( $service['passengers'] as $key => $amount)
                    <td>{{ $amount }}</td>
                @endforeach
            </tr>
        @endforeach
        <tr>
            <td></td>
            <td></td>
            @foreach( $data['passengers'] as $passenger)
                <td>{{ $passenger["total"] }}</td>
            @endforeach
        </tr>
        </tbody>
    </table>
@endforeach
</html>
