<html>
<table>
    <thead>
    <tr>
        <th style="background-color: #bd0d12;color: #ffffff;">
            <b>ID SERVICIO</b>
        </th>
        <th style="background-color: #bd0d12;color: #ffffff;">
            <b>NOMBRE DEL SERVICIO</b>
        </th>
        <th style="background-color: #bd0d12;color: #ffffff;">
            <b>CODIGO</b>
        </th>
        <th style="background-color: #bd0d12;color: #ffffff;">
            <b>URL</b>
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
                {{ $service['name'] }}
            </td>
            <td>
                {{ $service['aurora_code'] }}
            </td>
            <td>
                <b>{{ secure_url('/#/services_new/' . $service['id'] . '/manage_service/gallery') }}</b>
            </td>
        </tr>
    @endforeach

    </tbody>
</table>
</html>
