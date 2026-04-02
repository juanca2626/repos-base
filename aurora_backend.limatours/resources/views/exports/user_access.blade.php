<html>
    <table>
        <thead>
        <tr>
            <td style="background-color: #8d0a0d;color: #ffffff;" >
                <b>Código</b>
            </td>
            <td style="background-color: #8d0a0d;color: #ffffff;"><b>Nombre</b></td>
            <td style="background-color: #8d0a0d;color: #ffffff;"><b>Fecha de Ingreso</b></td>
            <td style="background-color: #8d0a0d;color: #ffffff;"><b>Hora de Ingreso</b></td>
        </tr>
        </thead>
        <tbody>
        @foreach($data['logs'] as $log)
            <tr>
                <td>{{ $log["user"]["code"] }}</td>
                <td>{{ $log["user"]["name"] }}</td>
                <td>{{ date("Y-m-d", strtotime($log["created_at"])) }}</td>
                <td>{{ date("H:i:s", strtotime($log["created_at"])) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</html>
