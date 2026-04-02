<html>
    <table>
        <thead>
        <tr>
            <td style="background-color: #8d0a0d;color: #ffffff;" >
                <b>SERVICIO</b>
            </td>
            <td style="background-color: #8d0a0d;color: #ffffff;">REMARKS</td>
            <td style="background-color: #8d0a0d;color: #ffffff;" >
                ENLACE
            </td>
        </tr>
        </thead>
        <tbody>
        @foreach( $data['services'] as $service)
            <tr>
                <td>{{ $service["aurora_code"] }} - {{ $service["name"] }}</td>
                <td>{{ $service["notes"] }}</td>
                <td>{{ url('/#/services_new/edit/' . $service['id']) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</html>
